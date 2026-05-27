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
	
	//Replace $WDI_type with $RR_type
	$RR_type = getUrlParm("rr");
	if($RR_type == ""){
		//$RR_type = 1;
	}
	$WDI_type = getUrlParm("wd");
	
	$OrderNumberLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_OrderNumberLimit);
	$RefundNumberLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_RefundNumberLimit);
	$reservableInventoryLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_INVENTORY_LIMIT);
	$productPriceLimit = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_PRODUCT_DOLLAR_VALUE);
	$productPriceFloatLimit = floatval($productPriceLimit);
	
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
	$debugging_output = "<br>This is the Damaged Wrong Reship Item Flow<br>";
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
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OutOfStock" default_value="#rn:php:$this->data['attrs']['OutOfStock']#" />
		<!-- Commented by Sajith since the Line ID format changed and it was not getting saved in integer field due to size limitation -->
        <!-- <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.OrderLineID" default_value="#rn:php:$this->data['attrs']['OrderLineID']#" /> -->
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="1"  />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ShipToNewAddress" default_value="0" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_source" default_value="100" />
		<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" default_value="119"/>
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityOrdered" default_value="#rn:php:$this->data['getOrder']['Quantity']#" />

        ********************************************************************************
    </div>

<?  

			//echo("XXX RESHIP IT");
			if($this->data['attrs']['NoChargeOrderCount'] < $OrderNumberLimit && $this->data['attrs']['RefundCount'] < $RefundNumberLimit ) {
				//Less than 2 $0 orders
				$debugging_output .= "Less than 2 $0 orders && Less than 2 Refunds<br>" ;
				if($this->data['attrs']['isHazardous'] == '1') {
					//Formula or Aerosol
					//****Ask Address Question, get details of damaged/incorrect.
					//if address is the same, create order to reship, if different, just create incident
					$debugging_output .= "Formula or Aerosol, Ask Address Question <br>";
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
						<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0', 'Damaged/Wrong Item Reship - Formula - No Agent Review')" />
						<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1', 'Damaged/Wrong Item Reship - Formula New Address - Agent Review')" />
					</div>
					<div id="AddressEntry" class="rn_Hidden">
						<div id="rn_NewShipAddress">
							<h3>#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#</h3>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipFirstName" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipLastName" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipAddr2" />
							<span id="CityStateZip">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipCity" />
							<div class="rn_Hidden">
								<rn:widget path="input/FormInput" name="Contact.Address.Country" label_input="#rn:msg:COUNTRY_LBL#" default_value="#rn:php:$this->data['attrs']['countryID']#"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipState" label_input="#rn:msg:STATE_PROV_LBL#" />
							</div>
							<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" label_input="#rn:msg:STATE_PROV_LBL#"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipZip" />
							</span>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.NewShipPhone" always_show_mask="true" />
							<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" />
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
								#rn:msg:CUSTOM_MSG_WDMR_MORE_DETAILS# #rn:msg:CUSTOM_MSG_WDMR_SHIP_TO_SPECIFIED_ADDR# #rn:msg:CUSTOM_MSG_WDMR_NO_COST#
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
								#rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO#
							</p>
						</div>
						<? if(isset($this->data['attrs']['emailAddr'])) { ?>
							<p>
								#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
							</p>
						<? } ?>
						<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
						<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
						
						<div class="rn_WDMR_rnHidden">
							<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
							<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
								default_value="Damaged/Wrong Item - Reship - Product is Formula or Hazardous - No Agent Review"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
								default_value="Damaged/Wrong Item - Reship - Product is Formula or Hazardous - No Agent Review"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
							********************************************************************************
						</div>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#" required="true" hideEmptyOption="true" />
						<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
						<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
						
						<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
			  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
					</div>
			<?	} else {
					//Not formula or Aerosol
					$debugging_output .= "Not formula or Aerosol <br>";
					if($this->data['attrs']['ActiveSKU'] == '1') {
						//SKU is active	
						$debugging_output .= "SKU is active <br>";
						//echo("SKU ACTIVE, check inventory");
						if($this->data['attrs']['reservableInventory'] > $reservableInventoryLimit) {	
							//echo(" " . $this->data['attrs']['reservableInventory']);
							//echo("there is enough inventory");
							$debugging_output .= "there is enough inventory<br>";
							//Reservable inventory is sufficient to do a reship
							if($this->data['attrs']['productPriceCurrent'] <= $productPriceFloatLimit) {
								//Product Price <= 50
								//****Ask Address Question, get details of damaged/incorrect.
								//if address is the same, create order to reship, if different, just create incident
								$debugging_output .= "Product Price <= 50, Ask Address Question, if address is the same, create order to reship, if different, just create incident<br>";
								//echo("price is under limit");
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
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0', 'Damaged/Wrong Item - Reship - No Agent Review')" />
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1', 'Damaged/Wrong Item - Reship - New address - Agent Review')" />
								</div>
								<div id="AddressEntry" class="rn_Hidden">
									<div id="rn_NewShipAddress">
										<h3>#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#</h3>
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
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" />
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
											#rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO#
										</p>
									</div>
									<? if(isset($this->data['attrs']['emailAddr'])) { ?>
										<p>
											#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
										</p>
									<? } ?>
									<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
									<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
									
									<div class="rn_WDMR_rnHidden">
										<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
										<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
											default_value="Damaged/Wrong Item Reship - New address - Agent Review"/>
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
											default_value="Damaged/Wrong Item Reship - New address - Agent Review"/>
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
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#" required="true" hideEmptyOption="true" />
									<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
									<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
						  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
								</div>
						
						<?	} else {
								//echo("price > 50");
								//Product price > 50
								//**** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
								//Show the address update question, show form if they select yes.
								$debugging_output .= "Product price > 50, Ask Address Question, create incident<br>";
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
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0', 'Damaged/Wrong Item Reship - Over Price Limit - Agent Review')" />
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1', 'Damaged/Wrong Item Reship - Over Price Limit, Different Address - Agent Review')" />
								</div>
								<div id="AddressEntry" class="rn_Hidden">
									<div id="rn_NewShipAddress">
										<h3>#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#</h3>
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
										<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" />
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
											#rn:msg:CUSTOM_MSG_WDMR_FEDEX_LBL_EMAIL_TXT#
										</p>
										<p>
											#rn:msg:CUSTOM_MSG_WDMR_AFTER_RETURN_SHIIP# 
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
											<!-- #rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO# -->
										</p>
									</div>
									<? if(isset($this->data['attrs']['emailAddr'])) { ?>
										<p>
											#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
										</p>
									<? } ?>
									<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
									<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />

									<div class="rn_WDMR_rnHidden">
										<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
										<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
											default_value="Damaged/Wrong Item Reship - Price Over Limit(#rn:php:$productPriceLimit#) - Agent Review"/>
										<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
											default_value="Damaged/Wrong Item Reship - Price Over Limit(#rn:php:$productPriceLimit#) - Agent Review"/>
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
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#" required="true" hideEmptyOption="true" />
									<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
									<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
									<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
						  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
								</div>
						
						
						<?	}	//end ProductPriceCheck
						} else {
							//echo("not enough inventory");
							$debugging_output .= "not enough inventory, Ask the reship/refund question again<br>";
							//Reservable inventory is NOT sufficient to do an immediate reship
							//Ask the reship/refund question...  Copy the flow for each answer...
							if($RR_type == "") { ?>
					        	<p>
						        	<h3>#rn:msg:CUSTOM_MSG_WDMR_OUT_OF_STOCK_HDR#</h3>
					        	</p>
					        	<p>
						        	#rn:msg:CUSTOM_MSG_WDMR_OUT_OF_STOCK_TEXT#
					        	</p>
					        	<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_REFUND" button_1_text="REFUND" button_1_url="javascript:void(0)" button_1_onclick="DI_RefundReshipSelection('0')" />
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_RESHIP" button_1_text="RESHIP" button_1_url="javascript:void(0)" button_1_onclick="DI_RefundReshipSelection('1')" />
							<? } else {
									if($RR_type == 0) { ?>					
										<div class="rn_RR_REFUND">
											<? $debugging_output .= "Refund, I should not get here<br>"; ?>
										</div>
									<? } else { ?>
										<div class="rn_RR_RESHIP">
											
											<? $debugging_output .= "Reship when inventory is in stock, ask address question<br>"; ?>
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
												<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_SAMEADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_SAME_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('0', 'Damaged/Wrong Item Reship - Not Enough Inventory - Agent Review')" />
												<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_DIFFADDRESS" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_DIFF_ADDR_BTN#" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('1', 'Damaged/Wrong Item Reship - Not Enough Inventory, Different Address - Agent Review')" />
											</div>
											<div id="AddressEntry" class="rn_Hidden">
												<div id="rn_NewShipAddress">
													<h3>#rn:msg:CUSTOM_MSG_WDMR_NEW_ADDRESS_HDR#</h3>
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
													<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_NEXT" button_1_text="NEXT" button_1_url="javascript:void(0)" button_1_onclick="AddressSelection('2')" />
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
														#rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO#
													</p>
												</div>
												
												<? if(isset($this->data['attrs']['emailAddr'])) { ?>
													<p>
														#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
													</p>
												<? } ?>
												<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
												<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
												
												<div class="rn_WDMR_rnHidden">
													<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
													<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
														default_value="Damaged/Wrong Item - Reship - Reship when inventory available - Agent Review"/>
													<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
														default_value="Damaged/Wrong Item - Reship - Reship when inventory available - Agent Review"/>
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
												<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#" required="true" hideEmptyOption="true" />
												<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
												<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
												<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
									  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
											</div>
										</div>
									<? } ?>
							<? } ?>
					<?	}		//end reservableInventory
					} else {
						// SKU NOT active
						// **** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
						$debugging_output .= "SKU NOT active<br>";
						?>	
						<p>
							#rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO#
						</p>
						<? if(isset($this->data['attrs']['emailAddr'])) { ?>
							<p>
								#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
							</p>
						<? } ?>
						<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
						<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
						
						<div class="rn_WDMR_rnHidden">
							<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
							<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
								default_value="Damaged/Wrong Item - Reship - SKU is not active in price list - Agent Review"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
								default_value="Damaged/Wrong Item - Reship - SKU is not active in price list - Agent Review"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
							********************************************************************************
						</div>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#" required="true" hideEmptyOption="true" />
						<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
						<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
						
						<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
			  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>

				<?	}	// end ActiveSKU
				}		// end isHazardous
			} else {	//  >=2 $0 orders
			//More than 2 $0 orders
				//****  Automated confirmation email sends to consumer
				//****  Create incident for Manual agent review. Also displays forward- facing (Collect Email if not on file) 3rd level category open, Disposition blank, status open,
				$debugging_output .= "Too many no charge orders or returns, create incident <br>";
				?>	
					<p>
						#rn:msg:CUSTOM_MSG_WDMR_AGENT_MORE_INFO#
					</p>
					<? if(isset($this->data['attrs']['emailAddr'])) { ?>
						<p>
							#rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
						</p>
					<? } ?>
					<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
					<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
					
					<div class="rn_WDMR_rnHidden">
						<br>************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
						<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
							default_value="Damaged/Wrong Item - Reship - Too many no charge orders or returns - Agent Review"/>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
								default_value="Damaged/Wrong Item - Reship - Too many no charge orders or returns - Agent Review"/>
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
						<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
						********************************************************************************
					</div>
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.DamagedOrWrong" label_input="#rn:msg:CUSTOM_MSG_WDMR_WDI_REASON_LBL#"  required="true" hideEmptyOption="true" />
					<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
					<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_REASON_LBL#"/>
					
					<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
		  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
											
				
		<?	}			// end >=2 $0 orders

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
function AddressSelection(type, msg) {
	//alert("in AddressSelection");
	//alert(type);
	var prodPrice = parseFloat("<?= $this->data['attrs']['productPriceCurrent']; ?>");
	var priceLimit = parseFloat("<?= $productPriceFloatLimit; ?>");
	var orderedQty = parseInt("<?= $this->data['getOrder']['Quantity']; ?>");
	//alert(priceLimit);
	//alert(prodPrice);
	//alert(orderedQty);
	
	if(type == "0") { //Ship to same address
		$("#rn_MissingItemReshipSameAddress").removeClass("rn_Hidden");
		$("#rn_MissingItemReshipDiffAddress").addClass("rn_Hidden");
		$("#AddressQuestion").addClass("rn_Hidden");
		$("#rn_MissingItemReship1").removeClass("rn_Hidden");
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=1]').prop("checked",true);
		$('input[name="Incident.Subject"]').val(msg);
		$('input[name="Incident.CustomFields.WDMR.Reason"]').val(msg);

		if(prodPrice < priceLimit) {
			$('input[name="Incident.CustomFields.WDMR.createOrder"][value=1]').prop("checked",true);
			$('input[name="Incident.CustomFields.WDMR.createLabel"][value=0]').prop("checked",true);
			$('input[name="Incident.CustomFields.WDMR.createRA"][value=0]').prop("checked",true);
			$('input[name="Incident.Subject"]').val("Damaged/Wrong Item - Reship - Price Under Limit - No Agent Review,Create Order");
			$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - Price Under Limit - No Agent Review,Create Order");
			$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=0]').prop("checked",true);
			$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=1]').prop("checked",false);
		} else {
			if(orderedQty > 1) {
				$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createLabel"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createRA"][value=0]').prop("checked",true);
				$('input[name="Incident.Subject"]').val("Damaged/Wrong Item - Reship - Price Over Limit, Ordered multiple - Agent Review");
				$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - Price Over Limit, Ordered multiple - Agent Review");
				$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=1]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=0]').prop("checked",false);
			} else {
				$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createLabel"][value=1]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createRA"][value=1]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=1]').prop("checked",false);
				$('input[name="Incident.Subject"]').val("Damaged/Wrong Item Reship - Price Over Limit, Ordered 1 - No Agent Review, Create RA, Create Label");
				$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - Price Over Limit, Ordered 1 - No Agent Review");
			}
		}
	} else if(type =="1") { //Ship to differnent address
		$("#rn_MissingItemReshipDiffAddress").removeClass("rn_Hidden");
		$("#rn_MissingItemReshipSameAddress").addClass("rn_Hidden");
		$('input[name="Incident.CustomFields.WDMR.ShipToNewAddress"][value=1]').prop("checked",true);
		//$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.c.wdmr_process_flag"][value=0]').prop("checked",true);
		$('input[name="Incident.CustomFields.WDMR.AgentReviewRequired"][value=1]').prop("checked",true);
		$('input[name="Incident.Subject"]').val(msg);
		if(prodPrice < priceLimit) {
			$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
			$('input[name="Incident.CustomFields.WDMR.createLabel"][value=0]').prop("checked",true);
			$('input[name="Incident.CustomFields.WDMR.createRA"][value=0]').prop("checked",true);
			$('input[name="Incident.Subject"]').val("Damaged/Wrong Item - Reship - New address - Agent Review");
			$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - New address - Agent Review");
		} else {
			if(orderedQty > 1) {
				$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createLabel"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createRA"][value=0]').prop("checked",true);
				$('input[name="Incident.Subject"]').val("Damaged/Wrong Item - Reship - New address, Price Over Limit, Ordered more than 1 - Agent Review");
				$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - New address, Price Over Limit, Ordered more than 1 - Agent Review");
			} else {
				$('input[name="Incident.CustomFields.WDMR.createOrder"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createLabel"][value=0]').prop("checked",true);
				$('input[name="Incident.CustomFields.WDMR.createRA"][value=0]').prop("checked",true);
				$('input[name="Incident.Subject"]').val("Damaged/Wrong Item - Reship - New address, Price Over Limit, Ordered 1 - Agent Review");
				$('input[name="Incident.CustomFields.WDMR.Reason"]').val("Damaged/Wrong Item - Reship - New address, Price Over Limit, Ordered 1 - Agent Review");
			}
		}
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
		
	} else if(type=="2") {  //Next button to hide the form, show submit buttons.
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
 function DI_RefundReshipSelection(type) {
	//alert("in RefundReshipSelection");
	//alert(type);
	//alert(window.location.href);
	if(type == '0') {
		//trim off the /wdi parameter, then add it back...
		//oldHref = window.location.href;
		oldHref = window.location.pathname;
		i = oldHref.indexOf("/wd/");
		result = oldHref.substr(0, i);
		//alert(result); 
		newHref = result + "/wd/" + type;
		//alert(newHref);
		window.location.href = newHref;
	} else {
		//newHref = window.location.href + "/rr/" + type;
		newHref = window.location.pathname + "/rr/" + type;
		//alert(newHref);
		window.location.href = newHref;
	}
}
</script>