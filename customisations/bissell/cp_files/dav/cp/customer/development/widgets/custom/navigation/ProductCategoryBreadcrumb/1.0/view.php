<? /* Overriding ProductCategoryBreadcrumb's view */ ?>
<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<rn:block id="top"/>
    <nav aria-label="breadcrumbs">
        <rn:block id="topNav"/>
        <p id="rn_<?= $this->instanceID ?>_Label" class="rn_ScreenReaderOnly"><?= $this->data['attrs']['label_screenreader_intro'] ?></p>
        <ol aria-labelledby="rn_<?= $this->instanceID ?>_Label">
            <? for ($i = 0; $i < count($this->data['levels']); $i++): ?>
                <? $level = $this->data['levels'][$i]; ?>
                <? $className = ($i === count($this->data['levels']) - 1) ? 'rn_CurrentItem' : ''; ?>
                <rn:block id="breadcrumbListItem">
                <li class="rn_BreadcrumbLevel rn_Level<?= $i ?> <?= $className ?>" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                <i class="fas fa-angle-right" alt=" " aria-hidden="true"></i>
                    <a href="<?= $this->data['attrs']['link_url'] ?>/<?= $this->data['paramKey'] ?>/<?= $level['id'] . \RightNow\Utils\Url::sessionParameter() ?>" itemprop="url">
                        <span itemprop="title"><?= $level['label'] ?></span>
                        <?/*i class="fas fa-angle-right" alt=" " aria-hidden="true"></i>*/?>
                    </a>
                    
                </li>
                </rn:block>
            <? endfor; ?>
        </ol>
        <rn:block id="bottomNav"/>
    </nav>
    <rn:block id="bottom"/>
</div>
