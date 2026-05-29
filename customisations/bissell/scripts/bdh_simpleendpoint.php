<?php

// require_once('agentsessionauth.php');
// Include helper function for parsing XML and common Web Service functions
	//require("custom/webservicehelper.php");
	
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}

	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	if(isset($_POST['sid']))
		$sid = $_POST['sid'];
	else
		$sid = $_GET['sid'];
	
	require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateSessionID($sid);
	initConnectAPI();
	use RightNow\Connect\v1_3 as RNCPHP;

/***
 *
 * This is script is used as the entry point for ajax calls being made by the order manager application.
 * It has references to other PHP scripts that handler interaction with CX objects.
 */

try{

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

	echo json_encode('Success');
}
catch(Exception $e){
    print_r($e);
}
?>