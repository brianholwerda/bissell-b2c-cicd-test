<rn:meta title="#rn:msg:CUSTOM_MSG_RETURNS_TITLE#" template="standard.php" clickstream="contact_us"/>

<div class="rn_PageContent rn_Returns">
<div id="rn_HelpfulResources" class="rn_Container rn_Returns2022">
	<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline">
		<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_RETURNS_TITLE#</h1>
		<span class="rn_ParagraphTextNormal">#rn:msg:CUSTOM_MSG_RETURNS_HDR_SUBTEXT#</span>
	</div>

	<div id="rn_ReturnsPage" class="rn_Container">
	<? if($referrer == 'US') { ?>
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022 rn_Returns2022_Box1 rn_CallToActionBox50"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_RETURN_INSTRUCTIONS_LBL#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_INSTRUCTIONS_SUBTEXT#"
			footersubtext=""
			button_1_text="#rn:msg:CUSTOM_MSG_RETURN_START_BTN#"
			button_1_url="#rn:config:CUSTOM_CFG_WDMR_NARVAR_REDIRECT_URL#"
			button_1_id="rn_StartReturn"
			button_2_text=""
			button_2_url=""
			special_headline=""
			/>
	<? } else { ?>
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022 rn_Returns2022_Box1 rn_CallToActionBox50"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_RETURN_INSTRUCTIONS_LBL#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_INSTRUCTIONS_SUBTEXT#"
			footersubtext=""
			button_1_text="#rn:msg:CUSTOM_MSG_RETURN_START_BTN#"
			button_1_url="#rn:config:CUSTOM_CFG_WDMR_NARVAR_REDIRECT_CAN_URL#"
			button_1_id="rn_StartReturn"
			button_2_text=""
			button_2_url=""
			special_headline=""
			/> 
	<? } ?> 
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022 rn_CallToActionBox50"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_RETURN_ORDERISSUE_TITLE#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_ORDERISSUE_SUBTEXT#"
			footersubtext=""
			button_1_text="#rn:msg:CUSTOM_MSG_RETURN_ORDERISSUE_BTN#"
			button_1_url="#rn:php:$environment#/check-order"
			button_1_id="rn_SubmitOrderIssue"
			button_2_text=""
			button_2_url=""
			/>
			
		<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline rn_ReturnHowToHeadline">
		<h3 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_RETURN_HOWTO_LBL#</h3>
		<span class="">#rn:msg:CUSTOM_MSG_RETURN_HOWTO_SUBTEXT#</span>
		</div>
		<div class="rn_ReturnsHowToSection">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022_Steps rn_Returns2022_Step1 rn_CallToActionBox33"
			image_url="images/icons/bissell/ReturnsComputer.svg"
			image_alt="#rn:msg:CUSTOM_MSG_RETURN_STEP1_HDR#"
			headline="#rn:msg:CUSTOM_MSG_RETURN_STEP1_HDR#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_STEP1_SUBTEXT#"
			footersubtext=""
			button_1_text=""
			button_1_url=""
			button_1_id=""
			button_2_text=""
			button_2_url=""
			/>
			
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022_Steps rn_Returns2022_Step2 rn_CallToActionBox33"
			image_url="images/icons/bissell/ReturnsPackage.svg"
			image_alt="#rn:msg:CUSTOM_MSG_RETURN_STEP2_HDR#"
			headline="#rn:msg:CUSTOM_MSG_RETURN_STEP2_HDR#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_STEP2_SUBTEXT#"
			footersubtext=""
			button_1_text=""
			button_1_url=""
			button_1_id=""
			button_2_text=""
			button_2_url=""
			/>
			
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022_Steps rn_Returns2022_Step3 rn_CallToActionBox33"
			image_url="images/icons/bissell/ReturnsTruck.svg"
			image_alt="#rn:msg:CUSTOM_MSG_RETURN_STEP3_HDR#"
			headline="#rn:msg:CUSTOM_MSG_RETURN_STEP3_HDR#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_STEP3_SUBTEXT#"
			footersubtext=""
			button_1_text=""
			button_1_url=""
			button_1_id=""
			button_2_text=""
			button_2_url=""
			/>
		</div>	
	</div>
	<div id="rn_ReturnPolicy" class="rn_Returns2022">
	<? if($referrer == 'US') { ?>		
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022_Policy rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_RETURN_POLICY_HDR#"
			subtext="#rn:msg:CUSTOM_MSG_RETURN_POLICY_TEXT#"
			footersubtext=""
			button_1_text=""
			button_1_url=""
			button_1_id=""
			button_2_text=""
			button_2_url=""
			/>
	<? } else { ?>
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Returns2022_Policy rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_RETURN_POLICY_HDR#"
			subtext="#rn:msg:CUSTOM_MSG_CANADA_RETURN_POLICY_TEXT#"
			footersubtext=""
			button_1_text=""
			button_1_url=""
			button_1_id=""
			button_2_text=""
			button_2_url=""
			/>
	<? } ?>
	</div>
	<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
    <div class="rn_ContentDetail rn_ReturnsAnswers2022">
        <div class="rn_PageTitle rn_RecordDetail">
	        
	        
        <span class="rn_Summary" itemprop="name"><h1>#rn:msg:CUSTOM_MSG_FAQS_LBL#</h1></span>
	    <div class="rn_AnswerQuestion">
                <rn:field name="Answer.Question" highlight="true"/>
        </div>
        </div>

        <div class="rn_PageContent rn_RecordDetail rn_LeftArea">
            <div id="rn_Returns2022" class="rn_RecordText rn_AnswerText" itemprop="articleBody">
                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true"/></span>
                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true"/></div>
                
        
				<div id="bissellcomURL" style="display:none;"><rn:field name="Answer.CustomFields.c.bissellcom_video_url" highlight="true"/></div>
            </div>
            <rn:widget path="knowledgebase/GuidedAssistant"/>
            <div class="rn_FileAttach">
                <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#"/>
            </div>
			
            
        </div>
    </div>
    <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div>
</article>
<rn:widget path="feedback/AnswerFeedback" dialog_threshold="1" label_dialog_title="#rn:msg:THANKS_FOR_YOUR_FEEDBACK_MSG#" label_dialog_description="#rn:msg:CUSTOM_MSG_TELL_MORE_EXPERIENCE#" label_title="#rn:msg:WAS_THIS_ANSWER_HELPFUL_MSG#"/>
<script>
	$(document).ready(function(){
		
		
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
		  })
	});
</script> 

</div>
</div>