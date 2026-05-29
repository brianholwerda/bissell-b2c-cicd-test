<?php	
	require("custom/webservicehelper.php");
	
	// Find our position in the file tree
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}
	
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

	switch($action){
		case 'gettechseelink':
			echo GetTechSeeLink();
			break;
	}
	
	function GetTechSeeLink(){
		try 
		{	
			$context = RNCPHP\ConnectAPI::getCurrentContext();
			$context->ApplicationContext = "TechSee Launcher";
			
			if(isset($_POST['refno']))
				$refno = $_POST['refno'];
			else
				$refno = $_GET['refno'];
			
			if(isset($_POST['agentid']))
				$agentid = $_POST['agentid'];
			else
				$agentid = $_GET['agentid'];
			
			if(isset($_POST['mobilephone']))
				$mobilephone = $_POST['mobilephone'];
			else
				$mobilephone = $_GET['mobilephone'];

			// $paymetricConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_PAYMETRIC_INTEGRATION");
			// $paymetricConfig = (json_decode($paymetricConfig->Value, true));

			// if($paymetricConfig['DEV_ENABLED'] == 0){
				// $url = $paymetricConfig['PROD']['BaseURL'] . '/AccessToken';
				// $merchantGUID = $paymetricConfig['PROD']['MerchantGUID'];
				// $sharedKey = $paymetricConfig['PROD']['SharedKey'];
			// } else {
				// $url = $paymetricConfig['DEV']['BaseURL'] . '/AccessToken';
				// $merchantGUID = $paymetricConfig['DEV']['MerchantGUID'];
				// $sharedKey = $paymetricConfig['DEV']['SharedKey'];
			// }

			$url = 'https://bisuat-api.techsee.me/public/session/live';
			//$merchantGUID = '8270a8c5-c1f8-460b-84b3-e9f72940a3d3';
			//$sharedKey = 'Es4-o!L9/2mBKb+7j8G$6}rMz5A?=c3C';
			$postData = '{"apiKey":"bissell_sdk_key!","apiSecret":"egDkMumsWHnB8QZQ","cid":"' . $refno . '","userName":"' . $agentid . '","to":"' . $mobilephone . '"}';
			
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
					'Authorization: Basic b3JhY2xlSW50VGVjaHNlZTpCaXNzZWxsMTIz',
					'Content-Type: application/json',
					'Cookie: AWSALB=Ti5/WiL7uFG0adbYCProCLE3Nndnxyw/ktWYC1L2o782KiYUL4l94R6TmHIV4YV/uu1hJRSWU2NLZ/fbrjE2PluJJVMNWA2lAtdt422XX2ix2+qmoSw8vuiGSLiC; AWSALBCORS=Ti5/WiL7uFG0adbYCProCLE3Nndnxyw/ktWYC1L2o782KiYUL4l94R6TmHIV4YV/uu1hJRSWU2NLZ/fbrjE2PluJJVMNWA2lAtdt422XX2ix2+qmoSw8vuiGSLiC'
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
					"PAYLOAD" => $postData);
			}
			
			else
			{
				//$result = json_decode($curlResponse);
				$message = "SUCCESS";
				$response = array(
					"SUCCESS" => true,
					"RESULT" => $curlResponse,
					"PAYLOAD" => $postData
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
		
		$info = array(
			'ExtSystem'	=> 'TechSeeIntegration',
			'RequestMsg' => $postData,
			'ResponseMsg' => $curlResponse,
			'Description' => $refno,
			'Error'	=> $message
		);
		logSysIntegration($info);
		
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header("Access-Control-Allow-Headers: X-Requested-With");
				
		$json = json_encode($response);
		//echo $json;
		return $json;
	}
?>