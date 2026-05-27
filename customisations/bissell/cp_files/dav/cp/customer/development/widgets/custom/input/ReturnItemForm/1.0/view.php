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
	$debugging_output = "<br>This is the Return Item Flow<br>";
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

<!--
<?	echo "<br>";
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
	print("This is product price:");
	echo "<br>";
	print_r($this->data['attrs']['productPriceCurrent']);
	echo "<br>";
			
?>
-->

 	<div class="rn_WDMR_rnHidden"><!--rn_Hidden-->
 		************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
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
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ShipToNewAddress" default_value="0" />
        <rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_source" default_value="100" />
		<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" default_value="119"/>
        <rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.QuantityOrdered" default_value="#rn:php:$this->data['getOrder']['Quantity']#" />
        
        ********************************************************************************
    </div>

<?  
	if($this->data['attrs']['issueType'] == "RI") {					//RETURNS
		//if is isNarvarEligible then
		if($this->data['attrs']['isNarvarEligible'] == "Y") { 
			$debugging_output .= "is isNarvarEligible<br>" ;
			
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
	            	<rn:widget path="custom/navigation/CallToActionBox" class_name="rn_WDMRNarVarCallToActionBox rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="" subtext="" button_2_text="#rn:msg:CUSTOM_MSG_WDMR_NARVAR_OK_BTN#" button_2_url="#rn:config:CUSTOM_CFG_WDMR_NARVAR_REDIRECT_URL#" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
	            <? } else { ?>
	            	<!-- CANADA -->
	            	<rn:widget path="custom/navigation/CallToActionBox" class_name="rn_WDMRNarVarCallToActionBox rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="" subtext="" button_2_text="#rn:msg:CUSTOM_MSG_WDMR_NARVAR_OK_BTN#" button_2_url="#rn:config:CUSTOM_CFG_WDMR_NARVAR_REDIRECT_CAN_URL#" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
	            <? } ?>
	        </div>
	 <? } else {
		 	?> <div class="rn_WDMR_rnHidden">
		 		<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.IsNarvarEligible" default_value="0" />
		 	</div> <?
			$debugging_output .= "is NOT isNarvarEligible<br>" ;
			if($this->data['attrs']['NoChargeOrderCount'] < $OrderNumberLimit && $this->data['attrs']['RefundCount'] < $RefundNumberLimit ) {
				//Less than 2 $0 orders
				$debugging_output .= "Less than 2 0$ orders and Less than 2 Refunds<br>" ;
				if($this->data['attrs']['isHazardous'] == '1') {
					//Formula or Aerosol
					$debugging_output .= "is isHazardous - no returns allowed.<br>" ;
					//****Display message notifying consumer that the refund confirmation will be sent to the partially displayed email address
				?>	<p>
						<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
					</p>
					<p>
						#rn:msg:CUSTOM_MSG_WDMR_HAZARDOUS_RETURN_TXT#
					</p>
					<p>
						#rn:msg:CUSTOM_MSG_WDMR_CANCEL_STAY#
					</p>
					<? /* KLA: As of 4/6 Bissell did NOT want to include returns on Hazardous/chemical items.  Just show message and have use leave page. 
						
						<div class="rn_WDMR_rnHidden">
						************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
						<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" default_value="Return Item Refund - Formula - No Agent Review"/>
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
						<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
						********************************************************************************
					</div> */ ?>
					<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
		  			<? /* <rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:SUBMIT_YOUR_QUESTION_CMD#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>	 */ ?>
			 <? } else {
					//Not formula or Aerosol
					$debugging_output .= "is NOT isHazardous<br>" ;
					if($this->data['attrs']['ActiveSKU'] == '1') {
						//SKU is active	
						$debugging_output .= "SKU is active<br>" ;
						if($this->data['attrs']['productPriceCurrent'] <= $productPriceFloatLimit) {
							//Product Price <= 50
							$debugging_output .= "Price <= 50<br>" ;
							//**** Display message notifying consumer that the refund confirmation will be sent to the partially displayed email address	
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
								************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
								<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
									default_value="Return Item - Refund - Price under limit(#rn:php:$productPriceLimit#) - No Agent Review"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
									default_value="Return Item - Refund - Price under limit(#rn:php:$productPriceLimit#) - No Agent Review"/>
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
							$debugging_output .= "Price > 50<br>" ;
							if($this->data['getOrder']['Quantity'] > 1) {
								//**** Create an incident for agent review, they ordered more than one
								$debugging_output .= "Qty Ordered > 1<br>" ;
							?>	<p>
									<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
								</p>
								<p>
									#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
								</p>
								<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
								<div class="rn_WDMR_rnHidden">
									************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
									<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
										default_value="Return Item - Ordered multiple items - Agent Review"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Return Item - Ordered multiple items - Agent Review"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
									********************************************************************************
								</div>
								<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
								<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_RETURN_REASON_LBL#"/>
								
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
					  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>	
								
							<? } else {
								//**** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
								$debugging_output .= "Qty Ordered = 1<br>" ;
							?>	<p>
									<h3>#rn:msg:CUSTOM_MSG_WDMR_PLEASE_RETURN#</h3>
								</p>
								<p>
									#rn:msg:CUSTOM_MSG_WDMR_FEDEX_LBL_EMAIL_TXT#
								</p>
								<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
								<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
								<p>
									#rn:msg:CUSTOM_MSG_WDMR_AFTER_RETURN_RI_EXPLAIN#
								</p>
								<div class="rn_WDMR_rnHidden">
									************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
									<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
										default_value="Return Item - Refund - Price Over Limit(#rn:php:$productPriceLimit#) - No Agent Review, Create Label, Create RA"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Return Item - Refund - Price Over Limit(#rn:php:$productPriceLimit#) - No Agent Review"/>
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="0" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
									<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
									********************************************************************************
								</div>
								
								<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
					  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
					<?		}		
				  		}		//Close price check
					} else {
						//SKU NOT active
						$debugging_output .= "SKU NOT active<br>" ;
						// **** Auto Create RA label: An FedEx return label will be emailed to the email on file (display partially hidden email). Please follow the instructions to return your damaged item. Once returned to us we will process your refund.
					?>	<p>
							<h3>#rn:msg:CUSTOM_MSG_WDMR_PLEASE_RETURN#</h3>
						</p>
						<p>
							#rn:msg:CUSTOM_MSG_WDMR_FEDEX_LBL_EMAIL_TXT#
						</p>
						<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
						<rn:widget path="custom/display/ShippingPhone" phone_number="#rn:php:$this->data['attrs']['ShippingPhone']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_PHONE#" />
						<p>
							#rn:msg:CUSTOM_MSG_WDMR_AFTER_RETURN_RI_EXPLAIN#
						</p>
						<div class="rn_WDMR_rnHidden">
							************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
							<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
								default_value="Return Item - Refund - SKU not active in price list - NO Agent Review,Create Label,Create RA"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Return Item - Refund - SKU not active in price list - No Agent Review"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyOrders" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.TooManyRefunds" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createLabel" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createOrder" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRA" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.createRefund" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.AgentReviewRequired" default_value="0" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.wdmr_process_flag" default_value="1" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
							********************************************************************************
						</div>
						<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
			  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
										
				 <? }		//Close SKU Active check
				}			//Close hazardous/Formula/Aerosol cehck
			} else {
			//More than 2 $0 orders
			$debugging_output .= ">2 $0 orders or >2 Refunds<br>" ;
			//****  Automated confirmation email sends to consumer
			//****  Create incident for Manual agent review. Also displays forward- facing (Collect Email if not on file) 3rd level category open, Disposition blank, status open,
			$debugging_output .= "Too many orders or refunds, create incident<br>" ;
			?>
				<p>
					<h3>#rn:msg:CUSTOM_MSG_WDMR_THANKYOU#</h3>
				</p>
				<p>
					#rn:msg:CUSTOM_MSG_WDMR_AGENT_REVIEW#  #rn:msg:CUSTOM_MSG_WDMR_INTERVIEW_SUMMARY#
				</p>
				<rn:widget path="custom/display/ObscureEmail" email_address="#rn:php:$this->data['attrs']['emailAddr']#" explanation_msg="#rn:msg:CUSTOM_MSG_WDMR_REQUEST_EMAIL#" />
				<div class="rn_WDMR_rnHidden">
					************ WILL NOT BE SHOWN IN PRODUCTION MODE ******************************
					<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" 
						default_value="Return Item - Too many no charge orders or returns - Agent Review"/>
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.Reason" required="true" label_input="WDMR Reason Description" 
										default_value="Return Item - Too many no charge orders or returns - Agent Review"/>
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
					<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ReshipOrRefund" default_value="2"  />
					********************************************************************************
				</div>
				<rn:widget path="input/FileAttachmentUpload"  label_input="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE#" label_invalid_extension="#rn:msg:CUSTOM_MSG_WDMR_ATTACH_IMAGE_ERROR#"/>
				<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_RETURN_REASON_LBL#"/>
				
				<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_cancel" button_1_text="Cancel" button_1_url="#rn:php:$cancelBtnLink#" />
	  			<rn:widget path="input/FormSubmitWDMR" label_button="#rn:msg:FORM_SUBMIT_LBL#" on_success_url="/app/wdmr_confirm" error_location="rn_ErrorLocation"/>
		<?	}		//Close $0 order check
		}		//Close Narvar			
	} else { 														//UNKNOWN - ERROR
		echo("Error");
		$debugging_output .= "Error<br>" ;
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
	
</script>