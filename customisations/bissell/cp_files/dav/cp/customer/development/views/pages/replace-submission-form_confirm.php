<rn:meta title="#rn:msg:QUESTION_SUBMITTED_LBL#" template="standard.php" clickstream="incident_confirm"/>
<div class="rn_PageContent">
	<div class="rn_Hero">
		<div style="color:black;">
			<h1>#rn:msg:CUSTOM_MSG_TT_REPLACE_CONFIRM_HEADER_LBL#</h1>
		</div>
	</div>
	<div>
		<div class="rn_TroubleshootingDivImportant">
			<div class="rn_JustifyCenterMobile">
				<i class="fa fa-solid fa-check-circle" style="padding-right:10px; color:#09A657;"></i>
				<span class="rn_TroubleshootingSpanSuccess">#rn:msg:CUSTOM_MSG_TT_REPLACE_SUCCESS_TEXT#</span>
			</div>
			<div class="rn_ConfirmationHeaderSpan">
				<span>#rn:msg:CUSTOM_MSG_TT_REPLACE_RECVD_TEXT#</span>
			</div>
		</div>
		<hr/>
		<div>
			#rn:msg:CUSTOM_MSG_TT_REPLACE_INCNUM_LBL# #rn:url_param_value:refno#
		</div>
		<div id="divModelNumber" class="rn_Hidden">
			<span id="spanModelNumber"></span>
		</div>
		<div id="divModelName" class="rn_Hidden">
			<span id="spanModelName"></span>
		</div>
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_CONFIRM_REQUEST_SUMMARY_TEXT#</div>
		<div class="rn_AnswerPreview rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_CONFIRM_SUMMARY_LISTOPTIONS#</div>
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_CONFIRM_OVERVIEW_TEXT#</div>
		<div class="rn_TroubleshootingDescriptionSpacing">#rn:msg:CUSTOM_MSG_TT_REPLACE_CONFIRM_NEXTSTEPS_TEXT#</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		var prodDesc = sessionStorage.getItem('TTProdDescription');
		var modNumber = sessionStorage.getItem('TTModelNumber');
		sessionStorage.removeItem('TTProdDescription');
		sessionStorage.removeItem('TTWarrDescription');
		sessionStorage.removeItem('TTModelNumber');
		sessionStorage.removeItem('TTSerialNumber');
		sessionStorage.removeItem('TTPostalJSON');
		
		if(prodDesc && modNumber){
			//var summaryHTML = '#rn:msg:CUSTOM_MSG_TT_OUT_WARRANTY_PRODWARR_SUMMARY_TEXT#';
			//summaryHTML = summaryHTML.replace('<insertProdDescription>', prodDesc).replace('<insertProdWarranty>', warrDesc);
			$("#spanModelNumber").html("<b>Model Number:</b> " + modNumber);
			$("#spanModelName").html("<b>Model Name:</b> " + prodDesc);
			$("#divModelNumber").removeClass('rn_Hidden');
			$("#divModelName").removeClass('rn_Hidden');
		}
	});
</script>