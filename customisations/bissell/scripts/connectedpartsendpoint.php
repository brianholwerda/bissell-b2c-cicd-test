<?
/** Derek Hammons
 *  8/16/19
 *  Custom endpoint for checking Contact Connected and Premium Parts
 *   - POST data to this endpoint - must be JSON
 *	 - Return HTTP status code based on failure or success
 *     - HTTP 401 AgentAuthenticator failed or authorization header not passed correctly
 */

require_once(get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
require_once(get_cfg_var('doc_root') . '/custom/src/connectedpartscontroller.php');

$partsController = new ConnectedPartsController();

// Authorization header is missing or Authentication failed
if(!$partsController->Initialize())
{
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$jsonResponse = array(
		"ERROR" => "401 Unauthorized"
	);
	$json = json_encode($jsonResponse, true);
}

else
	$json = $partsController->getConnectedParts();

header("Content-Type: application/json");
echo $json;
return $json;
?>