<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/ModalCss/dialog.css">

<div class="rn_PageContent rn_AskQuestion rn_Container">
<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm" autocomplete="on">
<article id="" class="page-optional-right category-landing single-column">
   <div class="content-header ContentHeader" style="min-height: 0px;">
      <div class="content">
         <h1>Email Consumer Care</h1>
      </div>
   </div>
   <section>
      <article>
		 <rn:condition config_check="CUSTOM:CUSTOM_CFG_AAQ_ENABLED == true">
			   #rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_CA#
			<div class="create-account  email-customer-care-form">
            <div id="" class="contact-form form">
               <div class="required-text"><span class="required">*</span>Required fields</div>
               <div role='alert' id="rn_ErrorLocation"></div>
			   <div class="title">
				   <h2 class="rn_h2Header">Comment or Question</h2>
			  </div>
               <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
               </div>
            <?/*<div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_incSubject_divFormItem" class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_subject" required="true" initial_focus="false" label_input="Subject"/>
               </div>
			   <div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_prCategory_divFormItem" class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_product_category" required="true" initial_focus="false" label_input="Product Category"/>
               </div>*/?>
               <div class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.CustomFields.c.model_num" required="false" initial_focus="false" label_input="Model Number"/>
                  <a href='#' class="open-dialog"> Where is this?</a>
               </div>
               
        <div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
		<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> <img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" ></button>
		<h1 class="dialog-title" >Model Number</h1>
		
		<div class="title"><span>It’ll be helpful to have your machine’s model number on hand. Not sure where to look?&nbsp;</span></div><br>
		<p id="dialog-description" class=""><strong><strong><span>On machines </span></strong><span></span></strong><span>it’s on a white label like this. It’s usually on the lower back or bottom of the machine (for Lift-Off® models, remove the Lift-Off pod to find label) or behind the cleaning tanks</span><strong><span>.</span></strong></p>
		<p id="dialog-description" class=""><img alt=" " height="172" alt="" width="187" src="//bissellmedia.azureedge.net/-/media/themes/bissellcom2013/shell/body/model_on_label.png?la=en&amp;modified=20181031162409&amp;cdnv=3.5" ></p>
		<p id="dialog-description" class=""><strong><strong><span>On cleaning formulas</span></strong><span> </span></strong><span>the model number is actually called an item number. Look for it on the back of the bottle or can, near the bottom. Here’s a sample:</span><strong><span></span></strong></p>
		<p id="dialog-description" class=""><img alt=" " height="18" alt="" width="93" src="//bissellmedia.azureedge.net/-/media/themes/bissellcom2013/shell/body/item_on_chemical.png?la=en&amp;modified=20181031162409&amp;cdnv=3.5" ></p>
	</div>	        
            <div class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.CustomFields.c.serial_num" required="false" initial_focus="false" label_input="Serial Number"/>
                  <a href='#' class="open-dialog2">Where is this?</a>
               </div>
               <div class="dialog2" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
		<button class="close-button" type="button" aria-label="Close Navigation" class="close-dialog2"> <img id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" alt=" "></button>
		<h1 class="dialog-title">Where's my serial number?</h1>
		
		<div class="title"><span>It’ll be helpful to have your machine’s model number on hand. Not sure where to look?&nbsp;</span></div><br>
		<p id="dialog-description" class=""><strong>On machines:</strong> The serial number is located on a white label like this. It is usually located at the lower back or bottom of the machine (for Lift-Off models - remove Lift-Off pod to locate), or behind the cleaning tanks.</p>
		<p id="dialog-description" class=""><img alt=" " height="172"  width="187" src="//bissellmedia.azureedge.net/-/media/themes/bissellcom2013/shell/body/serial_on_label.png?la=en&amp;modified=20181031162408&amp;cdnv=3.5" alt=""></p>
		<p id="dialog-description" class=""><strong>On cleaning formulas:</strong> cleaning formulas do not contain serial numbers. Instead, please enter the full number from the UPC code. Look for it on the back of the bottle or can, near the bottom. Here is a sample:</p>
		<p id="dialog-description" class=""><img alt=" " height="82"  width="121" src="//bissellmedia.azureedge.net/-/media/themes/bissellcom2013/shell/body/upc_on_chemical.png?la=en&amp;modified=20181031162408&amp;cdnv=3.5" alt=""></p>
	
	</div>
               <div class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="Comments"/>
               </div>
               <div class="title">
	               <h2 class="rn_h2Header">Contact Information</h2>
		       </div>
               <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Name.First" required="true" initial_focus="false" label_input="First Name"/>
               </div>
               <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Name.Last" required="true" initial_focus="false" label_input="Last Name"/>
               </div>
			   <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Address.Street" required="false" initial_focus="false" label_input="Street Address"/>
               </div>
			   <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Address.City" required="false" initial_focus="false" label_input="City"/>
               </div>
			   <div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_fiLastname_divFormItem" class="form-item" style="display:none">
                  <rn:widget path="input/FormInput" name="Contact.Address.Country" required="false" initial_focus="false" label_input="Country" default_value="2"/>
                  <rn:widget path="input/ProductCategoryInput" name="Incident.Product" default_value="#rn:config:CUSTOM_CFG_CANADA_PRODUCTID#"/>
               </div>
			   <div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_fiLastname_divFormItem" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" required="false" initial_focus="false" label_input="Province"/>
               </div>
			   <div id="" class="form-item">
                  <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" required="false" always_show_mask="true" />
               </div>
			   <div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_fiLastname_divFormItem" class="form-item">
                  <rn:widget path="input/FormInput" name="Contact.ph_home" required="false" initial_focus="false" label_input="Phone" always_show_mask="true"/>
               </div>
			   <div id="phmaincontainerarticleplacement_0_phmaincontainerarticlesection_0_phmaincontainerarticlediv_0_fileAttachment_divFormItem" class="form-item">
                  <rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" />
               </div>
			   <div class="rn_Hidden">
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_first_name" required="true" initial_focus="false" />
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_last_name" required="true" />
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_street_address" required="false" />
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" />				   
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_city" required="false" />
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_state" required="false" />
				   <!-- note, we are reversing the inputs here so that we can control the masking of the data on the form more efficiently.  The javascript will automatically add a -0000 to the end of the 5 digit zip code -->
				   <rn:widget path="input/FormInput" name="Contact.Address.PostalCode" required="false" initial_focus="false" label_input="Postal Code" always_show_mask="true"/>
				   <rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_home_phone" required="false" />
			   </div>
			   <div id="rn_Challenge"></div>
			   
				<rn:widget path="custom/input/FormSubmit" challenge_required="true" challenge_location="rn_Challenge" label_button="Submit Form" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation"/>
				
				<rn:widget path="input/SmartAssistantDialog" display_inline="false" label_prompt="#rn:msg:OFFICIAL_SSS_MIGHT_L_IMMEDIATELY_MSG#"/>
			</div>
         </div>
		 <rn:condition_else/>
			#rn:msg:CUSTOM_MSG_EMAIL_RESPONSE_TXT_AAQ_DISABLED#
		</rn:condition>
      </article>
   </section>
</article>
</form>
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


<script type="text/javascript">

		var navDialogEl = document.querySelector('.dialog');
    	var navDialogEl2 = document.querySelector('.dialog2');
		var dialogOverlay = document.querySelector('.dialog-overlay');
		
		var myDialog = new Dialog(navDialogEl, dialogOverlay);
		myDialog.addEventListeners('.open-dialog', '.close-dialog');
		
		var myDialog2 = new Dialog(navDialogEl2, dialogOverlay);
		myDialog2.addEventListeners('.open-dialog2', '.close-dialog2');
 	
    $(document).ready(function() {
	    
	    $('[name="Contact.Name.First"]').attr('autocomplete', 'given-name');
	    $('[name="Contact.Name.Last"]').attr('autocomplete', 'family-name');
	    $('[name="Contact.Address.Street"]').attr('autocomplete', 'address-line1');
	    $('[name="Contact.Address.City"]').attr('autocomplete', 'address-level2 ');
	    $('[name="Contact.Address.StateOrProvince"]').attr('autocomplete', 'address-level1 ');
	    $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').attr('autocomplete', 'postal-code');
	    $('[name="Contact.Phones.HOME.Number"]').attr('autocomplete', 'tel');

	    $('[name="Contact.Address.StateOrProvince"]').value = 1;
		
	    $('[name="Contact.Name.First"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_first_name"]').val($('[name="Contact.Name.First"]').val());
	    });
	    $('[name="Contact.Name.Last"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_last_name"]').val($('[name="Contact.Name.Last"]').val());
	    });
	    $('[name="Contact.Address.Street"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_street_address"]').val($('[name="Contact.Address.Street"]').val());
	    });
	    $('[name="Contact.Address.City"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_city"]').val($('[name="Contact.Address.City"]').val());
	    });
	    //Country doesn't change once set in the hidden, so we will just copy the value once the page is loaded into the hidden input
	    //$('[name="Contact.Address.Country"]').change(function() {
		//    alert("country changed");
			//alert($('[name="Contact.Address.Country"]').find(":selected").text());
		$('[name="Incident.CustomFields.c.aaq_contact_country"]').val($('[name="Contact.Address.Country"]').find(":selected").text());
	    //});
	    $('[name="Contact.Address.StateOrProvince"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_state"]').val($('[name="Contact.Address.StateOrProvince"]').find(":selected").text());
	    });
	    //This input is reversed so that we can include additional logic here to add -0000 to the end of US Zip to maintain masking
	    $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').change(function() {
		    myZip = $('[name="Incident.CustomFields.c.aaq_contact_zipcode"]').val();
		    myZip = myZip + "-0000";
		    //alert(myZip);
		    $('[name="Contact.Address.PostalCode"]').val(myZip);
	    });
	    $('[name="Incident.CustomFields.c.aaq_contact_postal_code"]').change(function() {
		    $('[name="Contact.Address.PostalCode"]').val($('[name="Incident.CustomFields.c.aaq_contact_postal_code"]').val());
	    });
	    $('[name="Contact.Phones.HOME.Number"]').change(function() {
		    $('[name="Incident.CustomFields.c.aaq_contact_home_phone"]').val($('[name="Contact.Phones.HOME.Number"]').val());
	    });
    });
  
    
</script>
</div>