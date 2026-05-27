<?php
namespace Custom\Models;
use RightNow\Connect\v1_2 as RNCPHP;

class Breadcrumb extends \RightNow\Models\Base
{
    function __construct()
    {
        parent::__construct();
    }

    function sampleFunction()
    {
        /**
         * This function can be executed a few different ways depending on where it's being called:
         *
         * From a widget or another model: $this->CI->model('custom/Sample')->sampleFunction();
         *
         * From a custom controller: $this->model('custom/Sample')->sampleFunction();
         * 
         * Everywhere else: $CI = get_instance();
         *                  $CI->model('custom/Sample')->sampleFunction();
         */
    }
    function getServiceCategory($id) {
        $sql = sprintf("SELECT ServiceCategory FROM ServiceCategory WHERE ID = %s", $id);
        $sub_links = RNCPHP\ROQL::queryObject($sql)->next();
        
        $ret = array();
        while ($sub_link = $sub_links->next()) {
            $ret[] = array("ID" => $sub_link->ID, "Name" => $sub_link->Name);
        }
        return $ret;
    }
    
    function getAnswerInfo($id) {
        $ans = RNCPHP\Answer::fetch( $id );
        return $ans;
    }
}
