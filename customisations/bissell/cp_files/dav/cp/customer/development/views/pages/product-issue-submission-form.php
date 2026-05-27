<rn:meta title="#rn:msg:CUSTOM_MSG_ASK_CROSSWAVE_ERRORS_TITLE_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<div class="rn_Container rn_AskQuestion">
		<div class="rn_AAQHead">
			<h1 class="rn_AAQHead">CrossWave Cordless Max Error Code Issue Submission form</h1>
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<? if($referrer == 'US') { ?>
					<!-- US -->
					#rn:msg:CUSTOM_MSG_CROSSWAVE_ERROR_ASK_BODY_US#
				<? } else { ?>
				   <!-- Canada -->
				   #rn:msg:CUSTOM_MSG_CROSSWAVE_ERROR_ASK_BODY_CA#
				<? } ?>
			<rn:condition_else/>
				#rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_AAQ_DISABLED#
			</rn:condition>
		</div>
		<div style="padding:10px;"></div>
		<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
				<div class="create-account  email-customer-care-form">
					<div class="contact-form form">
						<div class="required-text">#rn:msg:CUSTOM_MSG_AAQ_REQUIRED_TEXT#</div>
						<div role='alert' id="rn_ErrorLocation"></div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
						</div>
						<div class="form-item rn_Hidden">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_AAQ_SOURCE_LBL#" default_value="116"/>
						</div>
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
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.serial_num" required="true" initial_focus="false" label_input="Serial Number <a href='#' class='open-dialog2'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>"/>
						</div>
						<div class="dialog2" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
							<button class="close-button" type="button" aria-label="Close Navigation" class="close-dialog2"> 
							<img id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" alt=" ">
							</button>
							#rn:msg:CUSTOM_MSG_FIND_SERIAL_HTML#
						</div>
						<div class="form-item rn_Hidden">
							<? if($referrer == "CA") { ?>
								<rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="2"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="CA" />
								<? /* <rn:widget path="input/FormInput"  name="Contact.country_id" default_value="2"/> */ ?>
							<? } else { ?>
								<rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="1"/>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="US" />
								<? /*  <rn:widget path="input/FormInput"  name="Contact.country_id" default_value="1"/> */ ?>
							<? } ?>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_first_name" required="true" initial_focus="false" label_input="#rn:msg:FIRST_NAME_LBL#"/>
						</div>
						<div id="" class="form-item">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
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
							<? if($referrer == "CA") { ?>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" required="true" always_show_mask="true" />
							<? } else { ?>
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_zipcode" required="true" always_show_mask="true" />
							<? } ?>
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.ph_home" required="true" initial_focus="false" label_input="#rn:msg:PHONE_LBL#" always_show_mask="true"/>
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
						</div>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:COMMENTS_LBL#"/>
						</div>
						<div class="form-item">
							<rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" min_required_attachments="1" />
							<span>#rn:msg:CUSTOM_MSG_ERROR_CODES_ATTACH_HELPER_TEXT#</span>
						</div>
	
						<div id="rn_Challenge"></div>

						<rn:widget path="standard/input/FormSubmit" challenge_required="true" challenge_location="rn_Challenge" 
							label_button="#rn:msg:CUSTOM_MSG_SUBMIT_AAQ_BTN#" on_success_url="/app/product-issue-submission-form_confirm" error_location="rn_ErrorLocation"/>
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

<script type="text/javascript">

		var navDialogEl = document.querySelector('.dialog');
    	var navDialogEl2 = document.querySelector('.dialog2');
		var dialogOverlay = document.querySelector('.dialog-overlay');
		
		var myDialog = new Dialog(navDialogEl, dialogOverlay);
		myDialog.addEventListeners('.open-dialog', '.close-dialog');
		
		var myDialog2 = new Dialog(navDialogEl2, dialogOverlay);
		myDialog2.addEventListeners('.open-dialog2', '.close-dialog2');
 	
    $(document).ready(function() {
	    $("#rn_SearchControlsOtherPages .rn_SubmitButton").prepend(' <i class="fa fa-search" aria-hidden="true"></i>');
	    
	    /* $('[name="Contact.Name.First"]').attr('autocomplete', 'given-name');
	    $('[name="Incident.CustomFields.c.aaq_contact_first_name"]').attr('autocomplete', 'given-name');
	    $('[name="Contact.Name.Last"]').attr('autocomplete', 'family-name');
	    $('[name="Incident.CustomFields.c.aaq_contact_last_name"]').attr('autocomplete', 'family-name');
	    $('[name="Contact.Address.Street"]').attr('autocomplete', 'address-line1');
	    $('[name="Incident.CustomFields.c.aaq_contact_street_address"]').attr('autocomplete', 'address-line1');
	    $('[name="Contact.Address.City"]').attr('autocomplete', 'address-level2 ');
	    $('[name="Incident.CustomFields.c.aaq_contact_city"]').attr('autocomplete', 'address-level2 ');
	    $('[name="Contact.Address.StateOrProvince"]').attr('autocomplete', 'address-level1 ');
	    $('[name="Incident.CustomFields.c.aaq_contact_statee"]').attr('autocomplete', 'address-level1 ');
	    $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').attr('autocomplete', 'postal-code');
	    $('[name="Contact.Address.PostalCode"]').attr('autocomplete', 'postal-code');
	    $('[name="Contact.Phones.HOME.Number"]').attr('autocomplete', 'tel');
	    $('[name="Incident.CustomFields.c.aaq_contact_home_phone"]').attr('autocomplete', 'tel');
	    $('[name="Contact.Emails.PRIMARY.Address"]').attr('autocomplete', 'email');
		*/
	    
	    $('input[name="Incident.CustomFields.c.aaq_contact_first_name"]').change(function() {
		    //alert($('input[name="Incident.CustomFields.c.aaq_contact_first_name"]').val());
		    $('input[name="Contact.Name.First"]').val($('input[name="Incident.CustomFields.c.aaq_contact_first_name"]').val());
		    
	    });
	    $('[name="Incident.CustomFields.c.aaq_contact_last_name"]').change(function() {
		    $('[name="Contact.Name.Last"]').val($('[name="Incident.CustomFields.c.aaq_contact_last_name"]').val());
	    });
	    $('[name="Contact.Address.Street"]').change(function() {
		    //alert($('input[name="Contact.Address.Street"]').val());
		    $('[name="Incident.CustomFields.c.aaq_contact_street_address"]').val($('[name="Contact.Address.Street"]').val());
	    });
	    $('[name="Contact.Address.City"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_city"]').val($('[name="Contact.Address.City"]').val());
	    });
	    //Country doesn't change once set in the hidden, so we will just copy the value once the page is loaded into the hidden input
	    //$('[name="Contact.Address.Country"]').change(function() {
		//    alert("country changed");
			//alert($('[name="Contact.Address.Country"]').find(":selected").text());
		//$('[name="Incident.CustomFields.c.aaq_contact_country"]').val($('[name="Contact.Address.Country"]').find(":selected").text());
	    //});
	    
	    $('[name="Contact.Address.StateOrProvince"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_state"]').val($('[name="Contact.Address.StateOrProvince"]').find(":selected").text());
	    });
	    //This input is reversed so that we can include additional logic here to add -0000 to the end of US Zip to maintain masking
	    $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').change(function() {
		    myZip = $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').val();
		    // myZip = myZip + "-0000";
		    //alert(myZip);
		    $('[name="Contact.Address.PostalCode"]').val(myZip);
	    });
	    $('[name="Incident.CustomFields.c.aaq_contact_postal_code"]').change(function() {
		    $('[name="Contact.Address.PostalCode"]').val($('[name="Incident.CustomFields.c.aaq_contact_postal_code"]').val());
	    });
	    $('[name="Contact.Phones.HOME.Number"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_home_phone"]').val($('[name="Contact.Phones.HOME.Number"]').val());
	    });
		
		$('input[name="Incident.CustomFields.c.model_num"]').change(function() {
		    var theModels = "#rn:msg:CUSTOM_MSG_CROSSWAVE_MODELS#";
		    theModels = theModels.toUpperCase();
		    var inputModel = $('input[name="Incident.CustomFields.c.model_num"]').val();
		    inputModel = inputModel.toUpperCase();
		    //alert(theModels);
		    //alert(inputModel);
		    if(theModels.indexOf(inputModel) < 0) {
			    alert("#rn:msg:CUSTOM_MSG_CROSSWAVE_NOT_INCLUDED#");
			    $('input[name="Incident.CustomFields.c.model_num"]').val("");
			    
		    }
	    });
  });
     
</script>
