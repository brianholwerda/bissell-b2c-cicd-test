<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<?  $todays_date = date('Y-m-d'); 
	//print("here");
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
	$productPriceFloatLimit = floatval($productPriceLimit);
	
	//Replace $IDI_type with $RR_type
	$RR_type = getUrlParm("rr");
	
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
	$debugging_output = "<br>This is the Missing Item Flow<br>";
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
	

/*	
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
	print("This is the Current product price:");
	echo "<br>";
	print_r($this->data['attrs']['productPriceCurrent']);
	echo "<br>";
*/	
?>
 	<div class="rn_WDMR_rnHidden"><!--rn_hidden-->
 		<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
 		<rn:widget path="input/FormInput" name="Contact.CustomFields.c.consumer_id" default_value="#rn:php:$this->data['attrs']['consumerID']#"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TransactionType" default_value="#rn:php:$this->data['attrs']['issueType']#"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OriginalOrderNumber" default_value="#rn:php:$this->data['attrs']['orderNum']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductSKU" default_value="#rn:php:$this->data['attrs']['prodSku']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ProductPrice" default_value="#rn:php:$this->data['attrs']['productPriceCurrent']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReservableInventory" default_value="#rn:php:$this->data['attrs']['reservableInventory']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.HazardousItem" default_value="#rn:php:$this->data['attrs']['isHazardous']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumOrdersCurrent" default_value="#rn:php:$this->data['attrs']['NoChargeOrderCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NumRefundsCurrent" default_value="#rn:php:$this->data['attrs']['RefundCount']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OutOfStock" default_value="#rn:php:$this->data['attrs']['OutOfStock']#" />
        <!-- Commented by Sajith since the Line ID format changed and it was not getting saved in integer field due to size limitation -->
		<!-- <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OrderLineID" default_value="#rn:php:$this->data['attrs']['OrderLineID']#" /> -->
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityReceived" default_value="#rn:php:$this->data['attrs']['QuantityReceived']#" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ShipToNewAddress" default_value="0" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_source" default_value="100" />
		<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" default_value="119"/>
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityOrdered" default_value="#rn:php:$this->data['getOrder']['Quantity']#" />
        
        ********************************************************************************
    </div>

<?  
	if ($this->data['attrs']['issueType'] == "MI") {	 	//MISSING ITEM
		//echo("Missing Item");
		if ($this->data['attrs']['QuantityReceived'] > $this->data['getOrder']['Quantity']) {
			//echo("Too many received screen, create incident"); 
			$debugging_output .= "Too many received screen, create incident<br>" ;
		?>
			<p>
				<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
			</p>
			<p>
				#rn:msg:CUSTOM_MSG_WDMR_INCORRECT_QTY_HDR#
			</p>
			<p>
				#rn:msg:CUSTOM_MSG_WDMR_CLICK_ACCEPT# #rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW_LC#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
			</p>
			<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
			<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
			
			<div class="rn_WDMR_rnHidden">
				<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
				<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
					default_value="Missing Item - Received too many - Agent Review"/>
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
					default_value="Received too many - Agent Review"/>
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
				<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
				********************************************************************************
			</div>
			<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
		<?	
		} else if($this->data['attrs']['QuantityReceived'] == $this->data['getOrder']['Quantity']) {
			//echo("Same amount entered, error... Shouldn't get here, but just in case."); 
			$debugging_output .= "Same amount entered, error... Shouldn't get here, but just in case.<br>" ;
		?>  
			<p>
			#rn:msg:CUSTOM_MSG_WDMR_QTY_ERROR#
			</p>
			
			<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_QTYBACK" button_1_text="ENTER QUANTITY" button_1_url="javascript:void(0)" button_1_onclick="EnterQuantity(1)" />
	<?	} else {
		$debugging_output .= "Not enough recieved <br>" ;
			if($this->data['attrs']['NoChargeOrderCount'] < $OrderNumberLimit && $this->data['attrs']['RefundCount'] < $RefundNumberLimit ) {
				//Less than 2 $0 orders
				$debugging_output .= "Less than 2 $0 orders && Less than 2 Refunds<br>" ;
				if($this->data['attrs']['ActiveSKU'] == '1') {
					//SKU is active	
					$debugging_output .= "SKU is active<br>" ;
					if($this->data['attrs']['reservableInventory'] > $reservableInventoryLimit) {
						//Reservable inventory > Reservable Inventory Limit (15)
						$debugging_output .= "Reservable inventory > Reservable Inventory Limit (15)<br>" ;
						if($this->data['attrs']['productPriceCurrent'] <= $productPriceFloatLimit) {
							//Product Price <= 50
							$debugging_output .= "Product Price <= 50<br>" ;
							//Ask address change question... 
							//echo("Enough inventory, Product Price < 50, ask address question");
							$debugging_output .= "Enough inventory, Product Price < 50, ask address question<br>" ;
						?>
							<div id="AddressQuestion">
								<p>
					        		<h3>#rn:msg:CUSTOM_MSG_WDMR_CHOOSE_ADDR_HDR#</h3>
				        		</p>
				        		<p>
					        		#rn:msg:CUSTOM_MSG_WDMR_HAPPY_REPLACE#
				        		</p>
				        		<p>
					        		#rn:msg:CUSTOM_MSG_WDMR_CHOOSE_ADDR_TEXT#
				        		</p>
				        		<p>
					        		<rn:widget path="custom/display/ObscureShipping" shipping_addr="#rn:php:$this->data['attrs']['shippingAddr']#" />
				        		</p>
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0')" />
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1')" />
							</div>
							<div id="AddressEntry" class="rn_Hidden">
								<div id="rn_NewShipAddress">
									#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipFirstName" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipLastName" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr2" />
									<span id="CityStateZip">
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipCity" />
									<div class="rn_Hidden">
										<rn:widget path="input/FormInput" name="Contact.Address.Country" label_input="#rn:msg:COUNTRY_LBL#" default_value="#rn:php:$this->data['attrs']['countryID']#" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipState" label_input="#rn:msg:STATE_PROV_LBL#" />
									</div>
									<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" label_input="#rn:msg:STATE_PROV_LBL#"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipZip" />
									</span>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipPhone" always_show_mask="true" />
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" tab_index="tabindex='0'" div_id="address_next" />
								</div>
							</div> 
							<div id="rn_MissingItemReship1" class="rn_Hidden">
								<div id="rn_MissingItemReshipSameAddress" class="">
									<p>
										<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
									</p>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_IMPORTANT_CLEANING#
									</p>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_CLICK_ACCEPT# #rn:msg:CUSTOM_MSG_WDMR_SHIP_TO_SPECIFIED_ADDR# #rn:msg:CUSTOM_MSG_WDMR_NO_COST#
									</p>
								</div>
								<div id="rn_MissingItemReshipDiffAddress" class="rn_Hidden">
									<p>
										<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
									</p>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_IMPORTANT_CLEANING#
									</p>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#
									</p>
								</div>
								
								<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
								<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />		
								<div class="rn_WDMR_rnHidden">
									<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
									<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
										default_value="Missing Item - Reship - New address - Agent Review"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Missing Item - Reship - New address - Agent Review"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="1"  />
									********************************************************************************
								</div>
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
					  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
							</div>
					<?	} else {
							//Create incident for manual agent review
							//echo("enough inventory, Product Price > 50, create incident"); 
							$debugging_output .= "enough inventory, Product Price > 50, create incident<br>" ;
					?>
							<p>
								<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
							</p>
							<p>
								#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
							</p>
							<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
							<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
							<div class="rn_WDMR_rnHidden">
								<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
								<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
									default_value="Missing Item - Reship - Price Over Limit(#rn:php:$productPriceLimit#) - Agent Review"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Missing Item - Reship - Price Over Limit(#rn:php:$productPriceLimit#) - Agent Review"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="1"  />
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
								********************************************************************************
							</div>
							<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
				  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
							
					<?	}		//Close Product Price Check		
					} else {
						//REFUND OR RESHIP FLOW	
						//echo("Not Enough Inventory, Refund or Reship flow");
						$debugging_output .= "Not Enough Inventory, Ask Refund or Reship question again<br>" ;
						//echo($RR_type);
						if($RR_type == "" ) { 
						
						?>
							<p>
								<h3>#rn:msg:CUSTOM_MSG_WDMR_OUT_OF_STOCK_HDR#</h3>
							</p>
							<p>
								#rn:msg:CUSTOM_MSG_WDMR_OUT_OF_STOCK_TEXT#
							</p>
				        	<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_REFUND" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_REFUND_BTN#" button_1_url="javascript:void(0)" button_1_onclick="MissingRefundReshipSelection('0')" />
							<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_RESHIP" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_RESHIP_BTN#" button_1_url="javascript:void(0)" button_1_onclick="MissingRefundReshipSelection('1')" />
					<?  } else {
							if($RR_type == 0) { 					
								//echo("refund");
								$debugging_output .= "Not Enough Inventory, Refund Selected<br>" ;
					        	//if is isNarvarEligible then
								/* if($this->data['attrs']['isNarvarEligible'] == "Y") { 
									$debugging_output .= "isNarvarEligible<br>" ;
									
								?>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_FINALIZE_RETURN_NARVAR#
									</p>	
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_CANCEL_STAY#
									</p>
									<div class="rn_WDMR_rnHidden">
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.IsNarvarEligible" default_value="1" />
									</div>
									<div id="rn_WDMR_BUTTONS" class="rn_Container">
							            <rn:widget path="custom/navigation/CallToActionBox" class_name="rn_WDMRNarVarCallToActionBox rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="" subtext="" button_2_text="Accept"
							            button_2_url="#rn:config:CUSTOM_CFG_NARVAR_REDIRECT_URL#" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
							        </div>
							 <? } else { */ ?>
								 	<div class="rn_WDMR_rnHidden">
								 		<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.IsNarvarEligible" default_value="0" />
								 	</div> 
									
								<?	
									//$debugging_output .= "NOT isNarvarEligible<br>" ;
									if($this->data['attrs']['isHazardous'] == '1') {
										//Formula or Aerosol
										//****Display message notifying consumer that the refund confirmation will be sent to the partially displayed email address
										//echo("is formula, refund");
										$debugging_output .= "is formula, refund<br>" ;
								?>
										<p>
											<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
										</p>
										<p>
											#rn:msg:CUSTOM_MSG_WDMR_PROCESS_REFUND_EMAIL_TXT#
										</p>
										<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
										<p>
											#rn:msg:CUSTOM_MSG_WDMR_CANCEL_STAY#
										</p>
										<div class="rn_WDMR_rnHidden">
											<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
											<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
												default_value="Missing Item - Refund - Product is Formula or Hazardous - Auto Refund"/>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
												default_value="Missing Item - Refund - Product is Formula or Hazardous - Auto Refund"/>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="1" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
											********************************************************************************
										</div>
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
							  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>	
								 <? } else {
										//Not formula or Aerosol
										$debugging_output .= "Not formula or Aerosol<br>" ;
										if($this->data['attrs']['ActiveSKU'] == '1') {
											//SKU is active
											$debugging_output .= "SKU is active<br>" ;	
											if($this->data['attrs']['productPriceCurrent'] <= $productPriceFloatLimit) {
												//Product Price <= 50
												//**** Display message notifying consumer that the refund confirmation will be sent to the partially displayed email address
												$debugging_output .= "Product Price <= 50<br>" ;	
												//echo("Product Price <= 50, process refund");
											?>	
												<p>
													<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
												</p>
												<p>
													#rn:msg:CUSTOM_MSG_WDMR_PROCESS_REFUND_EMAIL_TXT#
												</p>
												<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
												<p>
													#rn:msg:CUSTOM_MSG_WDMR_CANCEL_STAY#
												</p>
												<div class="rn_WDMR_rnHidden">
													<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
													<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
														default_value="Missing Item - Refund - Product price less than limit(#rn:php:$productPriceLimit#) - Auto Refund"/>
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
														default_value="Missing Item - Refund - Product price less than limit(#rn:php:$productPriceLimit#) - Auto Refund"/>
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="1" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
													********************************************************************************
												</div>
												<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
									  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>	
									  	 <? } else {
												//Product price > 50
												//**** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
												$debugging_output .= "Product Price > 50, create incident<br>" ;
												//echo("Product Price > 50, create incident");
											?>	
												<p>
													<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
												</p>
												<p>
													#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
												</p>
												<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
												<? /* <rn:widget path="input/FormInput" name="Incident.Thread" required="true" label_input="put something" /> */ ?>
												<div class="rn_WDMR_rnHidden">
													<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
													<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
														default_value="Missing Item - Refund - Product price greater than limit(#rn:php:$productPriceLimit#) - Agent Review"/>
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
														default_value="Missing Item - Refund - Product price greater than limit(#rn:php:$productPriceLimit#) - Agent Review"/>
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
													<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
													********************************************************************************
												</div>
												
												<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
									  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
														
									  	<?	}		//Close price check
										} else {
											//SKU NOT active
											// **** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
											$debugging_output .= "SKU NOT ACTIVE, create incident<br>" ;
											//echo("SKU NOT ACTIVE, create incident");
										?>	
											<p>
												<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
											</p>
											<p>
												#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
											</p>
											<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
											<div class="rn_WDMR_rnHidden">
												<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
												<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
														default_value="Missing Item - Refund - SKU not active in price list - Agent Review"/>
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
														default_value="Missing Item - Refund - SKU not active in price list - Agent Review"/>
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
												********************************************************************************
											</div>
											<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
								  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
															
									 <? }		//Close SKU Active check
									}			//Close hazardous/Formula/Aerosol cehck
									
								//}		//Close Narvar
							} else { 
								//echo("reship");
								$debugging_output .= "Not Enough Inventory, Reship Selected<br>" ;
				        		//Reservable inventory > Reservable Inventory Limit (15)
								if($this->data['attrs']['productPriceCurrent'] <= $productPriceFloatLimit) {
									//Product Price <= 50
									//Ask address change question... 
									$debugging_output .= "Product Price <= 50, Ask address change question <br>" ;
								?>
									<div id="AddressQuestion">
										<p>
							        		<h3>#rn:msg:CUSTOM_MSG_WDMR_CHOOSE_ADDR_HDR#</h3>
						        		</p>
						        		<p>
							        		#rn:msg:CUSTOM_MSG_WDMR_HAPPY_REPLACE#
						        		</p>
						        		<p>
							        		#rn:msg:CUSTOM_MSG_WDMR_CHOOSE_ADDR_TEXT#
						        		</p>
						        		<p>
							        		<rn:widget path="custom/display/ObscureShipping" shipping_addr="#rn:php:$this->data['attrs']['shippingAddr']#" />
						        		</p>
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0')" />
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1')" />
									</div>
									<div id="AddressEntry" class="rn_Hidden">
										<div id="rn_NewShipAddress">
											#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipFirstName" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipLastName" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr1" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr2" />
											<span id="CityStateZip">
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipCity" />
											<div class="rn_Hidden">
												<rn:widget path="input/FormInput" name="Contact.Address.Country" label_input="#rn:msg:COUNTRY_LBL#" default_value="#rn:php:$this->data['attrs']['countryID']#" />
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipState" label_input="#rn:msg:STATE_PROV_LBL#" />
											</div>
											<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" label_input="#rn:msg:STATE_PROV_LBL#"/>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipZip" />
											</span>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipPhone" always_show_mask="true" />
											<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" tab_index="tabindex='0'" div_id="address_next" />
										</div>
									</div> 
									<div id="rn_MissingItemReship1" class="rn_Hidden">
										<div id="rn_MissingItemReshipSameAddress" class="">
											<p>
												<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
											</p>
											<p>
												#rn:msg:CUSTOM_MSG_WDMR_IMPORTANT_CLEANING#
											</p>
											<p>
												#rn:msg:CUSTOM_MSG_WDMR_BACK_IN_STOCK# #rn:msg:CUSTOM_MSG_WDMR_SHIP_TO_SPECIFIED_ADDR# #rn:msg:CUSTOM_MSG_WDMR_NO_COST#
											</p>
										</div>
										<div id="rn_MissingItemReshipDiffAddress" class="rn_Hidden">
											<p>
												<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
											</p>
											<p>
												#rn:msg:CUSTOM_MSG_WDMR_IMPORTANT_CLEANING#
											</p>
											<p>
												#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#
											</p>
										</div>
										
										<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
										<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
										
										<div class="rn_WDMR_rnHidden">
											
											<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
											<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
												default_value="Missing Item - Reship - New Address - Agent Review"/>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
														default_value="Missing Item - Reship - New Address - Agent Review"/>
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
											<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
											********************************************************************************
										</div>
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
							  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
									</div>
							</div>
							<?	} else {
									//Create incident for manual agent review
									//echo("Product Price > 50, create incident"); 
									$debugging_output .= "Product Price > 50, create incident <br>" ;
							?>
									<p>
										<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
									</p>
									<p>
										#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
									</p>
									<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
									<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
									
									<div class="rn_WDMR_rnHidden">
										<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
										<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
											default_value="Missing Item - Reship - Product price greater than limit(#rn:php:$productPriceLimit#) - Agent Review"/>
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
											default_value="Missing Item - Reship - Product price greater than limit(#rn:php:$productPriceLimit#) - Agent Review"/>
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
										<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
										********************************************************************************
									</div>
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
						  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>		
									
							<?	}		//Close Product Price Check
						   }	// Close RR 0 or 1 check 
						}		// Close RR not defined Check 
					}			// Close Reservable Inventory Check
				} else {
					//SKU is NOT active
					//Create Incident
					//echo("SKU NOT ACTIVE, create incident"); 
					$debugging_output .= "SKU NOT ACTIVE, create incident <br>" ;
				?>
					<p>
						<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
					</p>
					<p>
						#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
					</p>
					<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
					<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
					
					<div class="rn_WDMR_rnHidden">
						<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
						<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
							default_value="Missing Item - Reship - SKU not active in price list - Agent Review"/>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
							default_value="Missing Item - Reship - SKU not active in price list - Agent Review"/>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
						********************************************************************************
					</div>
					<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
		  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
					
			<?	}		//Close SKU Check
			} else {
			//More than 2 $0 orders
				//Create incident	
				//echo("Too many orders, create incident"); 
				$debugging_output .= "Too many no charge orders or returns, create incident <br>" ;
			?>
				<p>
					<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
				</p>
				<p>
					#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
				</p>
				<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
				<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
				
				<div class="rn_WDMR_rnHidden">
					<br />************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
					<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
						default_value="Missing Item - Too many no charge orders or returns - Agent Review"/>
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
						default_value="Missing Item - Too many no charge orders or returns - Agent Review"/>
					<? if($this->data['attrs']['NoChargeOrderCount'] >= $OrderNumberLimit && $this->data['attrs']['RefundCount'] >= $RefundNumberLimit) { ?>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="1" />
					<? } else if($this->data['attrs']['NoChargeOrderCount'] >= $OrderNumberLimit) { ?>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
					<? } else { ?>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="1" />
					<? } ?>
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="1"  />
					<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
					********************************************************************************
				</div>
				<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
	  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
		<?	}		//Close $0 order check
			
		}
	
		
	} else { 														//UNKNOWN - ERROR
		echo("Error");
		$debugging_output .= "ERROR <br>" ;
	}
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
    $('select[name="Contact.Address.StateOrProvince"]').on('change', function() {
		//alert("inside onchange");
		//alert( this.value );
		$('select[name="Incident.CustomFields.WDMR.NewShipState"]').val(this.value).prop('selected', true);
	});
	
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
	
	$( 'input[name="Incident.CustomFields.WDMR.NewShipZip"]' ).change(function() {
		//alert( "Handler for .change() called." );
		zip = this.value;
		
		if (zip.match(/^([0-9]{5})(?:[-\s]*([0-9]{4}))?$/)) {
			return true;
		}
		
		zip=zip.toUpperCase();
		if (zip.match(/^([A-Z][0-9][A-Z])\s*([0-9][A-Z][0-9])$/)) {
			return true;
		}

		alert("#rn:msg:CUSTOM_MSG_WDMR_INVALID_ZIP_FORMAT#");
	    $( 'input[name="Incident.CustomFields.WDMR.NewShipZip"]' ).val("");
		return false;
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

function AddressSelection(type) {
	//alert("in AddressSelection");
	//alert(type);
	
	if(type == "0") { //Same Address
		$("#AddressQuestion").addClass("rn_Hidden");
		$("#rn_MissingItemReshipSameAddress").removeClass("rn_Hidden");
		$("#rn_MissingItemReshipDiffAddress").addClass("rn_Hidden");
		$("#rn_MissingItemReship1").removeClass("rn_Hidden");
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.createOrder"][value=1]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=1]').prop("checked",true);
		$('input[name="Incident.Subject"]').val("Missing Item - Reship - Same address - Order Created - No Agent Review");
		$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Missing Item - Reship - Same address - Order Created - No Agent Review");
		$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=0]').prop("checked",true);
		$("button[type=submit]").focus();
		//$('input[name="Incident.CustomFields.c.ReshipOrRefund"][value=1]').prop("checked",true);
	} else if(type =="1") { //Different Address
		$("#rn_MissingItemReshipDiffAddress").removeClass("rn_Hidden");
		$("#rn_MissingItemReshipSameAddress").addClass("rn_Hidden");
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=1]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=1]').prop("checked",true);
		$("#AddressEntry").removeClass("rn_Hidden");
		$("#AddressQuestion").addClass("rn_Hidden");
		
		$('input').on('keypress', function(e) {
		  if (e.keyCode == 13) {
		    //alert("inside keydown type1");
		    $('input[name="Contact.Emails.PRIMARY.Address"]').focus();
		    e.preventDefault();
			AddressSelection('2');
			//return false;
		  }
		});
		
	} else if(type=="2") { //Next Address
		$("#AddressEntry").addClass("rn_Hidden");
		$("#rn_MissingItemReship1").removeClass("rn_Hidden");
		//$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=0]').prop("checked",true);
		$("button[type=submit]").focus();
	} 
}
function MissingRefundReshipSelection(type) {
	//alert("in MissingRefundReshipSelection");
	//alert(type);
	//alert(window.location.href);
	//newHref = window.location.href + "/rr/" + type;
	newHref = window.location.pathname + "/rr/" + type;
	//alert(newHref);
	window.location.href = newHref;
}	
function EnterQuantity(type) {
	//alert("in EnterQuantity");
	//alert(window.location.href);
	//newHref = window.location.href;
	newHref = window.location.pathname;
	i = newHref.indexOf('/q/');
	//alert(i);
	newHref = newHref.substr(0, newHref.indexOf('/q/'));
	//alert(newHref);
	
	window.location.href = newHref;
}	 
</script>