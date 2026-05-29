<?php
	require("custom/webservicehelper.php");
	
	$json = json_decode(file_get_contents('php://input'));
	
	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($json->Session);
	initConnectAPI();
	load_curl();
	use RightNow\Connect\v1_4 as RNCPHP;
	
	try
	{
		$context = RNCPHP\ConnectAPI::getCurrentContext();
		$context->ApplicationContext = "Vertex Tax Calculation";
		
		$vertexAuthToken = json_decode(handleAccessToken('Vertex','TAX'));
		
		if(!$vertexAuthToken->Success)
			$response = $vertexAuthToken;
		else
		{
			$bearerToken = $vertexAuthToken->AccessToken;
			
			$vertexConfig = json_decode(RNCPHP\Configuration::fetch('CUSTOM_CFG_VERTEX_API_CONFIG')->Value);
			$vertexAPI = strpos($_SERVER['HTTP_HOST'], "--") !== false ? $vertexConfig->DEV : $vertexConfig->PROD;
			$salesTaxURL = $vertexAPI->SALESTAX;
			
			$taxPayloadArray = array(
				"order" => array(
					"isTaxExempt" => $json->TaxExempt != true ? false : true,
					"lineItems" => $json->lineItems,
					"shipAmount" => $json->ShipAmount,
					"orderDate" => date("Y-m-d", time()),
					"billingAddress" => array(
						"name" => $json->BillName,
						"street" => $json->BillStreet,
						"street2" => isset($json->BillStreet2) ? $json->BillStreet2 : null,
						"city" => $json->BillCity,
						"stateOrProvince" => $json->BillState,
						"postalCode" => $json->BillPostal,
						"countryCode" => $json->BillCountry,
						"country" => $json->BillCountry
					),
					 "shippingAddress" => array(
						"name" => $json->ShipName,
						"street" => $json->ShipStreet,
						"street2" => isset($json->ShipStreet2) ? $json->ShipStreet2 : null,
						"city" => $json->ShipCity,
						"stateOrProvince" => $json->ShipState,
						"postalCode" => $json->ShipPostal,
						"countryCode" => $json->ShipCountry,
						"country" => $json->ShipCountry
					)
				)
			);
			
			$taxPayload = json_encode($taxPayloadArray);
			
			$cURL = curl_init();
			
			curl_setopt_array($cURL, array(
				CURLOPT_URL => $salesTaxURL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $taxPayload,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $bearerToken
				),
			));
			
			$taxResponse = curl_exec($cURL);

			$success = false;
			if(curl_errno($cURL))
				$message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
			else
			{
				$tax = json_decode($taxResponse);
				if(empty($tax))
					$message = $taxResponse;
				else if(!isset($tax->success))
					$message = $tax->message;
				else
				{
					$success = true;
					$message = print_r($tax, true);
					$primaryTax = 0.00;
					$secondaryTax = 0.00;
					$recycleFee = 0.00;

					if($json->BillCountry == 'US')
						foreach($tax->data as $nextTax)
							$primaryTax += $nextTax->amount;
					else
					{
						foreach($tax->data as $nextTax)
						{
							if($nextTax->jurisdiction == 'COUNTRY')
								$primaryTax += $nextTax->amount;
							elseif($nextTax->description == 'Product Care Eco Fee')
								$recycleFee += $nextTax->amount;
							else
								$secondaryTax += $nextTax->amount;
						}
					}
				}
			}
			
			$systemLog = array(
				"ExtSystem" => $salesTaxURL,
				"IncidentID" => $json->Incident,
				"RequestMsg" => print_r($taxPayload, true),
				"ResponseMsg" => $message,
				"Description" => "Vertex Sales Tax Request"
			);
				
			logSysIntegration($systemLog);
			curl_close($cURL);
			
			$response = array(
				"SUCCESS" => $success
			);
			
			if($success)
			{
				$response['Tax'] = number_format($primaryTax, 2);
				$response['SecondaryTax'] = number_format($secondaryTax, 2);
				$response['RecycleFee'] = number_format($recycleFee, 2);
			}
			else
				$response['Message'] = $message;
		}
	}
	catch(Exception $error)
	{
		$response = array(
			"SUCCESS" => false,
			"Message" => $error->getMessage()
		);
	}
	
	echo json_encode($response);
?>