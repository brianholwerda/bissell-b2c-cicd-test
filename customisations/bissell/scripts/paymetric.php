<?php	
	// Capture POST payload from BUI Extension
	if(isset($_GET['sid']) || isset($_POST['sid']))
	{
		if(isset($_POST['sid']))
			$sessionID = $_POST['sid'];
		else
			$sessionID = $_GET['sid'];
	}
	// BUI PAYLOAD
	else
	{
		$postJSON = json_decode(file_get_contents('php://input'));
		$sessionID = $postJSON->Session;
	}

	// Capture POST payload from BUI Extension
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
		$postJSON = json_decode(file_get_contents('php://input'));
		$action = $postJSON->Action;
	}
	
	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($sessionID);
	initConnectAPI();
	use RightNow\Connect\v1_4 as RNCPHP;
	use RightNow\Connect\Crypto\v1_4 as Crypto;

	switch($action){
		case 'generateaccesstoken':
			echo GenerateAccessToken();
			break;
		case 'getresponsepacket':
			if(isset($_POST['accesstoken']))
				$accessToken = $_POST['accesstoken'];
			else
				$accessToken = $_GET['accesstoken'];

			echo GetResponsePacket($accessToken);
			break;
	}
	
	function GenerateAccessToken(){
		try 
		{	
			$context = RNCPHP\ConnectAPI::getCurrentContext();
			$context->ApplicationContext = "Paymetric Tokenization";

			$paymetricConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_PAYMETRIC_INTEGRATION");
			$paymetricConfig = (json_decode($paymetricConfig->Value, true));

			if($paymetricConfig['DEV_ENABLED'] == 0){
				$url = $paymetricConfig['PROD']['BaseURL'] . '/AccessToken';
				$merchantGUID = $paymetricConfig['PROD']['MerchantGUID'];
				$sharedKey = $paymetricConfig['PROD']['SharedKey'];
			} else {
				$url = $paymetricConfig['DEV']['BaseURL'] . '/AccessToken';
				$merchantGUID = $paymetricConfig['DEV']['MerchantGUID'];
				$sharedKey = $paymetricConfig['DEV']['SharedKey'];
			}

			//$url = 'https://cert-xiecomm.paymetric.com/DIeComm/AccessToken';
			//$merchantGUID = '8270a8c5-c1f8-460b-84b3-e9f72940a3d3';
			//$sharedKey = 'Es4-o!L9/2mBKb+7j8G$6}rMz5A?=c3C';
			
			$xmlData = createCustomIFrameXML();
			
			$raw = custom_hmac('standard_crypt', $xmlData, $sharedKey);
			$base64 = base64_encode($raw);
			
			$urlSignature = urlencode($base64);
			$urlPacketXML = urlencode($xmlData);

			//$postData = "MerchantGuid=" . $merchantGUID . "&SessionRequestType=4&Signature=" . $urlSignature . "&MerchantDevelopmentEnvironment=php&Packet=" . $urlPacketXML;
			$postData = "MerchantGuid=" . $merchantGUID . "&SessionRequestType=1&Signature=" . $urlSignature . "&MerchantDevelopmentEnvironment=php&Packet=" . $urlPacketXML;
			
			load_curl();
			$cURL = curl_init();
			
			curl_setopt_array($cURL, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $postData,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/x-www-form-urlencoded"
				)
			));
			
			$curlResponse = curl_exec($cURL);
			$error = false;
			
			file_put_contents("/tmp/debug.txt", "TEST:" . PHP_EOL . $curlResponse);
			
			// Stop if curl error is encountered, otherwise process XML response
			if ($curlErrNo = curl_errno($cURL))
			{
				$error = true;
				$errorMessage = curl_strerror($curlErrNo);
				$message = "cURL Error: " . $errorMessage;
				$response = array(
					"SUCCESS" => false,
					"MESSAGE" => $message);
			}
			
			else
			{
				//$result = json_decode($curlResponse);
				$response = array(
					"SUCCESS" => true,
					"RESULT" => $curlResponse,
					"XMLDATA" => $xmlData,
					"URLPACKETXML" => $urlPacketXML,
					"POSTDATA" => $postData,
					"SIGNATURE" => $urlSignature,
					"SERVERNAME" => $_SERVER['SERVER_NAME'],
					"BILLTONAME" => $billtoname
				);
			}
			
			curl_close($cURL);
		}
		
		catch(\Exception $e)
		{
			$message = $e->getMessage();
			$response = array(
				"SUCCESS" => false,
				"MESSAGE" => $message
				);
		}
		
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header("Access-Control-Allow-Headers: X-Requested-With");
				
		$json = json_encode($response);
		//echo $json;
		return $json;
	}
	
	function GetResponsePacket($accessToken){
		try
		{
			$context = RNCPHP\ConnectAPI::getCurrentContext();
			$context->ApplicationContext = "Paymetric Tokenization";

			$paymetricConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_PAYMETRIC_INTEGRATION");
			$paymetricConfig = (json_decode($paymetricConfig->Value, true));

			if($paymetricConfig['DEV_ENABLED'] == 0){
				$url = $paymetricConfig['PROD']['BaseURL'] . '/ResponsePacket';
				$merchantGUID = $paymetricConfig['PROD']['MerchantGUID'];
				$sharedKey = $paymetricConfig['PROD']['SharedKey'];
			} else {
				$url = $paymetricConfig['DEV']['BaseURL'] . '/ResponsePacket';
				$merchantGUID = $paymetricConfig['DEV']['MerchantGUID'];
				$sharedKey = $paymetricConfig['DEV']['SharedKey'];
			}
			
			//$url = 'https://cert-xiecomm.paymetric.com/DIeComm/ResponsePacket';
			//$merchantGUID = '8270a8c5-c1f8-460b-84b3-e9f72940a3d3';
			//$sharedKey = 'Es4-o!L9/2mBKb+7j8G$6}rMz5A?=c3C';

			$raw = custom_hmac('standard_crypt', $accessToken, $sharedKey);
			$base64 = base64_encode($raw);
			
			$urlSignature = urlencode($base64);
			
			$getData = "?MerchantGUID=" . $merchantGUID . "&Signature=" . $urlSignature . "&AccessToken=" . $accessToken;
			
			load_curl();
			$cURL = curl_init();
			
			curl_setopt_array($cURL, array(
				CURLOPT_URL => $url . $getData,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/x-www-form-urlencoded"
				)
			));
			
			$curlResponse = curl_exec($cURL);
			$error = false;
			
			file_put_contents("/tmp/debug.txt", "TEST:" . PHP_EOL . $curlResponse);
			
			// Stop if curl error is encountered, otherwise process XML response
			if ($curlErrNo = curl_errno($cURL))
			{
				$error = true;
				$errorMessage = curl_strerror($curlErrNo);
				$message = "cURL Error: " . $errorMessage;
				$response = array(
					"SUCCESS" => false,
					"MESSAGE" => $message,
					"GETDATA" => $getData,
					"GENERATEDSIG" => $urlSignature,
					"SERVERNAME" => $_SERVER['SERVER_NAME']
				);
			}
			
			else
			{
				//$result = json_decode($curlResponse);
				$response = array(
					"SUCCESS" => true,
					"RESULT" => $curlResponse,
					"GENERATEDSIG" => $urlSignature
				);
			}
			
			curl_close($cURL);
		}
		
		catch(\Exception $e)
		{
			$message = $e->getMessage();
			$response = array(
				"SUCCESS" => false,
				"MESSAGE" => $message
				);
		}

		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header("Access-Control-Allow-Headers: X-Requested-With");
				
		$json = json_encode($response);
		//echo $json;
		return $json;
	}

	function createCustomIFrameXML(){
		if(isset($_POST['billtoname']))
			$urlBillToName = $_POST['billtoname'];
		else
			$urlBillToName = $_GET['billtoname'];

		$billtoname = base64_decode($urlBillToName);
		
		$payloadXML = '<?xml version="1.0" encoding="utf-8"?>';
		$payloadXML .= '<merchantHtmlPacketModel xmlns="Paymetric:XiIntercept:MerchantHtmlPacketModel">';
			$payloadXML .= '<iFramePacket>';
				$payloadXML .= '<hostUri>https://'. $_SERVER['SERVER_NAME'] .'/</hostUri>';
				$payloadXML .= '<cssUri>https://'. $_SERVER['SERVER_NAME'] .'/euf/assets/themes/standard/paymetric.css</cssUri>';
			$payloadXML .= '</iFramePacket>';
			$payloadXML .= '<htmlFieldValues>';
				$payloadXML .= '<field for="cardholderName" value="'. $billtoname .'" />';
			$payloadXML .= '</htmlFieldValues>';
			$payloadXML .= '<merchantHtml>';
				$payloadXML .= '<htmlSection class="merchant_paycontent" xmlns="Paymetric:XiIntercept:MerchantHtmlPacketModel">';
				$payloadXML .= '<cardDropdownSection>';
						$payloadXML .= '<tag name="div" class="PaymentDetailHeader">';
							$payloadXML .= 'Enter Credit Card Information';
						$payloadXML .= '</tag>';
						$payloadXML .= '<tag name="div" class="margin5pxTopBottom">';
							$payloadXML .= '<label for="cardholderName" text="Name" class="labelFields" />';
							$payloadXML .= '<tboxCardHolderName class="inputFields" />';
							$payloadXML .= '<validationMsg for="cardholderName" class="valmsg" />';
						$payloadXML .= '</tag>';
						$payloadXML .= '<tag name="div" class="margin5pxTopBottom">';
							$payloadXML .= '<label for="cardType" text="Card Type" class="labelFields" />';
							$payloadXML .= '<ddlCardType id="cd" class="inputFields">';
								$payloadXML .= '<items>';
									$payloadXML .= '<item for="american express" />';
									$payloadXML .= '<item for="mastercard" />';
									$payloadXML .= '<item for="visa" />';
									$payloadXML .= '<item for="discover" />';
								$payloadXML .= '</items>';
							$payloadXML .= '</ddlCardType>';
						$payloadXML .= '</tag>';
						$payloadXML .= '<tag name="div" class="margin5pxTopBottom">';
							$payloadXML .= '<label for="cardNumber" text="Card #" class="labelFields" />';
							$payloadXML .= '<tboxCardNumber tokenize="true" class="inputFields" luhn-check="true" luhn-check-msg="Invalid Card Number Entered" />';
							$payloadXML .= '<validationMsg for="cardNumber" class="valmsg" />';
						$payloadXML .= '</tag>';
						$payloadXML .= '<tag name="div" class="margin5pxTopBottom">';
							$payloadXML .= '<label for="expMonth" text="Expiration" class="labelFields" />';
							$payloadXML .= '<ddlExpMonth default-text="month" class="merchant_combos expInputFields" required="false" />';
							$payloadXML .= '<ddlExpYear default-text="year" class="merchant_combos expInputFields" required="false" exp-date="true" />';
							$payloadXML .= '<validationMsg for="expYear" class="valmsg" />';
						$payloadXML .= '</tag>';
						$payloadXML .= '<tag name="div" class="margin5pxTopBottom">';
							$payloadXML .= '<label for="cvv" text="CVV Code" class="labelFields" />';
							$payloadXML .= '<tboxCvv class="expInputFields" minlength="3" maxlength="4" minlength-msg="Invalid CVV Code" maxlength-msg="Invalid CVV Codes" digits-only="true" />';
							$payloadXML .= '<validationMsg for="cvv" class="valmsg" />';
						$payloadXML .= '</tag>';
					$payloadXML .= '</cardDropdownSection>';
				$payloadXML .= '</htmlSection>';
			$payloadXML .= '</merchantHtml>';
		$payloadXML .= '</merchantHtmlPacketModel>';

		return $payloadXML;
	}

	// HASH 256 functionality
	function standard_crypt($msg)
	{
		 $md = new Crypto\MessageDigest();
		 $md->Algorithm->ID = 3; // SHA256
		 $md->Text = $msg;
		 $md->hash();
		 $hashed_text = $md->HashText;
		 return $hashed_text;
	}
	
	// Create Signature Hash
	function custom_hmac($algo, $data, $key, $raw_output = false)
	{
		$size = 64;
		$pack = chr(0x00);
		if (strlen($key) > $size)
			$key = $algo($key);
		else
			$key = $key . str_repeat(chr(0x00), $size - strlen($key));
		
		// Outter and Inner pad
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);

		$k_ipad = $ipad ^ $key;
		$k_opad = $opad ^ $key;
		return $algo($k_opad.$algo($k_ipad.$data));
	}
?>