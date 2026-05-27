<!--
/////////////////////////////////////////////////////////////////////////////
// Copyright © 2006-2014, Oracle and/or its affiliates. All rights reserved.
// Sample code provided for training purposes only.  This sample code is
// provided "as is" with no warranties of any kind express or implied.
/////////////////////////////////////////////////////////////////////////////
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Custom Integration Page</title>
</head>
<body onload='GetUrlParms()'>
<form action="#" name="data" id="data">
<table>
  <tr><td colspan="2" align=center>Contact Info:</td></tr>
  <tr>
    <td>First Name:</td>
    <td><input type='text' readonly="readonly" name='cfname' /></td>
    <td><input type='button' value='Populate Contact Info' onclick='popContact()' /></td>
  </tr>
  <tr>
    <td>Last Name:</td>
    <td><input type='text' readonly="readonly" name='clname' /></td>
  </tr>
  <tr>
    <td>Email:</td>
    <td><input type='text' readonly="readonly" name='cemail' /></td>
  </tr>
  <tr>
    <td>ID:</td>
    <td><input type='text' readonly="readonly" name='cid' /></td>
  </tr>
  <tr><td height=40 colspan="2" valign=bottom align=center>Billing Address:</td></tr>
  <tr>
    <td>Street:</td>
    <td><input type='text' name='street' /></td>
    <td><input type='button' value='Populate Address' onclick='popAddr()' /></td>
  </tr>
  <tr>
    <td>City:</td>
    <td><input type='text' name='city' /></td>
    <td><input type='button' value='Update Address' onclick='upAddr()' /></td>
  </tr>
  <tr>
    <td>Province/State:</td>
    <td><input type='text' name='province' /></td>
  </tr>
  <tr>
    <td>Postal Code:</td>
    <td><input type='text' name='zip' /></td>
  </tr>
  <tr>
    <td>Country:</td>
    <td><input type='text' name='country' /></td>
  </tr>
  <tr><td height=40 colspan="2" valign=bottom align=center>Custom Fields/System Attributes:</td></tr>
  <tr>
    <td>String CF:</td>
    <td><input type='text' name='cfstr' /></td>
    <td><input type='button' value='Get' onclick='popStrCF()' />&nbsp;
        <input type='button' value='Set' onclick='upStrCF()' /></td>
  </tr>
  <tr>
    <td>Int CF:</td>
    <td><input type='text' name='cfint' /></td>
    <td><input type='button' value='Get' onclick='popIntCF()' />&nbsp;
        <input type='button' value='Set' onclick='upIntCF()' /></td>
  </tr>
  <tr>
    <td height=40 colspan="2" valign=bottom><input type='checkbox' name='alerts' checked='true' />Confirmation Messages</td>
  </tr>
</table>
</form>
<script type="text/javascript">

/* CUSTOM FIELD names
Note: You must change these custom field names to names of valid custom fields
on your site.
*/
var STR_CF_NAME = "c$text1"; // String Custom Field, e.g. "c$text1"
var INT_CF_NAME = "c$int1";  // Integer Custom Field, e.g. "c$int1"

/* onbeforesave(Boolean continue, String Msg)
Executed when a save has been requested, but before the save is executed.
Param 1: Boolean - If true, continue saving. If false, cancel the save.
Param 2: String  - The message that is dispalyed to the user.
*/
function onbeforesave()
{
    // prompt the user and capture their response
    var success = true;
    if (data.alerts.checked)
        success = confirm("save ok?");
    // execute the response
    window.external.beforesavecomplete(success, "beforesavecomplete message");
}

/* onsave(Boolean continue, String Msg)
Executed after a save is executed. This is mainly used to display a message
to the user after a save operation.
Param 1: Boolean - Ignored.
Param 2: String  - The message that is dispalyed to the user.
*/
function onsave()
{
    // display the same complete message
    window.external.savecomplete(true, "save complete message");
}

/* onclose(Boolean continue, String Msg)
Executed when a close is requested. This is mainly used to display a message
to the user after a close operation.
Param 1: Boolean - Ignored.
Param 2: String  - The message that is dispalyed to the user.
*/
function onclose()
{
    // display the same complete message
    window.external.closecomplete(true, "close complete message");
}

/* oncancel(Boolean continue, String Msg)
Executed when a close has been requested, but before the closed is executed.
Param 1: Boolean - If true, continue closing. If false, cancel the close.
Param 2: String  - The message that is dispalyed to the user.
*/
function oncancel()
{
    // prompt the user and capture their response
    var success = true;
    if (data.alerts.checked)
        success = confirm("close without saving?");
    // execute the response
    window.external.cancelcomplete(success, "cancelcomplete message");
}

var currentCId = window.external.Incident.CId;
/* ondataupdated(String ObjName)
Executed when data is updated in the current workspace.
Param 1: String - The name of the object that was updated.
Valid objects are: Contact, Org, Opportunity, Incident.
*/
function ondataupdated(obj)
{
    if (obj == "Incident") // Modified object = Incident?
    {
        var i = window.external.Incident;
        if (currentCId != i.CId) // Did the actual contact change?
        {
            currentCId = i.CId;
            popAllContactFields();
        }
    }
}

/* popAllContactFields()
Populates all the contact fields from the current contact.
*/
function popAllContactFields()
{
    var c = window.external.Contact;

    if (currentCId != c.Id) // New contact may not have been loaded yet
        setTimeout("popAllContactFields()", 100);
    else
    {
        // populate the contact info fields
        popContact();
        // populate the address fields
        popAddr();
        // populate the custom fields
        popIntCF();
        popStrCF();
    }
}

/* popContact()
Populates the contact info fields from the current contact.
*/
function popContact()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
    {
        alert("null contact");
        return;
    }

    // set the contact fields
    data.cfname.value = c.FirstName;
    data.clname.value = c.LastName;
    data.cemail.value = c.EmailAddr;
    data.cid.value = c.Id;
}

/* popAddr()
Populates the address fields from the current contact.
*/
function popAddr()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
    {
        alert("null contact");
        return;
    }

    // get the street address and convert newlines into ', '
    var street = c.AddrStreet;
    if (street)
        street = street.replace(/\n/g, ", ");

    // set the address fields
    data.street.value = street ? street : "";
    data.city.value = c.AddrCity ? c.AddrCity : "";
    data.country.value = getNameFromId("AddrCountryId", c.AddrCountryId);
    data.province.value = getNameFromId("AddrProvId", c.AddrProvId);
    data.zip.value = c.AddrPostalCode ? c.AddrPostalCode : "";
}

/* getNameFromId()
Returns the Name of a list item based on the Id
*/
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

/* upAddr()
Updates the address fields in the contact object.
*/
function upAddr()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
    {
        alert("null contact");
        return;
    }

    var street = data.street.value;
    var city = data.city.value;
    var zip = data.zip.value;

    // replace the ', ' with a newline
    street = street.replace(/, /g, "\n");

    // populate the fields
    if (street != null)
        c.AddrStreet = street;

    if (city != null)
        c.AddrCity = city;

    var a = getIdFromName("AddrProvId", data.province.value);
    c.AddrProvId = a;
    c.AddrCountryId = getIdFromName("AddrCountryId", data.country.value);

    if (zip != null)
        c.AddrPostalCode = zip;
}

/* getNameFromId()
Returns the Id of a list item based on the Name
*/
function getIdFromName(list, name)
{
    var c = window.external.Contact;
    var xml = c.GetNameValues(list);

    var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
    xmlDoc.async = "false";
    xmlDoc.loadXML(xml);

    var x = xmlDoc.getElementsByTagName("Item");
    for (i = 0; i < x.length; i++)
    {
        var attlist = x.item(i).attributes;
        var att = attlist.getNamedItem("Name");
        if (att.value == name)
            return parseInt(attlist.getNamedItem("Id").value);
    }

    return -1;
}

/* popStrCF()
Populate the string custom field fields from the current contact.
NOTE: STR_CF_NAME must match the site's custom field name.
*/
function popStrCF()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
        alert("null contact");
    else
    {
        var newStr = c.GetCustomFieldByName(STR_CF_NAME);
        data.cfstr.value = newStr ? newStr : "";
    }
}

/* popIntCF()
Populate the integer custom field fields from the current contact.
NOTE: INT_CF_NAME must match the site's custom field name.
*/
function popIntCF()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
        alert("null contact");
    else
        data.cfint.value = c.GetCustomFieldByName(INT_CF_NAME);
}

/* upStrCF()
Update the string custom field fields in the contact object from the form field.
NOTE: STR_CF_NAME must match the site's custom field name.
*/
function upStrCF()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
        alert("null contact");
    else
        c.SetCustomFieldByName(STR_CF_NAME, data.cfstr.value);
}

/* upIntCF()
Update the integer 'custom field' field in the contact object from the form field.
NOTE: INT_CF_NAME must match the site's custom field name.
*/
function upIntCF()
{
    var c = window.external.Contact;
    if (c == null || c.Id == 0)
        alert("null contact");
    else
    {
        var intVal = parseInt(data.cfint.value);
        c.SetCustomFieldByName(INT_CF_NAME, isNaN(intVal) ? 0 : intVal);
    }
}

/* GetUrlParms()
Reads the URL and creates an associative array of key->value.
Example: if the url contained http://server/page.html?c_id=123&i_id=456
Then arg["c_id"] evaluates to 123 and arg["i_id"] evaluates to 456.
*/
function GetUrlParms()
{
    // create the global arg array
    arg = new Array();

    // get the url
    var theUrl = window.location.href;

    // split the url to get the argument string
    var args = theUrl.split("?")[1];

    // if the argument string is not null, we can parse it
    if (args != null)
    {
        // split the argument string by & to get key=val strings
        args = args.split("&");

        // loop through the key=val strings
        for (i = 0; i < args.length; i++)
        {
            // get the key and val
            key = args[i].split("=")[0];
            val = args[i].split("=")[1];
            // put the val into the arg associative array at key
            arg[key] = val;
        }
    }
    else
    {
        // there were no arguments in the url
    }
}

//]]>
</script>
</body>
</html>
