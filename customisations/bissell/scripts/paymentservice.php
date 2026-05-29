<?php
	// Include helper function for parsing XML and handling common Web Service functions
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
	
	try
	{
		// Pull custom configuration setting to determine mode, username/password, endpoints
		$bwsConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_BWS_INTEGRATION");
		$bwsConfig = (json_decode($bwsConfig->Value, true));
		
		// BUS$SysIntegrationLog debugger
		$sysLogConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_DEBUG_SYSINTEGRATION);
		$debug = $sysLogConfig->Value;
	}
	
	catch(Exception $err)
	{
		return $err;
	}	
	
	// Call proper function based on the passed in action
	if(isset($_POST['action']))
		$action = $_POST['action'];
	else
		$action = $_GET['action'];
	
	switch($action)
	{
		case "getpaymenthistory":
			$response = getPaymentHistory($bwsConfig);
			break;
		case "makepayment":
			$response = makePayment($bwsConfig, $debug);
			break;
		case "updatepaymentdetails":
			$response = updatePaymentDetails($bwsConfig, $debug);
			break;
		case "updatepaymentschedule":
			$response = updatePaymentSchedule($bwsConfig, $debug);
			break;
		default:
			$response = array(
				'Code'		=> "400",
				'Message'	=> "Invalid action"
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
	
	// Call GetConsumerMultiPayHistory and return all payment history for given Contact
	function getPaymentHistory($bwsConfig)
	{	
		// Retrieve OrderNumber
		if(isset($_POST['OrderNumber']))
			$orderNum = $_POST['OrderNumber'];
		else
			$orderNum = $_GET['OrderNumber'];
		
		// Retrieve OracleAccountNo
		if(isset($_POST['OracleAccountNo']))
			$oracleAccountNo = $_POST['OracleAccountNo'];
		else
			$oracleAccountNo = $_GET['OracleAccountNo'];
		
		// Web Service call requires OrderNumber and OracleAccountNo. If either field are not passed, return an error
		if(!isset($orderNum) || !isset($oracleAccountNo))
		{
			$payHistory = array(
				'ResponseCode' => "400",
				'ResponseMessage' => "Missing field",
				'OrderNumber' => $orderNum,
				'OracleAccountNo' => $oracleAccountNo
			);
			return $payHistory;
		}
		
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['PAYMENT_SERVICE']['USERNAME'], $bwsConfig['PAYMENT_SERVICE']['PASSWORD']);
		
		$soapXML = $soapHeader .
			'<s:Body>
				<GetConsumerMultiPayHistory xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:OrderNumber>' . $orderNum . '</a:OrderNumber>
						<a:CustomerID>' . $oracleAccountNo . '</a:CustomerID>
						<a:EmailAddress i:nil="true"/>
						<a:PostalCode i:nil="true"/>
					</p>
				</GetConsumerMultiPayHistory>
			</s:Body>
		</s:Envelope>';

		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['PAYMENT_SERVICE']['WSDL'], $bwsConfig['PAYMENT_SERVICE']['PAY_HISTORY'], $soapXML);
		
		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$payHistory = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'GetConsumerMultiPayHistory',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $payHistory;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
		));
		
		// Drill-down into results
		$xml = $xmlTree->xtree->Envelope->Body->GetConsumerMultiPayHistoryResponse->GetConsumerMultiPayHistoryResult;
		
		// Grab the response code and response message
		if(isset($xml->ResponseCode))
			$respCode = $xml->ResponseCode->_['value'];
		if(isset($xml->ResponseMessage))
			$respMessage = $xml->ResponseMessage->_['value'];
		
		// Capture Customer Transaction ID, required for follow-up Payment Service function calls
		if(isset($xml->CustomerTrxID))
			$customerTrxID = $xml->CustomerTrxID->_['value'];
		// Total Amount Due
		if(isset($xml->TotalAmountDue))
			$totalDue = $xml->TotalAmountDue->_['value'];
		
		// Drill-down into Payments array
		$payments = array();
		$xmlPayments = $xml->PaymentsList->Payments;
		
		// IF there are multiple payments, we need to treat Payments as an array and iterate through
		if(count($xmlPayments) > 1)
		{
			foreach($xmlPayments as $payment)
			{
				$thisPayment = array(
					'AmountDueOriginal'	=> $payment->Amount_Due_Original->_['value'],
					'AmountDueRemaining' => $payment->Amount_Due_Remaining->_['value'],
					'DisplayCardInformation' => $payment->DisplayCardInformation->_['value'],
					'DisplayPaymentDate' => $payment->DisplayPaymentDate->_['value'],
					'PaymentNumber'	=> $payment->Payment_Number->_['value'],
					'PaymentScheduleID'	=> $payment->Payment_Schedule_ID->_['value'],
					'SettlementResponse' => $payment->Settlement_Response->_['value']
				);
				array_push($payments, $thisPayment);
			}
		}
		
		// ELSE a single payment is returned for an Order, treat payment as a single object
		else
		{
			$thisPayment = array(
				'AmountDueOriginal'	=> $xmlPayments->Amount_Due_Original->_['value'],
				'AmountDueRemaining' => $xmlPayments->Amount_Due_Remaining->_['value'],
				'DisplayCardInformation' => $xmlPayments->DisplayCardInformation->_['value'],
				'DisplayPaymentDate' => $xmlPayments->DisplayPaymentDate->_['value'],
				'PaymentNumber' => $xmlPayments->Payment_Number->_['value'],
				'PaymentScheduleID'	=> $xmlPayments->Payment_Schedule_ID->_['value'],
				'SettlementResponse' => $xmlPayments->Settlement_Response->_['value']
			);
			array_push($payments, $thisPayment);
		}
		
		// Create associative array and return
		$payHistory = array(
			'ResponseCode' => $respCode,
			'ResponseMessage' => $respMessage,
			'CustomerTrxID' => $customerTrxID,
			'TotalDue' => $totalDue,
			'Payments' => $payments
		);
		return $payHistory;
	}

	// Call SubmitMultiPayMakePayment and return submitted payment status
	function makePayment($bwsConfig, $debug)
	{
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['PAYMENT_SERVICE']['USERNAME'], $bwsConfig['PAYMENT_SERVICE']['PASSWORD']);
		
		if(isset($_POST['CustomerTrxID']))
			$custID = '<a:CustomerTrxID>' . $_POST['CustomerTrxID'] . '</a:CustomerTrxID>';
		if(isset($_POST['PaymentAmount']))
			$amount = '<a:PaymentAmount>' . $_POST['PaymentAmount'] . '</a:PaymentAmount>';
		
		$soapXML = $soapHeader .
			'<s:Body>
				<SubmitMultiPayMakePayment xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:BillingAddress1 i:nil="true"/>
						<a:BillingAddress2 i:nil="true"/>
						<a:BillingCity i:nil="true"/>
						<a:BillingName i:nil="true"/>
						<a:BillingPostalCode i:nil="true"/>
						<a:BillingState i:nil="true"/>
						<a:CCExpMonth i:nil="true"/>
						<a:CCExpYear i:nil="true"/>
						<a:CCNumber i:nil="true"/>
						<a:CCType i:nil="true"/>
						' . $custID . '
						' . $amount . '
					</p>
				</SubmitMultiPayMakePayment>
			</s:Body>
		</s:Envelope>';

		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['PAYMENT_SERVICE']['WSDL'], $bwsConfig['PAYMENT_SERVICE']['MAKE_PAYMENT'], $soapXML);
		
		// Log and return any cURL errors
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$paymentResponse = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'SubmitMultiPayMakePayment',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $paymentResponse;
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
			$respCode = $xmlTree->xtree->Envelope->Body->Fault->faultcode->_['value'];
			$respMessage = $xmlTree->xtree->Envelope->Body->Fault->faultstring->_['value'];
		}
		
		// Drill-down into results
		$xml = $xmlTree->xtree->Envelope->Body->SubmitMultiPayMakePaymentResponse->SubmitMultiPayMakePaymentResult;
		
		// Grab the response code and response message
		if(isset($xml->ResponseCode))
			$respCode = $xml->ResponseCode->_['value'];
		if(isset($xml->ResponseMessage))
			$respMessage = $xml->ResponseMessage->_['value'];

		$paymentResponse = array(
			'ResponseCode' => $respCode,
			'ResponseMessage' => $respMessage
		);
		
		// Write a payment log to BUS$TransactionLog
		$comment = 'Code: ' . $respCode . ' Message: ' . $respMessage;
		
		$info = array(
			'LogAction' => 'SubmitMultiPayMakePayment',
			'Comment' => $comment,
			'ContactID'	=> $_POST['ContactID']
		);
		logTransaction($info);
		
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{
			$info = array(
				'ExtSystem'	=> 'SubmitMultiPayMakePayment',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => $respMessage,
				'Error'	=> $respCode,
				'ContactID' => $_POST['ContactID']
			);
			logSysIntegration($info);
		}
		return $paymentResponse;
	}
	
	// Call SubmitMultiPayChangeCC and return requested credit card details change status
	function updatePaymentDetails($bwsConfig, $debug)
	{
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['USERNAME'], $bwsConfig['PASSWORD']);
		
		// Capture Address/Credit Card payment changes
		if(isset($_POST['Address1']))
			$addr1 = $_POST['Address1'];
		if(isset($_POST['Address2']))
			$addr2 = $_POST['Address2'];
		if(isset($_POST['City']))
			$city = $_POST['City'];
		if(isset($_POST['Name']))
			$name = $_POST['Name'];
		if(isset($_POST['PostalCode']))
			$zip = $_POST['PostalCode'];
		if(isset($_POST['StateCode']))
			$state = $_POST['StateCode'];
		if(isset($_POST['CCExpMonth']))
			$ccExpMonth = $_POST['CCExpMonth'];
		if(isset($_POST['CCExpYear']))
			$ccExpYear = $_POST['CCExpYear'];
		if(isset($_POST['CCNum']))
			$ccNum = $_POST['CCNum'];
		if(isset($_POST['CCType']))
			$ccType = $_POST['CCType'];
		if(isset($_POST['CustomerTrxID']))
			$custID = $_POST['CustomerTrxID'];
		
		$soapXML = $soapHeader .
			'<s:Body>
				<SubmitMultiPayChangeCC xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:BillingAddress1>' . $addr1 . '</a:BillingAddress1>
						<a:BillingAddress2>' . $addr2 . '</a:BillingAddress2>
						<a:BillingCity>' . $city. '</a:BillingCity>
						<a:BillingName>' . $name . '</a:BillingName>
						<a:BillingPostalCode>' . $zip . '</a:BillingPostalCode>
						<a:BillingState>' . $state . '</a:BillingState>
						<a:CCExpMonth>' . $ccExpMonth . '</a:CCExpMonth>
						<a:CCExpYear>' . $ccExpYear . '</a:CCExpYear>
						<a:CCNumber>' . $ccNum . '</a:CCNumber>
						<a:CCType>' . $ccType . '</a:CCType>
						<a:CustomerTrxID>' . $custID . '</a:CustomerTrxID>
					</p>
				</SubmitMultiPayChangeCC>
			</s:Body>
		</s:Envelope>';
		
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['PAYMENT_SERVICE']['WSDL'], $bwsConfig['PAYMENT_SERVICE']['CHANGE_CC_DETAILS'], $soapXML);
		
		// Log and return any cURL errors 
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$payUpdateResponse = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'SubmitMultiPayChangeCC',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $payUpdateResponse;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));
		
		// Drill-down into results
		$xml = $xmlTree->xtree->Envelope->Body->SubmitMultiPayChangeCCResponse->SubmitMultiPayChangeCCResult;
		
		// Grab the response code and response message
		if(isset($xml->ResponseCode))
			$respCode = $xml->ResponseCode->_['value'];
		if(isset($xml->ResponseMessage))
			$respMessage = $xml->ResponseMessage->_['value'];

		$payUpdateResponse = array(
			'ResponseCode' => $respCode,
			'ResponseMessage' => $respMessage
		);
		
		// Write a payment detail update log to BUS$TransactionLog
		$comment = 'Code: ' . $respCode . ' Message: ' . $respMessage;
		
		$info = array(
			'LogAction' => 'SubmitMultiPayChangeCC',
			'Comment' => $comment,
			'ContactID' => $_POST['ContactID']
		);
		logTransaction($info);
		
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{
			$info = array(
				'ExtSystem'	=> 'SubmitMultiPayChangeCC',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => $respMessage,
				'Error'	=> $respCode,
				'ContactID' => $_POST['ContactID']
			);
			logSysIntegration($info);
		}
		return $payUpdateResponse;
	}
	
	// Call SubmitMultiPayChangePayDate adn return requested payment schedule change status
	function updatePaymentSchedule($bwsConfig, $debug)
	{
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['USERNAME'], $bwsConfig['PASSWORD']);
		
		// Parse out all scheduled payment date changes
		$soapDateChanges = '<a:PaymentsList xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce">';
		
		$paymentSchedules = $_POST['PaymentSchedule'];
		
		// Iterate through all scheduled payment changes and create XML entry for each change
		foreach($paymentSchedules as $payment)
		{
			$soapDateChanges = $soapDateChanges . '<b:PaymentParameters>';
			
			if(isset($payment['Payment_Due_Date']))
				$soapDateChanges = $soapDateChanges . '<b:Payment_Due_Date>' . $payment['Payment_Due_Date'] . '</b:Payment_Due_Date>';
			if(isset($payment['Payment_Schedule_ID']))
				$soapDateChanges = $soapDateChanges . '<b:Payment_Schedule_ID>' . $payment['Payment_Schedule_ID'] . '</b:Payment_Schedule_ID>';
			
			$soapDateChanges = $soapDateChanges . '</b:PaymentParameters>';
		}
		$soapDateChanges = $soapDateChanges . '</a:PaymentsList>';
		
		$soapXML = $soapHeader .
			'<s:Body>
				<SubmitMultiPayChangePayDate xmlns="http://tempuri.org/">
					<p xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce.RequestParameters" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						' . $soapDateChanges . '
					</p>
				</SubmitMultiPayChangePayDate>
			</s:Body>
		</s:Envelope>';
		
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['PAYMENT_SERVICE']['WSDL'], $bwsConfig['PAYMENT_SERVICE']['CHANGE_PAY_DATE'], $soapXML);
			
		// Log and return any cURL errors 
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$dateUpdateResponse = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'SubmitMultiPayChangePayDate',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError
			);
			logSysIntegration($info);
			return $dateUpdateResponse;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));
		
		// Drill-down into results
		$xml = $xmlTree->xtree->Envelope->Body->SubmitMultiPayChangePayDateResponse->SubmitMultiPayChangePayDateResult;
		
		// Grab the response code and response message
		if(isset($xml->ResponseCode))
			$respCode = $xml->ResponseCode->_['value'];
		if(isset($xml->ResponseMessage))
			$respMessage = $xml->ResponseMessage->_['value'];

		$dateUpdateResponse = array(
			'ResponseCode' => $respCode,
			'ResponseMessage' => $respMessage
		);
		
		// Write a scheduled payment update log to BUS$TransactionLog
		$comment = 'Code: ' . $respCode . ' Message: ' . $respMessage;
		
		$info = array(
			'LogAction' => 'SubmitMultiPayChangePayDate',
			'Comment' => $comment,
			'ContactID'	=> $_POST['ContactID']
		);
		logTransaction($info);
		
		// IF debug mode is enabled, write integration to BUS$SysIntegrationLog
		if($debug == 1)
		{
			$info = array(
				'ExtSystem' => 'SubmitMultiPayChangePayDate',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => $respMessage,
				'Error'	=> $respCode,
				'ContactID' => $_POST['ContactID']
			);
			logSysIntegration($info);
		}
		return $dateUpdateResponse;
	}	
?>