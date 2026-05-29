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
	$response = getOrderResponse();
	$json = json_encode($response, JSON_PRETTY_PRINT);
	
	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	echo $json;
	return $json;
	
	// Call SubmitOnyxOrder and pass in given order
	function getOrderResponse()
	{
		// Pull custom configuration setting to determine mode, username/password, endpoints, debugging
		try
		{
			// BWS Integration configuration
			$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
			$bwsConfig = json_decode($bwsConfig->Value, true);
			
			// BUS$SysIntegrationLog debugger
			$sysLogConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_DEBUG_SYSINTEGRATION);
			$debug = $sysLogConfig->Value;
		}
		
		catch(Exception $err)
		{
			return $err;
		}
		
		// Capture JSON order data and parse to associative array
		$order = json_decode(file_get_contents('php://input'));
		
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['SUBMIT_ORDER']['BRAND'][$order->order->SubmitBrandConfig]['USERNAME'], $bwsConfig['SUBMIT_ORDER']['BRAND'][$order->order->SubmitBrandConfig]['PASSWORD']);
		
		// Create Billing Address XML
		$soapBilling = '<a:BillingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">';
		
		// Billing Address Name Logic
		$name = urldecode($order->order->BillingAddress->Name);
		$namePieces = explode(' ', $name);
		$nameCount = count($namePieces) - 1;
		
		$lastName = $namePieces[$nameCount];
		unset($namePieces[$nameCount]);
		$firstName = implode(' ', $namePieces);
		
		if(isset($order->order->BillingAddress->Address1))
			$soapBilling = $soapBilling . '<b:Address1>' . $order->order->BillingAddress->Address1 . '</b:Address1>';
		if(isset($order->order->BillingAddress->Address2))
			$soapBilling = $soapBilling . '<b:Address2>' . $order->order->BillingAddress->Address2 . '</b:Address2>';
		if(isset($order->order->BillingAddress->City))
			$soapBilling = $soapBilling . '<b:City>' . $order->order->BillingAddress->City . '</b:City>';
		if(isset($order->order->BillingAddress->CountryCode))
			$soapBilling = $soapBilling . '<b:CountryCode>' . $order->order->BillingAddress->CountryCode . '</b:CountryCode>';
		if(isset($order->order->BillingAddress->PostalCode))
			$soapBilling = $soapBilling . '<b:PostalCode>' . $order->order->BillingAddress->PostalCode . '</b:PostalCode>';
		if(isset($order->order->BillingAddress->StateCode))
			$soapBilling = $soapBilling . '<b:StateCode>' . $order->order->BillingAddress->StateCode . '</b:StateCode>';
		if(isset($order->order->BillingAddress->EmailAddress))
			$soapBilling = $soapBilling . '<b:EmailAddress>' . $order->order->BillingAddress->EmailAddress . '</b:EmailAddress>';
		$soapBilling = $soapBilling . '<b:FirstName>' . $firstName . '</b:FirstName>';
		$soapBilling = $soapBilling . '<b:LastName>' . $lastName . '</b:LastName>';
		$soapBilling = $soapBilling . '<b:Name>' . $name . '</b:Name>';
		
		if(isset($order->order->BillingAddress->Phone))
			$soapBilling = $soapBilling . '<b:Phone>' . $order->order->BillingAddress->Phone . '</b:Phone>';
				
		$soapBilling = $soapBilling . '</a:BillingAddress>';
		
		// Create Shipping Address XML
		$soapShipping = '<a:ShippingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">';
		
		// Shipping Address Name Logic
		$name = urldecode($order->order->ShippingAddress->Name);
		$namePieces = explode(' ', $name);
		$nameCount = count($namePieces) - 1;
		
		$lastName = $namePieces[$nameCount];
		unset($namePieces[$nameCount]);
		$firstName = implode(' ', $namePieces);
		
		
		if(isset($order->order->ShippingAddress->Address1))
			$soapShipping = $soapShipping . '<b:Address1>' . $order->order->ShippingAddress->Address1 . '</b:Address1>';
		if(isset($order->order->ShippingAddress->Address2))
			$soapShipping = $soapShipping . '<b:Address2>' . $order->order->ShippingAddress->Address2 . '</b:Address2>';
		if(isset($order->order->ShippingAddress->City))
			$soapShipping = $soapShipping . '<b:City>' . $order->order->ShippingAddress->City . '</b:City>';
		if(isset($order->order->ShippingAddress->CountryCode))
			$soapShipping = $soapShipping . '<b:CountryCode>' . $order->order->ShippingAddress->CountryCode . '</b:CountryCode>';
		if(isset($order->order->ShippingAddress->PostalCode))
			$soapShipping = $soapShipping . '<b:PostalCode>' . $order->order->ShippingAddress->PostalCode . '</b:PostalCode>';
		if(isset($order->order->ShippingAddress->StateCode))
			$soapShipping = $soapShipping . '<b:StateCode>' . $order->order->ShippingAddress->StateCode . '</b:StateCode>';
		if(isset($order->order->ShippingAddress->EmailAddress))
			$soapShipping = $soapShipping . '<b:EmailAddress>' . $order->order->ShippingAddress->EmailAddress . '</b:EmailAddress>';

		$soapShipping = $soapShipping . '<b:FirstName>' . $firstName . '</b:FirstName>';
		$soapShipping = $soapShipping . '<b:LastName>' . $lastName . '</b:LastName>';
		$soapShipping = $soapShipping . '<b:Name>' . $name . '</b:Name>';
		
		if(isset($order->order->ShippingAddress->Phone))
			$soapShipping = $soapShipping . '<b:Phone>' . $order->order->ShippingAddress->Phone . '</b:Phone>';
		
		$soapShipping = $soapShipping .	'</a:ShippingAddress>';
		
		// Array to map LineItem information to product registration
		$prodRegItems = array ();
		
		// Create LineItems XML
		$soapLineItems = '<a:LineItems>';
		foreach($order->order->LineItems as $lineItem)
		{	
			$soapLineItems = $soapLineItems . '<a:LineItem>';
			
			if(isset($lineItem->AdjustedTotal))
				$soapLineItems = $soapLineItems . '<a:AdjustedTotal>' . $lineItem->AdjustedTotal . '</a:AdjustedTotal>';
			if(isset($lineItem->Description))
				$soapLineItems = $soapLineItems . '<a:Description>' . htmlspecialchars($lineItem->Description) . '</a:Description>';
			if(isset($lineItem->EstArrivalDate))
				$soapLineItems = $soapLineItems . '<a:EstArrivalDate>' . $lineItem->EstArrivalDate . '</a:EstArrivalDate>';
			if(isset($lineItem->EstShipDate))
				$soapLineItems = $soapLineItems . '<a:EstShipDate>' . $lineItem->EstShipDate . '</a:EstShipDate>';
			if(isset($lineItem->FreeShipping) && $lineItem->FreeShipping === true)
				$soapLineItems = $soapLineItems . '<a:FreeShipping>' . $lineItem->FreeShipping . '</a:FreeShipping>';
			if(isset($lineItem->ListPrice))
				$soapLineItems = $soapLineItems . '<a:ListPrice>' . $lineItem->ListPrice . '</a:ListPrice>';
			if(isset($lineItem->Qty))
				$soapLineItems = $soapLineItems . '<a:Qty>' . $lineItem->Qty . '</a:Qty>';
			if(isset($lineItem->ReturnReason))
				$soapLineItems = $soapLineItems . '<a:ReturnReason>' . $lineItem->ReturnReason . '</a:ReturnReason>';
			if(isset($lineItem->Sku))
				$soapLineItems = $soapLineItems . '<a:Sku>' . trim($lineItem->Sku) . '</a:Sku>';
			if(isset($lineItem->SerialNo))
				$soapLineItems = $soapLineItems . '<a:SerialNo>' . $lineItem->SerialNo . '</a:SerialNo>';
			if(isset($lineItem->Total))
				$soapLineItems = $soapLineItems . '<a:Total>' . $lineItem->Total . '</a:Total>';
			
			$soapLineItems = $soapLineItems . '</a:LineItem>';
			
			// Array to hold individual LineItem information for product registration
			$prodRegItem = array(
				'PartNum' => trim($lineItem->Sku),
				'Qty' => $lineItem->Qty
			);
			array_push($prodRegItems, $prodRegItem);
		}
		
		$soapLineItems = $soapLineItems . '</a:LineItems>';
		
		// Create CreditCard Payment XML
		if(isset($order->order->Payment))
		{
			$soapCCPayment = '<a:Payment>';

			if(isset($order->order->Payment->AccountNumber))
				$soapCCPayment = $soapCCPayment . '<a:AccountNumber>' . $order->order->Payment->AccountNumber . '</a:AccountNumber>';
			if(isset($order->order->Payment->Amount))
				$soapCCPayment = $soapCCPayment . '<a:Amount>' . $order->order->Payment->Amount . '</a:Amount>';
			if(isset($order->order->Payment->TransactionDate))
				$soapCCPayment = $soapCCPayment . '<a:TransactionDate>' . $order->order->Payment->TransactionDate . '</a:TransactionDate>';
			if(isset($order->order->Payment->CVC))
				$soapCCPayment = $soapCCPayment . '<a:CVC>' . $order->order->Payment->CVC . '</a:CVC>';
			if(isset($order->order->Payment->CardCode))
				$soapCCPayment = $soapCCPayment . '<a:CardCode>' . $order->order->Payment->CardCode . '</a:CardCode>';
			if(isset($order->order->Payment->ExpMonth))
				$soapCCPayment = $soapCCPayment . '<a:ExpMonth>' . $order->order->Payment->ExpMonth . '</a:ExpMonth>';
			if(isset($order->order->Payment->ExpYear))
				$soapCCPayment = $soapCCPayment . '<a:ExpYear>' . $order->order->Payment->ExpYear . '</a:ExpYear>';
			if(isset($order->order->Payment->Name))
				$soapCCPayment = $soapCCPayment . '<a:Name>' . $order->order->Payment->Name . '</a:Name>';
					
			$soapCCPayment = $soapCCPayment . '<a:NumberofPayments i:nil="true"/><a:ProviderTransactionID i:nil="true"/></a:Payment>';
			
			$paymentType = "CreditCard";
		}
		else
			$soapCCPayment = '<a:Payment i:nil="true"/>';

		// Create CheckPayment XML
		if(isset($order->order->CheckPayment))
		{
			$soapCheckPayment = '<a:CheckPayment>';
			
			if(isset($order->order->CheckPayment->Amount))
				$soapCheckPayment = $soapCheckPayment . '<a:Amount>' . $order->order->CheckPayment->Amount . '</a:Amount>';
			if(isset($order->order->CheckPayment->TransactionDate))
				$soapCheckPayment = $soapCheckPayment . '<a:TransactionDate>' . $order->order->CheckPayment->TransactionDate . '</a:TransactionDate>';
			if(isset($order->order->CheckPayment->CheckNumber))
				$soapCheckPayment = $soapCheckPayment . '<a:CheckNumber>' . $order->order->CheckPayment->CheckNumber . '</a:CheckNumber>';
			
			$soapCheckPayment = $soapCheckPayment . '<a:Electronic>false</a:Electronic>';
			$soapCheckPayment = $soapCheckPayment . '</a:CheckPayment>';
			
			$paymentType = "Check";
		}
		else
			$soapCheckPayment = '<a:CheckPayment i:nil="true"/>';
		
		//Create LineItems XML
		if(isset($order->order->SalesTax))
			$soapSalesTax = '<a:SalesTax><a:Amount>' . $order->order->SalesTax . '</a:Amount></a:SalesTax>';
		
		// Create Onyx ID XML
		if(isset($order->order->OnyxIndividualId))
			$soapOnxyID = '<a:OnyxIndividualId>' . $order->order->OnyxIndividualId . '</a:OnyxIndividualId>';
		
		// Create Ship Amount XML
		if(isset($order->order->ShipAmount))
			$soapShipAmount = '<a:ShipAmount>' . $order->order->ShipAmount . '</a:ShipAmount>';
		
		// Create Ship Code XML
		if(isset($order->order->ShipCode))
			$soapShipCode = '<a:ShipCode>' . $order->order->ShipCode . '</a:ShipCode>';
		
		// Create SubTotal XML
		if(isset($order->order->SubTotal))
			$soapSubTotal = '<a:SubTotal>' . $order->order->SubTotal . '</a:SubTotal>';
		
		// Create OrderTotal XML
		if(isset($order->order->OrderTotal))
			$soapOrderTotal = '<a:OrderTotal>' . $order->order->OrderTotal . '</a:OrderTotal>';
		
		// Create OrderDate XML
		if(isset($order->order->OrderDate))
			$soapOrderDate = '<a:OrderDate>' . $order->order->OrderDate . '</a:OrderDate>';
		
		// Create Channel XML
		if(isset($order->order->Channel))
			$soapChannel = '<a:Channel>' . $order->order->Channel . '</a:Channel>';
		else
			$soapChannel = '<a:Channel i:nil="true"/>';
		
		// Create CRMPurchaseOrderID XML
		if(isset($order->order->CRMPurchaseOrderID))
			$soapOrderID = '<a:CRMPurchaseOrderID>' . $order->order->CRMPurchaseOrderID . '</a:CRMPurchaseOrderID>';
		
		if(isset($order->order->IncidentId))
			$soapIncidentID = '<a:CRMIncidentID>' . $order->order->IncidentId . '</a:CRMIncidentID>';
			
		$soapXML = $soapHeader . 
			'<s:Body>
				<SubmitOnyxOrder xmlns="http://tempuri.org/">
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
						' . $soapSalesTax . '
						<a:Scor i:nil="true"/>
						' . $soapShipAmount . '
						' . $soapShipCode . '
						<a:ShipType>None</a:ShipType>
						<a:Status>0</a:Status>
						' . $soapSubTotal . '
						<a:TrackingInfo i:nil="true"/>
						<a:Tran i:nil="true"/>
						' . $soapBilling . '
						<a:CashPayment i:nil="true"/>
						<a:CouponGiven i:nil="true"/>
						<a:EbayOrderId i:nil="true"/>
						<a:HeaderBaggage i:nil="true"/>
						' . $soapCCPayment . '
						<a:SessionID i:nil="true"/>
						' . $soapShipping . '
						<a:UserIP i:nil="true"/>
						<a:WebSourceOrderID i:nil="true"/>
						' . $soapIncidentID . '
						' . $soapOrderID . '
						' . $soapChannel . '
						' . $soapCheckPayment . '
						<a:Comments i:nil="true"/>
						' . $soapOnxyID . '
					</order>
				</SubmitOnyxOrder>
			</s:Body>
		</s:Envelope>';
		
		// Call Bissell webservices and capture response
		$soapXML = str_replace("&", "&amp;", $soapXML);
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['SUBMIT_ORDER']['WSDL'], $bwsConfig['SUBMIT_ORDER']['FUNCTION'], $soapXML);
		
		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$orderResponse = array(
				'OrderCode' => $bissellResponse['CurlErrorCode'],
				'OrderMessage' => $bissellResponse['CurlErrorMsg']
			);
				
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'SubmitOnyxOrder',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError,
				'IncidentID' => $order->order->IncidentId,
				'ContactID'	=> $order->order->ContactId,
				'OrderHeaderID'	=> $order->order->OrderHeaderId
			);
			logSysIntegration($info);
			return $orderResponse;
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
			$orderRespCode = 'SOAP Failure';
			$orderRespMessage = "ErrCode: " . $xmlTree->xtree->Envelope->Body->Fault->faultcode->_['value'] . " ErrMsg: ". $xmlTree->xtree->Envelope->Body->Fault->faultstring->_['value'];
			
			$orderResponse = array(
				'OrderCode' => $orderRespCode,
				'OrderMessage' => $orderRespMessage
			);
		}
		
		// Drill-down into ValidateAddressResult section of the XML body, we don't care about the rest of the response
		$xml = $xmlTree->xtree->Envelope->Body->SubmitOnyxOrderResponse->SubmitOnyxOrderResult;
				
		// Grab the response code and message for the entire order
		if(isset($xml->ResponseCode))
			$orderRespCode = $xml->ResponseCode->_['value'];
		if(isset($xml->ResponseMessage))
			$orderRespMessage = $xml->ResponseMessage->_['value'];
		
		// Grab any failure codes if they are returned
		if(isset($xml->Order->OnyxWebOrder->Errors->ErrorOfstringstring))
		{
			// Loop through the top and second level error code/message pairs
			foreach($xml->Order->OnyxWebOrder->Errors->ErrorOfstringstring as $key => $error)
			{
				// Top Level Error code/message
				if($key == 0)
					$errDesc = $error->Name->_['value'] . ": " . $error->Value->_['value'];
				// Second Level Error code/message
				else if($key == 1)
					$errCode = $error->Name->_['value'] . ": " . $error->Value->_['value'];
			}
		}
		
		// If the credit card payment authorization fails, return failure message to agent
		if($orderRespCode === "PaymentAuthorizationFailed")
		{	
			$agentMessage = "Credit Card payment authorization failed. Please confirm credit card information with customer.";
			$orderResponse = array(
				'OrderCode' => $orderRespCode,
				'OrderMessage' => $orderRespMessage,
				'AgentMessage' => $agentMessage
			);
			
			if(isset($errDesc))
				$orderRespMessage = $errDesc;
			if(isset($errCode))
				$orderRespCode = $errCode;
			
			// Set log variables for PaymentAuthorizationFailed response
			$comment = 'PaymentAuthorizationFailed - CreditCard: ' . $order->order->Payment->CardCode;
			
			$orderNumber = null;
			$orderStatus = 'Credit Card Declined';
			$orderLockTrans = false;
		}
		
		// If the order submission fails for reasons other than invalid credit card payment, return success message to agent
		// Bissell should attempt to re-process order on their end as part of the failed order process
		else if($orderRespCode === "Failure")
		{	
			$agentMessage = "Success, no order number available.";
			$orderResponse = array(
				'OrderCode' => $orderRespCode,
				'OrderMessage' => $orderRespMessage,
				'AgentMessage' => $agentMessage
			);
			
			if(isset($errDesc))
				$orderRespMessage = $errDesc;
			if(isset($errCode))
				$orderRespCode = $errCode;
			
			// Set log variables for failed order process response
			$comment = 'No order number available';
			
			$orderNumber = null;
			$orderStatus = 'Queued';
			$orderLockTrans = true;
		}
		
		/** The BWS order is successfully processed in the ERP order system, return the following
		 *	Order #
		 *	Expected Delivery Dates
		 *	Line Item Tracking #
		 */
		else if($orderRespCode === "Success")
		{
			// Build up PartNumber strings to query against SalesProduct table for Asset (Product) Registration
			$partNums = "('";
			
			// First, handle overall Order information
			$xml = $xml->Order->OnyxWebOrder;
			if(isset($xml->OrderDate))
				$orderDate = $xml->OrderDate->_['value'];
			if(isset($xml->OrderNumber))
				$orderNum = $xml->OrderNumber->_['value'];
			if(isset($xml->OrderTotal))
				$orderTotal = $xml->OrderTotal->_['value'];
			if(isset($xml->Tran))
				$transID = $xml->Tran->_['value'];
			
			// Next, handle individual Line Item shipping and tracking information
			$items = array();
			$xmlLineItem = $xml->LineItems->LineItem;
			
			// IF there are multiple line items, we need to treat LineItem as an array and iterate through
			if(count($xmlLineItem) > 1)
			{
				foreach($xmlLineItem as $lineItem)
				{	
					if(isset($lineItem->EstArrivalDate))
						$estArrivalDate = $lineItem->EstArrivalDate->_['value'];
					if(isset($lineItem->EstShipDate))
						$estShipDate = $lineItem->EstShipDate->_['value'];
					if(isset($lineItem->Sku))
						$sku = $lineItem->Sku->_['value'];
					if(isset($lineItem->TrackingNo->_['value']))
						$trackNum = $lineItem->TrackingNo->_['value'];
					else
						$trackNum = "N/A";
					
					$item = array(
						'EstArrivalDate' => $estArrivalDate,
						'EstShipDate' => $estShipDate,
						'Sku' => $sku,
						'TrackingNumber' => $trackNum
					);
					array_push($items, $item);
					
					$partNums = $partNums . $sku . "','";
				}
				
				$partNums = substr($partNums, 0, strlen($partNums) - 2);
				$partNums = $partNums . ")";
			}

			// ELSE a single line item is returned for an Order, treat LineItem as a single object
			else
			{
				if(isset($xmlLineItem->EstArrivalDate))
					$estArrivalDate = $xmlLineItem->EstArrivalDate->_['value'];
				if(isset($xmlLineItem->EstShipDate))
					$estShipDate = $xmlLineItem->EstShipDate->_['value'];
				if(isset($xmlLineItem->Sku))
					$sku = $xmlLineItem->Sku->_['value'];
				if(isset($xmlLineItem->TrackingNo->_['value']))
					$trackNum = $xmlLineItem->TrackingNo->_['value'];
				else
					$trackNum = "N/A";
				
				$item = array(
					'EstArrivalDate' => $estArrivalDate,
					'EstShipDate' => $estShipDate,
					'Sku' => $sku,
					'TrackingNumber' => $trackNum
				);
				array_push($items, $item);
				
				$partNums = $partNums . $sku . "')";
			}
			
			$orderResponse = array(
				'OrderCode' => $orderRespCode,
				'OrderMessage' => $orderRespMessage,
				'OrderDate' => $orderDate,
				'OrderNumber' => $orderNum,
				'OrderTotal' => $orderTotal,
				'TransactionID' => $transID,
				'LineItems' => $items
			);
			
			// Set log variables for successful order process response
			$comment = 'OrderNumber# ' . $orderNum . ' OrderTotal: ' . $orderTotal . ' PaymentType: ' . $paymentType;
			
			// Return all SalesProducts for the submitted LineItems
			$query = "SELECT Name, ID, PartNumber
				FROM SalesProduct 
				WHERE SalesProduct.PartNumber IN " . $partNums;
			
			$salesProds = RNCPHP\ROQL::query($query)->next();
			
			// Fetch contact record
			$contactRecord = RNCPHP\Contact::fetch($order->order->ContactId);
			
			// Register all LineItems that are actual 'Finished' products
			while($salesProd = $salesProds->next())
			{
				// Set boolean flag to send out Product Survey if it is not set
				if($contactRecord->CustomFields->c->sendproductsurvey == 0)
					$contactRecord->CustomFields->c->sendproductsurvey = 1;
				
				// Set Product for Product Survey if it is not set
				if(!isset($contactRecord->CustomFields->c->surveyed_product))
					$contactRecord->CustomFields->c->surveyed_product = $salesProd['Name'];
				
				$registeredProduct = new RNCPHP\Asset();
				$registeredProduct->Name = $salesProd['Name'];
				$registeredProduct->Product = RNCPHP\SalesProduct::fetch($salesProd['ID']);
				$registeredProduct->Contact = $contactRecord;
				$registeredProduct->CustomFields->CO->Incident = RNCPHP\Incident::fetch($order->order->IncidentId);
				$registeredProduct->PurchasedDate = time();
				
				// 0 - NO : 1 - YES
				$registeredProduct->CustomFields->BI->purchase_date_verified = 1;
				$registeredProduct->Description = $salesProd['Name'];
				
				// 26 - Active : 27 - Retired : 28 - Unregistered
				$registeredProduct->StatusWithType->Status = 26;
				
				//$registeredProduct->Price->Currency = 1;
				//$registeredProduct->Price->Value = '1.00';
				$registeredProduct->CustomFields->BI->store_puchased = 97;
				$registeredProduct->CustomFields->BI->source = 1;
				
				foreach($prodRegItems as $item)
					if($salesProd['PartNumber'] == $item['PartNum'])
						$registeredProduct->CustomFields->BI->qty = $item['Qty'];
					
				//$registeredProduct->CustomFields->BI->warranty_exp_date = time();
				//$registeredProduct->CustomFields->BI->replace_for_product = ;
				$registeredProduct->save();
			}
			$contactRecord->save();
			
			$orderNumber = $xml->OrderNumber->_['value'];
			$orderStatus = 'Submitted';
			$orderLockTrans = true;
		}
		
		$info = array(
			'LogAction' => 'SubmitOrder',
			'Comment' => $comment,
			'IncidentID' => $order->order->IncidentId,
			'ContactID'	=> $order->order->ContactId,
			'OrderHeaderID'	=> $order->order->OrderHeaderId
		);
		logTransaction($info);
		
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{	
			$info = array(
				'ExtSystem'	=> 'SubmitOrder',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => $orderRespMessage,
				'Error'	=> $orderRespCode,
				'IncidentID' => $order->order->IncidentId,
				'ContactID'	=> $order->order->ContactId,
				'OrderHeaderID'	=> $order->order->OrderHeaderId
			);
			logSysIntegration($info);
		}
		
		$orderInfo = array(
			'OrderNumber' => $orderNumber,
			'Status' => $orderStatus,
			'LockTrans' => $orderLockTrans,
			'OrderHeaderID'	=> $order->order->OrderHeaderId
		);
		saveOrderHeaderFields($orderInfo);
		
		return $orderResponse;
	}
?>