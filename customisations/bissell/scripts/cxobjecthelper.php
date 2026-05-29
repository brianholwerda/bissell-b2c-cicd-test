<?php

require_once('agentsessionauth.php');

/***
 * This file handles the interaction with CX using the Connect PHP API
 * It serves as the Model for data interaction for the Bissell Order Manger web app
 */

// Set up versioned namespace for the Connect PHP API
use RightNow\Connect\v1_3 as RNCPHP;

class CXObjectHelper{

    public function GetProductRecallList($countryId){
		$objectResponseArray = Array();
		
		$query = sprintf("select Country, IsActive, Product.LookupName as 'Product', ProductRecall.LookupName as 'Recall', RecallOverviewInfo from BI.ProductRecallConfig RC Where RC.Country = %s and IsActive = 1",$countryId);
		
		$objects = RNCPHP\ROQL::query($query)->next();
		
		while($object = $objects->next()){
			$objectResponseArray[] = array("product"=>$object['Product'],"recall"=>$object['Recall'],"recalloverviewinfo"=>$object['RecallOverviewInfo']);
		}
		
		return json_encode($objectResponseArray);
	}
	
	public function GetRAReturnTypeList($incidentType,$countryId){
		$objectResponseArray = Array();

        $query = sprintf("Select BUS.RAReturnTypeConfig From BUS.RAReturnTypeConfig RTC Where RTC.Country = %s",$countryId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();
		
		while($object = $objects->next()){
			$configObject = json_decode($object->Config);
			
			if(in_array($incidentType,$configObject->IncidentTypes)){
				$objectResponseArray[] = array("id"=>$configObject->custommenuid,"label"=>$configObject->label,"active"=>$configObject->IsActive,"displayOrder"=>$configObject->DisplayOrder);
			}
		}
		
		return json_encode($objectResponseArray);
	}
	
	public function GetRAReturnMethodList($returnTypeId,$countryISO){

        $objectResponseArray = Array();

        $query = sprintf("Select BUS.RAReturnMethodConfig From BUS.RAReturnMethodConfig RAM Where RAM.ReturnType.ID = %s",$returnTypeId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();

        while($object = $objects->next()){

            $configObject = json_decode($object->Config);

            if(in_array($countryISO,$configObject->country)){

                $objectResponseArray[] = array("id"=>$object->ID,"label"=>$configObject->label,"code"=>$configObject->code,"active"=>$configObject->IsActive,"provider"=>$configObject->Provider);

            }

        }

        return json_encode($objectResponseArray);

    }

    public function GetRAReturnLocationList($returnTypeId,$countryISO){

        $objectResponseArray = Array();

        $query = sprintf("Select BUS.RARetLocationConfig From BUS.RARetLocationConfig RAL Where RAL.ReturnType.ID = %s",$returnTypeId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();

        $provAbbrConfig = $this->GetConfiguration(CUSTOM_CFG_PROVINCE_ABBREVIATION_JSON);

        $proAbbrObj = json_decode($provAbbrConfig);

        while($object = $objects->next()){

            $configObject = json_decode($object->Config);
            $warehouseArray = array();
            $countryId = $object->Warehouse->Country->ID;
            $stateName = $object->Warehouse->State->LookupName;
            $abbrList = $proAbbrObj->countryid->{$countryId};
            $stateAbbr = "";
            foreach($abbrList as $key=>$value){
                if($value == $stateName){
                    $stateAbbr = $key;
                    break;
                }
            }
            //$stateAbbr = array_search($stateName,$proAbbrObj['countryid'][$countryId]);

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

            if(in_array($countryISO,$configObject->country)){

                $objectResponseArray[] = array("id"=>$object->ID,"location"=>$configObject->location,"action"=>$configObject->action,"code"=>$object->Warehouse->Code,"warehouse"=>$warehouseArray,"active"=>$configObject->IsActive);

            }

        }

        return json_encode($objectResponseArray);

    }

    function GetWarehouseByReturnLocation($returnLocationId){

        $query = sprintf("Select BUS.RARetLocationConfig From BUS.RARetLocationConfig RAL Where ID = %s",$returnLocationId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();

        $provAbbrConfig = $this->GetConfiguration(CUSTOM_CFG_PROVINCE_ABBREVIATION_JSON);

        $proAbbrObj = json_decode($provAbbrConfig);

        $warehouseArray = array();

        while($object = $objects->next()){

            $countryId = $object->Warehouse->Country->ID;
            $stateName = $object->Warehouse->State->LookupName;
            $abbrList = $proAbbrObj->countryid->{$countryId};
            $stateAbbr = "";
            foreach($abbrList as $key=>$value){
                if($value == $stateName){
                    $stateAbbr = $key;
                    break;
                }
            }
            //$stateAbbr = array_search($stateName,$proAbbrObj['countryid'][$countryId]);

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

            return json_encode($warehouseArray);

    }

    /*
    public function GetRAReturnLocationList($returnTypeId,$countryISO){

    $objectResponseArray = Array();

    $query = sprintf("Select BUS.RARetLocationConfig From BUS.RARetLocationConfig RAL Where RAL.ReturnType.ID = %s",$returnTypeId);

    $objects = RNCPHP\ROQL::queryObject($query)->next();

    while($object = $objects->next()){

    $configObject = json_decode($object->Config);

    if(in_array($countryISO,$configObject->country)){

    $objectResponseArray[] = array("id"=>$object->ID,"location"=>$configObject->location,"action"=>$configObject->action,"code"=>$configObject->code);

    }

    }

    return json_encode($objectResponseArray);

    }

     */

    public function GetRAActionList($returnTypeId,$countryISO){

        $objectResponseArray = Array();

        $query = "Select BUS.RAActionConfig From BUS.RAActionConfig RAA";

        if((int)$returnTypeId > 0)
            $query = sprintf("Select BUS.RAActionConfig From BUS.RAActionConfig RAA Where RAA.ReturnType.ID = %s",$returnTypeId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();

        while($object = $objects->next()){

            $configObject = json_decode($object->Config);

            if(in_array($countryISO,$configObject->country)){
                $requireReplacement = false;
                if($configObject->RequireReplacement){
                    $requireReplacement = $configObject->RequireReplacement;
                }
                $objectResponseArray[] = array("id"=>$object->ID,"label"=>$configObject->label,"code"=>$configObject->code,"requirereplacement"=>$requireReplacement,"active"=>$configObject->isActive);
            }

        }

        return json_encode($objectResponseArray);

    }

    public function GetRAReasonList($returnTypeId,$countryISO){

        $objectResponseArray = Array();

        $query = "Select BUS.RAReasonConfig From BUS.RAReasonConfig RAR ";

        if((int)$returnTypeId > 0)
            $query = sprintf("Select BUS.RAReasonConfig From BUS.RAReasonConfig RAR Where RAR.ReturnType.ID = %s",$returnTypeId);

        $objects = RNCPHP\ROQL::queryObject($query)->next();

        while($object = $objects->next()){

            $configObject = json_decode($object->Config);

            if(in_array($countryISO,$configObject->country)){

                $objectResponseArray[] = array("id"=>$object->ID,"label"=>$configObject->label,"code"=>$configObject->code,"requiresserialnumber"=>$configObject->RequireSN,"requiresmodelnumber"=>$configObject->RequireMN,"active"=>$configObject->isActive);
            }

        }

        return json_encode($objectResponseArray);

    }

    /**
     * Summary of GetCustomObjectMenuItems
     * @param mixed $package
     * @param mixed $object
     * @param mixed $field
     * @return string
     */
    public function GetCustomObjectMenuItems($package, $object, $field){
        try{

            $menuArray =  Array();
            $query = sprintf("RightNow\\Connect\\v1_3\\%s\\%s.%s",$package,$object,$field);
            $objMenuItems = RNCPHP\ConnectAPI::getNamedValues($query);

            foreach($objMenuItems as $menuItem){
                $menuArray[$menuItem->ID ] = $menuItem->Name;
            }

            return json_encode($menuArray);

        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }
    }

    /**
     * Get the payment methods by country
     * @param mixed $countryId
     * @return string
     */
    public function GetPaymentMethodsByCountry($countryId){

        try{

            $paymentTypeArray = Array();

            $query = sprintf("Select BUS.CountryPaymentType From BUS.CountryPaymentType CPT Where CPT.Country = %s",$countryId);

            $paymentTypes = RNCPHP\ROQL::queryObject($query)->next();

            while($paymentType = $paymentTypes->next()){

                $paymentTypeArray[$paymentType->PaymentType->ID] = $paymentType->PaymentType->Name;

            }

            return json_encode($paymentTypeArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }
    }

    /**
     * Get the country ISO list
     * @return string
     */
    public function GetCountryISOList(){
        try{

            $cArray = Array();

            $query = sprintf("select Country from Country");

            $countries = RNCPHP\ROQL::queryObject($query)->next();

            while($country = $countries->next()){

                $cArray[$country->ID] = $country->ISOCode;

            }

            return json_encode($cArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }
    }

    /**
     * Get the provice list
     * @return string
     */
    public function GetProvinceList(){
        try{

            $pArray = Array();

            $query = sprintf("SELECT ID as CountryID, Country.provinces.id as ProvinceID,Country.provinces.name as ProvinceName FROM Country LIMIT 10000");

            $provinces = RNCPHP\ROQL::query($query)->next();

            while($province = $provinces->next()){

                $pArray[] = array("countryid"=>$province['CountryID'],"provinceid"=>$province['ProvinceID'],"provincename"=>$province['ProvinceName']);

            }

            return json_encode($pArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }
    }

    /**
     * Get configuration value
     */
    public function GetConfiguration($configKey){

        $cfg = RNCPHP\Configuration::fetch( $configKey );
        return $cfg->Value;
    }

    /**
     * GetShipMethodReasonCodes
     * @return string
     */
    public function GetShipMethodReasonCodes(){
        try{

            $shipReasonArray = Array();

            $query = sprintf("Select BUS.ShipMethodReasonCode From BUS.ShipMethodReasonCode SMR Order By SMR.Sequence ASC");

            $shipReasons = RNCPHP\ROQL::queryObject($query)->next();

            while($shipReason = $shipReasons->next()){

                $shipReasonArray[] = array("id"=>$shipReason->ID,"label"=>$shipReason->Code,"requiresserialnumber"=>$shipReason->RequiresSearialNumber);

            }

            return json_encode($shipReasonArray);

        }
        catch(Exception $e){
            echo $e->getMessage();
            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }
    }

    /**
     * Get the reason code list
     * @return string
     */
    public function GetReasonCodeList(){
        try{

            $reasonArray = Array();

            $query = sprintf("Select BUS.ReasonCodeLookup From BUS.ReasonCodeLookup");

            $reasons = RNCPHP\ROQL::queryObject($query)->next();

            while($reason = $reasons->next()){

                $reasonArray[] = array("id"=>$reason->ID,"name"=>$reason->Name,"code"=>$reason->Code,"reasontype"=>$reason->ReasonType->ID,"requiresserialnumber"=>$reason->RequireSN,"requiresmodelnumber"=>$reason->RequireMN);

            }

            return json_encode($reasonArray);

        }
        catch(Exception $e){
            echo $e->getMessage();
            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }
    }

    /**
     * Get reason codes. TODO: This is being replaced by GetReasonCodeList
     * @return string
     */
    public function GetReasonCodes(){

        try{

            $menuArray =  Array();

            $query = sprintf("Select R from BUS.ReasonCode R");

            $reasons = RNCPHP\ROQL::queryObject($query)->next();

            while($reason = $reasons->next()){

                $menuArray[$reason->ID] = $reason->Name;

            }

            return json_encode($menuArray);

        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }

    }

    public function GetOrderChannelList(){

        try{

            $menuArray =  Array();

            $query = sprintf("Select BUS.OrderChannel From BUS.OrderChannel");

            $channels = RNCPHP\ROQL::queryObject($query)->next();

            while($channel = $channels->next()){

                $menuArray[$channel->ID] = $channel->Name;

            }

            return json_encode($menuArray);

        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }

    }

    /**
     * Get the ship methods by country
     * @param mixed $countryId
     * @param mixed $orderType
     * @return string
     */
    public function GetShipMethodsByCountry($countryId, $orderType){

        try{

            $shipMethodsArray = Array();

            $query = sprintf("Select BUS.CountryShipMethod From BUS.CountryShipMethod CSM Where CSM.Country = %s",$countryId);

            //Filter to the list to US shipping methods for orders
            if($countryId == 1 && $orderType == 1){
                $query = sprintf("Select BUS.CountryShipMethod From BUS.CountryShipMethod CSM Where CSM.USOrderShipMethod = 1 AND CSM.Country = %s",$countryId);
            }

            $shipMethods = RNCPHP\ROQL::queryObject($query)->next();

            while($shipMethod = $shipMethods->next()){
                $configObject = json_decode($shipMethod->Config);
                $shipMethodsArray[] = array("id"=>$shipMethod->ShipMethod->ID,"label"=>$shipMethod->ShipMethod->Name,"charges"=>json_decode($shipMethod->Charges),"airfreight"=>$shipMethod->AirFreight,"code"=>$configObject->Code,"expedited"=>$configObject->IsExpedited,"active"=>$configObject->IsActive,"displayOrder"=>$configObject->DisplayOrder);
            }

            return json_encode($shipMethodsArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }
    }
	
	/**
     * Get the order brands by country
     * @param mixed $countryId
     * @return string
     */
    public function GetOrderBrandsByCountry($countryId){

        try{

            $countryBrandsArray = Array();

            $query = sprintf("Select BUS.CountryOrderBrand From BUS.CountryOrderBrand COB Where COB.Country = %s",$countryId);

            $orderBrands = RNCPHP\ROQL::queryObject($query)->next();

            while($orderBrand = $orderBrands->next()){
                $configObject = json_decode($orderBrand->Config);
                $countryBrandsArray[] = array("id"=>$orderBrand->OrderBrand->ID,"label"=>$orderBrand->OrderBrand->Name,"overridesaleschannel"=>$configObject->OverrideSalesChannel,"bwssubmitorderconfig"=>$configObject->BWSSubmitOrderConfig,"active"=>$configObject->IsActive,"saleschannel"=>$configObject->SalesChannel,"incidentbrand"=>$configObject->IncidentBrand);
            }

            return json_encode($countryBrandsArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }
    }
	
	/**
     * Get order no charge reasons by country
     * @param mixed $countryId
     * @return string
     */
    public function GetOrderNoChargeReasonsByCountry($countryId){

        try{

            $orderReasonsArray = Array();

            $query = sprintf("SELECT BUS.NoChargeReasonConfig From BUS.NoChargeReasonConfig NCR Where NCR.Country = %s",$countryId);

            $orderReasons = RNCPHP\ROQL::queryObject($query)->next();

            while($orderReason = $orderReasons->next()){
                $configObject = json_decode($orderReason->Config);
                $orderReasonsArray[] = array("id"=>$orderReason->NoChargeReasons->ID,"label"=>$orderReason->NoChargeReasons->Name,"active"=>$configObject->IsActive,"linktoordernumber"=>$configObject->LinkToOrderNumber,"ordertype"=>$configObject->OrderType);
            }

            return json_encode($orderReasonsArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }
    }

    /**
     * Edit order line
     * @param mixed $lineJSON
     * @return string
     */
    public function EditOrderLine($lineJSON){

        //Only the quantity and adjusted price can be updated
        $lineObject = json_decode($lineJSON);
        $orderLine =  RNCPHP\BUS\OrderLine::fetch($lineObject->lineid);
        $orderLine->Quantity = $lineObject->qty;
        $orderLine->AdjustedPrice = $lineObject->adjustedprice;
        $orderLine->save();

        //Set the line identifier for the grid (this is not the same as the line id)
        $lineArray = array("data"=>array());
        $lineArray['data'][] = $this->getOrderLineArrayFromObject($orderLine,$lineObject->line);

        return json_encode($lineArray);

    }

    /**
     * Delete an order line
     * @param mixed $orderHeaderId
     * @param mixed $orderLineId
     */
    public function DeleteOrderLine($orderHeaderId,$orderLineId){

        $orderLine = RNCPHP\BUS\OrderLine::fetch($orderLineId);
        $orderLine->destroy();

        RNCPHP\ConnectAPI::commit();
    }

    /**
     * Create an OrderLine object
     * @param mixed $orderHeaderID
     * @param mixed $lineJSON
     * @return string
     */
    public function CreateOrderLine($orderHeaderId, $lineJSON){

        try{

            $lineObject = json_decode($lineJSON);

            $orderLine = new RNCPHP\BUS\OrderLine();
            $orderLine->OrderHeader = RNCPHP\BUS\OrderHeader::fetch($orderHeaderId);

            if($lineObject->ReasonCode){
                $orderLine->ReasonCode = (int)$lineObject->ReasonCode;
            }

            $this->buildOrderLineObject($orderLine,$lineObject,"Status");
            $this->buildOrderLineObject($orderLine,$lineObject,"TrackingNumber");
            $this->buildOrderLineObject($orderLine,$lineObject,"PartNumber");
            $this->buildOrderLineObject($orderLine,$lineObject,"PartDescription");
            $this->buildOrderLineObject($orderLine,$lineObject,"Quantity");
            $orderLine->Price = $lineObject->UnitPrice;
            //$this->buildOrderLineObject($orderLine,$lineObject,"Price");
            $this->buildOrderLineObject($orderLine,$lineObject,"AdjustedPrice");
            $this->buildOrderLineObject($orderLine,$lineObject,"SerialNumber");
            $this->buildOrderLineObject($orderLine,$lineObject,"ModelNumber");
            $this->buildOrderLineObject($orderLine,$lineObject,"CouponSavings");
            $this->buildOrderLineObject($orderLine,$lineObject,"TotalAfterDiscount");
            $this->buildOrderLineObject($orderLine,$lineObject,"EstSalesTax");
            $this->buildOrderLineObject($orderLine,$lineObject,"SummaryInformation");
            $this->buildOrderLineObject($orderLine,$lineObject,"SubTotal");
            $this->buildOrderLineObject($orderLine,$lineObject,"Shipping");
            $this->buildOrderLineObject($orderLine,$lineObject,"SalesTax");
            $this->buildOrderLineObject($orderLine,$lineObject,"Discounts");
            $this->buildOrderLineObject($orderLine,$lineObject,"Total");
            $this->buildOrderLineObject($orderLine,$lineObject,"EstShipTax");
            $this->buildOrderLineObject($orderLine,$lineObject,"CouponCodeApplied");
            $this->buildOrderLineObject($orderLine,$lineObject,"SKU");
            $this->buildOrderLineObject($orderLine,$lineObject,"CouponCode");
            $this->buildOrderLineObject($orderLine,$lineObject,"RefundRequestOrderNum");
            $this->buildOrderLineObject($orderLine,$lineObject,"RefundId");
            $this->buildOrderLineObject($orderLine,$lineObject,"RefundStatus");
            $this->buildOrderLineObject($orderLine,$lineObject,"RefundUrgency");
            $this->buildOrderLineObject($orderLine,$lineObject,"DRTVPaymentInfo");
            $this->buildOrderLineObject($orderLine,$lineObject,"PaymentNumber");
            $this->buildOrderLineObject($orderLine,$lineObject,"PaymentDueDate");
            $this->buildOrderLineObject($orderLine,$lineObject,"OriginalPaymentAmount");
            $this->buildOrderLineObject($orderLine,$lineObject,"AmountStillOwed");
            $this->buildOrderLineObject($orderLine,$lineObject,"CreditCardInfo");
            $this->buildOrderLineObject($orderLine,$lineObject,"SettlementResponse");
            $this->buildOrderLineObject($orderLine,$lineObject,"UnitOfMeasure");
            $this->buildOrderLineObject($orderLine,$lineObject,"Inventory");
            $this->buildOrderLineObject($orderLine,$lineObject,"ReasonCode");
            $this->buildOrderLineObject($orderLine,$lineObject,"NoCharge");
            $this->buildOrderLineObject($orderLine,$lineObject,"Aerosol");
            $this->buildOrderLineObject($orderLine,$lineObject,"LineType");
            $this->buildOrderLineObject($orderLine,$lineObject,"ActionCode");

            $orderLine->save();
            $lineArray = array("line"=>array());
            $lineArray['line'] = $this->getOrderLineArrayFromObject($orderLine,$lineObject->Line);

            return json_encode($lineArray);

        }
        catch(Exception $e){
            return $e->getMessage();
            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );
        }

    }

    /**
     * Update order line tracking numbers
     * @param mixed $orderHeaderId
     * @param mixed $lineJSON
     * @return boolean|string
     */
    public function UpdateOrderLineTrackingNumber($orderHeaderId, $lineJSON){

        try{

            $lineObject = json_decode($lineJSON);

            $query = "Select OL from BUS.OrderLine OL where OL.OrderHeader = ". $orderHeaderId;

            $orderLines = RNCPHP\ROQL::queryObject($query)->next();

            while($orderLine = $orderLines->next()){

                foreach($lineObject as $line){

                    if($line->Sku == $orderLine->PartNumber){
                        $orderLine->TrackingNumber = $line->TrackingNumber;
                        $orderLine->save();
                    }
                }
            }

            return true;

        }
        catch(Exception $e){

            return $e->getMessage();

        }

    }

    /**
     * Get the order lines by order header id
     * @param mixed $orderHeaderId
     */
    public function GetOrderLines($orderHeaderId,$lineType){

        $query = "Select OL from BUS.OrderLine OL where OL.OrderHeader = ". $orderHeaderId;

        if($lineType){
            $query = "Select OL from BUS.OrderLine OL where OL.OrderHeader = ". $orderHeaderId . " and OL.LineType =". $lineType;
        }

        $orderLines = RNCPHP\ROQL::queryObject($query)->next();
        $orderLinesArray = array("aaData"=>array());
        $lineNumber = 1;

        while($orderLine = $orderLines->next()){

            $orderLinesArray['aaData'][] = $this->getOrderLineArrayFromObject($orderLine,$lineNumber);

            $lineNumber++;

        }

        return json_encode($orderLinesArray);
    }

    /**
     * Convert an orderline object to an array for the response
     * @param mixed $orderLine
     * @param mixed $lineNumber
     * @return array
     */
    function getOrderLineArrayFromObject($orderLine,$lineNumber){

        $responseArray = array("lineid"=>$orderLine->ID,
                "orderheaderid"=>$orderLine->OrderHeader->ID,
                "ordernumber"=>"",
                "line"=>$lineNumber,
                "part"=>$orderLine->PartNumber,
                "description"=>$orderLine->PartDescription,
                "unitsellingprice"=>$orderLine->Price,
                "qty"=>$orderLine->Quantity,
                "uom"=>$orderLine->UnitOfMeasure,
                "adjustedprice"=>$orderLine->AdjustedPrice,
                "status"=>$orderLine->Status,
                "linetype"=>$orderLine->LineType,
                "inv"=>$orderLine->Inventory,
                "aerosol"=>$orderLine->Aerosol,
                "nocharge"=>$orderLine->NoCharge,
                "reasoncode"=>$orderLine->ReasonCode,
                "modelnumber"=>$orderLine->ModelNumber,
                "serialnumber"=>$orderLine->SerialNumber,
                "trackingnumber"=>$orderLine->TrackingNumber,
                "actioncode"=>$orderLine->ActionCode
                );

        return $responseArray;
    }

    /***
     * Private Functions
     */
    /**
     * Create the order line by setting the object attributes based on the matching field name passed in.
     * @param mixed $lineObject
     * @param mixed $sourceObject
     * @param mixed $fieldName
     */
    private function buildOrderLineObject(&$lineObject,$sourceObject,$fieldName){

        if($sourceObject->{$fieldName}){

            $lineObject->{$fieldName} = $sourceObject->{$fieldName};

        }
    }

    function LogSysIntegration($info)
	{
		try
		{
			// Create BUS$SysIntegrationLog record
			$sysLog = new RNCPHP\BUS\SysIntegrationLog();

			if(isset($info['ExtSystem']))
				$sysLog->ExtSystem = $info['ExtSystem'];
			if(isset($info['RequestMsg']))
				$sysLog->RequestMsg = $info['RequestMsg'];
			if(isset($info['ResponseMsg']))
				$sysLog->ResponseMsg = $info['ResponseMsg'];
			if(isset($info['Description']))
				$sysLog->Description = $info['Description'];
			if(isset($info['Error']))
				$sysLog->Error = $info['Error'];
			if(isset($info['IncidentID']))
				$sysLog->Incident = RNCPHP\Incident::fetch($info['IncidentID']);
			if(isset($info['ContactID']))
				$sysLog->Contact = RNCPHP\Contact::fetch($info['ContactID']);
			if(isset($info['OrderHeaderID']))
				$sysLog->OrderHeader = RNCPHP\BUS\OrderHeader::fetch($info['OrderHeaderID']);

			$sysLog->save();
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	function LockOrderLine($json)
	{
		try
		{
			// // Create BUS$SysIntegrationLog record
			$ordLock = new RNCPHP\BUS\OrderLocks();
			
			if(isset($json['Source'])){
				$ordLock->Source = $json['Source'];
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'Source' field missing"
				);
				return json_encode($response);
			}
				
			if(isset($json['OriginalOrderNumber'])){
				$ordLock->OriginalOrderNumber = $json['OriginalOrderNumber'];
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'OriginalOrderNumber' field missing"
				);
				return json_encode($response);
			}
				
			if(isset($json['SKU']))
				$ordLock->SKU = $json['SKU'];
			
			if(isset($json['EBSLineID'])){
				//$ordLock->EBSLineID = $json['EBSLineID'];
				$ordLock->EBSLineID = ltrim(substr($json['EBSLineID'], -10), '0');
			} else {
				$response = array(
					"OrderLockID" => null,
					"ResponseMessage" => "Error: 'EBSLineID' field missing"
				);
				return json_encode($response);
			}
				
			if(isset($json['NewOrderNumber']))
				$ordLock->NewOrderNumber = $json['NewOrderNumber'];
			if(isset($json['IncidentID']))
				$ordLock->IncidentID = RNCPHP\Incident::fetch($json['IncidentID']);
			if(isset($json['OrderHeaderID']))
				$ordLock->OrderHeaderID = RNCPHP\BUS\OrderHeader::fetch($json['OrderHeaderID']);
			
			$ordLock->save();
			$response = array(
				"OrderLockID" => $ordLock->ID,
				"ResponseMessage" => "Success"
			);
			return json_encode($response);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	public function DeleteOrderLock($orderLockID){

        try
		{
			$orderLock = RNCPHP\BUS\OrderLocks::fetch($orderLockID);
			$orderLock->destroy();

			RNCPHP\ConnectAPI::commit();
			
			$response = array(
				"OrderLockID" => $orderLockID,
				"ResponseMessage" => "Success"
			);
			return json_encode($response);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
    }
	
	public function UpdateOrderLock($orderLockID, $json){

        try
		{
			$orderLock = RNCPHP\BUS\OrderLocks::fetch($orderLockID);
			
			if(isset($json['NewOrderNumber']))
				$orderLock->NewOrderNumber = $json['NewOrderNumber'];
			
			$orderLock->save();
			
			$response = array(
				"OrderLockID" => $orderLock->ID,
				"ResponseMessage" => "Success"
			);
			return json_encode($response);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
    }
	
	public function GetOrderLocks($orderHeaderID){

        try{

            $orderLocksArray = Array();

            $query = sprintf("select BUS.OrderLocks from BUS.OrderLocks OL where OL.OrderHeaderID = %s",$orderHeaderID);

            $orderLocks = RNCPHP\ROQL::queryObject($query)->next();

            while($orderLock = $orderLocks->next()){
                $configObject = json_decode($orderBrand->Config);
                $orderLocksArray[] = array("id"=>$orderLock->ID,"sku"=>$orderLock->SKU,"ebslineid"=>$orderLock->EBSLineID,"originalordernumber"=>$orderLock->OriginalOrderNumber);
            }

            return json_encode($orderLocksArray);
        }
        catch(Exception $e){

            //logMessage("Failed calling " . __FUNCTION__ . " in ". __FILE__ );
            //logMessage("Exception Message was: " . $e->getMessage() );

        }
    }
	
	public function GetAssetDetails($assetID){
		$asset = RNCPHP\Asset::fetch($assetID);
		$response = array(
			"AssetID" => $asset->ID,
			"LookupName" => $asset->LookupName,
			"SerialNumber" => $asset->SerialNumber,
			"ProductName" => $asset->Product->LookupName,
			"ResponseMessage" => "Success"
		);
		return json_encode($response);
	}
	
	public function GetCategoryText($catID){
		$catArray = Array();
		
		$query1="select * from ServiceCategory where ID = " . $catID; 
		$getCat1 = RNCPHP\ROQL::query($query1)->next()->next();
		$catArray[] = array("id"=>$getCat1['ID'],"lookupname"=>$getCat1['LookupName'],"parent"=>$getCat1['Parent']);
		
		if(isset($getCat1['Parent'])){
			$query2="select * from ServiceCategory where ID = " . $getCat1['Parent']; 
			$getCat2 = RNCPHP\ROQL::query($query2)->next()->next();
			$catArray[] = array("id"=>$getCat2['ID'],"lookupname"=>$getCat2['LookupName'],"parent"=>$getCat2['Parent']);
			if(isset($getCat2['Parent'])){
				$query3="select * from ServiceCategory where ID = " . $getCat2['Parent']; 
				$getCat3 = RNCPHP\ROQL::query($query3)->next()->next();
				$catArray[] = array("id"=>$getCat3['ID'],"lookupname"=>$getCat3['LookupName'],"parent"=>$getCat3['Parent']);
			}
		}
		
		return json_encode($catArray);
	}
}
/*
try{
$oh = new CXObjectHelper();
$info = array(    "ExtSystem" => "raTesting",
    "RequestMsg" => "raTesting",
    "ResponseMsg" => "raTesting",
    "Error" => "raTesting",
    "Description" => "raTesting",
    "IncidentID" => "1695",
    "ContactID" => "2157",
    "OrderHeaderID" => "1169"
);
echo $oh->LogSysIntegration($info);
}
catch(Exception $e){
echo $e->getMessage();
}
*/
?>