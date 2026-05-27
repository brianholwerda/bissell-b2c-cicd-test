<? if (is_array($this->data['reportData']['data']) && count($this->data['reportData']['data']) > 0): ?>
    <? if ($this->data['reportData']['row_num']): ?>
        <? $reportColumns = count($this->data['reportData']['headers']);
           foreach ($this->data['reportData']['data'] as $value): ?>
                <? for ($i = 0; $i < $reportColumns; $i++): ?>
                    <? if ($i === 0): ?>
                        /app/answers/detail/a_id/<?=$value[$i];?>                          
                    <? endif; ?> 
                <? endfor; ?>
        <? endforeach; ?>
    <? endif; ?>
<? endif; ?>

