<rn:meta title="#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_LBL#" template="standard.php" clickstream="contact_us"/>

<div class="rn_PageContent rn_ConnectedAll">
<div id="rn_HelpfulResources" class="rn_Container rn_Connected2022">
	<div id="rn_HelpfulResourcesHeadline" class="rn_ConnectedProducts">
		<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_CONNECTED_TITLE#</h1>
		<span class="">#rn:msg:CUSTOM_MSG_CONNECTED_HDR_SUBTEXT#</span>
	</div>

	<div id="rn_ConnectedPage" class="rn_Container">

		<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline rn_ConnectedProductSelector">
		<h3 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_CONNECTED_PRODUCT_SELECTOR_HDR#</h3>
		</div>
		<? if($referrer == "US") { ?>
			<rn:widget path="navigation/ConnectedProductDisplayAll" numbered_pagination="false" country_cd="#rn:php:$country_cd#" numbered_pagination="true" prefetch_sub_items="false"/>
		<? } else { ?>
			<rn:widget path="navigation/ConnectedProductDisplayAll" numbered_pagination="false" country_cd="#rn:php:$country_cd#" numbered_pagination="true" prefetch_sub_items="false"/>
		<? } ?> 
		
	</div>			
</div>