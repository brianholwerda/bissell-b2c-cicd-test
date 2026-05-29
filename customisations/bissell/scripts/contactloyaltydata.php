<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script type="text/javascript">
	
		// Fetch current contact object
		var contact = window.external.Contact;
		
		loyaltyLookup(contact, loyaltyLookupCallback);

		function loyaltyLookup(con, callbackFunction) {
			
		// Get agent session parameter to valid against AgentAuthenticator
		var sid = '<?php echo $_GET['sid'] ?>';
		
			$.ajax({
				type: 'POST',
				url: 'loyaltydata.php',
				data: { Email:con.EmailAddr, sid:sid},
				success: function (data) {
					callbackFunction(data);
				},
				dataType: "json",
				error: function (status, error) {
					alert("Unable to retrieve customer loyalty data, please contact an Administrator.");
				}
			});
		}

		// Set loyalty points and loyalty rank NamedID based on BWS response
		function loyaltyLookupCallback(data) {
			
			if(data['ResponseCode'] == 'OK')
			{
				contact.SetCustomFieldByName("BI$loyalty_points", parseInt(data['TotalPoints']));
				contact.SetCustomFieldByName("BI$loyalty_level", data['LoyaltyRank']);
			}
			
			else
			{
				contact.SetCustomFieldByName("BI$loyalty_points", 0);
				contact.SetCustomFieldByName("BI$loyalty_level", "Non-Loyalty Member");
				
				// If a CURL Error code is returned, display an error message the cURL loyalty data fetch failed
				if(!isNaN(parseInt(data['ResponseCode'])))
					alert("Unable to retrieve customer loyalty data, please contact an Administrator.");
			}
		}

    </script>
</head>