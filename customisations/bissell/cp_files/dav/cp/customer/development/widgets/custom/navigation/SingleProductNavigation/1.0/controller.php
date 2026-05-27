<?php
namespace Custom\Widgets\navigation;

class SingleProductNavigation extends \RightNow\Widgets\Multiline {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
                
        parent::getData();
        
        if (count($this->data['reportData']['data']) == 1) {  
            
            
            //print_r($this->data['reportData']['data']);
           	//print("location: /app/products/detail/p/" . $this->data['reportData']['data'][0][2] . "/kw/" . $this->data['reportData']['data'][0][0]);
            $header_string = "location: /app/products/detail/p/" . $this->data['reportData']['data'][0][2] . "/kw/" . $this->data['reportData']['data'][0][0];
            
            header($header_string); 
           
        }
        
        return;

    }                                              
                                                    

    /**
     * Overridable methods from Multiline:
     */
    // function showColumn($value, array $header)
    // function getHeader(array $header)
}