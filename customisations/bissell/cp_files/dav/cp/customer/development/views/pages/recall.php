<rn:meta title="#rn:msg:CUSTOM_MSG_RECALL_FORM_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">
	<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
		    <div class="rn_ContentDetail rn_RecallAnswers2022">
		        <div class="rn_PageTitle rn_RecordDetail">
					<div class="rn_AnswerQuestion">
						<rn:field name="Answer.Question" highlight="true" id="6934"/>
					</div>
		        </div>
				<div class="rn_PageContent rn_RecordDetail rn_LeftArea" style="padding-bottom:0px;">
		            <div id="rn_Returns2022" class="rn_RecordText rn_AnswerText rn_RecallAnswers2022" itemprop="articleBody">
		                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true" id="6934" /></span>
		            </div>
		            <rn:widget path="knowledgebase/GuidedAssistant"/>
		        </div>
		    </div>
		</article>
	<rn:widget path="custom/input/RecallCheck" />
	<div id="divAlertMessage" class="alertMessage padding rn_Hidden"></div>
	<div id="overlay" class="overlay" style="display:none;">
		<div class="overlay__wrapper">
		  <div class="overlay__spinner">
			<span id="spanOverlayText" style="font-size: 20px; color: blue; font-weight: bold;"></span>
			<br>
			<div class="spinner-border text-primary" role="status">
			  <span class="sr-only">Loading...</span>
			</div>
		  </div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: rgba(211,211,211,.95);
    z-index: 9999;
  }

  .overlay__wrapper {
    width: 100%;
    height: 100%;
    position: relative;
  }

  .overlay__spinner {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, 0%);
    text-align: center;
  }
</style>

<script>
$("#btnRecallButton").click(function(){
	if($('#txtModelNumber').val()){
		$("#btnRecallButton").prop('disabled', true);
		$("#spanOverlayText").text('Searching Product Information');
		$("#overlay").show();
		checkRecall();
	} else {
		alert('Must Enter Model Number');
	}
})

$('#txtModelNumber').keypress(function(e){
	if(e.which == 13){
		if($('#txtModelNumber').val()){
			$("#btnRecallButton").prop('disabled', true);
			$("#spanOverlayText").text('Searching Product Information');
			$("#overlay").show();
			checkRecall();
		} else {
			alert('Must Enter Model Number');
		}
	}
});

const delay = ms => new Promise(res => setTimeout(res, ms));
		
const delayRedirectText = async (t) => {
  await delay(5000);
	$("#divAlertMessage").html(t);
	$("#overlay").hide();
	$("#spanOverlayText").text('');
	$("#btnTroubleshootingButton").prop('disabled', false);
};

function checkRecall(){
	if($("#txtModelNumber").val()){
		$.ajax({
			type: "POST",
			url: "/cc/AjaxCustom/searchRecall",
			data: {modelNumber: $("#txtModelNumber").val()},
			success: function(data){
				data = JSON.parse(data);
				if(data.IsValidModel){
					if(data.ModelHasRecall){
						$("#divAlertMessage").removeClass('alertMessageError');
						$("#divAlertMessage").removeClass('alertMessageSuccess');
						$("#divAlertMessage").removeClass('rn_Hidden');
						$("#divAlertMessage").addClass('alertActiveRecall');
						
						if(data.RecallID == 1){
							location.href = window.location.origin + '/app/crosswave-cordless-safety-recall';
							delayRedirectText("#rn:msg:CUSTOM_MSG_RECALL_CROSSWAVE_PAGE_LINK#");
						}
						if(data.RecallID == 291){
							location.href = window.location.origin + '/app/multireach-safety-recall';
							delayRedirectText("#rn:msg:CUSTOM_MSG_RECALL_MULTIREACH_PAGE_LINK#");
						}
						if(data.RecallID == 292){
							//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_RECALL_STEAMSHOT_PAGE_LINK#");
							location.href = window.location.origin + '/app/steamshot-safety-recall';
							delayRedirectText("#rn:msg:CUSTOM_MSG_RECALL_STEAMSHOT_PAGE_LINK#");
						}
					} else {
						$("#overlay").hide();
						$("#spanOverlayText").text('');
						$("#btnRecallButton").prop('disabled', false);
						$("#divAlertMessage").removeClass('alertMessageError');
						$("#divAlertMessage").removeClass('alertActiveRecall');
						$("#divAlertMessage").removeClass('rn_Hidden');
						$("#divAlertMessage").addClass('alertMessageSuccess');
						$("#divAlertMessage").text("#rn:msg:CUSTOM_MSG_RECALL_NOACTIVERECALLS_ALERT_TEXT#");
					}
				} else {
					$("#overlay").hide();
					$("#spanOverlayText").text('');
					$("#btnRecallButton").prop('disabled', false);
					$("#divAlertMessage").removeClass('alertMessageSuccess');
					$("#divAlertMessage").removeClass('alertActiveRecall');
					$("#divAlertMessage").removeClass('rn_Hidden');
					$("#divAlertMessage").addClass('alertMessageError');
					$("#divAlertMessage").text("#rn:msg:CUSTOM_MSG_RECALL_INVALIDMODELNUMBER_TEXT#");
				}
			},
			error: function(error){
				console.log(error);
			}
		});
	} else {
		alert("#rn:msg:CUSTOM_MSG_RECALL_ENTERMODELNUMBER_ALERT#");
	}
}
</script>
<style>
.alertMessageSuccess {
	font-size: large;
	color: green;
}

.alertMessageError {
	font-size: large;
	color: red;
}

.alertActiveRecall {
	font-size: large;
	color: #0074B0;
}

.padding {
	padding: 10px 32px 15px 32px;
}

@media all and (max-width : 768px) {
	.alertMessage {
		text-align:center;
	}
}
</style>