<?php /* Originating Release: May 2018 */?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>
    <div id="rn_<?=$this->instanceID;?>_Alert" role="alert" class="rn_ScreenReaderOnly"></div>
    <rn:block id="preLoadingIndicator"/>
    <div id="rn_<?=$this->instanceID;?>_Loading"></div>
    <rn:block id="postLoadingIndicator"/>
    <div id="rn_<?=$this->instanceID;?>_Content" class="rn_Content">
        <rn:block id="topContent"/>
        <? if (is_array($this->data['reportData']['data']) && count($this->data['reportData']['data']) > 0): ?>
        <rn:block id="preResultList"/>
        
            <ul>
        <rn:block id="topResultList"/>
       
        <? $reportColumns = count($this->data['reportData']['headers']);
	       $i=0;
           foreach ($this->data['reportData']['data'] as $value): 
           
           //print_r($value);
           ?>
           <rn:block id="resultListItem">
           <li class="rn_PaddleVideoList" video="<?=$value[2]?>">
                <?if($value[2] > 0) {?>
                    <? if($value[8]) { ?>
                        <span videoId="<?=$value[6]?>" title="<?=$value[9]?>" class="rn_PaddleVideo"><img src="https://i1.ytimg.com/vi/<?=$value[6]?>/mqdefault.jpg" class="rn_PaddleThumbnail"/>
                            <div class="playVideo"></div>
                        <br/><span class="rn_PaddleVideoTitle"><a title="<?=$value[9]?>" href="/app/answers/detail/a_id/<?=$value[2]?>"><?=$value[0]?></a></span></span>
                    <? } else { ?>
                        <a videoId="<?=$value[6]?>" title="<?=$value[9]?>" href="/app/answers/detail/a_id/<?=$value[2]?>" class="rn_PaddleVideo"><img src="https://i1.ytimg.com/vi/<?=$value[6]?>/mqdefault.jpg" class="rn_PaddleThumbnail"/>
                        <br/><span class="rn_PaddleVideoTitle"><?=$value[0];?></span></a>
                    <? } ?>
                <? } else { ?>
                    <?=$value[0];?>
                <? } ?>
            </li>

                           
              
                       </rn:block>

           <? endforeach; ?>
        <rn:block id="bottomResultList"/>
            </ul>
        <rn:block id="postResultList"/>
        <? else: ?>
        <rn:block id="noResultListItem"/>
        <? endif; ?>
        <rn:block id="bottomContent"/>
    </div>
    <rn:block id="bottom"/>
</div>
