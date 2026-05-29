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
require_once(get_cfg_var('doc_root') . '/custom/src/createordercontroller.php');

$orderController = new CreateOrderController();

// Authorization header is missing
if(!$orderController->Initialize())
{
	// Return NULL Contact
	header('HTTP/1.1 401 Unauthorized', false, 401);
		$response = array(
		"ID" => null
		);
	$json = json_encode($response, true);
}

// $productname = $incidentUpsert->ProductToRegister();
// $contact = $incidentUpsert->processConsumerUpsert();
// $assets = $incidentUpsert->processGetAsset($contact->ID, $productname->ID);
$incident = $orderController->processIncidentCreate();

if($incident['ResponseMessage'] == "Success"){
	if($incident['IsRefundIncident'] == true){
		$response = array(
			 "IncidentID" => $incident['ID'],
			 "ResponseCode" => "Success",
			 "ResponseMessage" => "Success"
		);
	} else {
		$orderHeader = $orderController->processCreateOrderHeader($incident['ID']);
	
		if($orderHeader['ResponseMessage'] == "Success"){
			$linkOrderHeaderIncidentResponse = $orderController->linkIncidentToOrderHeader($incident['ID'], $orderHeader['OrderHeaderID']);
			
			if($linkOrderHeaderIncidentResponse['ResponseMessage'] == "Success"){
				$orderLines = $orderController->createOrderLines($orderHeader['OrderHeaderID'], $incident['ID']);
				
				if($orderLines['ResponseMessage'] == "Success"){
					$response = array(
						 "IncidentID" => $incident['ID'],
						 "OrderHeaderID" => $orderHeader['OrderHeaderID'],
						 "ResponseCode" => "Success",
						 "ResponseMessage" => "Success"
					);
				} else {
					$response = array(
						 "IncidentID" => $incident['ID'],
						 "OrderHeaderID" => $orderHeader['OrderHeaderID'],
						 "ResponseCode" => "Error",
						 "ResponseMessage" => $orderLines['ResponseMessage']
					);
				}
			} else {
				$response = array(
					 "IncidentID" => $incident['ID'],
					 "OrderHeaderID" => $orderHeader['OrderHeaderID'],
					 "ResponseCode" => "Error",
					 "ResponseMessage" => $linkOrderHeaderIncidentResponse['ResponseMessage']
				);
			}
		} else {
			$response = array(
				 "IncidentID" => $incident['ID'],
				 "OrderHeaderID" => null,
				 "ResponseCode" => "Error",
				 "ResponseMessage" => $orderHeader['ResponseMessage']
			);
		}
	}
	
} else {
	$response = array(
		 "IncidentID" => null,
		 "OrderHeaderID" => null,
		 "ResponseCode" => "Error",
		 "ResponseMessage" => $incident['ResponseMessage']
	);
}

//$orderHeader = $orderController->processCreateOrderHeader($incident->ID);
//$responseArray = $orderController->linkIncidentToOrderHeader($incident->ID, $orderHeader->ID);
//$orderLines = $orderController->createOrderLines($orderHeader->ID);

 // $response = array(
		 // "IncidentID" => $incident->ID,
		 // "ReferenceNum" => $incident->ReferenceNumber,
		 // "OrderHeaderID" => $orderHeader->ID,
		 // "LinkHeaderToIncidentMessage" => $responseArray['ResponseMessage'],
		 // "OrderLines" => $orderLines['LineIDs'],
		 // "LinesMessage" => $orderLines['ResponseMessage']
	// );
	
$json = json_encode($response, true);
header("Content-Type: application/json");
echo $json;
return $json;
?>