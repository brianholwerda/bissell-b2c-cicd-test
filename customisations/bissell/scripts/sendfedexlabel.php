<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<style>
	#sendFedExLabel {
		height: 100%;
		width: 100%;
		background-color: rgba(186, 20, 25, 255);
		color: white;
		text-align: center;
		font-size: 9pt;
		font-weight: bold;
	}
	</style>
	
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript">
	
	// Capture global Order Header record
	var orderHeader = window.external.GetCustomObject('BUS', 'OrderHeader');
	
	// Capture agent session for AgentAuthenticator and BUS$OrderHeader.ID
	var sid = '<?php echo $_GET['sid'] ?>';
	var oid = '<?php echo $_GET['oid'] ?>';

	// Split up config, at runtime config is parsed to ID and call to get config fails
	var configPrefix = "CUSTOM_CFG_";
	var provinceSuffix = "PROVINCE_ABBREVIATION_JSON";
	
	function sendFedExLabel()
	{
		$("#sendFedExLabel").attr("disabled", true);
		var returnAuth = {};
		returnAuth.order = {};
		returnAuth.order.ShippingAddress = {};
		returnAuth.order.ReturnWarehouseDetails = {};
		
		// Check if required fields are set, if not exit
		var RANumber = orderHeader.GetCustomFieldByName('RANumber');
		if(RANumber == -1)
		{
			alert('Unable to Email FedEx label, RA Number is not set!');
			return;
		}
		
		// Set fields for BUS$SystemIntegration association
		returnAuth.order.IncidentId = orderHeader.GetCustomFieldByName('Incident');
		returnAuth.order.ContactId = orderHeader.GetCustomFieldByName('Contact');
		returnAuth.order.OrderHeaderId = oid;
		
		// Capture necessary RA fields for FedEx shipment fields
		returnAuth.order.OrderDate = new Date(orderHeader.GetCustomFieldByName('CreatedTime') * 1000); // Multiply timestamp by 1000 to get milliseconds
		returnAuth.order.ReturnNumber = RANumber;
		returnAuth.order.ShippingAddress.Name = orderHeader.GetCustomFieldByName('ShipToCustomerName');
		returnAuth.order.ShippingAddress.Phone = orderHeader.GetCustomFieldByName('ShipToPhone');
		returnAuth.order.ShippingAddress.Address1 = orderHeader.GetCustomFieldByName('ShipToAddress1');
		returnAuth.order.ShippingAddress.Address2 = orderHeader.GetCustomFieldByName('ShipToAddress2');
		returnAuth.order.ShippingAddress.City = orderHeader.GetCustomFieldByName('ShipToCity');
		returnAuth.order.ShippingAddress.PostalCode = orderHeader.GetCustomFieldByName('ShipToPostalCode');
		
		// First, get the State/Province label->abbreviation mapping config
		CountryID = orderHeader.GetCustomFieldByName('ShipToCountry');
		ProvName = getNameFromId("AddrProvId", orderHeader.GetCustomFieldByName('ShipToState'));
		
		$.ajax({
			type: "GET",
			url: 'ordermanagerajaxhandler.php?action=getconfiguration&configkey=' + configPrefix + provinceSuffix + '&sid=' + sid,
			cache: false,
			async: false,
			success: function (data) {
				provinceAbbreviationList = data;
				returnAuth.order.ShippingAddress.StateCode = getProvinceAbbreviation(CountryID, ProvName);
			},
			error:function(error){
				alert(JSON.stringify(error));
			},
			dataType: "json"
		});
		
		// Next, get the Country label->abbreviation mapping
		$.ajax({
			type: "GET",
			url: 'ordermanagerajaxhandler.php?action=getcountryisolist&sid=' + sid,
			cache: false,
			async: false,
			success: function (data) {
				returnAuth.order.ShippingAddress.CountryCode = data[CountryID];
			},
			error:function(error){
				alert(JSON.stringify(error));
			},
			dataType: "json"
		});
		
		// Fetch warehouse data required for FedEx web service call
		var returnLocationId = orderHeader.GetCustomFieldByName('RAReturnLocation');
		$.ajax({
			type: "GET",
			url: 'ordermanagerajaxhandler.php?action=getwarehousebyreturnlocation&returnlocationid=' + returnLocationId + '&sid=' + sid,
			cache: false,
			success: function (data) {
				returnAuth.order.ReturnWarehouseDetails.Company = data['Company'];
				returnAuth.order.ReturnWarehouseDetails.Phone = data['Phone'];
				returnAuth.order.ReturnWarehouseDetails.Address1 = data['Address1'];
				returnAuth.order.ReturnWarehouseDetails.Address2 = data['Address2'];
				returnAuth.order.ReturnWarehouseDetails.City = data['City'];
				returnAuth.order.ReturnWarehouseDetails.State = data['State'];
				returnAuth.order.ReturnWarehouseDetails.Country = data['Country'];
				returnAuth.order.ReturnWarehouseDetails.PostalCode = data['PostalCode'];
				
				// Fetch email address for BUS$OrderHeader.Contact
				var restReq = new XMLHttpRequest();
				var url = "/services/rest/connect/v1.3/contacts/" + orderHeader.GetCustomFieldByName('Contact') + "/emails/0";
				
				restReq.open("GET", url, false);
				restReq.setRequestHeader("Content-type", "application/json");
				restReq.setRequestHeader("Authorization", "Session " + sid);
				restReq.send();
				response = JSON.parse(restReq.responseText);
				returnAuth.order.ShippingAddress.EmailAddress = response['address'];
				
				// Regenerate FedEx RA Shipment label
				$.ajax({
					type: "POST",
					url: 'submitreturnauth.php',
					data: { action:"getLabel", ReturnAuth:JSON.stringify(returnAuth), sid:sid },
					success: function (data) {
						if(data != null)
						{
							alert("FedEx Shipment label has been emailed. Tracking # " + data);
							orderHeader.SetCustomFieldByName("RATrackingNumber", data);
							orderHeader.SetCustomFieldByName("FedExLabelSent", true);
						}
						else
							alert("Failure generating FedEx shipment label, please review System Integration logs!");
							
						$("#sendFedExLabel").attr("disabled", false);
					},
					dataType: "json",
					error: function (status, error) {
						alert(JSON.stringify(error));
					}
				});
		   },
		   error:function(error){
			   alert('Unable to fetch BUS$Warehouse, please confirm RA Return Location!');
		   },
		   dataType: "json"
		});
	}

	function getNameFromId(list, id)
	{
		var c = window.external.Contact;
		var names = c.GetNameValues(list);

		var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = "false";
		xmlDoc.loadXML(names);

		var x = xmlDoc.getElementsByTagName("Item");
		for (i = 0; i < x.length; i++)
		{
			var attlist = x.item(i).attributes;
			var att = attlist.getNamedItem("Id");
			if (att.value == id)
				return attlist.getNamedItem("Name").value;
		}

		return "";
	}
	
	// Map State/Province ID to abbreviation
	function getProvinceAbbreviation(countryId, province) 
	{
       var countryAbbrObj = provinceAbbreviationList.countryid[countryId];
       for (var provKey in countryAbbrObj)
           if (countryAbbrObj.hasOwnProperty(provKey))
               if (countryAbbrObj[provKey] == province)
                   return provKey;
		return "";
	}	   
	
	</script>
</head>
<body>
<input type='button' id ='sendFedExLabel' value='Email Label' onclick='sendFedExLabel()'/>
</body>
</html>