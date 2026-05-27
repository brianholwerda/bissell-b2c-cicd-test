<rn:meta title="#rn:msg:CUSTOM_MSG_TT_OUT_WARRANTY_FORM_TITLE#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<div class="rn_Container rn_AskQuestion">
		<div class="rn_AAQHead" style="display:flex;">
			<div class="rn_JustifyCenter"><img src="images/icons/bissell/wrench.svg" height="26" width="25" alt=""/></div>
			<div style="padding-left:10px;">
				<h1 id="repairPageLabel" class="rn_AAQHead">#rn:msg:CUSTOM_MSG_TT_OUT_WARRANTY_HEADER_LBL#</h1>
			</div>
		</div>
		<div class="rn_TroubleshootingDescriptionSpacing"><span class="rn_TroubleshootingFormLabels">#rn:msg:CUSTOM_MSG_TT_REPAIR_CONTACT_FORM_LBL#</span></div>
		<div style="padding-bottom:10px;">#rn:msg:CUSTOM_MSG_TT_OOW_FORM_HEADER_SUMMARYONE#</div>
		<div style="padding-bottom:10px;">#rn:msg:CUSTOM_MSG_TT_OOW_FORM_CONTACT_INFO_DISCLAIMER#</div>
		<div style="padding-bottom:10px;">#rn:msg:CUSTOM_MSG_TT_OOW_FORM_HEADER_SUMMARYTWO#</div>
		<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<div class="create-account  email-customer-care-form">
					<div class="contact-form form">
						<div class="required-text">#rn:msg:CUSTOM_MSG_AAQ_REQUIRED_TEXT#</div>
						<div role='alert' id="rn_ErrorLocation"></div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_num" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_MODEL_NUMBER_LBL# <a href='#' class='open-dialog'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.serial_num" required="true" initial_focus="false" label_input="Serial Number <a href='#' class='open-dialog2'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
						</div>
						<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_product_issue" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TT_PRODUCT_ISSSUE_LBL#"/>
							</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.ph_home" required="true" initial_focus="false" label_input="#rn:msg:PHONE_LBL#" always_show_mask="true"/>
						</div>
						<div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
							<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> 
							<img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" >
							</button>
							#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
						</div>	 
												
						
						<div class="dialog2" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
							<button class="close-button" type="button" aria-label="Close Navigation" class="close-dialog2"> 
							<img id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" alt=" ">
							</button>
							#rn:msg:CUSTOM_MSG_FIND_SERIAL_HTML#
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_first_name" required="true" initial_focus="false" label_input="#rn:msg:FIRST_NAME_LBL#"/>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
						</div>
						<rn:condition url_parameter_check="pop == null">
						   <div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.Street" required="true" initial_focus="false" label_input="#rn:msg:STREET_ADDRESS_LBL#"/>
							</div>
						</rn:condition>
						<rn:condition url_parameter_check="pop == null">
							<div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.City" required="true" initial_focus="false" label_input="#rn:msg:CITY_LBL#"/>
							</div>
						</rn:condition>
						<rn:condition url_parameter_check="pop == null">
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" required="true" initial_focus="false" label_input="#rn:php:$stateorprovinceLbl#"/>
							</div>
						</rn:condition>
						<div class="form-item">
							<rn:condition url_parameter_check="pc == 'ca'">
								<rn:widget path="input/FormInput" name="Contact.Address.Country" required="true" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="2"/>
							<rn:condition_else/>
								<rn:widget path="input/FormInput" name="Contact.Address.Country" required="true" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="1"/>
							</rn:condition>
						</div>
						<div class="form-item">
							<rn:condition url_parameter_check="pc == 'ca'">
								<div id="divCAPostal">
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" required="true" always_show_mask="true" />
								</div>
								<div id="divUSPostal" class="rn_Hidden">
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_zipcode" always_show_mask="true" />
								</div>
							<rn:condition_else/>
								<div id="divCAPostal" class="rn_Hidden">
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" always_show_mask="true" />
								</div>
								<div id="divUSPostal">
									<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_zipcode" required="true" always_show_mask="true" />
								</div>
							</rn:condition>
						</div>
						
						<div class="rn_Hidden">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.warranty_repair_confirmation" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TT_REPAIR_TERMS_FORM_LBL#"/>
							<rn:widget path="input/FormInput" name="Contact.Name.First" required="false" initial_focus="false" />
							<rn:widget path="input/FormInput" name="Contact.Name.Last" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_street_address" required="false" />			   
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_city" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_state" required="false" />
							<!-- note, we are reversing the inputs here so that we can control the masking of the data on the form more efficiently.  
								The javascript will automatically add a -0000 to the end of the 5 digit zip code -->
							<rn:widget path="input/FormInput" name="Contact.Address.PostalCode" required="false" initial_focus="false" always_show_mask="true"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_home_phone" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="US" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_AAQ_SOURCE_LBL#" default_value="126"/>
							
							<rn:condition url_parameter_check="pop != null">
								<rn:widget path="input/FormInput" name="Incident.Subject" initial_focus="false" default_value="#rn:msg:CUSTOM_MSG_TT_OOW_NO_POP_SUBJECT#"/>
							<rn:condition_else/>
								<rn:widget path="input/FormInput" name="Incident.Subject" initial_focus="false" default_value="#rn:msg:CUSTOM_MSG_TT_OOW_POP_SUBJECT#"/>
							</rn:condition>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_description" required="false" />
						</div>
						<rn:condition url_parameter_check="pop == null">
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.Threads" required="false" label_input="#rn:msg:COMMENTS_LBL#" hint="#rn:msg:CUSTOM_MSG_TT_COMMENTS_HELPER_TEXT#" always_show_hint=true/>
							</div>
						</rn:condition>
						<rn:condition url_parameter_check="pop == null">
						   <div class="form-item">
								<rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" min_required_attachments="1" required="true" />
								#rn:msg:CUSTOM_MSG_TT_OOW_POP_ATTACH_LBL#
							</div>
						</rn:condition>
						<div style="margin-left:15px; padding-bottom:15px;">
							<div class="rn_TroubleshootingRepairAcknowlegement">#rn:msg:CUSTOM_MSG_TT_WARRANTY_LINK#</div>
							<div class="rn_TroubleshootingRepairAcknowlegement">#rn:msg:CUSTOM_MSG_TT_REPAIR_TERMS_ACKNOWLEDGE_SUMMARY_TEXT#</div>
							<div class="rn_TroubleshootingRepairAcknowlegement">
								<div id="divTermsAgreement" style="width:150px;">
									<input id="chkTermsAgreement" type="checkbox">#rn:msg:CUSTOM_MSG_TT_REPAIR_AGREEMENT_CHECK_TEXT#
								</div>
							</div>
						</div>
	
						<div id="rn_Challenge" class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN"></div>
						
						<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<rn:widget path="standard/input/FormSubmit" challenge_required="true" challenge_location="rn_Challenge" 
							label_button="#rn:msg:CUSTOM_MSG_SUBMIT_AAQ_BTN#" on_success_url="/app/oow-submission-form_confirm" add_params_to_url="pop" error_location="rn_ErrorLocation"/>
						</div>
						<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
							<rn:condition url_parameter_check="pc == 'ca'">
								<a class="link-secondary" href="/app/oowpopcheck/pc/ca">< Back</a>
							<rn:condition_else/>
								<a class="link-secondary" href="/app/oowpopcheck">< Back</a>
							</rn:condition>
						</div>
					</div>
				</div>
			<rn:condition_else/>
				#rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_AAQ_DISABLED#
			</rn:condition>
		</form>
	</div>
</div>

<div class="dialog-overlay" tabindex="-1"></div>

<style>
.rn_TextInput .rn_Label, .rn_ProductCategoryInput .rn_Label, .rn_SelectionInput .rn_Label{
	float: left;
}
.rn_ProductCategoryInput .rn_Label{
	padding-left: 20.5%;
}

.rn_ProductCategoryInput button.rn_DisplayButton{
	margin-left: -25px;
}

.input-validation-error {
  border: 1px solid #ff0000 !important;
  background-color: salmon !important;
}

.aEditLink:hover{
	cursor:pointer;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
<script src="/euf/assets/themes/standard/AAQJS/standardfunctions.js"></script>
<script src="/euf/assets/themes/standard/AAQJS/ttfunctions.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		var prodDesc = sessionStorage.getItem('TTProdDescription');
		var warrDesc = sessionStorage.getItem('TTWarrDescription');
		//sessionStorage.removeItem('TTProdDescription');
		//sessionStorage.removeItem('TTWarrDescription');
		
		if(prodDesc && warrDesc){
			var summaryHTML = '#rn:msg:CUSTOM_MSG_TT_OUT_WARRANTY_PRODWARR_SUMMARY_TEXT#';
			summaryHTML = summaryHTML.replace('<insertProdDescription>', prodDesc).replace('<insertProdWarranty>', warrDesc);
			$("#spanWarrantySummary").html(summaryHTML);
			$("#spanWarrantySummary").removeClass('rn_Hidden');
		}
	});
	
	// window.onload = function(){ 
		// console.log("entered window onload"); 
		// var form = RightNow.Form.find("rn_QuestionSubmit");
		// var formAttachments = form.findField("Incident.FileAttachments");
		// formAttachments.setConstraints({required: false});
	// }
</script>
