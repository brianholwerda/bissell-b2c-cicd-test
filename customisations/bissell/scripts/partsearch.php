<?php
	// Find our position in the file tree
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

	try
	{
		/**  Return the following fields:
		  *  Asset.SerialNumber
		  *  PROD$Part.Description
		  *  PROD$Part.HazardCode
		  *  PROD$Part.ItemNumber
		  *  PROD$Part.StandardReplacement
		  *  PROD$Part.UOM
		  *  PROD$PriceList.Name
		  *  PROD$PriceList.Part
		  *  PROD$PriceList.QtyAvailable
		  *  SalesProduct.LookupName
		  */

		// Define constant(s)
		define('MIN_QTY_AVAILABLE', 9);

		// Define hazard codes ineligible for air ship methods
		try
		{
			$hazCodes = RNCPHP\Configuration::fetch("CUSTOM_CFG_HAZARD_CODES");
			$hazCodes = (json_decode($hazCodes->Value, true));
		}

		catch(Exception $e)
		{
			return $e;
		}

        $returnsearch = false;
		$repairreturn = false;

		// Check POST first, else default to GET
		if(isset($_POST['itemnumber']))
			$itemNumber = $_POST['itemnumber'];
		else
			$itemNumber = $_GET['itemnumber'];

		if(isset($_POST['country']))
			$country = $_POST['country'];
		else
			$country = $_GET['country'];

		if(isset($_POST['cid']))
			$cid = $_POST['cid'];
		else
			$cid = $_GET['cid'];

		if(isset($_POST['returnsearch']))
			$returnsearch = $_POST['returnsearch'];
		else
			$returnsearch = $_GET['returnsearch'];
		
		if(isset($_POST['repairreturn']))
			$repairreturn = $_POST['repairreturn'];
		else
			$repairreturn = $_GET['repairreturn'];

		// Only perform this logic for Return Authorization workflow
		if(isset($cid))
		{
			// Return list of all registered products in the SalesProduct list for the current Contact
			$query = "SELECT A.SerialNumber, Prod.LookupName, Prod.PartNumber
				FROM Asset A
				INNER JOIN A.ParentProduct Prod
				WHERE A.Contact = " . $cid;

			$result = RNCPHP\ROQL::query($query)->next();

			$assets = array();
			while($asset = $result->next())
			{
				$newAsset = array(
					'PartNumber' => $asset['PartNumber'],
					'PartName' => $asset['LookupName'],
					'SerialNumber' => $asset['SerialNumber']
				);
				array_push($assets, $newAsset);
			}
		}

        $parts = array();

        if($returnsearch || $repairreturn){
        //Get all parts using a report

            $report_id = RNCPHP\Configuration::fetch("CUSTOM_CFG_RETRUN_PART_SEARCH_RPT_ID")->Value;
            $item_filter= new RNCPHP\AnalyticsReportSearchFilter;
            $item_filter->Name = 'itemnumber';
            $item_filter->Values = array( "$itemNumber" );
            $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
            $filters[] = $item_filter;
            $partsReport = RNCPHP\AnalyticsReport::fetch( $report_id);
            $parts = $partsReport->run(0,$filters);

        }else{
		// Retrieve all Parts and PriceList for the part number entered by agent, //67041 | Added Active flag to the select columns
		$query = "SELECT Part.Description, Part.HazardCode, Part.ItemNumber, Part.UOM, Price.Name, Price.Price, Price.QtyAvailable, Price.Active
					FROM PROD.PriceList Price
					INNER JOIN Price.PartID Part
					WHERE Price.Active = 1
					AND Part.ItemNumber = '" . $itemNumber . "'
					AND Price.Country = " . $country;

		$parts = RNCPHP\ROQL::query($query)->next();

        }

		$partsArray = array();

		// Create a new subarray for each part returned
		while($part = $parts->next())
		{
            //Check country if the parts are from the return parts search

            if($returnsearch){
                if(!($part['Country'] == $country || $part['Country'] == ''))
                    continue;
            }

			// Part is in-stock if PROD$Part.QtyAvailable >= 5
			if($part['QtyAvailable'] > MIN_QTY_AVAILABLE)
				$inStock = $part['QtyAvailable'];
			else
				$inStock = 'Out of stock';

			// Check if product is eligible for Air Shipment
			$aerosol = 'N';
			foreach($hazCodes['AIRSHIP_HAZARD'] as $hazard)
			{
				if($part['HazardCode'] === $hazard['CODE'])
					$aerosol = 'Y';
			}

			// Flag to check if LineItem is a registered products
			$notReg = true;

			// Only perform this logic for Return Authorization workflow
			if(isset($cid))
			{
				foreach($assets as $asset)
				{
					$serialNum = '';
					if($part['ItemNumber'] == $asset['PartNumber'])
					{
						$serialNum = $asset['SerialNumber'];
						$partName = $asset['PartName'];

						// Add registered line item, allows for multiple registrations for the same part number
						$newPart = array(
							'description' => $part['Description'],
							'value' => $part['ItemNumber'],
							'uom' => $part['UOM'],
							'name' => $part['Name'],
							'price' => $part['Price'],
							'inv' => $inStock,
							'actualinv' => $part['QtyAvailable'],
							'aerosol' => $aerosol,
							'partname' => $partName,
							'serialnumber' => $serialNum,
							'active' => $part['Active']	//67041 | Add replacement item automatically for Inspection and repair return type
						);
						array_push($partsArray, $newPart);
						$notReg = false;
					}
				}
			}

			// Add non-registered LineItem
			if($notReg)
			{
				$newPart = array(
					'description' => $part['Description'],
					'value' => $part['ItemNumber'],
					'uom' => $part['UOM'],
					'name' => $part['Name'],
					'price' => $part['Price'],
					'inv' => $inStock,
					'actualinv' => $part['QtyAvailable'],
					'aerosol' => $aerosol,
					'partname' => $partName,
					'serialnumber' => $serialNum,
					'active' => $part['Active']	//67041 | Add replacement item automatically for Inspection and repair return type
				);
				array_push($partsArray, $newPart);
			}
		}

		// JSON encode and return
		$json = json_encode($partsArray, JSON_PRETTY_PRINT);

		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header("Access-Control-Allow-Headers: X-Requested-With");

		echo $json;
		return $json;
	}

	catch(Exception $e)
	{
		var_dump($e);
	}
?>