<rn:meta title="#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_TITLE#" template="standard.php" clickstream="home" />
	
	<div class="rn_PageContent rn_Contact2022">
		<div class="rn_TroubleshootingHeader">
			<div class="rn_JustifyCenter"><img src="images/icons/bissell/wrench.svg" height="26" width="25" alt=""/></div>
			<div style="padding-left:10px;">#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_HEADER_TEXT#</div>
		</div>
		<div style="padding-top:15px; display:flex;">#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_SUMMARY_TEXT#</div>
    	
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_SUMMARYONE_TEXT#</div>
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_SUMMARYTWO_TEXT#</div>
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_INSTRUCTIONS_SUMMARYTHREE_TEXT#</div>
		<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
			<rn:condition url_parameter_check="pc == 'ca'">
				<a href="/app/replace-submission-form/pc/ca"><button class="btn btn-primary rn_TroubleshootingCheck buttonFormat" id="btnTTGetStartedBTN">#rn:msg:CUSTOM_MSG_TT_INSTRUCTIONS_CONTINUE_BTN#</button></a>
			<rn:condition_else/>
				<a href="/app/replace-submission-form"><button class="btn btn-primary rn_TroubleshootingCheck buttonFormat" id="btnTTGetStartedBTN">#rn:msg:CUSTOM_MSG_TT_INSTRUCTIONS_CONTINUE_BTN#</button></a>
			</rn:condition>
		</div>
		<div class="rn_TroubleshootingDescriptionSpacing">
			#rn:msg:CUSTOM_MSG_TT_INSTRUCTIONS_SCREFERRAL_TEXT#
		</div>
		<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
			<a class="link-secondary" href="/app/productcheck">< Back</a>
		</div>
</div>