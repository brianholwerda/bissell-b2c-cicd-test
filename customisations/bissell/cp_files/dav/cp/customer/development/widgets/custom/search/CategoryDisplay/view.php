<rn:meta controller_path="custom/search/CategoryDisplay" js_path="custom/search/CategoryDisplay" css_path="css/widgets/custom/CategoryDisplay.css"/>

<? //if ($js['top_c']): ?>
    <div id="CategoryDisplayContainer">
        <?
        foreach ($js['cats'] as $cat):
            if ($js['top_c'] == $cat['ID']) :
                $item_class = "selected_text display_item";
            else : $item_class = "display_item";
            endif;
            ?>
            <div id="CategoryDisplay_<?= $cat['ID']; ?>" class="<?= $item_class; ?>">
                <? /*<div class="display_text">*/ ?>
                    <a href="/app/browse_topics/c/<?= $cat['url']; ?>" class="display_link"><?= $cat['Display_Text']; ?></a>
                <? /*</div>*/ ?>
                <? /*<div class="display_text"><?= $cat['Display_Text']; ?></div>*/ ?>
            </div>

        <? endforeach; ?>
    </div>
<? //endif; ?>