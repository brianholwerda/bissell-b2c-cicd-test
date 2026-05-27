<?php
namespace Custom\Widgets\navigation;
use \RightNow\Utils\Url,
    \RightNow\Utils\Text,
    \RightNow\Utils\Config,
    \RightNow\Utils\Framework,
    \RightNow\Connect\v1_3 as Connect;
    
class TrendingProductDisplay extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }
    
    function getData() {
		
		//print($this->data['attrs']['product_list_config']);	
		//$sql = "SELECT * FROM EXT_PROD.CustProdList WHERE EXT_COUNTRY_CD = " . $this->data['attrs']['country_cd'] . " AND SVC_PROD_ID IN (" . $this->data['attrs']['product_list_config'] . ")";
		//print($sql);
	
		$ExternalConnectedProducts = Connect\ROQL::query("SELECT * FROM EXT_PROD.CustProdList WHERE EXT_COUNTRY_CD = " . $this->data['attrs']['country_cd'] . " AND SVC_PROD_ID IN (" . $this->data['attrs']['product_list_config'] . ")")->next();

		$externalConnectedProductArray = [];	
		while($nextExternalProduct = $ExternalConnectedProducts->next()) {
			array_push($externalConnectedProductArray, $nextExternalProduct);
			//print_r($nextExternalProduct);
			//print('<br/><br/>');
	
		}	
	
		$this->data['js'] = array(
                'items'              => $externalConnectedProductArray,
                'appendedParameters' => Url::getParametersFromList($this->data['attrs']['add_params_to_url']) . Url::sessionParameter(),
            );
		 
		//https://bissell--tst1.custhelp.com/app/products/detail/p/67216/~/barkbath%E2%84%A2-portable-dog-grooming-bathing-system-1st-gen-1844
		//SVC_PROD_ID
		//PRODUCT_TITLE
		//SKU
		//<img src="https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/US%20Product%20Pages/150X150/1844.jpg" alt="">

		return $this;
    }
        /**
     * Overridable methods from VisualProductCategorySelector:
     */    // function limitItems($items, $limitTo, $maxLimit)    // protected function filterItemIDs($items, $limitTo)    // function($item) use ($limitTo)    // protected function limitToMaxSize($items, $maxLimit)    // function getSubItems($id, $filter, $linking)
}