<? if($this->data['category'] === "67098") { //Video Library?>
	<style>
		.rn_topicContainer {
		    width: 100% !important;
		    float: none !important;
		    padding: 20px 10px 20px 20px;
		    min-height: 225px;
		}
		.rn_answerContainerMore {
			display:none;
		    /*padding-top: 15px;
		    float: left !important;
	        position: absolute;
			margin-top: 125px;*/
		}
		.rn_PageInner {
		    width: 100%;
		    clear: both;
		}
		div#rn_answerContainerTitle, .rn_answerContainerMore {
		    margin-left: -20px;
		}
		ul.rn_AnswerGroup {
		    margin-left: 0px;
		    padding-top: 10px;
		    display:inline-block;
		    /*overflow-y: hidden;*/
		    white-space: nowrap;
		    margin-left: -40px;
	        float: left;
		}
		.rn_AnswerGroup {
			left:9999px;
		}
		.rn_PaddleVideoList{
			height: 150px;
		}
	</style>
<? } else {?>
	<style>
		.rn_PaddleAnswerList {
		    white-space: nowrap;
		    overflow: hidden;
		}
	</style>
<? } ?>

<div id="rn_<?= $this->instanceID ?>" class="topicBrowse">
	<div id="rn_answerContainer">
	    <div id="rn_answerContainerResults">
	    <? if($this->data['catError']) {?>
	    	<?=$this->data['catError']?>
	    <? } else { ?>
		        <? foreach($this->data['answerResults'] as $answerIndex => $answerGroup): ?>
		        	<div class="rn_topicContainer">
			        	<div id="rn_answerContainerTitle" class="rn_answerContainerTitle rn_PageInner"><h1><?=$answerGroup['category']?></h1></div>
			        	<? if($this->data['category'] === "67098") { //Video Library?>
			        		<div class="leftScrollArrow scrollArrow">
			        			<img src="images/arrowleft.png"/>
			        		</div>
			        	<? } ?>
			            <ul id="<?=$answerIndex?>" class="rn_CatList<?=$answerIndex?> rn_AnswerGroup">
			            	<? $i=0; ?>
			                <? foreach ($answerGroup as $answerId => $topicAnswer): ?>
			                	<?php if ($answerId !== 'category'): ?>
			                		<? if($this->data['category'] === "67098") { //Video Library?>
		                                <li class="rn_PaddleVideoList" video="<?=$i?>">
		                                    <?if($answerId > 0) {?>
		                                        <? if($topicAnswer['playModal']) { ?>
		                                            <span videoId="<?=$topicAnswer['youTubeId']?>" title="<?=$topicAnswer['excerpt']?>" class="rn_PaddleVideo"><img src="<?=$topicAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail"/>
		                                                <div class="playVideo"></div>
		                                            <br/><span class="rn_PaddleVideoTitle"><a title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>"><?=$topicAnswer['title'];?></a></span></span>
		                                        <? } else { ?>
		                                            <a videoId="<?=$topicAnswer['youTubeId']?>" title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleVideo"><img src="<?=$topicAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail"/>
		                                            <br/><span class="rn_PaddleVideoTitle"><?=$topicAnswer['title'];?></span></a>
		                                        <? } ?>
		                                    <? } else { ?>
		                                        <?=$topicAnswer['title'];?>
		                                    <? } ?>
		                                </li>
			                        <? } else { ?>
			                        	<? if($i == 5) { ?>
			                                <? break; ?>
			                            <? } else { ?>
						                    <li class="rn_PaddleAnswerList">
						                    	<?php if ($topicAnswer['title'] !== "No Results"): ?>
						                        	<?=$topicAnswer['image']?><a title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleAnswer"><?=$topicAnswer['title'];?></a>
						                        <?php else: ?>
						                        	<?=$topicAnswer['title'];?>
					                        	<?php endif; ?>
						                    </li>
						                <? } ?>
					                <? } ?>
			                    <?php endif; ?>
			                    <? $i++; ?>
			                <? endforeach; ?>
			            </ul>
			            <? if($this->data['category'] === "67098") { //Video Library?>
			        		<div class="rightScrollArrow scrollArrow">
			        			<img src="images/arrowleft.png"/>
			        		</div>
			        	<? } ?>
			            <div class="rn_answerContainerMore"><a href="/app/answers/list/c/<?=$answerIndex?>">See more <?=$answerGroup['category']?> answers</a><img class="rn_answerRightArrow" src="images/arrow-right.png"/></div>
			        </div>
		        <? endforeach; ?>
		    <? } ?>
	    </div>
	</div>
</div>
<div id="modalVideoContainer" style="display:none;"></div>