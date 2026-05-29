<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<style>
	body {    
		margin: 0 !important;
		padding: 0 !important;
		overflow:hidden;
	}

	/* Button height using inline-block */
	#sameAsBilling {
	  display: inline-block !important;
	  height: 20px;
	  background-color: #00abf1;
	  border: none;
	  color: white;
	  text-align: center;
	  font-size: 9pt;
	  font-weight: bold;
	  width: 46%;
	  padding-bottom:3px;
	  margin-left: 7px;
	  margin-right: 7px;
	}
	</style>
	
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
	
	// Logic check if Billing Address has been validated, used for validating Shipping Address after copying from Billing Address
	var shipSameAsBilling = false;
	
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
			// Check Shipping UNLESS shipping address is marked same as billing address
			var checkShipping = true;
			if(type === "Billing")
			{
				// CHECK if Shipping Address is already set, if so display the correct agent prompt
				var billingPrompt = "Use suggested billing address?";
				if(typeof orgCon.ShipStreet === 'undefined' || shipSameAsBilling === true)
				{
					billingPrompt = "Use suggested billing/shipping address?";
					checkShipping = false;
				}
				
				var useAddr = false;
				
				// IF the agent Street address is identical to the validated Street address
				// AND the agent zip 5 digits match the first 5 of the 9 validate zip
				// THEN auto-populate the Billing/Shipping Address fields with the validated address
				if(data['Validated']['Address1'] == contact.AddrStreet &&
					data['Validated']['PostalCode'].indexOf(contact.AddrPostalCode) >= 0)
					useAddr = true;
				else
					useAddr = window.confirm(billingPrompt +
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
				
				// IF Shipping Address is empty, apply the Billing Address field values to the Shipping Address counterparts
				if(typeof orgCon.ShipStreet === 'undefined' || shipSameAsBilling === true)
				{
					// We will not have Street1 set if the Billing address validation suggestion is declined, so set it here
					if (typeof Street1 === 'undefined') {
						var streetPieces = contact.AddrStreet.split("\n");
						var Street1 = streetPieces[0];
						var Street2 = streetPieces[1];
					}
					
					// Set the Shipping Address fields to the Billing Address fields automatically, only occurs when Billing Address changes
					contact.SetCustomFieldByName("BI$ship_street", Street1);
					contact.SetCustomFieldByName("BI$ship_street2", Street2);
					contact.SetCustomFieldByName("BI$ship_city", contact.AddrCity);
					contact.SetCustomFieldByName("BI$ship_state", contact.AddrProvID);
					contact.SetCustomFieldByName("BI$ship_country", contact.AddrCountryId);
					contact.SetCustomFieldByName("BI$ship_postalcode", contact.AddrPostalCode);
					
					// Reset Same As Billing workflow since we just synchronized Billing and Shipping address fields
					shipSameAsBilling = false;
				}
			}
			
			else if(type === "Shipping" && checkShipping === true)
			{
				var useAddr = false;
				
				// IF the agent Street address is identical to the validated Street address
				// AND the agent zip 5 digits match the first 5 of the 9 validate zip
				// THEN auto-populate the Shipping Address fields with the validated address
				if(data['Validated']['Address1'] == contact.GetCustomFieldByName("BI$ship_street") &&
					data['Validated']['PostalCode'].indexOf(contact.GetCustomFieldByName("BI$ship_postalcode")) >= 0)
					useAddr = true;
				else
					useAddr = window.confirm("Use suggested shipping address?" +
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
				url: 'enewsservice.php',
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
							"\nResult: " + data['Status']);
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
				url: 'enewsservice.php',
				data: { sid:sid, action:action, Email:email, Country:contact.AddrCountryID },
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
   
	function sameAsBilling(){

		// Split the Street address into 2 pieces
		var streets = contact.AddrStreet.split(/\r?\n/);
		var Street1 = streets[0];
		var Street2 = "";

		// Only set Street2 if it is not null
		if(streets[1] != null)
			Street2 = streets[1];
		
		// Copy Billing Address to Shipping Address
		contact.SetCustomFieldByName("BI$ship_street", Street1);
		contact.SetCustomFieldByName("BI$ship_street2", Street2);
		contact.SetCustomFieldByName("BI$ship_city", contact.AddrCity);
		contact.SetCustomFieldByName("BI$ship_state", contact.AddrProvId);
		contact.SetCustomFieldByName("BI$ship_postalcode", contact.AddrPostalCode);
		contact.SetCustomFieldByName("BI$ship_country", contact.AddrCountryId);
		
		// Reset the Original Contact shipping address fields so we do not trigger a Shipping Address validation after utilizing the Same To Billing feature
		orgCon.ShipStreet = contact.GetCustomFieldByName("BI$ship_street");
		orgCon.ShipStreet2 = contact.GetCustomFieldByName("BI$ship_street2");
		orgCon.ShipCity = contact.GetCustomFieldByName("BI$ship_city");
		orgCon.ShipProvID = contact.GetCustomFieldByName("BI$ship_state");
		orgCon.ShipCountryID = contact.GetCustomFieldByName("BI$ship_country");
		orgCon.ShipPostalCode = contact.GetCustomFieldByName("BI$ship_postalcode");
		
		// Set flag to synchronize Billing/Shipping on next address validation
		shipSameAsBilling = true;
	}	
	
    </script>
</head>
<body>
<div>
<input type='button' id ='sameAsBilling' value='Same As Billing' onclick='sameAsBilling()'/>
</div>
</body>
</html>