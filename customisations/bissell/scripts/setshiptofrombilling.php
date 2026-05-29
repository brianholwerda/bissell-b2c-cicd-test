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
	#updateshipadd {
	  display: inline-block !important;
	  height: 20px;
	  background-color: #b8001f;
	  border: none;
	  color: white;
	  text-align: center;
	  font-size: 9pt;
	  font-weight: bold;
	  width: 46%;
	  padding-bottom:3px;
	  margin-left: 7px;
	}

	/* Button height using inline-block */
	#sameasbilling {
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
	
	// Capture global contact record
	var c = window.external.Contact;

	/* ChangeShipTo()
	This changes the Ship Updated field to yes so that the system can recognize that it has been updated so that the onbeforesave function will not run and override the agents change. 
	*/
	function changeshipto(){
		var c = window.external.Contact;
		c.SetCustomFieldByName("BI$ship_updated", 1);
	}

	/* Same As Billing()
	If an agent updates the billing address wants to update the shipping address value to the new billing address, but a prior change was already made to the shipping address this function will update the field. 
	*/
	function sameasbilling(){

		// Split the Street address into 2 pieces
		var streets = c.AddrStreet.split(/\r?\n/);
		var Street1 = streets[0];
		var Street2 = "";

		// Only set Street2 if it is not null
		if(streets[1] != null)
			Street2 = streets[1];
		
		//Reset Shipping Updated
		c.SetCustomFieldByName("BI$ship_updated", 0);
		
		// Copy Billing Address to Shipping Address
		c.SetCustomFieldByName("BI$ship_street", Street1);
		c.SetCustomFieldByName("BI$ship_street2", Street2);
		c.SetCustomFieldByName("BI$ship_city", c.AddrCity);
		c.SetCustomFieldByName("BI$ship_state", c.AddrProvId);
		c.SetCustomFieldByName("BI$ship_postalcode", c.AddrPostalCode);
		c.SetCustomFieldByName("BI$ship_country", c.AddrCountryId);
	}	

	/* Set Shipping to Billing - Default()
	This function automatically sets the shipping address to the billing address when an incident is saved and when the Ship Updated field is not Yes. This is the default function on the workspace since it cannot be done in business rules or workflow rules. 
	*/
	function onbeforesave(){
		var shiptoupdated = c.GetCustomFieldByName("BI$ship_updated");
		if (shiptoupdated != 1)
		{
			// Split the Street address into 2 pieces
			var streets = c.AddrStreet.split(/\r?\n/);
			var Street1 = streets[0];
			var Street2 = "";

			// Only set Street2 if it is not null
			if(streets[1] != null)
				Street2 = streets[1];
				
			// Copy Billing Address to Shipping Address
			c.SetCustomFieldByName("BI$ship_street", Street1);
			c.SetCustomFieldByName("BI$ship_street2", Street2);
			c.SetCustomFieldByName("BI$ship_city", c.AddrCity);
			c.SetCustomFieldByName("BI$ship_state", c.AddrProvId);
			c.SetCustomFieldByName("BI$ship_postalcode", c.AddrPostalCode);
			c.SetCustomFieldByName("BI$ship_country", c.AddrCountryId);
		}
	}
	
	</script>
</head>
<body>
<div>
<input type='button' id ='updateshipadd' value='Change Shipping' onclick='changeshipto()'/>
<input type='button' id ='sameasbilling' value='Same As Billing' onclick='sameasbilling()'/>
</div>
</body>
</html>