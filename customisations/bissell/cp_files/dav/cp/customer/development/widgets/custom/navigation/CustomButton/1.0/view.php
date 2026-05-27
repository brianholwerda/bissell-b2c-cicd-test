<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= $this->data['attrs']['class_name']; ?>">
<? 
$button1ID = "";	
if($this->data['attrs']['button_1_id'] != "") {
	$button1ID = $this->data['attrs']['button_1_id'];
	//echo $button1ID;
}
if($this->data['attrs']['div_id'] == "") {
	$this->data['attrs']['div_id'] = "NoDivID";
	//echo $button1ID;
} 
?>
<div class="rn_CustomButtonContainer">
	<div id="<?= $this->data['attrs']['div_id'] ?>" class="rn_CustomButtonButtons <?= $this->data['attrs']['class_name']; ?>">
		<a href="<?= $this->data['attrs']['button_1_url']; ?>"  class="rn_CustomButtonButton1Link <?= $button1ID ?>" onclick="<?= $this->data['attrs']['button_1_onclick']; ?>" >
		<div  class="rn_CustomButtonButton1 <?= $this->data['attrs']['class_name']; ?>" <?= $this->data['attrs']['tab_index'] ?> >
			<?= $this->data['attrs']['button_1_text']; ?>	
		</div>
		</a>
    </div>
</div>
</div>