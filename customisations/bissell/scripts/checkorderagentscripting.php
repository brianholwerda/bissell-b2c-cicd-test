<?php
	// Include helper function for parsing XML and common Web Service functions
	require("custom/xtree.php");
	require("custom/webservicehelper.php");
	
	// Capture POST payload from the parent BUI Extension
	// $payload = json_decode(file_get_contents("php://input"));
	// $sessionID = $payload->Session;
	// $orderNumber = $payload->OrderNumber;
	
	if(isset($_GET['Session']) || isset($_POST['Session']))
	{
		if(isset($_POST['Session']))
			$sessionID = $_POST['Session'];
		else
			$sessionID = $_GET['Session'];
		
		if(isset($_POST['OrderNumber']))
			$orderNumber = $_POST['OrderNumber'];
		else
			$orderNumber = $_GET['OrderNumber'];
	}
	// BUI PAYLOAD
	else
	{
		$payload = json_decode(file_get_contents("php://input"));
		$sessionID = $payload->Session;
		$orderNumber = $payload->OrderNumber;
	}
	
	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	require_once (get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($sessionID);
	initConnectAPI();
	use RightNow\Connect\v1_3 as RNCPHP;
	
	try
	{
		$bwsConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_BWS_INTEGRATION);
		$bwsConfig = (json_decode($bwsConfig->Value, true));
		
		// BUS$SysIntegrationLog debugger
		$sysLogConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_DEBUG_SYSINTEGRATION);
		$debug = $sysLogConfig->Value;
		
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['GET_ORDER']['USERNAME'], $bwsConfig['GET_ORDER']['PASSWORD']);

		$soapXML = $soapHeader .
			'<s:Body>
				<GetOnyxWebOrder xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:OrderNumber>' . $orderNumber . '</a:OrderNumber>
						<a:Source i:nil="true"/>
						<a:CustomerID i:nil="true"/>
						<a:VaxPlantCode i:nil="true"/>
					</p>
				</GetOnyxWebOrder>
			</s:Body>
		</s:Envelope>';	
		
		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['GET_ORDER']['WSDL'], $bwsConfig['GET_ORDER']['LINE_ITEM_HISTORY'], $soapXML);
		$bissellResponse = str_replace("&amp;", "%26", $bissellResponse);

		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'GetOnyxWebOrder',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
				//'ContactID'	=> $consumerID
			);
			logSysIntegration($info);
			
			$response = array(
				"SUCCESS" => false,
				"ERROR" => $curlError
			);
		}
		
		else
		{
			
			
			// Parse entire XML response into tree
			$xmlTree = new xtree(
				array(
					'xmlRaw' => $bissellResponse,
					'stripNamespaces' => true
			));
			
			// // Drill-down into GetConsumerOrderHistory section of the XML body
			// $bissellResponse = $xmlTree->xtree->Envelope->Body->GetConsumerTransHistoryResponse->GetConsumerTransHistoryResult;

			// if(isset($bissellResponse->ResponseCode))
				// $responseCode = $bissellResponse->ResponseCode->_['value'];
			// if(isset($bissellResponse->ResponseMessage))
				// $responseMessage = $bissellResponse->ResponseMessage->_['value'];

			// CAPTURE ConsumerID needed to fetch ContactID
			$consumerID = $xmlTree->xtree->Envelope->Body->GetOnyxWebOrderResponse->GetOnyxWebOrderResult->Order->OnyxWebOrder->CRMConsumerID->_['value'];
			
			$contactID = 0;
			if(isset($consumerID))
			{
				// $query = "SELECT ID FROM Contact WHERE CustomFields.c.consumer_id = " . $consumerID;
				// $queryResult = RNCPHP\ROQL::query($query)->next()->next();
				// $contactID = $queryResult['ID'];
				
				$contact = RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $consumerID );
				
				$mobilePhone = '';
				$homePhone = '';
				
				$phoneLength = count($contact->Phones);
				
				for($i = 0; $i < $phoneLength; $i++) {
					if($contact->Phones[$i]->PhoneType->LookupName == "Home Phone"){
						$homePhone = $contact->Phones[$i]->Number;
					}
					
					if($contact->Phones[$i]->PhoneType->LookupName == "Mobile Phone"){
						$mobilePhone = $contact->Phones[$i]->Number;
					}
				}
				
				$orderFound = true;
				$contactArray = array(
					"ContactID" => $contact->ID,
					"ConsumerID" => intval($consumerID),
					"FirstName" => $contact->Name->First,
					"LastName" => $contact->Name->Last,
					"EmailAddress" => $contact->Emails[0]->Address,
					"HomePhone" => $homePhone,
					"MobilePhone" => $mobilePhone,
					"StreetAddress" => $contact->Address->Street,
					"City" => $contact->Address->City,
					"State" => $contact->Address->StateOrProvince->LookupName,
					"Country" => $contact->Address->Country->LookupName,
					"PostalCode" => $contact->Address->PostalCode
				);
			} else {
				$orderFound = false;
				$contactArray = array();
			}
			
			$response = array(
				"Success" => true,
				"OrderFound" => $orderFound,
				"Contact" => $contactArray,
				"BissellResponseCode" => $xmlTree->xtree->Envelope->Body->GetOnyxWebOrderResponse->GetOnyxWebOrderResult->ResponseCode->_['value'],
				"BissellResponseMessage" => $xmlTree->xtree->Envelope->Body->GetOnyxWebOrderResponse->GetOnyxWebOrderResult->ResponseMessage->_['value'],
				"BissellResponse" => $bissellResponse
			);
		}
	}
	
	catch(Exception $e)
	{
		$response = array (
			"SUCCESS" => false,
			"ERROR" => $e->getMessage()
		);
	}
	
	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	$json = json_encode($response);
	
	echo $json;
	return $json;
?>