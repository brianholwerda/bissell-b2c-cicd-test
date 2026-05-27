<rn:meta title="#rn:msg:CUSTOM_MSG_TT_REPLACE_FORM_TITLE#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<div class="rn_Container rn_AskQuestion">
		<div class="rn_AAQHead">
			<h1 id="repairPageLabel" class="rn_AAQHead">#rn:msg:CUSTOM_MSG_TT_REPLACE_HEADER_LBL#</h1>
		</div>
		<div class="rn_TroubleshootingRectangleLayout">
			<div class="rn_TroubleshootingStepRectangles" style="display:inline-block;"><img id="imgStepOne" src="images/icons/bissell/BlueRectangle.svg" alt=""/></div>
			<div class="rn_TroubleshootingStepRectangles" style="display:inline-block;"><img id="imgStepTwo" src="images/icons/bissell/GrayRectangle.svg" alt=""/></div>
			<div class="rn_TroubleshootingStepRectangles" style="display:inline-block;"><img id="imgStepThree" src="images/icons/bissell/GrayRectangle.svg" alt=""/></div>
		</div>
		<div style="padding:10px;"><input type="hidden" id="pageName" value="Replace"></div>
		<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<div class="create-account  email-customer-care-form">
					<div class="contact-form form">
						<div class="required-text">#rn:msg:CUSTOM_MSG_AAQ_REQUIRED_TEXT#</div>
						<div role='alert' id="rn_ErrorLocation"></div>
						<div id="divProductInfoComplete" class="rn_Hidden">
							<span class="rn_TroubleshootingFormLabels">#rn:msg:CUSTOM_MSG_TT_REPAIR_PRODUCT_FORM_LBL#</span>
							<i class="fa fa-solid fa-check-circle" style="padding-right:10px; color:#0074b0;"></i>
							<span style="float:right;"><a class="aEditLink" name="ProductEditLink">#rn:msg:CUSTOM_MSG_TT_FORMS_EDIT_TEXT#</a></span>
							<hr/>
						</div>
						<div id="divProductInfoEdit">
							<div class="rn_TroubleshootingDescriptionSpacing"><span class="rn_TroubleshootingFormLabels">#rn:msg:CUSTOM_MSG_TT_REPAIR_PRODUCT_FORM_LBL#</span></div>
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_num" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_MODEL_NUMBER_LBL# <a href='#' class='open-dialog'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
							</div>
							<div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
								<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> 
								<img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" >
								</button>
								#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
							</div>	
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.serial_num" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TT_SERIALNUM_LBL# <a href='#' class='open-dialog2'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
							</div>
							<div class="dialog2" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
								<button class="close-button" type="button" aria-label="Close Navigation" class="close-dialog2"> 
								<img id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" alt=" ">
								</button>
								#rn:msg:CUSTOM_MSG_FIND_SERIAL_HTML#
							</div>
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_product_issue" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TT_PRODUCT_ISSSUE_LBL#"/>
							</div>
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Incident.Threads" required="false" label_input="#rn:msg:COMMENTS_LBL#" hint="#rn:msg:CUSTOM_MSG_TT_COMMENTS_HELPER_TEXT#" always_show_hint=true/>
							</div>
							<div class="form-item">
								<rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" min_required_attachments="1" required="true" />
								<div id="divAttach">#rn:msg:CUSTOM_MSG_TT_REPAIR_ATTACH_LBL#</div>
							</div>
							<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<button type="button" class="btn btn-primary" id="btnProductInfoNext">#rn:msg:CUSTOM_MSG_TT_FORMS_BTN_NEXT#</button>
							</div>
							<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<rn:condition url_parameter_check="pc == 'ca'">
									<a class="link-secondary" href="/app/replaceinstructions/pc/ca">< Back</a>
								<rn:condition_else/>
									<a class="link-secondary" href="/app/replaceinstructions">< Back</a>
								</rn:condition>
							</div>
						</div>
						<div id="divContactInfoComplete" class="rn_Hidden">
							<span class="rn_TroubleshootingFormLabels">#rn:msg:CUSTOM_MSG_TT_REPAIR_CONTACT_FORM_LBL#</span>
							<i class="fa fa-solid fa-check-circle" style="padding-right:10px; color:#0074b0;"></i>
							<span style="float:right;"><a class="aEditLink" name="ContactEditLink">#rn:msg:CUSTOM_MSG_TT_FORMS_EDIT_TEXT#</a></span>
							<hr/>
						</div>
						<div id="divContactInfoEdit" class="rn_Hidden">
							<div class="rn_TroubleshootingDescriptionSpacing"><span class="rn_TroubleshootingFormLabels">#rn:msg:CUSTOM_MSG_TT_REPAIR_CONTACT_FORM_LBL#</span></div>
							<div style="padding-bottom:10px;">#rn:msg:CUSTOM_MSG_TT_REPLACE_FORM_CONTACT_INFO_DISCLAIMER#</div>
							<div id="divEmail" class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="false" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
							</div>
							<div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_first_name" required="true" initial_focus="false" label_input="#rn:msg:FIRST_NAME_LBL#"/>
							</div>
							<div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
							</div>
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Contact.ph_home" required="true" initial_focus="false" label_input="#rn:msg:PHONE_LBL#" always_show_mask="true"/>
							</div>
							<div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.Street" required="true" initial_focus="false" label_input="#rn:msg:STREET_ADDRESS_LBL#"/>
							</div>
							<div id="" class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.City" required="true" initial_focus="false" label_input="#rn:msg:CITY_LBL#"/>
							</div>
							<div class="form-item">
								<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" required="true" initial_focus="false" label_input="#rn:php:$stateorprovinceLbl#"/>
							</div>
							<div class="form-item">
								<rn:condition url_parameter_check="pc == 'ca'">
									<rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="2"/>
								<rn:condition_else/>
									<rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="1"/>
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
							<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<button type="button" class="btn btn-primary" id="btnContactInfoNext">#rn:msg:CUSTOM_MSG_TT_FORMS_BTN_NEXT#</button>
							</div>
							<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<a class="aEditLink link-secondary" name="ProductBackBTN">< Back</a></span>
							</div>
						</div>
						<div id="divTermsEdit" class="rn_Hidden">
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
									label_button="#rn:msg:CUSTOM_MSG_SUBMIT_AAQ_BTN#" on_success_url="/app/replace-submission-form_confirm" error_location="rn_ErrorLocation"/>
							</div>
							<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
								<a class="aEditLink link-secondary" name="ContactBackBTN">< Back</a></span>
							</div>
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
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_AAQ_SOURCE_LBL#" default_value="127"/>
							<rn:widget path="input/FormInput" name="Incident.Subject" initial_focus="false" default_value="#rn:msg:CUSTOM_MSG_TT_REPLACE_SUBJECT#"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_description" required="false" />
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

</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
<script src="/euf/assets/themes/standard/AAQJS/standardfunctions.js"></script>
<script src="/euf/assets/themes/standard/AAQJS/ttfunctions.js"></script>