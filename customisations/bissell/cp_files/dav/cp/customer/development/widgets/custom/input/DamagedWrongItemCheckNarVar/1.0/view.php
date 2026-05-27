<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<?  $todays_date = date('Y-m-d'); 
	
	//Replace $WDI_type with $RR_type
	$RR_type = getUrlParm("rr");
	if($RR_type == ""){
		$RR_type = 1;
	}
	$WDI_type = getUrlParm("wd");
	
	$referrer = $this->data['attrs']['referrer'];
	if($referrer == "CA") {
		$cancelBtnLink = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_WDMR_CA_CANCEL_BTN_LINK);
	} else {
		$cancelBtnLink = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_WDMR_CANCEL_BTN_LINK);
	}
	
	//print_r($cancelBtnLink);
	
	$OrderNumberLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_OrderNumberLimit);
	$RefundNumberLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_RefundNumberLimit);
	$reservableInventoryLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_INVENTORY_LIMIT);
	$productPriceLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_PRODUCT_DOLLAR_VALUE);
	
	/*print("this is the order num param ");
	echo "<br>";
	print_r($this->data['attrs']['orderNum']);
	echo "<br>";
	print("this is the ContactID param ");
	echo "<br>";
	print_r($this->data['attrs']['contactID']);
	echo "<br>";
	print("this is the prodSku param ");
	echo "<br>";
	print_r($this->data['attrs']['prodSku']);	
	echo "<br>";
	print("this is the itemfound param ");
	echo "<br>";
	print_r($this->data['attrs']['itemFound']);	
	echo "<br>"; */
	
	if($this->data['attrs']['IncidentExists'] == 1) {
	?>
		<div>
			#rn:msg:CUSTOM_MSG_WDMR_INCIDENT_EXISTS#
		</div>
	<?	
		return false;
	}
	
	if (is_null($this->data['attrs']['contactID']) || is_null($this->data['attrs']['orderNum']) || is_null($this->data['attrs']['prodSku']) || $this->data['attrs']['itemFound'] == "0" ) {
	?>
		<div>
			#rn:msg:CUSTOM_MSG_WDMR_MISSING_PARAMETERS#
		</div>
	<?
		return false;	
	}

	$debugIt = getUrlParm("debug");
	$debugging_output = "<br>This is the Damaged Wrong Check NarVar Item Flow<br>";
	if($debugIt == 1) {	?>
	 <style>
		.rn_WDMR_rnHidden {
			display: block;
		} 
	 </style>
<?	} else { ?>
		 <style>
		.rn_WDMR_rnHidden {
			display: none;
		} 
	 </style>
<?  }
	
/*	echo "<br>";
	print("this is the WDI Type");
	echo "<br>";
	print_r($this->data['attrs']['WDI_type']);  //0 = refund 1 = reship
	echo "<br>";
	echo "<br>";
	print("this is the RR Type");
	echo "<br>";
	print_r($this->data['attrs']['RR_type']);  //0 = refund 1 = reship
	echo "<br>";
	print("this is the order num param ");
	echo "<br>";
	print_r($this->data['attrs']['orderNum']);
	echo "<br>";
	print("this is the ContactID param ");
	echo "<br>";
	print_r($this->data['attrs']['contactID']);
	echo "<br>";
	print_r($this->data['attrs']['emailAddr']);
	echo "<br>";
	print("this is the ConsumerID param ");
	echo "<br>";
	print_r($this->data['attrs']['consumerID']);
	echo "<br>";
	print("this is the prodSku param ");
	echo "<br>";
	print_r($this->data['attrs']['prodSku']);	
	echo "<br>";
	print("this is data js ");
	echo "<br>";
	print_r($this->data['js']);
	echo "<br>";
	print("this is getOrder ");
	echo "<br>";
	print_r($this->data['getOrder']);
	echo "<br>";
	print("this is the issue type ");
	echo "<br>";
	print_r($this->data['attrs']['issueType']);
	echo "<br>";
	print("this is the is Narvar Eligible ");
	echo "<br>";
	print_r($this->data['attrs']['isNarvarEligible']);
	echo "<br>";
	print("this is the isHazardous ");
	echo "<br>";
	print_r($this->data['attrs']['isHazardous']);
	echo "<br>";
	print("this is the No Charge Order Count ");
	echo "<br>";
	print_r($this->data['attrs']['NoChargeOrderCount']);
	echo "<br>";
	print("this is the Refund Count ");
	echo "<br>";
	print_r($this->data['attrs']['RefundCount']);
	echo "<br>";
	print("this is the Active SKU toggle ");
	echo "<br>";
	print_r($this->data['attrs']['ActiveSKU']);
	echo "<br>";
	print("This is the shipping address formatted:");
	echo "<br>";
	print_r($this->data['attrs']['shippingAddr']);
	echo "<br>";
	print("This is the Product Price rounded:");	
	echo "<br>"; 
	print_r($this->data['attrs']['productPriceCurrent']);
	echo "<br>";
*/	
?>
 	<div class="rn_WDMR_rnHidden"><!--rn_hidden-->
 		<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
 		<rn:widget path="input/FormInput" name="Contact.CustomFields.c.consumer_id" default_value="#rn:php:$this->data['attrs']['consumerID']#"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TransactionType" default_value="#rn:php:$this->data['attrs']['issueType']#"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OriginalOrderNumber" default_value="#rn:php:$this->data['attrs']['orderNum']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductSKU" default_value="#rn:php:$this->data['attrs']['prodSku']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductPrice" default_value="#rn:php:$this->data['attrs']['productPriceCurrent']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReservableInventory" default_value="#rn:php:$this->data['attrs']['reservableInventory']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.HazardousItem" default_value="#rn:php:$this->data['attrs']['isHazardous']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumOrdersCurrent" default_value="#rn:php:$this->data['attrs']['NoChargeOrderCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumRefundsCurrent" default_value="#rn:php:$this->data['attrs']['RefundCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OutOfStock" default_value="0" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OrderLineID" default_value="#rn:php:$this->data['attrs']['OrderLineID']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_source" default_value="100" />
		<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" default_value="119"/>
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityOrdered" default_value="#rn:php:$this->data['getOrder']['Quantity']#" />
        
        ********************************************************************************
    </div>

<?  
	//echo("REFUND IT");
	//REFUND IT
	//if is isNarvarEligible then
	if($this->data['attrs']['isNarvarEligible'] == "Y") { 
		$debugging_output .= "isNarvarEligible<br>";
		
	?>
		<p>
			<h3>#rn:msg:CUSTOM_MSG_WDMR_FINALIZE_RETURN_NARVAR#</h3>
		</p>	
		<p>
			#rn:msg:CUSTOM_MSG_WDMR_CANCEL_STAY#
		</p>
		<div class="rn_WDMR_rnHidden">
			<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.IsNarvarEligible" default_value="1" />
		</div>
		<div id="rn_WDMR_BUTTONS" class="rn_Container">
            <? if($this->data['attrs']['countryID'] != 2) { ?>
				<!-- NOT CANADA -->
            	<rn:widget path="custom/navigation/CallToActionBox" class_name="rn_WDMRNarVarCallToActionBox rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="" subtext="" button_2_text="#rn:msg:CUSTOM_MSG_WDMR_NARVAR_OK_BTN#" button_2_url="#rn:config:CUSTOM_CFG_NARVAR_REDIRECT_URL#" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
            <? } else { ?>
            	<!-- CANADA -->
            	<rn:widget path="custom/navigation/CallToActionBox" class_name="rn_WDMRNarVarCallToActionBox rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="" subtext="" button_2_text="#rn:msg:CUSTOM_MSG_WDMR_NARVAR_OK_BTN#" button_2_url="#rn:config:CUSTOM_CFG_NARVAR_REDIRECT_CAN_URL#" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
            <? } ?>
        </div>
 <? } else { ?> 
	 	<!-- Must choose Refund or Reship -->
		<p>
    		<h3>#rn:msg:CUSTOM_MSG_WDMR_SORRY_WDI_HDR#</h3>
		</p>
		<p>
    		#rn:msg:CUSTOM_MSG_WDMR_WDI_HELP_TEXT#
		</p>
    	<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_REFUND" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_REFUND_BTN#" button_1_url="javascript:void(0)" button_1_onclick="RefundReshipSelection('0')" />
		<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_RESHIP" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_RESHIP_BTN#" button_1_url="javascript:void(0)" button_1_onclick="RefundReshipSelection('1')" />
 <? 
	}		//Close Narvar			 
 ?>
</div>
<div class="rn_WDMR_rnHidden">
<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
<? 
	if($debugIt == 1) {	
		echo $debugging_output; 
	}
	
?>
********************************************************************************
</div>
<script>

$( document ).ready(function() {
    console.log( "jquery ready!" );
    $( 'input[name="Incident.CustomFields.WDMR.NewShipPhone"]' ).change(function() {
	  //  /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/
	  //alert( "Handler for .change() called." );
	  var filter = /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/;
    if (filter.test(this.value)) {
        //alert("phone ok");
        return true;
    }
    else {
	    alert("#rn:msg:CUSTOM_MSG_WDMR_INVALID_PHONE_FORMAT#");
	    $( 'input[name="Incident.CustomFields.WDMR.NewShipPhone"]' ).val("");
        return false;
    }
	});
	
	
	
    $( 'input[name="Incident.CustomFields.WDMR.ShippingPhone"]' ).change(function() {
	  //  /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/
	  //alert( "Handler for .change() called." );
	  var filter = /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/;
    if (filter.test(this.value)) {
        //alert("phone ok");
        return true;
    }
    else {
	   alert("#rn:msg:CUSTOM_MSG_WDMR_INVALID_PHONE_FORMAT#");
	    $( 'input[name="Incident.CustomFields.WDMR.ShippingPhone"]' ).val("");
        return false;
    }
	});
});

function AddressSelection(type, msg) {
	//alert("in AddressSelection");
	//alert(type);
	
	if(type == "0") {
		$("#AddressQuestion").addClass("rn_Hidden");
		$("#rn_MissingItemReship1").removeClass("rn_Hidden");
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.createOrder"][value=1]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=1]').prop("checked",true);
		$('input[name="Incident.Subject"]').val(msg);
	} else if(type =="1") {
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=1]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=0]').prop("checked",true);
		$('input[name="Incident.Subject"]').val(msg);
		$("#AddressEntry").removeClass("rn_Hidden");
		$("#AddressQuestion").addClass("rn_Hidden");
	} else if(type=="2") {
		$("#AddressEntry").addClass("rn_Hidden");
		$("#rn_MissingItemReship1").removeClass("rn_Hidden");
		//$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=0]').prop("checked",true);
	}  
}
function MissingRefundReshipSelection(type) {
	//alert("in MissingRefundReshipSelection");
	//alert(type);
	//alert(window.location.href);
	newHref = window.location.href + "/rr/" + type;
	//alert(newHref);
	window.location.href = newHref;
}	
 function DI_RefundReshipSelection(type) {
	//alert("in RefundReshipSelection");
	//alert(type);
	//alert(window.location.href);
	newHref = window.location.href + "/rr/" + type;
	//alert(newHref);
	window.location.href = newHref;
}
</script>