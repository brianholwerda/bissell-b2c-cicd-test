<html>
<head>
<title>Custom Validation</title>

<script type="text/javascript">

function onbeforesave()
{
	var CID = 0;
	var contact = window.external.Contact;
	var Street = contact.AddrStreet;
	var City = contact.AddrCity;
	var Country = contact.AddrCountryID;
	var Province = contact.AddrProvId;
	var PostalCode = contact.AddrPostalCode;
	
	if (contact.Id != CID && Street != null && City != null && Country != null && Province != null && PostalCode!= null)
    	{
        		window.external.beforesavecomplete(true);
    	}        
    	else
    	{   
        		window.external.beforesavecomplete(false, "SAVE ABORTED!\n\nYou must complete all of the customer's address fields in order to save a coupon.");            
    	}   
}

</script>

</head>
<body>    
</body>
</html>