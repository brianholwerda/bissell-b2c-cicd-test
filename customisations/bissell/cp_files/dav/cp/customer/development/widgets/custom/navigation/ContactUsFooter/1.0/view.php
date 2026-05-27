<?php /* Originating Release: November 2021 */?>
<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<? $referrer = $this->data['attrs']['site_referrer']; ?>

<? 
	$customButtonsClassName = "";
	$actionBoxClassName = "";
	if($testint == 1) {
		$customButtonsClassName = "rn_CallToActionCustomButtonsSingle";
		$actionBoxClassName = "rn_CallToActionBoxSingle";
	} elseif ($testint == 2){
		$customButtonsClassName = "rn_CallToActionCustomButtonsDouble";
		$actionBoxClassName = "rn_CallToActionBoxDouble";
	} elseif ($testint == 3){
		$customButtonsClassName = "rn_CallToActionCustomButtonsTriple";
		$actionBoxClassName = "rn_CallToActionBoxTriple";
	}
?>

<? if($referrer == 'US') { ?>
	<div id="rn_MoreContactOptions" class="rn_Container">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsMoreOptions rn_ContactUsButtons rn_ContactUsFooterMoreButtons rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_BUTTONTEXT#"
			button_1_url="/app/contact"
			button_2_text=""
			button_2_url=""
			/>
	</div>	
<? } else { ?>
   <!-- Canada -->
	<div id="rn_MoreContactOptions" class="rn_Container">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsMoreOptions rn_ContactUsButtons rn_ContactUsFooterMoreButtons rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_OTHER_BUTTONTEXT#"
			button_1_url="/app/contact"
			button_2_text=""
			button_2_url=""
			/>
	</div>	
<? } ?>
<rn:condition config_check="CUSTOM:CUSTOM_CFG_CONTACTUSFOOTER_SHOW_SURVEY == 1">
	<div id="rn_ShowSurveyOptions" class="rn_Container">
		<rn:widget path="custom/navigation/CallToActionBox" 
			class_name="rn_ContactUsFooterSurveyButtons rn_CallToActionBox100"
			image_url=""
			image_alt=""
			headline="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SURVEY_HEADLINE#"
			subtext="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SURVEY_SUBTEXT#"
			button_1_text="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SURVEY_BUTTONTEXT#"
			button_1_url="#rn:msg:CUSTOM_MSG_CONTACTUSFOOTER_SURVEY_URL#"
			button_2_text=""
			button_2_url=""
			/>
	</div>	
</rn:condition>
<style>
	.rn_ButtonDisabled {
		background-color: #ececec;
		color: #a0a0a0;
	}
</style>
</div>
