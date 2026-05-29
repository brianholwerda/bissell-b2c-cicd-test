<?php
	// Include helper function for parsing XML and common Web Service functions
	require("custom/xtree.php");
	require("custom/webservicehelper.php");
	
	// Find our position in the file tree
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}

	// .NET PAYLOAD
	if(isset($_GET['sid']) || isset($_POST['sid']))
	{
		if(isset($_POST['sid']))
			$sid = $_POST['sid'];
		else
			$sid = $_GET['sid'];
		
		if(isset($_POST['Email']))
			$email = $_POST['Email'];
		elseif(isset($_GET['Email']))
			$email = $_GET['Email'];
	}
	// BUI PAYLOAD
	else
	{
		$json = json_decode(file_get_contents('php://input'));
		$sid = $json->Session;
		$email = $json->Email;
	}
	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($sid);
	initConnectAPI();
	use RightNow\Connect\v1_3 as RNCPHP;
	
	// Capture and parse SOAP XML response, convert to JSON and return
	$response = getLoyaltyData($email);
	$json = json_encode($response, JSON_PRETTY_PRINT);

	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	echo $json;
	return $json;
	
	// Call GetConsumerLoyaltyRank and return loyalty level and points for given Contact
	function getLoyaltyData($email)
	{
		try
		{
			// Pull custom configuration setting to determine mode, username/password, endpoints
			$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
			$bwsConfig = (json_decode($bwsConfig->Value, true));
		}
		
		catch(Exception $err)
		{
			return $err;
		}
		
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['USERNAME'], $bwsConfig['PASSWORD']);
		
		$soapXML = $soapHeader .
			'<s:Body>
				<GetConsumerLoyaltyRank xmlns="http://tempuri.org/">
					<consumer xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Consumer" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:EmailAddress>' . $email . '</a:EmailAddress>
						<a:RewardPoints xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.LoyaltyPoints">
							<b:LoyaltyDiscount>0</b:LoyaltyDiscount>
							<b:LoyaltyFreeShipping>false</b:LoyaltyFreeShipping>
							<b:TotalMonthlyPoints>0</b:TotalMonthlyPoints>
							<b:TotalPoints>0</b:TotalPoints>
						</a:RewardPoints>
					</consumer>
				</GetConsumerLoyaltyRank>
			</s:Body>
		</s:Envelope>';

		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['CUSTOMER_LOYALTY']['WSDL'], $bwsConfig['CUSTOMER_LOYALTY']['FUNCTION'], $soapXML);

		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$rewards = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'GetConsumerLoyaltyRank',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $rewards;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));
			
		// Check for any fault codes first
		if(isset($xmlTree->xtree->Envelope->Body->Fault))
		{
			$responseCode = $xmlTree->xtree->Envelope->Body->Fault->faultcode->_['value'];
			$responseMessage = $xmlTree->xtree->Envelope->Body->Fault->faultstring->_['value'];
		}
			
		// Drill-down into GetConsumerLoyaltyRankResult section of the XML body
		$xmlResponse = $xmlTree->xtree->Envelope->Body->GetConsumerLoyaltyRankResponse->GetConsumerLoyaltyRankResult;
		
		// Grab the response code and message
		if(isset($xmlResponse->ResponseCode))
			$responseCode = $xmlResponse->ResponseCode->_['value'];
		if(isset($xmlResponse->ResponseMessage))
			$responseMessage = $xmlResponse->ResponseMessage->_['value'];
		
		$xmlReward = $xmlResponse->Contact->RewardPoints;
		
		if(isset($xmlReward->LoyaltyDiscount))
			$discount = $xmlReward->LoyaltyDiscount->_['value'];
		if(isset($xmlReward->LoyaltyFreeShipping))
			$freeShipping = $xmlReward->LoyaltyFreeShipping->_['value'];
		if(isset($xmlReward->LoyaltyRank))
			$rank = $xmlReward->LoyaltyRank->_['value'];
		if(isset($xmlReward->TotalMonthlyPoints))
			$ptsMonth = $xmlReward->TotalMonthlyPoints->_['value'];
		if(isset($xmlReward->TotalPoints))
			$ptsTotal = $xmlReward->TotalPoints->_['value'];
		
		$rewards = array(
			'ResponseCode' => $responseCode,
			'ResponseMessage' => $responseMessage,
			'LoyaltyDiscount' => $discount,
			'LoyaltyFreeShipping' => $freeShipping,
			'LoyaltyRank' => $rank,
			'TotalMonthlyPoints' => $ptsMonth,
			'TotalPoints' => $ptsTotal
		);
		
		$info = array(
				'ExtSystem'	=> 'GetConsumerLoyaltyRank',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => json_encode($rewards),
				'Description' => 'Consumer Loyalty'
			);
		logSysIntegration($info);
		
		return $rewards;
	}