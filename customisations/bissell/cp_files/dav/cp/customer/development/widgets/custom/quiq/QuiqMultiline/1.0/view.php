<!--
<rn:block id='Multiline-top'>

</rn:block>
-->

<!--
<rn:block id='Multiline-preLoadingIndicator'>

</rn:block>
-->

<!--
<rn:block id='Multiline-postLoadingIndicator'>

</rn:block>
-->

<!--
<rn:block id='Multiline-topContent'>

</rn:block>
-->

<!--
<rn:block id='Multiline-preResultList'>

</rn:block>
-->

<!--
<rn:block id='Multiline-topResultList'>

</rn:block>
-->


<rn:block id='Multiline-resultListItem'>
<li quiq-item-container>

    <? for ($i = 0; $i < $reportColumns; $i++): ?>
        <? $id = $this->data['attrs']['counter']++; ?>
        <? $header = $this->data['reportData']['headers'][$i]; ?>
        <? $quiq_message_sender  = $this->data['attrs']['send_message_column_index'] === $i ? 'quiq-message-sender' : ''; ?>
        <? $quiq_message_content = $this->data['attrs']['message_column_index'] === $i ? 'quiq-message-content' : ''; ?>
        <? $is_render_and_hide_column = strpos((string) $i, $this->data['attrs']['render_and_hide_column_indexes']) !== false; ?>
        <? $limit_column = (int) $this->data['attrs']['limit_column_lengths'][$i] ?>


        <? if($limit_column > 0){
          $showing = substr($value[$i], 0, $limit_column);
          $hidden  = substr($value[$i], $limit_column);
        } else {
          $showing = $value[$i];
          $hidden  = null;
        }?>

        <?
          if($hidden !== null){
            $render_value = '<span>'.$showing.'<span data-togglee="More_'.$id.'" class="rn_Hidden">'.$hidden.'</span></span><button data-toggler="More_'.$id.'" data-togglee="More_'.$id.'" quiq-ignore-text>more</button><button class="rn_Hidden" data-toggler="More_'.$id.'" data-togglee="More_'.$id.'" quiq-ignore-text>less</button>';
          }
          else {
            $render_value = $showing;
          }
        ?>


        <? if ($this->showColumn($value[$i], $header) && !$is_render_and_hide_column):
            if ($i < 3):
                if ($i === 0): ?>
                    <div <?=$quiq_message_content?> <?=$quiq_message_sender?> class="rn_Element<?=$i + 1?>"><h3><?=$render_value?></h3></div>
                <? else: ?>
                    <span <?=$quiq_message_content?> <?=$quiq_message_sender?> class="rn_Element<?=$i + 1?>"><?=$render_value?></span>
                <? endif; ?>
            <? else: ?>
                <span <?=$quiq_message_sender?> class="rn_ElementsHeader"><?=$this->getHeader($header);?></span>
                <span <?=$quiq_message_content?> <?=$quiq_message_sender?> class="rn_ElementsData"><?=$render_value?></span>
            <? endif; ?>
        <? endif; ?>

        <? if ($is_render_and_hide_column): ?>
          <span <?=$quiq_message_content?> class="rn_Hidden"><?=$render_value?></span>
        <? endif; ?>
    <? endfor; ?>

    <? if($this->data['attrs']['show_send_full_button'] === true): ?>
    <button quiq-message-sender>send full answer</button>
    <? endif; ?>
</li>
</rn:block>

<!--
<rn:block id='Multiline-bottomResultList'>

</rn:block>
-->

<!--
<rn:block id='Multiline-postResultList'>

</rn:block>
-->

<!--
<rn:block id='Multiline-noResultListItem'>

</rn:block>
-->

<!--
<rn:block id='Multiline-bottomContent'>

</rn:block>
-->

<!--
<rn:block id='Multiline-bottom'>

</rn:block>
-->
