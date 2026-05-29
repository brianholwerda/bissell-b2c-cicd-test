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
require_once(get_cfg_var('doc_root') . '/custom/src/bdh_contactupsertcontroller.php');

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

$productname = $contactUpsert->ProductToRegister();
$contact = $contactUpsert->processUpsert($productname->LookupName,$productname->IsValid);
$testcontact = $contactUpsert->testconsumer($productname->LookupName,$productname->IsValid);
$storename = $contactUpsert->StoreToRegister("Ace Hardware");
$productid = $contactUpsert->RegisterProduct($productname->ID, $contact->ID);
$productsummary = $contactUpsert->FetchProduct();
$websource = $contactUpsert->FetchRegistrationSource();
$status = $contactUpsert->AssetStatus();
$purchasedate = $contactUpsert->GetPurchaseDate();

// Contact failed to update or create
if(!$contact)
{
	// Return NULL Contact
	header('HTTP/1.1 400 Bad Request', false, 401);
		$contactResponse = array(
		"ID" => null
		);
	$json = json_encode($contactResponse, true);
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
		"C_ID" => $contact->ID,
		"SendProductSurvey" => $contact->CustomFields->c->sendproductsurvey,
		"SurveyedProduct" => $contact->CustomFields->c->surveyed_product,
		"ProductName" => $productname,
		"StoreName" => $storename,
		"ProductID" => $productid,
		"ProductSummary" => $productsummary,
		"WebSource" => $websource,
		"Date" => strtotime("2018-12-27"),//date_create("2018-12-27"),
		"Status" => $status,
		"PurchaseDate" => $purchasedate,
		"TestContact" => $testcontact
		);
	$json = json_encode($contactResponse, true);
}

header("Content-Type: application/json");
echo $json;
return $json;
?>