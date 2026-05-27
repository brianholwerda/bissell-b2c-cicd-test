<?php
namespace Custom\Widgets\FNT;

use RightNow\Connect\v1 as RNCPHP;
use	RightNow\Utils\Config;

class ForwardAndTrackInput extends \RightNow\Libraries\Widget\Base 
{
    function __construct($attrs) 
	{
        parent::__construct($attrs);		
 		$this->setAjaxHandlers(array(
			'fnt_validate_token' => array( 
				'method' => 'fntValidateToken', 
				'clickstream' => 'custom_action',
			),
			'fnt_upload' => array( 
				'method'	=> 'fntUploadFile', 
				'clickstream' => 'custom_action',
			),
		));
	}

    function getData() 
	{
		$fnttok = \RightNow\Utils\Url::getParameter('r');
		
		if ( isset ( $fnttok ) ) 
		{
			// Get the incident
			$model = $this->CI->model('custom/Fnt_config_model');
			$response = $model->view( $fnttok );
			
			// expect an array of  token data.
			if (! is_array( $response->result ) ) 
			{ 
				$this->data['js'] = array( 
					'error' => $response->error->externalMessage ,
				);
				$this->reportError('Error: ' . $response->error->externalMessage );
				return true; // return true to allow the javascript to display.
			}
			$fntc = $response->result;
			$this->CI->load->library('Fnt_crypt');
			$this->data['js'] = array( 'data' => $fntc, 
				'FNT.Config.ResponseFrom' => $fnttok,
			);
			return true;
		} 
		
		else 
		{
			$message = Config::getMessage(INVALID_ARGUMENTS_LBL);
			$this->data['js'] = array( 'error' => $message );
			$this->reportError($message);;
			return true;
		}
    }
	
 	function fntUploadFile($postData) {
		$fnttok = $postData['FNT_Config_ResponseFrom'];
		$folder = $postData['AttachmentFolder'];
		
		if (! isset ( $fnttok ) ) 
		{
			$json['error'] = true;
			$json['errorMessage'] = Config::getMessage(INVALID_ARGUMENTS_LBL);		
		} 
		else 
		{
			if ($this->data['attrs']['read_only'] === true)
			{
				$json['error'] = true;
				$json['errorMessage'] = "Uploads not allowed in read only mode!";
			}
			else
			{
				$fileInfo = $_FILES['file'];
				$model = $this->CI->model('custom/Fnt_config_model');
				$res = $model->addFileAttachment($fnttok, $fileInfo, $folder);
				$file = $res->result;
				if (! is_array( $file ) ) {
					$json['error'] = true;
					$json['errorMessage'] = $res->error->externalMessage;				
				} else {
					$json['result'] = true;
					$json['data'] = $file;
				}
			}
		}
		$s = json_encode ( $json );
		header ('Content-Length: ' . strlen ($s));
		header ('Content-type: text/html');
		echo $s;
	} 
	
	/**
	 * Adds the incident thread.
	 */
	function fntValidateToken($postData) {
		$json = array( 'error' => false, 'errorMessage' => "", 'result' => false, 'data' => null, );
		$folder = $postData['AttachmentFolder'];
		$fnttok = $postData['FNT_Config_ResponseFrom'];
		$message = $postData['ResponseFrom_Message'];
		$from = $postData['From'];
		$filesChecked = $postData['FilesChecked'];
		$files = $postData['Files'];
		$attachmentsFolder = $postData['AttachmentFolder'];
		$pubMsg = $postData['PublicMessage'];
		if ($this->data['attrs']['read_only'] === true)
		{
			$json['error'] = true;
			$json['errorMessage'] = "Updates not allowed in read only mode!";
		}
		else
		{
			if ( isset ( $fnttok ) ) {
				if (! isset ( $from ) || empty( $from ) ) {
					$json['error'] = true;
					$json['errorMessage'] = Config::getMessage(INVALID_LBL) . " " . Config::getMessage(FROM_LBL);			
				} else {
					if( isset ( $message ) ) {
						$model = $this->CI->model('custom/Fnt_config_model');
						$addresult = $model->addIncidentThread ($fnttok, $from, $message, $files, $filesChecked, $attachmentsFolder, $pubMsg);
						$result = $addresult->result;
						if ( is_array( $result ) ) {
							$json['result'] = $result['result'];
							$json['data'] = $result;
						} else {
							$json['error'] = true;
							$json['result'] = $result;
							$json['errorMessage'] = "Update Incident Failed!";
						}
					} else {
						$json['error'] = true;
						$json['errorMessage'] = Config::getMessage(INVALID_LBL) . " " . Config::getMessage(RESPONSE_LBL);
					}
				}
			}
		}
		$s = json_encode ( $json );
		header ('Content-Length: ' . strlen ($s));
		header ('Content-type: application/json');
		echo $s;
	}
	
}
?>
