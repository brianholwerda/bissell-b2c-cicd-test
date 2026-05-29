<?php
$ip_dbreq = true; require_once('include/init.phph');

require_once(get_cfg_var('doc_root') . '/include/ConnectPHP/Connect_init.phph');

use RightNow\Connect\v1_3 as RNCPHP;
//initConnectAPI();


function getPass() {
	if (!empty($_POST['password'])) {
		return htmlspecialchars(trim($_POST['password']));
	}

	return "";

}

function getUser() {
	if (!empty($_POST['username'])) {
		return htmlspecialchars(trim($_POST['username']));
	}
	return '';
}

/**
 *  Set up and call the AgentAuthenticator
 */
function authenticate($userName, $password) {
	require_once (get_cfg_var('doc_root') . '/include/services/AgentAuthenticator.phph');
	return AgentAuthenticator::authenticateCookieOrCredentials($userName, $password);
}

$session  = authenticate(getUser(), getPass());


try
{
	$delete = false;
	if (!$delete) {
		$html = "<h2>Script is disabled!</h2>";
	}
	else {
		// $object = "HISTORICAL.LotusIncident";
		$object = "HS.H_Incident435s";
		$fields = "FileAttachments.ID as FID, ID";
		$html = "<h2>Deleting $object</h2><br>";
		$maxDelete = 10000;
		$filterLevel1 = 'Parent IS NULL';
		$filterLevel2 = 'Parent IS NOT NULL AND Parent.Level1 IS NULL';
		$filterLevel3 = 'Parent.Level1 IS NOT NULL AND Parent.Level2 IS NULL';
		$cfilter = 'CustomFields.c.consumer_id > 1000000';
		$afilter = "CustomFields.BI.source.LookupName LIKE 'Regi%'";
		$filterCT = " CreatedTime > '2017-09-28T19:08:04Z'";
		//$filter = "ID > 2087";
		$filter = $filterCT;
		$filter = " GROUP BY SerialNumber HAVING count(SerialNumber) > 1 ";
		$filter = "ORDER BY ID DESC";
		$filter = "WHERE FileAttachments.ID IS NOT NULL ";
		// $queryString = "select ID from $object WHERE $filter LIMIT $maxDelete";
		$queryString = "SELECT $object FROM $object $filter LIMIT $maxDelete";
		//$queryString = "select max(ID) as ID from $object $filter LIMIT $maxDelete";
		//$queryString = "select max(id) as ID from PROD.PriceList GROUP BY Name HAVING count(Name) > 1 order by id ";
		$html .= "<br>Query:=$queryString<br>";
		
		$queryResult = RNCPHP\ROQL::queryObject($queryString)->next();
		//$queryResult = RNCPHP\ROQL::query($queryString)->next();
		$counter=0;
		$doReload = false;

		//$queryString = "select count(ID) as ID from $object WHERE $filter LIMIT $maxDelete";
		//$html .= "<br>Query:=$queryString<br>";
		//$countResult = RNCPHP\ROQL::query($queryString)->next();
		//$html .= "Query Results: <br>" . $countResult->next();

		while ($getObjs = $queryResult->next())
		{
			$counter++;
			$objID = $getObjs->ID ;
			$obj_delete = $getObjs;
			if(is_numeric($objID ) ){
				$html .= "Deleting sub-object $object " . $objID  . "<BR>";
				try{
					// this wasnt working so use select statement below $obj_delete = RNCPHP\Asset::fetch($objID);
					//$obj_delete = RNCPHP\ROQL::queryObject( "SELECT Organization FROM Organization A WHERE A.ID = " . $objID )->next()->next();
					//$obj_delete = RNCPHP\SJ\OwnershipHistory::fetch($objID);
					//$obj_delete = RNCPHP\SJ\OwnershipHistory::fetch($objID);
					//$obj_delete = RNCPHP\ServiceProduct::fetch($objID);
					//$obj_delete = RNCPHP\Contact::fetch($objID);
					//$obj_delete = RNCPHP\SalesProduct::fetch($objID);
					//$obj_delete = RNCPHP\SalesProduct::fetch($objID);
					//$obj_delete = RNCPHP\HISTORICAL\LotusIncident::fetch($objID);
					//$obj_delete = RNCPHP\PROD\Part::fetch($objID);
					//$obj_delete = RNCPHP\PROD\PriceList::fetch($objID);
					//$obj_delete = RNCPHP\HS\H_Claim::fetch($objID);
					//$obj_delete = RNCPHP\HS\H_Claim::fetch($objID);
						
					if($obj_delete->ID > 0)
					{
						$f_count = count($obj_delete->FileAttachments);
						if($f_count > 0 ){
							for($f = ($f_count-1); $f >=0 ; $f--) {
								$obj_delete->FileAttachments->offsetUnset($f);
								//$obj_delete->FileAttachments[] = null;
								$obj_delete->save(RNCPHP\RNObject::SuppressAll);
								$html .= $objID . " -- FileAttachments Destroyed <br>";
							}
						}
						
						
						//$obj_delete->FileAttachments = null;
						RNCPHP\ConnectAPI::commit();

						
					}
					else
						$html .= "DID NOT FIND ID OF " . $objID . " -- NOOOOT Destroyed <br>";
				}
				catch ( Exception $e )
				{
					$msg = "Failed to destroy': " . $objID . " error msg"  . $e->getMessage() . " <br>";
					echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e, 'OBJID' => $objID) );
				}

			}

			if($counter >= $maxDelete) {
				$doReload = true;
				header( "refresh:2;" );
				$counter = 0;
				$doReload = false;
			}
		}
	}

}
catch (RNCPHP\ConnectAPIError $ex) {
	$msg = "Failed SElect $queryString : " . $e->getMessage() . "\r\n\r\n<br>";
	$html .= "<br>$msg<br>";
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e, 'OBJID' => $getObjs['ID']) );
}

catch ( Exception $e )
{
	$msg = "Failed SElect $queryString : " . $e->getMessage() . "\r\n\r\n<br>";
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e, 'OBJID' => $getObjs['ID']) );
}
if($counter >= $maxDelete)
	$doReload = true;


	function getStatus($valid, $message, $data = null) {
		$status = array ('valid' => $valid, 'message' => $message, 'data' => $data);
		return json_encode ($status);
	}

	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title> New Document </title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <style>
	input {width:100%;}
  </style>
 </head>

 
<body> 
   <? echo $html;

	if($doReload)
		  header( "refresh:2;" );
?>
 </body>
</html>
