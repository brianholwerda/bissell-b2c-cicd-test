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

class OrderDataUpdatesController
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
	 
	 
	public function BeginUpdate(){
		header('Content-Type: application/json');
		header("Access-Control-Allow-Methods: POST,PATCH,DELETE");
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
	
		$response = array(
			"responseCode" => "Success",
			"responseMessage" => "Success"
		);
		
		// $info = array(
			// 'ExtSystem'	=> 'RepairDepotStatusUpdates',
			// 'RequestMsg'	=> $payload,
			// 'ResponseMsg'	=> json_encode($response, true),
			// 'Description'	=> 'Repair Update: ' . $status,
			// 'Error'	=> $response['responseMessage'],
			// 'IncidentID'	=> $raDetails->Incident->ID
		// );
		// logSysIntegration($info);
		
		return $response;
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
	
	/** FUNCTION: getNamedValueID
	 *  PARAM: $object String name of the NamedID object to fetch
	 *  PARAM: $match String name for the NamedID.Name field to match
	 *  Returns the NamedID.ID for the given NamedID.Name
	 *d
	 */
	private function getNamedValueID($object, $match, $countryID = null)
	{	
		$values = RNCPHP\ConnectAPI::getNamedValues("RightNow\\Connect\\v1_3\\" . $object);
		foreach($values as $value)
		{
			if($value->LookupName === $match)
			{
				$id = $value->ID;
				break;
			}
		}

		return $id;
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
