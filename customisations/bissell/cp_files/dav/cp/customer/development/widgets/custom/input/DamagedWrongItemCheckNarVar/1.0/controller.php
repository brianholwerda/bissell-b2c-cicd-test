<?php
namespace Custom\Widgets\input;

use RightNow\Connect\v1_3 as RNCPHP,
	\RightNow\Utils\Url,
	\RightNow\Utils\Config;
 
// class ReturnItemForm extends \RightNow\Libraries\Widget\Base {
class DamagedWrongItemCheckNarVar extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
		
    }

    function getData() {
		
		//select * from PROD.PriceList LIMIT 25
		//select * from BUS.OrderHeader LIMIT 25
		//select Contact.CustomFields.c.consumer_id from contact where Contact.CustomFields.c.consumer_id = 1823785
		//select Contact.ID from contact where Contact.CustomFields.c.consumer_id = 12570682
		//select * from BUS.OrderHeader where Contact = 4534898 LIMIT 25
				
		$WDMRtype = Url::getParameter("t");
		$consumerID = Url::getParameter("c");
		$orderNum = Url::getParameter("o");
		$prodSku = Url::getParameter("s");  //need to find out if they are passing me product number or sku. Going to assume sku for now....
		$this->data['attrs']['WDI_type'] = Url::getParameter("wdi");
		
		$type = $WDMRtype;
		//$type = "WDI"; //Report Wrong Damaged Item
		//$type = "RI";//Return Item
		//$type = "MI"; //Missing Item
		//$consumerID = 18333699;
		//$orderNum = 37739948;
		//$prodSku = "2252";
		
		$this->data['attrs']['issueType'] = $type;
		$this->data['attrs']['consumerID'] = $consumerID;
		$this->data['attrs']['orderNum'] = $orderNum;
		$this->data['attrs']['prodSku'] = $prodSku;
		$this->data['attrs']['QuantityReceived'] = $qty;
		
		//Check to see if there's already an incident for this order number and sku
		$checkIncident = RNCPHP\Incident::first("CustomFields.WDMR.OriginalOrderNumber = '" . $orderNum . "' AND CustomFields.WDMR.ProductSKU = '" . $prodSku . "'");
		$query = "select ID from BUS.OrderLocks where OriginalOrderNumber = '" . $orderNum . "' and SKU = '" . $prodSku . "' LIMIT 1"; 
		//print($query);
		$getBUSLOCK = RNCPHP\ROQL::query($query)->next()->next();
		//print_r(isset($getBUSLOCK));
		
		if(isset($checkIncident) || isset($getBUSLOCK))
		{
			$this->data['attrs']['IncidentExists'] = 1;
			return;
		}
		else
		{
			$this->data['attrs']['IncidentExists'] = 0;
		}
		
		 
		
		//Get the contact information, so we can determine the email address.
		$Contact = RNCPHP\Contact::first("Contact.CustomFields.c.consumer_id = " . $consumerID );
		$c_id = $Contact->ID;
		$this->data['js']['ContactData'] = array('ContactData' => $Contact);
		$this->data['attrs']['contactID'] = $c_id;
		$contact = $c_id;
		$myCountry = $Contact->Address->Country->ID;
		if($myCountry == "")
			$myCountry = 1;
		$this->data['attrs']['emailAddr'] = $Contact->Emails[0]->Address;
		
		load_curl();
		$cURL = curl_init();
		
		$orderNumber = $orderNum;
		$consumerID = $consumerID;
		
		$url = Config::getConfig(CUSTOM_CFG_WDMR_ORDERHISTORYSCRIPT_URL);
		//$url = 'https://bissell-ext--tst1.custhelp.com/cgi-bin/bissell_ext.cfg/php/custom/wdmrorderhistory.php;
		$url .= '?action=getlineitemhistory';
		$url .= '&ConsumerID=' . $consumerID;
		$url .= '&OrderNumber=' . $orderNumber;

		//print($url);
		
		curl_setopt_array($cURL, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
					"OSVC_AUTHORIZATION: Basic UkVTVF9BQ0NPVU5UOlRpTDBWWlE4RlJJdmFFMG03N1hW"
					),
		));
		$result = json_decode(curl_exec($cURL));
		//print("here is result:");
		//print_r($result);
		//print("end");
		LogMessage(print_r($result, true));
		curl_close($cURL);

			
		// Navigate through each Line Item in the Order
		$lineItems = array();
		$xmlLineItem = $result->LineItems;
		//print("here is xmlLineItem:");
		//print_r($xmlLineItem);
		//print("end");
		//print(count($xmlLineItem));
		//print("end2");
        $subTotal = 0;
		// IF there are line items, we need to treat LineItem as an array and iterate through
		$this->data['attrs']['itemFound'] = "0";
		if(count($xmlLineItem) > 0)
		{
			//print("more than 1 line item");
			// Append each LineItem to the Order response array
			
			foreach($xmlLineItem as $lineItem)
			{
				//Find the line item that matches the SKU passed in, set useThisLine to that line item
				
				if($lineItem->PartNo == $prodSku) {
					//print_r($lineItem);
					$this->data['attrs']['itemFound'] = "1";
					$this->data['getOrder']['FriendlyProductName'] = $lineItem->FriendlyProductName;
					$this->data['getOrder']['Part #'] = $lineItem->PartNo; 
					$this->data['getOrder']['Part Price'] =  $lineItem->Price; 
					$this->data['getOrder']['Quantity'] = $lineItem->Qty; 
					$this->data['attrs']['OrderLineID'] = $lineItem->LineId;
					//if($lineItem->NarvarReturnEligible == "false" || $lineItem->NarvarReturnEligible == "" || $lineItem->NarvarReturnEligible == null)
					if($lineItem->NarvarReturnEligible != 'Y')
						$this->data['attrs']['isNarvarEligible'] = "N";
					else
						$this->data['attrs']['isNarvarEligible'] = "Y";
						
					 break;
			
				}
			}
		}

		// ELSE a single line item is returned for an Order, treat LineItem as a single object
		// WE don't need to check for single line item, we can just do > 0 above.  
		/*else
		{
			//print_r($xmlLineItem);
			$this->data['attrs']['itemFound'] = "1";
			$this->data['getOrder']['FriendlyProductName'] = $xmlLineItem->FriendlyProductName;
			$this->data['getOrder']['Part #'] = $xmlLineItem->PartNo; 
			$this->data['getOrder']['Part Price'] =  $xmlLineItem->Price; 
			$this->data['getOrder']['Quantity'] = $xmlLineItem->Qty; 
			$this->data['attrs']['OrderLineID'] = $xmlLineItem->LineId;
			//if($xmlLineItem->NarvarReturnEligible == "false" || $xmlLineItem->NarvarReturnEligible == "" || $xmlLineItem->NarvarReturnEligible == null)
			if($xmlLineItem->NarvarReturnEligible != 'Y')
				$this->data['attrs']['isNarvarEligible'] = "N";
			else
				$this->data['attrs']['isNarvarEligible'] = "Y";
			
			//print("only 1 line item");
		} */
		
		//print("getOrder = <br>");
		//print_r($this->data['getOrder']);
		//print("end getOrder <br>");
			
		
		//Format the shipping address for use in display
		$countryCode = $result->ShippingAddress->Country;
		//echo($countryCode);
		
		$zip = $result->ShippingAddress->Postal;
		$zipRegex = array(
		    "US" => "/^[0-9]{5}(?:-[0-9]{4})?$/i",
		    "CA" => "/^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/i",
		    "MX" => "/\d{5}/i"
		);
		 	
		if (array_key_exists($countryCode, $zipRegex))
		{
		    if (preg_match($zipRegex[$countryCode], $zip)) {
		        //echo "No need formatting for ". $zip;
		        $newzip = $zip;
		    } else {
			    $zipnum = preg_replace('/\D/', '', $zip);
				$newzip = substr($zipnum, 0, 5) . '-' . substr($zipnum, 5);
		        //echo "Formatted as " . $newzip;
		        //echo PHP_EOL;
		        // echo "Formatted as ". sprintf("%s-%s", substr($zip, 0, 5), substr($zip, -4));
		    }
		} else {
			$newzip = $zip;
		}
		$shippingAddr = $result->ShippingAddress->Street1 . ' ' . $result->ShippingAddress->Street2 . ' ' . $result->ShippingAddress->City . ', ' . $result->ShippingAddress->State . ' ' . $newzip;
		$this->data['attrs']['shippingAddr'] = $shippingAddr;
		
		//Get Product Price Information from OSVC	
		$query="select * from PROD.PriceList where Name = '" . $prodSku . "' and Country = " . $myCountry;
		//print($query);
		$getProdSkuPrice = RNCPHP\ROQL::query($query)->next()->next();
				
		$this->data['attrs']['reservableInventory'] = $getProdSkuPrice['QtyAvailable'];
		$this->data['attrs']['productPriceCurrent'] = (int) ceil($getProdSkuPrice['Price']);
		$this->data['attrs']['ActiveSKU'] = $getProdSkuPrice['Active'];
		
		
		//Get Part Information from OSVC	
		$query="select * from PROD.Part where ItemNumber = '" . $prodSku . "'"; 
		//print($query);
		$getProdSkuPart = RNCPHP\ROQL::query($query)->next()->next();
		//print_r('xxx' . $getProdSkuPart['HazardCode'] . 'yyy');
		//if($getProdSkuPart['ItemClassification'] === 'CHEMICAL' || $getProdSkuPart['HazardCode'] == '-' || $getProdSkuPart['HazardCode'] == '')
		if($getProdSkuPart['ItemClassification'] == 'CHEMICAL')
		
		{
			//echo("not hazardous");
			$this->data['attrs']['isHazardous'] = '1';
		} else {
			//echo("is hazardous");
			$this->data['attrs']['isHazardous'] = '0';
		}

		//Check the number of no charge orders for this customer in the past X days
		$numDays = Config::getConfig(CUSTOM_CFG_WDMR_ORDER_HISTORY_DAYS);
		//print_r($numDays);
		//echo PHP_EOL;
		//print_r($consumerID);
		
		$ReturnUrl = Config::getConfig(CUSTOM_CFG_WDMR_ORDERHISTORYSCRIPT_URL);
		//$ReturnUrl = 'https://bissell-ext--tst1.custhelp.com/cgi-bin/bissell_ext.cfg/php/custom/wdmrorderhistory.php';
		$ReturnUrl .= '?action=getnochargeorders';
		$ReturnUrl .= '&ConsumerID=' . $consumerID;
		$ReturnUrl .= '&Days=' . $numDays;
		// cURL call to BWS
		//load_curl();
		$cURL = curl_init();

		curl_setopt_array($cURL, array(
			CURLOPT_URL => $ReturnUrl,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
					"OSVC_AUTHORIZATION: Basic UkVTVF9BQ0NPVU5UOlRpTDBWWlE4RlJJdmFFMG03N1hW"
					),
		));
		$returnResult = json_decode(curl_exec($cURL));
		/* print("here is returnResult:");
		print_r($returnResult);
		print("end");
		LogMessage(print_r($returnResult, true)); */
		curl_close($cURL);
		
		$this->data['attrs']['NoChargeOrderCount'] = $returnResult->NoChargeOrderCount;
		$this->data['attrs']['RefundCount'] = $returnResult->RefundCount;
		
		//SET TEST DATA AS NEEDED HERE
		//$this->data['attrs']['isNarvarEligible'] = 'N';
		//$this->data['attrs']['ActiveSKU'] = 'Y';
		//$this->data['attrs']['isHazardous'] = '1';
		
        return $this;

    }
}