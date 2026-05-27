<?php
namespace Custom\Widgets\display;

use RightNow\Utils\Text,
    RightNow\Utils\Url,
    RightNow\Utils\Config;

require_once(get_cfg_var("doc_root")."/include/ConnectPHP/Connect_init.phph");
initConnectAPI();

class Parse_Url extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData()
    {
        // ---------------------------------------------
        // Grab Third Party Header/Footer/HTML. Get 
        // new code every X hours and place into tmp
        // file. Check if file exists and whether it 
        // has expired.
        // ---------------------------------------------

        //Get attributes from widget call
        $url = $this->data['attrs']['url'];
        $filename = $this->data['attrs']['file_name'];
        $frequency = $this->data['attrs']['frequency'];
        
        $split_me = $this->data['attrs']['split_me'];
		$header_or_footer = $this->data['attrs']['header_or_footer'];
		$top_half = $this->data['attrs']['top_half'];
		$parse_me = $this->data['attrs']['parse_me'];

        //Set today's date
        $today = date('Y-m-d g:i a');
        $currentTime = time($today);

        //Check every X hours
		$freqAdjuster =  (3600 * $frequency);
 
        if (file_exists($filename)) {				
			$deltaFileTimes = $currentTime - filemtime($filename);
            if ($deltaFileTimes > $freqAdjuster) {  
				//echo "Current file time is " . gmdate("Y-m-d\TH:i:s\Z",filemtime($filename)) . " Compared to " . gmdate("Y-m-d\TH:i:s\Z", $timeMinusFrequency) . "<br>";
                $result = $this->getHTML($url,$filename);
             }
            else {
                $result = $this->getFile($filename);
                //echo "Exists and still good. Return file";
            }
        }
        else {
            $result = $this->getHTML($url,$filename);
            //echo "Doesn't exist. Grab new HTML and create file";
        }
 
		/* Does the file need to be split in two? */
		//split file
		if (!is_null($split_me) && $split_me == "true")
		{
			//header file
			if (!is_null($header_or_footer) && $header_or_footer == "header")
			{
				//make sure there isn't a commented footer CS Container
				$result = preg_replace('/<!--(.*)-->/Uis', '', $result);
				
				//add breakpoint to split header
				$result = preg_replace('@header\-styleAdvisorContainer\">@', 'header-styleAdvisorContainer">hlx_breakpoint', $result);
				
				//split the output to put a ConditionalChatLink inside.
				$header_halves = explode("hlx_breakpoint", $result);
				
				//top half
				if (!is_null($top_half) && $top_half == "true")
				{
					//echo the HTML
					echo $header_halves[0];
				}
				//bottom half
				else
				{
					//echo the HTML
					echo $header_halves[1];
				}
			}
			//footer file
			else if (!is_null($header_or_footer) && $header_or_footer == "footer")
			{
				//make sure there isn't a commented footer CS Container
				$result = preg_replace('/<!--(.*)-->/Uis', '', $result);
				//echo "removed comments results = ";
				//echo $result;
				
				//add breakpoint to split header
				$result = preg_replace('@footer\-CSContainer\"\>@', 'footer-CSContainer">hlx_breakpoint', $result);
				
				//split the output to put a ConditionalChatLink inside.
				$footer_halves = explode("hlx_breakpoint", $result);
				//echo count($footer_halves);
				
				//top half
				if (!is_null($top_half) && $top_half == "true")
				{
					//echo "in footer top";
					//echo the HTML
					echo $footer_halves[0];
				}
				else
				{
					//bottom half
					//echo "in footer bottom";
					//echo "<br>element 1 = ";
					echo $footer_halves[1];
					
				}
			}
		}
		//do not split file
		else
		{
			//echo the HTML
			echo $result;
		}

        //Echo the HTML
        // echo $result;
    }

    function getHTML($url,$filename) 
    {
        // ---------------------------------------------
        // Curl call to IP or url
        // Save HTML in tmp/$filename
        // ---------------------------------------------
   
		if(!extension_loaded('curl'))
            \load_curl();
        
        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        //echo $url;
        $result = \curl_exec($ch);
                
        if (curl_error($ch))
			die(curl_error($ch));

		// Get the status code
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $status;
		
        \curl_close($ch);
		$consumedURL = $url; //Config::getConfig(CUSTOM_CFG_CONSUMED_URL);
		
		//Replace HTML as necessary (relative paths, bad code, override styles...etc)
       // $result = preg_replace('@\/assets\/styles\/global.min.rio.css@', '', $result);
        //$result = preg_replace('@\/assets/styles/print.min.css@', '', $result);
 /*		$result = preg_replace('/\"\/assets\/scripts\//', $consumedURL . '/assets/scripts/', $result);
		$result = preg_replace('/\"\/assets\/styles\//', $consumedURL . '/assets/styles/', $result);
		$result = preg_replace('/\"\/assets\/images\//', $consumedURL . '/assets/images/', $result);
        $result = preg_replace('/\"\/justice\/baseAjaxServlet/', $consumedURL . '/justice/baseAjaxServlet', $result);
*/
		//specific URLs
		//$result=preg_replace('#(href)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="//146.20.1.171$2$3',$result);
		//$result=preg_replace('#(href|action|src|data-main)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="//lanebryant.ascenaretail.com$2$3',$result);
		$result=preg_replace('/(href|action|src|data-main)="\/\//','$1="http://',$result);
		$result=preg_replace('#(href|action|src|data-main)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="' . $consumedURL . '$2$3',$result);
        $result=preg_replace('(https://www.rei.com#)', '#', $result);
         //  $result=preg_replace('(//lanebryantuat.ascenaretail.com//)', '//', $result);
        
 
		//$result=preg_replace('("smartSearchFeed" : "$consumedURL/lanebryant/includes/searchEndecaFlyout.jsp",)', '', $result);
        //Write HTML to /tmp/$filename
        file_put_contents($filename, $result);

		
        return $result;
    }

    function getFile($filename)
    {
        // ---------------------------------------------
        // return tmp/$filename html
        // ---------------------------------------------
        return file_get_contents($filename);
    }
}