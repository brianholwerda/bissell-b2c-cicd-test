<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
use RightNow\Connect\v1_3 as RNCPHP;
require_once(get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php");
require_once(get_cfg_var( 'doc_root' ).'/include/init.phph');
require_once(get_cfg_var( 'doc_root' ).'/include/services/AgentAuthenticator.phph');
require_once(get_cfg_var( 'doc_root' ).'/include/msgbase/msgbase.phph');
require("custom/xtree.php");
require("custom/webservicehelper.php");

$sessionkey = isset($_REQUEST['session']) ? trim($_REQUEST['session']) : null;
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : null;

$account = AgentAuthenticator::authenticateSessionID($sessionkey);
initConnectAPI();

if($action=="searchcontact")
{
	$returndata = array();	
	$report = isset($_REQUEST['report']) ? trim($_REQUEST['report']) : null;
	$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : null;
	$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : null;	
	if($sessionkey==null || $report==null || $keyword==null || $type==null)
	{
		$returndata['status'] = 0;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();


	$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
	if($type=="Phone")
	{
		$filters[0]= new RNCPHP\AnalyticsReportSearchFilter;
		$filters[0]->Name = "Phone Number";
		$filters[0]->Values = array($keyword);	
	}
	if($type=="Email")
	{
		$filters[0]= new RNCPHP\AnalyticsReportSearchFilter;
		$filters[0]->Name = "Email";
		$filters[0]->Values = array($keyword);	
	}
	if($type=="Name")
	{
		$expandedname = explode(' ',$keyword);
		if(count($expandedname) > 1)
		{
			$filters[0]= new RNCPHP\AnalyticsReportSearchFilter;
			$filters[0]->Name = "First Name";
			$filters[0]->Values = array($expandedname[0]);
			$filters[1]= new RNCPHP\AnalyticsReportSearchFilter;
			$filters[1]->Name = "Last Name";
			$filters[1]->Values = array($expandedname[count($expandedname)-1]);
		}else if(count($expandedname) == 1)	
		{
			$filters[0]= new RNCPHP\AnalyticsReportSearchFilter;
			$filters[0]->Name = "First Name";
			$filters[0]->Values = array($expandedname[0]);
		}
	}
	$contactdataarray = array();
	$contactdata = array();
	$searchcontacts = RNCPHP\AnalyticsReport::fetch((int) $report);
	$searchcontactsedata = $searchcontacts->run(0,$filters);
	$resultscount = $searchcontactsedata->count();
	if($resultscount > 0)
	{
		while($row = $searchcontactsedata->next()){
			$column_name = array_keys($row);
			$value = array_values($row);
			for($k=0;$k<count($column_name);$k++)
			{
				$contactdataarray[$column_name[$k]] = $value[$k];
			}
			$contactdata[] = $contactdataarray;
		}
		$returndata['status'] = 1;
		$returndata['resultcount'] = $resultscount;
		$returndata['data'] = $contactdata;
		echo json_encode($returndata);exit;
	} else {
		$returndata['status'] = 1;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
} else if ($action=="advancedsearch") {
	$returndata = array();	
	$report = isset($_REQUEST['parameters']['ReportID']) ? trim($_REQUEST['parameters']['ReportID']) : null;
	$sernumreport = isset($_REQUEST['parameters']['SerNumReportID']) ? trim($_REQUEST['parameters']['SerNumReportID']) : null;
	//$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : null;
	//$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : null;
	$phoneNumber = isset($_REQUEST['parameters']['PhoneNumber']) ? trim($_REQUEST['parameters']['PhoneNumber']) : null;
	$emailAddress = isset($_REQUEST['parameters']['EmailAddress']) ? trim($_REQUEST['parameters']['EmailAddress']) : null;
	$lastName = isset($_REQUEST['parameters']['LastName']) ? trim($_REQUEST['parameters']['LastName']) : null;
	$firstName = isset($_REQUEST['parameters']['FirstName']) ? trim($_REQUEST['parameters']['FirstName']) : null;
	$postalCode = isset($_REQUEST['parameters']['PostalCode']) ? trim($_REQUEST['parameters']['PostalCode']) : null;
	$consumerID = isset($_REQUEST['parameters']['ConsumerID']) ? trim($_REQUEST['parameters']['ConsumerID']) : null;
	$serialNumber = isset($_REQUEST['parameters']['SerialNumber']) ? trim($_REQUEST['parameters']['SerialNumber']) : null;
	$stateProvinceID = isset($_REQUEST['parameters']['StateProvince']) ? trim($_REQUEST['parameters']['StateProvince']) : null;
	$countryID = isset($_REQUEST['parameters']['Country']) ? trim($_REQUEST['parameters']['Country']) : null;
	
	if($sessionkey==null || $report==null)
	{
		$returndata['status'] = 0;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();
	
	$runStandardReport = false;
	$runSerialNumberReport = false;
	
	$filterCounter = 0;

	$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
	$sernumfilter = new RNCPHP\AnalyticsReportSearchFilterArray;
	
	$logging = '';
	
	if(isset($phoneNumber) && !empty($phoneNumber)){
		$logging .= "/r/nPhone";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;
		$filters[$filterCounter]->Name = "PhoneNumber";
		$filters[$filterCounter]->Values = array($phoneNumber);
		$filterCounter++;
	}
	
	if(isset($emailAddress) && !empty($emailAddress)){
		$logging .= "/r/nEmail";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "EmailAddress";
		$filters[$filterCounter]->Values = array($emailAddress);
		$filterCounter++;
	}
	
	if(isset($lastName) && !empty($lastName)){
		$logging .= "/r/nLastName";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "LastName";
		$filters[$filterCounter]->Values = array($lastName);
		$filterCounter++;
	}
	
	if(isset($firstName) && !empty($firstName)){
		$logging .= "/r/nFirstName";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "FirstName";
		$filters[$filterCounter]->Values = array($firstName);
		$filterCounter++;
	}
	
	if(isset($postalCode) && !empty($postalCode)){
		$logging .= "/r/nPostalCode";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "PostalCode";
		$filters[$filterCounter]->Values = array($postalCode);
		$filterCounter++;
	}
	
	if(isset($consumerID) && !empty($consumerID)){
		$logging .= "/r/nConsumerID";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "ConsumerID";
		$filters[$filterCounter]->Values = array($consumerID);
		$filterCounter++;
	}
	
	if(isset($serialNumber) && !empty($serialNumber)){
		$logging .= "/r/nSerialNumber";
		$runSerialNumberReport = true;
		$sernumfilter[0]= new RNCPHP\AnalyticsReportSearchFilter;	
		$sernumfilter[0]->Name = "SerialNumber";
		$sernumfilter[0]->Values = array($serialNumber);
	}
	
	if(isset($stateProvinceID) && !empty($stateProvinceID)){
		$logging .= "/r/nStateProvince";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "StateProvince";
		$filters[$filterCounter]->Values = array($stateProvinceID);
		$filterCounter++;
	}
	
	if(isset($countryID) && !empty($countryID)){
		$logging .= "/r/nCountry";
		$runStandardReport = true;
		$filters[$filterCounter]= new RNCPHP\AnalyticsReportSearchFilter;	
		$filters[$filterCounter]->Name = "Country";
		$filters[$filterCounter]->Values = array($countryID);
		$filterCounter++;
	}
	
	// $returndata['logging'] = $logging;
	// $returndata['stateProvinceID'] = $stateProvinceID;
	// $returndata['countryID'] = $countryID;
	// $returndata['serialNumber'] = $serialNumber;
	// $returndata['consumerID'] = $consumerID;
	// $returndata['postalCode'] = $postalCode;
	// $returndata['firstName'] = $firstName;
	// $returndata['lastName'] = $lastName;
	// $returndata['phoneNumber'] = $phoneNumber;
	// $returndata['emailAddress'] = $emailAddress;
	// $returndata['runStandardReport'] = $runStandardReport;
	// $returndata['runSerialNumberReport'] = $runSerialNumberReport;
	// $returndata['report'] = $report;
	// $returndata['sernumreport'] = $sernumreport;
	// echo json_encode($returndata);exit;
	
	$contactdataarray = array();
	$contactdata = array();
	
	if($runStandardReport){
		$searchcontacts = RNCPHP\AnalyticsReport::fetch((int) $report);
		$searchcontactsedata = $searchcontacts->run(0,$filters);
		//$resultscount = $searchcontactsedata->count();
		if($searchcontactsedata->count() > 0){
			while($row = $searchcontactsedata->next()){
				$column_name = array_keys($row);
				$value = array_values($row);
				for($k=0;$k<count($column_name);$k++)
				{
					$contactdataarray[$column_name[$k]] = $value[$k];
				}
				
				if(!checkExists($contactdata, 'CID', $contactdataarray['CID'])){
					$contactdata[] = $contactdataarray;
				}
			}
		}
	}
	
	if($runSerialNumberReport){
		$searchserialnumber = RNCPHP\AnalyticsReport::fetch((int) $sernumreport);
		$searchserialnumberdata = $searchserialnumber->run(0,$sernumfilter);
		//$resultscount = $searchcontactsedata->count();
		if($searchserialnumberdata->count() > 0){
			while($row = $searchserialnumberdata->next()){
				$column_name = array_keys($row);
				$value = array_values($row);
				for($k=0;$k<count($column_name);$k++)
				{
					$contactdataarray[$column_name[$k]] = $value[$k];
				}
				
				if(!checkExists($contactdata, 'CID', $contactdataarray['CID'])){
					$contactdata[] = $contactdataarray;
				}
			}
		}
	}
	
	if(count($contactdata) > 0)
	{
		$returndata['status'] = 1;
		$returndata['resultcount'] = count($contactdata);
		$returndata['data'] = $contactdata;
		echo json_encode($returndata);exit;
	} else {
		$returndata['status'] = 1;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
} else if($action=="create") {
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();
	$fname = isset($_REQUEST['fname']) ? trim($_REQUEST['fname']) : null;
	$lname = isset($_REQUEST['lname']) ? trim($_REQUEST['lname']) : null;
	$phonetype = isset($_REQUEST['phonetype']) ? trim($_REQUEST['phonetype']) : null;	
	$phone = isset($_REQUEST['phone']) ? trim($_REQUEST['phone']) : null;	
	$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : null;	
	$contact = new RNCPHP\Contact();
	$contact->Name = new RNCPHP\PersonName();
	$contact->Name->First = $fname;
	$contact->Name->Last = $lname;
	if($email!="")
	{
		$contact->Emails = new RNCPHP\EmailArray();
		$contact->Emails[0] = new RNCPHP\Email();
		$contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
		$contact->Emails[0]->AddressType->LookupName = "Email - Primary";
		$contact->Emails[0]->Address = $email;		
	}
	if($phonetype!="" && $phone!="")
	{

		$contact->Phones = new RNCPHP\PhoneArray();
		$contact->Phones[0] = new RNCPHP\Phone();
		$contact->Phones[0]->PhoneType = new RNCPHP\NamedIDOptList();
		$contact->Phones[0]->PhoneType->LookupName = str_replace('_',' ',$phonetype);
		$contact->Phones[0]->Number = $phone;
	}
	$contact->CustomFields->BI->source_inbound = RNCPHP\BI\BissellSourceMenu::fetch(1);
	$contact->save();
	echo $contact->ID;exit;
} else if($action=="verify") {
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();	
	$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : null;
	$res_count = 0;
	if($email!="")
	{	
		$query="SELECT Contact.ID FROM Contact WHERE Contact.Emails.Address ='".$email."' and Contact.Emails.AddressType=0";
		$queryresult = RNCPHP\ROQL::query($query)->next();
		$res_count=$queryresult->count();
	}
	echo $res_count;exit;		
} else if($action=="getcountries"){
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();	
	$query = "SELECT ID, Name from Country";
	$countries = RNCPHP\ROQL::query($query)->next();
	
	while($country = $countries->next())
	{
		$countryArray[] = array("ID"=>$country['ID'],"Name"=>$country['Name']);
	}
	
	$response = array(
		"Count" => $countries->count(),
		"ResponseMessage" => "Success",
		"Countries" => $countryArray
	);
	
	echo json_encode($response);exit;
} else if ($action=="getstates"){
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();	
	
	$countryID = isset($_REQUEST['countryid']) ? trim($_REQUEST['countryid']) : null;
	$states = RNCPHP\ROQL::query("SELECT Provinces.ID, Provinces.Name FROM Country WHERE Country.ID = " . $countryID)->next();
	
	while($state = $states->next())
	{
		$stateArray[] = array("ID"=>$state['ID'],"Name"=>$state['Name']);
	}
	
	$response = array(
		"Count" => $states->count(),
		"ResponseMessage" => "Success",
		"States" => $stateArray
	);
	
	echo json_encode($response);exit;
} else if ($action=="incidentslookup"){
	$returndata = array();	
	$report = isset($_REQUEST['parameters']['ReportID']) ? trim($_REQUEST['parameters']['ReportID']) : null;
	$contactID = isset($_REQUEST['parameters']['ContactID']) ? trim($_REQUEST['parameters']['ContactID']) : null;

	if($sessionkey==null || $report==null || $contactID==null)
	{
		$returndata['status'] = 0;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();


	$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
	$filters[0]= new RNCPHP\AnalyticsReportSearchFilter;
	$filters[0]->Name = "ContactID";
	$filters[0]->Values = array($contactID);	
	
	$incidentdataarray = array();
	$incidentdata = array();
	$searchincidents = RNCPHP\AnalyticsReport::fetch((int) $report);
	$searchincidentsdata = $searchincidents->run(0,$filters);
	$resultscount = $searchincidentsdata->count();
	if($resultscount > 0)
	{
		while($row = $searchincidentsdata->next()){
			$column_name = array_keys($row);
			$value = array_values($row);
			for($k=0;$k<count($column_name);$k++)
			{
				$incidentdataarray[$column_name[$k]] = $value[$k];
			}
			$incidentdata[] = $incidentdataarray;
		}
		$returndata['status'] = 1;
		$returndata['resultcount'] = $resultscount;
		$returndata['data'] = $incidentdata;
		echo json_encode($returndata);exit;
	} else {
		$returndata['status'] = 1;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
} else if ($action=="getmessagebasevalues"){
	$returndata = array();	
	$messageBaseArray = isset($_REQUEST['messagebasearray']) ? trim($_REQUEST['messagebasearray']) : null;
	
	if($sessionkey==null)
	{
		$returndata['status'] = 0;
		$returndata['resultcount'] = 0;
		$returndata['data'] = array();
		echo json_encode($returndata);exit;
	}
	// $account = AgentAuthenticator::authenticateSessionID($sessionkey);
	// initConnectAPI();
	
	$messageBaseList = json_decode($messageBaseArray);
	
	
	$searchString = 'select * from MessageBase where LookupName in (';
	$resultCount = 0;
	
	foreach ($messageBaseList as $value) {
	  if($resultCount == 0){
		  $searchString .= "'" . $value->messagebase . "'";
	  } else {
		  $searchString .= ",'" . $value->messagebase . "'";
	  }
	  $resultCount++;
	}
	
	$searchString .= ')';
	
	$msgbases = RNCPHP\ROQL::query($searchString)->next();
	
	while($msgbase = $msgbases->next())
	{
		$msgBaseArray[] = array("LookupName"=>$msgbase['LookupName'],"Value"=>$msgbase['Value']);
	}
	
	$msg = RNCPHP\MessageBase::fetch("CUSTOM_MSG_CONTACT_SEARCH_BUI_PHONE_NUMBER_LBL");
	$msgBaseArray[] = array("LookupName"=>$msg->Usage->LookupName,"Value"=>$msg->Value);
	
	$response = array(
		"Count" => $msgbases->count(),
		"ResponseMessage" => "Success",
		"MessageBases" => $msgBaseArray
	);
	
	echo json_encode($response);exit;
} else if  ($action=="getsalesproduct"){
	$modelNumber = isset($_REQUEST['modelnumber']) ? trim($_REQUEST['modelnumber']) : null;
	$salesProducts = RNCPHP\ROQL::query("SELECT ID, LookupName FROM SalesProduct WHERE LookupName like '" . $modelNumber . " %'")->next();
	
	while($salesProduct = $salesProducts->next())
	{
		$salesProdArray[] = array("ID"=>$salesProduct['ID'],"Name"=>$salesProduct['LookupName']);
	}
	
	$response = array(
		"Count" => $salesProducts->count(),
		"ResponseMessage" => "Success",
		"SalesProduct" => $salesProdArray
	);
	
	echo json_encode($response);exit;
}

function checkExists($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
}
/*
function _cleanMe($target) {
$target = trim($target);
//$target = htmlspecialchars($target, ENT_QUOTES, "UTF-8");
return $target;
}*/
?>