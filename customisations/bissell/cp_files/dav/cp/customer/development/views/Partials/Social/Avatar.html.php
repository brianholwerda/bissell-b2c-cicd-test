<span class="rn_UserAvatar" title="<?= $title ?>">
    <? if ($profileUrl): ?>
        <a itemprop="url" href="<?= $profileUrl ?>"<?= $target ? ' target="' . $target . '"' : '' ?>>
    <? endif; ?>
    <? if ($size !== '0'): ?>
    <span class="rn_Avatar <?= ($className ?: 'rn_Medium') ?> <?= ($avatarUrl) ? 'rn_Image' : 'rn_Placeholder' ?>">
        <? if ($avatarUrl): ?>
            <img itemprop="image" src="<?= $avatarUrl ?>" height="<?= $size ?>" width="<?= $size ?>" alt=""/>
        <? else: ?>
            <span class="rn_Default rn_DefaultColor<?= $defaultAvatar['color'] ?>" aria-hidden="true" role="presentation">
                <span class="rn_Liner"><?= $defaultAvatar['text'] ?></span>
            </span>
        <? endif; ?>
    </span>
    <? endif; ?>
    <? if ($displayName): ?>
        <span class="rn_DisplayName<?= $profileUrl ? '' : ' rn_DisplayNameDisabled' ?>" itemprop="name"><?= $displayName ?></span>
    <? endif; ?>
    <? if ($profileUrl): ?>
        </a>
    <? endif; ?>
</span>
