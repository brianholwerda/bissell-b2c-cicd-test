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
	}
	// BUI PAYLOAD
	else
	{
		$json = json_decode(file_get_contents('php://input'));
		$sid = $json->Session;
	}

	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	if(isset($_SERVER['HTTP_OSVC_AUTHENTICATION']))
	{
		$basicPieces = explode('Basic ', $_SERVER['HTTP_OSVC_AUTHENTICATION']);
		$rawSecurity = base64_decode($basicPieces[1]);
		$security = explode(':', $rawSecurity);
		$user = $security[0];
		$pass = $security[1];
		
		$account = AgentAuthenticator::authenticateCredentials($user, $pass);
	}
	else
		$account = AgentAuthenticator::authenticateSessionID($sid);
	initConnectAPI();
	use RightNow\Connect\v1_3 as RNCPHP;

	// Pull custom configuration setting to determine mode, username/password, endpoints
	try
	{
		$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
		$bwsConfig = (json_decode($bwsConfig->Value, true));

		// BUS$SysIntegrationLog debugger
		$sysLogConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_DEBUG_SYSINTEGRATION);
		$debug = $sysLogConfig->Value;
	}

	catch(Exception $e)
	{
		return $e;
	}

	// .NET PAYLOAD
	if(isset($_GET['action']) || isset($_POST['action']))
	{
		if(isset($_POST['action']))
			$action = $_POST['action'];
		else
			$action = $_GET['action'];
	}
	// BUI PAYLOAD
	else
	{
		$json = json_decode(file_get_contents('php://input'));
		$action = $json->Action;
	}

	switch($action)
	{
		case "getorderhistory":
			$response = getOrderHistory($bwsConfig, $debug);
			break;
		case "getlineitemhistory":
			$response = getLineItemHistory($bwsConfig, $debug);
			break;
		default:
			$response = array(
				'Code' => "400",
				'Message' => "Invalid action"
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

	// Call GetConsumerTransHistory and return high-level order history for UI
	function getOrderHistory($bwsConfig, $debug)
	{
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['GET_ORDER']['USERNAME'], $bwsConfig['GET_ORDER']['PASSWORD']);

		// Grab POST Consumer ID
		if(isset($_POST['ConsumerID']))
			$consumerID = $_POST['ConsumerID'];
		
		// .NET PAYLOAD
		if(isset($_GET['ConsumerID']) || isset($_POST['ConsumerID']))
		{
			if(isset($_POST['ConsumerID']))
				$consumerID = $_POST['ConsumerID'];
		}
		// BUI PAYLOAD
		else
		{
			$json = json_decode(file_get_contents('php://input'));
			$consumerID = $json->ConsumerID;
		}

		$soapXML = $soapHeader .
			'<s:Body>
				<GetConsumerTransHistory xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:CustomerID>' . $consumerID . '</a:CustomerID>
						<a:Email i:nil="true"/>
					</p>
				</GetConsumerTransHistory>
			</s:Body>
		</s:Envelope>';

		$bissellResponse = curlExec($bwsConfig, $bwsConfig['GET_ORDER']['WSDL'], $bwsConfig['GET_ORDER']['ORDER_HISTORY'], $soapXML);

		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$orderHistory = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);

			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'GetConsumerTransHistory',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
				//'ContactID'	=> $consumerID
			);
			logSysIntegration($info);
			return $orderHistory;
		}

		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));


		// Drill-down into GetConsumerOrderHistory section of the XML body
		$bissellResponse = $xmlTree->xtree->Envelope->Body->GetConsumerTransHistoryResponse->GetConsumerTransHistoryResult;

		if(isset($bissellResponse->ResponseCode))
			$responseCode = $bissellResponse->ResponseCode->_['value'];
		if(isset($bissellResponse->ResponseMessage))
			$responseMessage = $bissellResponse->ResponseCode->_['value'];

		// Navigate through each returned Order
		$consumerWebOrder = array();
		$xmlOrder = $bissellResponse->OrderList->ConsumerTransHistory;

		// IF there are orders, we need to treat ConsuemrTransHistory as an array and iterate through
		if(count($xmlOrder) > 1)
		{
			foreach($xmlOrder as $order)
			{
				// Grab order-specific fields
				if(isset($order->Channel))
					$orderChannel = $order->Channel->_['value'];
				if(isset($order->EbayOrderId))
					$ebayOrderID = $order->EbayOrderId->_['value'];
                if(isset($order->eBayOrderNum))
					$ebayOrderNum = $order->eBayOrderNum->_['value'];
				if(isset($order->OrderDate))
					$orderDate = $order->OrderDate->_['value'];
				if(isset($order->OrderNumber))
					$orderNum = $order->OrderNumber->_['value'];
				if(isset($order->OrderStatus))
					$orderStat = $order->OrderStatus->_['value'];
				if(isset($order->OrderTotal))
					$orderTotal = $order->OrderTotal->_['value'];
				if(isset($order->PartsList))
					$orderParts = $order->PartsList->_['value'];
				if(isset($order->PaymentType))
					$orderPaymentType = $order->PaymentType->_['value'];
				if(isset($order->isSubscription))
					$isSubscription = $order->isSubscription->_['value'];

				// Append Order/LineItems/Payments array along with additional order history information
				$thisOrder = array(
					'EBayOrderId' => $ebayOrderID,
                    'EBayOrderNum' => $ebayOrderNum,
					'OrderDate' => $orderDate,
					'OrderNumber' => $orderNum,
					'OrderStatus' => $orderStat,
					'OrderType' => $orderChannel,
					'OrderTotal' => $orderTotal,
					'PartNumbers' => $orderParts,
					'PaymentType' => $orderPaymentType,
					'IsSubscription' => $isSubscription
				);
				array_push($consumerWebOrder, $thisOrder);
			}
		}

		// ELSE only a single order is returned, handle the XML response accordingly
		else
		{
			// Grab order-specific fields
			if(isset($xmlOrder->Channel))
				$orderChannel = $xmlOrder->Channel->_['value'];
			if(isset($xmlOrder->EbayOrderId))
				$ebayOrderID = $xmlOrder->EbayOrderId->_['value'];
            if(isset($xmlOrder->eBayOrderNum))
				$ebayOrderNum = $xmlOrder->eBayOrderNum->_['value'];
			if(isset($xmlOrder->OrderDate))
				$orderDate = $xmlOrder->OrderDate->_['value'];
			if(isset($xmlOrder->OrderNumber))
				$orderNum = $xmlOrder->OrderNumber->_['value'];
			if(isset($xmlOrder->OrderStatus))
				$orderStat = $xmlOrder->OrderStatus->_['value'];
			if(isset($xmlOrder->OrderTotal))
				$orderTotal = $xmlOrder->OrderTotal->_['value'];
			if(isset($xmlOrder->PartsList))
				$orderParts = $xmlOrder->PartsList->_['value'];
			if(isset($xmlOrder->PaymentType))
				$orderPaymentType = $xmlOrder->PaymentType->_['value'];
			if(isset($xmlOrder->isSubscription))
				$isSubscription = $xmlOrder->isSubscription->_['value'];

			// Append Order/LineItems/Payments array along with additional order history information
			$thisOrder = array(
				'EBayOrderId' => $ebayOrderID,
                'EBayOrderNum' => $ebayOrderNum,
				'OrderDate' => $orderDate,
				'OrderNumber' => $orderNum,
				'OrderStatus' => $orderStat,
				'OrderType' => $orderChannel,
				'OrderTotal' => $orderTotal,
				'PartNumbers' => $orderParts,
				'PaymentType' => $orderPaymentType,
				'IsSubscription' => $isSubscription
			);
			array_push($consumerWebOrder, $thisOrder);
		}

		// Package entire Order history with response codes
		$orderHistory = array(
			'Order' => $consumerWebOrder,
			'ResponseCode' => $responseCode,
			'ResponseMessage' => $responseMessage
		);
		return $orderHistory;
	}

	// Call GetConsumerWebOrder and return LineItem details for given order
	function getLineItemHistory($bwsConfig, $debug)
	{
		// .NET PAYLOAD
		if(isset($_GET['ConsumerID']) || isset($_POST['ConsumerID']))
		{
			if(isset($_POST['ConsumerID']))
				$consumerID = $_POST['ConsumerID'];
		}
		// BUI PAYLOAD
		else
		{
			$json = json_decode(file_get_contents('php://input'));
			$consumerID = $json->ConsumerID;
		}
		
		// .NET PAYLOAD
		if(isset($_GET['OrderNumber']) || isset($_POST['OrderNumber']))
		{
			if(isset($_POST['OrderNumber']))
				$orderNum = $_POST['OrderNumber'];
		}
		// BUI PAYLOAD
		else
		{
			$json = json_decode(file_get_contents('php://input'));
			$orderNum = $json->OrderNumber;
		}
		

		// Web Service call requires OrderNumber and ConsumerID. If either field are not passed, return an error
		//if(!isset($orderNum) || !isset($consumerID))
		if(!isset($orderNum))
		{
			$lineItemHistory = array(
				'Code'		=> "400",
				'Message'	=> "Missing field",
				'OrderNumber'	=> $orderNum,
				'ConsumerID'	=> $consumerID
			);
			return $lineItemHistory;
		}

        $calcVarsArray = [];//pass variables to the tax calculation function

		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['GET_ORDER']['USERNAME'], $bwsConfig['GET_ORDER']['PASSWORD']);

		$soapXML = $soapHeader .
			'<s:Body>
				<GetOnyxWebOrder xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:OrderNumber>' . $orderNum . '</a:OrderNumber>
						<a:Source i:nil="true"/>
						<a:CustomerID>' . $consumerID . '</a:CustomerID>
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
			$lineItemHistory = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);

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
			return $lineItemHistory;
		}

		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
		));
		
		// Drill-down into GetConsumerOrderHistory section of the XML body
		$bissellResponse = $xmlTree->xtree->Envelope->Body->GetOnyxWebOrderResponse->GetOnyxWebOrderResult;

		if(isset($bissellResponse->ResponseCode))
			$responseCode = $bissellResponse->ResponseCode->_['value'];
		if(isset($bissellResponse->ResponseMessage))
			$responseMessage = $bissellResponse->ResponseCode->_['value'];

		$bissellResponse = $bissellResponse->Order->OnyxWebOrder;

		// Capture OracleAccountNo response
		if(isset($bissellResponse->OracleAccountNo))
			$oracleAccountNo = $bissellResponse->OracleAccountNo->_['value'];

		// Capture ShipAmount response
		$shipAmount = 0;
		if(count($bissellResponse->OrderAdjustment->OrderAdjustment > 1))
			foreach($bissellResponse->OrderAdjustment->OrderAdjustment as $adj)
				if($adj->AdjustmentCode->_['value'] == "9858")
					$shipAmount = $adj->Amount->_['value'];

		else
			if($bissellResponse->OrderAdjustment->OrderAdjustment->AdjustmentCode->_['value'] == "9858")
				$shipAmount = $bissellResponse->OrderAdjustment->OrderAdjustment->Amount->_['value'];

		// Capture ShipCode response
		if(isset($bissellResponse->ShipCode))
			$shipCode = $bissellResponse->ShipCode->_['value'];

		// Return entire SalesTax object
		if(isset($bissellResponse->SalesTax->Amount))
			$amt = $bissellResponse->SalesTax->Amount->_['value'];
		if(isset($bissellResponse->SalesTax->Description))
			$desc = $bissellResponse->SalesTax->Description->_['value'];
		if(isset($bissellResponse->SalesTax->Percent))
			$percent = $bissellResponse->SalesTax->Percent->_['value'];
		if(isset($bissellResponse->SalesTax->SecondaryAmount))
			$secAmt = $bissellResponse->SalesTax->SecondaryAmount->_['value'];
		if(isset($bissellResponse->SalesTax->SecondaryDescription))
			$secDesc = $bissellResponse->SalesTax->SecondaryDescription->_['value'];
		if(isset($bissellResponse->SalesTax->SecondaryPercent))
			$secPercent = $bissellResponse->SalesTax->SecondaryPercent->_['value'];

		$salesTax = array (
			'Amount' => $amt,
			'Description' => $desc,
			'Percent' => $percent,
			'SecondaryAmount' => $secAmt,
			'SecondaryDescription' => $secDesc,
			'SecondaryPercent' => $secPercent
		);

		// Capture BillingAddress Response
		if(isset($bissellResponse->BillingAddress->Name))
			$billName = $bissellResponse->BillingAddress->Name->_['value'];
		if(isset($bissellResponse->BillingAddress->EmailAddress))
			$billEmail = $bissellResponse->BillingAddress->EmailAddress->_['value'];
		if(isset($bissellResponse->BillingAddress->Phone))
			$billPhone = $bissellResponse->BillingAddress->Phone->_['value'];
		if(isset($bissellResponse->BillingAddress->Address1))
			$billStreet1 = $bissellResponse->BillingAddress->Address1->_['value'];
		if(isset($bissellResponse->BillingAddress->Address2))
			$billStreet2 = $bissellResponse->BillingAddress->Address2->_['value'];
		if(isset($bissellResponse->BillingAddress->City))
			$billCity = $bissellResponse->BillingAddress->City->_['value'];
		if(isset($bissellResponse->BillingAddress->StateCode))
			$billState = $bissellResponse->BillingAddress->StateCode->_['value'];
        if(strlen($billState) < 1 && isset($bissellResponse->BillingAddress->Province))
			$billState = $bissellResponse->BillingAddress->Province->_['value'];
		if(isset($bissellResponse->BillingAddress->CountryCode))
			$billCountry = $bissellResponse->BillingAddress->CountryCode->_['value'];
		if(isset($bissellResponse->BillingAddress->PostalCode))
			$billPostal = $bissellResponse->BillingAddress->PostalCode->_['value'];

		$billTo = array (
			'Name' => $billName,
			'Email' => $billEmail,
			'Phone' => $billPhone,
			'Street1' => $billStreet1,
			'Street2' => $billStreet2,
			'City' => $billCity,
			'State' => $billState,
			'Country' => $billCountry,
			'Postal' => $billPostal
		);

		// Change null fields to ''
		foreach($billTo as $key => $field)
			if($field == null)
				$billTo[$key] = '';

		// Capture ShippingAddress Response
		if(isset($bissellResponse->ShippingAddress->Name))
			$shipName = $bissellResponse->ShippingAddress->Name->_['value'];
		if(isset($bissellResponse->ShipingAddress->EmailAddress))
			$shipEmail = $bissellResponse->ShipingAddress->EmailAddress->_['value'];
		if(isset($bissellResponse->ShippingAddress->Phone))
			$shipPhone = $bissellResponse->ShippingAddress->Phone->_['value'];
		if(isset($bissellResponse->ShippingAddress->Address1))
			$shipStreet1 = $bissellResponse->ShippingAddress->Address1->_['value'];
		if(isset($bissellResponse->ShippingAddress->Address2))
			$shipStreet2 = $bissellResponse->ShippingAddress->Address2->_['value'];
		if(isset($bissellResponse->ShippingAddress->City))
			$shipCity = $bissellResponse->ShippingAddress->City->_['value'];
		if(isset($bissellResponse->ShippingAddress->StateCode))
			$shipState = $bissellResponse->ShippingAddress->StateCode->_['value'];
        if(strlen($shipState) < 1 && isset($bissellResponse->ShippingAddress->Province))
			    $shipState = $bissellResponse->ShippingAddress->Province->_['value'];
		if(isset($bissellResponse->ShippingAddress->CountryCode))
			$shipCountry = $bissellResponse->ShippingAddress->CountryCode->_['value'];
		if(isset($bissellResponse->ShippingAddress->PostalCode))
			$shipPostal = $bissellResponse->ShippingAddress->PostalCode->_['value'];


		$shipTo = array (
			'Name' => $shipName,
			'Email' => $shipEmail,
			'Phone' => $shipPhone,
			'Street1' => $shipStreet1,
			'Street2' => $shipStreet2,
			'City' => $shipCity,
			'State' => $shipState,
			'Country' => $shipCountry,
			'Postal' => $shipPostal
		);

		// Change null fields to ''
		foreach($shipTo as $key => $field)
			if($field == null)
				$shipTo[$key] = '';

			
		// Capture Order Status and Total
		if(isset($bissellResponse->OrderStatus->_['value']))
			$orderStatus = $bissellResponse->OrderStatus->_['value'];
		if(isset($bissellResponse->OrderTotal->_['value']))
			$orderTotal = $bissellResponse->OrderTotal->_['value'];
		
		
		// Navigate through each Line Item in the Order
		$lineItems = array();
		$xmlLineItem = $bissellResponse->LineItems->LineItem;
        $subTotal = 0;
		// IF there are multiple line items, we need to treat LineItem as an array and iterate through
		if(count($xmlLineItem) > 1)
		{
			// Append each LineItem to the Order response array
			foreach($xmlLineItem as $lineItem)
			{
				$onlineReturns = array();
				$xmlOnlineReturn = $lineItem->NarvarOnlineReturn->ConsumerOnlineReturn;
				
				if(count($xmlOnlineReturn) > 1){
					foreach($xmlOnlineReturn as $onlineReturn){
					$thisOnlineReturn = array(
						'OnlineReturnDate' => $onlineReturn->OnlineReturnDate->_['value'],
						'OnlineReturnDeliveryDate' => $onlineReturn->OnlineReturnDeliveryDate->_['value'],
						'OnlineReturnQty' => $onlineReturn->OnlineReturnQty->_['value'],
						'OnlineReturnReason' => $onlineReturn->OnlineReturnReason->_['value'],
						'OnlineReturnShipDate' => $onlineReturn->OnlineReturnShipDate->_['value'],
						'OnlineReturnTrackingNo' => $onlineReturn->OnlineReturnTrackingNo->_['value']
					);
					
					array_push($onlineReturns, $thisOnlineReturn);
				}
				} else {
					$thisOnlineReturn = array(
						'OnlineReturnDate' => $xmlOnlineReturn->OnlineReturnDate->_['value'],
						'OnlineReturnDeliveryDate' => $xmlOnlineReturn->OnlineReturnDeliveryDate->_['value'],
						'OnlineReturnQty' => $xmlOnlineReturn->OnlineReturnQty->_['value'],
						'OnlineReturnReason' => $xmlOnlineReturn->OnlineReturnReason->_['value'],
						'OnlineReturnShipDate' => $xmlOnlineReturn->OnlineReturnShipDate->_['value'],
						'OnlineReturnTrackingNo' => $xmlOnlineReturn->OnlineReturnTrackingNo->_['value']
					);
					
					array_push($onlineReturns, $thisOnlineReturn);
				}
				
				$thisLineItem = array(
					'CouponCode' => $lineItem->CouponCode->_['value'],
					'LineDiscounts' => $lineItem->LineDiscounts->_['value'],
					'PartDesc' => str_replace("%26", "&", $lineItem->Description->_['value']),
					'PartNo' => $lineItem->Sku->_['value'],
					'Price' => $lineItem->ListPrice->_['value'],
					'Qty' => $lineItem->Qty->_['value'],
					'ReasonCode' => $lineItem->ReturnReason->_['value'],
					'Status' => $lineItem->Status->_['value'],
					'CouponSKU' => $lineItem->Sku->_['value'],
					'Total' => $lineItem->Total->_['value'],
					'TotalAdjusted' => $lineItem->AdjustedTotal->_['value'],
					'TotalAfterDiscount' => $lineItem->TotalAfterDiscount->_['value'],
					'TrackingNo' => $lineItem->TrackingNo->_['value'],
					'TrackingURL' => str_replace("%26", "&", $lineItem->TrackingURL->_['value']),
					'Warehouse' => $lineItem->Warehouse->_['value'],
					'OnlineReturnArray' => $onlineReturns,
					'OnlineReturnTrackingNo' => $onlineReturns[0]['OnlineReturnTrackingNo'],
					'ExpDeliveryDateFrom' => $lineItem->ExpDeliveryDateFrom->_['value'],
					'ExpDeliveryDateTo' => $lineItem->ExpDeliveryDateTo->_['value'],
					'IsSubscription' => $lineItem->isSubscription->_['value'],
					'LineId' => $lineItem->LineId->_['value']
				);

                $subTotal += ((int)$thisLineItem['Qty'] * $thisLineItem['TotalAfterDiscount']);
				array_push($lineItems, $thisLineItem);
			}
		}

		// ELSE a single line item is returned for an Order, treat LineItem as a single object
		else
		{
			$xmlOnlineReturn = $xmlLineItem->NarvarOnlineReturn->ConsumerOnlineReturn;
			$onlineReturns = array();
			
			if(count($xmlOnlineReturn) > 1){
				foreach($xmlOnlineReturn as $onlineReturn){
				$thisOnlineReturn = array(
					'OnlineReturnDate' => $onlineReturn->OnlineReturnDate->_['value'],
					'OnlineReturnDeliveryDate' => $onlineReturn->OnlineReturnDeliveryDate->_['value'],
					'OnlineReturnQty' => $onlineReturn->OnlineReturnQty->_['value'],
					'OnlineReturnReason' => $onlineReturn->OnlineReturnReason->_['value'],
					'OnlineReturnShipDate' => $onlineReturn->OnlineReturnShipDate->_['value'],
					'OnlineReturnTrackingNo' => $onlineReturn->OnlineReturnTrackingNo->_['value']
				);
				
				array_push($onlineReturns, $thisOnlineReturn);
			}
			} else {
				$thisOnlineReturn = array(
					'OnlineReturnDate' => $xmlOnlineReturn->OnlineReturnDate->_['value'],
					'OnlineReturnDeliveryDate' => $xmlOnlineReturn->OnlineReturnDeliveryDate->_['value'],
					'OnlineReturnQty' => $xmlOnlineReturn->OnlineReturnQty->_['value'],
					'OnlineReturnReason' => $xmlOnlineReturn->OnlineReturnReason->_['value'],
					'OnlineReturnShipDate' => $xmlOnlineReturn->OnlineReturnShipDate->_['value'],
					'OnlineReturnTrackingNo' => $xmlOnlineReturn->OnlineReturnTrackingNo->_['value']
				);
				
				array_push($onlineReturns, $thisOnlineReturn);
			}
			
			// Append LineItem to the Order response array
			$thisLineItem = array(
				'CouponCode' => $xmlLineItem->CouponCode->_['value'],
				'LineDiscounts' => $xmlLineItem->LineDiscounts->_['value'],
				'PartDesc' => str_replace("%26", "&", $xmlLineItem->Description->_['value']),
				'PartNo' => $xmlLineItem->Sku->_['value'],
				'Price' => $xmlLineItem->ListPrice->_['value'],
				'Qty' => $xmlLineItem->Qty->_['value'],
				'ReasonCode' => $xmlLineItem->ReturnReason->_['value'],
				'Status' => $xmlLineItem->Status->_['value'],
				'CouponSKU' => $xmlLineItem->Sku->_['value'],
				'Total' => $xmlLineItem->Total->_['value'],
				'TotalAdjusted' => $xmlLineItem->AdjustedTotal->_['value'],
				'TotalAfterDiscount' => $xmlLineItem->TotalAfterDiscount->_['value'],
				'TrackingNo' => $xmlLineItem->TrackingNo->_['value'],
				'TrackingURL' => str_replace("%26", "&", $xmlLineItem->TrackingURL->_['value']),
				'Warehouse' => $xmlLineItem->Warehouse->_['value'],
				'OnlineReturnArray' => $onlineReturns,
				'OnlineReturnTrackingNo' => $onlineReturns[0]['OnlineReturnTrackingNo'],
				'ExpDeliveryDateFrom' => $xmlLineItem->ExpDeliveryDateFrom->_['value'],
				'ExpDeliveryDateTo' => $xmlLineItem->ExpDeliveryDateTo->_['value'],
				'IsSubscription' => $xmlLineItem->isSubscription->_['value'],
				'LineId' => $xmlLineItem->LineId->_['value']
			);

                $subTotal += ((int)$thisLineItem['Qty'] * $thisLineItem['TotalAfterDiscount']);
			    array_push($lineItems, $thisLineItem);
		}

		// Package entire Order history with response codes
		$lineItemHistory = array(
			'BillingAddress' => $billTo,
			'LineItems' => $lineItems,
			'OracleAccountNo' => $oracleAccountNo,
			'OrderStatus' => $orderStatus,
			'OrderTotal' => $orderTotal,
			'ResponseCode' => $responseCode,
			'ResponseMessage' => $responseMessage,
			'SalesTax' => $salesTax,
			'ShipAmount' => $shipAmount,
			'ShipCode' => $shipCode,
			'ShippingAddress' => $shipTo
		);

		return $lineItemHistory;
	}
?>