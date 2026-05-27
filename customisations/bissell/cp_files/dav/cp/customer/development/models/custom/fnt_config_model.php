<?php
namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;
use	RightNow\Utils\Config;
use RightNow\Libraries\AbuseDetection;

require_once (get_cfg_var ( "doc_root" ) . "/ConnectPHP/Connect_init.php");

class Fnt_config_model extends \RightNow\Models\Base
{
	private $startFNTText = "|-* This forward and track message is from ";
	private $endFNTText = " *-|\n\n";
	
    function __construct()
    {
        parent::__construct();
		try 
		{
			$this->CI->load->library('Fnt_crypt');
		} 
		catch (\Exception $err) 
		{
			//LogMessage(print_r($err));
		}
    }

    /**
     * Returns a ResponseObject with a FNT.Config record based on the ID passed.
     * 
     * If an Exception occurs or validation fails the Errors and Warnings are set accordingly.
     *    
    */
    function getConfigRecordById( $id )
    {
	    AbuseDetection::check();

    	$result = null;
    	try {
			$c = RNCPHP\FNT\Config::fetch ( $id, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			$result = $this->getResponseObject( $c );
		}
		catch ( \Exception $e ) {
    		$result = $this->getResponseObject( null, null, $e->getMessage() );
    	}	
		return $result;
    }

    /**
     * Returns a ResponseObject with an Incident record based on the ID passed.
     *
     * If an Exception occurs or validation fails the Errors and Warnings are set accordingly.
     *
     */
    function getIncidentById( $id )
    {
	    AbuseDetection::check();

		$result = null;
    	try {
			$i = RNCPHP\Incident::fetch ( $id, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			$result = $this->getResponseObject( $i );
		} catch ( \Exception $e ) {
    		$result = $this->getResponseObject( null, null, $e->getMessage() );
    	}    	 
    	return $result;
    }
    
	function view($tok) 
	{
		$result = null;
    	try 
		{
			$validResponse = $this->validateToken ( $tok, true );
			//return $validResponse;
			if (! is_array ( $validResponse->result ) )
				return $validResponse;

			$fntc = $validResponse->result;
			// everything passed validation now return the incident and FNT data
			// find the incident and return the data in the response object
			$i_id = $fntc['IncidentId'];
			$res = $this->getIncidentById( $i_id );
			$inc = $res->result;
			if ( isset( $inc ) && is_object( $inc ) ) 
			{
				$this->CI->load->library('Fnt_crypt');
				
				$formatObj = array( "Incident"=>$inc );
				
				if(isset($inc->Category->ID))
				{
					// Fetch conditional fields based on Incident.Category
					$query = "SELECT SectionHeader, Fields 
						FROM FNT.ConditionalFields 
						WHERE FNT.ConditionalFields.Category = " . $inc->Category->ID . " 
						AND FNT.ConditionalFields.Active = 1 
						ORDER BY FNT.ConditionalFields.ID ASC 
						LIMIT 1";
						
					$results = RNCPHP\ROQL::query($query)->next()->next();

					if(isset($results['Fields']))
					{
						$categoryHeader = $results['SectionHeader'];
						$finalFieldTemplate = "";
						
						$categoryFields = $this->CI->fnt_crypt->templateFormat($formatObj, $results['Fields']);
						$fields = explode(",", $categoryFields);
						foreach($fields as $field)
						{
							$pieces = explode(":", $field);
							$fieldName = $pieces[0];
							$fieldValue = $pieces[1];
							$finalFieldTemplate .= "<b>" . $fieldName . "</b>: " . $fieldValue . "<br>";
						}
						
						$categoryFields = $finalFieldTemplate;
					}
				}
				
				
				if(isset($inc->Queue->ID))
				{
					$query = "SELECT SectionHeader, Fields 
						FROM FNT.ConditionalFields 
						WHERE FNT.ConditionalFields.Queue = " . $inc->Queue->ID . " 
						AND FNT.ConditionalFields.Active = 1 
						ORDER BY FNT.ConditionalFields.ID ASC 
						LIMIT 1";
						
					$results = RNCPHP\ROQL::query($query)->next()->next();

					if(isset($results['Fields']))
					{
						$queueHeader = $results['SectionHeader'];
						$finalFieldTemplate = "";
						
						$queueFields = $this->CI->fnt_crypt->templateFormat($formatObj, $results['Fields']);
						$fields = explode(",", $queueFields);
						foreach($fields as $field)
						{
							$pieces = explode(":", $field);
							$fieldName = $pieces[0];
							$fieldValue = $pieces[1];
							$finalFieldTemplate .= "<b>" . $fieldName . "</b>: " . $fieldValue . "<br>";
						}
						
						$queueFields = $finalFieldTemplate;
					}
				}
				
				// Fetch conditional fields based on Incident.Queue
				
				$dtoArray = array( 
					'Incident' => $inc, 
					'ThreadIndex' => $fntc['ThreadIndex'],
					'Token' => $tok,
					'FileAttachments' => $fntc['FileAttachments'],
					'FilesChecked' => $fntc['FilesChecked'],
					'ResponseFrom' => $fntc['ResponseFrom'],
					'FNTID' => $fntc['FNTID'],
					'StartFNTText' => $this->startFNTText,
					'ShowThreads' => $fntc['ShowThreads'],
					'FNT' => $fntc["Data"],
					'CategoryFields' => $categoryFields,
					'CategoryHeader' => $categoryHeader,
					'QueueFields' => $queueFields,
					'QueueHeader' => $queueHeader,
				);
				$result = $this->getResponseObject( $dtoArray, 'is_array' );
			} 
			
			else 
				$result = $this->getResponseObject( null, null, sprintf(Config::getMessage(INVALID_PARAMETERS_PCT_S_LBL), Config::getMessage(INCIDENT_ID_LBL)) );	
    	} 
		catch(RNCPHP\ConnectAPIError $e)
		{
			$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () );
		}
		catch ( \Exception $e ) 
		{
    		$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () 
								. "\r\n" . $e->getTraceAsString() );
    	}    	 
    	return $result;
	}
	
	function validateToken ($tok) 
	{

		$result = null;
    	try {
			if ( empty ($tok) ) {
				return $this->getResponseObject( null, null, Config::getMessage(INVALID_ARGUMENTS_LBL) );
			}
			$this->CI->fnt_crypt->initConnect();
			$is_tfnt_token = false;
			if ( strpos( $tok, "TFNT:" ) !== false )
			{
				$is_tfnt_token = true;
				$parsedtok = substr($tok, 5); // remove "TFNT:"
				$json = $this->CI->fnt_crypt->aes_decrypt( $parsedtok );
			}
			else
			{
				$json = $this->CI->fnt_crypt->aes_decrypt( $tok );
			}
			$dta = json_decode( $json );
			
			if ( !is_object( $dta )  ) {
				return $this->getResponseObject( null, null, Config::getMessage(INVALID_PARAMETERS_MSG) . " " . $tok );
			}

			// define variables for later use
			$i_id = $dta->IncidentId;
			$threadIndex = (int) $dta->ThreadIndex;
			$responseFrom = $dta->Address;
			$fntid = (int) $dta->FNTID;
			// validate the token data.
			if ( !isset ($i_id) || !isset ($fntid) ) {
				return $this->getResponseObject( null, null, sprintf(Config::getMessage(INVALID_PARAMETERS_PCT_S_LBL), Config::getMessage(INCIDENT_ID_LBL)));
			}
			
			// get the config record to validate the request.
			$res = $this->getConfigRecordById( $fntid );
			$fntc = $res->result;
			if ( !isset ( $fntc ) || !is_object ( $fntc ) ) {
				return $res;
			}
						
			// validate the incident id
			if ( (int)$i_id != $fntc->Incident_ID->ID) {
				return $this->getResponseObject( null, null, Config::getMessage(INVALID_LBL) . " " . Config::getMessage(INCIDENT_LBL) . " '$i_id'!" );
			}
			// collect data used from fnt.config
			$fileattach = $fntc->ViewAttachEnabled;
			$addfile = $fntc->AddAttachEnabled;
			$exp = $fntc->LinkExpirationHours;
			$expiredTime = $fntc->CreatedTime + (60 * 60 * $exp);
			$created = $fntc->CreatedTime;
			$filesChecked = array();
			if (!empty( $fntc->ViewableFiles ) ) {
				$filesChecked = explode( ",", $fntc->ViewableFiles);
			}
			
			// check for expired link.
			if ($expiredTime < gmmktime ()) {
				return $this->getResponseObject( null, null, Config::getMessage(EXPIRED_LBL) );
			}
			
			// everything passed validation now return the FNT data
			$dtoArray = array( 'ThreadIndex' => $threadIndex,
				'Token' => $tok,
				'FileAttachments' => $fileattach,
				'FilesChecked' => $filesChecked,
				'ResponseFrom' => $responseFrom,
				'FNTID' => $fntid,
				'IncidentId' => $i_id,
				'TI' => $threadIndex,
				'SendTo' => $fntc->SendTo,
				'Config' => $fntc,
				'ShowThreads' => $dta->ShowThreads,
				'Data' => $dta, // Security issue only use for debugging
			);

			$result = $this->getResponseObject( $dtoArray, 'is_array' );						
    	} 
		catch(RNCPHP\ConnectAPIError $e)
		{
			$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () );
		}
		catch ( \Exception $e ) 
		{
    		$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () );
    	}    	 
    	return $result;
	}
	
    /**
     * Adds a new Private Message Thread to the Incident and updates the FNT.Config Record.
     * 
     * returns a ResponseObject with a valid Incident as the ResonseObject->return.
     * 
     */
    function addIncidentThread ($tok, $from, $thread, $filesStr, $filesCheckedStr, $folder, $pubThread = "", $status=8) 
	{

		$result = null;
    	try {		
			// validateToken is called which calls abuse detect methods
			$validResponse = $this->validateToken ( $tok );
			if (! is_array ( $validResponse->result ) ) {
				return $validResponse;
			}
			$dto = $validResponse->result;
			// $from = $validResponse['ResponseFrom'];
			$i_id = $dto['IncidentId'];
			$fntid = $dto['FNTID'];
			// update the Incident
			$res = $this->getIncidentById ( $i_id );
			$incident = $res->result;
			if ( is_object($incident) ) {
				// DHammons 07/24/2017 Update Incident with custom "Updated from FnT" status
				$incident->StatusWithType->Status->ID = Config::getConfig(CUSTOM_CFG_FNT_UPDATE_STATUS_ID);
				$incident->Threads = new RNCPHP\ThreadArray ();
				$indx = -1;
				if (! empty( $thread ) )
				{
					$indx = $indx + 1;
					$incident->Threads [$indx] = new RNCPHP\Thread ();
					$incident->Threads [$indx]->EntryType = new RNCPHP\NamedIDOptList ();
					$incident->Threads [$indx]->EntryType->ID = 1; // Private Note
					$incident->Threads [$indx]->ContentType = new RNCPHP\NamedIDOptList ();
					$incident->Threads [$indx]->ContentType->LookupName = "text/plain"; // 				
					$incident->Threads [$indx]->Text = $this->startFNTText . $from . $this->endFNTText . htmlspecialchars_decode( $thread );
				}
				
				if (! empty($pubThread) )
				{
					$indx = $indx + 1;
					$incident->Threads [$indx] = new RNCPHP\Thread ();
					$incident->Threads [$indx]->EntryType = new RNCPHP\NamedIDOptList ();
					$incident->Threads [$indx]->EntryType->ID = 2; // Staff Account
					$incident->Threads [$indx]->Channel->ID = 5; // Post
					$incident->Threads [$indx]->ContentType = new RNCPHP\NamedIDOptList ();
					$incident->Threads [$indx]->ContentType->LookupName = "text/plain"; // 				
					$incident->Threads [$indx]->Text = $this->startFNTText . $from . $this->endFNTText . htmlspecialchars_decode( $pubThread );
				}
				
				if ($indx === -1)
				{
					return $this->getResponseObject( null, null, "Text Required.." ); // TODO: use message
				}
				// handle file attachments
				$incident->FileAttachments = new RNCPHP\FileAttachmentIncidentArray();
				$files = explode(",", $filesStr);
				$filesChecked = explode(",", $filesCheckedStr);
				foreach ($files as $filestr)
				{
					if (! empty($filestr) ) {
						$fileTok = explode ("|", $filestr);
						if ( count ($fileTok) == 3 ) {
							$name = $fileTok[0];
							$tmp_name = $fileTok[1];
							$content_type = $fileTok[2];
							if (in_array ($name, $filesChecked) ) {
								$fattach = new RNCPHP\FileAttachmentIncident();
								$fattach->ContentType = trim($content_type);
								$fp = $fattach->makeFile();
								$loaded_file = file_get_contents($tmp_name);
								fwrite( $fp, $loaded_file );
								fclose( $fp );
								$fattach->FileName = $name;
								$incident->FileAttachments[] = $fattach;
							}
						}
					}
				}
				// remove each file
				foreach ($files as $filestr)
				{
					if (! empty($filestr) ) {
						$fileTok = explode ("|", $filestr);
						if ( count ($fileTok) == 3 ) {
							$name = $fileTok[1];
							@unlink($name);
						}
					}
				}
				// remove the temp folder
				if (isset ($folder) && !empty ($folder) ) {
					@rmdir($folder);
				}
				$incident->save ();
				// Update the FNT.Config record
				$res = $this->getConfigRecordById ( $fntid );
				$fntc = $res->result;
				if ( is_object($fntc) ) {
					// ViewableFiles
					$vf = explode(",", $fntc->ViewableFiles);
					foreach($filesChecked as $fileCompare) {
						if ( !in_array ( $fileCompare, $vf ) ) {
							if (strlen ( $fntc->ViewableFiles ) > 0)
								$fntc->ViewableFiles .= ",";
							$fntc->ViewableFiles .= $fileCompare;
						}
					}
					// ResponseFrom
					if (strlen ( $fntc->ResponseFrom ) > 0)
						$fntc->ResponseFrom .= ",";
					$fntc->ResponseFrom .= $from . " at " . date ( 'm-d-Y H:i:s', time () );
					$fntc->Save ();
					$result = $this->getResponseObject( array('result' => true, ) , 'is_array' );
				} else {
					$result = $res;
				}
			} else {
				$result = $res;
			}			
    	} 
		catch(RNCPHP\ConnectAPIError $e)
		{
			$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () );
		}
		catch ( \Exception $e) 
		{
			$result = $this->getResponseObject( null, null, "Application Error: " . $e->getMessage () );
		}
			
		return $result;
    } 
	
	function addFileAttachment($tok, $fileInfo, $folder = null) {
		AbuseDetection::check(); 
		$result = null;
		try {
			// validateToken is called which calls abuse detect methods
			$res = $this->validateToken ( $tok );
			$fnt = $res->result;
			if (! is_array( $fnt ) ) {
				$result = $res;				
			} else {
				// file upload validation stuff..
 				if($fileInfo['error'] === UPLOAD_ERR_NO_FILE) {
					$result = $this->getResponseObject( null, null, FILE_PATH_FOUND_MSG );
				} else if($fileInfo['size'] === false || $fileInfo['size'] === null  || ($fileInfo['error'] > 0)) {
					$result = $this->getResponseObject( null, null, Config::getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG) );
				} else if($fileInfo['size'] === 0) {
					$result = $this->getResponseObject( null, null, Config::getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG) );
				} else if($fileInfo['size'] > getConfig(FATTACH_MAX_SIZE)) {
					$result = $this->getResponseObject( null, null, Config::getMessage(SORRY_FILE_TRYING_UPLOAD_MSG) );
				} else if(strlen($fileInfo['name']) > 100) {
					$result = $this->getResponseObject( null, null, Config::getMessage(NAME_ATTACHED_FILE_100_CHARS_MSG) );
				} else {

					// all checks cleared so deal with the attachment.
					$name = $fileInfo['tmp_name'] . ".fnt";
					if (move_uploaded_file($fileInfo['tmp_name'], $name)) {
						$fileInfo['tmp_name'] = $name;
						$result = $this->getResponseObject( array( 'AttachmentsFolder' => '/tmp', 'FileInfo' => $fileInfo, ), 'is_array' );
						//@unlink($name); // just for now...
					} else {
						$result = $this->getResponseObject( null, null, Config::getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG) );
					}
				}
			}
		}
		catch ( Exception $e) {
			$result = $this->getResponseObject( null, null, "Application Error: " .$e->getMessage () );
		}
		return $result;
	}
	
	private function _createUploadFolder() {
		$tempfile=tempnam(sys_get_temp_dir(),'');
		if (file_exists($tempfile)) { unlink($tempfile);}
		mkdir($tempfile);
		if (is_dir($tempfile)) { return $tempfile;}
	}
	
	private function _getValues($parent) 
	{
		try
		{
			// $parent is a non-associative (numerically-indexed) array
			if (is_array($parent)) {
				foreach($parent as $val) {
					$this->_getValues($val);
				}
			}
			// $parent is an associative array or an object
			elseif (is_object($parent)) {
				while (list($key, $val) = each($parent)) {
					$tmp = $parent->$key;
					if ( (is_object($parent->$key)) || (is_array($parent->$key)) ) {
						$this->_getValues($parent->$key);
					}
				}
			}
		}
		catch (Exception $ex) 
		{
			// we don't care
		}
	}
}
