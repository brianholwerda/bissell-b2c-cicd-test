<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style>
	body {    
		margin: 0 !important;
		padding: 0 !important;
		overflow:hidden;
	}

	.selected {
	  background-color: navy;
	  color: yellow;
	  font-weight: bold;
	}
	</style>
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript">
	
	var osvcEndpointsAccessible = false;
	var internetConnectivity = false;
	var bwsAddressServiceConnectivity = false;
	var bwsOrderServiceConnectivity = false;
	var boolWriteDebugFile = true;
	var boolDebugFileRuntime = false;
	var strWriteDebugFileError = '';
	
	// Capture agent session
	var sid;
	
	// Capture global contact record
	var c = window.external.Contact;
	
	function runSelectedTests(){
		$('#btnRunTests').prop( "disabled", true );
		
		osvcEndpointsAccessible = false;
		internetConnectivity = false;
		bwsAddressServiceConnectivity = false;
		bwsOrderServiceConnectivity = false;
		boolWriteDebugFile = true;
		boolDebugFileRuntime = false;
		strWriteDebugFileError = '';
		
		$('#internetConnectivityResponse').html('');
		$('#sessionIDResponse').html('');
		$('#detectedUserResponse').html('');
		$('#bwsAddressServiceConnectivityResponse').html('');
		$('#bwsOrderServiceConnectivityResponse').html('');
		$('#bwsUSAddressValidationResponse').html('');
		$('#bwsAUAddressValidationResponse').html('');
		$('#bwsTransHistoryResponse').html('');
		$('#bwsGetWebOrderResponse').html('');
		$('#runIPTestResponse').html('');
		$('#testIDResponse').html('');
		$('#writeDebugFileResponse').html('');
		
		var today = new Date();
		var testid = today.getFullYear() + ':' + (today.getMonth()+1) + ':' + today.getDate() + ':' + today.getHours() + ':' + today.getMinutes() + ':' + today.getSeconds();
		
		// if (!$('#addressValidationTest').prop('checked') && !$('#getOnyxWebOrderTest').prop('checked')){
			// alert('No Tests Selected');
			// $('#btnRunTests').prop( "disabled", false );
			// return false;
		// }
		
		$('#internetConnectivityResponse').html(GeneralInternetConnectivityTest(testid));
		$('#sessionIDResponse').html('<div style="color:navy;">Session ID: ' + '<?php echo $_GET['sid'] ?>' + '</div>');
		$('#testIDResponse').html('<div style="color:navy;">Test ID: ' + testid + '</div>');
		
		if (osvcEndpointsAccessible){
			$('#detectedUserResponse').html(DetectUserAccount());
			
			// if ($('#addressValidationTest').prop('checked') && internetConnectivity){
				$('#bwsAddressServiceConnectivityResponse').html(BWSAddressServiceConnectivityTest(testid));
				
				if (bwsAddressServiceConnectivity){
					$('#bwsUSAddressValidationResponse').html(ValidateUSAddressTest(testid));
					$('#bwsAUAddressValidationResponse').html(ValidateAUAddressTest(testid));
				}
			// }
		
			// if ($('#getOnyxWebOrderTest').prop('checked')){
				$('#bwsOrderServiceConnectivityResponse').html(BWSOrderServiceConnectivityTest(testid));
				
				if (bwsOrderServiceConnectivity){
					$('#bwsTransHistoryResponse').html(TransactionHistoryTest(testid));
					$('#bwsGetWebOrderResponse').html(GetWebOrderTest(testid));
				}
			// }
		}
		
		$.getJSON('http://gd.geobytes.com/GetCityDetails?callback=?', function(data) {
			$('#runIPTestResponse').html('<div style="color:navy;">Public IP Address: ' + data.geobytesipaddress + '</div>');
			if (boolWriteDebugFile){
				WriteToDebugFile(JSON.stringify(data, null, 2), 'GetIPAddress');
			}
		});
		
		var strDebugHTML = '';
		if (boolWriteDebugFile && boolDebugFileRuntime){
			strDebugHTML = '<div style="color:green;">Write Debug File: Success</div>';
		} else {
			if (!strWriteDebugFileError){
				WriteToDebugFile('Test', 'TestMessageForFileErrorVerification');
			}
			strDebugHTML = '<div style="color:red;">Write Debug File: ' + strWriteDebugFileError + '</div>';
		}
		
		$('#writeDebugFileResponse').html(strDebugHTML);
	}
	
	function WriteToDebugFile(stringToWrite, actionToDebug, testID){
		sid = '<?php echo $_GET['sid'] ?>';
		
		$.ajax({
           type: "GET",
		   url: 'endpointtestajaxhandler.php',
		   data: { action: "writetodebug", debugaction: actionToDebug, stringtowrite:stringToWrite, testid: testID, sid:sid },
           cache: false,
		   async: false,
           success: function (data) {
			   if (!(data == 'Success')){
				   writeSuccessful = false;
				   strWriteDebugFileError += 'WriteDebugFileStatus: ' + data;
			   } else {
				   boolDebugFileRuntime = true;
			   }
           },
           error:function(error){
               if (error.readyState == 4){
				   boolWriteDebugFile = false;
				   strWriteDebugFileError = 'WriteDebugFile - Error: OSvC Endpoints not reachable';
			   } else {
				   boolWriteDebugFile = false;
				   returnValue = 'WriteDebugFile - Exception: ' + JSON.stringify(error);
			   }
           },
           dataType: "json"
       });
	}
	
	function GeneralInternetConnectivityTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		osvcEndpointsAccessible = false;
		var returnValue = '<div style="color:red;">Internet Connectivity Test Failed - Error: General error in Internet Connectivity Test</div>';
		
		$.ajax({
           type: "GET",
		   url: 'endpointtestajaxhandler.php',
		   data: { action: "geturlstatus", url: "https://www.google.com", sid:sid },
           cache: false,
		   async: false,
           success: function (data) {
			   osvcEndpointsAccessible = true;
			   WriteToDebugFile(JSON.stringify(data), 'InternetConnectivityTest', testid);
				if (data['http_code'] == '200'){
					internetConnectivity = true;
					returnValue = '<div style="color:green;">Internet Connectivity Test Passed - Google Status Code: ' + data['http_code'] + '</div>';
				} else {
				   returnValue = '<div style="color:red;">Internet Connectivity Test Failed - Google Status Code: ' + data['http_code'] + '</div>';
				}
           },
           error:function(error){
               if (error.readyState == 4){
				   osvcEndpointsAccessible = false;
				   returnValue = '<div style="color:red;">Internet Connectivity Test Failed - Error: OSvC Endpoints not reachable</div>';
			   } else {
				   osvcEndpointsAccessible = false;
				   returnValue = '<div style="color:red;">Internet Connectivity Test Failed - Error: Internet Connectivity Test Exception: ' + JSON.stringify(error) + '</div>';
			   }
           },
           dataType: "json"
       });
		
		return returnValue;
	}
	
	function BWSAddressServiceConnectivityTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		bwsAddressServiceConnectivity = false;
		var returnValue = '<div style="color:red;">BWS Address Service Connectivity Test Failed - Error: General error in BWS Address Service Connectivity Test</div>';
		
		$.ajax({
           type: "GET",
		   url: 'endpointtestajaxhandler.php',
		   data: { action: "getbwsaddressservicestatus", sid:sid },
           cache: false,
		   async: false,
           success: function (data) {
			   bwsAddressServiceConnectivity = false;
			   WriteToDebugFile(JSON.stringify(data), 'BWSAddressServiceConnectivityTest', testid);
				if (data['http_code'] == '200'){
					bwsAddressServiceConnectivity = true;
					returnValue = '<div style="color:green;">BWS Address Service Connectivity Test Passed - Status Code: ' + data['http_code'] + ' | URL: ' + data['url'] + '</div>';
				} else {
				   returnValue = '<div style="color:red;">BWS Address Service Connectivity Test Failed - Status Code: ' + data['http_code'] + ' | URL: ' + data['url'] + '</div>';
				}
           },
           error:function(error){
               if (error.readyState == 4){
				   bwsAddressServiceConnectivity = false;
				   returnValue = '<div style="color:red;">BWS Address Service Connectivity Test Failed - Error: BWS Address Service Endpoint not reachable</div>';
			   } else {
				   bwsAddressServiceConnectivity = false;
				   returnValue = '<div style="color:red;">BWS Address Service Connectivity Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
           },
           dataType: "json"
       });
	   
	   return returnValue;
	}
	
	function BWSOrderServiceConnectivityTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		bwsOrderServiceConnectivity = false;
		var returnValue = '<div style="color:red;">BWS Order Service Connectivity Test Failed - Error: General error in BWS Order Service Connectivity Test</div>';
		
		$.ajax({
           type: "GET",
		   url: 'endpointtestajaxhandler.php',
		   data: { action: "getbwsorderservicestatus", sid:sid },
           cache: false,
		   async: false,
           success: function (data) {
			   bwsOrderServiceConnectivity = false;
			   WriteToDebugFile(JSON.stringify(data), 'BWSOrderServiceConnectivityTest', testid);
				if (data['http_code'] == '200'){
					bwsOrderServiceConnectivity = true;
					returnValue = '<div style="color:green;">BWS Order Service Connectivity Test Passed - Status Code: ' + data['http_code'] + ' | URL: ' + data['url'] + '</div>';
				} else {
				   returnValue = '<div style="color:red;">BWS Order Service Connectivity Test Failed - Status Code: ' + data['http_code'] + ' | URL: ' + data['url'] + '</div>';
				}
           },
           error:function(error){
               if (error.readyState == 4){
				   bwsOrderServiceConnectivity = false;
				   returnValue = '<div style="color:red;">BWS Order Service Connectivity Test Failed - Error: BWS Order Service Endpoint not reachable</div>';
			   } else {
				   bwsOrderServiceConnectivity = false;
				   returnValue = '<div style="color:red;">BWS Order Service Connectivity Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
           },
           dataType: "json"
       });
	   
	   return returnValue;
	}
	
	function ValidateUSAddressTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		var returnValue = '<div style="color:red;">US Address Validation Test Failed - Error: General error in US Address Validation Test</div>';
		
		$.ajax({
			type: "POST",
			url: 'validateaddress.php',
			async: false,
			data: { Address1: "2345 Walker Ave.", Address2: "", City: "Grand Rapids", StateCode: "MI", PostalCode: "49544", CountryCode: "US", sid:sid },
			success: function (data) {
				//addressValidateCallback(data, type);
				//addressData = data;
				WriteToDebugFile(JSON.stringify(data), 'ValidateUSAddressTest', testid);
				if (data['Code'] == '2' || data['Code'] == '1'){
					returnValue = '<div style="color:green;">US Address Validation Test Passed - Return Code: ' + data['Code'] + '</div>';
				} else {
					returnValue = '<div style="color:red;">US Address Validation Test Failed - Unexpected Return Code: ' + data['Code'] + '</div>';
				}
			},
			dataType: "json",
			error: function (status, error) {
				if (error.readyState == 4){
				   returnValue = '<div style="color:red;">US Address Validation Test Failed - Error: BWS Address Service Endpoint not reachable</div>';
			   } else {
				   returnValue = '<div style="color:red;">US Address Validation Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
			}
		});
		
		return returnValue;
	}
	
	function ValidateAUAddressTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		var returnValue = '<div style="color:red;">AU Address Validation Test Failed - Error: General error in AU Address Validation Test</div>';
		
		$.ajax({
			type: "POST",
			url: 'validateaddress.php',
			async: false,
			data: { Address1: "42 Rocco Drive", Address2: "", City: "Scoresby", StateCode: "VIC", PostalCode: "3179", CountryCode: "AU", sid:sid },
			success: function (data) {
				//addressValidateCallback(data, type);
				//addressData = data;
				WriteToDebugFile(JSON.stringify(data), 'ValidateAUAddressTest', testid);
				if (data['Code'] == '2' || data['Code'] == '1'){
					returnValue = '<div style="color:green;">AU Address Validation Test Passed - Return Code: ' + data['Code'] + '</div>';
				} else {
					returnValue = '<div style="color:red;">AU Address Validation Test Failed - Unexpected Return Code: ' + data['Code'] + '</div>';
				}
			},
			dataType: "json",
			error: function (status, error) {
				if (error.readyState == 4){
				   returnValue = '<div style="color:red;">AU Address Validation Test Failed - Error: BWS Address Service Endpoint not reachable</div>';
			   } else {
				   returnValue = '<div style="color:red;">AU Address Validation Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
			}
		});
		
		return returnValue;
	}
	
	function TransactionHistoryTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		var returnValue = '<div style="color:red;">Get Transaction History Test Failed - Error: General error in Get Transaction History Test</div>';
		
		$.ajax({
			type: "POST",
			url: 'contactorderhistory.php',
			async: false,
			data: { action: "getorderhistory", ConsumerID: "9238453", sid:sid },
			success: function (data) {
				WriteToDebugFile(JSON.stringify(data), 'GetTransactionHistoryTest', testid);
				if (data['ResponseCode'] == 'Success'){
					returnValue = '<div style="color:green;">Get Transaction History Test Passed - Response Message: ' + data['ResponseMessage'] + '</div>';
				} else {
					returnValue = '<div style="color:red;">Get Transaction History Test Failed - Response Code: ' + data['ResponseCode'] + ' | Response Message: ' + data['ResponseMessage'] + '</div>';
				}
			},
			dataType: "json",
			error: function (status, error) {
				if (error.readyState == 4){
				   returnValue = '<div style="color:red;">Get Transaction History Test Failed - Error: BWS Address Service Endpoint not reachable</div>';
			   } else {
				   returnValue = '<div style="color:red;">Get Transaction History Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
			}
		});
		
		return returnValue;
	}
	
	function GetWebOrderTest(testid){
		sid = '<?php echo $_GET['sid'] ?>';
		var returnValue = '<div style="color:red;">Get Web Order Test Failed - Error: General error in Get Web Order Test</div>';
		
		$.ajax({
			type: "POST",
			url: 'contactorderhistory.php',
			async: false,
			data: { action: "getlineitemhistory", ConsumerID: "9238453", OrderNumber: "9166377", sid:sid },
			success: function (data) {
				WriteToDebugFile(JSON.stringify(data), 'GetWebOrderTest', testid);
				if (data['ResponseCode'] == 'Success'){
					returnValue = '<div style="color:green;">Get Web Order Test Passed - Response Message: ' + data['ResponseMessage'] + '</div>';
				} else {
					returnValue = '<div style="color:red;">Get Web Order Test Failed - Response Code: ' + data['ResponseCode'] + ' | Response Message: ' + data['ResponseMessage'] + '</div>';
				}
			},
			dataType: "json",
			error: function (status, error) {
				if (error.readyState == 4){
				   returnValue = '<div style="color:red;">Get Web Order Test Failed - Error: BWS Address Service Endpoint not reachable</div>';
			   } else {
				   returnValue = '<div style="color:red;">Get Web Order Test Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
			}
		});
		
		return returnValue;
	}
	
	function DetectUserAccount(){
		sid = '<?php echo $_GET['sid'] ?>';
		var returnValue = '<div style="color:red;">Detect User Account Failed - Error: General error in detecting user account</div>';
		
		$.ajax({
           type: "GET",
		   url: 'endpointtestajaxhandler.php',
		   data: { action: "detectuser", sid:sid },
           cache: false,
		   async: false,
           success: function (data) {
			   // WriteToDebugFile(JSON.stringify(data));
				returnValue = '<div style="color:navy;">Detected User ID: ' + data + '</div>';
           },
           error:function(error){
               if (error.readyState == 4){
				   returnValue = '<div style="color:red;">Detect User Account Failed - Error: OSvC Endpoints not reachable</div>';
			   } else {
				   returnValue = '<div style="color:red;">Detect User Account Failed - Exception: ' + JSON.stringify(error) + '</div>';
			   }
           },
           dataType: "json"
       });
		
		return returnValue;
	}

	$(function () {
		$(".optionsList").change(function (e) {
			var $this = $(this);
			var $row = $this.parent();

			if ($this.is(":checked"))
			{
				$row.addClass('selected');
				//$row.removeClass('rowWarehouseApproved');
			}
			else {
				//$row.addClass('rowWarehouseApproved');
				$row.removeClass('selected');
			}
		});
	});
	</script>
</head>
<body>
<div>
	<div><input type="button" id="btnRunTests" value="Run Workspace Tests" onclick="runSelectedTests()"/></div>
</div>
<div>
	<div id="runIPTestResponse" style="font-size:0.8em;"></div>
	<div id="testIDResponse" style="font-size:0.8em;"></div>
	<div id="sessionIDResponse" style="font-size:0.8em;"></div>
	<div id="detectedUserResponse" style="font-size:0.8em;"></div>
	<div id="internetConnectivityResponse" style="font-size:0.8em;"></div>
	<div id="bwsAddressServiceConnectivityResponse" style="font-size:0.8em;"></div>
	<div id="bwsUSAddressValidationResponse" style="font-size:0.8em;"></div>
	<div id="bwsAUAddressValidationResponse" style="font-size:0.8em;"></div>
	<div id="bwsOrderServiceConnectivityResponse" style="font-size:0.8em;"></div>
	<div id="bwsTransHistoryResponse" style="font-size:0.8em;"></div>
	<div id="bwsGetWebOrderResponse" style="font-size:0.8em;"></div>
	<div id="writeDebugFileResponse" style="font-size:0.8em;"></div>
</div>
</body>
</html>