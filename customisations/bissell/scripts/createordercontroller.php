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

class CreateOrderController
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
	
	
	public function processIncidentCreate()
	{
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		try
		{
			// BUS$SysIntegrationLog debugger
			$sysLogConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_DEBUG_SYSINTEGRATION");
			$debug = $sysLogConfig->Value;
		}
		
		catch(Exception $e)
		{
			$response = array(
				"ID" => null,
				"ReferenceNumber" => null,
				"ResponseMessage" => "Exception: " . $e->getMessage()
			);
			return $response;
		}
		
		try{
			$isRefundIncident = false;
			
			if(isset($json['IncidentID']) && $json['IncidentID'] != 0){
				$incident = RNCPHP\Incident::fetch($json['IncidentID']);
			} else {
				$incident = new RNCPHP\Incident();
		
				$languageID = 1;
				$interfaceID = 1;
				$incident->Language = new RNCPHP\NamedIDOptList();
				$incident->Language->ID = $languageID;
				$incident->Interface = RNCPHP\SiteInterface::fetch($interfaceID);
				$incident->SiteInterface->ID = $interfaceID;
				
				$incident->PrimaryContact = RNCPHP\Contact::fetch($json['CID']);//Required field to create an incident through connect PHP

				$incident->Severity = new RNCPHP\NamedIDOptList();
				$incident->Severity->LookupName  = "Normal";
				$incident->StatusWithType->Status->ID = 1;
				
				$incident->CustomFields->c->incident_source = new RNCPHP\NamedIDLabel();
				$incident->CustomFields->c->incident_source->LookupName = "Phone";
			}
			
			if($json['IncidentType'] == "Refund"){
				$incident->CustomFields->c->incident_type = new RNCPHP\NamedIDLabel();
				$incident->CustomFields->c->incident_type->LookupName = "Refund";
				$incident->CustomFields->c->order_number = $json['OriginalOrderNumber'];
				$isRefundIncident = true;
			} else {
				$incident->CustomFields->c->incident_type = new RNCPHP\NamedIDLabel();
				$incident->CustomFields->c->incident_type->LookupName = "Order";
			}
			
			
			
			$incident->save(RNCPHP\RNObject::SuppressExternalEvents);

			if(!empty($incident->ID) && $incident->ID > 0){
				$response = array(
					"ID" => $incident->ID,
					"ReferenceNumber" => $incident->ReferenceNumber,
					"ResponseMessage" => "Success",
					"IsRefundIncident" => $isRefundIncident
				);
				return $response;
			} else {
				$response = array(
					"ID" => 0,
					"ReferenceNumber" => "Error",
					"ResponseMessage" => "Error generating incident"
				);
				return $response;
			}
		}
		 
		catch (Exception $err ){
			$response = array(
				"ID" => null,
				"ReferenceNumber" => null,
				"ResponseMessage" => "Create Incident Exception: " . $err->getMessage()
			);
			return $response;
		}
	}
	
	public function processCreateOrderHeader($incidentID)
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		try
		{
			$countryID = $this->getNamedValueID("Country", $json['BillToCountry']);

			$orderHeader = new RNCPHP\BUS\OrderHeader();
			
			$orderHeader->BillToAddress1 = $json['BillToAddress1'];
			$orderHeader->BillToCity = $json['BillToCity'];
			$orderHeader->BillToCountry = $countryID;
			$orderHeader->BillToCustomerName = $json['BillToCustomerName'];
			//email
			//phone
			$orderHeader->BillToPostalCode = $json['BillToPostalCode'];
			
			$orderHeader->BillToState = new RNCPHP\NamedIDLabel();
			$orderHeader->BillToState->ID = $this->getNamedValueID("StateOrProvince", $json['BillToState'], $countryID);
			
			$orderHeader->Contact = RNCPHP\Contact::fetch($json['CID']);
			$orderHeader->Incident = RNCPHP\Incident::fetch($incidentID);
			$orderHeader->LockTrans = false;
			//$orderHeader->NoChargeOrder = $json['NoChargeOrder'];
			$orderHeader->NoChargeOrder = true;
			
			$orderHeader->NoChargeReason = RNCPHP\BUS\NoChargeReasons::fetch($json['NoChargeReason']);
			
			$orderHeader->OrderBrand = RNCPHP\BUS\OrderBrand::fetch(1);
			$orderHeader->OrderChannel = RNCPHP\BUS\OrderChannel::fetch(4);
			$orderHeader->OrderType = RNCPHP\BUS\OrderType::fetch(1);
			//$orderHeader->ShipMethod = RNCPHP\BUS\ShipMethod::fetch(28);
			//$orderHeader->ShipMethodReason = 31;
			$orderHeader->ShipToAddress1 = $json['ShipToAddress1'];
			$orderHeader->ShipToCity = $json['ShipToCity'];
			$orderHeader->ShipToCountry = $countryID;
			$orderHeader->ShipToCustomerName = $json['ShipToCustomerName'];
			//email
			//phone
			$orderHeader->ShipToPostalCode = $json['ShipToPostalCode'];
			
			$orderHeader->ShipToState = new RNCPHP\NamedIDLabel();
			$orderHeader->ShipToState->ID = $this->getNamedValueID("StateOrProvince", $json['ShipToState'], $countryID);
			
			$orderHeader->Status = "New";
			
			$orderHeader->save();
			
			if(!empty($orderHeader->ID) && $orderHeader->ID > 0){
				$response = array(
					"OrderHeaderID" => $orderHeader->ID,
					"ResponseMessage" => "Success"
				);
				return $response;
			} else {
				$response = array(
					"OrderHeaderID" => 0,
					"ResponseMessage" => "Error generating OrderHeader object"
				);
				return $response;
			}
		}
		catch(Exception $e)
		{
			// file_put_contents("/tmp/debug.txt", print_r($e, true));
			// return $e;
			$response = array(
				"OrderHeaderID" => null,
				"ResponseMessage" => "Create Orderheader Exception: " . $e->getMessage()
			);
			return $response;
		}
		
		
		// $result = array(
			// "OrderHeaderID" => $orderHeader->ID,
			// "ResponseMessage" => "Success"
		// );
		
		// //$responseJSON = json_encode($testResult, true);
		// //return $responseJSON;
		// return $orderHeader;
	}
	
	public function linkIncidentToOrderHeader($incidentID, $orderHeaderID){
		try
		{
			$incident = RNCPHP\Incident::fetch($incidentID);
			$incident->CustomFields->BUS->order_header = RNCPHP\BUS\OrderHeader::fetch($orderHeaderID);
			$incident->save(RNCPHP\RNObject::SuppressExternalEvents);
			
			if(!empty($incident->ID) && $incident->ID == $incidentID){
				$response = array(
					"ResponseMessage" => "Success"
				);
				return $response;
			} else {
				$response = array(
					"ResponseMessage" => "Error linking Incident to OrderHeader Object"
				);
				return $response;
			}
		}
		catch(Exception $e){
			$response = array(
				"ResponseMessage" => "Link Incident to Orderheader Exception: " . $e->getMessage()
			);
			return $response;
		}
		
		// $result = array(
			// "ResponseMessage" => "Success"
		// );
		
		// return $result;
	}
	
	public function createOrderLines($orderHeaderID, $incidentID){
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		$lineIDArray = array();
		
		try
		{
			foreach($json['OrderLines'] as $line){
				if($json['CreateOrderLock']){
					$this->createOrderLock($line, $json['OriginalOrderNumber'], $incidentID, $orderHeaderID);
				}
				
				$orderLine = new RNCPHP\BUS\OrderLine();
				
				$orderLine->AdjustedPrice = "0.00";
				
				$orderLine->LineType = 1;
				$orderLine->NoCharge = true;
				
				$orderLine->ReasonCode = $json['ReasonCode'];
				
				$orderLine->OrderHeader = RNCPHP\BUS\OrderHeader::fetch($orderHeaderID);
				$orderLine->PartDescription = $line['PartDescription'];
				$orderLine->PartNumber = $line['PartNumber'];
				$orderLine->Price = $line['Price'];
				$orderLine->Quantity = $line['Quantity'];
				//$orderLine->Shipping = "0.00";
				$orderLine->Status = "Entered";
				//$orderLine->SubTotal = $line['Price'];
				//$orderLine->Total = $line['Price'];
				$orderLine->UnitOfMeasure = "EA";
				
				if(isset($json['SerialNumber']) && !empty($json['SerialNumber'])){
					$orderLine->SerialNumber = $json['SerialNumber'];
				}
				
				if(isset($json['ModelNumber']) && !empty($json['ModelNumber'])){
					$orderLine->ModelNumber = $json['ModelNumber'];
				}
				
				$orderLine->save();
				array_push($lineIDArray, $orderLine->ID);
				//array_push($lineIDArray, $line);
			}
			
			if(count($lineIDArray) > 0){
				$response = array(
					"ResponseMessage" => "Success",
					"LineIDs" => $lineIDArray
				);
				return $response;
			} else {
				$response = array(
					"ResponseMessage" => "Error creating OrderLine objects",
					"LineIDs" => $lineIDArray
				);
				return $response;
			}
		}
		catch(Exception $e)
		{
			// file_put_contents("/tmp/debug.txt", print_r($e, true));
			// return $e;
			$response = array(
				"ResponseMessage" => "Create Orderlines Exception: " . $e->getMessage(),
				"LineIDs" => null
			);
			return $response;
		}
	}
	
	public function createOrderLock($line, $originalOrderNumber, $incidentID, $orderHeaderID){
		$ordLock = new RNCPHP\BUS\OrderLocks();
		$ordLock->Source = "Phone";
		$ordLock->OriginalOrderNumber = $originalOrderNumber;
		$ordLock->NewOrderNumber = "0";
		$ordLock->SKU = $line['PartNumber'];
		$ordLock->EBSLineID = $line['EBSLineID'];
		$ordLock->IncidentID = RNCPHP\Incident::fetch($incidentID);
		$ordLock->OrderHeaderID = RNCPHP\BUS\OrderHeader::fetch($orderHeaderID);
		
		$ordLock->save();
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
			$json = json_decode(file_get_contents('php://input'));
			if(!empty($json->Session))
			{
				$sid = $json->Session;
				$account = AgentAuthenticator::authenticateSessionID($sid);
				initConnectAPI();
				return TRUE;				
			} else {
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
		// Handle Country object based on LookupName, Abbreviation, or Name
		if($object === "Country")
		{
			// First, return all NamedID values for the given object
			$values = RNCPHP\ConnectAPI::getNamedValues("RightNow\\Connect\\v1_3\\" . $object);
			foreach($values as $value)
			{
				if($value->LookupName === $match ||
				   $value->Abbreviation === $match ||
				   $value->Name === $match)
					{
						$id = $value->ID;
						break;
					}
			}
		}
		
		// Handle StateOrProvince, must use ROQL as the 'Province' object is non-enumerable
		elseif($object === "StateOrProvince")
		{
			// IF an abbreviation is passed in, we need to map the abbreviation to the out-of-box label so we can get the correct NamedID/Label pair
			if(strlen($match) <= 3)
			{
				// Fetch custom configuration setting which holds State/Province Abbreviation->Label mapping
				try
				{
					$abbrMap = RNCPHP\Configuration::fetch("CUSTOM_CFG_PROVINCE_ABBREVIATION_JSON");
					$abbrMap = (json_decode($abbrMap->Value, true));
				}
				
				catch(Exception $err)
				{
					return $err;
				}
				
				// Drill down to full label using State/Province abbreviation as key, if Abbreviation is not found address is not updated
				$match = $abbrMap['countryid'][$countryID][$match];
			}
			
			// Remove all space characters from our matching text
			$match = str_replace(' ', '', $match);
			
			// Fetch all States/Provinces based on Country ID
			$states = RNCPHP\ROQL::query("SELECT Provinces.ID, Provinces.Name FROM Country WHERE Country.ID = " . $countryID)->next();
			
			// Loop through all States/Provinces until the correct ID is found, then set Address.StateOrProvince field
			while($state = $states->next())
			{
				// Compare Matching string with State/Province name, case-insensitive, all white space removed
				$name = str_replace(' ', '', $state['Name']);
				if(strcasecmp($match, $name) == 0)
				{
					$id = $state['ID'];
					break;
				}
			}
		}
		
		// Handle other objects
		else
		{
			$values = RNCPHP\ConnectAPI::getNamedValues("RightNow\\Connect\\v1_3\\" . $object);
			foreach($values as $value)
			{
				if($value->Name === $match)
				{
					$id = $value->ID;
					break;
				}
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
