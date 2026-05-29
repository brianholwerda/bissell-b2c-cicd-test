<?php
	// Include helper function for parsing XML and common Web Service functions
	require("custom/xtree.php");
	require("custom/webservicehelper.php");
	
	// Find our position in the file tree
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}

	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	if(isset($_POST['sid']))
		$sid = $_POST['sid'];
	else
		$sid = $_GET['sid'];
	
	require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($sid);
	initConnectAPI();
	use RightNow\Connect\v1_3 as RNCPHP;
	
	// Pull custom configuration setting to determine mode, username/password, endpoints
	try
	{
		$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
		$bwsConfig = (json_decode($bwsConfig->Value, true));
	}
	
	catch(Exception $e)
	{
		return $e;
	}
	
	// Call proper function based on the passed in action
	if(isset($_POST['action']))
		$action = $_POST['action'];
	else
		$action = $_GET['action'];
	
	switch($action)
	{
		case "Subscribe":
		case "Unsubscribe":
			$response = handleSubscription($bwsConfig, $action);
			break;
		case "Status":
			$response = isSubscribed($bwsConfig);
			break;
		default:
			$response = array(
				'ResponseCode' => '400',
				'Status' => 'Invalid action',
				'Action' => $action
			);
			break;
	}	
	
	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	$json = json_encode($response, JSON_PRETTY_PRINT);
	echo $json;
	return $json;
	
	// Call Exact eNews web service and handle subscription/unsubscription request
	function handleSubscription($bwsConfig, $action)
	{				
		// Grab Email address
		if(isset($_POST['Email']))
			$email = $_POST['Email'];
		else
			$email = $_GET['Email'];
		
		//Grab Country Code
		if(isset($_POST['Country']))
			$country = $_POST['Country'];
		else
			$country = $_GET['Country'];
		
		if ($country == 2){
			$countryVal = 'CA';
		} else {
			$countryVal = 'US';
		}
		
		$soapXML = 
			'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
				<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
					<' . $action . 'Newsletter xmlns="http://tempuri.org/">
						<emailAddress>' . $email . '</emailAddress>
						<source>' . $bwsConfig['ENEWS']['SOURCE'][$countryVal] . '</source>
						<sourceURL>' . $bwsConfig['ENEWS']['SOURCE'][$countryVal] . '</sourceURL>
					</' . $action . 'Newsletter>
				</s:Body>
			</s:Envelope>';
		
		if($action == "Subscribe")
			$function = $bwsConfig['ENEWS']['SUB_FUNCTION'];
		else
			$function = $bwsConfig['ENEWS']['UNSUB_FUNCTION'];
		
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['ENEWS']['WSDL'] . $countryVal, $function, $soapXML);
		
		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$status = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$extSystem = "ENews" . $action;
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> $extSystem,
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $status;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));

		// Capture and return response
		if($action == "Subscribe")
			$result = $xmlTree->xtree->Envelope->Body->SubscribeNewsletterResponse->SubscribeNewsletterResult->Status->_['value'];
		else
			$result = $xmlTree->xtree->Envelope->Body->UnsubscribeNewsletterResponse->UnsubscribeNewsletterResult->Status->_['value'];
		
		$status = array (
			'Status' => $result
		);
		
		return $status;
	}

	// Call Exact eNews web service and fetch current subscription status for given email address
	function isSubscribed($bwsConfig)
	{				
		try
		{
			// Grab Email address
			if(isset($_POST['Email']))
				$email = $_POST['Email'];
			else
				$email = $_GET['Email'];
			
			//Grab Country Code
			if(isset($_POST['Country']))
				$country = $_POST['Country'];
			else
				$country = $_GET['Country'];
			
			if ($country == 2){
				$countryVal = 'CA';
			} else {
				$countryVal = 'US';
			}
			
			$soapXML = 
				'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
					<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
						<IsEmailSubscribed xmlns="http://tempuri.org/">
							<emailAddress>' . $email . '</emailAddress>
							<ChannelNumber>0</ChannelNumber>
						</IsEmailSubscribed>
					</s:Body>
				</s:Envelope>';
			
			$bissellResponse = curlExec($bwsConfig, $bwsConfig['ENEWS']['WSDL'] . $countryVal, $bwsConfig['ENEWS']['IS_SUBBED_FUNCTION'], $soapXML);
			
			// First, check for and return any cURL errors 
			if(isset($bissellResponse['CurlErrorCode']))
			{
				$status = array(
					'ResponseCode' => $bissellResponse['CurlErrorCode'],
					'ResponseMessage' => $bissellResponse['CurlErrorMsg']
				);
				
				$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
				$info = array(
					'ExtSystem'	=> 'ENewsSubscriptionStatusCheck',
					'RequestMsg' => $soapXML,
					'ResponseMsg' => $curlError,
					'Description' => $curlError,
					'Error'	=> $curlError
				);
				logSysIntegration($info);
				return $status;
			}
			
			// Parse entire XML response into tree
			$xmlTree = new xtree(
				array(
					'xmlRaw' => $bissellResponse,
					'stripNamespaces' => true
				));

			// Capture and return response
			$result = $xmlTree->xtree->Envelope->Body->IsEmailSubscribedResponse->IsEmailSubscribedResult->_['value'];

			$status = array (
				'Status' => $result
			);
			
			return $status;
		}
		
		catch(Exception $e)
		{
			return $e;
		}
		
		
	}
?>