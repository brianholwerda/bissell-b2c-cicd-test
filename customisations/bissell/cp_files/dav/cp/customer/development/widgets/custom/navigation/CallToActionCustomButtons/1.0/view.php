<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= $this->data['attrs']['class_name']; ?>" <? if($this->data['attrs']['styleoverridetoplevel'] !== "") { ?> style="<?= $this->data['attrs']['styleoverridetoplevel']; ?>" <? } ?>>
<? 
$button1ID = "";
$button2ID = "";
$button3ID = "";	
if($this->data['attrs']['button_1_id'] != "") {
	$button1ID = $this->data['attrs']['button_1_id'];
	//echo $button1ID;
} 
if($this->data['attrs']['button_2_id'] != "") {
	$button2ID = $this->data['attrs']['button_2_id'];
}
if($this->data['attrs']['button_3_id'] != "") {
	$button3ID = $this->data['attrs']['button_3_id'];
}
?>
<div class="rn_CallToActionContainer" <? if($this->data['attrs']['styleoverridesecondlevel'] !== "") { ?> style="<?= $this->data['attrs']['styleoverridesecondlevel']; ?>" <? } ?>>
    <? if($this->data['attrs']['image_url'] !== "") { ?>
	<div class="rn_ImageContainer">
        <img src="<?= $this->data['attrs']['image_url']; ?>" alt="<?= $this->data['attrs']['image_alt']; ?>"/>
	</div>
	<? } ?>
	<? if($this->data['attrs']['headline'] !== "") { ?>
	<div class="rn_CallToActionDesc">
		<h2 class=""><?= $this->data['attrs']['headline']; ?></h2>
		<span class=""><?= $this->data['attrs']['subtext']; ?></span>
	</div>
	<? } ?>
	<? if($this->data['attrs']['button_1_url'] !== "" || $this->data['attrs']['button_2_url'] !== "" || $this->data['attrs']['button_3_url'] !== "") { ?>
	<div class="rn_CallToActionButtons <?= $this->data['attrs']['class_name']; ?> <?= $this->data['attrs']['responsive_class_buttons']; ?>">
		<? if($this->data['attrs']['button_1_url'] !== "") { ?>
		<div class="rn_CallToActionButton1 <?= $this->data['attrs']['class_name']; ?>">
			<a href="<?= $this->data['attrs']['button_1_url']; ?>"  class="rn_CallToActionButton1Link <?= $button1ID ?>" ><?= $this->data['attrs']['button_1_text']; ?></a>	
		</div>
		<? } ?>
		<? if($this->data['attrs']['button_2_url'] !== "") { ?>
		<div class="rn_CallToActionButton2 <?= $this->data['attrs']['class_name']; ?>">
			<a href="<?= $this->data['attrs']['button_2_url']; ?>" class="rn_CallToActionButton2Link  <?= $button2ID ?>"><?= $this->data['attrs']['button_2_text']; ?></a>
		</div>
        <? } ?>
		<? if($this->data['attrs']['button_3_url'] !== "") { ?>
		<div class="rn_CallToActionButton3 <?= $this->data['attrs']['class_name']; ?>">
			<a href="<?= $this->data['attrs']['button_3_url']; ?>" class="rn_CallToActionButton3Link  <?= $button3ID ?>"><?= $this->data['attrs']['button_3_text']; ?></a>
		</div>
        <? } ?>
    </div>
	<? } ?>
	<? if($this->data['attrs']['desktoptext'] !== "") { ?>
	<div class="desktop-only <?= $this->data['attrs']['class_name']; ?> <?= $this->data['attrs']['desktop_span_class']; ?>">
		<span class="<?= $this->data['attrs']['desktop_span_class']; ?>"><?= $this->data['attrs']['desktoptext']; ?></span>
	</div>
	<? } ?>
	<? if($this->data['attrs']['mobiletext'] !== "") { ?>
	<div class="mobile-only <?= $this->data['attrs']['class_name']; ?> <?= $this->data['attrs']['mobile_span_class']; ?>">
		<span class="<?= $this->data['attrs']['mobile_span_class']; ?>"><?= $this->data['attrs']['mobiletext']; ?></span>
	</div>
	<? } ?>
</div>
</div>