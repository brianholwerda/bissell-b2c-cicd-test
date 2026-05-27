<?php /* Originating Release: August 2013 */?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
	<rn:block id="top"/>
		<? if($this->data['attrs']['label_title']):?>
			<h2><?=$this->data['attrs']['label_title'];?></h2>
		<? endif;?>
		<? 
		$numColumns =  $this->data['attrs']['num_of_columns'];
 		$colWidth = round(100/$numColumns);
		$columnCounter = 0;
		$index = 0;
		$selectedProd = getUrlParm("p"); 
		//var_dump($selectedProd);
		//var_dump($this->data['results']);
		foreach($this->data['results'] as $key => $value): 
			$filterID =  $value['hierList'];
			$columnCounter+=1;

			?>
			<rn:block id="preItem"/>
				<div class="rn_HierList rn_HierList_<?=$key;?>"   style="float:left;width:<?=$colWidth;?>%;" >
					<h3 id="rn_ProductHeader_<?=$value['hierList'];?>" style="padding-bottom:10px;" >
						<a href="<?=$this->data['attrs']['report_page_url'] . $this->data['appendedParameters'] . \RightNow\Utils\Url::sessionParameter() . "/{$this->data['type']}/" . $value['hierList']; ?>">
							<div class="rn_HierListIcon rn_HierListIcon_<?=$key;?>"><em></em></div>
							<span class="rn_ProductHeader rn_ProductHeader<?=$value['hierList'];?>"><?=htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8');?></span>
						</a>
					</h3>
                    <div class="catBox">
                    <!-- Sub-Categories -->
					<? if(count($value['subItems'])):?>
						<rn:block id="preSubList"/> 
							<? for($i = 0; $i < count($value['subItems']); $i++): ?>
								<rn:block id="listItem">
									<? if (strcmp($selectedProd,$value['subItems'][$i]['hierList'])) 
										{ 
											$setClass='';
										}
										else
										{ 
											$setClass='rn_SubSelected';
										
										} 
									?>
									<ul class="subcats <?= $setClass ?>">
										<li>
											<a href="<?=$this->data['attrs']['report_page_url'] . $this->data['appendedParameters'] . \RightNow\Utils\Url::sessionParameter() . "/{$this->data['type']}/" . $value['subItems'][$i]['hierList'];?>">
												<?=htmlspecialchars($value['subItems'][$i]['label'], ENT_QUOTES, 'UTF-8') ?>
											</a>
										</li>
									</ul>	
                                    
								</rn:block>
                                
							<?endfor;?>
                            
						<rn:block id="postSubList"/>
					<? endif;?> 
                    </div>
				</div>
				<? $index++;
				if($columnCounter == $numColumns)
				{
					echo "<div style='clear:left' /></div>";
					$columnCounter = 0;
				}
        endforeach;?>
    <rn:block id="bottom"/>
</div>

<? /*
<!--
<rn:block id='ProductCategoryList-top'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-preItem'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-preSubList'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-listItem'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-postSubList'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-postItem'>

</rn:block>
-->

<!--
<rn:block id='ProductCategoryList-bottom'>

</rn:block>
-->

*/ ?>