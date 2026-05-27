<? if($this->data['attrs']['is_video']) { //Video Library?>
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
		    // margin-left: -40px;
	        float: left;
		}
		.rn_AnswerGroup {
			left:9999px;
		}
		.rn_PaddleVideoList{
			height: 150px;
		}
		.leftScrollArrow{
			margin-left: 0px;
		}
		.rightScrollArrow{
			margin-right: -17px;
		}
		.filterBtn {
			background-color: #3873c4;
			color: white;
			padding: 16px;
			font-size: 16px;
			border: none;
			cursor: pointer;
		}

		.filterBtn:hover, .filterBtn:focus {
			background-color: #5b8cd0;
		}

		#searchInput {
			border-box: box-sizing;
			background-position: 14px 12px;
			background-repeat: no-repeat;
			font-size: 16px;
			padding: 14px 20px 12px 45px;
			border: none;
			border-bottom: 1px solid #ddd;
		}

		#searchInput:focus {outline: 3px solid #ddd;}

		.dropdown {
			position: relative;
			display: inline-block;
		}

		.dropdown-content {
			display: none;
			position: absolute;
			background-color: #f6f6f6;
			min-width: 230px;
			overflow: auto;
			border: 1px solid #ddd;
			z-index: 1;
		}

		.dropdown-content a {
			color: black;
			padding: 12px 16px;
			text-decoration: none;
			display: block;
		}

		.dropdown a:hover {background-color: #ddd;}

		.show {display: block;}
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
			<? if($this->data['attrs']['show_filter']) { ?>
			<div class="dropdown">
			<button class="filterBtn">Filter</button>
			  <div id="filterDropdown" class="dropdown-content">
				<input type="text" placeholder="Search.." id="searchInput">
			  </div>
			</div>
			<? } ?>
	    <? if($this->data['catError']) {?>
	    	<?=$this->data['catError']?>
	    <? } else { ?>
		        <? foreach($this->data['answerResults'] as $answerIndex => $answerGroup): ?>
		        	<div class="rn_topicContainer">
			        	<div id="rn_answerContainerTitle" class="rn_answerContainerTitle rn_PageInner"><h2 id="rn_subVideoHeader"><?=$answerGroup['category']?></h2></div>
			        	
			        	<? if($this->data['attrs']['is_video']) { //Video Library?>
			        	<button id="rn_backButton">
			        		<div aria-label="Back arrow scroll" class="leftScrollArrow scrollArrow">
			        			<img src="images/arrowleft.png" alt=""/>
			        		</div>
			        	</button>
			        	<? } ?>
			            <ul id="<?=$answerIndex?>" class="rn_CatList<?=$answerIndex?> rn_AnswerGroup">
			            	<? $i=0; ?>
			                <? foreach ($answerGroup as $answerId => $topicAnswer): ?>
			                	<?php if ($answerId !== 'category'): ?>
			                		<? if($this->data['attrs']['is_video']) { //Video Library?>
		                                <li class="rn_PaddleVideoList" video="<?=$i?>" role="group" aria-label="Video <?=$i?>">
		                                    <?if($answerId > 0) {?>
		                                        <? if($topicAnswer['playModal']) { ?>
		                                            <span videoId="<?=$topicAnswer['youTubeId']?>" title="<?=$topicAnswer['excerpt']?>" class="rn_PaddleVideo"><img src="<?=$topicAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail" alt=""/>
		                                                <div class="playVideo"></div>
		                                            <br/><span class="rn_PaddleVideoTitle"><a title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>"><?=$topicAnswer['title'];?></a></span></span>
		                                        <? } else { ?>
		                                            <a videoId="<?=$topicAnswer['youTubeId']?>" title="<?=$topicAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleVideo"><img src="<?=$topicAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail" alt=""/>
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
			            <? if($this->data['attrs']['is_video']) { //Video Library?>
			            <button id="rn_forwardButton">
			        		<div aria-label="Forward scroll arrow" class="rightScrollArrow scrollArrow">
			        			<img src="images/arrowleft.png" alt=""/>
			        		</div>
			            </button>
			        	<? } ?>
			            <div class="rn_answerContainerMore"><a href="/app/answers/list/c/<?=$answerIndex?>">See more <?=$answerGroup['category']?> answers</a><img class="rn_answerRightArrow" src="images/arrow-right.png" alt=""/></div>
						
			        </div>
		        <? endforeach; ?>
		    <? } ?>
	    </div>
	</div>
</div>
<div class="rn_answerContainerMore2"><a href="/app/answers/topics/p/<?= getUrlParm('p') ?>/c/67098">See All <?=$answerGroup['category']?></a></div>
<div id="modalVideoContainer" style="display:none;"></div>