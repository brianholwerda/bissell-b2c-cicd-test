<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript">

	// Fetch current contact object
	var contact = window.external.Contact;
	
	// Create array to hold original Contact address information
	var orgCon = {};
	
	// Hold country/province mapping variables
	var countryAbbreviationList;
	var countryAbbr;
	var provinceAbbreviationList;
	var provinceAbbr;
	
	// Split up config, at runtime config is parsed to ID and call to get config fails
	var configPrefix = "CUSTOM_CFG_";
	var provinceSuffix = "PROVINCE_ABBREVIATION_JSON";
	
	// Capture agent session
	var sid;
	
	// Declare address fields
	var Street1;
	var Street2;
	var City;
	var ProvName;
	var CountryID;
	var Postal;
	
	// Capture all Billing address information
	orgCon.BillStreet = contact.AddrStreet;
	orgCon.BillCity = contact.AddrCity;
	orgCon.BillProvID = contact.AddrProvID;
	orgCon.BillCountryID = contact.AddrCountryID;
	orgCon.BillPostalCode = contact.AddrPostalCode;
		
	// Capture all Shipping address information
	orgCon.ShipStreet = contact.GetCustomFieldByName("BI$ship_street");
	orgCon.ShipStreet2 = contact.GetCustomFieldByName("BI$ship_street2");
	orgCon.ShipCity = contact.GetCustomFieldByName("BI$ship_city");
	orgCon.ShipProvID = contact.GetCustomFieldByName("BI$ship_state");
	orgCon.ShipCountryID = contact.GetCustomFieldByName("BI$ship_country");
	orgCon.ShipPostalCode = contact.GetCustomFieldByName("BI$ship_postalcode");
	
	// Fetch Exact eNews Subscription flag
	eNewsStatus(contact.EmailAddr);
	
	/** Function called when an agent saves a Contact record
	 *	Compare Billing and Shipping address fields to their original values when the Contact record was opened
	 *	IF no changes are found, save the Contact record
	 *	IF changes are found, call Address Validation service and require agent to select original or suggested address
	 *	IF changes are found to Exact eNews Subscription field, call subscription Web Service 
	 */
	function onbeforesave()
	{	
		if(orgCon.BillStreet != contact.AddrStreet ||
			orgCon.BillCity != contact.AddrCity ||
			orgCon.BillProvID != contact.AddrProvID ||
			orgCon.BillCountryID != contact.AddrCountryID ||
			orgCon.BillPostalCode != contact.AddrPostalCode)
		{
			validateAddress(contact, "Billing", addressValidateCallback);
		}
		
		if(orgCon.ShipStreet != contact.GetCustomFieldByName("BI$ship_street") ||
			(orgCon.ShipStreet2 != contact.GetCustomFieldByName("BI$ship_street2") &&
			orgCon.ShipStreet2 != null) ||
			orgCon.ShipCity != contact.GetCustomFieldByName("BI$ship_city")||
			orgCon.ShipProvID != contact.GetCustomFieldByName("BI$ship_state") ||
			orgCon.ShipCountryID != contact.GetCustomFieldByName("BI$ship_country") ||
			orgCon.ShipPostalCode != contact.GetCustomFieldByName("BI$ship_postalcode"))
		{
			validateAddress(contact, "Shipping", addressValidateCallback);
		}
		
		if(orgCon.MaOptIn != contact.MaOptIn)
		{
			eNewsService(contact);
		}
		
		contact.SetCustomFieldByName("c$cur_order_num", null);
		window.external.beforesavecomplete(true);
	}
	
	/** Function called when an Agent closes a Contact record
	 *  Set the Contact.c$cur_order_num field to null 
	 */
	/**function oncancel()
	{
		contact.SetCustomFieldByName("c$cur_order_num", null);
		window.external.cancelcompleted(true);
	}
          */
	function validateAddress(contact, type, addressValidateCallback)
	{
		// Capture agent session to satisfy AgentAuthenticator
		sid = '<?php echo $_GET['sid'] ?>';
		
		// Handle billing address
		if(type === "Billing")
		{
			// Split standard Contact street address by CRLF and pass in as street1 and street2
			var streets = contact.AddrStreet.split(/\r?\n/);
			Street1 = streets[0];
			Street2 = streets[1];
			City = contact.AddrCity;
			CountryID = contact.AddrCountryID;
			ProvName = getNameFromId("AddrProvId", contact.AddrProvId);
			Postal = contact.AddrPostalCode;
		}
		
		// Handle shipping address
		else if(type === "Shipping")
		{
			Street1 = contact.GetCustomFieldByName("BI$ship_street");
			Street2 = contact.GetCustomFieldByName("BI$ship_street2");
			City = contact.GetCustomFieldByName("BI$ship_city");
			CountryID = contact.GetCustomFieldByName("BI$ship_country");
			ProvName = getNameFromId("AddrProvId", contact.GetCustomFieldByName("BI$ship_state"));
			Postal = contact.GetCustomFieldByName("BI$ship_postalcode");
		}
		
		// First, get the State/Province label->abbreviation mapping config
		$.ajax({
		   type: "GET",
		   url: 'ordermanagerajaxhandler.php?action=getconfiguration&configkey=' + configPrefix + provinceSuffix + '&sid=' + sid,
		   cache: false,
		   async: false,
		   success: function (data) {
				provinceAbbreviationList = data;
		   },
		   error:function(error){
			   alert(JSON.stringify(error));
		   },
		   dataType: "json"
	   });
	   
		// Return the State/Province abbreviation expected by Bissell AddressService web call
		provinceAbbr = getProvinceAbbreviation(CountryID, ProvName);
		
		// Second, get the Country label->abbreviation mapping
		$.ajax({
           type: "GET",
		   url: 'ordermanagerajaxhandler.php?action=getcountryisolist&sid=' + sid,
           cache: false,
		   async: false,
           success: function (data) {
			   countryAbbr = data[CountryID];
           },
           error:function(error){
               alert(JSON.stringify(error));
           },
           dataType: "json"
       });
		
		// Third, post address for validation and handle response
		$.ajax({
			type: "POST",
			url: 'validateaddress.php',
			async: false,
			data: { Address1: Street1, Address2: Street2, City: City, StateCode: provinceAbbr, PostalCode: Postal, CountryCode: countryAbbr, sid:sid },
			success: function (data) {
				addressValidateCallback(data, type);
			},
			dataType: "json",
			error: function (status, error) {
				alert(JSON.stringify(error));
			}
		});
	}
		
	function addressValidateCallback(data, type) 
	{
		// IF address validation is successful, present agent with suggested address
		if(data['Code'] == 1 || data['Code'] == 2)
		{
			if(type === "Billing")
			{
				var useAddr = window.confirm("Use suggested billing address?" +
					"\n\nStreet1: " + data['Validated']['Address1'] +
					"\nStreet2: " + data['Validated']['Address2'] +
					"\nCity: " + data['Validated']['City'] +
					"\nState: " + data['Validated']['StateCode'] +
					"\nCountry: " + data['Validated']['CountryCode'] +
					"\nPostal: " + data['Validated']['PostalCode']);
					
				if(useAddr == true)
				{
					var response = null;

					// Get the State/Province ID for the returned State/Province abbreviation
					ProvName = getProvinceLabel(CountryID, data['Validated']['StateCode']);
					
					var restReq = new XMLHttpRequest();
					var url = "/services/rest/connect/v1.3/queryResults/?query=SELECT provinces.id FROM countries WHERE countries.id = " + CountryID + " AND countries.provinces.name = '" + ProvName + "'";
					
					restReq.open("GET", url, false);
					restReq.setRequestHeader("Content-type", "application/json");
					restReq.setRequestHeader("Authorization", "Session " + sid);
					restReq.send();
					response = JSON.parse(restReq.responseText);
					
					var Street1 = data['Validated']['Address1'];
					var Street2;
					
					// Only set Street2 if it is not null
					if(data['Validated']['Address2'] != null)
						var Street2 = "\r\n" + data['Validated']['Address2'];
					
					// Parse together Address1 and Address2, split with line break so each street appears on a separate line
					if(Street2 != null)
						contact.AddrStreet = Street1 + Street2;
					else
						contact.AddrStreet = Street1;
					
					contact.AddrCity = data['Validated']['City'];
					
					// Only set State/Province if an ID exists in OSvC
					if(response.items[0].rows.length > 0)
						contact.AddrProvID = response.items[0].rows;
					contact.AddrPostalCode = data['Validated']['PostalCode'];
				}
			}
			
			else if(type === "Shipping")
			{
				var useAddr = window.confirm("Use suggested shipping address?" +
					"\n\nStreet1: " + data['Validated']['Address1'] +
					"\nStreet2: " + data['Validated']['Address2'] +
					"\nCity: " + data['Validated']['City'] +
					"\nState: " + data['Validated']['StateCode'] +
					"\nCountry: " + data['Validated']['CountryCode'] +
					"\nPostal: " + data['Validated']['PostalCode']);
					
				if(useAddr == true)
				{
					var response = null;

					// Get the State/Province ID for the returned State/Province abbreviation
					ProvName = getProvinceLabel(CountryID, data['Validated']['StateCode']);
		
					var restReq = new XMLHttpRequest();
					var url = "/services/rest/connect/v1.3/queryResults/?query=SELECT provinces.id FROM countries WHERE countries.id = " + CountryID + " AND countries.provinces.name = '" + ProvName + "'";
					
					restReq.open("GET", url, false);
					restReq.setRequestHeader("Content-type", "application/json");
					restReq.setRequestHeader("Authorization", "Session " + sid);
					restReq.send();
					var response = JSON.parse(restReq.responseText);
					
					contact.SetCustomFieldByName("BI$ship_street", data['Validated']['Address1']);
					contact.SetCustomFieldByName("BI$ship_street2", data['Validated']['Address2']);
					contact.SetCustomFieldByName("BI$ship_city", data['Validated']['City']);
					// Only set State/Province if an ID exists in OSvC
					if(response.items[0].rows.length > 0)
						contact.SetCustomFieldByName("BI$ship_state", parseInt(response.items[0].rows));
					contact.SetCustomFieldByName("BI$ship_postalcode", data['Validated']['PostalCode']);
				}
			}
		}
		
		else
		{
			// Capture either BWS or cURL error response
			var errCode;
			var errMsg;
			if(data['Code'] != null)
			{
				errCode = data['Code'];
				errMsg = data['Message'];
			}
			
			else
			{
				errCode = data['ResponseCode'];
				errMsg = data['ResponseMessage'];
			}
			// Alert agent if Billing/Shipping address fails to validate, display Bissell error response code and message
			alert("Error validating " + type + " address!" +
				"\nErrorCode: " + errCode +
				"\nErrorMessage: " + errMsg);
		}
	}
	
	function eNewsService(contact)
	{
		// Capture agent session to satisfy AgentAuthenticator
		sid = '<?php echo $_GET['sid'] ?>';
		
		var action;
		
		if(contact.MaOptIn == 0)
			action = 'Unsubscribe';
		else if(contact.MaOptIn == 1)
			action = 'Subscribe';
		else
			action = 'Undefined';
		
		if (contact.MaOptIn == 1 && contact.EmailAddr == undefined){
			alert('Error setting eNews status. Contact record must have a valid email address.');
		} 
		else if (contact.MaOptIn == 1 && [1,2].indexOf(contact.AddrCountryID) == -1) {
			alert('Error setting eNews status. Enabling eNews is only valid for US and CA consumers.');
		} else {
		
			$.ajax({
				type: "POST",
				url: 'bdh_enewsservice.php',
				async: false,
				data: { sid:sid, action:action, Email:contact.EmailAddr, Country:contact.AddrCountryID },
				success: function (data) {
					
					// If the subscribe or unsubscribe event fail, warn agent
					if(data['Status'] == null)
						alert("Unable to complete Exact eNews " + action + " action. Please contact an Adminsitrator");
					
					// Otherwise display successful response
					else
					{
						alert("Email: " + contact.EmailAddr +
							"\nAction: " + action +
							"\nResult: " + data['Status'] +
							"\nEmailFromAjax: " + data['Email'] +
							"\nCountryFromAjax: " + data['Country'] +
							"\nSourceVal: " + data['Source'] +
							"\nSoapXML: " + data['SoapXML']);
					}
				},
				dataType: "json",
				error: function (status, error) {
					alert(JSON.stringify(error));
				}
			});
			
		}
	}
	
	function eNewsStatus(email)
	{
		// Capture agent session to satisfy AgentAuthenticator
		sid = '<?php echo $_GET['sid'] ?>';
		
		if (email == undefined) {
			contact.MaOptIn = 0;
			orgCon.MaOptIn = contact.MaOptIn;
		} else {
		
			var action = "Status";
			
			$.ajax({
				type: "POST",
				url: 'bdh_enewsservice.php',
				data: { sid:sid, action:action, Email:email },
				success: function (data) {
					if(data['Status'] === "false")
						contact.MaOptIn = 0;
					else if(data['Status'] === "true")
						contact.MaOptIn = 1;
					else
						alert("Error fetching Exact eNews subscription status");
					
					orgCon.MaOptIn = contact.MaOptIn;
				},
				dataType: "json",
				error: function (status, error) {
					alert(JSON.stringify(error));
				}
			});
		}
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
	
	function getProvinceAbbreviation(countryId, province) 
	{
       var countryAbbrObj = provinceAbbreviationList.countryid[countryId];
       for (var provKey in countryAbbrObj) {

           if (countryAbbrObj.hasOwnProperty(provKey)) {
               
               if (countryAbbrObj[provKey] == province) {
                   return provKey;
               }
           }
       }
       return "";
   }
   
	function getProvinceLabel(countryId, provinceAbbr) 
	{
       var countryAbbrObj = provinceAbbreviationList.countryid[countryId];
       for (var provKey in countryAbbrObj) {
		   
           if (countryAbbrObj.hasOwnProperty(provKey)) {
			   
               if (provKey == provinceAbbr) {
					return countryAbbrObj[provKey];
               }
           }
       }
       return "";
   }
   
    </script>
</head>