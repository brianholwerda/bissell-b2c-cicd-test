<rn:meta title="#rn:msg:CUSTOM_MSG_TT_OOW_INSTRUCTIONS_TITLE#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<div class="rn_TroubleshootingHeader">
		<div class="rn_JustifyCenter"><img src="images/icons/bissell/wrench.svg" height="26" width="25" alt=""/></div>
		<div style="padding-left:10px;">#rn:msg:CUSTOM_MSG_TT_OOW_INSTRUCTIONS_HEADER_TEXT#</div>
	</div>
	<div style="margin-top:10px;">
		<div><span id="spanWarrantySummary" class="rn_TroubleshootingOutWarrantyDescription rn_Hidden" style="margin-bottom:10px;"></span></div>
	</div>
	<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_OOW_POP_DESCRIPTION_TEXTONE#</div>
	<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_OOW_POP_DESCRIPTION_TEXTTWO#</div>
	<div class="rn_TroubleshootingDescriptionSpacing rn_PoPYesNoButtons">
		<div class="form-item rn_TroubleshootingCheckBTN rn_PoPYesNoButtons">
			<rn:condition url_parameter_check="pc == 'ca'">
				<a href="/app/oow-submission-form/pc/ca"><button class="btn btn-primary rn_TroubleshootingCheckBTN buttonFormat" id="btnTTHasPoP">#rn:msg:CUSTOM_MSG_TT_OOW_POP_YESBTN_TEXT#</button></a>
			<rn:condition_else/>
				<a href="/app/oow-submission-form"><button class="btn btn-primary rn_TroubleshootingCheckBTN buttonFormat" id="btnTTHasPoP">#rn:msg:CUSTOM_MSG_TT_OOW_POP_YESBTN_TEXT#</button></a>
			</rn:condition>
		</div>
		<div class="form-item rn_TroubleshootingCheckBTN rn_PoPYesNoButtons">
			<rn:condition url_parameter_check="pc == 'ca'">
				<a href="/app/oow-submission-form/pc/ca/pop/no"><button class="btn btn-primary rn_TroubleshootingCheckBTN buttonFormat" id="btnTTNoPoP">#rn:msg:CUSTOM_MSG_TT_OOW_POP_NOBTN_TEXT#</button></a>
			<rn:condition_else/>
				<a href="/app/oow-submission-form/pop/no"><button class="btn btn-primary rn_TroubleshootingCheckBTN buttonFormat" id="btnTTNoPoP">#rn:msg:CUSTOM_MSG_TT_OOW_POP_NOBTN_TEXT#</button></a>
			</rn:condition>
		</div>
	</div>
	<div class="rn_TroubleshootingDescriptionSpacing rn_TroubleshootingCheckBTN">
		<a class="link-secondary" href="/app/productcheck">< Back</a>
	</div>
</div>

<script>
	$(document).ready(function() {
		var prodDesc = sessionStorage.getItem('TTProdDescription');
		var warrDesc = sessionStorage.getItem('TTWarrDescription');
		// sessionStorage.removeItem('TTProdDescription');
		// sessionStorage.removeItem('TTWarrDescription');
		
		if(prodDesc && warrDesc){
			var summaryHTML = '#rn:msg:CUSTOM_MSG_TT_OUT_WARRANTY_PRODWARR_SUMMARY_TEXT#';
			summaryHTML = summaryHTML.replace('<insertProdDescription>', prodDesc).replace('<insertProdWarranty>', warrDesc);
			$("#spanWarrantySummary").html(summaryHTML);
			$("#spanWarrantySummary").removeClass('rn_Hidden');
		}
	});
</script>