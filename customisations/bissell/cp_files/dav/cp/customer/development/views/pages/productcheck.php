<rn:meta title="#rn:msg:CUSTOM_MSG_TT_LOOKUP_HDG#" template="standard.php" clickstream="incident_create"/>
<div class="rn_PageContent">

<div id="rn_HelpfulResources" class="rn_HomeContainer">
			<div class="rn_HomeContainer rn_TouchlessTroubleshooting">
				<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_CallToActionBox100 rn_TouchlessTroubleshooting"
					image_url=""
					image_alt=""
					headline="#rn:msg:CUSTOM_MSG_TT_LOOKUP_HEADLINE#"
					subtext="#rn:msg:CUSTOM_MSG_TT_LOOKUP_SUBTEXT#"
					button_1_text=""
					button_1_url=""
					button_2_text=""
					button_2_url=""
					/>
    		</div>		
    	</div>

	
	<div class="create-account  email-customer-care-form">
		<div class="form-item">
			<label class="label-2">#rn:msg:CUSTOM_MSG_TT_MODELNUM_LBL#</label>
			<a href='#' class='open-dialog'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>
			<input class="textInputFormat" type="text" placeholder="#rn:msg:CUSTOM_MSG_TT_MODELNUM_PH_TEXT#" id="txtModelNumber">
			<div id="divModelNumberError" role="alert" class="rn_ErrorMessage rn_Hidden"></div>
		</div>
		<div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
			<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> 
			<img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" >
			</button>
			#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
		</div>	 
								
		<div class="form-item">
			<label class="label-2">#rn:msg:CUSTOM_MSG_TT_SERIALNUM_LBL#</label>
			<a href='#' class='open-dialog2'><i class='fa fa-solid fa-question-circle' style='color:#3B6DB1; padding-left:7px; font-size:18px;'></i></a>
			<input class="textInputFormat" type="text" placeholder="#rn:msg:CUSTOM_MSG_TT_SERIALNUM_PH_TEXT#" id="txtSerialNumber">
			<div id="divSerialNumberError" role="alert" class="rn_ErrorMessage rn_Hidden"></div>
		</div>
		<div class="dialog2" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
			<button class="close-button" type="button" aria-label="Close Navigation" class="close-dialog2"> 
			<img id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" alt=" ">
			</button>
			#rn:msg:CUSTOM_MSG_FIND_SERIAL_HTML#
		</div>
		<div class="form-item">
			<label class="label-2">#rn:msg:CUSTOM_MSG_TT_POSTALCODE_LBL#</label>
			<input class="textInputFormat" type="text" placeholder="#rn:msg:CUSTOM_MSG_TT_POSTALCODE_PH_TEXT#" id="txtZipCode">
			<div id="divPostalCodeError" role="alert" class="rn_ErrorMessage rn_Hidden"></div>
		</div>
		<div class="form-item rn_TroubleshootingCheckBTN rn_TroubleshootingDescriptionSpacing">
			<button class="btn btn-primary rn_TroubleshootingCheckBTN" id="btnTroubleshootingButton">#rn:msg:CUSTOM_MSG_TT_LOOKUP_BTN#</button>
		</div>
	</div>
	
	<div id="divAlertMessage" class="alertMessage padding rn_Hidden"></div>
	<!--<div id="divWCLabel" class="alertMessage rn_Hidden"><span style="font-weight:bold;">Warranty Calculator Response (Debugging Purposes Only)</span></div>
	<div id="divSerialNumberLookup" class="alertMessage rn_Hidden"></div>
	<div id="divAVLabel" class="alertMessage rn_Hidden" style="padding-top:15px;"><span style="font-weight:bold;">Postal Code Lookup Response (Debugging Purposes Only)</span></div>
	<div id="divPostalCodeLookup" class="alertMessage rn_Hidden"></div>-->
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

<div class="dialog-overlay" tabindex="-1"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
    crossorigin="anonymous"></script>
	
	<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">-->

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">

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

	var navDialogEl = document.querySelector('.dialog');
	var navDialogEl2 = document.querySelector('.dialog2');
	var dialogOverlay = document.querySelector('.dialog-overlay');
	
	var myDialog = new Dialog(navDialogEl, dialogOverlay);
	myDialog.addEventListeners('.open-dialog', '.close-dialog');
	
	var myDialog2 = new Dialog(navDialogEl2, dialogOverlay);
	myDialog2.addEventListeners('.open-dialog2', '.close-dialog2');
	
	// var defWarrantyCalc = $.Deferred();
	// var defAddressVal = $.Deferred();
	
	$(document).ready(function() {
		var serialNumber = sessionStorage.getItem('TTSerialNumber');
		var modelNumber = sessionStorage.getItem('TTModelNumber');
		var postalCode = sessionStorage.getItem('TTPostalJSON');
		
		if(modelNumber){
			$("#txtModelNumber").val(modelNumber);
		}

		if(serialNumber){
			$("#txtSerialNumber").val(serialNumber);
		}

		if(postalCode){
			postalCode = JSON.parse(postalCode);
			$("#txtZipCode").val(postalCode.PostalCode);
		}
		
		$("#txtModelNumber").change(function(){
			$(this).removeClass('input-validation-error');
			$("#divModelNumberError").html('');
			$("#divModelNumberError").addClass('rn_Hidden');
		});
		$("#txtSerialNumber").change(function(){
			$(this).removeClass('input-validation-error');
			$("#divSerialNumberError").html('');
			$("#divSerialNumberError").addClass('rn_Hidden');
		});
		$("#txtZipCode").change(function(){
			$(this).removeClass('input-validation-error');
			$("#divPostalCodeError").html('');
			$("#divPostalCodeError").addClass('rn_Hidden');
		});
		
		$("#btnTroubleshootingButton").click(function(){
			var isValid = true;
			var usPostal = false;
			var caPostal = false;
			
			if(!$("#txtModelNumber").val()){
				//$("#txtModelNumber").addClass('input-validation-error');
				$("#divModelNumberError").html('<span>Model Number Mandatory</span>');
				$("#divModelNumberError").removeClass('rn_Hidden');
				isValid = false;
			}
			if(!$("#txtSerialNumber").val()){
				//$("#txtSerialNumber").addClass('input-validation-error');
				$("#divSerialNumberError").html('<span>Serial Number Mandatory</span>');
				$("#divSerialNumberError").removeClass('rn_Hidden');
				isValid = false;
			}
			if(!$("#txtZipCode").val()){
				//$("#txtZipCode").addClass('input-validation-error');
				$("#divPostalCodeError").html('<span>Postal Code Mandatory</span>');
				$("#divPostalCodeError").removeClass('rn_Hidden');
				isValid = false;
			} else {
				var isValidUSZip = /(^\d{5}$)|(^\d{5}-\d{4}$)/.test($("#txtZipCode").val());
				
				if(!isValidUSZip){
					var ca = new RegExp(/([ABCEGHJKLMNPRSTVXY]\d)([ABCEGHJKLMNPRSTVWXYZ]\d){2}/i);
					var isValidCAZip = ca.test($("#txtZipCode").val().replace(/\W+/g, ''))
					
					if(!isValidCAZip){
						//$("#txtZipCode").addClass('input-validation-error');
						$("#divPostalCodeError").html('<span>Invalid Postal Code Format</span>');
						$("#divPostalCodeError").removeClass('rn_Hidden');
						isValid = false;
					} else {
						caPostal = true;
					}
				} else {
					usPostal = true;
				}
			}
			
			
			
			if(isValid){
				$("#btnTroubleshootingButton").prop('disabled', true);
				$("#spanOverlayText").text('Searching Product Information');
				$("#overlay").show();
				if(usPostal){
					$.when(checkTroubleshooting(), checkPostalCode()).done(function (wc, av) {
						//var url = window.location.href;
						let wcresponse = wc[0];
						let avresponse = av[0];
						
						buildResults(wcresponse, avresponse.ResponseCode, avresponse.IsCONUS, "US");
						
						// $('#divPostalCodeLookup').text("Response Code: " + avresponse.ResponseCode + ", Response Message: " + avresponse.ResponseMessage + ", State Code: " + avresponse.StateCode + ", Is Continental US: " + avresponse.IsCONUS);
						// $('#divPostalCodeLookup').removeClass('rn_Hidden');
						// $('#divAVLabel').removeClass('rn_Hidden');
					});
				}
				
				if(caPostal){
					$.when(checkTroubleshooting()).done(function (wc) {
						
						//let wcresponse = wc[0];
						//console.log(wc);
						
						buildResults(wc, "Success", false, "CA");
						
						// $('#divPostalCodeLookup').text("CA Postal Code Detected. No Postal Lookup Completed.");
						// $('#divPostalCodeLookup').removeClass('rn_Hidden');
						// $('#divAVLabel').removeClass('rn_Hidden');
					});
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
		
		function buildResults(wcresponse, avResponseCode, avIsCONUS, avCountry){
			//var url = window.location.href;
			sessionStorage.setItem('TTModelNumber', $("#txtModelNumber").val().toUpperCase());
			sessionStorage.setItem('TTSerialNumber', $("#txtSerialNumber").val().toUpperCase());
			
			if(wcresponse.ProductDescription){
				sessionStorage.setItem('TTProdDescription', wcresponse.ProductDescription);
			}
			
			if(wcresponse.WarrantyDescription){
				sessionStorage.setItem('TTWarrDescription', wcresponse.WarrantyDescription);
			}
			
			var postalData = $("#txtZipCode").val().toUpperCase();
			
			if(postalData.length == 6 && avCountry == "CA"){
				postalData = postalData.substring(0, postalData.length - 3) + ' ' + postalData.substring(postalData.length - 3);
			}
			
			var avJSONData = {"PostalCode":postalData,"PostalCountry":avCountry};
			sessionStorage.setItem('TTPostalJSON', JSON.stringify(avJSONData));
			
			if(wcresponse.ResponseCode == "Success" && avResponseCode == "Success" && avIsCONUS && wcresponse.InWarranty){
				//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPAIR_LINK#");
				location.href = window.location.origin + '/app/repairinstructions';
				delayRedirectText("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPAIR_LINK#");
			} else if(wcresponse.ResponseCode == "Success" && avResponseCode == "Success" && !wcresponse.InWarranty && avCountry == "CA"){
				//sessionStorage.setItem('TTProdDescription', wcresponse.ProductDescription);
				//sessionStorage.setItem('TTWarrDescription', wcresponse.WarrantyDescription);
				//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_OUTOFWARRANTY_LINK#");
				location.href = window.location.origin + '/app/oowpopcheck/pc/ca';
				delayRedirectText("#rn:msg:CUSTOM_MSG_TT_OUTOFWARRANTY_CA_LINK#");
			} else if(wcresponse.ResponseCode == "Success" && avResponseCode == "Success" && !wcresponse.InWarranty){
				//sessionStorage.setItem('TTProdDescription', wcresponse.ProductDescription);
				//sessionStorage.setItem('TTWarrDescription', wcresponse.WarrantyDescription);
				//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_OUTOFWARRANTY_LINK#");
				location.href = window.location.origin + '/app/oowpopcheck';
				delayRedirectText("#rn:msg:CUSTOM_MSG_TT_OUTOFWARRANTY_LINK#");
			} else if(wcresponse.ResponseCode == "Success" && avResponseCode == "Success" && avCountry == "CA"){
				//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPLACE_LINK#");
				location.href = window.location.origin + '/app/replaceinstructions/pc/ca';
				delayRedirectText("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPLACE_CA_LINK#");
			} else if(wcresponse.ResponseCode == "Success" && avResponseCode == "Success" && !avIsCONUS){
				//$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPLACE_LINK#");
				location.href = window.location.origin + '/app/replaceinstructions';
				delayRedirectText("#rn:msg:CUSTOM_MSG_TT_INWARRANTYREPLACE_LINK#");
			} else if(wcresponse.ResponseCode == "Error" && !wcresponse.IsValidModelNumber){
				$("#overlay").hide();
				$("#spanOverlayText").text('');
				$("#btnTroubleshootingButton").prop('disabled', false);
				$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INVALIDMODEL_TEXT#");
			} else if(wcresponse.ResponseCode == "Error" && wcresponse.IsValidModelNumber){
				$("#overlay").hide();
				$("#spanOverlayText").text('');
				$("#btnTroubleshootingButton").prop('disabled', false);
				$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INVALIDSERIAL_TEXT#");
			} else if(avResponseCode == "Error"){
				$("#overlay").hide();
				$("#spanOverlayText").text('');
				$("#btnTroubleshootingButton").prop('disabled', false);				
				$("#divAlertMessage").html("#rn:msg:CUSTOM_MSG_TT_INVALIDPOSTAL_TEXT#");
			} else {
				$("#overlay").hide();
				$("#spanOverlayText").text('');
				$("#btnTroubleshootingButton").prop('disabled', false);
				$("#divAlertMessage").html("General Error. Please notify Brian so this can be noted as an undocumented error.");
			}
			
			// $('#divSerialNumberLookup').text("Response Code: " + wcresponse.ResponseCode + ", Response Message: " + wcresponse.ResponseMessage + ", Warranty Expiration Date: " + wcresponse.WarrantyExpirationDate + ", In Warranty: " + wcresponse.InWarranty + ", Is Valid Model Number: " + wcresponse.IsValidModelNumber);
			// $('#divSerialNumberLookup').removeClass('rn_Hidden');
			
			// $('#divWCLabel').removeClass('rn_Hidden');
			$('#divAlertMessage').removeClass('rn_Hidden');
		}

		function checkTroubleshooting(){
			var settings = {
			  "url": "https://bissell--tst2.custhelp.com/cgi-bin/bissell.cfg/php/custom/warrantycalculatorendpoint.php",
			  "method": "POST",
			  "timeout": 0,
			  "headers": {
				"Content-Type": "application/json",
				"X-HTTP-Authorization": "Qmlzc2VsbE9zdmNBUElQcm9kOkIxc3MzbGwyMDE3",
				"Authorization": "Basic Qmlzc2VsbE9zdmNBUElQcm9kOkIxc3MzbGwyMDE3"
			  },
			  "data": JSON.stringify({"ModelNumber":$("#txtModelNumber").val(),"SerialNumber":$("#txtSerialNumber").val()}),
			};
			return $.ajax(settings);
		}
		
		function checkPostalCode(){
			var settings = {
			  "url": "https://bissell--tst2.custhelp.com/cgi-bin/bissell.cfg/php/custom/validatepostalendpoint.php",
			  "method": "POST",
			  "timeout": 0,
			  "headers": {
				"Content-Type": "application/json",
				"X-HTTP-Authorization": "Qmlzc2VsbE9zdmNBUElQcm9kOkIxc3MzbGwyMDE3",
				"Authorization": "Basic Qmlzc2VsbE9zdmNBUElQcm9kOkIxc3MzbGwyMDE3"
			  },
			  "data": JSON.stringify({"PostalCode":$("#txtZipCode").val()})
			};
			return $.ajax(settings);
		}
		
		function getUrlParam(url, key) {
			var spliturl = url.replace(/^https?:\/\//, '').split('/');
			
			var nextparam = false;
			
			for(let i = 0; i < spliturl.length; i++){
				if(nextparam){
					return spliturl[i];
				}
				
				if(spliturl[i] === key){
					nextparam = true;
				}
			}
		}
	});


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

.input-validation-error {
  border: 1px solid #ff0000 !important;
  background-color: salmon !important;
}

@media all and (max-width : 768px) {
	.alertMessage {
		text-align:center;
	}
}
</style>