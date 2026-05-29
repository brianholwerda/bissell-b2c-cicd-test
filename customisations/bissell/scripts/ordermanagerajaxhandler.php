<?php

require_once('agentsessionauth.php');

/***
 *
 * This is script is used as the entry point for ajax calls being made by the order manager application.
 * It has references to other PHP scripts that handler interaction with CX objects.
 */

try{

    require_once("cxobjecthelper.php");

    global $cxObjHelper;

    $cxObjHelper = new CXObjectHelper();

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");

    if (isset($_GET['action']))
        $action = $_GET['action'];
    else
        $action = $_POST['action'];

    switch($action){

        case 'getorderhistory':
            $sid = $_GET['sid'];
            //$consumer_id = $_GET['consumer_id'];
            $consumer_id = $_GET['ConsumerID'];
            echo GetOrderHistory($action,$sid,$consumer_id);
            break;
        case 'getlineitemhistory':
            $sid = $_POST['sid'];
            //$consumer_id = $_POST['consumer_id'];
            $consumer_id = $_POST['ConsumerID'];
            $order_number = $_POST['OrderNumber'];
            echo GetOrderData($action,$sid,$consumer_id,$order_number);
            break;
        case 'updatepaymentsschedule':
            $sid = $_GET['sid'];
            $transaction_ids = $_GET['transaction_ids'];
            $payment_schedule = $_GET['payment_schedule'];
            echo UpdatePaymentDetails($action, $sid, $transaction_ids, $payment_schedule);
            break;
        case 'getmenulist':
            $menuObject = explode(".",$_GET['menu']);
            echo GetMenuList($menuObject[0],$menuObject[1],$menuObject[2]);
            break;
        case 'getpaymentmethods':
            $country = $_GET['country'];
            echo GetPaymentMethods($country);
            break;
        case 'getshipmethods':
            $country = $_GET['country'];
            $orderType = $_GET['ordertype'];
            echo GetShipMethods($country,$orderType);
            break;
		case 'getorderbrands':
            $country = $_GET['country'];
            echo GetOrderBrands($country);
            break;
		case 'getordernochargereasons':
            $country = $_GET['country'];
            echo GetOrderNoChargeReasons($country);
            break;	
        case 'getshipmethodreason':
            echo GetShipMethodReasonCodes();
            break;
        case 'getproductrecalls':
	   $country = $_GET['country'];
	   echo GetProductRecallList($country);
	   break;
        case 'getreasoncodes':
            echo GetReasonCodes();
            break;
        case 'getorderchannellist':
            echo GetOrderChannelList();
            break;
        case 'getreasoncodelist':
            echo GetReasonCodeList();
            break;
        case 'editorderline':
            if($_POST['action'] == 'remove'){
                echo DeleteOrderLine($_POST);
            }else{
                echo EditOrderLine($_POST);
            }
            break;
        case 'addorderline':
            echo AddOrderLine($_POST);
            break;
        case 'getorderlinedata':
            $orderHeaderId = (int)$_GET['orderheaderid'];
            $linetype = $_GET['linetype'];
            if($orderHeaderId < 1){
                $emptyLines = array();
                $emptyLines['aaData'] = [];
                echo json_encode($emptyLines);
            }else{
                echo GetOrderLines($orderHeaderId,$linetype);
            }
            break;
        case 'updatelinetrakingnumber':
            $orderHeaderId = (int)$_GET['orderheaderid'];
            $content = trim(file_get_contents("php://input"));
            echo UpdateOrderLineTrackingNumber($orderHeaderId,$content);
            break;
        case 'getcountryisolist':
            echo  GetCountryISOList();
            break;
        case 'getprovincelist':
            echo  GetProvinceList();
            break;
        case 'getconfiguration':
            $configKey = $_GET['configkey'];
            echo GetConfiguration($configKey);
            break;
        case 'getrareasonlist':
            $returnTypeId = $_GET['returntypeid'];
            $countryISO = $_GET['countryiso'];
            echo GetRAReasonList($returnTypeId,$countryISO);
            break;
        case 'getraactionlist':
            $returnTypeId = $_GET['returntypeid'];
            $countryISO = $_GET['countryiso'];
            echo GetRAActionList($returnTypeId,$countryISO);
            break;
        case 'getrareturnlocationlist':
            $returnTypeId = $_GET['returntypeid'];
            $countryISO = $_GET['countryiso'];
            echo GetRAReturnLocationList($returnTypeId,$countryISO);
            break;
        case 'getrareturnmethodlist':
            $returnTypeId = $_GET['returntypeid'];
            $countryISO = $_GET['countryiso'];
            echo GetRAReturnMethodList($returnTypeId,$countryISO);
            break;
		case 'getrareturntypelist':
			$incidentType = $_GET['incidenttype'];
			$country = $_GET['country'];
			echo GetRAReturnTypeList($incidentType,$country);
			break;
        case 'logsysintegration':
            $info = json_decode(file_get_contents('php://input'),true);
            LogSysIntegration($info);
            break;
		case 'createorderlock':
            $info = json_decode(file_get_contents('php://input'),true);
            echo LockOrderLine($info);
            break;
		case 'updateorderlock':
            $info = json_decode(file_get_contents('php://input'),true);
            $orderLockID = (int)$_GET['orderlockid'];
			echo UpdateOrderLock($orderLockID, $info);
            break;
		case 'deleteorderlock':
            $orderLockID = (int)$_GET['orderlockid'];
			echo DeleteOrderLock($orderLockID);
            break;
		case 'getorderlocks':
            $orderHeaderID = $_GET['orderheaderid'];
            echo GetOrderOrderLocks($orderHeaderID);
            break;
        case 'getwarehousebyreturnlocation':
            $returnLocationId = $_GET['returnlocationid'];
            echo GetWarehouseByReturnLocation($returnLocationId);
            break;
		case 'getassetdetails':
            $assetID = $_GET['assetid'];
            echo GetAssetDetails($assetID);
            break;
		case 'getcategorydetails':
            $catID = $_GET['categoryid'];
            echo GetCategoryText($catID);
            break;
    };

}
catch(Exception $e){
    print_r($e);
}

/**
 * Get order history
 * @param mixed $consumer_id
 * @param mixed $sid
 * @return mixed
 */
function GetOrderHistory($action,$sid,$consumer_id){

    if (!function_exists("curl_init"))
        load_curl();

    $post = [
    'action' => $action,
    'ConsumerID' => $consumer_id,
    'sid' => $sid
    ];

    $path = 'https://'.$_SERVER['HTTP_HOST'].'/cgi-bin/bissell.cfg/php/custom/contactorderhistory.php';

    $ch = curl_init($path);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);

    curl_close($ch);

    $json_data = $response;

    $json_to_array = json_decode($json_data);

    return $json_data;
}

/**
 * Return order header and detail data in JSON format
 * @return string
 */
function GetOrderData($action,$sid,$consumer_id,$order_number){

    if (!function_exists("curl_init"))
        load_curl();

    $post = [
        'action' => $action,
        'sid' => $sid,
        'ConsumerID' => $consumer_id,
        'OrderNumber' => $order_number
    ];

    $path = 'https://'.$_SERVER['HTTP_HOST'].'/cgi-bin/bissell.cfg/php/custom/contactorderhistory.php';

    $ch = curl_init($path);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);

    curl_close($ch);

    $json_data = $response;

    $json_to_array = json_decode($json_data);

    return $json_data;
}

/**
 * Get order history
 * @param mixed $consumer_id
 * @param mixed $sid
 * @return mixed
 */
function UpdatePaymentDetails($action,$sid,$transaction_ids, $payment_schedule){

    if (!function_exists("curl_init"))
        load_curl();

    $post = [
        'action' => $action,
        'sid' => $sid,
        'CustomerTRX' => $transaction_ids,
        'PaymentSchedule' => $payment_schedule
    ];

    $path = 'https://'.$_SERVER['HTTP_HOST'].'/cgi-bin/bissell.cfg/php/custom/paymentservice.php';

    $ch = curl_init($path);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);

    curl_close($ch);

    $json_data = $response;

    $json_to_array = json_decode($json_data);

    return $json_data;
}

/**
 * Get a specific menu list
 * @param mixed $package
 * @param mixed $object
 * @param mixed $field
 * @return mixed
 */
function GetMenuList($package,$object,$field){

    global $cxObjHelper;
    return $cxObjHelper->GetCustomObjectMenuItems($package,$object,$field);
}

/**
 * Get the ISO country list
 * @return mixed
 */
function GetCountryISOList(){

    global $cxObjHelper;
    return $cxObjHelper->GetCountryISOList();
}

/**
 * Get the province list
 * @return mixed
 */
function GetProvinceList(){

    global $cxObjHelper;
    return $cxObjHelper->GetProvinceList();
}

/**
 * Get the list of payment methods
 * @param mixed $countryId
 * @return mixed
 */
function GetPaymentMethods($countryId){

    global $cxObjHelper;
    return $cxObjHelper->GetPaymentMethodsByCountry($countryId);
}

function GetProductRecallList($countryId){
	global $cxObjHelper;
    return $cxObjHelper->GetProductRecallList($countryId);
}

/**
 * Get list of ship methods
 * @param mixed $countryId
 * @param mixed $orderType
 * @return mixed
 */
function GetShipMethods($countryId,$orderType){

    global $cxObjHelper;
    return $cxObjHelper->GetShipMethodsByCountry($countryId,$orderType);
}

function GetOrderBrands($countryId){

    global $cxObjHelper;
    return $cxObjHelper->GetOrderBrandsByCountry($countryId);
}

function GetOrderNoChargeReasons($countryId){

    global $cxObjHelper;
    return $cxObjHelper->GetOrderNoChargeReasonsByCountry($countryId);
}

/*
function GetShipMethodReasonCodes(){

global $cxObjHelper;
return $cxObjHelper->GetShipMethodReasonCodes();
}

function GetReasonCodes(){

global $cxObjHelper;
return $cxObjHelper->GetReasonCodes();
}

 */

function GetOrderChannelList(){
    global $cxObjHelper;
    return $cxObjHelper->GetOrderChannelList();
}

/**
 * Get the reason code list
 * @return mixed
 */
function GetReasonCodeList(){

    global $cxObjHelper;
    return $cxObjHelper->GetReasonCodeList();
}

/**
 * Get configuration values
 * @param mixed $configKey
 * @return mixed
 */
function GetConfiguration($configKey){
    global $cxObjHelper;
    return $cxObjHelper->GetConfiguration($configKey);
}

/**
 * Edit an order line
 * @param mixed $lineData
 * @return mixed
 */
function EditOrderLine($lineData){

    global $cxObjHelper;

    $line = $lineData['data'][$_GET['id']];

    $orderLineId = $line['lineid'];

    $responseData = $cxObjHelper->EditOrderLine(json_encode($line));

    return $responseData;
}

/**
 * Update the tracking numbers on order lines
 * @param mixed $orderHeaderId
 * @param mixed $lineJson
 * @return mixed
 */
function UpdateOrderLineTrackingNumber($orderHeaderId,$lineJson){

    global $cxObjHelper;
    return $cxObjHelper->UpdateOrderLineTrackingNumber($orderHeaderId,$lineJson);
}

/**
 * Delete an order line
 * @param mixed $lineData
 * @return string
 */
function DeleteOrderLine($lineData){

    global $cxObjHelper;

    $line = $lineData['data'][$_GET['id']];

    $orderHeaderId = $line['orderheaderid'];

    $orderLineId = $line['lineid'];

    //If the line id is < 0 return an empty object. This handles lines in the grid
    //that are not persisted to the lines object. An example is shipping.

    if((int)$orderLineId < 1)
        return '{}';

    $cxObjHelper->DeleteOrderLine($orderHeaderId,$orderLineId);

    return '{}';
}

/**
 * Add an order line
 * @param mixed $lineData
 * @return mixed
 */
function AddOrderLine($lineData){

    global $cxObjHelper;

    $lineResponse =  array();

    $orderHeaderId = $lineData['OrderHeaderId'];
    $lineResponse['line'] = array();
    $lineResponse['line']["orderheaderid"] = $orderHeaderId;
    $lineResponse['line']["ordernumber"] = $lineData['OrderNumber']; //: orderNumber,
    $lineResponse['line']["line"] = $lineData['Line']; //: lineNumber + 1,
    $lineResponse['line']["part"] = $lineData['PartNumber']; //: orderLine.PartNumber,
    $lineResponse['line']["qty"] = $lineData['Quantity']; //: orderLine.Quantity,
    $lineResponse['line']["uom"] = $lineData['UnitOfMeasure']; //: "EA",
    $lineResponse['line']["description"] = $lineData['PartDescription']; //: orderLine.PartDescription,
    $lineResponse['line']["unitsellingprice"] = $lineData['UnitPrice']; //: $('#unitprice').val(),
    $lineResponse['line']["adjustedprice"] = $lineData['AdjustedPrice']; //: orderLine.Price,
    $lineResponse['line']["status"] = $lineData['Status']; //: "Entered",
    $lineResponse['line']["linetype"] = $lineData['LineType']; //: "New",
    $lineResponse['line']["inv"] = $lineData['Inventory'];; //: "Y",
    $lineResponse['line']["aerosol"] = $lineData['Aerosol'];; //: "Y",
    $lineResponse['line']["reasoncode"] = $lineData['ReasonCode'];; //: "Y",
    $lineResponse['line']["nocharge"] = $lineData['NoCharge'];; //: "Y",
    $lineResponse['line']["serialnumber"] = $lineData['SerialNumber'];; //: "Y",
    $lineResponse['line']["modelnumber"] = $lineData['ModelNumber'];; //: "Y",
    $lineResponse['line']["lineid"] = ""; //:""

    $lineJSON = json_encode($lineData);

    $addLineResponse = $cxObjHelper->CreateOrderLine($orderHeaderId,$lineJSON);

    return $addLineResponse;

    //$lineResponse['line']["lineid"] = $orderLienID;

    //return json_encode($lineResponse);
}

/**
 * Retrun order lines
 * @param mixed $orderHeaderId
 * @return mixed
 */
function GetOrderLines($orderHeaderId,$lineType){

    global $cxObjHelper;
    return $cxObjHelper->GetOrderLines($orderHeaderId,$lineType);

}

function GetRAReasonList($returnTypeId,$countryISO){
    global $cxObjHelper;
    return $cxObjHelper->GetRAReasonList($returnTypeId,$countryISO);
}

function GetRAActionList($returnTypeId,$countryISO){
    global $cxObjHelper;
    return $cxObjHelper->GetRAActionList($returnTypeId,$countryISO);
}

function GetRAReturnLocationList($returnTypeId,$countryISO){
    global $cxObjHelper;
    return $cxObjHelper->GetRAReturnLocationList($returnTypeId,$countryISO);
}

function GetRAReturnMethodList($returnTypeId,$countryISO){
    global $cxObjHelper;
    return $cxObjHelper->GetRAReturnmethodList($returnTypeId,$countryISO);
}

function GetRAReturnTypeList($incidentType,$country){
	global $cxObjHelper;
	return $cxObjHelper->GetRAReturnTypeList($incidentType,$country);
}

function LogSysIntegration($info){
    global $cxObjHelper;
    return $cxObjHelper->LogSysIntegration($info);
}

function LockOrderLine($info){
    global $cxObjHelper;
    return $cxObjHelper->LockOrderLine($info);
}

function UpdateOrderLock($orderLockID, $info){
    global $cxObjHelper;
    return $cxObjHelper->UpdateOrderLock($orderLockID, $info);
}

function DeleteOrderLock($orderLockID){

    global $cxObjHelper;
    return $cxObjHelper->DeleteOrderLock($orderLockID);
}

function GetOrderOrderLocks($orderHeaderID){

    global $cxObjHelper;
    return $cxObjHelper->GetOrderLocks($orderHeaderID);
}

function GetWarehouseByReturnLocation($returnLocationId){
    global $cxObjHelper;
    return $cxObjHelper->GetWarehouseByReturnLocation($returnLocationId);
}

function GetAssetDetails($assetID){
    global $cxObjHelper;
    return $cxObjHelper->GetAssetDetails($assetID);
}

function GetCategoryText($catID){
    global $cxObjHelper;
    return $cxObjHelper->GetCategoryText($catID);
}

?>