 
<rn:block id='ProductCategoryInput-top'>
	<? if($this->data['attrs']['display_as_select']):?>
		<div class="rn_AFselectDiv rn_AFselectDiv1">
			<span class="rn_AFselectSpan">
			<select  id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['data_type'];?>_SelectObject_lvl1" name="<?=$this->data['js']['data_type'];?>_SelectObject_lvl1" class="rn_AFselect rn_AFselect1"> 
				<?
				foreach($this->data['js']['hierData'][0] as $prod_cat_data){
					echo "<option value=\"" . $prod_cat_data['id'] ."\">".  $prod_cat_data['label'] . "</option>";
				}
				?>
			</select>
			</span>
		</div>
		<div class="rn_AFselectDiv rn_AFselectDiv2">
			<span class="rn_AFselectSpan"> 
			<select  id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['data_type'];?>_SelectObject_lvl2" name="<?=$this->data['js']['data_type'];?>_SelectObject_lvl2" class="rn_AFselect rn_AFselect2"> 
				<?
				 
					echo "<option value=\"0\">". $this->data['attrs']['label_nothing_selected'] . "</option>";
				 
				?>
			</select>
			</span>
		</div>
	<? endif;?>

</rn:block>
 

<!--
<rn:block id='ProductCategoryInput-preLabel'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-postLabel'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-preButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-postButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-preTree'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-preConfirmButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-confirmButtonTop'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-confirmButtonBottom'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-postConfirmButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-postTree'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-preSetButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-postSetButton'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryInput-bottom'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-top'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-label'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-preList'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-listItem'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-postList'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryDisplay-bottom'>

</rn:block>
-->

