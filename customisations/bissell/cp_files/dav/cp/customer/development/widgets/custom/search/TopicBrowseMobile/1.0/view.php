
<style>
	.rn_PaddleAnswerList {
	    white-space: nowrap;
	    overflow: hidden;
	}
</style>

<div id="rn_<?= $this->instanceID ?>" class="topicBrowse">
	<div id="rn_answerContainer">
	    <div id="rn_answerContainerResults">
	    <? if($this->data['catError']) {?>
	    	<?=$this->data['catError']?>
	    <? } else { ?>
		        <? foreach($this->data['answerResults'] as $answerIndex => $answerGroup): ?>
		        	<div class="rn_topicContainer">
			        	<div id="rn_answerContainerTitle" class="rn_answerContainerTitle rn_PageInner"><h1><?=$answerGroup['category']?></h1></div>
			            <ul id="<?=$answerIndex?>" class="rn_CatList<?=$answerIndex?> rn_AnswerGroup">
			            	<? $i=0; ?>
			                <? foreach ($answerGroup as $answerId => $topicAnswer): ?>
			                	<?php if ($answerId !== 'category'): ?>
			                        	<? if($i == 5): ?>
			                                <? break; ?>
			                            <?php else: ?>
						                    <li class="rn_PaddleAnswerList">
						                    	<?php if ($topicAnswer['title'] !== "No Results"): ?>
						                        	<?=$topicAnswer['image']?><a title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleAnswer"><?=$topicAnswer['title'];?></a>
						                        <?php else: ?>
						                        	<?=$topicAnswer['title'];?>
					                        	<?php endif; ?>
						                    </li>
						                <?php endif; ?>
			                    <?php endif; ?>
			                    <? $i++; ?>
			                <? endforeach; ?>
			            </ul>
			            <div class="rn_answerContainerMore"><a href="/app/answers/list/c/<?=$answerIndex?>">See more <?=$answerGroup['category']?> answers</a><img class="rn_answerRightArrow" src="images/arrow-right.png"/></div>
			        </div>
		        <? endforeach; ?>
		    <? } ?>
	    </div>
	</div>
</div>
<div id="modalVideoContainer" style="display:none;"></div>