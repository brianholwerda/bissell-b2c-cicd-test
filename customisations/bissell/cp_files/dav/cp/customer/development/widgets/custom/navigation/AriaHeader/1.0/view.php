<nav>
<div class="rn_NavigationBar">
    
    <input type="checkbox" id="rn_NavigationMenuButtonToggle" class="rn_ScreenReaderOnly" />
	<div id="rn_rightLogo">
		<div class="rn_TopLogoImage"><a href="/"><img alt="Bissell Logo" id="rn_whiteLogo" src="#rn:config:CUSTOM_CFG_HEADER_LOGO#"></a></div>
		<div class="rn_SupportText"><a href="/"><img alt="Support Icon" id="rn_headerSupport" src="#rn:config:CUSTOM_CFG_SUPPORT_HEADER#"></a></div>
		<? /* <p id="rn_headerSupport">SUPPORT</p> */ ?>
	</div>
	<div id="rn_desktopNav">
	    <ul class="rn_NavigationMenu">
			<li class="rn_desktopNav" tabindex="0"><rn:widget path="navigation/NavigationTab" label_tab="#rn:msg:CUSTOM_MSG_PRODUCT_SUPPORT#" link="javascript:void(0)" pages="home"/></li>
			<li class="rn_desktopNav" tabindex="0"><a href="javascript:void(0);" id="rn_underlineHover">#rn:msg:CUSTOM_MSG_ORDER_SUPPORT_HDG#</a></li>
			<?/*subpages="Returns > app/returns/a_id/5286, Shipping FAQs > app/answers/detail/a_id/1134/p/, Order Status ><?=$this->data['attrs']['com_environment']; ?>/check-order"*/?>
			<li class="rn_desktopNav" tabindex="0"><a href="<?=$this->data['attrs']['com_environment']; ?>/product-registration">#rn:msg:CUSTOM_MSG_REGISTER_YOUR_PRODUCT#</a></li>
			<li class="rn_desktopNav" tabindex="0"><a href="<?=$this->data['attrs']['com_environment']; ?>/parts/">#rn:msg:CUSTOM_MSG_PARTS#</a></li>
		</ul> 
	</div>
   
</div>
<div id="rn_leftShop">
	<img alt="Shopping Cart icon" id="rn_shoppingCart" src="#rn:config:CUSTOM_CFG_SHOPPING_HEADER#"><a href="<?=$this->data['attrs']['com_environment']; ?>/" id="rn_shop">#rn:msg:RCT_SHOP_CMD#</a>    
</div>

<div id="rn_subMenuProd" class="rn_Hidden">
	<div class="rn_prodList">
		<h3 class="rn_subSupportHeaderProducts">#rn:msg:CUSTOM_MSG_TROUBLESHOOT_YOUR_PRODUCT#</h3>
		<? if($this->data['attrs']['referrer'] == "US") { ?>
			 <rn:widget path="custom/navigation/ProductCategoryLeftNav" label_title="" filter_type="products" levels="2"  hide_top_level="true" only_display='#rn:config:CUSTOM_CFG_US_PRODUCTID#' add_params_to_url="p" report_page_url="/app/products/detail" />
		<? } else { ?>
			<rn:widget path="custom/navigation/ProductCategoryLeftNav" label_title="" filter_type="products" levels="2"  hide_top_level="true" only_display='#rn:config:CUSTOM_CFG_CANADA_PRODUCTID#' add_params_to_url="p" report_page_url="/app/products/detail" />
		<? } ?>
	</div>
	
	<div class="rn_subSupport">
		<div class="rn_supportCenter">
			<h3 class="rn_subSupportHeader">#rn:msg:CUSTOM_MSG_NEED_FURTHER_SUPPORT#</h3>
			<br>
			<?php if ($this->data['attrs']['referrer'] == "US"): ?>
				<? /* US */ ?>
			<?php else : ?>
				<? /* CANADA */ ?>
				<p class="rn_subSupportLink"><a href="<?=$this->data['attrs']['com_environment']; ?>/service-center-locator/">#rn:msg:CUSTOM_MSG_FIND_AUTHORIZED_SVC_CENTER#</a></p>
			<?php endif; ?>
			<p class="rn_subSupportLink"><a href="/app/answers/detail/a_id/4677">#rn:msg:CUSTOM_MSG_WARRANTY_INFORMATION#</a></p>
			<p class="rn_subSupportLink"><a href="<?=$this->data['attrs']['com_environment']; ?>/parts/">#rn:msg:CUSTOM_MSG_ORDER_PARTS_ACCESS#</a></p>
			<p class="rn_subSupportLink"><a href="<?=$this->data['attrs']['com_environment']; ?>/productsafety">#rn:msg:CUSTOM_MSG_PRODUCT_SAFETY#</a></p>
		</div>
	</div>
</div>

<div id="rn_subMenuOrder" class="rn_Hidden">
	<div class="rn_orderList">
		<h3 class="rn_subSupportHeaderProducts" >#rn:msg:CUSTOM_MSG_ORDER_SUPPORT_HDG#:</h3>
		<br>
		<ul>
			<li class="rn_subSupportLink"><a href="/app/returns/a_id/5286">#rn:msg:CUSTOM_MSG_RETURNS_TITLE#</a></li>
			<li class="rn_subSupportLink"><a href="/app/answers/detail/a_id/1134">#rn:msg:CUSTOM_MSG_SHIPPING_FAQS#</a></li>
			<li class="rn_subSupportLink"><a href="<?=$this->data['attrs']['com_environment']; ?>/check-order/">#rn:msg:CUSTOM_MSG_ORDER_STATUS_HDG#</a></li>
		</ul>
	</div>
	
		
</div>



<script>
	
$(document).ready(function(){
	$("span[id^='rn_Product']").on('click', function(){
		$("#rn_subMenuProd").toggleClass('rn_Hidden');
		$("#rn_subMenuOrder").addClass('rn_Hidden');
		$("a.rn_SelectedTab").toggleClass('headerNavOnclick');
		$("a#rn_underlineHover").removeClass('headerNavOnclick');
	});
	
	$("a#rn_underlineHover").on('click', function(){
		$("#rn_subMenuOrder").toggleClass('rn_Hidden');
		$("#rn_subMenuProd").addClass('rn_Hidden');
		$("a#rn_underlineHover").toggleClass('headerNavOnclick');
		$("a.rn_SelectedTab").removeClass('headerNavOnclick');
		
	});
	
	$("#rn_prodsupportMobile").on('click', function(){
		$("#rn_subProductMenu").toggleClass('rn_Hidden');
		$("#rn_mobileShop").addClass('rn_Hidden');
		$("#closeNavMenu").focus();
	});
	
	$("#rn_mobileSteam").on('click', function(){
		$("#rn_subSteamMenu").toggleClass('rn_Hidden');
		$("#rn_mobileSteam").addClass('rn_Hidden');
		$("#rn_subProductMenu").addClass('rn_Hidden');
		$("#closeNavMenu").focus();
	});
	
});
	
	
</script>
</nav>

