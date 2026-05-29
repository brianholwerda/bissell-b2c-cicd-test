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
require_once(get_cfg_var('doc_root') . '/custom/src/updateorderdatacontroller.php');

$orderUpdate = new OrderDataUpdatesController();

// Authorization header is missing
if(!$orderUpdate->Initialize())
{
	// Return NULL Contact
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$response = array(
		"ID" => null
		);
	$json = json_encode($response, true);
}

$order = $orderUpdate->BeginUpdate();

$json = json_encode($order, true);
header("Content-Type: application/json");
echo $json;
return $json;

?>