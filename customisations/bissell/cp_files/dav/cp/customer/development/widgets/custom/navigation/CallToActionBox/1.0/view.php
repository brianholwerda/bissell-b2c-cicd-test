<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= $this->data['attrs']['class_name']; ?>">
<? 
$button1ID = "";
$button2ID = "";	
if($this->data['attrs']['button_1_id'] != "") {
	$button1ID = $this->data['attrs']['button_1_id'];
	//echo $button1ID;
}
if($this->data['attrs']['button_2_id'] != "") {
	$button2ID = $this->data['attrs']['button_2_id'];
}
?>
<div class="rn_CallToActionContainer">
	<? if($this->data['attrs']['above_image_text'] !== "") { ?>
		<div class="rn_CallToActionAboveImageText">
			<span class="rn_CallToActionAboveImageTextSpan"><?= $this->data['attrs']['above_image_text']; ?></span>
		</div>
	<? } ?>
	
	<? if($this->data['attrs']['image_url'] !== "") { ?>
		<div class="rn_ImageContainer">
			<img src="<?= $this->data['attrs']['image_url']; ?>" alt=""/>
		</div>
	<? } ?>
	
	<div class="rn_CallToActionDesc">
		<h3 class="rn_CallToActionH3"><?= $this->data['attrs']['headline']; ?></h3>
		<? if($this->data['attrs']['subheadline'] !== "") { ?>
			<span class="<?= $this->data['attrs']['subheadlineclass']; ?>"><?= $this->data['attrs']['subheadline']; ?></span>
		<? } ?>
		
		<span class="rn_CallToActionSubText"><?= $this->data['attrs']['subtext']; ?></span>
	</div>
	
	<? if(isset($this->data['attrs']['image_url_below_header']) && $this->data['attrs']['image_url_below_header'] !== "") { ?>
		<div class="rn_ImageContainer">
			<img src="<?= $this->data['attrs']['image_url_below_header']; ?>" alt=""/>
		</div>
	<? } ?>
	
	<? if($this->data['attrs']['button_1_url'] !== "") { ?>
		<div class="rn_CallToActionButtons <?= $this->data['attrs']['class_name']; ?>">
			<div class="rn_CallToActionButton1 <?= $this->data['attrs']['class_name']; ?>">
				<a href="<?= $this->data['attrs']['button_1_url']; ?>"  class="rn_CallToActionButton1Link <?= $button1ID ?>" ><?= $this->data['attrs']['button_1_text']; ?></a>	
			</div>
			
			<? if($this->data['attrs']['button_2_url'] !== "") { ?>
				<div class="rn_CallToActionButton2 <?= $this->data['attrs']['class_name']; ?>">
					<a href="<?= $this->data['attrs']['button_2_url']; ?>" class="rn_CallToActionButton2Link  <?= $button2ID ?>"><?= $this->data['attrs']['button_2_text']; ?></a>
				</div>
			<? } ?>
		</div>
	<? } ?>
	
    <? if($this->data['attrs']['footersubtext'] !== "") { ?>
		<div class="rn_CallToActionFooter">
			<span class="rn_CallToActionFooterSubText"><?= $this->data['attrs']['footersubtext']; ?></span>
		</div>
	<? } ?>
</div>
</div>