<rn:meta title="#rn:msg:CUSTOM_MSG_RECALL_FORM_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	
	<div class="rn_Container rn_Returns2022 rn_Recall2022">
		<rn:condition config_check="CUSTOM:CUSTOM_CFG_RECALL_FORM_ENABLED == true">
		<? /* <div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline">
			<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_RECALL_TITLE#</h1>
			<span class="rn_ParagraphTextNormal">#rn:msg:CUSTOM_MSG_RECALL_HEADER_TEXT#</span>
		</div> */ ?>
		<? if($referrer == 'US') {  ?>  
		<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
		    <div class="rn_ContentDetail rn_RecallAnswers2022">
		        <div class="rn_PageTitle rn_RecordDetail">
			        
			    <div class="rn_AnswerQuestion">
		                <rn:field name="Answer.Question" highlight="true" id="5696"/>
		        </div>
		        </div>
		
		        <div class="rn_PageContent rn_RecordDetail rn_LeftArea">
		            <div id="rn_Returns2022" class="rn_RecordText rn_AnswerText rn_RecallAnswers2022" itemprop="articleBody">
		                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true" id="5696" /></span>
		                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true" id="5696"/></div>
		                
		        
						<div id="bissellcomURL" style="display:none;"><rn:field name="Answer.CustomFields.c.bissellcom_video_url" highlight="true" id="5696"/></div>
		            </div>
		            <rn:widget path="knowledgebase/GuidedAssistant"/>
		            <? /* <div class="rn_FileAttach">
		                <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#" id="#rn:php:$recallAnswerID#"/>
		            </div>
		            */ ?>
					
		            
		        </div>
		    </div>
		    <? /* 
		    <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div>
		    */ ?>
		</article>
		<? } else { ?>
		<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
		    <div class="rn_ContentDetail  rn_RecallAnswers2022">
		        <div class="rn_PageTitle rn_RecordDetail">
			        
			    <div class="rn_AnswerQuestion">
		                <rn:field name="Answer.Question" highlight="true" id="5697"/>
		        </div>
		        </div>
		
		        <div class="rn_PageContent rn_RecordDetail rn_LeftArea">
		            <div id="rn_Returns2022" class="rn_RecordText rn_AnswerText rn_RecallAnswers2022" itemprop="articleBody">
		                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true" id="5697" /></span>
		                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true" id="5697"/></div>
		                
		        
						<div id="bissellcomURL" style="display:none;"><rn:field name="Answer.CustomFields.c.bissellcom_video_url" highlight="true" id="5697"/></div>
		            </div>
		            <rn:widget path="knowledgebase/GuidedAssistant"/>
		            <? /* <div class="rn_FileAttach">
		                <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#" id="#rn:php:$recallAnswerID#"/>
		            </div>
		            */ ?>
					
		            
		        </div>
		    </div>
		    <? /* 
		    <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div>
		    */ ?>
		</article>
		<? } ?>
	</div>
	<div class="rn_Container rn_AskQuestion">
		<div class="rn_AAQHead">
			<h1 class="rn_AAQHead">#rn:msg:CUSTOM_MSG_RECALL_FORM_HDG#</h1>
			
				<? if($referrer == 'US') { ?>
					<!-- US -->
					#rn:msg:CUSTOM_MSG_RECALL_RESPONSE_TXT_US#
				<? } else { ?>
				   <!-- Canada -->
				   #rn:msg:CUSTOM_MSG_RECALL_RESPONSE_TXT_CA#
				<? } ?>
			<rn:condition_else/>
				<? /* #rn:msg:CUSTOM_MSG_RECALL_RESPONSE_TXT_AAQ_DISABLED# */ ?>
			</rn:condition>
		</div>
		<form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
			<rn:condition config_check="CUSTOM:CUSTOM_CFG_RECALL_FORM_ENABLED == true">
				<div class="create-account  email-customer-care-form">
					<div class="contact-form form">
						<div class="required-text">#rn:msg:CUSTOM_MSG_AAQ_REQUIRED_TEXT#</div>
						<div role='alert' id="rn_ErrorLocation"></div>
						<? /* <div class="title">
							<h2 class="rn_h2Header">#rn:msg:CUSTOM_MSG_COMMENT_OR_QUESTION_HDR#</h2>
						</div> */ ?>
						<div class="form-item">
							<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="false" label_input="#rn:msg:EMAIL_ADDR_LBL#" autocomplete="email"/>
						</div>
						<div class="form-item rn_Hidden">
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_source" required="true" initial_focus="false" label_input="#rn:msg:CUSTOM_MSG_AAQ_SOURCE_LBL#" default_value="118"/>
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
							<rn:widget path="input/FormInput" name="Contact.Address.Country" required="true" initial_focus="false" label_input="#rn:msg:COUNTRY_LBL#" default_value="1"/>
						</div>
						<div class="form-item">
							<div id="divCAPostal" class="rn_Hidden">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_postal_code" always_show_mask="true" />
							</div>
							<div id="divUSPostal">
								<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_zipcode" required="true" always_show_mask="true" />
							</div>
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
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.aaq_contact_country" required="false" default_value="US" />
						</div>
						<div class="form-item rn_Hidden">
							<rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="#rn:msg:COMMENTS_LBL#" default_value="Recall Product 2022"/>
							<rn:widget path="input/FormInput" name="Incident.CustomFields.c.incident_type" required="true"  default_value="115"/>
						</div>
	
						
	
						<?/* commenting out for now
						<div class="form-item">
							<rn:widget path="input/FileAttachmentUpload" name="Incident.FileAttachments" valid_file_extensions="#rn:msg:CUSTOM_MSG_ASK_ATTACHMENTS#" />
						</div>
						
						<div id="rn_Challenge"></div>
						*/ ?>
						<rn:widget path="standard/input/FormSubmit" challenge_required="false" challenge_location="rn_Challenge" 
							label_button="#rn:msg:CUSTOM_MSG_SUBMIT_AAQ_BTN#" on_success_url="/app/cordless-safety-recall_confirm" error_location="rn_ErrorLocation"/>

						<?/* commenting out for now
						<rn:widget path="input/SmartAssistantDialog"/> 
						*/ ?>
						<? /* <rn:widget path="input/SmartAssistantDialog" display_inline="false" label_prompt="#rn:msg:OFFICIAL_SSS_MIGHT_L_IMMEDIATELY_MSG#"/> 
						<rn:widget path="input/SmartAssistantDialog" label_prompt="#rn:msg:OFFICIAL_SSS_MIGHT_L_IMMEDIATELY_MSG#"/> */ ?>


					</div>
				</div>
			
		</form>
		<rn:condition_else/>
		#rn:msg:CUSTOM_MSG_RECALL_RESPONSE_TXT_AAQ_DISABLED#
	</rn:condition>
	</div>
</div>

<rn:condition config_check="CUSTOM:CUSTOM_CFG_RECALL_FORM_ENABLED == true">
<div class="dialog-overlay" tabindex="-1"></div>
</rn:condition>
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

<script type="text/javascript">
    $(document).ready(function() {
	    $('.rn_ReturnAnswerLink').on('click', function(event) {
			event.preventDefault();
		    //alert(this.id);
		    //alert(this.id.split(/\_/)[1]);
		    theSection = this.id.split(/\_/)[1];
		    //alert(theSection);	
		    answerTitle = "#AnswerTitle_" + theSection + " a";
		    //alert(answerTitle);
		    answerSection = "#AnswerText_" + theSection;
		    //alert(answerSection);
		    if ($(answerSection).hasClass('rn_Hidden'))
		        $(answerSection).removeClass('rn_Hidden');
		    else
		        $(answerSection).addClass('rn_Hidden');
		    
		    $(answerTitle).focus();
		});
		$('input[name="Incident.CustomFields.c.model_num"]').change(function() {
		    var theModels = "#rn:msg:CUSTOM_MSG_RECALL_MODELS#";
		    theModels = theModels.toUpperCase();
		    var inputModel = $('input[name="Incident.CustomFields.c.model_num"]').val();
		    inputModel = inputModel.toUpperCase();
		    //alert(theModels);
		    //alert(inputModel);
		    if(theModels.indexOf(inputModel) < 0) {
			    alert("#rn:msg:CUSTOM_MSG_RECALL_NOT_INCLUDED#");
			    $('input[name="Incident.CustomFields.c.model_num"]').val("");
			    
		    }
	    });
	});
     
</script>