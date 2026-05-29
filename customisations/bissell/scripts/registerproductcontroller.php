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

class RegisterProductController
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
	
	public function processConsumerUpsert($surveyedproduct, $isvalidproduct)
    {
        header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		// Grab the Email if they are sent over
		if(isset($json['Email']))
			$email = $json['Email'];
		
		// If Email isn't passed, return error message
		if(!isset($email))
			return FALSE;
		
		// Return Contact based on ContactID, if not found try by email
		$contact = RNCPHP\Contact::first("Emails.Address = '" . $email . "'");
		
		try
		{
			// BUS$SysIntegrationLog debugger
			$sysLogConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_DEBUG_SYSINTEGRATION");
			$debug = $sysLogConfig->Value;
		}
		
		catch(Exception $e)
		{
			return $e;
		}
		
		try
		{
			// Determine if this a new contact
			$action = "Update";
			
			// If Contact does not exist, create a new one
			if(!isset($contact))
			{
				$contact = new RNCPHP\Contact();
				$contact->Emails = new RNCPHP\EmailArray();
				$contact->Emails[0] = new RNCPHP\Email();
				$contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
				$contact->Emails[0]->AddressType->LookupName = "Email - Primary";
				$contact->Address = new RNCPHP\Address();
				$contact->Address->StateOrProvince = new RNCPHP\NamedIDLabel();
				$contact->Phones = new RNCPHP\PhoneArray();
				$action = "Create";
			}
			
			if (!$contact->CustomFields->c->sendproductsurvey){
				if ($isvalidproduct){
					$contact->CustomFields->c->sendproductsurvey = true;
					$contact->CustomFields->c->surveyed_product = $surveyedproduct;
				}
			}
			
			// First Name
			if(isset($json['FirstName']))
				$contact->Name->First = $json['FirstName'];
			
			// Middle Name
			if(isset($json['MiddleName']))
				$contact->CustomFields->BI->middle_name = $json['MiddleName'];
			
			// Last Name
			if(isset($json['LastName']))
				$contact->Name->Last = $json['LastName'];
			
			// Email Address
			if(isset($json['Email']))
				$contact->Emails[0]->Address = $json['Email'];
			
			/** BILLING ADDRESS
			 *  IF a valid country and stateorprovince are passed, the address field is updated
			 *  ELSE we skip updating address fields
			 */
			// Confirm we have a valid country passed in
			if(isset($json['BillingAddress']['Country']))
				$countryID = $this->getNamedValueID("Country", $json['BillingAddress']['Country']);
			
			// If a valid country is found, check if StateOrProvince is also valid
			if(isset($json['BillingAddress']['StateOrProvince']) && 
			   isset($countryID))
				$stateID = $this->getNamedValueID("StateOrProvince", $json['BillingAddress']['StateOrProvince'], $countryID);
			
			// Country and StateOrProvince
			if((isset($countryID)) && (isset($stateID)))
			{
				$contact->Address->Country = RNCPHP\Country::fetch($countryID);
				$contact->Address->StateOrProvince->ID = $stateID;
			
				// Street
				if(isset($json['BillingAddress']['Street']))
					$contact->Address->Street = $json['BillingAddress']['Street'];
				
				// Street2
				if(isset($json['BillingAddress']['Street2']))
					$contact->Address->Street = $contact->Address->Street . "\n" . $json['BillingAddress']['Street2'];
				
				// City
				if(isset($json['BillingAddress']['City']))
					$contact->Address->City = $json['BillingAddress']['City'];
				
				// PostalCode
				if(isset($json['BillingAddress']['PostalCode']))
					$contact->Address->PostalCode = $json['BillingAddress']['PostalCode'];
			}
			
			// Phone Numbers
			if(isset($json['Phones']))
			{
				// Cycle through all phones passed in
				foreach($json['Phones'] as $phone)
				{
					// Cycle through all phones set for Contact
					// IF phone type is found, update phone number
					$found = false;
					foreach($contact->Phones as $conPhone)
					{
						if($conPhone->PhoneType->LookupName === $phone['Type'])
						{
							$conPhone->Number = $phone['Number'];
							$found = true;
						}
					}
					
					// ELSE create new phone type
					if(!$found)
					{
						// Create a new PhoneArray if it doesn't already exist
						$phoneIndex = count($contact->Phones);
						if($phoneIndex === 0)
							$contact->Phones = new RNCPHP\PhoneArray();
						
						$contact->Phones[$phoneIndex] = new RNCPHP\Phone();
						$contact->Phones[$phoneIndex]->PhoneType = new RNCPHP\NamedIDOptList();
						$contact->Phones[$phoneIndex]->PhoneType->LookupName = $phone['Type'];
						$contact->Phones[$phoneIndex]->Number = $phone['Number'];
					}
				}
			}
			
			// Primary Phone
			if(isset($json['PrimaryPhone']))
				$contact->CustomFields->BI->primary_ph = $this->getNamedValueID("Contact.CustomFields.BI.Primary_ph", $json['PrimaryPhone']);
			
			$contact->save();

			//2018.03.24 sharris: need to fetch new contact, populated with consumer_id
			$newcontact = RNCPHP\Contact::fetch( intval($contact->ID),RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			$contact = $newcontact;
			
			// Log debug record for successful create/update action
			if($debug == 1)
			{
				$info = array(
					'ExtSystem'	=> 'ContactUpsert',
					'RequestMsg'	=> $payload,
					'ResponseMsg'	=> 'Success',
					'Description'	=> $action,
					'Error'	=> 'HTTP 200 OK',
					'ContactID'	=> $contact->ID
				);
				logSysIntegration($info);
			}
		}
		
		catch(Exception $e)
		{
			// Log debug record for failed create/update action
			if($debug == 1)
			{
				// GET Contact ID for failed update, otherwise return NULL for failed create
				if($action === "Updated")
					$cID = $contact->ID;
				else
					$cID = null;
				
				$info = array(
					'ExtSystem'	=> "ContactUpsert",
					'RequestMsg'	=> $payload,
					'ResponseMsg'	=> $e->getMessage(),
					'Description'	=> $action,
					'Error'	=> "HTTP 400 Bad Request",
					'ContactID'	=> $cID
				);
				logSysIntegration($info);
			}
			return FALSE;
		}
		
		return $contact;
    }
	
	public function ProductToRegister()
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
				
		// Grab the ContactID and/or Email if they are sent over
		if(isset($json['ProductName']))
			$productName = $json['ProductName'];
		
		try {
			$product = RNCPHP\SalesProduct::fetch($productName);
			$productObject->ID = $product->ID;
			$productObject->LookupName = $product->LookupName;
			$productObject->IsValid = true;
		}
		catch (Exception $err){
			$productObject->ID = 5;
			$productObject->LookupName = "== Other Product ==";
			$productObject->IsValid = false;
		}
				
		return $productObject;
	}
	
	public function StoreToRegister($storeName)
	{
		try {
			$store = RNCPHP\BI\StorePurchasedMenu::fetch($storeName);
			$storeID = $store->ID;
		}
		catch (Exception $err){
			$storeID = 5;
		}
		
		return $storeID;
	}
	
	public function RegisterProduct($productid, $contactid)
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		$productname = $json['ProductName'];
		$storename = $json['StoreName'];
		$serialnumber = $json['SerialNumber'];
		$purchasedate = strtotime($json['PurchasedDate']);
		$qty = $json['Qty'];
		$source = $json['Source'];
		$duididentifier = $json['DUID'];
		$macaddress = $json['MACAddress'];
		$storeid = $this->StoreToRegister($storename);
		
		try
		{
			$asset = new RNCPHP\Asset();
			$asset->Name = $productname;
			$asset->Contact = RNCPHP\Contact::fetch($contactid);
			$asset->Product = $productid;
			if(isset($serialnumber))
				$asset->SerialNumber = $serialnumber;
			// 26 - Active : 27 - Retired : 28 - Unregistered
			$asset->StatusWithType->Status = 26;
			$asset->PurchasedDate = $purchasedate;
			$asset->CustomFields->BI->purchased_date = $purchasedate;
			$asset->CustomFields->BI->other_store = $storename;
			$asset->CustomFields->BI->qty = $qty;
			$asset->CustomFields->BI->source = RNCPHP\BI\BissellSourceMenu::fetch($source);
			$asset->CustomFields->BI->store_puchased = $storeid;
			if(isset($macaddress))
				$asset->CustomFields->BI->mac_address = $macaddress;
			if(isset($duididentifier))
			$asset->CustomFields->BI->duid_identifier = $duididentifier;
			$asset->save();
		}
		catch (Exception $err){
			// Log debug record for failed registerproduct action
			if($debug == 1)
			{
				$info = array(
					'ExtSystem'	=> "RegisterProduct",
					'RequestMsg'	=> $payload,
					'ResponseMsg'	=> $err->getMessage(),
					'Description'	=> "RegisterProduct",
					'Error'	=> "HTTP 400 Bad Request",
					'ContactID'	=> $contactid
				);
				logSysIntegration($info);
			}
			return FALSE;
		}
		return $asset;
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
