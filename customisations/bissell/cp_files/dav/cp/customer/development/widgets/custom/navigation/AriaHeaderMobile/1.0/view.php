<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<script >
$( document ).ready(function() {
	$('.menu-toggleable-left .close-menu .back').focus();
	
	$('#rn_hamburgerToggle').on('click', function(){
		if($('#hamburger-header-container_id').hasClass('rn_MenuOff')) {
			$('#rn_Mobile_TopLeveleMenu').removeClass('rn_Hidden');
			$('nav.rn_Mobile_mainmenu_navbar').removeClass('rn_Hidden');
			$('#hamburger-header-container_id').removeClass('rn_MenuOff');
		} else {
			$('#rn_Mobile_TopLeveleMenu').addClass('rn_Hidden');
			$('nav.rn_Mobile_mainmenu_navbar').addClass('rn_Hidden');
			$('#hamburger-header-container_id').addClass('rn_MenuOff');
		}		
		$('#rn_close').toggleClass('rn_Hidden');
		$('.rn_Mobile_mainmenu').toggleClass('rn_Hidden');
		//Close the Product Sub Menus, just in case
		if (! $('#rn_Mobile_productNav').hasClass("rn_Hidden")) {
			$('#rn_Mobile_productNav').addClass("rn_Hidden");
		}
		/* if (! $('#rn_Mobile_TopLeveleMenu').hasClass("rn_Hidden")) {
			$('#rn_Mobile_productNav').addClass("rn_Hidden");
		}
		if (! $('nav.rn_Mobile_mainmenu_navbar').hasClass("rn_Hidden")) {
			$('nav.rn_Mobile_mainmenu_navbar').addClass("rn_Hidden");
		}*/
		
		//UnHide the Products Back link when Hamburger Clicked
		document.getElementById("rn_Mobile_Products_Back").classList.remove('rn_Hidden');
		
		//Make sure all Second Level Products are hidden
		$('.rn_Mobile_SubItemList2').removeClass("rn_Hidden");
		$('.rn_Mobile_SubItemList2').addClass("rn_Hidden");
		
		//Make sure all First Level Products are not hidden
		$('.rn_Mobile_ProductCategorySubItem').removeClass("rn_Hidden");
		$('.rn_Mobile_ProductCategorySubItem a img.rn_chevronLeft').removeClass("rn_Hidden");
		$('.rn_Mobile_ProductCategorySubItem a img.rn_chevronLeft').addClass("rn_Hidden");
		$('.rn_Mobile_ProductCategorySubItem a img.rn_chevronRight').removeClass("rn_Hidden");
		
		$('#rn_close').focus();
		
	});
	
	$('#rn_Mobile_productLink').on('click', function(){
		$('#rn_Mobile_productNav').toggleClass('rn_Hidden');
		$('#rn_Mobile_TopLeveleMenu').toggleClass('rn_Hidden');
		$('#rn_Mobile_ProdSupport').toggleClass('rn_Hidden');
	});
	
	$('#rn_close').on('click', function(){
		$('#rn_Mobile_TopLeveleMenu').toggleClass('rn_Hidden');
		$('#rn_close').addClass('rn_Hidden');
		$('#rn_Mobile_TopLeveleMenu').focus();
		
	});
	
	$( '#rn_close' ).on( 'keydown', function ( e ) {
	    if ( e.keyCode === 27 ) { // ESC
	        $('#rn_Mobile_TopLeveleMenu').addClass('rn_Hidden');
	        $('#rn_Mobile_productNav').addClass('rn_Hidden');
			$('#rn_close').addClass('rn_Hidden');
			$('#rn_hamburgerToggle').focus();
	    }
	});
	$('#rn_Mobile_ProdSupport').on('click', function(){
		//alert('clicked');
		$('#rn_Mobile_productNav').addClass('rn_Hidden');
		$('#rn_Mobile_TopLeveleMenu').toggleClass('rn_Hidden');
		$('#rn_Mobile_ProdSupport').toggleClass('rn_Hidden');
		
		
	});

	
	
});
</script>
<div class="header-nav-layout-alt rn_header_mobile">
   <a href="/#maincontent" class="sr-only sr-only-focusable skip-to-main-content-link" aria-label="Skip to main content" tabindex="0">#rn:msg:WEB_CONSOLE_SKIP_TO_CONTENT_LBL#</a>
   <a href="/#footercontent" class="sr-only sr-only-focusable skip-to-footer-content-link" aria-label="Skip to footer content">#rn:msg:CUSTOM_MSG_SKIP_TO_FOOTER#</a>
   
   <nav role="navigation" aria-label="Primary" class="rn_BissellNav" >
	   <div id="rn_rightLogo">
			<div class="rn_TopLogoImage"><a href="/"><img alt="Bissell Logo" id="rn_whiteLogo" src="#rn:config:CUSTOM_CFG_HEADER_LOGO#"></a></div>
			<div class="rn_SupportText"><a href="/"><img alt="Support Icon" id="rn_headerSupport" src="#rn:config:CUSTOM_CFG_SUPPORT_HEADER#"></a></div>
			<? /* <p id="rn_headerSupport">SUPPORT</p> */ ?>
		</div>
      <div id="hamburger-header-container_id" class="hamburger-header-container container rn_MenuOff">
         <div class="header-main navbar-header">
            <div class="utility-left-container" aria-hidden="true">
	            <button id="rn_hamburgerToggle" class="navbar-toggler" aria-label="Toggle navigation" aria-expanded="false" tabindex="0"><img id ="rn_mobilehamburger" src="#rn:config:CUSTOM_CFG_HAMBURGER#"></button>
            </div>
            <div class="rn_Mobile_mainmenu main-menu navbar-toggleable menu-toggleable-left multilevel-dropdown rn_Hidden" id="sg-navbar-collapse" aria-hidden="false">
               <div class="menu-inner-wrapper">
                  <div class="container">
                     <div class="row">
                        <nav class="navbar rn_Mobile_mainmenu_navbar navbar-flyout-menu bg-inverse col-12" aria-label="Main Menu">
                           <div class="header-banner slide-up">
						      <div class="container">
						         <div class="close-button d-none">
						            <button type="button" id="rn_close" class="close rn_Hidden" aria-label="Close" >
						            <span aria-hidden="true">×</span>
						            </button>
						         </div>
						      </div>
						   </div>
							<div id="rn_Mobile_productNav" class="rn_Hidden">
								<? if($this->data['attrs']['referrer'] == "US") { ?>
									<rn:widget path="custom/navigation/AriaHeaderProductNavMobile" label_title="" filter_type="Product" levels="2" 
											only_display="#rn:config:CUSTOM_CFG_US_PRODUCTID#" report_page_url="/app/products/detail" />
								<? } else { ?>
									<rn:widget path="custom/navigation/AriaHeaderProductNavMobile" label_title="" filter_type="Product" levels="2" 
											only_display="#rn:config:CUSTOM_CFG_CANADA_PRODUCTID#" report_page_url="/app/products/detail" />
								<? } ?>
								
							</div>
                           
                           <div id="rn_Mobile_TopLeveleMenu" class="menu-group rn_Hidden">
                              <ul class="navbar-nav">
                                 <li class="menu-item menu-item-first dropdown position-static">
                                    <a href="javascript:;" class="menu-link dropdown-toggle" id="rn_Mobile_productLink" 
	                                    role="button" data-toggle="dropdown" aria-expanded="false" aria-controls="Products-menu">
										#rn:msg:CUSTOM_MSG_PRODUCT_SUPPORT#
										<img class="rn_chevronLeft" src="/euf/assets/images/chevronLeft.png">
									</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="<?=$this->data['attrs']['com_environment']; ?>/product-registration/" class="menu-link">#rn:msg:CUSTOM_MSG_REGISTER_YOUR_PRODUCT#</a>
                                 </li>
								 <?php if ($this->data['attrs']['referrer'] == "US"): ?>
                                     <? /* US */ ?>
                                 <?php else : ?>
									 <? /* CANADA */ ?>
                                     <hr class="rn_lineBreak">
                                     <li class="menu-item">
                                         <a href="<?=$this->data['attrs']['com_environment']; ?>/service-center-locator/" class="menu-link">#rn:msg:CUSTOM_MSG_FIND_AUTHORIZED_SVC_CENTER#</a>
                                     </li>
                                 <?php endif; ?>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="/app/answers/detail/a_id/4677" class="menu-link">#rn:msg:CUSTOM_MSG_WARRANTY_INFORMATION#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="<?=$this->data['attrs']['com_environment']; ?>/parts/" class="menu-link">#rn:msg:CUSTOM_MSG_ORDER_PARTS_ACCESS#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="<?=$this->data['attrs']['com_environment']; ?>/productsafety" class="menu-link">#rn:msg:CUSTOM_MSG_PRODUCT_SAFETY#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="/app/returns/a_id/5286" class="menu-link">#rn:msg:CUSTOM_MSG_RETURNS_TITLE#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="/app/answers/detail/a_id/1134" class="menu-link">#rn:msg:CUSTOM_MSG_SHIPPING_FAQS#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="<?=$this->data['attrs']['com_environment']; ?>/check-order/" class="menu-link">#rn:msg:CUSTOM_MSG_ORDER_STATUS_HDG#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                                 <li class="menu-item">
                                    <a href="<?=$this->data['attrs']['com_environment']; ?>/" class="menu-link">#rn:msg:RCT_SHOP_CMD#</a>
                                 </li>
                                 <hr class="rn_lineBreak">
                              </ul>
                           </div>
                        </nav>
                     </div>
                  </div>
               </div>
            </div>
            <div class="utility-right-container" aria-hidden="true"></div>
         </div>
   </nav>
</div>
</div>