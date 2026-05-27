<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

    <span>
        <span>
            <a href="/app/home" class="titleText">Home <i class="fas fa-angle-right" alt="" aria-hidden="true"></i></a>
        </span>
        <? if ($this->data['js']['a_id'] == 1360 || $this->data['js']['a_id'] == 2555) { ?>
        <!-- This handles the More Contact Options Answer -->
            <span>
				<a href="/app/contact" class="titleText">Contact Us
					<i class="fas fa-angle-right" alt="" aria-hidden="true"> </i>
				</a>
			</span>
            <?= $this->data['js']['a_name']; ?>
		<? } else { ?>
        <? if ($this->data['js']['links']) : ?>
            <? foreach ($this->data['js']['links'] as $link) : ?>
                <?/*<span> <i class="fas fa-angle-right" alt="" aria-hidden="true"></i></span>*/?>
                <? if(!empty($link['url']) && !empty($link['Name'])) { ?>
					<a href="/app/answers/list/c/<?= $link['url']; ?>" class="titleText"><?= $link['Name']; ?> <i class="fas fa-angle-right" alt="" aria-hidden="true"></i></a>
				<? } ?>
            <? endforeach; ?>
        <? endif; ?>
        <? if ($this->data['js']['a_id']) : ?>     
            <span></span>
            <span><?= $this->data['js']['a_name']; ?></span>
        <? endif; ?>
        <? if ($this->data['js']['page_url']) : ?>     
            <? foreach ($this->data['js']['page_url'] as $p_url) : ?>
                <?/*<span><i class="fas fa-angle-right" alt="" aria-hidden="true"></i></span>*/?>
                <span><?= $p_url; ?></span>
            <? endforeach; ?>
        <? endif; ?>
        <? } ?>
        <a id="ctl00_ContentMap_SkipLink"></a>
    </span>
    
</div>