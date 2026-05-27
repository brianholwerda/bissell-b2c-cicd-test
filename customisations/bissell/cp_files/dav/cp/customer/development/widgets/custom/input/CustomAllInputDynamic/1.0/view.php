
<rn:block id='CustomAllInput-top'>
	<div id="rn_CustomAllInputDivID" >
		<h3><div id="rn_<?=$this->instanceID;?>_DisplayMessage" class="rn_dynamic_msg" ></div></h3>

		 <div id="rn_<?= $this->instanceID ?>" >
			<?$initialFocus = ($this->data['attrs']['initial_focus_on_first_field']) ? 'true' : 'false';?>
			<? foreach($this->data['fields'] as $fieldName):?>
				<rn:widget path="input/FormInputDynamic" name="#rn:php:$fieldName#" initial_focus="#rn:php:$initialFocus#"/>
				<? $initialFocus = 'false';
			endforeach;?>
		</div>
	</div>
</rn:block>




 
 