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
require_once(get_cfg_var('doc_root') . '/custom/src/registerproductcontroller.php');

$MAX_CONSUMER_DIFF = 8075000;

$registerProduct = new RegisterProductController();

// Authorization header is missing
if(!$registerProduct->Initialize())
{
	// Return NULL Contact
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$contactResponse = array(
		"ID" => null
		);
	$json = json_encode($contactResponse, true);
}

$productname = $registerProduct->ProductToRegister();
$contact = $registerProduct->processConsumerUpsert($productname->LookupName,$productname->IsValid);
$asset = $registerProduct->RegisterProduct($productname->ID, $contact->ID);

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
	// Create associative array and return
	$registrationResponse = array(
		"ContactID" => is_null($contact->CustomFields->c->consumer_id) ? ($MAX_CONSUMER_DIFF + $contact->ID) : $contact->CustomFields->c->consumer_id,
		"C_ID" => $contact->ID,
		"AssetID" => $asset->ID
		);
	$json = json_encode($registrationResponse, true);
}

header("Content-Type: application/json");
echo $json;
return $json;
?>