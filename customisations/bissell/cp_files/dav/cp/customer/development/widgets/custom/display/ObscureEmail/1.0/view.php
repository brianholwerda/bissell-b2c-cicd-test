<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

<? if($this->data['attrs']['email_address'] != "") { 
	//print("inside !=");
	$em   = explode("@",$this->data['attrs']['email_address']);
    //print_r($em); echo "<br>";
    $name = implode('@', array_slice($em, 0, count($em)-1));
    //print($name); echo "<br>";
    
	//Requirement is to obsure all letters except the first and last of the email address account (before the @)
    $obsureEmail = substr($name,0, 1) . str_repeat('*', strlen($name)-2) . substr($name,strlen($name)-1, strlen($name)) ."@" . end($em);   
?>
	<p>
	<div class="rn_obscureEmail">
		<?= $obsureEmail; ?>
	</div>
	<div class="rn_Hidden">
		<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" default_value="#rn:php:$this->data['attrs']['email_address']#"/>
	</div>
	</p>
<? } else { ?>
	<p>
	<?= $this->data['attrs']['explanation_msg']; ?>
	<rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" initial_focus="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" />
	</p>
<? } ?>
</div>