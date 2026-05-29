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
	
	// Capture and parse SOAP XML response, convert to JSON and return
	$response = validateAddress();
	$json = json_encode($response, JSON_PRETTY_PRINT);
	
	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	echo $json;
	return $json;
	
	// Call ValidateAddress and and pass in given address
	function validateAddress()
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
		
		// Grab POST address data
		if(isset($_POST['Address1']))
			$addr1 = $_POST['Address1'];
		if(isset($_POST['Address2']))
			$addr2 = $_POST['Address2'];
		if(isset($_POST['City']))
			$city = $_POST['City'];
		if(isset($_POST['StateCode']))
			$state = $_POST['StateCode'];
		if(isset($_POST['PostalCode']))
			$postal = $_POST['PostalCode'];
		if(isset($_POST['CountryCode']))
			$country = $_POST['CountryCode'];
		
		$soapXML = $soapHeader . 
			'<s:Body>
				<ValidateAddress xmlns="http://tempuri.org/">
					<address xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:Address1>' . $addr1 . '</a:Address1>
						<a:Address2>' . $addr2 . '</a:Address2>
						<a:City>' . $city . '</a:City>
						<a:CountryCode>' . $country . '</a:CountryCode>
						<a:PostalCode>' . $postal . '</a:PostalCode>
						<a:StateCode>' . $state . '</a:StateCode>
					</address>
				</ValidateAddress>
			</s:Body>
		</s:Envelope>';

		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['VALIDATE_ADDRESS']['WSDL'], $bwsConfig['VALIDATE_ADDRESS']['FUNCTION'], $soapXML);
		
		// First, check for and return any cURL errors 
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$validateResponse = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'ValidateAddress',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $validateResponse;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));
		
		// Drill-down into ValidateAddressResult section of the XML body, we don't care about the rest of the response
		$xml = $xmlTree->xtree->Envelope->Body->ValidateAddressResponse->ValidateAddressResult;
		
		// Grab the status code if it is returned
		if (isset( $xml->Status))
			$statusCode = $xml->Status->_['value'];

		// Create JSON response data based on status code received
		switch($statusCode)
		{
			case 0:
				$message = "Failure - unknown error";
				break;
			case 1:
				$message = "Success no change required";
				$addr1 = $xml->Validated->Address1->_['value'];
				$addr2 = $xml->Validated->Address2->_['value'];
				$city = $xml->Validated->City->_['value'];
				$country = $xml->Validated->CountryCode->_['value'];
				$postal = $xml->Validated->PostalCode->_['value'];
				$state = $xml->Validated->StateCode->_['value'];
				break;
			case 2:
				$message = "Success suggested address";
				$addr1 = $xml->Validated->Address1->_['value'];
				$addr2 = $xml->Validated->Address2->_['value'];
				$city = $xml->Validated->City->_['value'];
				$country = $xml->Validated->CountryCode->_['value'];
				$postal = $xml->Validated->PostalCode->_['value'];
				$state = $xml->Validated->StateCode->_['value'];
				break;
			case 3:
				$message = "Failure - multiple suggested addresses returned";
				break;
			case 4:
				$message = "Failure - incomplete address";
				break;
			case 5:
				$message = "Failure - missing parameter 'CountryCode'";
				break;
			case 6:
				$message = "Failure - address validation failed";
				break;
			default:
				$message = "Failure - unknown error";
				break;
		}
		
		// Create associative array and return
		$validateResponse = array(
			'Code' => $statusCode,
			'Message' => $message,
			'Validated' => array(
				'Address1' => $addr1,
				'Address2' => $addr2,
				'City' => $city,
				'CountryCode' => $country,
				'PostalCode' => $postal,
				'StateCode' => $state
				)
			);
		return $validateResponse;
	}
?>