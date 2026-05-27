<?php
namespace Custom\Widgets\reports;
use RightNow\Utils\Url,
    RightNow\Utils\Text,
    RightNow\Utils\Config,
    RightNow\Utils\Connect,
    RightNow\Utils\Framework,
    RightNow\Connect\v1_3 as ConnectPHP;

class CustProdList extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->info['p_id'] = getUrlParm("p");
    }

    function getData() {
		$product_id = $this->info['p_id'] ;
		$this->data['attrs']['foundArticles'] = "NO";
		//print($product_id);
		if($product_id)
		{
			//$prod_arr = ConnectPHP\EXT_PROD\CustProdList :: find('SVC_PROD_ID = '.getUrlParm("p"));
			$this->data['results'] =  $this->CI->model('custom/CustProdList_model')->getProduct(getUrlParm("p"));


			//Get Warranty Information from OSVC	
			$query="select * from PROD.Part where ItemNumber = '" . $this->data['results']['SKU'] . "'"; 
			//print($query);
			$getProdSkuPart = ConnectPHP\ROQL::query($query)->next()->next();
			//print_r($getProdSkuPart['Warranty']);
			if($this->data['attrs']['referrer'] == 'US') {
				switch ($getProdSkuPart['Warranty']) {
				    case 'L90':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/90%20Day%20Limited%20Warranty.pdf";
				        break;
				    case 'L1Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/1%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L2Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/2%20Year%20Limited%20Warranty.updated.pdf";
				        break;
				    case 'L3Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/3%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L4Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/4%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L5Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/5%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'LLT':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/US/Lifetime%20Warranty.pdf";
				        break;
				    default:
				    	$this->data['attrs']['Warranty'] = "";
				        break;
				}
			} else {
				switch ($getProdSkuPart['Warranty']) {
				    case 'L1Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/CA/Canada%201%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L2Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/CA/Canada%202%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L3Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/CA/Canada%203%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L4Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/CA/Canada%204%20Year%20Limited%20Warranty.pdf";
				        break;
				    case 'L5Y':
				        $this->data['attrs']['Warranty'] = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Warranty%20Guides/CA/Canada%205%20Year%20Limited%20Warranty.pdf";
				        break;
				    default:
				    	$this->data['attrs']['Warranty'] = "";
				        break;
				}
			}
			//$this->data['attrs']['Warranty'] = $getProdSkuPart['Warranty'];
			
			//Get the links for the Troubleshooting Categories
			$format = array(
	            'truncate_size' => $this->data['attrs']['truncate_size'],
	            'max_wordbreak_trunc' => $this->data['attrs']['max_wordbreak_trunc'],
	            'emphasisHighlight' => $this->data['attrs']['highlight'],
	            'dateFormat' => $this->data['attrs']['date_format'],
	            'urlParms' => \RightNow\Utils\Url::getParametersFromList($this->data['attrs']['add_params_to_url']),
	        );
	        $filters = array('recordKeywordSearch' => true);
        

			$ReportIds = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_PROD_TROUBLESHOOT_REPORTS);
			//$ReportIds = '115869,115870';
			$array = explode(',', $ReportIds); //split string into array seperated by ', '
			foreach($array as $value) //loop over values
			{
			    //echo $value . PHP_EOL; //print value

				$this->data['attrs']['report_id'] = $value;
		        $reportToken = \RightNow\Utils\Framework::createToken($this->data['attrs']['report_id']);
		        \RightNow\Utils\Url::setFiltersFromAttributesAndUrl($this->data['attrs'], $filters);
		        $results = $this->CI->model('Report')->getDataHTML($this->data['attrs']['report_id'], $reportToken, $filters, $format)->result;
		        if ($results['error'] !== null) {
		            echo $this->reportError($results['error']);
		        }

		        for($i=0; $i<2; $i++) {
			        if($results['data'][$i] != "") {
				    	$this->data['attrs']['foundArticles'] = "YES";  
			        }
					$this->data['reportData'][$this->data['attrs']['report_id']][$i] = $results['data'][$i];
				}
					//print_r($this->data['reportData']);
		    }
	    }

    }

}