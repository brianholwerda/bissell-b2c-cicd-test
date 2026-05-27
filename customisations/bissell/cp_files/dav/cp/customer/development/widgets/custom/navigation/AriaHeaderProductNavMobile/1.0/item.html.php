<a class="rn_Mobile_ListItemLink rn_Hidden" href="<?= $this->data['itemLink'] . $item['hierList'] ?>">
    This is the label<?= \RightNow\Utils\Text::escapeHtml($item['label']) ?>
</a>

<? if (count($item['subItems'])): ?>
    <rn:block id="preSubList"/>
    <ul class="rn_Mobile_SubItemList" aria-controls="rn_Mobile_ProdCatSub2_li_<?=$subItem2['id'] ?>">
	    <li id="rn_Mobile_prodsupport_Back_<?=$subItem['id'];?>" class="rn_Hidden" >
	    	<a href="javascript:void(0)"><img class="rn_chevronRight" src="/euf/assets/images/chevronRight.png">#rn:msg:CUSTOM_MSG_PRODUCT_SUPPORT#</a>
	    </li>

    <? foreach ($item['subItems'] as $subItem): 
	       
        $this->data['resultsChildren'] = $this->CI->model('Prodcat')->getDirectDescendants(
            	$this->data['attrs']['data_type'],
				$subItem['id']
				)->result; 
				
		//print_r($this->data['resultsChildren']);
    ?>
        <rn:block id="subListItem">
        <li class="rn_Mobile_ProductCategorySubItem" id="rn_Mobile_subItem_<?=$subItem['id'];?>" >
          
           <?$label = \RightNow\Utils\Text::escapeHtml($subItem['label']) ?>     
                      
				
            <a id="rn_Mobile_productLink_<?=$subItem['id'];?>" href="javascript:;"  onClick="showHideSubmMenu(<?=$subItem['id'];?>)">
	           <img class="rn_chevronLeft rn_Hidden" src="/euf/assets/images/chevronRight.png">
	           <?= \RightNow\Utils\Text::escapeHtml($subItem['label']) ?>
	           <img class="rn_chevronRight" src="/euf/assets/images/chevronLeft.png">
            </a>
            
           
           <ul id="rn_Mobile_ProdCatSub2_<?= $subItem['id']?>" class="rn_Hidden rn_Mobile_SubItemList2 rn_Mobile_OpenMenu rn_Mobile_SelectedMenu ">
	            
	        <? foreach ($this->data['resultsChildren'] as $subItem2	): 
	   		   //print_r($subItem2);
	        ?>
	        <?$submenu = \RightNow\Utils\Text::escapeHtml($subItem2['label'])?>
			<li id="rn_Mobile_ProdCatSub2_li_<?=$subItem2['id'] ?>" class="rn_Mobile_ProductCategorySubItem2 prodcatSub_<?=$subItem2['id'] ?>">
			<a href="<?= $this->data['itemLink'] .$subItem['hierList'] . ',' . $subItem2['id'] ?>" aria-label="Submenu of  <?=$label?>, <?=$submenu?> ">
            <?= \RightNow\Utils\Text::escapeHtml($subItem2['label']) ?>
            
        	</a>
			</li>
			<? endforeach; ?>
            </ul>
        </li>
        </rn:block>
    <? endforeach; ?>
    </ul>
    <rn:block id="postSubList"/>
<? endif; ?>

