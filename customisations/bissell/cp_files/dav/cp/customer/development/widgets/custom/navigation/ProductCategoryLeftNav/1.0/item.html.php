<a class="rn_ListItemLink rn_Hidden" href="<?= $this->data['itemLink'] . $item['hierList'] ?>">
    This is the label<?= \RightNow\Utils\Text::escapeHtml($item['label']) ?>
</a>

<? if (count($item['subItems'])): ?>
    <rn:block id="preSubList"/>
    <ul class="rn_SubItemList" aria-controls="rn_ProdCatSub2_li_<?=$subItem2['id'] ?>">
    <? foreach ($item['subItems'] as $subItem): 
	       
        $this->data['resultsChildren'] = $this->CI->model('Prodcat')->getDirectDescendants(
            	$this->data['attrs']['data_type'],
				$subItem['id']
				)->result; 
				
		//print_r($this->data['resultsChildren']);
    ?>
        <rn:block id="subListItem">
        <li class="rn_ProductCategorySubItem" >
          
           <?$label = \RightNow\Utils\Text::escapeHtml($subItem['label']) ?>     
            <a aria-controls="rn_ProdCatSub2_<?= $subItem['id']?>" aria-label="Toggle <?=$label?> menu"  id="myLink_<?=$subItem['id'];?>" href="#" onclick="OpenCloseLeftNav('<?=$subItem['id'];?>');return false;" class="<? if($subItem['id'] != \RightNow\Utils\Url::getParameter('p')) { ?>rn_ClosedMenu <? } else { ?> rn_OpenMenu <? } ?>">
                <span class="rn_AriaHidden">open</span> 
            </a>
          
				
            <a id="productLink_<?=$subItem['id'];?>" href="<?= $this->data['itemLink'] . $subItem['hierList'] ?>" >
	           <?= \RightNow\Utils\Text::escapeHtml($subItem['label']) ?>
            </a>
            
           
           <ul id="rn_ProdCatSub2_<?= $subItem['id']?>" class="rn_SubItemList2 <? if($subItem['id'] != \RightNow\Utils\Url::getParameter('p')) { ?> rn_Hidden <? } else { ?> rn_OpenMenu rn_SelectedMenu  <? } ?>">
	            
	        <? foreach ($this->data['resultsChildren'] as $subItem2	): 
	   		   //print_r($subItem2);
	        ?>
	        <?$submenu = \RightNow\Utils\Text::escapeHtml($subItem2['label'])?>
			<li id="rn_ProdCatSub2_li_<?=$subItem2['id'] ?>" class="rn_ProductCategorySubItem2">
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

