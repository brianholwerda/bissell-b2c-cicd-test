<?php
namespace Custom\Widgets\navigation;

class Breadcrumbs extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
        $this->getUrlData();
        return parent::getData();

    }
    
    function getUrlData() {  
        //Perform php logic here
        $ret = array();
        $i = 0;
        $url_c = getUrlParm('c');
        $a_id = getUrlParm('a_id');
        $previous_c = "";
        if ($url_c) {
            $cat = explode(",", $url_c);
            foreach ($cat as $c) {
                $this->CI->load->model('custom/Breadcrumb');
                $tmp = $this->CI->Breadcrumb->getServiceCategory($c);
                if ($tmp) {
                    $ret[$i] = array("url" => $previous_c . $c, "Name" => $tmp[0]['Name']);
                    
                    if (substr($ret[$i]['url'], strlen($ret[$i]['url']) - 1, 1) == ",") {
                        $ret[$i]['url'] = substr($ret[$i]['url'], 0, -1);
                    }
                }

                $previous_c .= $c . ',';
                $i++;
            }
            if (strlen($previous_c) > 0) {
                $tmp = substr($previous_c, 0, -1);
                $previous_c = $tmp;
            }
            setcookie("previous_c", $previous_c, 0, "/");
        }
        if ($a_id) {
            $previous_cookie_c = "";
            /* if ($_COOKIE["previous_c"]) {
                $previous_cookie_c = $_COOKIE["previous_c"];
            } */

            $this->CI->load->model('custom/Breadcrumb');

            $tmp = $this->CI->Breadcrumb->getAnswerInfo($a_id);
            $first_cat = "";
            //print_r($tmp);
            if(is_array($tmp->Categories) || is_object($tmp->Categories)){
                foreach ($tmp->Categories as $t) {
                    $a_cat = "";
                    $i = 0;

                    foreach ($t->CategoryHierarchy as $ch) {
                        $a_cat .= $ch->ID . ',';
                        $ret[$i] = array("url" => $a_cat, "Name" => $ch->Name);
						
						if($ret[$i]['Name']=='Troubleshooting') {
							//echo "here1";
							//$i++;
							$isTrouble = 1;
							break;
						} else {
							$i++;
						}
                        
                    }
                    $a_cat .= $t->ID;

				   //print_r(rtrim($ret[$i]['url'],','));
                    $ret[$i] = array("url" => rtrim($ret[$i]['url'],',') , "Name" => $ret[$i]['Name']);

					
					if($ret[$i]['Name']=='Troubleshooting' || $isTrouble === 1) {
						//echo "here2";
						//$i = $i - 1;
						//print_r($ret[$i]);
						$isTrouble = 1;
					} else {
						//echo 'not trouble';
						for ($i = 0; $i < count($ret); $i++) {
		                    if (substr($ret[$i]['url'], strlen($ret[$i]['url']) - 1, 1) == ",") {
	                            $ret[$i]['url'] = substr($ret[$i]['url'], 0, -1);
	                        }
	                    }
					}

					//print_r($ret[$i]['Name']);
                    

                    if (strlen($first_cat == 0)) {
                        $first_cat = $a_cat;
                    }
                    
                    if ($a_cat == $previous_cookie_c) {
                        $this->data['js']['a_id'] = $a_id;
                        $this->data['js']['a_name'] = $tmp->Summary;
                        //echo "here4";
                        //print_r($tmp->Summary);
                        break;
                    } else {
                        $this->data['js']['a_id'] = $a_id;
                        $this->data['js']['a_name'] = $tmp->Summary;
                        //echo "here5";
                        //print_r($tmp->Summary);
                        if($isTrouble == 1)
                        	break;
                    }
                }
            }
        }

        $page_url = array();
        if (!$url_c && !$a_id)
		{
            $pages = explode("/", $_SERVER["REQUEST_URI"]);
			$prevWasTheme = false;//used to not include THEME or its variable.  2014/9/10
            $prevWasntRedirect = true;
            foreach ($pages as $p) 
			{ 
				
                if ( $p != "app" && $p != "home" && $p != 'kw' && $p != 'session' && $p != 'search' && $p != 'theme' && $prevWasntRedirect && $p != 'redirect') 
				{             
                    if ($p == 'survey_comp_id' || $p == 'chat_data' || $p == 'list' || $p == 'session' || $p == 'kw' || $p == 'search' || $p == 'customer ser' || $prevWasTheme) 
					{
                        
                        break;
                    }
					elseif (strlen(trim($p)) > 0)
					{
                        $page_url[] = ucfirst(str_replace('_', ' ', $p));
                    }
                }
                if( $p == 'session')
					break;
					
				if( $p == 'theme')
					$prevWasTheme = true;
				else
					$prevWasTheme = false;
                
                if( $p === 'redirect'){
                    $prevWasntRedirect = false;
                }
                else {
                    $prevWasntRedirect = true;
                }
                
				if(  $p == 'ask' )
					break;
			}
			//printf("cur url: " . $_SERVER["REQUEST_URI"]);
			//print_r($page_url);
        }
        $this->data['js']['page_url'] = $page_url;
        $this->data['js']['links'] = $ret;
    }
    
}