<?php
namespace Custom\Widgets\reports;

class ListAnswerNavigation extends \RightNow\Widgets\Multiline {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
        parent::getData();
        //printf("<pre>");
        //print_r($this->data['reportData']);
        //print_r(getUrlParm("p"));
        //printf("</pre>");
        if (count($this->data['reportData']['data']) == 1) {
	        //print_r($this->data['reportData']['data'][0][0]);  
	        //print(count($this->data['reportData']['data'][0]));   
	        //Get the last field from the returned data
	        $reportLength = count($this->data['reportData']['data'][0]) -1;
            $a_link = $this->data['reportData']['data'][0][$reportLength];

			//print_r($matches);
            if ($a_link != "") {
	            //print_r($a_link);
	            $a_id_begins = strpos($a_link, '/a_id/') + strlen('/a_id/'); // <a%20href='/app/answers/detail/a_id/3947/kw/115'>QA%
	            $a_id_length = strpos(substr($a_link, $a_id_begins), '/');
	            //$a_id = substr($a_link, $a_id_begins, $a_id_length);
	            $a_id = $this->data['reportData']['data'][0][$reportLength];
	            //print($a_id);
            	//print("location: /app/answers/detail/a_id/" . $a_id ."/p/" . getUrlParm("p"));
                header("location: /app/answers/detail/a_id/" . $a_id ."/p/" . getUrlParm("p"));
                
                return;
            }
        }
        return;
//        return parent::getData();

    }

    /**
     * Overridable methods from Multiline:
     */
    // function showColumn($value, array $header)
    // function getHeader(array $header)
}