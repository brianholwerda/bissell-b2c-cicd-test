<?php
namespace Custom\Controllers;

use RightNow\Connect\v1_3 as RNCPHP,
    RightNow\Utils\Framework,
    RightNow\Libraries\AbuseDetection,
    RightNow\Utils\Config;

class FntUpload extends \RightNow\Controllers\Base
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    function __construct()
    {
        parent::__construct();
    }

    public function upload()
    {
        $json = array();
		$r = $this->input->post('FNT_Config_ResponseFrom');
		AbuseDetection::check();
        $fileInfo = $_FILES['file'];
		$folder = $this->input->post('AttachmentFolder');
		$res = $this->model('custom/Fnt_config_model')->addFileAttachment($r, $fileInfo, $folder);
		$file = $res->result;
		if (! is_array( $file ) ) {
			$json['error'] = true;
			$json['errorMessage'] = $res->error->externalMessage;				
		} else {
			$json['result'] = true;
			$json['data'] = $file;
		}
		$s = json_encode ( $json );
		header ('Content-Length: ' . strlen ($s));
		header ('Content-type: text/html');
		echo $s;
    }
}