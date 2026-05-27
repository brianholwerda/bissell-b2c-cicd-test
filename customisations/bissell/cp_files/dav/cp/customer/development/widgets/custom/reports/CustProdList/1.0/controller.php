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
		if($product_id) 
		{
			//$prod_arr = ConnectPHP\EXT_PROD\CustProdList :: find('SVC_PROD_ID = '.getUrlParm("p"));
			$this->data['results'] =  $this->CI->model('custom/CustProdList_model')->getProduct(getUrlParm("p"));
			
			//print_r($this->info['p_id']);
			//print_r($this->data['results']);
       }
       
    }

}