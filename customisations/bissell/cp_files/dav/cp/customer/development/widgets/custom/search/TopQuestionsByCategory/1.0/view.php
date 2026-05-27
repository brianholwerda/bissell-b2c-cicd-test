<?php /* Originating Release: November 2015 */?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
	<rn:block id="top"/>
		<? if($this->data['attrs']['label_title']):?>
			<h2><?=$this->data['attrs']['label_title'];?></h2>
		<? endif;?>
		<div class="rn_ProductContainer">
		<? 
		$numColumns =  $this->data['attrs']['num_of_columns'];
 		$colWidth = round(100/$numColumns) - 2;
		$columnCounter = 0;
		$index = 0;
		$selectedProd = getUrlParm("p"); 
		//var_dump($selectedProd);
		//var_dump($this->data['results']);
		$max = sizeof($this->data['results']);
		for($k=0; $k<$max; $k++) {
		foreach($this->data['results'][$k] as $key => $value): 
			$filterID =  $value['hierList'];
			$columnCounter+=1;
			
			?>
			<rn:block id="preItem"/>
				<div class="rn_HierList rn_HierList_<?=$key;?>"   style="float:left;width:<?=$colWidth;?>%;" >
				
					<div id="rn_ProductHeader_<?=$value['hierList'];?>"  >
						<? if($this->data['attrs']['prod_cat_hyperlink'] == "Y") { ?>
						<a href="<?=$this->data['attrs']['report_page_url'] . $this->data['appendedParameters'] . \RightNow\Utils\Url::sessionParameter() . "/{$this->data['type']}/" . $value['hierList']; ?>">
							<div class="rn_HierListIcon rn_HierListIcon_<?=$key;?>"><em></em></div>
							<span class="rn_ProductHeader rn_ProductHeader<?=$value['hierList'];?>"><?=htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8');?></span>
						</a>
						<? } else { ?>
							<div class="rn_HierListIcon rn_HierListIcon_<?=$key;?>"><em></em></div>
							<span class="rn_ProductHeader rn_ProductHeader<?=$value['hierList'];?>"><?=htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8');?></span>
						<? } ?>
					</div>
                    <div class="catBox">
                    <!-- Answers for Category -->
                    	<div id="kari_<?=$value['hierList'];?>"></div>
						<rn:widget path="reports/TopAnswers" category_filter_id="#rn:php:$filterID#" limit="#rn:php:intval($this->data['attrs']['num_to_display'])#"/>
					</div>
				</div>
				<? $index++;
				if($columnCounter == $numColumns)
				{
					echo "<div style='clear:left' /></div>";
					$columnCounter = 0;
				}
        endforeach;
        } ?>
       </div>
    <rn:block id="bottom"/>
</div>
