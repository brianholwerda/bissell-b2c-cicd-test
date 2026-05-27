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
    <li>
        <? for ($i = 0; $i < $reportColumns; $i++): ?>            
            <? $header = $this->data['reportData']['headers'][$i]; ?>            
            <? if ($this->showColumn($value[$i], $header)): ?>
                <? if ($i < 4): ?>                    
                    <span class="rn_Element<?=$i + 1?>"><?($i === 3 && !empty($value[$i-1])) ? print "" : print $value[$i]?></span>                        
                <? else: ?>
                        <span class="rn_ElementsHeader"><?=$this->getHeader($header);?></span>
                        <span class="rn_ElementsData"><?=$value[$i];?></span>  
                <? endif; ?>
            <? endif; ?>            
        <? endfor; ?>        
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

