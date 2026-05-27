<?php /* Originating Release: May 2014 */?>
<? if ($this->data['readOnly']): ?>
    <rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else: ?>
<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>" >
<rn:block id="top"/>
<? if ($this->data['attrs']['label_input']): ?>
    <div id="rn_<?= $this->instanceID ?>_LabelContainer" >
        <rn:block id="preLabel"/>
        <label for="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>" id="rn_<?= $this->instanceID ?>_Label" class="rn_Label rn_Hidden">
        <? if ($this->data['attrs']['required']) { echo($this->data['attrs']['label_input'] . '<span class="rn_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</span>');} else { echo($this->data['attrs']['label_input']);} ?>
        <? if ($this->data['attrs']['required']): ?>
            <rn:block id="preRequired"/>
            <span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL) ?></span>
            <rn:block id="postRequired"/>
        <? endif; ?>
        <? if ($this->data['attrs']['hint']): ?>
            <span class="rn_ScreenReaderOnly"><?= $this->data['attrs']['hint'] ?></span>
        <? endif; ?>
        </label>
        <rn:block id="postLabel"/>
    </div>
<? endif; ?>
<? if ($this->data['displayType'] === 'Textarea'): ?>
<rn:block id="preInput"/>
    <? /* if ($this->data['attrs']['required']) {echo('<div class="tb_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</div>');} */ ?>
    <textarea id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>" class="rn_TextArea" rows="7" cols="60" name="<?= $this->data['inputName'] ?>" <?= $this->outputConstraints(); ?> ><?= $this->data['value'] ?></textarea>
<rn:block id="postInput"/>
<? if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
    <rn:block id="preHint"/>
    <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
    <rn:block id="postHint"/>
<? endif; ?>
<? else: ?>
<rn:block id="preInput"/>
    <? /* if ($this->data['attrs']['required']) {echo('<div class="tb_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</div>');} */ ?>
    <input type="<?=$this->data['inputType']?>" id="rn_<?=$this->instanceID?>_<?=$this->data['js']['name']?>" name="<?= $this->data['inputName'] ?>" class="rn_<?=$this->data['displayType']?>" <?=$this->outputConstraints();?> <?if($this->data['value'] !== null && $this->data['value'] !== '') echo "value='{$this->data['value']}'";?> />
<rn:block id="postInput"/>
<? if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
    <rn:block id="preHint"/>
    <span class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
    <rn:block id="postHint"/>
<? endif; ?>
    <? if ($this->data['attrs']['require_validation']): ?>
    <div class="rn_TextInputValidate">
        <div id="rn_<?= $this->instanceID ?>_LabelValidateContainer">
            <rn:block id="preValidateLabel"/>
            <label for="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>_Validate" id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>_LabelValidate" class="rn_Label"><?printf($this->data['attrs']['label_validation'], $this->data['attrs']['label_input']) ?>
            <? if ($this->data['attrs']['required']): ?>
                <rn:block id="preValidateRequired"/>
                <span class="rn_Required"><?= \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) ?></span><span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL) ?></span>
                <rn:block id="postValidateRequired"/>
            <? endif; ?>
            </label>
            <rn:block id="postValidateLabel"/>
        </div>
        <rn:block id="preValidateInput"/>
        <? if ($this->data['attrs']['required']) {echo('<div class="tb_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</div>');} ?>
        <input type="<?= $this->data['inputType'] ?>" id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>_Validate" name="<?= $this->data['inputName'] ?>_Validation" class="rn_<?=$this->data['displayType']?> rn_Validation" <?= $this->outputConstraints(); ?> value="<?= $this->data['value'] ?>"/>
        <rn:block id="postValidateInput"/>
    </div>
   <? endif; ?>
<? endif; ?>
<rn:block id="bottom"/>
</div>
<? endif; ?>