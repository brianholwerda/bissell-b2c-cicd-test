<?php
namespace Custom\Models;
use RightNow\Api,
    RightNow\Utils\Config,
    RightNow\Utils\Text,
    RightNow\Utils\Framework,
    RightNow\Connect\v1_2 as Connect,
    RightNow\Utils\Connect as ConnectUtil;

class CustProdList_model extends \RightNow\Models\Base  
{
	function __construct()
    {
        parent::__construct();
        //This model is loaded by using $this->load->model('Epsilon_webservice_model');
    }

	function getProduct($p_id)
    {
         // Get the product  (all values matching the product ID passed in)

        $productsArray = array();
        try{
            $products = Connect\ROQL::query("SELECT * FROM EXT_PROD.CustProdList where SVC_PROD_ID = " . $p_id)->next();
            //print_r($products->next());
            
        }
        catch(Connect\ConnectAPIErrorBase $e){
            return $this->getResponseObject(null, null, $e->getMessage());
        }
        return $products->next();//$this->getResponseObject($productsArray);
    }
}    
?>
