<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<div class="rn_Container rn_AskQuestion">
		<div class="rn_AAQHead">
			<h1 class="rn_AAQHead">Email Consumer Care</h1>
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<? if($referrer == 'US') { ?>
					<!-- US -->
					#rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_US#
				<? } else { ?>
				   <!-- Canada -->
				   #rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_CA#
				<? } ?>
			<rn:condition_else/>
				#rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_AAQ_DISABLED#
			</rn:condition>
		</div>
		<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<div class="create-account  email-customer-care-form">
					<div class="contact-form form">
						<div class="required-text">#rn:msg:CUSTOM_MSG_AAQ_REQUIRED_TEXT#</div>
						<div role='alert' id="rn_ErrorLocation"></div>
						<div class="title">
							<h2 class="rn_h2Header">#rn:msg:CUSTOM_MSG_COMMENT_OR_QUESTION_HDR#</h2>
						</div>
						<div class="form-item" style="margin-top: 40px;">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_subject" required="true" initial_focus="false" label_input="#rn:msg:SUBJECT_LBL#"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_num" required="false" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_MODEL_NUMBER_LBL# <a href='#' class='open-dialog'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
							
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.serial_num" required="false" initial_focus="false" label_input="Serial Number <a href='#' class='open-dialog2'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
							
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.ph_home" required="false" initial_focus="false" label_input="#rn:msg:PHONE_LBL#" always_show_mask="true"/>
						</div>
						<div class="rn_Hidden" id="divActiveRecall">
							<label style="color:red;" class="rn_Label">#rn:msg:CUSTOM_MSG_RECALL_AAQ_MODEL_NUM_ERROR#</label>
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
						<div class="title">
							<h2 class="rn_h2Header">#rn:msg:CONTACT_INFO_LBL#</h2>
						</div>
						<div class="form-item rn_Hidden">
							<? if($referrer == "CA") { ?>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="CA" />
							<? } else { ?>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="US" />
							<? } ?>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_first_name" required="true" initial_focus="false" label_input="#rn:msg:FIRST_NAME_LBL#"/>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="1"/>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Address.Street" required="false" initial_focus="false" label_input="#rn:msg:STREET_ADDRESS_LBL#"/>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Address.City" required="false" initial_focus="false" label_input="#rn:msg:CITY_LBL#"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" required="false" initial_focus="false" label_input="#rn:php:$stateorprovinceLbl#"/>
						</div>
						<div class="form-item">
							<div id="divCAPostal" class="rn_Hidden">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" required="false" always_show_mask="true" />
							</div>
							<div id="divUSPostal">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_zipcode" required="false" always_show_mask="true" />
							</div>
						</div>
						
						<div class="rn_Hidden">
							<rn:widget path="input/FormInput" name="Contact.Name.First" required="false" initial_focus="false" />
							<rn:widget path="input/FormInput" name="Contact.Name.Last" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_street_address" required="false" />			   
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_city" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_state" required="false" />
							<!-- note, we are reversing the inputs here so that we can control the masking of the data on the form more efficiently.  
								The javascript will automatically add a -0000 to the end of the 5 digit zip code -->
							<rn:widget path="input/FormInput" name="Contact.Address.PostalCode" required="false" initial_focus="false" always_show_mask="true"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_home_phone" required="false" />
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_AAQ_SOURCE_LBL#" default_value="117"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:COMMENTS_LBL#"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" />
							<span class="rn_uploadNotice">File size of photo cannot exceed 20MB</span>
						</div>
	
						<div id="rn_Challenge"></div>

						<rn:widget path="standard/input/FormSubmit" challenge_required="true" challenge_location="rn_Challenge" 
							label_button="#rn:msg:CUSTOM_MSG_SUBMIT_AAQ_BTN#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation"/>

						<? /* <rn:widget path="input/SmartAssistantDialog" display_inline="false" label_prompt="#rn:msg:OFFICIAL_SSS_MIGHT_L_IMMEDIATELY_MSG#"/> */ ?>
						<rn:widget path="input/SmartAssistantDialog" label_prompt="#rn:msg:OFFICIAL_SSS_MIGHT_L_IMMEDIATELY_MSG#"/>


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

.rn_uploadNotice{
	font-style: italic;
	font-size: x-small;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
<script src="/euf/assets/themes/standard/AAQJS/standardfunctions.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		var aaqform = RightNow.Form.find("rn_QuestionSubmit");

		aaqform.on("submit", formSubmit, this);

		function formSubmit(){
			var inputModel = $('input[name="Incident.CustomFields.c.model_num"]').val();

			if(inputModel){
				inputModel = inputModel.toUpperCase();
				var theModels = "#rn:msg:CUSTOM_MSG_RECALL_MODELS#";
				theModels = theModels.toUpperCase();
				var models = theModels.split(',');
				if(models.indexOf(inputModel) > 0) {
					$("#rn_ErrorLocation").append('<div style="color:#3d090b; font-weight:700; font-size:16px; line-height:1.3;"><b>#rn:msg:CUSTOM_MSG_RECALL_AAQ_MODEL_NUM_ERROR#</b></div>');
					return false;
				}
			}
		};

		$('input[name="Incident.CustomFields.c.model_num"]').change(function() {
			var inputModel = $('input[name="Incident.CustomFields.c.model_num"]').val();
			if(inputModel){
				inputModel = inputModel.toUpperCase();
				var theModels = "#rn:msg:CUSTOM_MSG_RECALL_MODELS#";
				theModels = theModels.toUpperCase();
				var models = theModels.split(',');
				if(models.indexOf(inputModel) < 0) {
					$("#divActiveRecall").addClass('rn_Hidden');
				} else {
					$("#divActiveRecall").removeClass('rn_Hidden');
				}
			} else {
				$("#divActiveRecall").addClass('rn_Hidden');
			}
		});
	});
</script>
