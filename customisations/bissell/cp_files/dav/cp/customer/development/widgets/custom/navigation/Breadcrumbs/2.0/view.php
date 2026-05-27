<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

    <span>
        <span>
            <a href="/app/home" class="titleText">Home</a> &nbsp;|&nbsp;
        </span>
        <? if ($this->data['js']['a_id'] == 1360 || $this->data['js']['a_id'] == 2555) { ?>
        <!-- This handles the More Contact Options Answer -->
            <span>
				<a href="/app/contact" class="titleText">Contact Us</a>&nbsp;|&nbsp;
			</span>
            <?= $this->data['js']['a_name']; ?>
		<? } else { ?>
        <? if ($this->data['js']['links']) : ?>
            <? foreach ($this->data['js']['links'] as $link) : ?>
                <?/*<span> <i class="fas fa-angle-right" alt="" aria-hidden="true"></i></span>*/?>
                <? if(!empty($link['url']) && !empty($link['Name'])) { ?>
					<a href="/app/answers/list/c/<?= $link['url']; ?>" class="titleText"><?= $link['Name']; ?></a>&nbsp;|&nbsp;
				<? } ?>
            <? endforeach; ?>
        <? endif; ?>
        <? if ($this->data['js']['a_id']) : ?>     
            <span></span>
            <span><?= $this->data['js']['a_name']; ?></span>
        <? endif; ?>
		
		<? $ttbreadcrumbs = ''; ?>
		
		<? if ($this->data['js']['page_url'][0] == 'Oow-submission-form') { ?>
			<? $haspop = true; ?>
			<? foreach ($this->data['js']['page_url'] as $p_url) : ?>
				<? if ($p_url == 'Pop') { ?>
					<? $haspop = false; ?>
				<? } ?>
			<? endforeach; ?>
			<? if ($haspop) { ?>
				<? $ttbreadcrumbs = 'Out of Warranty - Proof of Purchase'; ?>
			<? } else { ?>
				<? $ttbreadcrumbs = 'Out of Warranty Form'; ?>
			<? } ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Replaceinstructions') { ?>
			<? $ttbreadcrumbs = 'Replacement Information'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Oowpopcheck') { ?>
			<? $ttbreadcrumbs = 'Out of Warranty'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Repairinstructions') { ?>
			<? $ttbreadcrumbs = 'Repair Instructions'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Repair-submission-form confirm') { ?>
			<? $ttbreadcrumbs = 'Repair Submission Confirmation'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Repair-submission-form') { ?>
			<? $ttbreadcrumbs = 'Repair Submission Form'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Replace-submission-form') { ?>
			<? $ttbreadcrumbs = 'Replacement Request Form'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Replace-submission-form confirm') { ?>
			<? $ttbreadcrumbs = 'Replacement Request Submitted'; ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Oow-submission-form confirm') { ?>
			<? $haspop = true; ?>
			<? foreach ($this->data['js']['page_url'] as $p_url) : ?>
				<? if ($p_url == 'Pop') { ?>
					<? $haspop = false; ?>
				<? } ?>
			<? endforeach; ?>
			<? if ($haspop) { ?>
				<? $ttbreadcrumbs = 'Proof of Purchase Submitted'; ?>
			<? } else { ?>
				<? $ttbreadcrumbs = 'Out of Warranty Form Submitted'; ?>
			<? } ?>
		<? } else if ($this->data['js']['page_url'][0] == 'Productcheck') { ?>
			<? $ttbreadcrumbs = 'Product Check'; ?>
		<? } ?>
		
        <? if(empty($ttbreadcrumbs)) { ?>
			<? if ($this->data['js']['page_url']) : ?>     
				<? foreach ($this->data['js']['page_url'] as $p_url) : ?>
					<?/*<span><i class="fas fa-angle-right" alt="" aria-hidden="true"></i></span>*/?>
					<? if ($p_url == 'Product-issue-submission-form') { ?>
						<span>Product Issue Submission Form</span>
					<? } else { ?>
						<span><?= $p_url; ?></span>
					<? } ?>
				<? endforeach; ?>
			<? endif; ?>
		<? } else { ?>
			<span><?= $ttbreadcrumbs; ?></span>
		<? } ?>
        <? } ?>
        <a id="ctl00_ContentMap_SkipLink"></a>
		
    </span>
    
</div>