<rn:meta title="#rn:msg:CONTACT_US_LBL#" template="standard.php" clickstream="contact_us"/>

<? $param = "test"; ?>
<? $testint = 0; ?>
<? $displaySMS = false; ?>
<? $displayChat = false; ?>
<? $displayEmail = false; ?>

<rn:condition config_check="CUSTOM:CUSTOM_CFG_CONTACT_SMSBOX_ENABLED == true">
	<? $displaySMS = true; ?>
	<? $testint++; ?>
</rn:condition>
<rn:condition config_check="CUSTOM:CUSTOM_CFG_CONTACT_CHATBOX_ENABLED == true">
	<? $displayChat = true; ?>
	<? $testint++; ?>
</rn:condition>
<rn:condition config_check="CUSTOM:CUSTOM_CFG_CONTACT_EMAILUSBOX_ENABLED == true">
	<? $displayEmail = true; ?>
	<? $testint++; ?>
</rn:condition>

<? 
	$customButtonsClassName = "";
	$actionBoxClassName = "";
	if($testint == 1) {
		$customButtonsClassName = "rn_CallToActionCustomButtons100";
		$actionBoxClassName = "rn_CallToActionBox100";
	} elseif ($testint == 2){
		$customButtonsClassName = "rn_CallToActionCustomButtons50wMargin";
		$actionBoxClassName = "rn_CallToActionBox50wMargin";
	} elseif ($testint == 3){
		$customButtonsClassName = "rn_CallToActionCustomButtons33";
		$actionBoxClassName = "rn_CallToActionBox33";
	}
?>

<div class="rn_PageContent rn_Contact2022">
<div id="rn_HelpfulResources" class="rn_Container">
		<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline">
		<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_CONTACTUS_HDR#</h1>
		<span class="">#rn:msg:CUSTOM_MSG_CONTACT_US_SUBTEXT#</span>
		</div>

<? if($displayChat == true || $displaySMS == true || $displayEmail == true) { ?>
	<div id="rn_ContactUsOptions" class="rn_Container">

		<? if($displayChat == true) { ?>
			<div class="rn_ChatContactUs">
				<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_ContactUs2022 rn_ContactUsChat rn_ContactUsButtons #rn:php:$actionBoxClassName#"
					image_url="images/icons/bissell/chat2022.png"
					image_alt="#rn:msg:CHAT_LBL#"
					headline="#rn:msg:CUSTOM_MSG_CONTACTUS_CHAT_HEADLINE#"
					subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_CHAT_SUBTEXT#"
					footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUS_CHAT_AVAIL#"
					button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_CHAT_AVAIL_BUTTONTEXT#"
					button_1_url="#"
					button_1_id="rn_ChatButton"
					button_2_text=""
					button_2_url=""
					special_headline="#rn:msg:CUSTOM_MSG_CONTACTUS_RECOMMENDED#"
					/>
			</div>
		<? } ?>
		
		<? if($displaySMS == true) { ?>
			<? if($referrer == 'US') { ?>
			<div class="rn_smsContactUs">
				<div class="rn_smsContactUsDesktop">
					<rn:widget path="custom/navigation/CallToActionBox" 
						class_name="rn_ContactUs2022 rn_ContactUsSMS rn_ContactUsButtons  #rn:php:$actionBoxClassName#"
						image_url="images/icons/bissell/sms2022.png"
						image_alt="#rn:msg:CUSTOM_MSG_SMS_LBL#"
						headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_HEADLINE#"
						subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_SUBTEXT#"
						footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_FOOTERSUBTEXT#"
						button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_DESKTOPNUMBER#"
						button_1_url="javascript:;"
						button_2_text=""
						button_2_url=""
						/>
				</div>	
				<div class="rn_smsContactUsMobile">
					<rn:widget path="custom/navigation/CallToActionBox" 
						class_name="rn_ContactUs2022 rn_ContactUsSMS rn_ContactUsButtons  #rn:php:$actionBoxClassName#"
						image_url="images/icons/bissell/sms2022.png"
						image_alt="#rn:msg:CUSTOM_MSG_SMS_LBL#"
						headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_HEADLINE#"
						subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_SUBTEXT#"
						footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_FOOTERSUBTEXT#"
						button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_DESKTOPNUMBER#"
						button_1_url="sms://#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_DESKTOPNUMBER#"
						button_2_text=""
						button_2_url=""
						/>
				</div>
				<? /* <rn:widget path="custom/navigation/CallToActionCustomButtons" 
					class_name="rn_ContactUsSMS rn_ContactUsButtons rn_ContactUsFooterButtons  #rn:php:$customButtonsClassName# #rn:php:$actionBoxClassNameMobile#"
					responsive_class_buttons="div_LargeScreenDisplay"
					responsive_class_bottomtext="div_ChatWindowDisplay"
					image_url="images/icons/bissell/sms2022.png"
					image_alt="SMS"
					headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_HEADLINE#"
					subtext=""
					footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SMSBOX_SUBTEXT#"
					desktoptext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_DESKTOPNUMBER#"
					desktop_span_class="rn_TextDesktopSpan"
					mobiletext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_MOBILENUMBER#"
					mobile_span_class="rn_TextMobileSpan"
					/> */ ?>
			</div>
			<? } else { ?>
			<div class="rn_smsContactUs">
				<div class="rn_smsContactUsDesktop">
				   <!-- Canada -->
					<rn:widget path="custom/navigation/CallToActionBox" 
						class_name="rn_ContactUs2022 rn_ContactUsSMS rn_ContactUsButtons #rn:php:$actionBoxClassName#"
						image_url="images/icons/bissell/sms2022.png"
						image_alt="#rn:msg:CUSTOM_MSG_SMS_LBL#"
						headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_HEADLINE#"
						subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_SUBTEXT#"
						footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_FOOTERSUBTEXT#"
						button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_DESKTOPNUMBER#"
						button_1_url="javascript:;"
						button_2_text=""
						button_2_url=""
						/>
				</div>
				<div class="rn_smsContactUsMobile">
				   <!-- Canada -->
					<rn:widget path="custom/navigation/CallToActionBox" 
						class_name="rn_ContactUs2022 rn_ContactUsSMS rn_ContactUsButtons #rn:php:$actionBoxClassName#"
						image_url="images/icons/bissell/sms2022.png"
						image_alt="#rn:msg:CUSTOM_MSG_SMS_LBL#"
						headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_HEADLINE#"
						subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_SUBTEXT#"
						footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMS_FOOTERSUBTEXT#"
						button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_DESKTOPNUMBER#"
						button_1_url="sms://#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_DESKTOPNUMBER#"
						button_2_text=""
						button_2_url=""
						/>
				</div>	
				<? /* <rn:widget path="custom/navigation/CallToActionCustomButtons" 
					class_name="rn_ContactUsSMS rn_ContactUsButtons rn_ContactUsFooterButtons #rn:php:$customButtonsClassName#"
					responsive_class_buttons="div_LargeScreenDisplay"
					responsive_class_bottomtext="div_ChatWindowDisplay"
					image_url="images/icons/bissell/sms2022.png"
					image_alt="SMS"
					headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_HEADLINE#"
					subtext=""
					footersubtext="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SMSBOX_SUBTEXT#"
					desktoptext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_DESKTOPNUMBER#"
					desktop_span_class="rn_TextDesktopSpan"
					mobiletext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_MOBILENUMBER#"
					mobile_span_class="rn_TextMobileSpan"
					/> */ ?>
			</div>
			<? } ?>
		<? } ?>
		
		
		
		<? if($displayEmail == true) { ?>
		<div class="rn_emailContactUs">
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_ContactUs2022 rn_ContactUsEmail rn_ContactUsButtons #rn:php:$actionBoxClassName#"
				image_url="images/icons/bissell/email2022.png"
				image_alt="#rn:msg:EMAIL_LBL#"
				headline="#rn:msg:CUSTOM_MSG_CONTACT_US_EMAIL_HEADLINE#"
				subtext="#rn:msg:CUSTOM_MSG_CONTACT_US_EMAIL_SUBTEXT#"
				footersubtext="#rn:msg:CUSTOM_MSG_CONTACT_US_EMAIL_FOOTERSUBTEXT#"
				button_1_text="#rn:msg:CUSTOM_MSG_CONTACT_US_EMAIL_BUTTONTEXT#"
				button_1_url="/app/ask"
				button_2_text=""
				button_2_url=""
				/>	
		</div>
		<? } ?>
	</div>
<? } ?>
<div class="rn_ContactUsNotesBoldMsg">
	#rn:msg:CUSTOM_MSG_CONTACT_US_CLICK_CHAT#
</div>
<div class="rn_ContactUsNotesBoldMsg">
	#rn:msg:CUSTOM_MSG_CONTACT_US_CHAT_NOT_AVAILABLE#
</div>
<div class="rn_LocateModelMsg">
	#rn:msg:CUSTOM_MSG_CONTACT_US_LOCATE_MODEL#
</div>
<div class="rn_ContactUsOtherText">
	<div class="rn_ContactUsTextLeft">
		<div class="rn_GeneralProduct">
		<? if($referrer == 'US') { ?>
			#rn:msg:CUSTOM_MSG_CONTACT_US_GENERAL_PRODUCT#
		<? } else { ?>
			#rn:msg:CUSTOM_MSG_CONTACT_US_CANADA_GENERAL_PRODUCT#
		<? } ?>
		</div>
		<div class="rn_ConnectedProduct">
			#rn:msg:CUSTOM_MSG_CONTACT_US_CONNECTED_PRODUCT#
		</div>
	</div>
	<div class="rn_ContactUsTextRight">
		<div class="rn_Headquarters">
			#rn:msg:CUSTOM_MSG_CONTACT_US_HEADQUARTERS#
		</div>
		<div class="rn_ByMail">
			#rn:msg:CUSTOM_MSG_CONTACT_US_BYMAIL#
		</div>
		<div class="rn_International">
			#rn:msg:CUSTOM_MSG_CONTACT_US_INTL#
		</div>
	</div>
</div>
<div class="rn_ContactUsNotesBoldMsg">
	#rn:msg:CUSTOM_MSG_CONTACT_US_NO_CHAT_EMAIL_US#
</div>
</div>

<style>
	.rn_ButtonDisabled {
		background-color: #ececec;
		color: #a0a0a0;
	}
</style>