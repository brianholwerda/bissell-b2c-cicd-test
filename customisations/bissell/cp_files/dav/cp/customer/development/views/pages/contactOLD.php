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

<div id="rn_HelpfulResources" class="rn_Container">
		<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline">
		<h2 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_HELPFUL_RESOURCES_HDG#</h2>
		<span class="">#rn:msg:CUSTOM_MSG_HELPFUL_RESOURCES_SUBTEXT#</span>
		</div>
		<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_ServiceCenterLocator rn_CallToActionBox100"
				image_url="images/icons/bissell/service_locator_white-01.png"
				image_alt="#rn:msg:CUSTOM_MSG_SVC_CENTER_HDG#"
				headline="#rn:msg:CUSTOM_MSG_SVC_CENTER_HDG#"
				subtext="#rn:msg:CUSTOM_MSG_SVC_CENTER_SUBTEXT#"
				button_1_text="#rn:msg:CUSTOM_MSG_SVC_CENTER_BTN#"
				button_1_url="#rn:php:$environment#/support/product-support/service-centers"
				button_2_text=""
				button_2_url=""
				/>
    	</div>

<div id="rn_ContactUsOptions" class="rn_Container">

    <? if($displaySMS == true) { ?>
		<? if($referrer == 'US') { ?>
			<rn:widget path="custom/navigation/CallToActionCustomButtons" 
				class_name="rn_ContactUsSMS rn_ContactUsButtons #rn:php:$customButtonsClassName#"
				responsive_class_buttons="div_LargeScreenDisplay"
				responsive_class_bottomtext="div_ChatWindowDisplay"
				image_url="images/icons/bissell/contact_text.png"
				image_alt="SMS"
				headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_HEADLINE#"
				subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_SUBTEXT#"
				desktoptext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_DESKTOPNUMBER#"
				desktop_span_class="rn_TextDesktopSpan"
				mobiletext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_US_MOBILENUMBER#"
				mobile_span_class="rn_TextMobileSpan"
				/>
		<? } else { ?>
		   <!-- Canada -->
			<rn:widget path="custom/navigation/CallToActionCustomButtons" 
				class_name="rn_ContactUsSMS rn_ContactUsButtons #rn:php:$customButtonsClassName#"
				responsive_class_buttons="div_LargeScreenDisplay"
				responsive_class_bottomtext="div_ChatWindowDisplay"
				image_url="images/icons/bissell/contact_text.png"
				image_alt="SMS"
				headline="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_HEADLINE#"
				subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_SUBTEXT#"
				desktoptext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_DESKTOPNUMBER#"
				desktop_span_class="rn_TextDesktopSpan"
				mobiletext="#rn:msg:CUSTOM_MSG_CONTACTUS_SMSBOX_CA_MOBILENUMBER#"
				mobile_span_class="rn_TextMobileSpan"
				/>
		<? } ?>
	<? } ?>

	<? if($displayChat == true) { ?>
		<div class="desktop-only">
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_ContactUsChat rn_ContactUsButtons #rn:php:$actionBoxClassName#"
				image_url="images/icons/bissell/contact_chat.png"
				image_alt="Chat"
				headline="#rn:msg:CUSTOM_MSG_CONTACT_CHAT_HEADLINE#"
				subtext="#rn:msg:CUSTOM_MSG_CONTACT_CHAT_AVAIL#"
				button_1_text="#rn:msg:CUSTOM_MSG_CONTACT_CHAT_BUTTONTEXT#"
				button_1_url="#"
				button_1_id="rn_ChatButton"
				button_2_text=""
				button_2_url=""
				/>
		</div>
	<? } ?>
	
	<? if($displayEmail == true) { ?>
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsEmail rn_ContactUsButtons #rn:php:$actionBoxClassName#"
			image_url="images/icons/bissell/contact_email.png"
			image_alt="Email"
			headline="#rn:msg:CUSTOM_MSG_CONTACTUS_EMAIL_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_EMAIL_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_EMAIL_BUTTONTEXT#"
			button_1_url="/app/ask"
			button_2_text=""
			button_2_url=""
			/>
		</div>
	<? } ?>

<? if($referrer == 'US') { ?>
	<div id="rn_MoreContactOptions" class="rn_Container">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsMoreOptions rn_ContactUsButtons rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_BUTTONTEXT#"
			button_1_url="/app/answers/detail/a_id/1360"
			button_2_text=""
			button_2_url=""
			/>
	</div>	
<? } else { ?>
   <!-- Canada -->
	<div id="rn_MoreContactOptions" class="rn_Container">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsMoreOptions rn_ContactUsButtons rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUS_OTHER_BUTTONTEXT#"
			button_1_url="/app/answers/detail/a_id/2555"
			button_2_text=""
			button_2_url=""
			/>
	</div>	
<? } ?>
<style>
	.rn_ButtonDisabled {
		background-color: #ececec;
		color: #a0a0a0;
	}
</style>
<script>
    $(document).ready(function(){
		$.ajax({ 
	        type: 'GET', 
	        url: 'https://prod-live-chat.sprinklr.com/api/livechat/handshake/check-visibility/5fac3f2d2115df5952a2e1e9_app_622222', 
	        data: { get_param: 'value' }, 
	        success: function (data) { 
				var chat_available = data;
	            if(chat_available == false) {
	            	$(".rn_ContactUsChat .rn_CallToActionContainer .rn_CallToActionDesc .rn_CallToActionSubText:first").text("#rn:msg:CUSTOM_MSG_CONTACT_CHAT_UNAVAIL#");
	            	$(".rn_CallToActionButton1.rn_ContactUsChat:first").css('visibility','hidden');
					$(".rn_CallToActionCustomButtons.rn_ContactUsSMS").css('display','none');
					$(".rn_ContactUsSMS.rn_ContactUsButtons.rn_TextDesktopSpan:first").css('visibility','hidden');
					$(".rn_ContactUsSMS.rn_ContactUsButtons.rn_TextMobileSpan:first").css('visibility','hidden');
					$(".rn_CallToActionBox.rn_ContactUsChat").css('display','none');
					$(".rn_CallToActionBox.rn_ContactUsEmail").removeClass('rn_CallToActionBox33');
					$(".rn_CallToActionBox.rn_ContactUsEmail").removeClass('rn_CallToActionBox50wMargin');
					$(".rn_CallToActionBox.rn_ContactUsEmail").addClass('rn_CallToActionBox100');
	            }
	        }
	    });
		$('a.rn_ChatButton').click(function(){
			window.sprChat('open');
		});
	});
</script>