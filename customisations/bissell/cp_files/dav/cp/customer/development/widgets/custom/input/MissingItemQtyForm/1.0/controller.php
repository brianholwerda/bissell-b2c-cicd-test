<?php
namespace Custom\Widgets\input;

use RightNow\Connect\v1_3 as RNCPHP,
	\RightNow\Utils\Url,
	\RightNow\Utils\Config;

class MissingItemQtyForm extends \RightNow\Libraries\Widget\Base {
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
		if(isset($checkIncident))
		{
			$this->data['attrs']['IncidentExists'] = 1;
			return;
		}
		else
		{
			$this->data['attrs']['IncidentExists'] = 0;
		}
		
		//Don't need to get the contact information, so we can determine the email address on the quantity form.
		load_curl();
		$cURL = curl_init();
		
		$orderNumber = $orderNum;
		$consumerID = $consumerID;
		
		$url = Config::getConfig(CUSTOM_CFG_WDMR_ORDERHISTORYSCRIPT_URL);
		//$url = 'https://bissell-ext--tst1.custhelp.com/cgi-bin/bissell_ext.cfg/php/custom/wdmrorderhistory.php;
		$url .= '?action=getlineitemhistory';
		$url .= '&ConsumerID=' . $consumerID;
		$url .= '&OrderNumber=' . $orderNumber;

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
		//print_r($this->data['attrs']['itemFound']);
		//print("end getOrder <br>");
		
		//Don't need shipping on quantity form.
				
		//Don't need Product Price Information from OSVC on quantity form.	
		
		//Don't need  Part Information from OSVC on quantity form	

		//Don't need to Check the number of no charge orders for this customer in the past X days on quantity form
		
        return $this;


    }
}