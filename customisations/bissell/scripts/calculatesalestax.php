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
	$response = calculateSalesTax();
	$json = json_encode($response, JSON_PRETTY_PRINT);
	
	header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
	
	echo $json;
	return $json;
	
	// Call GetConsumerWebOrderSalesTax and return calculated sales tax for given address
	function calculateSalesTax()
	{
		// Pull custom configuration setting to determine mode, username/password, endpoints, debugging
		try
		{
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
		
		// Call WebServiceHelper to create SOAP security header
		$soapHeader = createHeader($bwsConfig['USERNAME'], $bwsConfig['PASSWORD']);
		
		// Grab POST Billing Address
		if(isset($_POST['BillAddress1']))
			$billAddr1 = $_POST['BillAddress1'];
		if(isset($_POST['BillAddress2']))
			$billAddr2 = $_POST['BillAddress2'];
		if(isset($_POST['BillCity']))
			$billCity = $_POST['BillCity'];
		if(isset($_POST['BillStateCode']))
			$billState = $_POST['BillStateCode'];
		if(isset($_POST['BillPostalCode']))
			$billPostal = $_POST['BillPostalCode'];
		if(isset($_POST['BillCountryCode']))
			$billCountry = $_POST['BillCountryCode'];
		
		// Grab POST Shipping Address
		if(isset($_POST['ShipAddress1']))
			$shipAddr1 = $_POST['ShipAddress1'];
		if(isset($_POST['ShipAddress2']))
			$shipAddr2 = $_POST['ShipAddress2'];
		if(isset($_POST['ShipCity']))
			$shipCity = $_POST['ShipCity'];
		if(isset($_POST['ShipStateCode']))
			$shipState = $_POST['ShipStateCode'];
		if(isset($_POST['ShipPostalCode']))
			$shipPostal = $_POST['ShipPostalCode'];
		if(isset($_POST['ShipCountryCode']))
			$shipCountry = $_POST['ShipCountryCode'];
		
		// Grab POST shipping and order amounts
		if(isset($_POST['ShipAmount']))
			$shipAmount = $_POST['ShipAmount'];
		if(isset($_POST['OrderTotal']))
			$orderTotal = $_POST['OrderTotal'];
		if(isset($_POST['SubTotal']))
			$subTotal = $_POST['SubTotal'];
		
		$soapXML = $soapHeader .
			'<s:Body>
				<GetConsumerWebOrderSalesTax xmlns="http://tempuri.org/">
					<order xmlns:a="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Commerce" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<a:Errors i:nil="true"/>
						<a:InvoiceNo i:nil="true"/>
						<a:LineItems i:nil="true"/>
						<a:OperatingUnit i:nil="true"/>
						<a:OracleAccountNo i:nil="true"/>
						<a:OrderAdjustment i:nil="true"/>
						<a:OrderDate>0001-01-01T00:00:00</a:OrderDate>
						<a:OrderNumber i:nil="true"/>
						<a:OrderStatus i:nil="true"/>
						<a:OrderTotal>' . $orderTotal . '</a:OrderTotal>
						<a:RISAction i:nil="true"/>
						<a:ResponseCode i:nil="true"/>
						<a:ResponseMessage i:nil="true"/>
						<a:SalesTax i:nil="true"/>
						<a:Scor i:nil="true"/>
						<a:ShipAmount>' . $shipAmount . '</a:ShipAmount>
						<a:ShipCode i:nil="true"/>
						<a:ShipType>None</a:ShipType>
						<a:Status>0</a:Status>
						<a:SubTotal>' . $subTotal . '</a:SubTotal>
						<a:TrackingInfo i:nil="true"/>
						<a:Tran i:nil="true"/>
						<a:BillingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">
							<b:Address1>' . $billAddr1 . '</b:Address1>
							<b:Address2>' . $billAddr2 . '</b:Address2>
							<b:City>' . $billCity . '</b:City>
							<b:CountryCode>' . $billCountry . '</b:CountryCode>
							<b:PostalCode>' . $billPostal . '</b:PostalCode>
							<b:StateCode>' . $billState . '</b:StateCode>
							<b:Company i:nil="true"/>
							<b:Country i:nil="true"/>
							<b:EmailAddress i:nil="true"/>
							<b:FirstName i:nil="true"/>
							<b:LastName i:nil="true"/>
							<b:Name i:nil="true"/>
							<b:Phone i:nil="true"/>
							<b:Province i:nil="true"/>
							<b:State i:nil="true"/>
						</a:BillingAddress>
						<a:CashPayment i:nil="true"/>
						<a:CouponGiven i:nil="true"/>
						<a:EbayOrderId i:nil="true"/>
						<a:HeaderBaggage i:nil="true"/>
						<a:Payment i:nil="true"/>
						<a:SessionID i:nil="true"/>
						<a:ShippingAddress xmlns:b="http://schemas.datacontract.org/2004/07/BissellServicesWCF.BO.Address">
							<b:Address1>' . $shipAddr1 . '</b:Address1>
							<b:Address2>' . $shipAddr2 . '</b:Address2>
							<b:City>' . $shipCity . '</b:City>
							<b:CountryCode>' . $shipCountry . '</b:CountryCode>
							<b:PostalCode>' . $shipPostal . '</b:PostalCode>
							<b:StateCode>' . $shipState . '</b:StateCode>
							<b:Company i:nil="true"/>
							<b:Country i:nil="true"/>
							<b:EmailAddress i:nil="true"/>
							<b:FirstName i:nil="true"/>
							<b:LastName i:nil="true"/>
							<b:Name i:nil="true"/>
							<b:Phone i:nil="true"/>
							<b:Province i:nil="true"/>
							<b:State i:nil="true"/>
						</a:ShippingAddress>
						<a:UserIP i:nil="true"/>
						<a:WebSourceOrderID i:nil="true"/>
					</order>
				</GetConsumerWebOrderSalesTax>
			</s:Body>
		</s:Envelope>';

		// Call Bissell webservices and capture response
		$bissellResponse = curlExec($bwsConfig, $bwsConfig['CALCULATE_SALES_TAX']['WSDL'], $bwsConfig['CALCULATE_SALES_TAX']['FUNCTION'], $soapXML);
		
		// Log and return any cURL errors  
		if(isset($bissellResponse['CurlErrorCode']))
		{
			$salesTax = array(
				'ResponseCode' => $bissellResponse['CurlErrorCode'],
				'ResponseMessage' => $bissellResponse['CurlErrorMsg']
			);
			
			$curlError = 'CURL Error Code: ' . $bissellResponse['CurlErrorCode'] . " - " . $bissellResponse['CurlErrorMsg'] ;
			$info = array(
				'ExtSystem'	=> 'GetConsumerWebOrderSalesTax',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $curlError,
				'Description' => $curlError,
				'Error'	=> $curlError,
				'IncidentID' => $_POST['IncidentId'],
				'ContactID'	=> $_POST['ContactId'],
				'OrderHeaderID'	=> $_POST['OrderHeaderId']
			);
			logSysIntegration($info);
			return $salesTax;
		}
		
		// Parse entire XML response into tree
		$xmlTree = new xtree(
			array(
				'xmlRaw' => $bissellResponse,
				'stripNamespaces' => true
			));
		
		// Drill-down into GetConsumerWebOrderSalesTaxResult section of the XML body, we don't care about the rest of the response
		$xml = $xmlTree->xtree->Envelope->Body->GetConsumerWebOrderSalesTaxResponse->GetConsumerWebOrderSalesTaxResult;
		
		// Grab all Primary and Secondary return data
		if(isset($xml->Amount))
			$amount = $xml->Amount->_['value'];
		if(isset($xml->Description))
			$description = $xml->Description->_['value'];
		if(isset($xml->Percent))
			$percent = $xml->Percent->_['value'];
		if(isset($xml->SecondaryAmount))
			$secondaryAmount = $xml->SecondaryAmount->_['value'];
		if(isset($xml->SecondaryDescription))
			$secondaryDescription = $xml->SecondaryDescription->_['value'];
		if(isset($xml->SecondaryPercent))
			$secondaryPercent = $xml->SecondaryPercent->_['value'];

		// Calculate order total
		// Order Total = Order + Shipping + Tax
		$total = $orderTotal + $shipAmount + $amount;
		
		// Add secondary sales tax from Canada if it is set
		if(isset($secondaryAmount))
			$total = $total + $secondaryAmount;
		
		// Create associative array and convert to JSON
		$salesTax = array(
			'Amount' => $amount,
			'Description' => $description,
			'Percent' => $percent,
			'SecondaryAmount' => $secondaryAmount,
			'SecondaryDescription' => $secondaryDescription,
			'SecondaryPercent' => $secondaryPercent,
			'Total' => $total
			);
		
		// Log system integration record if debug mode is enabled
		if($debug == 1)
		{
			$info = array(
				'ExtSystem'	=> 'GetConsumerWebOrderSalesTax',
				'RequestMsg' => $soapXML,
				'ResponseMsg' => $bissellResponse,
				'Description' => 'Sales Tax Calculation Debug Log',
				'Error'	=> 'Sales Tax Calculation Debug Log',
				'IncidentID' => $_POST['IncidentId'],
				'ContactID'	=> $_POST['ContactId'],
				'OrderHeaderID'	=> $_POST['OrderHeaderId']
			);
			logSysIntegration($info);
		}
		
		return $salesTax;
	}
?>