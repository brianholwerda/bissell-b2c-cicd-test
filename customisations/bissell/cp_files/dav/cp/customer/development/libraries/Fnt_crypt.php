<?php
namespace Custom\Libraries;
use RightNow\Connect\v1_3 as RNCPHP;
use RightNow\Utils\Config;
use RightNow\Connect\Crypto\v1_3 as Crypto;
require_once( get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php" );

class Fnt_crypt {
	
	// Populate these for each customer
	private $Key = "826153B7F64944D5820BB81F295CC523"; // 32 char string, Must match the security string in the addin
	public $u = "Rk5UQWRtaW4="; // Base64 encode admin account login
	public $p = "ZWoybk5LTWZ2NnNtYzlTajJBejc="; // Base64 encode admin account password 
	
	function __construct()
	{
		
	}
	
	function aes_encrypt($data)
	{
		$cipher = new Crypto\AES();  
		$cipher->Mode->LookupName = "CBC";
		$cipher->Padding->ID = 2;
		$cipher->KeySize->LookupName = "256_bits";
		$cipher->Key = $this->Key;
		$cipher->IV->Value = substr($this->Key, 0 , 16);
		$cipher->Text = $data;
		$cipher->EncryptedText = "";//fromUrlSafeBase64($_POST['Payload']);
		$cipher->encrypt();
		$enc = base64_encode( $cipher->EncryptedText );
		$enc = str_replace(array('/','+'), array('_','-'), $enc);
		unset($cipher);
		return $enc;
	}

	function aes_decrypt($data)
	{
		$clear = str_replace(array('_','-'), array('/','+'), $data);
		$clear = base64_decode( $clear );		
		$cipher = new Crypto\AES();  
		$cipher->Mode->LookupName = "CBC";
		$cipher->Padding->ID = 2;
		$cipher->KeySize->LookupName = "256_bits";
		$cipher->Key = $this->Key;
		$cipher->IV->Value = substr($this->Key, 0 , 16);
		$cipher->Text = "";
		$cipher->EncryptedText = $clear;//fromUrlSafeBase64($_POST['Payload']);
		$cipher->decrypt();
		$clear = $cipher->Text;
		unset($cipher);
		return $clear;
	}
	
	function initConnect()
	{
		initConnectAPI( base64_decode($this->u), base64_decode($this->p) );
	}
	
	function isKeyHash( $hash )
	{
		$key = sha1( $this->Key );
		if( $key != $hash )
		{
			return false;
		}
		return true;
	}
	
	function fromUrlSafeBase64($data)
	{
		$clear = str_replace(array('_','-'), array('/','+'), $data);
		$clear = base64_decode( $clear );
		return $clear;
	}
		
	function templateFormat( $object, $template  )
	{
		
		$result = $template; // set default
		try
		{
			$regex = "/(?<={)([A-Za-z0-9_]*\.*[A-Za-z0-9_])*(?=})/";
			if (preg_match_all($regex, $result, $matches))
			{
				foreach ( $matches[0] as $m)
				{
					try
					{
						$v = $this->_interpolate( $object, $m );
						if ( is_string( $v ) || is_numeric( $v ) )
						{
							$result = str_replace("{" . $m . "}", (string)$v, $result);
						}
						else
						{
							$result = str_replace("{" . $m . "}", $m, $result);
						}
					}
					catch(\Exception $e)
					{
						// leave the string the way it is.
						$result = str_replace("{" . $m . "}", $m, $result);
					}
				}	
			}
		}
		catch (\Exception $ex) 
		{
			$results = $template;
		}		
		return $result;
	}
	
	function formatMessage( $const, $template_object)
	{
		$msg = $this->getCustomMessage( $const );
		return $this->templateFormat( $template_object, $msg );
	}
	
	function getCustomMessage($const)
	{
		$msg = "";
		try 
		{
			$q = RNCPHP\ROQL::queryObject( "Select MessageBase From MessageBase Where MessageBase.Name = '$const'" )->next ();
			while ($mb = $q->next ())
			{
				$msg = $mb->Value;
			}
		}
		catch(\Exception $e)
		{
			return $e->getMessage();
		}
		catch(RNCPHP\ConnectAPIError $e)
		{
			return $e->getMessage();
		}
		catch(RNCPHP\ConnectAPIErrorFatal $e)
		{
			return $e->getMessage();
		}
		return $msg;
	}
	
	function getGroupMemberInfo( $member )
	{
		$memobj = new \stdClass;
		$memobj->ID = $member->ID;
		$memobj->MemberType = $member->GroupMemberType->LookupName;
		if ($member->GroupMemberType->LookupName == "Contact")
		{
			if ( isset( $member->Contact ) )
			{
				foreach ( $member->Contact->Emails as $email)
				{
					if ( strpos( $email->AddressType->LookupName, "Primary") !== false )
					{
						$email = $email->Address;
						$memobj->First = $member->Contact->Name->First;
						$memobj->Last = $member->Contact->Name->Last;
					}
				}
			}
		}
		elseif ($member->GroupMemberType->LookupName == "Staff Account")
		{
			if ( isset( $member->Account ) )
			{
				foreach ( $member->Account->Emails as $email)
				{
					if ( strpos( $email->AddressType->LookupName, "Primary") !== false )
					{
						$email = $email->Address;
						$memobj->First = $member->Account->Name->First;
						$memobj->Last = $member->Account->Name->Last;
						break;
					}
				}
			}
		}
		else
		{
			$memobj->MemberType = "Member";
			if ( isset( $member->EmailAddress ) )
			{
				$email = $member->EmailAddress;
				$memobj->First = $member->FirstName;
				$memobj->Last = $member->LastName;
			}
		}
		$memobj->Name = $memobj->First . " " . $memobj->Last;
		$memobj->Email = $email;
		return $memobj;
	}

	private function _interpolate($obj, $string)
	{
		$result = &$obj;
		try
		{
			$tokens = explode(".", $string);
			foreach ($tokens as $propToken)
			{
				if ( is_array ( $result ) )
					$result = $obj[$propToken];
				elseif ( is_object ( $result ) )
					$result = $obj->$propToken;
			}
		}
		catch (\Exception $ex) 
		{
			$results = "";
		}
		if ( !isset( $result ) || empty( $result ) )
			$result = ""; // TODO: just make the report return nothing
		return $result;
	}
	
}

?>
