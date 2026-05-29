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

class IncidentUpsertController
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
	 
	public function processInitialTest()
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		$testResult = array(
			"ContactID" => $json['ContactID'],
			"IncidentID" => $json['IncidentID'],
			"ReferenceNum" => $json['ReferenceNumber'],
			"ResponseMessage" => "Success, Site in Testing Mode"
		);
		
		//$responseJSON = json_encode($testResult, true);
		//return $responseJSON;
		return $testResult;
	}
	
	public function processIncidentUpsert($cid, $asset, $productID)
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
			//return $e->getMessage();
		}
		
		$create = false;
		
		try{
			if(isset($json['IncidentID']) && $json['IncidentID'] != 0) {
				$incident = RNCPHP\Incident::fetch($json['IncidentID']);
			} elseif(isset($json['ReferenceNumber']) && !empty($json['ReferenceNumber'])){
				$incident = RNCPHP\Incident::first("ReferenceNumber = '" . $json['ReferenceNumber'] . "'" );
			} else {
				$create = true;
				$incident = new RNCPHP\Incident();
			}
		
			if(isset($json['Subject'])) {
				$incident->Subject = $json['Subject'];
			}
			
			if($create){
				//Set incident product. Default will use ProductName if passed in and there is a match. If no match is found, check if there is an asset with a product populated.
				if(isset($json['ProductName']) && !empty($json['ProductName'])){
					try
					{
						//$incident->Product =  RNCPHP\ServiceProduct::fetch($json['ProductName']);
						$incProduct = RNCPHP\ServiceProduct::fetch($json['ProductName']);
					}
					catch(Exception $e){
						//1060 is ID for '0 Default' product
						$incProduct =  RNCPHP\ServiceProduct::fetch(1060);
					}
				} else {
					//1060 is ID for '0 Default' product
					$incProduct =  RNCPHP\ServiceProduct::fetch(1060);
				}
				
				//Asset product ID 5 is "Other Product", do not update if other product
				if(isset($asset) && $incProduct->ID == 1060 && $asset->Product->ID != 5){
					try
					{
						$incProduct = RNCPHP\ServiceProduct::fetch($asset->LookupName);
					}
					catch(Exception $e){
						//No change, continue to use 1060
					}
				}
				
				$incident->Product = $incProduct;
		
				//$incident->Category = RNCPHP\ServiceCategory::fetch(1022);
				if(isset($json['Category']) && !empty($json['Category'])){
					try
					{
						$incident->Category = RNCPHP\ServiceCategory::fetch($json['Category']);
					}
					catch(Exception $e)
					{
						//Create incident without a category
					}
				}
				
				if(isset($asset))
					$incident->Asset = $asset; //RNCPHP\Asset::fetch($asset->ID);
		 
			
				if(isset($json['Disposition']) && !empty($json['Disposition'])){
					try
					{
						$incident->Disposition = RNCPHP\ServiceDisposition::fetch($json['Disposition']);
					}
					catch(Exception $e)
					{
						//Create incident without a category
					}
				}
				
				//Language List
				//en_us - ID: 1 (English US)
				//fr_FR - ID: 9 (French)
				//de_DE - ID: 3 (German)
				//it_IT - ID: 10 (Italian)
				//pt_PT - ID: 39 (Portugese)
				//es_ES - ID: 6 (Spanish)
				//nl_NL - ID: 12 (Dutch)
				//pl_PL - ID: 15 (Polish)
				//sv_SE - ID: 14 (Swedish)
				//en_UK - ID: 5 (English UK)
				//en_AU - ID: 16 (English AU)
				
				//Interface List
				//bissell - ID: 1
				//bissell_ext - ID: 2
				//bissell_de - ID: 6
				//bissell_es - ID: 3
				//bissell_fr - ID: 5
				//bissell_it - ID: 7
				//bissell_pt - ID: 4
				//bissell_nl - ID: 8
				//bissell_pl - ID: 9
				//bissell_se - ID: 10
				//bissell_uk - ID: 13
				//bissell_au - ID: 14
				
				//Mailbox List in TST1
				//Connected Care NA - ID: 20
				//Consumer Care NA BISSELL Pet Network - ID: 18
				//Consumer Care NA Better Life - ID: 17
				//Consumer Care NA Sanitaire - ID: 11
				//Consumer Care UK - ID: 6
				//Consumer Care NZ - ID: 5
				//Consumer Care AU - ID: 4
				//Consumer Care NA - ID: 3
				//CS Resolutions - ID: 2
				//Connected Care - ID: 20
				
				//Language Interface Mailboxes
				//Consumer Care DE - ID: 35
				//Consumer Care ES - ID: 32
				//Consumer Care FR - ID: 34
				//Consumer Care IT - ID: 36
				//Consumer Care PT - ID: 33
				//Consumer Care NL - ID: 44
				//Consumer Care PL - ID: 45
				//Consumer Care SE - ID: 46
								
				//$incident->Mailbox = RNCPHP\Mailbox::fetch(3);
				
				switch(strtoupper($json['Language']))
				{
					case 'ES':
						//$mailbox = RNCPHP\Mailbox::fetch(32);
						//$queue = "Sertec Spanish Tier 1 Phone/Email";
						$languageID = 6;
						$interfaceID = 3;
						break;
					case 'EN':
						if (isset($json['Address']['Country']) && (strtoupper($json['Address']['Country']) == "GB" || strtoupper($json['Address']['Country']) == "IE"))
						{
							// $mailbox = RNCPHP\Mailbox::fetch(6);
							// $queue = "UK/IE Tier 1 Email";
							$languageID = 5;
							$interfaceID = 13;
						} else if (isset($json['Address']['Country']) && strtoupper($json['Address']['Country']) == "AU") { 
							$languageID = 16;
							$interfaceID = 14;
						} else {
							// $mailbox = RNCPHP\Mailbox::fetch(20);
							// $queue = "HKT Tier 1 US/CA Connected Email";
							$languageID = 1;
							$interfaceID = 1;
						}
						
						break;
					case 'DE':
						//$mailbox = RNCPHP\Mailbox::fetch(35);
						//$queue = "Sertec German Tier 1 Phone/Email";
						$languageID = 3;
						$interfaceID = 6;
						break;
					case 'FR':
						//$mailbox = RNCPHP\Mailbox::fetch(34);
						//$queue = "Sertec French Tier 1 Phone/Email";
						$languageID = 9;
						$interfaceID = 5;
						break;
					case 'IT':
						//$mailbox = RNCPHP\Mailbox::fetch(36);
						//$queue = "Sertec Italian Tier 1 Phone/Email";
						$languageID = 10;
						$interfaceID = 7;
						break;
					case 'PT':
						//$mailbox = RNCPHP\Mailbox::fetch(33);
						//$queue = "Sertec Portuguese Tier 1 Phone/Email";
						$languageID = 39;
						$interfaceID = 4;
						break;
					case 'NL':
						//$mailbox = RNCPHP\Mailbox::fetch(44);
						//$queue = "Sertec Dutch Tier 1 Phone/Email";
						$languageID = 12;
						$interfaceID = 8;
						break;
					case 'PL':
						//$mailbox = RNCPHP\Mailbox::fetch(45);
						//$queue = "Sertec Polish Tier 1 Phone/Email";
						$languageID = 15;
						$interfaceID = 9;
						break;
					case 'SE':
						//$mailbox = RNCPHP\Mailbox::fetch(46);
						//$queue = "Sertec Swedish Tier 1 Phone/Email";
						$languageID = 14;
						$interfaceID = 10;
						break;
					default:
						//$mailbox = RNCPHP\Mailbox::fetch(3);
						//$queue = "TP Tier 1 Homecare US Email";
						$languageID = 1;
						$interfaceID = 1;
						break;
				}
				
				//$incident->Mailbox = $mailbox;
				$incident->Language = new RNCPHP\NamedIDOptList();
				$incident->Language->ID = $languageID;
				//$incident->Queue = new RNCPHP\NamedIDLabel();
				//$incident->Queue->LookupName = $queue;
				$incident->Interface = RNCPHP\SiteInterface::fetch($interfaceID);
				$incident->SiteInterface->ID = $interfaceID;
				
				//$incident->Organization = RNCPHP\Organization::fetch(8);
			 
				$incident->PrimaryContact = RNCPHP\Contact::fetch($cid);//Required field to create an incident through connect PHP

				$incident->Severity = new RNCPHP\NamedIDOptList();
				$incident->Severity->LookupName  = "Normal";
			 
				if(isset($json['Status']) && !empty($json['Status']))
				{
					$incident->StatusWithType->Status->LookupName = $json['Status'];
				}
				else {
					//Status ID 1 = Open
					$incident->StatusWithType->Status->ID = 1;
				}
				
				if(isset($json['MobileVersion']) && !empty($json['MobileVersion']))
					$incident->CustomFields->BI->mobile_version = $json['MobileVersion'];
				
				if(isset($json['MobileModelNum']) && !empty($json['MobileModelNum']))
					$incident->CustomFields->BI->mobile_model_num = $json['MobileModelNum'];
				
				if(isset($json['AppVersion']) && !empty($json['AppVersion']))
					$incident->CustomFields->BI->app_version = $json['AppVersion'];
				
				if(isset($json['MobileType']) && !empty($json['MobileType']))
					$incident->CustomFields->BI->mobile_type = RNCPHP\BI\MobileTypeMenu::fetch($json['MobileType']);
					//$incident->CustomFields->BI->mobile_type = $this->getNamedValueID("Incident.CustomFields.BI.Mobile_Type", $json['MobileType']);
				
				if(isset($json['MobilePlatform']) && !empty($json['MobilePlatform']))
					$incident->CustomFields->BI->mobile_platform = RNCPHP\BI\MobilePlatformMenu::fetch($json['MobilePlatform']);
					//$incident->CustomFields->BI->mobile_platform = $this->getNamedValueID("Incident.CustomFields.BI.Mobile_Platform", $json['MobilePlatform']);
				
				$incident->CustomFields->c->incident_source = new RNCPHP\NamedIDLabel();
				$incident->CustomFields->c->incident_source->LookupName = $json['Source'];
				
				if(isset($json['WebSource']) && !empty($json['WebSource'])) {
					$incident->CustomFields->c->web_source = new RNCPHP\NamedIDLabel();
					$incident->CustomFields->c->web_source->LookupName = $json['WebSource'];
				}
				
				if(isset($json['IncidentType']) && !empty($json['IncidentType'])) {
					$incident->CustomFields->c->incident_type = new RNCPHP\NamedIDLabel();
					$incident->CustomFields->c->incident_type->LookupName = $json['IncidentType'];
				} else {
					$incident->CustomFields->c->incident_type = new RNCPHP\NamedIDLabel();
					$incident->CustomFields->c->incident_type->LookupName = "Standard";
				}
				
				if(isset($json['OrderNumber']) && !empty($json['OrderNumber']))
					$incident->CustomFields->c->order_number = $json['OrderNumber'];
			}
			
			$incident->Threads = new RNCPHP\ThreadArray();
			$incident->Threads[0] = new RNCPHP\Thread();
			$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
			//Entry type ID 1 is private note
			$incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
			if(isset($json['ContentType'])) {
				$incident->Threads[0]->ContentType = new RNCPHP\NamedIDOptList();
				$incident->Threads[0]->ContentType->LookupName = $json['ContentType'];
			}
			$incident->Threads[0]->Channel = new RNCPHP\NamedIDLabel();
			//Channel 6 is web note
			$incident->Threads[0]->Channel->ID = 6;
			if(isset($json['IncidentDetails'])) {
				$incident->Threads[0]->Text = $json['IncidentDetails'];
			} else {
				$incident->Threads[0]->Text = "Incident created through OSvC API";
			}
			
			
		 	
			
			
			$incident->save(RNCPHP\RNObject::SuppressExternalEvents);
			//$incident->save();
			
			//$newincident = RNCPHP\Incident::fetch( intval($incident->ID),RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			//$newincident = RNCPHP\Incident::fetch(intval($incident->ID));
			//$incident = $newincident;
			
			//echo "Incident Created";
			//return $incident;
			//return $newincident;
			$response = array(
				"ID" => $incident->ID,
				"ReferenceNumber" => $incident->ReferenceNumber,
				"ResponseMessage" => "Success"
			);
			return $response;
		}
		 
		catch (Exception $err ){
			$response = array(
				"ID" => null,
				"ReferenceNumber" => null,
				"ResponseMessage" => "Exception: " . $err->getMessage()
			);
			return $response;
		}
	}
	
	public function testIncidentFetch(){
		//$newincident = RNCPHP\Incident::fetch( 1297022, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
		$newcontact = RNCPHP\Contact::fetch( intval(1823785),RNCPHP\RNObject::VALIDATE_KEYS_OFF );
		//	$contact = $newcontact;
		return $newcontact;
	}
	
	public function processGetAsset($contactID, $productID)
	{
		header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);

		if(isset($json['SerialNumber']) && !empty($json['SerialNumber'])){
			$assets = RNCPHP\Asset::find("Contact.ID = " . $contactID);
		
			$defaultCount = count($assets);
			for ($x = 0; $x < $defaultCount; $x++) {
				if ($assets[$x]->SerialNumber == $json['SerialNumber'])
					return $assets[$x];
			}
			
			if (isset($json['ProductName']) && !empty($json['ProductName'])){
				try {
					$asset = new RNCPHP\Asset();
					$asset->Name = $json['ProductName'];
					$asset->Contact = RNCPHP\Contact::fetch($contactID);
					$asset->Product = $productID;
					$asset->SerialNumber = $json['SerialNumber'];
					// 26 - Active : 27 - Retired : 28 - Unregistered
					$asset->StatusWithType->Status = 26;
					$asset->CustomFields->BI->qty = 1;
					$asset->CustomFields->BI->source = RNCPHP\BI\BissellSourceMenu::fetch($json['Source']);
					if(isset($json['MACAddress']) && !empty($json['MACAddress']))
						$asset->CustomFields->BI->mac_address = $json['MACAddress'];
					if(isset($json['DUID']) && !empty($json['DUID']))
						$asset->CustomFields->BI->duid_identifier = $json['DUID'];
					
					$asset->save();				
					
					return $asset;
				}
				catch(Exception $e){
					return null;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}
	}
	
	public function processConsumerUpsert()
    {
        header('Content-Type: application/json');
		$payload = $this->getPostData();
		
		$json = json_decode($payload, true);
		
		// Grab the Email if they are sent over
		if(isset($json['Email']))
			$email = $json['Email'];
		
		if(isset($json['ContactId']) && $json['ContactId'] != 0)
			$contactID = $json['ContactId'];
		
		// If Email isn't passed, return error message
		if(!isset($email) && !isset($contactID))
			return FALSE;
		
		// Return Contact based on ContactID, if not found try by email
		if(isset($contactID)){
			$contact = RNCPHP\Contact::first("CustomFields.c.consumer_id = " . $contactID );
		} else {
			$contact = RNCPHP\Contact::first("Emails.Address = '" . $email . "'");
		}
		
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
			
			// First Name
			if(isset($json['FirstName']))
				$contact->Name->First = $json['FirstName'];
			
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
			if(isset($json['Address']['Country']))
				$countryID = $this->getNamedValueID("Country", $json['Address']['Country']);
			
			// If a valid country is found, check if StateOrProvince is also valid
			if(isset($json['Address']['StateOrProvince']) && 
			   isset($countryID))
				$stateID = $this->getNamedValueID("StateOrProvince", $json['Address']['StateOrProvince'], $countryID);
			
			// Country and StateOrProvince
			//if((isset($countryID)) && (isset($stateID)))
			if(isset($countryID))
			{
				$contact->Address->Country = RNCPHP\Country::fetch($countryID);
				//$contact->Address->StateOrProvince->ID = $stateID;
				
				if (isset($stateID))
					$contact->Address->StateOrProvince->ID = $stateID;
				
				// Street
				if(isset($json['Address']['Street']))
					$contact->Address->Street = $json['Address']['Street'];
				
				// Street2
				if(isset($json['Address']['Street2']))
					$contact->Address->Street = $contact->Address->Street . "\n" . $json['Address']['Street2'];
				
				// City
				if(isset($json['Address']['City']))
					$contact->Address->City = $json['Address']['City'];
				
				// PostalCode
				if(isset($json['Address']['PostalCode']))
					$contact->Address->PostalCode = $json['Address']['PostalCode'];
				
				if ($action == "Create"){
					$contact->CustomFields->BI->ship_country = $countryID;
				
					if (isset($stateID)){
						$contact->CustomFields->BI->ship_state = new RNCPHP\NamedIDLabel();
						$contact->CustomFields->BI->ship_state->ID = $stateID;
					}
				
					// Street
					if(isset($json['Address']['Street']))
						$contact->CustomFields->BI->ship_street = $json['Address']['Street'];
					
					// Street2
					if(isset($json['Address']['Street2']))
						$contact->CustomFields->BI->ship_street2 = $json['Address']['Street2'];
					
					// City
					if(isset($json['Address']['City']))
						$contact->CustomFields->BI->ship_city = $json['Address']['City'];
					
					// PostalCode
					if(isset($json['Address']['PostalCode']))
						$contact->CustomFields->BI->ship_postalcode = $json['Address']['PostalCode'];
				}
				
			}
			
			// Phone Numbers
			if(isset($json['PhoneNumber']))
			{
				$contact->Phones = new RNCPHP\PhoneArray();
						
				$contact->Phones[0] = new RNCPHP\Phone();
				$contact->Phones[0]->PhoneType = new RNCPHP\NamedIDOptList();
				$contact->Phones[0]->PhoneType->LookupName = "Home Phone";
				$contact->Phones[0]->Number = $json['PhoneNumber'];
				
				$contact->CustomFields->BI->primary_ph = $this->getNamedValueID("Contact.CustomFields.BI.Primary_ph", "Home");
				
				// // Cycle through all phones passed in
				// foreach($json['PhoneNumber'] as $phone)
				// {
					// // Cycle through all phones set for Contact
					// // IF phone type is found, update phone number
					// $found = false;
					// foreach($contact->Phones as $conPhone)
					// {
						// if($conPhone->PhoneType->LookupName === $phone['Type'])
						// {
							// $conPhone->Number = $phone['Number'];
							// $found = true;
						// }
					// }
					
					// // ELSE create new phone type
					// if(!$found)
					// {
						// // Create a new PhoneArray if it doesn't already exist
						// $phoneIndex = count($contact->Phones);
						// if($phoneIndex === 0)
							// $contact->Phones = new RNCPHP\PhoneArray();
						
						// $contact->Phones[$phoneIndex] = new RNCPHP\Phone();
						// $contact->Phones[$phoneIndex]->PhoneType = new RNCPHP\NamedIDOptList();
						// $contact->Phones[$phoneIndex]->PhoneType->LookupName = $phone['Type'];
						// $contact->Phones[$phoneIndex]->Number = $phone['Number'];
					// }
				// }
			}
			
			// // Primary Phone
			// if(isset($json['PrimaryPhone']))
				// $contact->CustomFields->BI->primary_ph = $this->getNamedValueID("Contact.CustomFields.BI.Primary_ph", $json['PrimaryPhone']);
			
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
