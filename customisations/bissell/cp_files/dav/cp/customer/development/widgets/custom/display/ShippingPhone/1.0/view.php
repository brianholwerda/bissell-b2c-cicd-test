<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

<? if($this->data['attrs']['phone_number'] != "") {  ?>
	<p>
	<div class="rn_Hidden">
		<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ShippingPhone" required="true" initial_focus="true"  default_value="#rn:php:$this->data['attrs']['phone_number']#" />
	</div>
	</p>
<? } else { ?>
	
	<b><?= $this->data['attrs']['explanation_msg']; ?></b>
	<rn:widget path="input/FormInput" name="Incident.CustomFields.WDMR.ShippingPhone" required="true" initial_focus="true" label_input="#rn:msg:CUSTOM_MSG_WDMR_SHIPPINGPHONE_LBL#" always_show_mask="true" />
	
<? } ?>
</div>