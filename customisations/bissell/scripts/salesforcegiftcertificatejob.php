<?php
	require("custom/webservicehelper.php");
	
	// Parse Basic Authentication	
	$basicPieces = explode('Basic ', $_SERVER['HTTP_OSVC_AUTHENTICATION']);
	$rawSecurity = base64_decode($basicPieces[1]);
	$security = explode(':', $rawSecurity);
	$user = $security[0];
	$pass = $security[1];
	
	/************* Agent Authentication ***************/
	require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	if(isset($_SERVER['HTTP_OSVC_AUTHENTICATION']))
		$account = AgentAuthenticator::authenticateCredentials($user, $pass);
	else
		$account = AgentAuthenticator::authenticateSessionID(null);
	initConnectAPI();
	use RightNow\Connect\v1_4 as RNCPHP;
	
	try
	{
		$context = RNCPHP\ConnectAPI::getCurrentContext();
		$context->ApplicationContext = "SalesForceGiftCertificate";
		
		$sfAuthToken = json_decode(handleAccessToken('SalesForce','GIFTCERTIFICATE'));
		
		if(!$sfAuthToken->Success)
			$response = $sfAuthToken;
		else
		{
			// INITIALIZE Common SalesForce API Integration details for all calls
			$bearerToken = $sfAuthToken->AccessToken;
			
			$sfConfig = json_decode(RNCPHP\Configuration::fetch('CUSTOM_CFG_SALESFORCE_API_CONFIG')->Value);
			$sfAPI = strpos($_SERVER['HTTP_HOST'], "--") !== false ? $sfConfig->DEV : $sfConfig->PROD;
			
			if(!function_exists("curl_init"))
				load_curl();
			
			// RUN Analytics Report to capture next batch of Incidents to be processed for SalesForce Digital Credit integration flow
			$jobReport = RNCPHP\AnalyticsReport::fetch(116437); // Cronjob: Scheduled Digital Credits
			$jobReportResults = $jobReport->run();
			
			$batchSize = 1;
			$recordsProcessed = 0;
			
			while($nextResult = $jobReportResults->next())
			{
				$country = $nextResult['Country'];
				$incidentID = $nextResult['IncidentID'];
				$stateProvince = $nextResult['State/Province'];
				
				$sfGiftCertificateURL = $sfAPI->CREATEGIFTCERTIFICATE;
				
				// GIFT CERTIFICATE EXPIRATION LOGIC
				/* US - Maryland: 4 years from date of issue
				 * US - Washington: No expiration date
				 * US - All other states/territories: 2 years from date of issue
				 * CANADA - Nova Scotia/Newfoundland and Labrador: No expiration date
				 * CANADA - All other provinces/territories: 2 years from date of issue
				*/
				
				$expirationDate = null;
				if($country == "US")
				{
					$sfGiftCertificateURL .= $sfConfig->SITEID->US;
					$currency = "USD";
					if($stateProvince == "Maryland")
						$expirationDate = date('Y-m-d', strtotime('+4 years'));
					elseif($stateProvince != "Washington")
						$expirationDate = date('Y-m-d', strtotime('+2 years'));
				}
				elseif($country == "CA")
				{
					$sfGiftCertificateURL .= $sfConfig->SITEID->CA;
					$currency = "CAD";
					if($stateProvince != "Nova Scotia" && $stateProvince != "Newfoundland and Labrador")
						$expirationDate = date('Y-m-d', strtotime('+2 years'));
				}
				
				$cURL = curl_init();
				
				// BUILD Unique SalesForce Gift Certificate payload for each Incident				
				$giftCertificatePayloadArray = array(
					"amount" => array(
						"currencyMnemonic" => $currency,
						"value" => floatval(ltrim($nextResult['Amount'], '$'))
					),
					"description" => "SteamShot Digital Credit",
					"message" => "SteamShot Digital Credit",
					"senderName" => "Bissell",
					"recipientName" => $nextResult['First Name'] . " " . $nextResult['Last Name'],
					//"recipientEmail" => $nextResult['Email'],
					"recipientEmail" => "derek.hammons@speridian.com",
					"status" => "pending",
					"orderNo" => strval($nextResult['Reference #']),
					"enabled" => true,
					"c_incidentID" => $incidentID,
					"c_expirationDate" => $expirationDate
				);
			
				$giftCertificatePayload = json_encode($giftCertificatePayloadArray);
				
				curl_setopt_array($cURL, array(
					CURLOPT_URL => $sfGiftCertificateURL,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'PUT',
					CURLOPT_POSTFIELDS => $giftCertificatePayload,
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json',
						'Authorization: Bearer ' . $bearerToken
					),
				));
				
				$giftCertificateResponse = curl_exec($cURL);
				
				$success = false;
				if(curl_errno($cURL))
					$error = $message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
				else
				{
					$giftCertificate = json_decode($giftCertificateResponse);
					$message = print_r($giftCertificate, true);
					if(isset($giftCertificate->title))
						$error = $giftCertificate->title;
					else
					{
						$incident = RNCPHP\Incident::fetch($incidentID);
						$incident->CustomFields->c->merchantid = $giftCertificate->merchantId;
						$incident->CustomFields->c->digital_credit_expiration = $expirationDate;
						
						// CREATE Private Thread entry with SalesForce Digital Credit details.
						$threadContent = "<b>Refund Method:</b> " . $incident->CustomFields->c->steam_shot_refund_method->LookupName . "<br/>";
						$threadContent .= "<b>Date Issued:</b> " . date("m-d-Y", time()) . "<br/>";
						$threadContent .= "<b>Amount:</b> " . $incident->CustomFields->BI->settlement_amount . "<br/>";
						$threadContent .= "<b>Sent To:</b> " . $nextResult['Email'] . "<br/><br/>";
						$threadContent .= "<b>Agent Instructions:</b> For any questions regarding the discount, please see the Digital Credit FAQ and escalate to Grand Rapids for any changes or troubleshooting.<br/>Digital Credit FAQ: <a href='https://support.bissell.com/app/answers/detail/a_id/7248/kw/digital%20credit'>https://support.bissell.com/app/answers/detail/a_id/7248/kw/digital%20credit</a>";
						
						$threadIndex = count($incident->Threads);
						if($threadIndex == 0)
							$incident->Threads = new RNCPHP\ThreadArray();
						$incident->Threads[$threadIndex] = new RNCPHP\Thread();
						$incident->Threads[$threadIndex]->EntryType = new RNCPHP\NamedIDOptList();
						$incident->Threads[$threadIndex]->EntryType->ID = 1; // Private Note
						$incident->Threads[$threadIndex]->ContentType = new RNCPHP\NamedIDOptList();
						$incident->Threads[$threadIndex]->ContentType->ID = 2; // HTML
						$incident->Threads[$threadIndex]->Text = $threadContent;
						
						$incident->save(RNCPHP\RNObject::SuppressAll);
						$success = true;
						$error = 'Digital Credit Successful';
					}
				}
				
				$systemLog = array(
					"Description" => "SalesForce Digital Credit",
					"Error" => $error,
					"ExtSystem" => $sfGiftCertificateURL,
					"IncidentID" => $incidentID,
					"RequestMsg" => print_r(json_decode($giftCertificatePayload), true),
					"ResponseMsg" => $message
				);
				
				logSysIntegration($systemLog);
				curl_close($cURL);
			
				$recordsProcessed++;
				
				if(!$success)
					break;
				if($recordsProcessed >= $batchSize)
					break;
			}
				
			$response = array(
				"Success" => $success,
				"Records" => $recordsProcessed,
				"Message" => $message
			);
		}
	}
	catch(Exception $error)
	{
		$response = array(
			"Success" => false,
			"Message" => $error->getMessage()
		);
	}
	
	echo json_encode($response);
?>