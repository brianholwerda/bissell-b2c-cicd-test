<?php
require_once('include/init.phph');
use RightNow\Connect\v1_3 as RNCPHP;
require_once( get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php" );

$src = get_cfg_var ( "doc_root" )."/cp/generated/production/source/libraries/";
require_once( $src . 'class.phpmailer.php' );
require_once( $src . 'class.smtp.php' );
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
	if(empty($secstrObj->Subject))
	{
		exit ( getStatus (false, "Subject is required!", $_POST['Payload'] ) );
	}
	$cid = (int) $secstrObj->FNTID;
	$i_id = (int) $secstrObj->IncidentId;
	
	// Get the config record.
	$valid = false;
	$fntc = RNCPHP\FNT\Config::fetch( $cid, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
	if( $fntc == null )
	{
		echo getStatus( false, "Unable to fetch the Forward and Track Record $cid!", $secstrFromPost);
		exit();
	}
	
	// collect data from the config record.
	$msg = "";
	$created = $fntc->CreatedTime;
	$from = $fntc->DefaultFrom;
	$msg .= $fntc->MailMsg; // Append the config mail message to the user message.
	$url = $fntc->UpdateURL;
	$exp = $fntc->LinkExpirationHours;
	$fnt_id = $fntc->ID;
	
	// The field name $fntc->Incident_ID is misleading 
	// because this is an association to Incident so this is an Incident object
	$fnt_incident = $fntc->Incident_ID; 
	$valid = $fnt_incident != null && is_object( $fnt_incident );
	if (!$valid) 
	{
		exit ( getStatus (false, "Invalid Incident ID", $secstrFromPost ) );
	} 
	else 
	{
		// verify the config record and the posted incident id match
		if( $i_id !== $fnt_incident->ID )
		{
			$d = array("IncidentId" => $i_id, "FNT_IncidentId" => $fnt_incident->ID );
			exit( getStatus( false, "Requested Incident Id does not match the ID of the Forward and Track record!", $d) );
		}
		else
		{
			// load thread index
			$incident = RNCPHP\Incident::fetch( $i_id, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			$threads = $incident->Threads;
			$idx = count ( $threads );
			$contact = null;
			if ( isset($incident->PrimaryContact) )
			{
				$contact = RNCPHP\Contact::fetch( $incident->PrimaryContact->ID, RNCPHP\RNObject::VALIDATE_KEYS_OFF );
			}
			$timestamp = time ();
			// need to send mail for every address that is comma delimited
			$addresses = explode ( ",", $secstrObj->To );
			$sendStatus = array();
			// format the message e.g. {FNT.TO}  {FNT.Subject}  {Incident.ReferenceNumber}  {FNT.URL}
			$tokens = array();
			foreach ( $addresses as $address ) {
				$address = trim ( $address );				
				// This gets json encoded then aes encrypted and appended to the configured update url
				$p_tokenArr = array(
					"FNTID" => $fnt_id, 
					"IncidentId" => $i_id,
					"ThreadIndex" => $idx,
					"Address" => $address,
				);
				if(!empty($secstrObj->Vendor))
					$p_tokenArr["Vendor"] = $secstrObj->Vendor;
				// AES Encrypt the token.
				$p_token = json_encode( $p_tokenArr );
				$p_token_enc = $crypt->aes_encrypt( $p_token );
				$ourl = substr($url, 0, strpos($url, ".com") + 4) . "/app/fnt/vo";
				$curl = substr($url, 0, strpos($url, ".com") + 4) . "/app/fnt/vc";
				$url = $url . "/r/" . $p_token_enc;
				$fntData = array( 
						"URL" => $url, 
						"OpenURL" => $ourl,
						"CompleteURL" => $curl,
						"TO" => $address,
						"Message" => str_replace( "\n", "<br>", $secstrObj->Message), 
				);
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
				$formatObj = array( "Incident"=>$incident, "FNT"=>$fntData, );
				$html = $crypt->templateFormat( $formatObj, $msg );
				
				// use PHPMailer it has made sending emails much more stable.
				$m = new \PHPMailer();
				$m->isSMTP(true);
				$m->isHTML(true);
				$m->Host = "localhost";
				$m->Port = 25;
				$m->Subject = $secstrObj->Subject;
				$m->Body = $html;
				$m->setFrom($from);
				$m->addAddress($address);
				$sendStatus[$address] = $m->send() == true ? "SEND_SUCCEEDED" : "SEND_FAILED";
				$p_tokenArr["Subject"] = $secstrObj->Subject;
				$tokens[$address] = $p_tokenArr;
				$address = "";
				$p_token = "";
			}			
			// update the FNT.Config Record
			$fntc->MailSent = true;
			$fntc->Notes = new RNCPHP\NoteArray();
			$fntc->Notes[0] = new RNCPHP\Note();
			$fntc->Notes[0]->Channel = new RNCPHP\NamedIDLabel();
			$fntc->Notes[0]->Channel->ID = 5;
			$fntc->Notes[0]->Text = json_encode( $tokens ) ;
			
			if (strLen($fntc->SMTPResponse) > 0) $fntc->SMTPResponse .= ",\n";
			// resave the config encrypted strings with PHP RSA
			$fntc->SMTPResponse .= json_encode ( $sendStatus );
			$fntc->Save ();			
			echo getStatus ( true, "mail sent", $sendStatus );
		}
	}
	
} 
catch ( Exception $e ) 
{
	$msg = "Application Error: " . $e->getMessage() . "\r\n\r\n" . $e->getTraceAsString();
	echo getStatus (false, $msg, array('DataSent' => $_POST, 'Exception' => $e) );
}

function getStatus($valid, $message, $data = null) {
	$status = array ('valid' => $valid, 'message' => $message, 'data' => $data);
	return json_encode ($status);
}
	
?>