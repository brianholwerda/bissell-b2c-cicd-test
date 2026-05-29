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

class RepairStatusUpdatesController
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
		
		$raDetails = $this->GetIncidentFromRANumber($json['returnAuthorizationNumber']);
		
		if(isset($raDetails)){
			$status = strtoupper($json['status']);
		
			switch($status)
			{
				case "RECEIVED":
					$response = $this->ProductReceivedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "ESTIMATE CREATED":
					$response = $this->EstimateCreatedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "ESTIMATE APPROVED":
					$response = $this->EstimateApprovedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "REPAIR STARTED":
					$response = $this->RepairStartedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "REPAIR ENDED":
					$response = $this->RepairEndedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "SHIPPED":
					$response = $this->ProductShippedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "REPAIR CANCELLED":
					$response = $this->RepairCancelledStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "REPAIR ON HOLD":
					$response = $this->RepairOnHoldStatusUpdate($raDetails->Incident->ID, $json);
					break;
				case "REPAIR HOLD RELEASED":
					$response = $this->RepairHoldReleasedStatusUpdate($raDetails->Incident->ID, $json);
					break;
				default:
					$response = array("responseCode"=>"Error","responseMessage"=>"Invalid Request Method");
					break;
			}
		} else {
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => "Return Authorization Number not found.",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		// $info = array(
			// 'ExtSystem'	=> 'RepairDepotStatusUpdates',
			// 'RequestMsg'	=> $json,
			// 'ResponseMsg'	=> json_encode($response, true),
			// 'Description'	=> $status,
			// 'Error'	=> $response['responseMessage'],
			// 'IncidentID'	=> $raDetails->Incident->ID
		// );
		
		$info = array(
			'ExtSystem'	=> 'RepairDepotStatusUpdates',
			'RequestMsg'	=> $payload,
			'ResponseMsg'	=> json_encode($response, true),
			'Description'	=> 'Repair Update: ' . $status,
			'Error'	=> $response['responseMessage'],
			'IncidentID'	=> $raDetails->Incident->ID
		);
		logSysIntegration($info);
		
		return $response;
	}
	
	public function GetIncidentFromRANumber($raNumber){
		$queryRA = RNCPHP\ROQL::queryObject("SELECT BUS.OrderHeader FROM BUS.OrderHeader WHERE RANumber = " . $raNumber)->next()->next();
		return $queryRA;
	}
	
	public function ProductReceivedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['statusTimestamp']);
			$incident->CustomFields->Repair->WarehouseReceivedDate = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));
			$incident->CustomFields->Repair->RepairStatus = 276;
			
			if($json['condition'] == 'Good'){
				$incident->CustomFields->Repair->PartCondition = 1;
			} else if ($json['condition'] == 'Fair'){
				$incident->CustomFields->Repair->PartCondition = 2;
			} else if ($json['condition'] == 'Poor'){
				$incident->CustomFields->Repair->PartCondition = 3;
			} else if ($json['condition'] == 'Very Poor'){
				$incident->CustomFields->Repair->PartCondition = 4;
			}
			
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Product Received";
			$incident->StatusWithType->Status->ID = 126;
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"phpdate" => (new DateTime('now'))->format('Y-m-d H:i:s')
			);
		}
		
		return $response;
	}
	
	public function EstimateCreatedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			//$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['StatusTimestamp']);
			$incident->CustomFields->Repair->RepairStatus = 296;
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Estimate Created<br/><br/><b>Technician Analysis:</b><br/>" . $json['comments'] . "<br/><br/><b>Estimated Completion Date:</b><br/>" . $json['estimatedCompletionDate'];
			$incident->StatusWithType->Status->ID = 103;
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function EstimateApprovedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			//$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['StatusTimestamp']);
			$incident->CustomFields->Repair->RepairStatus = 297;
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Estimate Approved";
			$incident->StatusWithType->Status->ID = 103;
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function RepairStartedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			//$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->RepairStartDate = $this->convert_date($json['statusTimestamp']);
			$incident->CustomFields->Repair->RepairStartDate = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));
			$incident->CustomFields->Repair->RepairStatus = 3;
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Repair Started";
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function RepairEndedStatusUpdate($incidentID, $json){
		try {
			$faultlvl1 = $this->getNamedValueID("Repair\\FaultTreeLvl1", $json['reasonCode1']);
			$faultlvl2 = $this->getNamedValueID("Repair\\FaultTreeLvl2", $json['reasonCode2']);
			
			if(!isset($faultlvl1) || !isset($faultlvl2)){
				if(!isset($faultlvl1) && !isset($faultlvl2)){
					$faultmessage = "Invalid ReasonCode1 and ReasonCode2 values";
				} else if (!isset($faultlvl1)) {
					$faultmessage = "Invalid ReasonCode1 value";
				} else if (!isset($faultlvl2)) {
					$faultmessage = "Invalid ReasonCode2 value";
				}
				$response = array(
					"responseCode" => "Error",
					"responseMessage" => $faultmessage,
					"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
					"status" => $json['status']
				);
			} else {
				$incident = RNCPHP\Incident::fetch($incidentID);
				$incident->CustomFields->c->send_msg_repaired_product = true;
				
				//$incident->CustomFields->Repair->RepairEndDate = $this->convert_date($json['statusTimestamp']);
				$incident->CustomFields->Repair->RepairEndDate = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));
				$incident->CustomFields->Repair->RepairStatus = 298;
				$incident->Threads = new RNCPHP\ThreadArray();
				$incident->Threads[0] = new RNCPHP\Thread();
				$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
				//Entry type ID 1 is private note
				$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
				$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
				$incident->Threads[0]->ContentType->LookupName = "text/html";
				$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
				//Channel 6 is web note
				$incident->Threads[0]->Channel->ID = 1;
				$incident->Threads[0]->Text = "Repair Depot Update - Repair Ended<br/><br/><b>Repair Completion Notes:</b><br/>" . $json['comments'];
				$incident->CustomFields->Repair->WorkCompleted = $json['comments'];
				$incident->CustomFields->Repair->PartsUsed = $json['partsUsed'];
				$incident->CustomFields->Repair->FaultFound = $json['faultFound'];
				$incident->CustomFields->Repair->FaultLevel1 = $faultlvl1;
				$incident->CustomFields->Repair->FaultLevel2 = $faultlvl2;
				$incident->CustomFields->Repair->RepairAction = 178912;
				
				$incident->save();
				
				$response = array(
					"responseCode" => "Success",
					"responseMessage" => "Success",
					"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
					"status" => $json['status'],
					"debugFields" => array(
						"incidentID" => $incident->ID,
						"referenceNumber" => $incident->ReferenceNumber
					)
				);
			}
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function ProductShippedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			$incident->CustomFields->c->send_msg_repair_shipped = true;
			
			//$incident->CustomFields->Repair->ShippedDate = $this->convert_date($json['statusTimestamp']);
			$incident->CustomFields->Repair->ShippedDate = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));
			$incident->CustomFields->Repair->RepairStatus = 4;
			$incident->CustomFields->Repair->RetCourierReference = $json['trackingNumber'];
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Product Shipped";
			$incident->StatusWithType->Status->ID = 2;
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function RepairCancelledStatusUpdate($incidentID, $json){
		try {
			$faultlvl1 = $this->getNamedValueID("Repair\\FaultTreeLvl1", $json['reasonCode1']);
			$faultlvl2 = $this->getNamedValueID("Repair\\FaultTreeLvl2", $json['reasonCode2']);
			
			if(!isset($faultlvl1) || !isset($faultlvl2)){
				if(!isset($faultlvl1) && !isset($faultlvl2)){
					$faultmessage = "Invalid ReasonCode1 and ReasonCode2 values";
				} else if (!isset($faultlvl1)) {
					$faultmessage = "Invalid ReasonCode1 value";
				} else if (!isset($faultlvl2)) {
					$faultmessage = "Invalid ReasonCode2 value";
				}
				$response = array(
					"responseCode" => "Error",
					"responseMessage" => $faultmessage,
					"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
					"status" => $json['status']
				);
			} else {
				$incident = RNCPHP\Incident::fetch($incidentID);
				//$incident->CustomFields->c->send_msg_product_received = true;
				
				//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['StatusTimestamp']);
				$incident->CustomFields->Repair->RepairEndDate = strtotime((new DateTime('now'))->format('Y-m-d H:i:s'));
				$incident->CustomFields->Repair->RepairStatus = 299;
				$incident->Threads = new RNCPHP\ThreadArray();
				$incident->Threads[0] = new RNCPHP\Thread();
				$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
				//Entry type ID 1 is private note
				$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
				$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
				$incident->Threads[0]->ContentType->LookupName = "text/html";
				$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
				//Channel 6 is web note
				$incident->Threads[0]->Channel->ID = 1;
				$incident->Threads[0]->Text = "Repair Depot Update - Repair Cancelled<br/><br/><b>Cancellation Reason:</b><br/>" . $json['comments'];
				$incident->StatusWithType->Status->ID = 103;
				$incident->CustomFields->Repair->WorkCompleted = $json['comments'];
				$incident->CustomFields->Repair->PartsUsed = $json['partsUsed'];
				$incident->CustomFields->Repair->FaultFound = $json['faultFound'];
				$incident->CustomFields->Repair->FaultLevel1 = $faultlvl1;
				$incident->CustomFields->Repair->FaultLevel2 = $faultlvl2;
				
				if($json['repairAction'] == 'No Problem Found'){
					$incident->CustomFields->Repair->RepairAction = 178914;
				} else if ($json['repairAction'] == 'Needs Replaced'){
					$incident->CustomFields->Repair->RepairAction = 178913;
				}
				
				
				$incident->save();
				
				$response = array(
					"responseCode" => "Success",
					"responseMessage" => "Success",
					"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
					"status" => $json['status'],
					"debugFields" => array(
						"incidentID" => $incident->ID,
						"referenceNumber" => $incident->ReferenceNumber
					)
				);
			}
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function RepairOnHoldStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			//$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['StatusTimestamp']);
			$incident->CustomFields->Repair->RepairStatus = 300;
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Repair on HOLD";
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	public function RepairHoldReleasedStatusUpdate($incidentID, $json){
		try {
			$incident = RNCPHP\Incident::fetch($incidentID);
			//$incident->CustomFields->c->send_msg_product_received = true;
			
			//$incident->CustomFields->Repair->WarehouseReceivedDate = $this->convert_date($json['StatusTimestamp']);
			$incident->CustomFields->Repair->RepairStatus = 301;
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
			$incident->Threads[0]->ContentType->LookupName = "text/html";
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 1;
			$incident->Threads[0]->Text = "Repair Depot Update - Repair HOLD Released";
			$incident->save();
			
			$response = array(
				"responseCode" => "Success",
				"responseMessage" => "Success",
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status'],
				"debugFields" => array(
					"incidentID" => $incident->ID,
					"referenceNumber" => $incident->ReferenceNumber
				)
			);
		}
		catch (Exception $err){
			$response = array(
				"responseCode" => "Error",
				"responseMessage" => $err->getMessage(),
				"returnAuthorizationNumber" => $json['returnAuthorizationNumber'],
				"status" => $json['status']
			);
		}
		
		return $response;
	}
	
	function convert_date($date)
	{
		try {
			$utctime = strtotime(gmdate("Y-m-d\TH:i:s\Z"));
			$regtime = strtotime(date("Y-m-d\TH:i:s\Z"));
			$timediff = $utctime - $regtime;
			return strtotime($date) + $timediff;
		} catch (Exception $e) {
			return '';
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
	
	/** FUNCTION: getPhoneNumber
	 *  Return PhoneNumber for the given Phone Type
	 *	This is required as it is not guaranteed which Phone Number will be in which Phone array index
	 */
	public function getPhoneNumber($contact, $type)
	{
		$number = "";
		foreach($contact->Phones as $phone)
		{
			if($phone->PhoneType->LookupName === $type)
				$number = $phone->Number;
		}
		return $number;
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
