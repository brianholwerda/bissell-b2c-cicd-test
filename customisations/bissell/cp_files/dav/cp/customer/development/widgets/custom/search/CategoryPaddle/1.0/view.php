<?php /* Originating Release: November 2015 */?>

<div id="paddleWrapper">
    <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
    	<div id="TopAnswersTitle">#rn:msg:CUSTOM_MSG_TOP_ANSWERS#</div>
    	<div id="rn_PaddleMenu">
    		<? foreach($this->data['results'] as $resultIndex => $resultGroup): ?>
                <ul class="PaddleList">
                    <? foreach ($resultGroup as $index => $item): ?>
                        <li class="rn_PaddleCategoryItem">
                            <rn:block id="preItem"/>
                            <?= $this->render('item', array('item' => $item)) ?>
                            <rn:block id="postItem"/>
                        </li>
                    <? endforeach; ?>
                </ul>
            <? endforeach; ?>
    	</div>
    </div>
    <div id="rn_answerContainer">
        <!--<div id="rn_answerContainerTitle" class="rn_answerContainerTitle"><?=$this->data['results'][0][0]['label'];?></div>--><hr>
        <div id="rn_answerContainerResults">
            <? foreach($this->data['answerResults'] as $answerIndex => $answerGroup): ?>
                <ul class="rn_CatList<?=$answerIndex?> rn_AnswerGroup">
                    <? $i=0; ?>
                    <? foreach ($answerGroup as $answerId => $paddleAnswer): ?>
                        <? if($answerIndex == "43") { //Video Library?>
                            <? if($i == 6) { ?>
                                <? break; ?>
                            <? } else { ?>
                                <li class="rn_PaddleVideoList">
                                    <?if($answerId > 0) {?>
                                        <? if($paddleAnswer['playModal']) { ?>
                                            <span videoId="<?=$paddleAnswer['youTubeId']?>" title="<?=$paddleAnswer['excerpt']?>" class="rn_PaddleVideo"><img src="<?=$paddleAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail"/>
                                                <div class="playVideo"></div>
                                            <br/><span class="rn_PaddleVideoTitle"><a title="<?=$paddleAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>"><?=$paddleAnswer['title'];?></a></span></span>
                                        <? } else { ?>
                                            <a videoId="<?=$paddleAnswer['youTubeId']?>" title="<?=$paddleAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleVideo"><img src="<?=$paddleAnswer['thumbNailUrl'];?>" class="rn_PaddleThumbnail"/>
                                            <br/><span class="rn_PaddleVideoTitle"><?=$paddleAnswer['title'];?></span></a>
                                        <? } ?>
                                    <? } else { ?>
                                        <?=$paddleAnswer['title'];?>
                                    <? } ?>
                                </li>
                            <? } ?>
                        <? } else { ?>
                            <li class="rn_PaddleAnswerList">
                                <?if($answerId > 0) {?>
                                    <?=$paddleAnswer['image']?><a title="<?=$paddleAnswer['excerpt']?>" href="/app/answers/detail/a_id/<?=$answerId?>" class="rn_PaddleAnswer"><?=$paddleAnswer['title'];?></a>
                                <? } else { ?>
                                    <?=$paddleAnswer['title'];?>
                                <? } ?>
                            </li>
                        <? } ?>
                        <? $i++; ?>
                    <? endforeach; ?>
                </ul>
            <? endforeach; ?>
        </div>
        <div id="rn_answerContainerMore"><a href="/app/answers/topics/c/<?=$this->data['results'][0][0]['id'];?>">#rn:msg:CUSTOM_MSG_SEE_MORE# <span class="rn_answerContainerTitle"><?=$this->data['results'][0][0]['label'];?></span> #rn:msg:ANSWERS_LWR_LBL#</a><img class="rn_answerRightArrow" src="images/arrow-right.png"/></div>
        <hr>
    </div>
    <div id="modalVideoContainer" style="display:none;"></div>
</div>
