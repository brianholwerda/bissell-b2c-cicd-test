<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

    <ul>
        <? foreach($this->data['js']['icon_items'] as $key => $value){?>
            <li> 
                <a href="<?=$value['link']?>">
                    <img src="<?=$value['image_src']?>" alt="<?=$value['image_alt']?>">
                </a>
                <div>
<? /*                   <a href="<?=$value['link']?>">
                        <?=$value['label']?>
                    </a> */ ?>
                </div>
            </li>
        <?}?>
    </ul>
    <?/* 
    this width needs to be dynamic because we are basing it on an attribute of
    how many icons should be on each row
    */?>
    <style>
        .<?= $this->classList ?> ul li {
            width: <?=$this->data['js']['width']?>%;
        }
    </style>
    
    
</div>