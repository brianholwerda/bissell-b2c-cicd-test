<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<?  
	$referrer = $this->data['attrs']['referrer'];
	if($referrer == "CA") {
		$cancelBtnLink = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_WDMR_CA_CANCEL_BTN_LINK);
	} else {
		$cancelBtnLink = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_WDMR_CANCEL_BTN_LINK);
	}
	
	//print_r($cancelBtnLink);
	
	if($this->data['attrs']['IncidentExists'] == 1) {
	?>
		<div>
			#rn:msg:CUSTOM_MSG_WDMR_INCIDENT_EXISTS#
		</div>
	<?	
		return false;
	}
	
	
	if (is_null($this->data['attrs']['consumerID']) || is_null($this->data['attrs']['orderNum']) || is_null($this->data['attrs']['prodSku']) || $this->data['attrs']['itemFound'] == "0" ) {
	?>
		<div>
			#rn:msg:CUSTOM_MSG_WDMR_MISSING_PARAMETERS#
		</div>
	<?
		return false;	
	}
	
	$debugIt = getUrlParm("debug");
	$debugging_output = "<br>This is the Missing Item Flow Quantity Form<br>";
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
<?  } ?>
	
<? /*	
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
	print("This is product price current:");
	echo "<br>";		
	print_r($this->data['attrs']['productPriceCurrent']);
	echo "<br>";
*/	
?>
 	<div class="rn_WDMR_rnHidden"><!--rn_hidden-->
        <? /* We don't need this inputs for the Quantity form. 
	    <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TransactionType" default_value="#rn:php:$this->data['attrs']['issueType']#"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OriginalOrderNumber" default_value="#rn:php:$this->data['attrs']['orderNum']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductSKU" default_value="#rn:php:$this->data['attrs']['prodSku']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductPrice" default_value="#rn:php:$this->data['attrs']['productPriceCurrent']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReservableInventory" default_value="#rn:php:$this->data['attrs']['reservableInventory']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.HazardousItem" default_value="#rn:php:$this->data['attrs']['isHarzardous']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumOrdersCurrent" default_value="#rn:php:$this->data['attrs']['NoChargeOrderCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumRefundsCurrent" default_value="#rn:php:$this->data['attrs']['RefundCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OutOfStock" default_value="0" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OrderLineID" default_value="#rn:php:$this->data['attrs']['OrderLineID']#" /> 
        <rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_source" default_value="100" />
		<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" default_value="119"/>
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityOrdered" default_value="#rn:php:$this->data['getOrder']['Quantity']#" />. */ ?>
        <input type="number" id="QuantityOrderedOrig" name="QuantityOrderedOrig" class="rn_Number" max="2147483647" min="0" value=<?=$this->data['getOrder']['Quantity'];?>>
        
        
    </div>

<?  
	if($this->data['attrs']['issueType'] == "MI") {					//Missing Items or Too Many Items in Order
?>
		<div id="rn_MI_Page1">
			<p>
				<h3>#rn:msg:CUSTOM_MSG_WDMR_INCORRECT_QTY_HDR#</h3>
			</p>
			<p>
				#rn:msg:CUSTOM_MSG_WDMR_INCORRECT_QTY_DETAIL#
			</p>
			<table>
				<tr class="rn_WDMR_th">
					<th>Item</th>
					<th>Model No</th>
					<th>Cost</th>
					<th>Quantity Ordered</th>
					<th>Qty Received?</th>
				</tr>
				<tr class="rn_WDMR_tr">
					<td><?= $this->data['getOrder']['FriendlyProductName']; ?></td>
					<td><?= $this->data['getOrder']['Part #']; ?></td>
					<td><?= $this->data['getOrder']['Part Price']; ?></td>
					<td><?= $this->data['getOrder']['Quantity']; ?></td>
					<td><rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityReceived" default_value="" label_input="" /></td>
				</tr>
			</table>
			<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="Next" button_1_url="javascript:void(0)" button_1_onclick="GOTO_MI_PAGE2()" />
		</div>
		<div id="rn_MI_Page1_Mobile">
			<p>
				<h3>#rn:msg:CUSTOM_MSG_WDMR_INCORRECT_QTY_HDR#</h3>
			</p>
			<p>
				#rn:msg:CUSTOM_MSG_WDMR_INCORRECT_QTY_DETAIL#
			</p>
			<table>
				<tr class="rn_WDMR_tr">
					<th>Item:</th> <td><?= $this->data['getOrder']['FriendlyProductName']; ?></td>
				</tr>
				<tr class="rn_WDMR_tr">
					<th>Model No:</th> <td><?= $this->data['getOrder']['Part #']; ?></td>
				</tr>
				<tr class="rn_WDMR_tr">
					<th>Cost:</th> <td><?= $this->data['getOrder']['Part Price']; ?></td>
				</tr>
				<tr class="rn_WDMR_tr">
					<th>Quantity Ordered:</th> <td><?= $this->data['getOrder']['Quantity']; ?></td>
				</tr>
				<tr class="rn_WDMR_tr">
					<th>Qty Received?</th> <td><input type="number" id="WDMR.QuantityReceivedMOBILE" name="QuantityReceivedMOBILE" class="rn_Number" max="2147483647" min="0" onChange="setOSVCVal(this.value);"></td>
				</tr>
			</table>
			<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="Next" button_1_url="javascript:void(0)" button_1_onclick="GOTO_MI_PAGE2()" />
		</div>			
<?	} else { 														//UNKNOWN - ERROR
		echo("Error");
	}
?>
</div>
<div class="rn_WDMR_rnHidden">
************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
<? echo $debugging_output; ?>
********************************************************************************
</div>
<script>

$( document ).ready(function() {
    console.log( "jquery ready!" );
        
});

function setOSVCVal(myQty) {
	//alert("Value changed");
    //alert(myQty);
    $( 'input[name="Incident.CustomFields.WDMR.QuantityReceived"]' ).val(myQty);
    //alert($( 'input[name="Incident.CustomFields.WDMR.QuantityReceived"]' ).val());
    return true;
}

function GOTO_MI_PAGE2() {
	//alert("in GOTO_MI_PAGE2");
	var myQty = $('input[name="Incident.CustomFields.WDMR.QuantityReceived"]').val();
	var origQty = $('input[name="QuantityOrderedOrig"]').val();
	//alert(myQty);
	if(myQty==origQty) {
		alert("#rn:msg:CUSTOM_MSG_WDMR_QTY_ERROR#");
		return false;
	}
	let number = parseInt(myQty, 10);
	if(isNaN(number)) {
		alert("You must enter a Quantity Received to continue");
		return false;
	} else {
		//alert(window.location.href);
		//newHref = window.location.href + "/q/" + myQty;
		newHref = window.location.pathname + "/q/" + myQty;
		//alert(newHref);
		window.location.href = newHref;
	}
}	
	
</script>