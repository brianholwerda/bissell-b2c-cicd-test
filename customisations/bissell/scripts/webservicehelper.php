<?php
	use RightNow\Connect\v1_3 as RNCPHP;
	
	/***** NEW REST API INTEGRATION FUNCTIONS *****/
	function handleAccessToken($application, $scope)
	{
		try
		{
			$now = time();
			
			switch($application)
			{
				case 'SalesForce':
					$tokenConfig = json_decode(RNCPHP\Configuration::fetch('CUSTOM_CFG_SALESFORCE_TOKEN_AUTH')->Value);
					break;
				case 'Vertex':
					$tokenConfig = json_decode(RNCPHP\Configuration::fetch('CUSTOM_CFG_VERTEX_TOKEN_AUTH')->Value);
					break;
				default:
					$systemLog = array(
						"ExtSystem" => $application . " Access Token Request",
						"RequestMsg" => "Application: " . $application . PHP_EOL . "Scope: " . $scope,
						"ResponseMsg" => "Invalid application, expecting Vertex or SalesForce",
						"Description" => "Invalid application"
					);
					
					logSysIntegration($systemLog);
				
					$response = array(
						'Success' => false,
						'Message' => "Invalid application"
					);
					
					return json_encode($response);
					break;
			}
			
			// Toggle between DEV/PROD based on runtime environment
			$tokenAPI = strpos($_SERVER['HTTP_HOST'], "--") !== false ? $tokenConfig->DEV : $tokenConfig->PROD;
			$tokenURL = $tokenAPI->URL;
			if($application == "Vertex")
				$tokenScope = $tokenConfig->SCOPES->$scope;
			else
				$tokenScope = strpos($_SERVER['HTTP_HOST'], "--") !== false ? $tokenConfig->DEV->SCOPES->$scope : $tokenConfig->PROD->SCOPES->$scope;
			
			// GET AUTH$Token and process
			$tokenQuery = "Application = '" . $application . "' AND Scope";
			$tokenQuery .= trim($tokenScope) != "" ? " = '" . $tokenScope . "'" : " IS NULL";

			$authToken = RNCPHP\AUTH\Token::first($tokenQuery);
			
			if(!isset($authToken))
				$authToken = new RNCPHP\AUTH\Token();
			
			// AUTH$Token has expired, need to create a new one.
			$success = true;
			if($now > $authToken->Expiration)
			{	
				$tokenPayload = "scope=" . $tokenScope;
				$tokenPayload .= "&grant_type=" . $tokenAPI->GRANTTYPE;
				
				if($application == 'Vertex')
				{
					$tokenPayload .= "&client_id=" . $tokenAPI->CLIENTID;
					$tokenPayload .= "&client_secret=" . $tokenAPI->CLIENTSECRET;
				}

				if(!function_exists("curl_init"))
					load_curl();
	
				$cURL = curl_init();
				
				curl_setopt_array($cURL, array(
					CURLOPT_URL => $tokenURL,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 10,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => $tokenPayload,
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/x-www-form-urlencoded'
					),
				));
				
				if($application == 'SalesForce')
					curl_setopt($cURL, CURLOPT_USERPWD, $tokenAPI->CLIENTID . ":" . $tokenAPI->CLIENTSECRET);  
				
				$tokenResponse = curl_exec($cURL);
				
				if(curl_errno($cURL))
				{
					$success = false;
					$message = "Curl Error:"  . curl_errno($cURL) . " - " . curl_strerror(curl_errno($cURL));
				}
				
				else
				{
					$token = json_decode($tokenResponse);
					if(isset($token->error))
					{
						$success = false;
						$message = $token->error;
					}
					elseif(!isset($token->access_token))
					{
						$success = false;
						$message = $token;
					}
					else
					{	
						$message = print_r($token, true);
						$authToken->AccessToken = $token->access_token;
						$authToken->Application = $application;
						$authToken->Expiration = ($now + $token->expires_in) - 300; // GIVE 5 minute grace period to generate new OAuth token before it truly expires
						$authToken->Scope = trim($tokenScope) == "" ? null : $tokenScope;
						$authToken->save(RNCPHP\RNobject::SuppressAll);
					}
				}
				
				$systemLog = array(
					"ExtSystem" => $tokenURL,
					"RequestMsg" => $tokenPayload,
					"ResponseMsg" => $message,
					"Description" => $application . " Access Token Request",
				);
				
				if(!$success)
					$systemLog["Error"] = $message;
				
				logSysIntegration($systemLog);
				curl_close($cURL);
			}
			
			$response = array(
				"Success" => $success
			);
			
			if($success)
				$response["AccessToken"] = $authToken->AccessToken;
			else
				$response["Message"] = $message;
		} 
		catch (\Exception $e)
		{
			$response = array(
				'Success' => false,
				'Message' => $e->getMessage()
			);
		}

		return json_encode($response);
	}
	
	
	
	/***** LEGACY SOAP FUNCTIONS *****/
	// Creates generic security header for Bissell SOAP XML request, takes in username/password for the given Bissell service
	function createHeader($username, $password)
	{
		// Get Unix timestamp for now (created) and now + 5 mintes (expired)
		$now = time();
		$then = $now + (5 * 60);
		
		// Parse created timestamp
		$created = gmdate("Y-m-d H:i:s", $now);
		$pieces = explode (" ", $created);
		$created = $pieces[0] . "T" . $pieces[1] . ".001Z";
		
		// Parse expired timestamp
		$expires = gmdate("Y-m-d H:i:s", $then);
		$pieces = explode (" ", $expires);
		$expires = $pieces[0] . "T" . $pieces[1] . ".001Z";
		
		$soapHeader = 
		'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
			<s:Header>
				<o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					<u:Timestamp u:Id="_0">
						<u:Created>' . $created . '</u:Created>
						<u:Expires>' . $expires . '</u:Expires>
					</u:Timestamp>
					<o:UsernameToken u:Id="uuid-cd0b8ade-79ba-481a-a5a2-f56d66e3fa00-1">
						<o:Username>' . $username . '</o:Username>
						<o:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' .  $password . '</o:Password>
					</o:UsernameToken>
				</o:Security>
			</s:Header>';
			
		return $soapHeader;
	}
	
	// POSTs a generic cURL call to Bissell given the webservice WSDL, webservice function call, and SOAP XML request payload
	function curlExec($bwsConfig, $soapWSDL, $function, $xml)
	{
		if (!function_exists("curl_init"))
		   load_curl();
	   
		// Grab either PROD or DEV wsdl and create cURL endpoint
		if($bwsConfig['DEV_ENABLED'] == 0)
			$soapURL = $bwsConfig['PROD_PREFIX'] . $soapWSDL;
		else
			$soapURL = $bwsConfig['DEV_PREFIX'] . $soapWSDL;
		
		// // Handle Exact eNews web service
		// if($soapWSDL == "DMSServices")
		// {
			// if($bwsConfig['DEV_ENABLED'] == 0)
				// $soapURL = $bwsConfig['ENEWS']['PROD'];
			// else
				// $soapURL = $bwsConfig['ENEWS']['DEV'];
		// }
		
		// Handle Exact eNews web service
		if($soapWSDL == "DMSServicesUS" || $soapWSDL == "DMSServicesCA")
		{
			if($bwsConfig['DEV_ENABLED'] == 0)
				$soapURL = $bwsConfig['ENEWS']['PROD'][substr($soapWSDL,strlen($soapWSDL)-2,strlen($soapWSDL))];
			else
				$soapURL = $bwsConfig['ENEWS']['DEV'][substr($soapWSDL,strlen($soapWSDL)-2,strlen($soapWSDL))];
		}
		
		// Handle FedEx return shipment label
		if($soapWSDL == "FEDEX")
		{
			if($bwsConfig['DEV_ENABLED'] == 0)
				$soapURL = $bwsConfig['FEDEX']['PROD']['URL'];
			else
				$soapURL = $bwsConfig['FEDEX']['DEV']['URL'];
		}
		
		$cURL = curl_init();
		
		if($cURL)
		{
			curl_setopt($cURL, CURLOPT_URL, $soapURL);
			curl_setopt($cURL, CURLOPT_HEADER, 0);
			curl_setopt($cURL, CURLOPT_TIMEOUT, 300);
			curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cURL, CURLOPT_POST, 1);
			curl_setopt($cURL, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array (
				'Content-type: text/xml; charset="UTF-8"',
				'SOAPAction: "' . $function . '"'));

			$result = curl_exec($cURL);
			
			// Stop if curl error is encountered, otherwise process XML response
			if ($curlErrNo = curl_errno($cURL))
			{
				$curlError = curl_strerror($curlErrNo);
				$error = true;
			}
			
			curl_close($cURL);
			
			if($error)
				$result = array( 'CurlErrorCode' => $curlErrNo, 'CurlErrorMsg' => $curlError);
			
			return $result;
		}
		else
			return "Unable to initialize CURL";
	}
	
	// POSTs a generic cURL call to Bissell given the webservice WSDL, webservice function call, and SOAP XML request payload
	function curlExecJSON($json)
	{
		if (!function_exists("curl_init"))
		   load_curl();
	   
		$soapURL = 'https://api.addressy.com/Cleansing/International/Batch/v1.00/json4.ws';
		
		$cURL = curl_init();
		
		if($cURL)
		{
			curl_setopt($cURL, CURLOPT_URL, $soapURL);
			curl_setopt($cURL, CURLOPT_HEADER, 0);
			curl_setopt($cURL, CURLOPT_TIMEOUT, 300);
			curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cURL, CURLOPT_POST, 1);
			curl_setopt($cURL, CURLOPT_POSTFIELDS, $json);
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			$result = curl_exec($cURL);
			
			// Stop if curl error is encountered, otherwise process XML response
			if ($curlErrNo = curl_errno($cURL))
			{
				$curlError = curl_strerror($curlErrNo);
				$error = true;
			}
			
			curl_close($cURL);
			
			if($error)
				$result = array( 'CurlErrorCode' => $curlErrNo, 'CurlErrorMsg' => $curlError);
			
			return $result;
		}
		else
			return "Unable to initialize CURL";
	}
	
	function curlExecTechSee($json)
	{
		if (!function_exists("curl_init"))
		   load_curl();
	   
		$soapURL = 'https://bisuat-api.techsee.me/public/session/live';
		
		$cURL = curl_init();
		
		if($cURL)
		{
			curl_setopt($cURL, CURLOPT_URL, $soapURL);
			curl_setopt($cURL, CURLOPT_HEADER, 0);
			curl_setopt($cURL, CURLOPT_TIMEOUT, 300);
			//curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cURL, CURLOPT_POST, 1);
			curl_setopt($cURL, CURLOPT_POSTFIELDS, $json);
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Authorization: Basic b3JhY2xlSW50VGVjaHNlZTpCaXNzZWxsMTIz','Content-Type: application/json'));

			$result = curl_exec($cURL);
			
			// Stop if curl error is encountered, otherwise process XML response
			if ($curlErrNo = curl_errno($cURL))
			{
				$curlError = curl_strerror($curlErrNo);
				$error = true;
			}
			
			curl_close($cURL);
			
			if($error)
				$result = array( 'CurlErrorCode' => $curlErrNo, 'CurlErrorMsg' => $curlError);
			
			return $result;
		}
		else
			return "Unable to initialize CURL";
	}
	
	// Creates a new BUS$SysIntegrationLog debug record for OSvC/Bissell web service call
	function logSysIntegration($info)
	{				
		try
		{
			// Create BUS$SysIntegrationLog record
			$sysLog = new RNCPHP\BUS\SysIntegrationLog();
			
			if(isset($info['ExtSystem']))
				$sysLog->ExtSystem = $info['ExtSystem'];
			if(isset($info['RequestMsg']))
				$sysLog->RequestMsg = sanitize($info['RequestMsg']);
			if(isset($info['ResponseMsg']))
				$sysLog->ResponseMsg = sanitize($info['ResponseMsg']);
			if(isset($info['Description']))
				$sysLog->Description = $info['Description'];
			if(isset($info['Error']))
				$sysLog->Error = $info['Error'];
			if(isset($info['IncidentID']))
				$sysLog->Incident = RNCPHP\Incident::fetch($info['IncidentID']);
			if(isset($info['ContactID']))
				$sysLog->Contact = RNCPHP\Contact::fetch($info['ContactID']);
			if(isset($info['OrderHeaderID']))
				$sysLog->OrderHeader = RNCPHP\BUS\OrderHeader::fetch($info['OrderHeaderID']);
			
			$sysLog->save();
		}
		catch(Exception $e)
		{
			file_put_contents("/tmp/debug.txt", print_r($e, true));
			return $e;
		}
	}
	
	// Creates a new BUS$TransactionLog record 
	function logTransaction($info)
	{			
		try
		{
			// Create BUS$SysIntegrationLog record
			$transLog = new RNCPHP\BUS\TransactionLog();
			
			if(isset($info['LogAction']))
				$transLog->LogAction = $info['LogAction'];
			if(isset($info['Comment']))
				$transLog->Comment = $info['Comment'];
			if(isset($info['IncidentID']))
				$transLog->Incident = RNCPHP\Incident::fetch($info['IncidentID']);
			if(isset($info['ContactID']))
				$transLog->Contact = RNCPHP\Contact::fetch($info['ContactID']);
			if(isset($info['OrderHeaderID']))
				$transLog->OrderHeader = RNCPHP\BUS\OrderHeader::fetch($info['OrderHeaderID']);
			
			$transLog->save();
		}
		catch(Exception $e)
		{
			return $e;
		}
	}
	
	function saveOrderHeaderFields($info)
	{			
		try
		{
			$orderHeader = RNCPHP\BUS\OrderHeader::fetch($info['OrderHeaderID']);
			
			if(isset($info['OrderNumber']))
				$orderHeader->OrderNumber = $info['OrderNumber'];
			if(isset($info['SourceOrderNumber']))
				$orderHeader->SourceOrderNumber = $info['SourceOrderNumber'];
			if(isset($info['Status']))
				$orderHeader->Status = $info['Status'];
			if(isset($info['LockTrans']))
				$orderHeader->LockTrans = $info['LockTrans'];
			
			$orderHeader->save();
		}
		catch(Exception $e)
		{
			return $e;
		}
	}
	
	function saveRAHeaderFields($info)
	{			
		try
		{
			$orderHeader = RNCPHP\BUS\OrderHeader::fetch($info['OrderHeaderID']);
			
			if(isset($info['RANumber']))
				$orderHeader->RANumber = $info['RANumber'];
			if(isset($info['SourceOrderNumber']))
				$orderHeader->SourceOrderNumber = $info['SourceOrderNumber'];
			if(isset($info['Status']))
				$orderHeader->Status = $info['Status'];
			if(isset($info['LockTrans']))
				$orderHeader->LockTrans = $info['LockTrans'];
			if(isset($info['TrackingNumber']))
				$orderHeader->RATrackingNumber = $info['TrackingNumber'];
			
			$orderHeader->save();
		}
		catch(Exception $e)
		{
			return $e;
		}
	}
	
	function createFileLog($noteText)
	{
		file_put_contents("/tmp/snow_" . date("Ymd") .".txt", $noteText . "\r\n", FILE_APPEND);
	}
	
	// Redacts sensitive information before logging new BUS$SysIntegrationLog record
	function sanitize($clean)
	{	
		// Pull all tags to be redacted
		try
		{
			$tags = RNCPHP\Configuration::fetch("CUSTOM_CFG_LOG_REDACTED_TAGS");
			$tags = (json_decode($tags->Value, true));
		}
		
		catch(Exception $err)
		{
			return $err;
		}
		
		// Iterate through each tag and if found in the data, redact
		foreach($tags['TAGS'] as $tag)
			$clean = preg_replace($tag['MATCH'], $tag['REPLACE'], $clean);
			
		return $clean;
	}
?>