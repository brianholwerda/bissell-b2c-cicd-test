<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>" role="navigation" aria-label="product/category menu">
	<? if($this->data['availTopLevelOptions']): ?>
		<span class="modal_label" aria-label="Product" tabindex="1" >
			<? if(strlen($this->data['attrs']['default_label']) > 0)
				  echo $this->data['attrs']['default_label'];
			   else
			   	  echo $this->data['attrs']['name'];
			?></span>  <span id="<?=$this->data['attrs']['id_prefix'];?>selectionLimitContainer" class="selectionLimitContainer"><span id="<?=$this->data['attrs']['id_prefix'];?>selectionLimit" class="selectionLimit"><?=$this->data['attrs']['final_selection_text'];?></span></span><br />
		<?=$this->data['prodcat_menu1'];?> 
		<!-- Traverse the prodcat_menus array and build the required number of select menus-->
		<? foreach ($this->data['prodcat_menus'] as $menuId => $menu): ?>
	        <?=$menu."\n"?> 
	    <? endforeach; ?>
		
		<div id="<?=$this->data['attrs']['id_prefix'];?>finalProdCatID" class="finalProdCatID"><input type="text" id="<?=$this->data['attrs']['id_prefix'];?>Selection" name="<?=$this->data['attrs']['id_prefix'];?>Selection" class="" maxlength="20" value="" <?=$this->data['attrs']['required'];?> validation_label="<?=$this->data['attrs']['validation_label']?>" aria-label="<?=$this->data['attrs']['validation_label']?>"/> </div>
	<? else: ?>
		<span id="<?=$this->data['attrs']['id_prefix'];?>widget_error">A valid top level ID was not defined in "show_ids" attribute.  Be sure to define a top level ID, or a child ID of the starting_level_id attribute specified.</span>
	<? endif; ?>
</div>


<script>
$(document).ready(function() {
	//$('[id^="rn_ProductCategorySelectMenu_"]')
	var menuText = $('.chosen-container .chosen-results li.active-result option:selected').text();
	
   $("[id^='rn_ProductMenu']").attr("aria-label", <?=$this->data['prodcat_menus']?>);
   $('.chosen-container .chosen-results li.active-result .result-selected').attr('aria-label', menuText );
   console.log("this is the selection" + menuText);
 });

</script>