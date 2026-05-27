<rn:meta title="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_LBL#" template="standard.php" clickstream="contact_us"/>

<div class="rn_PageContent rn_Connected">
<div id="rn_HelpfulResources" class="rn_Container rn_Connected2022">
	<div id="rn_HelpfulResourcesHeadline" class="rn_ConnectedProducts">
		<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_CONNECTED_TITLE#</h1>
		<span class="">#rn:msg:CUSTOM_MSG_CONNECTED_HDR_SUBTEXT#</span>
	</div>

	<div id="rn_ConnectedPage" class="rn_Container">

		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_Connected2022 rn_ConnectedApps rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONNECTED_APPS_HEADLINE#"
			subtext=""
			footersubtext=""
			button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_APPS_ANDROID_TXT#"
			button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_APPS_ANDROID_URL#"
			button_1_id="rn_ConnectedAndroid"
			button_2_text="#rn:msg:CUSTOM_MSG_CONNECTED_APPS_IOS_TXT#"
			button_2_url="#rn:msg:CUSTOM_MSG_CONNECTED_APPS_IOS_URL#"
			button_2_id="rn_ConnectedIOS"
			special_headline=""
			/>
	
		<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline rn_ConnectedProductSelector">
		<h3 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_SELECTOR_HDR#</h3>
		</div>
		<? if($referrer == "US") { ?>
			<rn:widget path="navigation/ConnectedProductDisplay" numbered_pagination="false" country_cd="#rn:php:$country_cd#" product_list_config="#rn:msg:CUSTOM_MSG_CONNECTED_HOME_PRODUCTS#"  numbered_pagination="false" prefetch_sub_items="false"/>
		<? } else { ?>
			<rn:widget path="navigation/ConnectedProductDisplay" numbered_pagination="false" country_cd="#rn:php:$country_cd#" product_list_config="#rn:msg:CUSTOM_MSG_CA_CONNECTED_HOME_PRODUCTS#"  numbered_pagination="false" prefetch_sub_items="false"/>
		<? } ?> 
		<div class="rn_AlignRight rn_AllConnectedLinkContainer">
			<a class="rn_AllConnectedLink" href="/app/connected_all" >#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_SHOW_ALL#</a>
		</div>
		<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
		    <div id="rn_ConnectedErrorLights" class="rn_ContentDetail rn_ConnectedErrorLights">
		        <? /* <div class="rn_PageTitle rn_RecordDetail">
			        
			    <div class="rn_AnswerQuestion">
		                <rn:field name="Answer.Question" highlight="true" id="6382"/>
		        </div>
		        </div>*/ ?>
		
		        <? /* <div class="rn_PageContent rn_RecordDetail rn_LeftArea">*/ ?>
				<div class="rn_RecordDetail rn_LeftArea">
		            <div class="rn_RecordText rn_AnswerText" itemprop="articleBody">
		                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true" id="6382" /></span>
		                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true" id="6382"/></div>
		                
		        
						<div id="bissellcomURL" style="display:none;"><rn:field name="Answer.CustomFields.c.bissellcom_video_url" highlight="true" id="6382"/></div>
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
		<? /* HOME PAGE SAMPLE
		<rn:widget path="navigation/VisualProductCategorySelector" numbered_pagination="false" show_sub_items_for="#rn:config:CUSTOM_CFG_US_PRODUCTID#" top_level_items="#rn:config:CUSTOM_CFG_DISPLAY_HOME_PROD_CAT#" numbered_pagination="true" prefetch_sub_items="false"/> */ ?>
		<div id="rn_HelpfulResourcesHeadline" class="rn_ConnectedOtherIssues">
		<h3 class="">#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_OTHER_ISSUES#</h3>
		</div>
		<div id="rn_ConnectedOtherIssues">
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_Connected2022 rn_ConnectedOtherIssues rn_CallToActionBox25"
				image_url="images/icons/bissell/account_troubleshoot.png"
				image_alt="#rn:msg:CUSTOM_MSG_CONNECTED_ACCOUNT_TROUBLESHOOT#"
				headline=""
				subtext=""
				footersubtext=""
				button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_ACCOUNT_TROUBLESHOOT#"
				button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_ACCOUNT_TROUBLESHOOT_URL#"
				button_1_id="rn_ConnectedAccountTroubleshoot"
				button_2_text=""
				button_2_url=""
				/>
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_Connected2022 rn_ConnectedOtherIssues rn_CallToActionBox25"
				image_url="images/icons/bissell/wifi.png"
				image_alt="#rn:msg:CUSTOM_MSG_CONNECTED_WIFI#"
				headline=""
				subtext=""
				footersubtext=""
				button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_WIFI#"
				button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_WIFI_URL#"
				button_1_id="rn_ConnectedAccountTroubleshoot"
				button_2_text=""
				button_2_url=""
				/>
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_Connected2022 rn_ConnectedOtherIssues rn_CallToActionBox25"
				image_url="images/icons/bissell/pairing.png"
				image_alt="#rn:msg:CUSTOM_MSG_CONNECTED_PAIRING#"
				headline=""
				subtext=""
				footersubtext=""
				button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_PAIRING#"
				button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_PAIRING_URL#"
				button_1_id="rn_ConnectedPairing"
				button_2_text=""
				button_2_url=""
				/>
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_Connected2022 rn_ConnectedOtherIssues rn_CallToActionBox25"
				image_url="images/icons/bissell/mapping.png"
				image_alt="#rn:msg:CUSTOM_MSG_CONNECTED_MAPPING#"
				headline=""
				subtext=""
				footersubtext=""
				button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_MAPPING#"
				button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_MAPPING_URL#"
				button_1_id="rn_ConnectedMapping"
				button_2_text=""
				button_2_url=""
				/>
		</div>
			
		<div id="rn_ConnectedHelp">	
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_Connected2022 rn_ConnectedProductHelpLine rn_CallToActionBox100"
				image_url=""
				image_alt=""
				headline="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_LBL#"
				subtext="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_HELP_SUBTEXT#"
				footersubtext="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_HELP_AVAILABILITY#"
				button_1_text="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_PHONE#"
				button_1_url="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_PHONE_URL#"
				button_1_id="rn_ConnectedHelpPhone"
				button_2_text=""
				button_2_url=""
				/>
		</div>
	</div>			
<script>
	function showAnswer(theSection) {
	    //alert(theSection);	
	    answerTitle = "#AnswerTitle_" + theSection + " a";
	    //alert(answerTitle);
	    answerSection = "#AnswerText_" + theSection;
	    //alert(answerSection);
	    if ($(answerSection).hasClass('rn_Hidden'))
	        $(answerSection).removeClass('rn_Hidden');
	    else
	        $(answerSection).addClass('rn_Hidden');
	    
	    $(answerSection).focus();
	}	 
</script> 


</div>