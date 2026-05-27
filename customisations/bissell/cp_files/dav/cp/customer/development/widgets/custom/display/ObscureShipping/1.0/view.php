<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<?
    $addr   = $this->data['attrs']['shipping_addr'];
    //print_r($addr); echo "<br>";
    //var_dump(strrpos($addr,'-', -5));
    $len = strlen($addr);
    //print($len); echo "<br>";
    
	//Requirement is to obsure all letters except the first 5 and last 5 of the shipping address
    $obscureAddr = substr($addr,0, 10) . str_repeat('*', $len-10) . substr($addr,$len-20, $len);   
    
?>
	
	<div class="rn_obscureEmail">
		<? /* Before Obscure:
		<?= $this->data['attrs']['shipping_addr']; ?>
		After Obscure: */ ?>
		<?= $obscureAddr; ?>
	</div>
</div>