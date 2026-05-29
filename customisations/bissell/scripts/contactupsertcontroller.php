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

class ContactUpsertController
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
	public function processUpsert()
    {
        header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		// Grab the ContactID and/or Email if they are sent over
		if(isset($json['ContactID']) && !empty($json['Email']) && $json['ContactID'] != 0)
			$contactID = $json['ContactID'];
		if(isset($json['Email']) && !empty($json['Email']))
			$email = $json['Email'];
		
		// If Email isn't passed, return error message
		if(!isset($email)) {
			$response = array(
				"ID" => null,
				"ErrorMessage" => "Missing Email Address"
			);
			return $response;
		} else {
			$emailcontact = RNCPHP\Contact::first("Emails.Address = '" . $email . "'");
		}
		
		// Return Contact based on ContactID, if not found try by email
		if(isset($contactID)){
			$contact = RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $contactID );
			if(isset($emailcontact)){
				$match = ($contact->ID == $emailcontact->ID);
				if (!$match){
					$response = array(
						"ID" => $emailcontact->CustomFields->c->consumer_id,
						"ErrorMessage" => "Email address already exists"
					);
					return $response;
				} 
			} 
		}
		if(!isset($contact)){
			$contact = $emailcontact;
		}
		
		try
		{
			// BUS$SysIntegrationLog debugger
			$sysLogConfig = RNCPHP\Configuration::fetch("CUSTOM_CFG_DEBUG_SYSINTEGRATION");
			$debug = $sysLogConfig->Value;
		}
		
		catch(Exception $e)
		{
			$response = array(
				"ID" => $emailcontact->CustomFields->c->consumer_id,
				"ErrorMessage" => "Exception: " . $e->getMessage()
			);
			return $response;
			//return $e;
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
			
			// Prefix
			if(isset($json['Prefix']))
				$contact->CustomFields->BI->prefix = $this->getNamedValueID("Contact.CustomFields.BI.Prefix", $json['Prefix']);
			
			// First Name
			if(isset($json['FirstName']))
				$contact->Name->First = $json['FirstName'];
			
			// Middle Name
			if(isset($json['MiddleName']))
				$contact->CustomFields->BI->middle_name = $json['MiddleName'];
			
			// Last Name
			if(isset($json['LastName']))
				$contact->Name->Last = $json['LastName'];
			
			// Suffix
			if(isset($json['Suffix']))
				$contact->CustomFields->BI->suffix = $json['Suffix'];
			
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
			
			/** SHIPPING ADDRESS
			 *  IF a valid country and stateorprovince are passed, the address field is updated
			 *  ELSE we skip updating address fields
			 */
			// Confirm we have a valid country passed in
			if(isset($json['ShippingAddress']['Country']))
				$countryID = $this->getNamedValueID("Country", $json['ShippingAddress']['Country']);
			
			// If a valid country is found, check if StateOrProvince is also valid
			if(isset($json['ShippingAddress']['StateOrProvince']) && 
			   isset($countryID))
				$stateID = $this->getNamedValueID("StateOrProvince", $json['ShippingAddress']['StateOrProvince'], $countryID);
			
			// Country and StateOrProvince
			if((isset($countryID)) && (isset($stateID)))
			{
				$contact->CustomFields->BI->ship_country = $countryID;
				$contact->CustomFields->BI->ship_state = new RNCPHP\NamedIDLabel();
				$contact->CustomFields->BI->ship_state->ID = $stateID;
			
				// Street
				if(isset($json['ShippingAddress']['Street']))
					$contact->CustomFields->BI->ship_street = $json['ShippingAddress']['Street'];
				
				// Street2
				if(isset($json['ShippingAddress']['Street2']))
					$contact->CustomFields->BI->ship_street2 = $json['ShippingAddress']['Street2'];
				
				// City
				if(isset($json['ShippingAddress']['City']))
					$contact->CustomFields->BI->ship_city = $json['ShippingAddress']['City'];
				
				// PostalCode
				if(isset($json['ShippingAddress']['PostalCode']))
					$contact->CustomFields->BI->ship_postalcode = $json['ShippingAddress']['PostalCode'];
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
			
			// Apartment
			if(isset($json['Apartment']))
				$contact->CustomFields->BI->apartment = $this->getNamedValueID("Contact.CustomFields.BI.Apartment", $json['Apartment']);
			
			// Allergen
			if(isset($json['Allergen']))
				$contact->CustomFields->BI->cc_allergen = $json['Allergen'];
			
			// Eco Friendly
			if(isset($json['EcoFriendly']))
				$contact->CustomFields->BI->cc_eco_friendly = $json['EcoFriendly'];
			
			// Lightweight Products
			if(isset($json['LightweightProds']))
				$contact->CustomFields->BI->cc_lightweight_prods = $json['LightweightProds'];
			
			// Other
			if(isset($json['Other']))
				$contact->CustomFields->BI->cc_other = $json['Other'];
			
			// None
			if(isset($json['None']))
				$contact->CustomFields->BI->cc_none = $json['None'];
			
			// Bare Floor
			if(isset($json['BareFloor']))
				$contact->CustomFields->BI->cs_bare_floor = $json['BareFloor'];
			
			// Carpet
			if(isset($json['Carpet']))
				$contact->CustomFields->BI->cs_carpet = $json['Carpet'];
			
			// Upholstery
			if(isset($json['Upholstery']))
				$contact->CustomFields->BI->cs_upholstery = $json['Upholstery'];
			
			// Stairs
			if(isset($json['Stairs']))
				$contact->CustomFields->BI->cs_stairs = $json['Stairs'];
			
			// Car Garage
			if(isset($json['CarGarage']))
				$contact->CustomFields->BI->cs_car_garage = $json['CarGarage'];
			
			// Dogs
			if(isset($json['Dogs']))
				$contact->CustomFields->BI->pets_dogs = $json['Dogs'];		

			// Cats
			if(isset($json['Cats']))
				$contact->CustomFields->BI->pets_cats = $json['Cats'];	

			// Other Pets
			if(isset($json['OtherPets']))
				$contact->CustomFields->BI->pets_other = $json['OtherPets'];

			// No Pets
			if(isset($json['NoPets']))
				$contact->CustomFields->BI->pets_none = $json['NoPets'];			
			
			// Kids
			if(isset($json['Kids']))
				$contact->CustomFields->BI->kids = $this->getNamedValueID("Contact.CustomFields.BI.Kids", $json['Kids']);
			
			// Date of Birth			
			if(isset($json['DOB']))
			{
				if($json['DOB'] != null)
					$contact->CustomFields->BI->dob = strtotime($json['DOB']);
				else
					$contact->CustomFields->BI->dob = null;
			}
			else if($json['DOB'] == null)
				$contact->CustomFields->BI->dob = null;
			
			// Gender
			if(isset($json['Gender']))
				$contact->CustomFields->BI->gender = $this->getNamedValueID("Contact.CustomFields.BI.Gender", $json['Gender']);
			
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
			//return FALSE;
			$contactResponse = array(
				"ID" => null,
				"ErrorMessage" => $e->getMessage()
			);
			return $contactResponse;
		}
		
		return $contact;
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
