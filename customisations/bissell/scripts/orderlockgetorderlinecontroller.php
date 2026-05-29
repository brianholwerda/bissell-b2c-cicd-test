<?php

/** Derek Hammons
 *  8/16/19
 *  Custom controller for checking Contact Connected and Premium Parts
 *   - Parse POST payload into JSON
 *	 - Initialize CPHP and AgentAuthenticator
 *   - Return
 */
	
// Include helper function for common Web Service functions
require("custom/webservicehelper.php");
	
use RightNow\Utils\Framework;
use RightNow\Connect\v1_3 as RNCPHP;
use RightNow\Utils\Config;

class OrderLockGetLineController
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
	public function getOrderLock()
    {
        header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		try
		{
			if(!isset($json['EBSLineID']) || !isset($json['OrderNumber'])){
				$response = array(
					"Count" => 0,
					"LockOrderLine" => true,
					"ResponseMessage" => "Missing Required Fields"
				);
			} else {
				$query = "SELECT BUS.OrderLocks FROM BUS.OrderLocks WHERE EBSLineID = " . $json['EBSLineID'] . " AND OriginalOrderNumber = '" . $json['OrderNumber'] . "'";
				$locks = RNCPHP\ROQL::query($query)->next();
				
				$response = array(
					"Count" => $locks->count(),
					"LockOrderLine" => ($locks->count() > 0) ? true : false,
					"ResponseMessage" => "Success"
				);
			}
			
			$jsonResponse = json_encode($response, true);
			return $jsonResponse;
		}
		
		catch(Exception $e)
		{
			$response = array(
					"Count" => 0,
					"LockOrderLine" => true,
					"ResponseMessage" => "Error: " . $e->getMessage()
				);
			$jsonResponse = json_encode($response, true);
			return $jsonResponse;
		}
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
