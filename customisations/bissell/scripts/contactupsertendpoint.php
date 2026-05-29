<?

/** Derek Hammons
 *  9/22/17
 *  Custom endpoint for the Bissell Contact Upsert Process
 *   - POST data to this endpoint - must be JSON
 *	 - Retrun HTTP status code based on failure or success
 *     - HTTP 401 AgentAuthenticator failed or authorization header not passed correctly
 *	   - HTTP 400 Email field not passed in JSON payload
 *	   - HTTP 200 Contact created or updated correctly
 */

require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
require_once(get_cfg_var('doc_root') . '/custom/src/contactupsertcontroller.php');

$MAX_CONSUMER_DIFF = 8075000;

$contactUpsert = new ContactUpsertController();

// Authorization header is missing
if(!$contactUpsert->Initialize())
{
	// Return NULL Contact
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$contactResponse = array(
		"ID" => null
		);
	$json = json_encode($contactResponse, true);
}

$contact = $contactUpsert->processUpsert();

// Contact failed to update or create
if(!$contact->ID)
{
	// Return NULL Contact
	header('HTTP/1.1 400 Bad Request', false, 400);
		$contactResponse = array(
			"ID" => null,
			"ErrorMessage" => $contact->ErrorMessage
		);
	//$json = json_encode($contactResponse, true);
	$json = json_encode($contact, true);
}

else
{
	if(isset($contact->CustomFields->BI->dob))
		$dob = Date("F j, Y", $contact->CustomFields->BI->dob);
	
	// Handle Billing Street1 and Street2
	$billStreets = preg_split('/\\r\\n|\\r|\\n/', $contact->Address->Street);
		
	// Create associative array and return
	$contactResponse = array(
		"ContactID" => is_null($contact->CustomFields->c->consumer_id) ? ($MAX_CONSUMER_DIFF + $contact->ID) : $contact->CustomFields->c->consumer_id,
		"Prefix" => $contact->CustomFields->BI->prefix->LookupName,
		"FirstName" => $contact->Name->First,
		"MiddleName" => $contact->CustomFields->BI->middle_name,
		"LastName" => $contact->Name->Last,
		"Suffix" => $contact->CustomFields->BI->suffix,
		"Email" => $contact->Emails[0]->Address,
		"BillingAddress" => array(
			"Street" => $billStreets[0],
			"Street2" => $billStreets[1],
			"City" => $contact->Address->City,
			"Country" => $contact->Address->Country->Name,
			"StateOrProvince" => $contact->Address->StateOrProvince->LookupName,
			"PostalCode" => $contact->Address->PostalCode
			),
		"ShippingAddress" => array(
			"Street" => $contact->CustomFields->BI->ship_street,
			"Street2" => $contact->CustomFields->BI->ship_street2,
			"City" => $contact->CustomFields->BI->ship_city,
			"Country" => $contact->CustomFields->BI->ship_country->LookupName,
			"StateOrProvince" => $contact->CustomFields->BI->ship_state->LookupName,
			"PostalCode" => $contact->CustomFields->BI->ship_postalcode
			),
		"Phones" => array(
			"Home Phone" => $contactUpsert->getPhoneNumber($contact, "Home Phone"),
			"Mobile Phone" => $contactUpsert->getPhoneNumber($contact, "Mobile Phone"),
			"Office Phone" => $contactUpsert->getPhoneNumber($contact, "Office Phone"),
			"Fax Phone" => $contactUpsert->getPhoneNumber($contact, "Fax Phone"),
			"Assistant Phone" => $contactUpsert->getPhoneNumber($contact, "Assistant Phone")
		),
		"PrimaryPhone" => $contact->CustomFields->BI->primary_ph->LookupName,
		"Apartment" => $contact->CustomFields->BI->apartment->LookupName,
		"Allergen" => $contact->CustomFields->BI->cc_allergen,
		"EcoFriendly" => $contact->CustomFields->BI->cc_eco_friendly,
		"LightweightProds" => $contact->CustomFields->BI->cc_lightweight_prods,
		"Other" => $contact->CustomFields->BI->cc_other,
		"None" => $contact->CustomFields->BI->cc_none,
		"BareFloor" => $contact->CustomFields->BI->cs_bare_floor,
		"Carpet" => $contact->CustomFields->BI->cs_carpet,
		"Upholstery" => $contact->CustomFields->BI->cs_upholstery,
		"Stairs" => $contact->CustomFields->BI->cs_stairs,
		"CarGarage" => $contact->CustomFields->BI->cs_car_garage,
		"Dogs" => $contact->CustomFields->BI->pets_dogs,
		"Cats" => $contact->CustomFields->BI->pets_cats,
		"OtherPets" => $contact->CustomFields->BI->pets_other,
		"NoPets" => $contact->CustomFields->BI->pets_none,
		"Kids" => $contact->CustomFields->BI->kids->LookupName,
		"DOB" => $dob,
		"Gender" => $contact->CustomFields->BI->gender->LookupName
		);
	$json = json_encode($contactResponse, true);
}

header("Content-Type: application/json");
echo $json;
return $json;
?>