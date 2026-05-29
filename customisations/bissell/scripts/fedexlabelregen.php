<?php
	// require("custom/xtree.php");
	// require("custom/webservicehelper.php");
	
	// Find our position in the file tree
	if (!defined('DOCROOT')) {
	   $docroot = get_cfg_var('doc_root');
	   define('DOCROOT', $docroot);
	}

	/************* Agent Authentication ***************/
	// Set up and call AgentAuthenticator
	if(isset($_POST['t']))
		$token = $_POST['t'];
	else
		$token = $_GET['t'];
	
	// Decode string into username:password
	$token = base64_decode($token);
	$pieces = explode(":", $token);
	$user = $pieces[0];
	$pass = $pieces[1];
	
	require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
	$account = AgentAuthenticator::authenticateCredentials($user, $pass);
	use RightNow\Connect\v1_3 as RNCPHP;
	
	$totalProcessed = 0;
	$totalIgnored = 0;
	$totalUpdated = 0;
	$totalErrors = 0;
	$totalExceptions = 0;
	$startTime = date('Y-m-d h:i:sa');
	
	try
	{
		$query = "SELECT BUS.OrderHeader 
			FROM BUS.OrderHeader 
			WHERE BUS.OrderHeader.RANumber IS NOT NULL
			AND BUS.OrderHeader.RANumber > 0
			AND BUS.OrderHeader.OrderType IN(2,279,294)
			AND BUS.OrderHeader.RAReturnMethod IN (2,5,12,13,14,15)
			AND BUS.OrderHeader.FedExLabelSent = 0
			AND BUS.OrderHeader.RAReturnLocation > 0
			AND BUS.OrderHeader.FedExRetryCount < 2
			LIMIT 100";
			
		$results = RNCPHP\ROQL::queryObject($query)->next();
		
		// Pull in FedEx label function
		$action = "labelRegen";
		require_once("submitreturnauth.php");
		
		while($ra = $results->next())
		{
			if(isset($ra->Incident))
				$raObj->order->IncidentId = $ra->Incident->ID;
			
			if(isset($ra->Contact))
				$raObj->order->ContactId = $ra->Contact->ID;
			
			$totalProcessed++;
			$raObj->order->OrderDate = gmdate("Y-m-d\TH:i:s\Z", $ra->CreatedTime);
			$raObj->order->OrderHeaderId = $ra->ID;
			
			$raObj->order->ShippingAddress->Name = $ra->ShipToCustomerName;
			$raObj->order->ShippingAddress->Phone = $ra->ShipToPhone;
			$raObj->order->ShippingAddress->EmailAddress = $ra->ShipToEmail;
			$raObj->order->ShippingAddress->EmailAddress = $ra->Contact->Emails[0]->Address;
			$raObj->order->ShippingAddress->Address1 = $ra->ShipToAddress1;
			$raObj->order->ShippingAddress->Address2 = $ra->ShipToAddress2;
			$raObj->order->ShippingAddress->City = $ra->ShipToCity;
			$raObj->order->ShippingAddress->StateCode = GetStateAbbr($ra->ShipToState->LookupName, $ra->ShipToCountry->ID);
			$raObj->order->ShippingAddress->PostalCode = $ra->ShipToPostalCode;
			$raObj->order->ShippingAddress->CountryCode = $ra->ShipToCountry->LookupName;

			$wareHouse = GetWarehouseByReturnLocation($ra->RAReturnLocation->ID);
			
			$raObj->order->ReturnWarehouseDetails->Company = $wareHouse['Company'];
			$raObj->order->ReturnWarehouseDetails->Phone = $wareHouse['Phone'];
			$raObj->order->ReturnWarehouseDetails->Address1 = $wareHouse['Address1'];
			$raObj->order->ReturnWarehouseDetails->Address2 = $wareHouse['Address2'];
			$raObj->order->ReturnWarehouseDetails->City = $wareHouse['City'];
			$raObj->order->ReturnWarehouseDetails->State = $wareHouse['State'];
			$raObj->order->ReturnWarehouseDetails->PostalCode = $wareHouse['PostalCode'];
			$raObj->order->ReturnWarehouseDetails->Country = $wareHouse['Country'];
			
			if(isset($ra->RATrackingNumber)){
				$totalIgnored++;
				$ra->FedExLabelSent = 1;
				$ra->save();
				
				$info = array(
					'ExtSystem'	=> 'FedExCreatePendingShipmentRequest',
					'RequestMsg' => 'Tracking Number Present on OrderHeader Record - ' . $ra->RATrackingNumber,
					'ResponseMsg' => 'Updated FedExLabelSent to True',
					'Description' => 'Update OrderHeader Record',
					'Error'	=> 'No Errors',
					'OrderHeaderID' => $ra->ID
				);
				
				logSysIntegration($info);
				
			} else {
				$trackingNum = sendFedExLabel($bwsConfig, $debug, $ra->RANumber, $raObj);
				
				if(!empty($trackingNum) && !is_null($trackingNum)){
					if($trackingNum['FedExResponse'] == "SUCCESS" || $trackingNum['FedExResponse'] == "WARNING"){
						$totalUpdated++;
						$ra->RATrackingNumber = $trackingNum["TrackingNumber"];
						$ra->FedExLabelSent = 1;
						$ra->FedExMessage = $trackingNum['FedExMessage'];
						$ra->save();
					} else {
						$totalErrors++;
						$ra->FedExRetryCount = ($ra->FedExRetryCount + 1);
						$ra->FedExMessage = $trackingNum['FedExMessage'];
						$ra->Status = "FedEx Error";
						$ra->save();
					}
				} else {
					$totalErrors++;
					$ra->FedExMessage = "No Response from FedEx";
					$ra->Status = "FedEx Error";
					$ra->save();
				}
				
			}
		}
		
	}
	catch(Exception $e)
	{
		$totalExceptions++;
		file_put_contents("/tmp/debug.txt", "\n" . date("Y.m.d") . " " . date("h:i:sa") . " FedEx Reprocessor Exception:" . print_r($e, true), FILE_APPEND);
	}
	
	$endTime = date('Y-m-d h:i:sa');
	
	$batchSummary = array(
		'CXObject' => 'INTEG.BatchLog',
		'ApplicationName' => 'FedExReprocessor',
		'StartTime' => $startTime,
		'EndTime' => $endTime,
		'TotalProcessed' => $totalProcessed,
		'TotalUpdated' => $totalUpdated,
		'TotalErrors' => $totalErrors,
		'TotalIgnored' => $totalIgnored,
		'TotalExceptions' => $totalExceptions
	);
	
	logBatchJob($batchSummary);
	
	function GetWarehouseByReturnLocation($returnLocationId)
	{
		$query = sprintf("Select BUS.RARetLocationConfig From BUS.RARetLocationConfig RAL Where ID = %s",$returnLocationId);
		$objects = RNCPHP\ROQL::queryObject($query)->next();
		$warehouseArray = array();
		
		while($object = $objects->next())
		{
			
		$stateAbbr = GetStateAbbr($object->Warehouse->State->LookupName, $object->Warehouse->Country->ID);
		
		$warehouseArray = array("ID"=>$object->Warehouse->ID,
			"Name"=>$object->Warehouse->Name,
			"Company"=>$object->Warehouse->Company,
			"Address1"=>$object->Warehouse->Address1,
			"Address2"=>$object->Warehouse->Address2,
			"City"=>$object->Warehouse->City,
			"PostalCode"=>$object->Warehouse->PostalCode,
			"Code"=>$object->Warehouse->Code,
			"Country"=>$object->Warehouse->Country->ISOCode,
			"State"=>$stateAbbr,
			"Phone"=>$object->Warehouse->Phone
			);
				
		}
		return $warehouseArray;
    }
	
	function GetStateAbbr($stateName, $countryID)
	{
		$provAbbrConfig = RNCPHP\Configuration::fetch(CUSTOM_CFG_PROVINCE_ABBREVIATION_JSON);
		$proAbbrObj = json_decode($provAbbrConfig->Value);
		$warehouseArray = array();
		
		$abbrList = $proAbbrObj->countryid->{$countryID};
		$stateAbbr = "";
		foreach($abbrList as $key=>$value)
		{
			if($value == $stateName)
			{
				$stateAbbr = $key;
				break;
			}
		}
		
		return $stateAbbr;
	}
	
	// Creates a new BUS$SysIntegrationLog debug record for OSvC/Bissell web service call
	function logBatchJob($batchSummary)
	{				
		try
		{
			// Create BUS$SysIntegrationLog record
			$batchLog = new RNCPHP\INTEG\BatchLog();
			
			$batchLog->CXObject = $batchSummary['CXObject'];
			$batchLog->ApplicationName = $batchSummary['ApplicationName'];
			$batchLog->StartTime = strtotime($batchSummary['StartTime']);
			$batchLog->EndTime = strtotime($batchSummary['EndTime']);
			$batchLog->TotalCreated = 0;
			$batchLog->TotalRejected = 0;
			$batchLog->TotalProcessed = $batchSummary['TotalProcessed'];
			$batchLog->TotalUpdated = $batchSummary['TotalUpdated'];
			$batchLog->TotalErrors = $batchSummary['TotalErrors'];
			$batchLog->TotalIgnored = $batchSummary['TotalIgnored'];
			$batchLog->ExceptionsCount = $batchSummary['TotalExceptions'];
			
			$batchLog->save();
		}
		catch(Exception $e)
		{
			//file_put_contents("/tmp/debug.txt", print_r($e, true));
			file_put_contents("/tmp/debug.txt", "\n" . date("Y.m.d") . " " . date("h:i:sa") . " FedEx Reprocessor Exception Logging Batch Job:" . print_r($e, true), FILE_APPEND);
			return $e;
		}
	}
?>