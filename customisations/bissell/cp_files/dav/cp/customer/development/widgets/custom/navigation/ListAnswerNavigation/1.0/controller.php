<?php
namespace Custom\Widgets\navigation;

class ListAnswerNavigation extends \RightNow\Widgets\Multiline {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
        $this->data['attrs']['defining_param'] = ($this->data['attrs']['defining_param']) ? $this->data['attrs']['defining_param'] : 'a_id'; 
        $this->data['attrs']['column_index'] = ($this->data['attrs']['column_index']) ? $this->data['attrs']['column_index'] : 0; 
        $this->data['attrs']['forward_on_ajax'] = ($this->data['attrs']['forward_on_ajax'] === 'true') ? true : false; 
        
        $url_param = $this->data['attrs']['defining_param'];
        $column_index = $this->data['attrs']['column_index'];
        
        parent::getData();
        
        if (count($this->data['reportData']['data']) == 1) {  
            $link = $this->data['reportData']['data'][0][$column_index];
            
            $parameter_key_start = strpos($link, $url_param.'/');
            $parameter_key_start += strlen($url_param.'/');
            
            $parameter_key_end = strpos($link, '"', $parameter_key_start); 
            
            if(!$parameter_key_end){
                
                $parameter_key_end = strpos($link, "'", $parameter_key_start); 
                
                if(!$parameter_key_end){
                    
                        $parameter_key_end = strpos($link, '/', $parameter_key_start);
                }
            }
            $parameter_value = substr($link, $parameter_key_start, $parameter_key_end - $parameter_key_start);
            if ($parameter_value != "") {
            	//print("location: /app/answers/detail/a_id/" . $a_link ."/p/" . getUrlParm("p"));
                $header_string = "location: /app/answers/detail/".$url_param."/" . $parameter_value;
                /*
                if(getUrlParm("c")) {this.data.js.format.urlParms
                    $header_string .= "/c/" . getUrlParm("c");
                }
                if(getUrlParm("p")) {
                    $header_string .= "/p/" . getUrlParm("p");
                }
                if(getUrlParm("kw")) {
                    $header_string .= "/kw/" . getUrlParm("kw");
                }
                */
                
                header($header_string); 
            }
        }
        
        return;

    }                                              
                                                    

    /**
     * Overridable methods from Multiline:
     */
    // function showColumn($value, array $header)
    // function getHeader(array $header)
}