<rn:condition config_check="CUSTOM:CUSTOM_CFG_SHOW_ANNOUNCEMENT == true">
	<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
		<div id="rn_AnnouncementContainer" style="display">
			<div id="rn_AnnImageContainer">
				<img src="images/redex.png" alt=""/>
			</div>
			<div id="rn_AnnBodyWrapper">
				<span id="rn_AnnouncementTitle"><?=$this->data['attrs']['announcement_title']?>:</span>
				<div class="rn_AnnouncementBody"><?=$this->data['attrs']['announcement_text']?></div>
			</div>
			<div style="clear:both;"></div>
		</div>
	</div>
</rn:condition>