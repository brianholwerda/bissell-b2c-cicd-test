<?php
require_once('include/init.phph');
include_once('custom/util.phph') ;
use RightNow\Connect\v1_3 as RNCPHP;
require_once( get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php" );

$src = get_cfg_var ( "doc_root" )."/cp/generated/production/source/libraries/";
require_once( $src . 'Fnt_crypt.php');

try 
{	
	if ( ! isset( $_POST['Payload'] ) ) 
	{
		exit( getStatus( false, "Invalid JSON Data posted!", $_POST));
	}
	$fnt_id = 0;
	$crypt = new Custom\Libraries\Fnt_crypt();
	$crypt->initConnect();
	$secstrFromPost = $crypt->fromUrlSafeBase64( urldecode( $_POST['Payload'] ) );
	$secstrObj = json_decode( $secstrFromPost );
	if( !$crypt->isKeyHash( $secstrObj->Hash ) )
	{
		exit ( getStatus (false, "Security Hash does not match!", $_POST['Payload'] ) );
	}
	$i_id = (int) $secstrObj->IncidentId;
	if (empty( $i_id ) ) 
	{
		exit ( getStatus (false, "Invalid Incident ID", $_POST["Payload"] ) );
	} 
	else 
	{
		$incident = RNCPHP\Incident::fetch( $i_id, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
		$contact = null;
		if ( isset($incident->PrimaryContact) )
		{
			$contact = RNCPHP\Contact::fetch( $incident->PrimaryContact->ID, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			}	
		$fntData = array( );
		$c = 1;
		if (isset( $contact ) )
		{
			foreach( $contact->Emails as $email)
			{
				if( $email->AddressType->ID == 0 ) // Primary email
				{
					$fntData["ContactEmail"] = $email->Address;
				}
				$p = "ContactEmail" . (string)$c;
				$fntData[$p] = $email->Address;
				$c++;
			}
		}
		$formatObj = array( "Incident"=>$incident, "FNT"=>$fntData, "Contact"=>$contact, );
		$html = $crypt->templateFormat( $formatObj, $secstrObj->Template );
		echo getStatus( true, $html, array( "Parsed"=> $html ) );
	}
	
} 
catch ( Exception $e ) 
{
	$msg = "Application Error for FNTID '$fnt_id': " . $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n\r\n";
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e, 'FNTID' => $fnt_id, ) );
}
catch(RNCPHP\ConnectAPIError $e)
{
	$msg = "Application Error for FNTID '$fnt_id': " . $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n\r\n";
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e,'FNTID' => $fnt_id,) );
}
catch(RNCPHP\ConnectAPIErrorFatal $e)
{
	$msg = "Application Error for FNTID '$fnt_id': " . $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n\r\n";
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e,'FNTID' => $fnt_id,) );
}

function getStatus($valid, $message, $data = null) {
	$status = array ('valid' => $valid, 'message' => $message, 'data' => $data);
	return json_encode ($status);
}
	
?>