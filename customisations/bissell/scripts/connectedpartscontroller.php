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

class ConnectedPartsController
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
	public function getConnectedParts()
    {
        header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		try
		{
			// Fetch Report
			$report = RNCPHP\AnalyticsReport::fetch(101624);
			
			// Set Home Phone Number filter
			$phoneFilter = new RNCPHP\AnalyticsReportSearchFilter;
			$phoneFilter->Name = 'HomePhone';
			$phoneFilter->Values = array( $json['HomePhone'] );
			
			$phoneFilters = new RNCPHP\AnalyticsReportSearchFilterArray;
			$phoneFilters[] = $phoneFilter;
			
			// Run report
			$reportResults = $report->run(0, $phoneFilters);
			
			// If the report returns 0 results, return No Contact Found empty JSON array
			if($reportResults->count() == 0)
			{
				$emptyResult = array(
					$contactName = "No Contact Found!",
					$contactConnected = "N",
					$contactPremium = "N"
				);
				
				$emptyJSON = json_encode($emptyResult, true);
				return $emptyJSON;
			}
			
			// Results array to ensure only a single instance of each Contact is captured
			$results = array();
			
			// Otherwise, process the returned results
			while($reportRow = $reportResults->next())
			{				
				$contactName = $reportRow['Full Name'];
				$connected = ($reportRow['Connected'] == "Yes") ? "Y" : "N";
				$premium = ($reportRow['Premium'] == "Yes") ? "Y" : "N";
				
				// If we haven't processed this Contact, add it to our final array
				if(!array_key_exists($contactName, $results))
				{
					$newContact = array(
						"Contact" => $contactName,
						"Connected" => $connected,
						"Premium" => $premium
					);
					
					$results[$contactName] = $newContact;
				}
				
				// Otherwise, check if the Connected or Premium fields need updated
				else
				{
					if($connected == "Y")
						$results[$contactName]["Connected"] = "Y";
					if($premium == "Y")
						$results[$contactName]["Premium"] = "Y";
				}
			}
			
			$jsonResponse = json_encode($results, true);
			return $jsonResponse;
		}
		
		catch(Exception $e)
		{
			return FALSE;
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
