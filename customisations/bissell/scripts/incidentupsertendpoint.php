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
require_once(get_cfg_var('doc_root') . '/custom/src/incidentupsertcontroller.php');

$MAX_CONSUMER_DIFF = 8075000;

$incidentUpsert = new IncidentUpsertController();

// Authorization header is missing
if(!$incidentUpsert->Initialize())
{
	// Return NULL Contact
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$contactResponse = array(
		"ID" => null
		);
	$json = json_encode($contactResponse, true);
}

$productname = $incidentUpsert->ProductToRegister();
$contact = $incidentUpsert->processConsumerUpsert();
$assets = $incidentUpsert->processGetAsset($contact->ID, $productname->ID);
$incident = $incidentUpsert->processIncidentUpsert($contact->ID, $assets, $productname->ID);

 $consumerResponse = array(
		 "ContactId" => is_null($contact->CustomFields->c->consumer_id) ? ($MAX_CONSUMER_DIFF + $contact->ID) : $contact->CustomFields->c->consumer_id,
		 "IncidentID" => $incident['ID'],
		 "ReferenceNum" => $incident['ReferenceNumber'],
		 "ResponseMessage" => $incident['ResponseMessage']
	);
	
$json = json_encode($consumerResponse, true);
header("Content-Type: application/json");
echo $json;
return $json;
?>