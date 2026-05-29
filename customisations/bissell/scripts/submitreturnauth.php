<?php

	// Include helper function for parsing XML and common Web Service functions
	require("custom/xtree.php");
	require("custom/webservicehelper.php");
	
	// Find our position in the file tree
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}
	
	if(!isset($account)){
		// .NET Payload
		if(isset($_GET['sid']))
		{
			$sid = $_GET['sid'];
			$action = $_GET['action'];
			$payload = json_decode(file_get_contents("php://input"));
			$returnAuth = $payload->ReturnAuth;
		}
		
		// BUI Payload from Send Return label and Submit Return workflows
		else
		{
			$payload = json_decode(file_get_contents("php://input"));
			$sid = $payload->Session;
			$action = $payload->Action;
			$returnAuth = $payload->ReturnAuth;
			if($action == "getLabel")
				$returnAuth = json_decode($payload->ReturnAuth);
		}
		
		/************* Agent Authentication ***************/
		// Set up and call AgentAuthenticator
		require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
		$account = AgentAuthenticator::authenticateSessionID($sid);
	}
	use RightNow\Connect\v1_3 as RNCPHP;
	initConnectAPI();

	try
	{
		// Pull custom configuration setting to determine mode, username/password, endpoints
		$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
		$bwsConfig = (json_decode($bwsConfig->Value, true));
		
		// BUS$SysIntegrationLog debugger
		$sysLogConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_DEBUG_SYSINTEGRATION);
		$debug = $sysLogConfig->Value;
		
		if($action != "labelRegen")
		{
			// Call proper function based on the passed in action		
			switch($action)
			{
				case "getLabel":
					$response = sendFedExLabel($bwsConfig, $debug, $returnAuth->order->ReturnNumber, $returnAuth);
					break;
				default:
					$response = submitRA($bwsConfig, $debug, $returnAuth);
					break;
			}
				
			// Capture and parse SOAP XML response, convert to JSON and return
			$json = json_encode($response, JSON_PRETTY_PRINT);

			header('Content-Type: application/json');
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: GET, POST');
			header("Access-Control-Allow-Headers: X-Requested-With");
			
			echo $json;
			return $json;
		}
	}
	
	catch(Exception $err)
	{
		return $err;
	}
	
	// Call SubmitReturnAuthorization and pass in given return
	function submitRA($bwsConfig, $debug, $returnAuth)
	{	
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['RETURN_SERVICE']['USERNAME'], $bwsConfig['RETURN_SERVICE']['PASSWORD']);
		
		// Create Billing Address XML
		$soapBilling = '<a:BillingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">';
		
		// Billing Address Name Logic
		$name = urldecode($returnAuth->order->BillingAddress->Name);
		$namePieces = explode(' ', $name);
		$nameCount = count($namePieces) - 1;
		
		$lastName = $namePieces[$nameCount];
		unset($namePieces[$nameCount]);
		$firstName = implode(' ', $namePieces);
		
		if(isset($returnAuth->order->BillingAddress->Address1))
			$soapBilling = $soapBilling . '<b:Address1>' . $returnAuth->order->BillingAddress->Address1 . '</b:Address1>';
		if(isset($returnAuth->order->BillingAddress->Address2))
			$soapBilling = $soapBilling . '<b:Address2>' . $returnAuth->order->BillingAddress->Address2 . '</b:Address2>';
		if(isset($returnAuth->order->BillingAddress->City))
			$soapBilling = $soapBilling . '<b:City>' . $returnAuth->order->BillingAddress->City . '</b:City>';
		if(isset($returnAuth->order->BillingAddress->CountryCode))
			$soapBilling = $soapBilling . '<b:CountryCode>' . $returnAuth->order->BillingAddress->CountryCode . '</b:CountryCode>';
		if(isset($returnAuth->order->BillingAddress->PostalCode))
			$soapBilling = $soapBilling . '<b:PostalCode>' . $returnAuth->order->BillingAddress->PostalCode . '</b:PostalCode>';
		if(isset($returnAuth->order->BillingAddress->StateCode))
			$soapBilling = $soapBilling . '<b:StateCode>' . $returnAuth->order->BillingAddress->StateCode . '</b:StateCode>';
		if(isset($returnAuth->order->BillingAddress->EmailAddress))
			$soapBilling = $soapBilling . '<b:EmailAddress>' . $returnAuth->order->BillingAddress->EmailAddress . '</b:EmailAddress>';

		$soapBilling = $soapBilling . '<b:FirstName>' . $firstName . '</b:FirstName>';
		$soapBilling = $soapBilling . '<b:LastName>' . $lastName . '</b:LastName>';
		$soapBilling = $soapBilling . '<b:Name>' . $name . '</b:Name>';
		
		if(isset($returnAuth->order->BillingAddress->Phone))
			$soapBilling = $soapBilling . '<b:Phone>' . $returnAuth->order->BillingAddress->Phone . '</b:Phone>';
				
		$soapBilling = $soapBilling . '</a:BillingAddress>';
		
		// Create Shipping Address XML
		$soapShipping = '<a:ShippingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">';
		
		// Shipping Address Name Logic
		$name = urldecode($returnAuth->order->ShippingAddress->Name);
		$namePieces = explode(' ', $name);
		$nameCount = count($namePieces) - 1;
		
		$lastName = $namePieces[$nameCount];
		unset($namePieces[$nameCount]);
		$firstName = implode(' ', $namePieces);
		
		if(isset($returnAuth->order->ShippingAddress->Address1))
			$soapShipping = $soapShipping . '<b:Address1>' . $returnAuth->order->ShippingAddress->Address1 . '</b:Address1>';
		if(isset($returnAuth->order->ShippingAddress->Address2))
			$soapShipping = $soapShipping . '<b:Address2>' . $returnAuth->order->ShippingAddress->Address2 . '</b:Address2>';
		if(isset($returnAuth->order->ShippingAddress->City))
			$soapShipping = $soapShipping . '<b:City>' . $returnAuth->order->ShippingAddress->City . '</b:City>';
		if(isset($returnAuth->order->ShippingAddress->CountryCode))
			$soapShipping = $soapShipping . '<b:CountryCode>' . $returnAuth->order->ShippingAddress->CountryCode . '</b:CountryCode>';
		if(isset($returnAuth->order->ShippingAddress->PostalCode))
			$soapShipping = $soapShipping . '<b:PostalCode>' . $returnAuth->order->ShippingAddress->PostalCode . '</b:PostalCode>';
		if(isset($returnAuth->order->ShippingAddress->StateCode))
			$soapShipping = $soapShipping . '<b:StateCode>' . $returnAuth->order->ShippingAddress->StateCode . '</b:StateCode>';
		if(isset($returnAuth->order->ShippingAddress->EmailAddress))
			$soapShipping = $soapShipping . '<b:EmailAddress>' . $returnAuth->order->ShippingAddress->EmailAddress . '</b:EmailAddress>';

		$soapShipping = $soapShipping . '<b:FirstName>' . $firstName . '</b:FirstName>';
		$soapShipping = $soapShipping . '<b:LastName>' . $lastName . '</b:LastName>';
		$soapShipping = $soapShipping . '<b:Name>' . $name . '</b:Name>';
		
		if(isset($returnAuth->order->ShippingAddress->Phone))
			$soapShipping = $soapShipping . '<b:Phone>' . $returnAuth->order->ShippingAddress->Phone . '</b:Phone>';
		
		$soapShipping = $soapShipping .	'</a:ShippingAddress>';
		
		// Create LineItems XML
		$soapLineItems = '<a:LineItems>';
		foreach($returnAuth->order->LineItems as $lineItem)
		{		
			$soapLineItems = $soapLineItems . '<a:LineItem>';
			
			if(isset($lineItem->AdjustedTotal))
				$soapLineItems = $soapLineItems . '<a:AdjustedTotal>' . $lineItem->AdjustedTotal . '</a:AdjustedTotal>';
			if(isset($lineItem->Description))
				$soapLineItems = $soapLineItems . '<a:Description>' . $lineItem->Description . '</a:Description>';
			if(isset($lineItem->EstArrivalDate))
				$soapLineItems = $soapLineItems . '<a:EstArrivalDate>' . $lineItem->EstArrivalDate . '</a:EstArrivalDate>';
			if(isset($lineItem->EstShipDate))
				$soapLineItems = $soapLineItems . '<a:EstShipDate>' . $lineItem->EstShipDate . '</a:EstShipDate>';
			if(isset($lineItem->FreeShipping))
				$soapLineItems = $soapLineItems . '<a:FreeShipping>' . $lineItem->FreeShipping . '</a:FreeShipping>';
			if(isset($lineItem->ListPrice))
				$soapLineItems = $soapLineItems . '<a:ListPrice>' . $lineItem->ListPrice . '</a:ListPrice>';
			if(isset($lineItem->Qty))
				$soapLineItems = $soapLineItems . '<a:Qty>' . $lineItem->Qty . '</a:Qty>';
			
			// IF LineItem is to be returned, set LineItem ReturnLineType XML
			if($lineItem->Qty == -1)
				$soapLineItems = $soapLineItems . '<a:ReturnLineType>' . $lineItem->ReturnLineType . '</a:ReturnLineType>';

			if(isset($lineItem->ReturnReason))
				$soapLineItems = $soapLineItems . '<a:ReturnReason>' . $lineItem->ReturnReason . '</a:ReturnReason>';
			if(isset($lineItem->Sku))
				$soapLineItems = $soapLineItems . '<a:Sku>' . trim($lineItem->Sku) . '</a:Sku>';
			if(isset($lineItem->SerialNo))
				$soapLineItems = $soapLineItems . '<a:SerialNo>' . $lineItem->SerialNo . '</a:SerialNo>';
			if(isset($lineItem->Total))
				$soapLineItems = $soapLineItems . '<a:Total>' . $lineItem->Total . '</a:Total>';
			
			$soapLineItems = $soapLineItems . '</a:LineItem>';
		}
		$soapLineItems = $soapLineItems . '</a:LineItems>';
		
		// Channel XML
		if(isset($returnAuth->order->Channel))
			$soapChannel = '<a:Channel>' . $returnAuth->order->Channel . '</a:Channel>';
		
		// OnyxIndividualID XML
		if(isset($returnAuth->order->OnyxIndividualId))
			$soapOnyxID = '<a:OnyxIndividualID>' . $returnAuth->order->OnyxIndividualId . '</a:OnyxIndividualID>';
		
		// OrderDate XML
		if(isset($returnAuth->order->OrderDate))
			$soapOrderDate = '<a:OrderDate>' . $returnAuth->order->OrderDate . '</a:OrderDate>';
		
		// OrderTotal XML
		if(isset($returnAuth->order->OrderTotal))
			$soapOrderTotal = '<a:OrderTotal>' . $returnAuth->order->OrderTotal . '</a:OrderTotal>';
		
		// ShipAmount XML
		if(isset($returnAuth->order->ShipAmount))
			$soapShipAmount = '<a:ShipAmount>' . $returnAuth->order->ShipAmount . '</a:ShipAmount>';
		
		// Create Ship Code XML
		if(isset($returnAuth->order->ShipCode))
			$soapShipCode = '<a:ShipCode>' . $returnAuth->order->ShipCode . '</a:ShipCode>';
		
		// SubTotal XML
		if(isset($returnAuth->order->SubTotal))
			$soapSubTotal = '<a:SubTotal>' . $returnAuth->order->SubTotal . '</a:SubTotal>';
		
		// CRMPurchaseOrderID XML
		if(isset($returnAuth->order->CRMPurchaseOrderID))
			$soapCRMPurchaseOrderID = '<a:CRMPurchaseOrderID>' . $returnAuth->order->CRMPurchaseOrderID . '</a:CRMPurchaseOrderID>';
		
		// CRMIncidentID XML
		if(isset($returnAuth->order->IncidentId))
			$soapCRMIncidentID = '<a:CRMIncidentID>' . $returnAuth->order->IncidentId . '</a:CRMIncidentID>';
		
		// ReturnWarehouse XML
		if(isset($returnAuth->order->ReturnWarehouse))
			$soapReturnWarehouseXML = '<a:ReturnWarehouse>' . $returnAuth->order->ReturnWarehouse . '</a:ReturnWarehouse>';
		
		// ActionCode XML
		if(isset($returnAuth->order->ActionCode))
			$soapActionCodeXML = '<a:ActionCode>' . $returnAuth->order->ActionCode . '</a:ActionCode>';
		
		// Create XML body
		$soapXML = $soapHeader .
			'<s:Body>
				<SubmitReturnAuthorization xmlns="http://tempuri.org/">
					<order xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:Errors i:nil="true"/>
						<a:InvoiceNo i:nil="true"/>
						' . $soapLineItems . '
						<a:OperatingUnit i:nil="true"/>
						<a:OracleAccountNo i:nil="true"/>
						<a:OrderAdjustment i:nil="true"/>
						' . $soapOrderDate . '
						<a:OrderNumber i:nil="true"/>
						<a:OrderStatus i:nil="true"/>
						' . $soapOrderTotal . '
						<a:RISAction i:nil="true"/>
						<a:ResponseCode i:nil="true"/>
						<a:ResponseMessage i:nil="true"/>
						<a:Scor i:nil="true"/>
						' . $soapShipAmount . '
						' . $soapShipCode . '
						<a:ShipType>None</a:ShipType>
						<a:Status>0</a:Status>
						' . $soapSubTotal . '
						<a:TrackingInfo i:nil="true"/>
						<a:Tran i:nil="true"/>
						' . $soapActionCodeXML . '
						' . $soapBilling . '
						<a:CRMConsumerID i:nil="true"/>
						' . $soapCRMIncidentID . '
						' . $soapCRMPurchaseOrderID . '
						' . $soapChannel . '
						<a:Comments>Return submitted via OSvC</a:Comments>
						' . $soapOnyxID . '
						' . $soapReturnWarehouseXML . '
						' . $soapShipping . '
					</order>
				</SubmitReturnAuthorization>
			</s:Body>
		</s:Envelope>';
		
		// Check the ReturnAuth was passed correctly, try and capture Blank RA Submission before we process the RA
		// $returnAuth = null;
		if(count($returnAuth->order->LineItems) < 1)
		{
			// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
			if($debug == 1)
			{
				$info = array(
					'ExtSystem' => "SubmitReturnAuthorization",
					'RequestMsg' => $soapXML,
					'ResponseMsg' => "Not submitted to BWS, blank RA submission detected!",
					'Description' => "Not submitted to BWS, blank RA submission detected!",
					'Error'	=> "Blank RA Submission Detected",
					'IncidentID' => $returnAuth->order->IncidentId,
					'ContactID' => $returnAuth->order->ContactId,
					'OrderHeaderID' => $returnAuth->order->OrderHeaderId
				);
				
				logSysIntegration($info);
			}
			
			$response = array(
				'ResponseMessage' => "Blank RA Submission",
				'Count' => $testCount,
				'NewCount' => $newCount
			);
			return $response;
		}
		
		// Call Bissell webservices and capture response
		$soapXML = str_replace("&", "&amp;", $soapXML);
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['RETURN_SERVICE']['WSDL'], $bwsConfig['RETURN_SERVICE']['SUBMIT_RETURN'], $soapXML);

		// Log and return any cURL errors 
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$response = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'SubmitReturnAuthorization',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError,
				'IncidentID' => $returnAuth->order->IncidentId,
				'ContactID' => $returnAuth->order->ContactId,
				'OrderHeaderID' => $returnAuth->order->OrderHeaderId
			);
			logSysIntegration($info);
			return $response;
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
			$responseCode = "SOAP Failure";
			$responseMessage = "ErrCode: " . $xmlTree->xtree->Envelope->Body->Fault->faultcode->_['value'] . " ErrMsg: " . $xmlTree->xtree->Envelope->Body->Fault->faultstring->_['value'];
		}
		
		// Parse the RA Submission response
		else
		{
			// Drill-down into top level response code/message section of the XML body
			$xmlResponse = $xmlTree->xtree->Envelope->Body->SubmitReturnAuthorizationResponse->SubmitReturnAuthorizationResult;
			
			if(isset($xmlResponse->ResponseCode))
				$responseCode = $xmlResponse->ResponseCode->_['value'];
			if(isset($xmlResponse->ResponseMessage))
				$responseMessage = $xmlResponse->ResponseMessage->_['value'];
			
			// Grab any failure codes if they are returned
			if(isset($xmlResponse->Order->ReturnAuthorization->Errors->ErrorOfstringstring))
			{
				// Loop through the top and second level error code/message pairs
				foreach($xmlResponse->Order->ReturnAuthorization->Errors->ErrorOfstringstring as $key => $error)
				{
					// Top Level Error code/message
					if($key == 0)
						$orderRespMessage = $error->Name->_['value'] . ": " . $error->Value->_['value'];
					// Second Level Error code/message
					else if($key == 1)
						$orderRespCode = $error->Name->_['value'] . ": " . $error->Value->_['value'];
				}
			}
			
			// Drill-down into Return Authorization response
			$xmlResponse = $xmlResponse->Order->ReturnAuthorization;
			
			if(isset($xmlResponse->OrderNumber))
				$returnNumber = $xmlResponse->OrderNumber->_['value'];
			
			// Write return authorization submission log to BUS$TransactionLog
			$comment = 'RANumber#: ' . $returnNumber . ' Code: ' . $responseCode;
			
			$info = array(
				'LogAction' => 'SubmitReturnAuthorization',
				'Comment' => $comment,
				'IncidentID' => $returnAuth->order->IncidentId,
				'ContactID' => $returnAuth->order->ContactId,
				'OrderHeaderID' => $returnAuth->order->OrderHeaderId
			);
			
			logTransaction($info);
		}
		
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{
			$info = array(
				'ExtSystem' => "SubmitReturnAuthorization",
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => $responseMessage,
				'Error'	=> $responseCode,
				'IncidentID' => $returnAuth->order->IncidentId,
				'ContactID' => $returnAuth->order->ContactId,
				'OrderHeaderID' => $returnAuth->order->OrderHeaderId
			);
			
			logSysIntegration($info);
		}
		
		// Email FedEx return shipment label to customer if ReturnMethod is set to Email and only if a ReturnNumber is returned from Bissell
		if(strpos($returnAuth->order->ReturnMethod, 'Email') === 0 && isset($returnNumber) && $returnNumber > 0)
			$trackNumber = sendFedExLabel($bwsConfig, $debug, $returnNumber, $returnAuth);
			
		// Return BWS response along with FedEx tracking number, tracking number set only if RA return type is EMAIL
		// if($responseCode === "Failure")
			// $responseMessage = "Success, no return authorization number available.";
		
		if($responseCode === "Success" && isset($returnNumber) && $returnNumber > 0){
			if(strpos($returnAuth->order->ReturnMethod, 'Email') === 0){
				if($trackNumber['FedExResponse'] === 'SUCCESS' || $trackNumber['FedExResponse'] === 'WARNING' || $trackNumber['FedExResponse'] === 'NOTE'){
					$raNumber = $returnNumber;
					$raStatus = 'Submitted';
					$raLockTrans = true;
					$raTracking = $trackNumber['TrackingNumber'];
				} else {
					$raNumber = $returnNumber;
					$raStatus = 'FedEx Error';
					$raLockTrans = true;
					$raTracking = null;
				}
			} else {
				$raNumber = $returnNumber;
				$raStatus = 'Submitted';
				$raLockTrans = true;
				$raTracking = null;
			}
		} else {
			$responseMessage = "Success, no return authorization number available.";
			$raNumber = null;
			$raStatus = 'Queued';
			$raLockTrans = true;
			$raTracking = null;
		}
		
		$raInfo = array(
			'RANumber' => $raNumber,
			'Status' => $raStatus,
			'LockTrans' => $raLockTrans,
			'TrackingNumber' => $raTracking,
			'OrderHeaderID'	=> $returnAuth->order->OrderHeaderId
		);
		saveRAHeaderFields($raInfo);
		
		$response = array(
			'ResponseCode' => $responseCode,
			'ResponseMessage' => $responseMessage,
			'ReturnNumber' => $returnNumber,
			'FedEx' => $trackNumber
		);
		
		return $response;
	}

	// Take the passed in Return Authorization and call FedEx to generate/email a return shipment label to the customer
	function sendFedExLabel($bwsConfig, $debug, $returnNumber, $returnAuth)
	{
		// Pull custom configuration setting to determine mode, username/password, endpoints
		$fedexConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_FEDEX_INTEGRATION");
		$fedexConfig = (json_decode($fedexConfig->Value, true));
		
		// Set label expiration date to 30 days out = 60 seconds * 60 minutes * 24 hours * 30 days
		$expire = gmdate('Y-m-d', time() + (60 * 60 * 24 * 30));
		
		//Decoding the Name to remove '%20' between first name and last name, which is sent to FedEx	
		$name = urldecode($returnAuth->order->ShippingAddress->Name);	
		$namePieces = explode(' ', $name);
		
		// Grab either Dev or Prod credentials
		if($fedexConfig['DEV_ENABLED'] == 0)
		{
			if($returnAuth->order->ActionCode == "RPR"){
				$key = $fedexConfig['FEDEX']['PROD']['REPAIRRA']['KEY'];
				$password = $fedexConfig['FEDEX']['PROD']['REPAIRRA']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['PROD']['REPAIRRA']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['PROD']['REPAIRRA']['METER_NUMBER'];
			} elseif($returnAuth->order->ShippingAddress->CountryCode == "CA"){
				$key = $fedexConfig['FEDEX']['PROD']['CA']['KEY'];
				$password = $fedexConfig['FEDEX']['PROD']['CA']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['PROD']['CA']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['PROD']['CA']['METER_NUMBER'];
			} else {
				$key = $fedexConfig['FEDEX']['PROD']['US']['KEY'];
				$password = $fedexConfig['FEDEX']['PROD']['US']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['PROD']['US']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['PROD']['US']['METER_NUMBER'];
			}
		}
		else
		{
			if($returnAuth->order->ActionCode == "RPR"){
				$key = $fedexConfig['FEDEX']['DEV']['REPAIRRA']['KEY'];
				$password = $fedexConfig['FEDEX']['DEV']['REPAIRRA']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['DEV']['REPAIRRA']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['DEV']['REPAIRRA']['METER_NUMBER'];
			} elseif($returnAuth->order->ShippingAddress->CountryCode == "CA"){
				$key = $fedexConfig['FEDEX']['DEV']['CA']['KEY'];
				$password = $fedexConfig['FEDEX']['DEV']['CA']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['DEV']['CA']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['DEV']['CA']['METER_NUMBER'];
			} else {
				$key = $fedexConfig['FEDEX']['DEV']['US']['KEY'];
				$password = $fedexConfig['FEDEX']['DEV']['US']['PASSWORD'];
				$accountNum = $fedexConfig['FEDEX']['DEV']['US']['ACCOUNT_NUMBER'];
				$meterNum = $fedexConfig['FEDEX']['DEV']['US']['METER_NUMBER'];
			}
		}
		
		$soapXML = 
		'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
			<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
				<CreatePendingShipmentRequest xmlns="http://fedex.com/ws/openship/v13">
					<WebAuthenticationDetail>
						<UserCredential>
							<Key>' . $key . '</Key>
							<Password>' . $password . '</Password>
						</UserCredential>
					</WebAuthenticationDetail>
					<ClientDetail>
						<AccountNumber>' . $accountNum . '</AccountNumber>
						<MeterNumber>' . $meterNum . '</MeterNumber>
					</ClientDetail>
					<TransactionDetail>
						<CustomerTransactionId>Bissell RA #' . $returnNumber . '</CustomerTransactionId>
					</TransactionDetail>
					<Version>
						<ServiceId>ship</ServiceId>
						<Major>13</Major>
						<Intermediate>0</Intermediate>
						<Minor>0</Minor>
					</Version>
					<Actions>TRANSFER</Actions>
					<RequestedShipment>
						<ShipTimestamp>' . $returnAuth->order->OrderDate . '</ShipTimestamp>
						<DropoffType>REGULAR_PICKUP</DropoffType>
						<ServiceType>FEDEX_GROUND</ServiceType>
						<PackagingType>YOUR_PACKAGING</PackagingType>
						<TotalWeight>
							<Units>LB</Units>
							<Value>50.0</Value>
						</TotalWeight>
						<Shipper>
							<Contact>
								<PersonName>' . $name . '</PersonName>
								<PhoneNumber>' . $returnAuth->order->ShippingAddress->Phone . '</PhoneNumber>
								<EMailAddress>' . $returnAuth->order->ShippingAddress->EmailAddress . '</EMailAddress>
							</Contact>
							<Address>
								<StreetLines>' . $returnAuth->order->ShippingAddress->Address1 . '</StreetLines>
								<StreetLines>' . $returnAuth->order->ShippingAddress->Address2 . '</StreetLines>
								<City>' . $returnAuth->order->ShippingAddress->City . '</City>
								<StateOrProvinceCode>' . $returnAuth->order->ShippingAddress->StateCode . '</StateOrProvinceCode>
								<PostalCode>' . $returnAuth->order->ShippingAddress->PostalCode . '</PostalCode>
								<CountryCode>' . $returnAuth->order->ShippingAddress->CountryCode . '</CountryCode>
							</Address>
						</Shipper>
						<Recipient>
							<Contact>	
								<PersonName>RA: ' . $returnNumber . '</PersonName>	
								<CompanyName>' . $returnAuth->order->ReturnWarehouseDetails->Company . '</CompanyName>	
								<PhoneNumber>' . $returnAuth->order->ReturnWarehouseDetails->Phone . '</PhoneNumber>	
								<EMailAddress></EMailAddress>	
							</Contact>
							<Address>
								<StreetLines>' . $returnAuth->order->ReturnWarehouseDetails->Address1 . '</StreetLines>
								<StreetLines>' . $returnAuth->order->ReturnWarehouseDetails->Address2 . '</StreetLines>
								<City>' . $returnAuth->order->ReturnWarehouseDetails->City . '</City>
								<StateOrProvinceCode>' . $returnAuth->order->ReturnWarehouseDetails->State . '</StateOrProvinceCode>
								<PostalCode>' . $returnAuth->order->ReturnWarehouseDetails->PostalCode . '</PostalCode>
								<CountryCode>' . $returnAuth->order->ReturnWarehouseDetails->Country . '</CountryCode>
							</Address>
						</Recipient>
						<ShippingChargesPayment>
							<PaymentType>SENDER</PaymentType>
							<Payor>
								<ResponsibleParty>
									<AccountNumber>' . $accountNum . '</AccountNumber>
									<Contact>
										<PersonName>' . $returnAuth->order->ReturnWarehouseDetails->Company . '</PersonName>
										<PhoneNumber>' . $returnAuth->order->ReturnWarehouseDetails->Phone . '</PhoneNumber>
										<EMailAddress></EMailAddress>
									</Contact>
									<Address>
										<StreetLines>' . $returnAuth->order->ReturnWarehouseDetails->Address1 . '</StreetLines>
										<StreetLines>' . $returnAuth->order->ReturnWarehouseDetails->Address2 . '</StreetLines>
										<City>' . $returnAuth->order->ReturnWarehouseDetails->City . '</City>
										<StateOrProvinceCode>' . $returnAuth->order->ReturnWarehouseDetails->State . '</StateOrProvinceCode>
										<PostalCode>' . $returnAuth->order->ReturnWarehouseDetails->PostalCode . '</PostalCode>
										<CountryCode>' . $returnAuth->order->ReturnWarehouseDetails->Country . '</CountryCode>
									</Address>
								</ResponsibleParty>
							</Payor>
						</ShippingChargesPayment>
						<SpecialServicesRequested>
							<SpecialServiceTypes>RETURN_SHIPMENT</SpecialServiceTypes>
							<SpecialServiceTypes>PENDING_SHIPMENT</SpecialServiceTypes>
							<EventNotificationDetail>
								<PersonalMessage>Bissell RA #' . $returnNumber . '</PersonalMessage>
								<EventNotifications>
									<Role>RECIPIENT</Role>
									<NotificationDetail>
										<NotificationType>EMAIL</NotificationType>
										<EmailDetail>
											<EmailAddress>' . $returnAuth->order->ShippingAddress->EmailAddress . '</EmailAddress>
										</EmailDetail>
									</NotificationDetail>
									<FormatSpecification>
										<Type>HTML</Type>
									</FormatSpecification>
								</EventNotifications>
							</EventNotificationDetail>
							<ReturnShipmentDetail>
								<ReturnType>PENDING</ReturnType>
								<Rma>
									<Reason>Return for Refund</Reason>
								</Rma>
								<ReturnEMailDetail>
									<MerchantPhoneNumber>' . $returnAuth->order->ReturnWarehouseDetails->Phone . '</MerchantPhoneNumber>
								</ReturnEMailDetail>
							</ReturnShipmentDetail>
							<PendingShipmentDetail>
								<Type>EMAIL</Type>
								<ExpirationDate>' . $expire . '</ExpirationDate>
								<EmailLabelDetail>
									<Message>Return Shipment Label</Message>
									<Recipients>
										<EmailAddress>' . $returnAuth->order->ShippingAddress->EmailAddress . '</EmailAddress>
										<Role>SHIPMENT_COMPLETOR</Role>
									</Recipients>
								</EmailLabelDetail>
							</PendingShipmentDetail>
						</SpecialServicesRequested>
						<LabelSpecification>
							<LabelFormatType>COMMON2D</LabelFormatType>
							<ImageType>PDF</ImageType>
							<LabelStockType>PAPER_8.5X11_TOP_HALF_LABEL</LabelStockType>
							<LabelPrintingOrientation>TOP_EDGE_OF_TEXT_FIRST</LabelPrintingOrientation>
						</LabelSpecification>
						<PackageCount>1</PackageCount>
						<RequestedPackageLineItems>
							<SequenceNumber>1</SequenceNumber>
							<Weight>
								<Units>LB</Units>
								<Value>10</Value>
							</Weight>
							<ItemDescription>Product Return</ItemDescription>
							<CustomerReferences>
								<CustomerReferenceType>RMA_ASSOCIATION</CustomerReferenceType>
								<Value>' . $returnNumber . '</Value>
							</CustomerReferences>
						</RequestedPackageLineItems>
					</RequestedShipment>
				</CreatePendingShipmentRequest>
			</s:Body>
		</s:Envelope>';
		
		$fedexResponse = curlExec($fedexConfig, $fedexConfig['FEDEX']['WSDL'], $fedexConfig['FEDEX']['FUNCTION'], $soapXML);
		
		// Log any cURL errors
		if(isset($fedexResponse['CurlErrorCode']))
		{
			$orderResponse = array(
				'ResponseCode' => $fedexResponse['CurlErrorCode'],
				'ResponseMessage' => $fedexResponse['CurlErrorMsg']
			);
				
			$curlError = 'CURL Error Code: ' . $fedexResponse['CurlErrorCode'] . " - " . $fedexResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'FedExCreatePendingShipmentRequest',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError,
				'IncidentID' => $returnAuth->order->IncidentId,
				'ContactID' => $returnAuth->order->ContactId,
				'OrderHeaderID' => $returnAuth->order->OrderHeaderId
			);
			
			logSysIntegration($info);
			return $response;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
		array(
			'xmlRaw' => $fedexResponse,
			'stripNamespaces' => true
		));
		
		// Check for any fault codes first
		$xmlTree = $xmlTree->xtree->Envelope->Body;
		if(isset($xmlTree->Fault))
		{
			$error = $xmlTree->Fault->faultcode->_['value'];
			$description = $xmlTree->Fault->detail->desc->_['value'];
		}
	
		else
		{	
			// Capture success/failure response from FedEx
			$xmlTree = $xmlTree->CreatePendingShipmentReply;
			$error = $xmlTree->HighestSeverity->_['value'];
			
			$description = $xmlTree->Notifications->Message->_['value'];
			
			if(!isset($description))
				$description = $xmlTree->Notifications[0]->Message->_['value'];
			
			// Capture return label URL and tracking number
			$xmlTree = $xmlTree->CompletedShipmentDetail;
			if(isset($xmlTree->AccessDetail->AccessorDetails->EmailLabelUrl->_['value']))
				$description = $xmlTree->AccessDetail->AccessorDetails->EmailLabelUrl->_['value'];
			if(isset($xmlTree->CompletedPackageDetails->TrackingIds->TrackingNumber->_['value']))
				$trackNumber = $xmlTree->CompletedPackageDetails->TrackingIds->TrackingNumber->_['value'];
		}
	
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{
			$info = array(
				'ExtSystem' => "FedExCreatePendingShipmentRequest",
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $fedexResponse,
				'Description' => $description,
				'Error'	=> $error,
				'IncidentID' => $returnAuth->order->IncidentId,
				'ContactID' => $returnAuth->order->ContactId,
				'OrderHeaderID' => $returnAuth->order->OrderHeaderId
			);
			logSysIntegration($info);
		}
		
		$response = array(
			'TrackingNumber' => $trackNumber,
			'FedExResponse' => $error,
			'FedExMessage' => $description
		);
		
		//return $trackNumber;
		return $response;
	}
?>