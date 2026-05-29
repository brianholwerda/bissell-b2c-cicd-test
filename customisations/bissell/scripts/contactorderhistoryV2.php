<?php
	require("custom/webservicehelper.php");
	
	$json = json_decode(file_get_contents('php://input'));
	$session = isset($_POST['Session']) ? $_POST['Session'] : $json->Session;
	
	/************* Agent Authentication ***************/
	require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($session);
	initConnectAPI();
	load_curl();
	use RightNow\Connect\v1_4 as RNCPHP;
	
	try
	{
		$context = RNCPHP\ConnectAPI::getCurrentContext();
		$context->ApplicationContext = "Vertex Order History/Details";
		
		$vertexAuthToken = json_decode(handleAccessToken('Vertex','ORDERHISTORY'));
		
		if(!$vertexAuthToken->Success)
			$response = $vertexAuthToken;
		else
		{
			$bearerToken = $vertexAuthToken->AccessToken;
			
			$vertexConfig = json_decode(RNCPHP\Configuration::fetch('CUSTOM_CFG_VERTEX_API_CONFIG')->Value);
			$vertexAPI = strpos($_SERVER['HTTP_HOST'], "--") !== false ? $vertexConfig->DEV : $vertexConfig->PROD;
			
			$action = isset($_POST['Action']) ? $_POST['Action'] : $json->Action;
			switch($action)
			{
				case "GetContactByOrder":
					$orderDetailsURL = $vertexAPI->ORDERDETAILS . "/" . trim($json->OrderNumber);
					$response = getContactByOrder($bearerToken, $orderDetailsURL, trim($json->OrderNumber));
					break;
				case "getorderhistory":
					$orderHistoryURL = $vertexAPI->ORDERHISTORY;
					$response = getOrderHistory($bearerToken, $orderHistoryURL, $_POST['ConsumerID']);
					break;
				case "getlineitemhistory":
					$orderDetailsURL = $vertexAPI->ORDERDETAILS . "/" . $_POST['OrderNumber'];
					$response = getLineItemHistory($bearerToken, $orderDetailsURL, $_POST['ConsumerID']);
					break;
				case 'getorderdetails':
					$orderDetailsURL = $vertexAPI->ORDERDETAILS . "/" . trim($json->OrderNumber);
					$response = getOrderDetails($bearerToken, $orderDetailsURL);
					break;
				default:
					$response = array(
						'Code' => "400",
						'Message' => "Invalid action"
					);
					break;
			}
		}
	}

	catch(Exception $e)
	{
		return $e;
	}

	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

	$json = json_encode($response, JSON_PRETTY_PRINT);
	echo $json;
	return $json;
	
	function getContactByOrder($bearerToken, $orderDetailsURL, $orderNumber)
	{
		$cURL = curl_init();
			
		curl_setopt_array($cURL, array(
			CURLOPT_URL => $orderDetailsURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $bearerToken
			),
		));
		
		$orderDetailsResponse = curl_exec($cURL);
		
		$success = false;
		if(curl_errno($cURL))
			$error = $message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
		else
		{
			$orderDetails = json_decode($orderDetailsResponse);
			$message = print_r($orderDetails, true);
			if(isset($orderDetails->message))
				$error = $orderDetails->message;
			else
			{
				$order = $orderDetails->order->ConsumerWebOrder;
				
				if(!is_numeric($order->customerId))
					$error = "Bad Request: ConsumerID is not numeric - " . $order->customerId;
				else
				{
					$consumerID = $order->customerId;
					
					if(isset($order->onyxId)){
						if(is_numeric($order->onyxId)){
							$consumerID = $order->onyxId;
						}
					}
					
					$contact = RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $consumerID);
					if(isset($contact))
					{
						$success = true;
						$contactID = $contact->ID;
					}
					else
						$error = "Cannot find Contact record with ConsumerID " . $order->customerId . " from OrderNumber " . $orderNumber;
				}
			}
		}
		
		$systemLog = array(
			"Error" => $success == true ? "Side Panel Order Search Successful" : $error,
			"ExtSystem" => "Vertex GET Order Details",
			"RequestMsg" => "URL: " . $orderDetailsURL,
			"ResponseMsg" => $message,
			"Description" => "Vertex GET Order Details"
		);
		
		logSysIntegration($systemLog);
		curl_close($cURL);

		if(!$success)
			$contactInformtion = array('Error' => $error);
		else
			$contactInformtion = array('ContactID' => $contactID);
		
		return $contactInformtion;
	}
	
	function getOrderHistory($bearerToken, $orderHistoryURL, $consumerID)
	{
		$orderHistoryPayloadArray = array(
			"crmCustomerId" => $consumerID,
			"startDate" => $_POST['CreatedAfterDate'],
			"endDate" => date("Y-m-d", strtotime("+1 day"))
		);
		
		$orderHistoryPayload = json_encode($orderHistoryPayloadArray);
		
		$cURL = curl_init();
			
		curl_setopt_array($cURL, array(
			CURLOPT_URL => $orderHistoryURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $orderHistoryPayload,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $bearerToken
			),
		));
		
		$orderHistoryResponse = curl_exec($cURL);
		
		$success = false;
		if(curl_errno($cURL))
			$error = $message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
		else
		{
			$orderHistory = json_decode($orderHistoryResponse);
			$message = print_r($orderHistory, true);
			if(isset($orderHistory->message))
				$error = $orderHistory->message;
			else
			{
				$success = true;
				$orderHistoryResults = array();
				
				foreach($orderHistory->orderList as $nextOrder)
				{					
					$orderResult = array(
						"EBayOrderNum" => $nextOrder->custPONumber,
						"IsSubscription" => $nextOrder->isSubscription,
						"OrderDate" => $nextOrder->orderDate,
						"OrderNumber" => $nextOrder->orderNumber,
						"OrderStatus" => $nextOrder->orderStatus,
						"OrderType" => $nextOrder->channel,
						"OrderTotal" => $nextOrder->orderTotal,
						"PartNumbers" => $nextOrder->partsList,
						"PaymentType" => $nextOrder->paymentType,
					);
					
					array_push($orderHistoryResults, $orderResult);
				}
			}
		}
		
		$systemLog = array(
			"ContactID" => RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $consumerID)->ID,
			"Error" => $success == true ? "Vertex GET Order History Successful" : $error,
			"ExtSystem" => "Vertex GET Order History",
			"RequestMsg" => "URL: " . $orderHistoryURL . PHP_EOL . PHP_EOL . "PAYLOAD: " . PHP_EOL . print_r($orderHistoryPayload, true),
			"ResponseMsg" => $message,
			"Description" => "Vertex GET Order History"
		);
		
		logSysIntegration($systemLog);
		curl_close($cURL);
			
		if(!$success)
			$orderHistory = array('ResponseCode' => $error, 'ResponseMessage' => $message);
		else
			$orderHistory = array('Order' => $orderHistoryResults, 'ResponseCode' => 'Success', 'ResponseMessage' => 'Success');
		
		return $orderHistory;
	}

	function getLineItemHistory($bearerToken, $orderDetailsURL, $consumerID)
	{	
		$cURL = curl_init();
			
		curl_setopt_array($cURL, array(
			CURLOPT_URL => $orderDetailsURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $bearerToken
			),
		));
		
		$orderDetailsResponse = curl_exec($cURL);
		
		$success = false;
		if(curl_errno($cURL))
			$error = $message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
		else
		{
			$orderDetails = json_decode($orderDetailsResponse);
			$message = print_r($orderDetails, true);
			if(isset($orderDetails->message))
				$error = $orderDetails->message;
			else
			{
				$success = true;
				$order = $orderDetails->order->ConsumerWebOrder;
				$lineItems = array();
				
				foreach($order->lineItems as $nextLineItem)
				{
					$lineItem = array(
						'CouponCode' => $nextLineItem->couponCode,
						'LineDiscounts' => $nextLineItem->couponSavings,
						'PartDesc' => $nextLineItem->description,
						'PartNo' => $nextLineItem->sku,
						'Price' => $nextLineItem->listPrice,
						'Qty' => $nextLineItem->qty,
						'Status' => $nextLineItem->status,
						'CouponSKU' => $nextLineItem->couponCode,
						'Total' => $nextLineItem->productTotalAfterDiscounts,
						'TotalAdjusted' => $nextLineItem->adjustedTotal,
						'TotalAfterDiscount' => $nextLineItem->productTotalAfterDiscounts,
						'TrackingNo' => $nextLineItem->trackingNo,
						'Warehouse' => isset($nextLineItem->warehouse) ? $nextLineItem->warehouse : "-",
						'OnlineReturnArray' => $nextLineItem->narvarOnlineReturn,
						'OnlineReturnTrackingNo' => $nextLineItem->narvarOnlineReturn[0]->onlineReturnTrackingNo,
						'ExpDeliveryDateFrom' => $nextLineItem->expDeliveryDateFrom,
						'ExpDeliveryDateTo' => $nextLineItem->expDeliveryDateTo,
						'IsSubscription' => $nextLineItem->subscriptionFlag,
						'LineId' => $nextLineItem->lineId
					);
					
					array_push($lineItems, $lineItem);
				}
				
				$lineItemHistory = array(
					'BillingAddress' => array (
						'Name' => $order->billingAddress->name,
						'Email' => $order->customerEmail,
						'Phone' => $order->billingAddress->phone,
						'Street1' => $order->billingAddress->address1,
						'Street2' => $order->billingAddress->address2,
						'City' => $order->billingAddress->city,
						'State' => $order->billingAddress->state,
						'Country' => $order->billingAddress->country,
						'Postal' => $order->billingAddress->postalCode
					),
					'LineItems' => $lineItems,
					'OracleAccountNo' => $order->oracleAccountNo,
					'OrderTotal' => $order->orderTotal,
					'ResponseCode' => $orderDetails->responseCode,
					'ResponseMessage' => $orderDetails->responseMessage,
					'SalesTax' => array (
						'Amount' => $order->salesTax->amount
					),
					'ShipAmount' => trim($order->shippingAmount) != '' ? $order->shippingAmount : '0',
					'ShipCode' => $order->shipCode,
					'ShippingAddress' =>array (
						'Name' => $order->shippingAddress->name,
						'Email' => $order->customerEmail,
						'Phone' => $order->shippingAddress->phone,
						'Street1' => $order->shippingAddress->address1,
						'Street2' => $order->shippingAddress->address2,
						'City' => $order->shippingAddress->city,
						'State' => $order->shippingAddress->state,
						'Country' => $order->shippingAddress->country,
						'Postal' => $order->shippingAddress->postalCode
					)
				);
			}
		}

		$systemLog = array(
			"ContactID" => RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $consumerID)->ID,
			"Error" => $success == true ? "Vertex GET Order Details Successful" : $error,
			"ExtSystem" => "Vertex GET Order Details",
			"RequestMsg" => "URL: " . $orderDetailsURL,
			"ResponseMsg" => $message,
			"Description" => "Vertex GET Order Details"
		);
		
		logSysIntegration($systemLog);
		curl_close($cURL);
		
		return $lineItemHistory;
	}
	
	function getOrderDetails($bearerToken, $orderDetailsURL)
	{	
		$cURL = curl_init();
			
		curl_setopt_array($cURL, array(
			CURLOPT_URL => $orderDetailsURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $bearerToken
			),
		));
		
		$orderDetailsResponse = curl_exec($cURL);
		
		$success = false;
		if(curl_errno($cURL))
			$error = $message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
		else
		{
			$orderDetails = json_decode($orderDetailsResponse);
			$message = print_r($orderDetails, true);
			if(isset($orderDetails->message))
				$error = $orderDetails->message;
			else
			{
				$order = $orderDetails->order->ConsumerWebOrder;
				$success = true;
				
				$lineItems = array();
				
				foreach($order->lineItems as $nextLineItem)
				{
					$lineItem = array(
						'CouponCode' => $nextLineItem->couponCode,
						'LineDiscounts' => $nextLineItem->couponSavings,
						'PartDesc' => $nextLineItem->description,
						'PartNo' => $nextLineItem->sku,
						'Price' => $nextLineItem->listPrice,
						'Qty' => $nextLineItem->qty,
						'SelectedQty' => $nextLineItem->qty,
						'ApprovalStatus' => null,
						'Status' => $nextLineItem->status,
						'CouponSKU' => $nextLineItem->couponCode,
						'Total' => $nextLineItem->productTotalAfterDiscounts,
						'TotalAdjusted' => $nextLineItem->adjustedTotal,
						'TotalAfterDiscount' => $nextLineItem->productTotalAfterDiscounts,
						'TrackingNo' => $nextLineItem->trackingNo,
						'Warehouse' => isset($nextLineItem->warehouse) ? $nextLineItem->warehouse : "-",
						'OnlineReturnArray' => $nextLineItem->narvarOnlineReturn,
						'OnlineReturnTrackingNo' => $nextLineItem->narvarOnlineReturn[0]->onlineReturnTrackingNo,
						'ExpDeliveryDateFrom' => $nextLineItem->expDeliveryDateFrom,
						'ExpDeliveryDateTo' => $nextLineItem->expDeliveryDateTo,
						'IsSubscription' => $nextLineItem->subscriptionFlag,
						'LineId' => $nextLineItem->lineId
					);
					
					array_push($lineItems, $lineItem);
				}
				
				$lineItemHistory = array(
					'BillingAddress' => array (
						'Name' => $order->billingAddress->name,
						'Email' => $order->customerEmail,
						'Phone' => $order->billingAddress->phone,
						'Street1' => $order->billingAddress->address1,
						'Street2' => $order->billingAddress->address2,
						'City' => $order->billingAddress->city,
						'State' => $order->billingAddress->state,
						'Country' => $order->billingAddress->country,
						'Postal' => $order->billingAddress->postalCode
					),
					'LineItems' => $lineItems,
					'OracleAccountNo' => $order->oracleAccountNo,
					'OrderTotal' => $order->orderTotal,
					'ResponseCode' => $orderDetails->responseCode,
					'ResponseMessage' => $orderDetails->responseMessage,
					'SalesTax' => array (
						'Amount' => $order->salesTax->amount
					),
					'ShipAmount' => trim($order->shippingAmount) != '' ? $order->shippingAmount : '0',
					'ShipCode' => $order->shipCode,
					'ShippingAddress' =>array (
						'Name' => $order->shippingAddress->name,
						'Email' => $order->customerEmail,
						'Phone' => $order->shippingAddress->phone,
						'Street1' => $order->shippingAddress->address1,
						'Street2' => $order->shippingAddress->address2,
						'City' => $order->shippingAddress->city,
						'State' => $order->shippingAddress->state,
						'Country' => $order->shippingAddress->country,
						'Postal' => $order->shippingAddress->postalCode
					)
				);
			}
		}
		
		$systemLog = array(
			"Error" => $success == true ? "Side Panel Order Search Successful" : $error,
			"ExtSystem" => "Vertex GET Order Details",
			"RequestMsg" => "URL: " . $orderDetailsURL,
			"ResponseMsg" => $message,
			"Description" => "Vertex GET Order Details"
		);
		
		logSysIntegration($systemLog);
		curl_close($cURL);

		// if(!$success)
			// $contactInformtion = array('Error' => $error);
		// else
			// $contactInformtion = array('ContactID' => $contactID);
		
		return $lineItemHistory;
	}
?>