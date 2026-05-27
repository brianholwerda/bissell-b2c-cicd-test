<?php /* Originating Release: May 2014 */?>
<? if ($this->data['readOnly']): ?>
    <rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else: ?>
<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>
<? if ($this->data['attrs']['label_input'] && $this->data['displayType'] !== 'Radio'): ?>
    <div id="rn_<?= $this->instanceID ?>_LabelContainer">
        <rn:block id="preLabel"/>
        <label for="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>" id="rn_<?= $this->instanceID ?>_Label" class="rn_Label">
        <? if ($this->data['attrs']['required']) { echo($this->data['attrs']['label_input'] . '<span class="rn_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</span>');} else { echo($this->data['attrs']['label_input']);} ?>
        <? if ($this->data['attrs']['required']): ?>
            <rn:block id="preRequired"/>
            <rn:block id="postRequired"/>
        <? endif; ?>
        <? if ($this->data['attrs']['hint']): ?>
            <span class="rn_ScreenReaderOnly"><?= $this->data['attrs']['hint'] ?></span>
        <? endif; ?>
        </label>
        <rn:block id="postLabel"/>
    </div>
<? endif; ?>
<? if ($this->data['displayType'] === 'Select'): ?>
    <rn:block id="preInput"/>
    <? if ($this->data['attrs']['required']) { echo('<div class="tb_Required">' . \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) . '</div>');} ?>
    <select id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>" name="<?= $this->data['inputName'] ?>">
        <rn:block id="inputTop"/>
    <? if (!$this->data['hideEmptyOption']): ?>
        <option value="" disabled selected><?= $this->data['attrs']['placeholder'] ?></option>
    <? endif; ?>
    <? if (is_array($this->data['menuItems'])): ?>
        <? foreach ($this->data['menuItems'] as $key => $item): ?>
            <option value="<?= $key ?>" <?= $this->outputSelected($key) ?>><?=\RightNow\Utils\Text::escapeHtml($item);?></option>
        <? endforeach; ?>
    <? endif; ?>
        <rn:block id="inputBottom"/>
    </select>
    <rn:block id="postInput"/>
    <? if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
        <rn:block id="preHint"/>
        <span id="rn_<?= $this->instanceID ?>_Hint" class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
        <rn:block id="postHint"/>
    <? endif; ?>
<? else: ?>
    <? if ($this->data['displayType'] === 'Checkbox'): ?>
        <rn:block id="preInput"/>
        <input type="checkbox" id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['name'] ?>" name="<?= $this->data['inputName'] ?>" <?= $this->outputChecked(1) ?> value="1"/>
        <rn:block id="postInput"/>
        <? if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
            <rn:block id="preHint"/>
            <span id="rn_<?= $this->instanceID ?>_Hint" class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <rn:block id="postHint"/>
        <? endif; ?>
    <? else: ?>
        <fieldset>
        <? if ($this->data['attrs']['label_input']): ?>
            <legend id="rn_<?= $this->instanceID ?>_Label" class="rn_Label">
                <rn:block id="preLabel"/>
                <?= $this->data['attrs']['label_input'] ?>
                <? if ($this->data['attrs']['required']): ?>
                    <rn:block id="preRequired"/>
                    <span class="rn_Required"><?= \RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL) ?></span><span class="rn_ScreenReaderOnly"> <?= \RightNow\Utils\Config::getMessage(REQUIRED_LBL)?></span>
                    <rn:block id="postRequired"/>
                <? endif; ?>
                <rn:block id="postLabel"/>
            </legend>
        <? endif; ?>
        <rn:block id="preInput"/>
        <? for($i = 1; $i >= 0; $i--):
                $id = "rn_{$this->instanceID}_{$this->data['js']['name']}_$i"; ?>
            <rn:block id="preRadioInput"/>
            <input type="radio" name="<?= $this->data['inputName']?>" id="<?= $id ?>" <?= $this->outputChecked($i) ?> value="<?= $i ?>"/>
            <rn:block id="postRadioInput"/>
            <rn:block id="preRadioLabel"/>
            <label for="<?= $id ?>">
            <?= $this->data['radioLabel'][$i] ?>
            <? if ($this->data['attrs']['hint'] && $i === 1): ?>
                <span class="rn_ScreenReaderOnly"><?= $this->data['attrs']['hint'] ?></span>
            <? endif; ?>
            </label>
            <rn:block id="postRadioLabel"/>
        <? endfor; ?>
        <rn:block id="postInput"/>
        <? if ($this->data['attrs']['hint'] && $this->data['attrs']['always_show_hint']): ?>
            <rn:block id="preHint"/>
            <span id="rn_<?= $this->instanceID ?>_Hint"  class="rn_HintText"><?= $this->data['attrs']['hint'] ?></span>
            <rn:block id="postHint"/>
        <? endif; ?>
        </fieldset>
    <?endif; ?>
<? endif; ?>
<rn:block id="bottom"/>
</div>
<? endif; ?>
