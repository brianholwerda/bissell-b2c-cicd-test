<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="standard_internal.php" clickstream="incident_create" login_required="true"/>

<?/*<div class="rn_Hero">
    <div class="rn_HeroInner">
        <div class="rn_HeroCopy">
	             	<h1>#rn:msg:SUBMIT_QUESTION_OUR_SUPPORT_TEAM_CMD#</h1>
            	<div class="rn_HeaderText">
				<p>#rn:msg:OUR_DEDICATED_RESPOND_WITHIN_48_HOURS_MSG#</p>
            </div>
        </div>
        <div class="translucent">
            <strong>#rn:msg:TIPS_LBL#:</strong>
            <ul>
                <li><i class="fa fa-thumbs-up"></i> #rn:msg:INCLUDE_AS_MANY_DETAILS_AS_POSSIBLE_LBL#</li>
            </ul>
        </div>
        <br>
        <div class="rn_HeaderResponse">
        <p>#rn:msg:NEED_A_QUICKER_RESPONSE_LBL# <a href="/app/social/ask#rn:session#">#rn:msg:ASK_OUR_COMMUNITY_LBL#</a></p>
        </div>
    </div>
</div>*/?>

<div id="rn_FeedbackHeader">
	<p>#rn:msg:CUSTOM_MSG_FEEDBACK_HEADER#</p>
	<span id="FeedbackMsg">#rn:msg:CUSTOM_MSG_FEEDBACK_MSG#</span><br><br>
	<span id="rn_required">Required</span>
</div>

<div class="rn_PageContent rn_AskQuestion rn_Container">
    <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
        <div id="rn_ErrorLocation"></div>
        <rn:condition logged_in="false">
        
        <rn:widget path="input/ProductCategoryInput" name="Incident.Category"/>
        <rn:widget path="input/ProductCategoryInput" name="Incident.Product" label_input="Concepts"/>
        
        <rn:widget path="input/FormInput" name="contacts.first_name" required="true" initial_focus="true" label_input="#rn:msg:FIRST_NAME_LBL#"/>
        
        <rn:widget path="input/FormInput" name="contacts.last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
        
       <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="false" label_input="#rn:msg:EMAIL_ADDR_LBL#"/>
       
        <rn:widget path="input/FormInput" name="Contact.CustomFields.c.team_name" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TEAM_NAME#"/>
        
        
        <?/*<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:SUBJECT_LBL#"/>*/?>
        </rn:condition>
        <rn:condition logged_in="true">
        
        <rn:widget path="input/ProductCategoryInput" name="Incident.Category"/>
        <rn:widget path="input/ProductCategoryInput" name="Incident.Product" label_input="Concepts"/>
        
        
        
       <div id="rn_name">
        <rn:widget path="input/FormInput" name="contacts.first_name" required="true" initial_focus="false" label_input="#rn:msg:FIRST_NAME_LBL#"/>
        
        <rn:widget path="input/FormInput" name="contacts.last_name" required="true" initial_focus="false" label_input="#rn:msg:LAST_NAME_LBL#"/>
       </div>
       
       <div id="rn_contacts">
        <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="false" label_input="#rn:msg:EMAIL_ADDR_LBL#"/>
        
        <rn:widget path="input/FormInput" name="Contact.CustomFields.c.team_name" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_TEAM_NAME#"/>
       </div>
        
       

        
        </rn:condition>
        <rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="#rn:msg:CUSTOM_MSG_SUGGESTION_LBL#"/>
        
        <?/*<rn:widget path="input/FileAttachmentUpload"/>*/?>
        <rn:widget path="input/FormSubmit" label_button="#rn:msg:SUBMIT_YOUR_QUESTION_CMD#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation"/>
            </form>
            <rn:condition content_viewed="2" searches_done="1">
        <rn:condition_else/>
       <rn:widget path="input/SmartAssistantDialog" 
	dialog_width="385px" 
	label_cancel_button="" 
	label_submit_button="Send" 
	label_solved_button="Cancel" 
	label_dialog_title="EMAIL US" 
	label_banner="Great Question!" 
	label_prompt="#rn:msg:CUSTOM_MSG_SMART_ASST_MORE#" 
	label_no_results="#rn:msg:CUSTOM_MSG_SA_NO_SUGGESTIONS_FOUND#"
	solved_url="/app/home" 
	dnc_redirect_url="/app/home" 
	display_answers_inline="false" />

        </rn:condition>

</div>

