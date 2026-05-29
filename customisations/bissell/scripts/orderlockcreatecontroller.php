<?php

/** Derek Hammons
 *  9/22/17
 *  Custom controller for the Bissell Contact Upsert Process
 *   - Parse POST payload into JSON
 *	 - Initialize CPHP and AgentAuthenticator
 *   - Create or Update Contact record
 */
	
// Include helper function for common Web Service functions
require("custom/webservicehelper.php");
	
use RightNow\Utils\Framework;
use RightNow\Connect\v1_3 as RNCPHP;
use RightNow\Utils\Config;

class CreateOrderLockController
{  
    public function __construct()
    {

    }
	
	/** FUNCTION: processUpsert
	 *  Process the JSON payload
	 *  Create a new Contact if one is not found in the system based on ContactID or Email
	 *  Update an existing Contact if one is found by ContactID or Email
	 *  Missing email fails and returns HTTP 400
	 */
	 
	public function processCreateOrderLock()
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		try
		{
			// // Create BUS$SysIntegrationLog record
			$ordLock = new RNCPHP\BUS\OrderLocks();
			
			if(isset($json['Source'])){
				$ordLock->Source = $json['Source'];
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'Source' field missing"
				);
				return $response;
			}
				
			if(isset($json['OriginalOrderNumber'])){
				$ordLock->OriginalOrderNumber = $json['OriginalOrderNumber'];
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'OriginalOrderNumber' field missing"
				);
				return $response;
			}
				
			if(isset($json['SKU']))
				$ordLock->SKU = $json['SKU'];
			
			if(isset($json['RefundID']))
				$ordLock->RefundID = $json['RefundID'];
			
			if(isset($json['EBSLineID']) && $json['EBSLineID'] != 0){
				$ordLock->EBSLineID = $json['EBSLineID'];
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'EBSLineID' field missing"
				);
				return $response;
			}
				
			if(isset($json['NewOrderNumber']))
				$ordLock->NewOrderNumber = $json['NewOrderNumber'];
			if(isset($json['IncidentID']))
				$ordLock->IncidentID = RNCPHP\Incident::fetch($json['IncidentID']);
			// // if(isset($info['ContactID']))
				// // $sysLog->Contact = RNCPHP\Contact::fetch($info['ContactID']);
			if(isset($json['OrderHeaderID']))
				$ordLock->OrderHeaderID = RNCPHP\BUS\OrderHeader::fetch($json['OrderHeaderID']);
			
			$ordLock->save();
		}
		catch(Exception $e)
		{
			// file_put_contents("/tmp/debug.txt", print_r($e, true));
			// return $e;
			$response = array(
				"OrderLockID" => null,
				"ResponseMessage" => "Exception: " . $e->getMessage()
			);
			return $response;
		}
		
		
		$lockResult = array(
			"OrderLockID" => $ordLock->ID,
			"ResponseMessage" => "Success"
		);
		
		//$responseJSON = json_encode($testResult, true);
		//return $responseJSON;
		return $lockResult;
	}
	
	/** FUNCTION: Initialize
	 *  Grab the custom HTTP_X_HTTP_AUTHORIZATION header
	 *  Decode the string for AgentAuthenticator username:password
	 *  Missing header fails and returns HTTP 401
	 */
	public function Initialize()
	{
		try
		{
			// Grab the Basic Authorization base64 encoded string
			if(isset($_SERVER['HTTP_X_HTTP_AUTHORIZATION']))
				$basic = $_SERVER['HTTP_X_HTTP_AUTHORIZATION'];
			else
				return FALSE;
			
			// Decode string into username:password
			$basic = base64_decode($basic);
			$pieces = explode(":", $basic);
			$user = $pieces[0];
			$pass = $pieces[1];
			
			// Satisfy AgentAuthenticator
			$account = AgentAuthenticator::authenticateCredentials($user, $pass);
			initConnectAPI();
			return TRUE;
		}
		
		catch(RNCPHP\ConnectAPIError $err)
		{
			return $err;
		}
	}
	
	/** FUNCTION: getPostData
	 *  Read and return raw POST data
	 */
	private function getPostData()
	{
        if(!empty($this->post_payload))
            return $this->post_payload;
        else
            $this->post_payload = file_get_contents('php://input');
		
        return $this->post_payload;
    }
}
?>
