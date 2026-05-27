<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="standard_internal.php" clickstream="incident_create" login_required="true" />

<div class="rn_PageContent rn_AskQuestion rn_Container">
     <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
	     <input type="hidden"  value ="yes">
        <div id="rn_ErrorLocation"></div>
        
        <div id="rn_CategorySelectorDiv">
			
			<rn:widget path="custom/input/ProductCategoryInputSelect" data_type="Category" name="Incident.Category" label_all_values="Questions/Comments regarding * :" label_nothing_selected="Customer Type:" />		
		</div>
				<div id="rn_submitAAQ_section" style="display:none;">	             
        	<rn:widget path="custom/input/CustomAllInputDynamic" table="Incident" data_type="Category"/>
		</div>

        <div id="rn_names">
        	<rn:widget path="input/FormInput" name="Incident.CustomFields.c.salutation" label_input="Salutation" required="false"/>
        	<rn:widget path="input/FormInput" name="Contact.Name.First" label_input="#rn:msg:FIRST_NAME_LBL#" required="true" placeholder="#rn:msg:FIRST_NAME_LBL#" initial_focus="true"/>
			<rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="#rn:msg:LAST_NAME_LBL#" required="true" placeholder="#rn:msg:LAST_NAME_LBL#" /> 
			
     		</div>
     		
     		<div id="rn_email">
			<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true"  label_input="#rn:msg:EMAIL_ADDR_LBL#"/>
			<rn:widget path="input/FormInput" name="contacts.ph_mobile" required="true" label_input="#rn:msg:PHONE_LBL#" always_show_hint="true" hint="( Telephone formatting = xxx-xxx-xxxx )"/>
     		</div>
     		
     		<div id="rn_street">
			<rn:widget path="input/FormInput" name="contacts.street" label_input="#rn:msg:STREET_ADDRESS_LBL#" required="true" placeholder="#rn:msg:STREET_ADDRESS_LBL#" /> 
			<rn:widget path="input/FormInput" name="contacts.city" label_input="#rn:msg:CITY_LBL#" required="true" placeholder="#rn:msg:CITY_LBL#" />
     		</div>
     		
     		<div id="rn_address">
	     		<div id="rn_country">
	     	<rn:widget path="input/FormInput"  name="contacts.country_id" default_value="1"/></div>		
			<rn:widget path="input/FormInput" name="contacts.postal_code" label_input="#rn:msg:POSTAL_CODE_LBL#" required="true" placeholder="#rn:msg:POSTAL_CODE_LBL#" always_show_hint="true" hint="( Please Enter 5 digit zip )" /> 
			<div id="rn_state">
			<rn:widget path="input/FormInput" name="Contact.Address.StateOrProvince" label_input="#rn:msg:STATE_LBL#" required="true" placeholder="#rn:msg:STATE_LBL#" />
			</div>
			<rn:widget path="input/FormInput" name="Incident.CustomFields.c.owned" label_input="#rn:msg:CUSTOM_MSG_ALREADY_OWN#" required="true" placeholder="#rn:msg:CUSTOM_MSG_ALREADY_OWN#" />
			<rn:widget path="input/FormInput" name="Incident.CustomFields.c.i_am_16_years_old" label_input="#rn:msg:CUSTOM_MSG_AGE#" required="true" placeholder="#rn:msg:CUSTOM_MSG_AGE#" />
			
				
     		</div>
     	<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#" /> 
		<rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:QUESTION_LBL#"/>
		
		<div id="rn_checkboxes" >
			<label class="rn_check"><input type="checkbox" required="" checked="" >#rn:msg:CUSTOM_MSG_OPT_IN#</label>
		</div>
			
		<div id="rn_fileAttachment">
			<rn:widget path="input/FileAttachmentUpload"/> 
		</div>
		
		<rn:widget path="input/FormSubmit" label_button="Submit" on_success_url="/app/ask_confirm" challenge_required="true" challenge_location="rn_captcha" error_location="rn_ErrorLocation"/>
		
</form>
</div>


 <script type="text/javascript" >
$(document).ready(function(){
	
	 
   	
	$("#rn_QuestionSubmit").trigger('reset');
	$("input[type=text], textarea").val("");
		console.log('showMe'); 	

} );
function displayFormSubmit()
{
	$("#rn_submitAAQ_section").show();

}


</script>




 

   
 
	   

