<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="standard.php" clickstream="home" />
	
    <?	//NA Portal Redesign
	    //This section sets all the variables for the widgets that may be different for US vs Canada site.
	    if($referrer == 'US') { 
	    	$VisualProductCategorySelectorCUSTOM_CFG_PRODUCTID = intval(\RightNow\Utils\Config::getConfig(CUSTOM_CFG_US_PRODUCTID));
	    	$VisualProductCategorySelectorCUSTOM_CFG_DISPLAY_HOME_PROD_CAT = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_DISPLAY_HOME_PROD_CAT);
	    	$TrendingProductDisplayCUSTOM_MSG_TRENDING_HOME_PRODUCTS = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_TRENDING_HOME_PRODUCTS);
	    	$narvar = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_NARVAR_REDIRECT_URL);
	    	$returnAnswer = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_RETURN_ANSWER_LINK);
		} else {
			//CANADA
		 	$VisualProductCategorySelectorCUSTOM_CFG_PRODUCTID = intval(\RightNow\Utils\Config::getConfig(CUSTOM_CFG_CANADA_PRODUCTID));
	    	$VisualProductCategorySelectorCUSTOM_CFG_DISPLAY_HOME_PROD_CAT = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_DISPLAY_HOME_PROD_CAN); 
			$TrendingProductDisplayCUSTOM_MSG_TRENDING_HOME_PRODUCTS = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_TRENDING_HOME_CAN_PRODUCTS);
			$narvar = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_NARVAR_REDIRECT_CAN_URL);
			$returnAnswer = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_RETURN_CAN_ANSWER_LINK);
		}
	    	
    ?>
    <div>This is a custom change to the home.php page (Test Two)</div>
    <div class="rn_PageContent_HomeProductDisplay rn_Home">
	    <div id="rn_HomeProductDisplay" class="rn_Container">
		    <div class="rn_HomeProductDisplayHead">
			<h1 class="rn_HomeProductDisplayHead">#rn:msg:CUSTOM_MSG__HOME_PRODUCT_SELECT_HDR#</h1>
			<span class="rn_HomeProductDisplayHead">#rn:msg:CUSTOM_MSG_HOME_PRODUCT_SELECT_SUBHDR#</span>
			</div>
	        <rn:widget path="navigation/VisualProductCategorySelector" 
	        		show_sub_items_for="#rn:php:$VisualProductCategorySelectorCUSTOM_CFG_PRODUCTID#" 
	        		top_level_items="#rn:php:$VisualProductCategorySelectorCUSTOM_CFG_DISPLAY_HOME_PROD_CAT#" numbered_pagination="true" 
	        		prefetch_sub_items="true" label_show_sub_header="#rn:msg:CUSTOM_MSG_SUBPRODUCT_SELECT_HDR#" />
	    </div>
    </div>
	<div class="rn_PageContent rn_Home">
		<div id="rn_SearchControlsLinkHideShow" class="rn_SearchControlsLinkHideShow rn_HomeSearchControlsLink">
			<span class="rn_ParagraphTextNormal">#rn:msg:CUSTOM_MSG_FASTER_HELP#</span> <a class="rn_SearchControlsHomeShowLink link-secondary" href="javascript:;" >#rn:msg:CUSTOM_MSG_SEARCH_MODEL_LBL#</a>	
		</div>
		<div id="rn_SearchControlsHome" class="rn_SearchControls rn_SearchControlsHome rn_Hidden">
			<?/*h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>*/?>
			<span id="rn_ADALabel" class="rn_Hidden">#rn:msg:CUSTOM_MSG_SEARCH_MODEL_LBL#</span>
			<form method="get" action="/app/results">
				<rn:container source_id="KFSearch">
					<div class="rn_SearchInput">
						<rn:widget path="searchsource/SourceSearchField" initial_focus="true" label_placeholder="#rn:msg:CUSTOM_MSG_SEARCH_MODEL_PLACEHOLDER#" />
					</div>
					<rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list" />
				</rn:container>
			</form>
			<div class="rn_AlignLeft rn_WhereIsModel">
				<a href='#' class="open-dialog">#rn:msg:CUSTOM_MSG_WHERE_IS_THIS#</a>
			</div>
			 <div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
				<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> <img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" ></button>
				#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
			</div>	        
		</div>
		<div id="rn_TrendingProductsDiv" class="">
			<div id="rn_TrendingProductsHeadline" class="rn_TrendingProductsHeadline">
				<h1 class="">#rn:msg:CUSTOM_MSG_TRENDING_PRODUCTS_HDG#</h1>
				<span class="">#rn:msg:CUSTOM_MSG_TRENDING_PRODUCTS_SUBTEXT#</span>
			</div>
			<rn:widget path="navigation/TrendingProductDisplay" numbered_pagination="false" 
						country_cd="#rn:php:$country_cd#" 
						product_list_config="#rn:php:$TrendingProductDisplayCUSTOM_MSG_TRENDING_HOME_PRODUCTS#"  
						numbered_pagination="false" prefetch_sub_items="false" />		
		</div>
		<div id="rn_OrdersReturns" class="rn_HomeContainer rn_OrderStatus rn_HomeReturns">	
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_OrderStatus rn_CallToActionBox50"
				image_url="images/icons/bissell/OrderStatusHome.svg"
				image_alt="#rn:msg:CUSTOM_MSG_ORDER_STATUS_HDG#"
				headline="#rn:msg:CUSTOM_MSG_ORDER_STATUS_HDG#"
				subtext="#rn:msg:CUSTOM_MSG_ORDER_STATUS_SUBTEXT#"
				button_1_text="#rn:msg:CUSTOM_MSG_ORDER_STATUS_BTN#"
				button_1_url="#rn:php:$environment#/check-order/"
				button_2_text=""
				button_2_url=""
				/>				
			<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_HomeReturns rn_CallToActionBox50"
				image_url="images/icons/bissell/ReturnsHome.svg"
				image_alt="#rn:msg:CUSTOM_MSG_RETURNS_TITLE#"
				headline="#rn:msg:CUSTOM_MSG_RETURNS_TITLE#"
				subtext="#rn:msg:CUSTOM_MSG_RETURNS_HOME_SUBTEXT#"
				button_1_text="#rn:msg:CUSTOM_MSG_START_RETURN_BTN#"
				button_1_url="#rn:php:$narvar#"
				button_2_text="#rn:msg:CUSTOM_MSG_RETURN_POLICY_BTN#"
				button_2_url="#rn:php:$returnAnswer#"
				/>
		</div>
    	<div id="rn_HelpfulResources" class="rn_HomeContainer">
			<div id="rn_HelpfulResourcesHeadline" class="rn_HomeSectionHeadline">
				<h1 class="rn_HomeSectionHead">#rn:msg:CUSTOM_MSG_HELPFUL_RESOURCES_HDG#</h1>
				<span class="rn_ParagraphText">#rn:msg:CUSTOM_MSG_HELPFUL_RESOURCES_SUBTEXT#</span>
			</div>
			<div id="rn_HelpfulResourcesBoxes" class="rn_HomeContainer rn_ServiceCenter rn_AppSupport">
				<?php if ($referrer == 'US'): ?>
					<? /* US */ ?>
				<?php else : ?>
					<? /* CANADA */ ?>
					<rn:widget path="custom/navigation/CallToActionBox" 
						class_name="rn_ServiceCenterLocator rn_CallToActionBox50"
						image_url="images/icons/bissell/ServiceCenter.svg"
						image_alt="#rn:msg:CUSTOM_MSG_SVC_CENTER_HDG#"
						headline="#rn:msg:CUSTOM_MSG_SVC_CENTER_HDG#"
						subtext="#rn:msg:CUSTOM_MSG_SVC_CENTER_SUBTEXT#"
						button_1_text="#rn:msg:CUSTOM_MSG_SRV_CENTER_HOME_BTN#"
						button_1_url="#rn:php:$environment#/service-center-locator/"
						button_2_text=""
						button_2_url=""
						/>
				<?php endif; ?>
				<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_AppSupport rn_CallToActionBox50"
					image_url="images/icons/bissell/AppSupport.svg"
					image_alt="#rn:msg:CUSTOM_MSG_APP_SUPPORT_HOME_HDG#"
					headline="#rn:msg:CUSTOM_MSG_APP_SUPPORT_HOME_HDG#"
					subtext="#rn:msg:CUSTOM_MSG_APP_SUPPORT_HOME_SUBTEXT#"
					button_1_text="#rn:msg:CUSTOM_MSG_APP_SUPPORT_HOME_BTN#"
					button_1_url="/app/connected"
					button_2_text=""
					button_2_url=""
					/>
    		</div>		
    	</div>
    <div class="dialog-overlay" tabindex="-1"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>


<script type="text/javascript">

	var navDialogEl = document.querySelector('.dialog');
	var dialogOverlay = document.querySelector('.dialog-overlay');
	
	var myDialog = new Dialog(navDialogEl, dialogOverlay);
	myDialog.addEventListeners('.open-dialog', '.close-dialog');

$(document).ready(function(){
	$(".rn_ItemLevel2 ul").before('<div class="rn_HomeProductDisplayHead"><h4 class="rn_HomeProductDisplayHead">#rn:msg:CUSTOM_MSG_SUBPRODUCT_SELECT_HDR#</h4></div>');
	
	$('.rn_SearchControlsHomeShowLink').on('click', function(event) {
		event.preventDefault();
	    //alert("in here");
	    $("#rn_SearchControlsProductPages").attr("style", "display:block");
		$('#rn_SearchControlsProductPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_MODEL_PLACEHOLDER#');
		$("#rn_SearchControlsLinkHideShow").attr("style", "display:none");
		$("#rn_SearchControlsHome").attr("style", "padding-top:16px; padding-bottom:32px;");
		$("#rn_SearchControlsHome").removeClass("rn_Hidden");
		$('.rn_SourceSearchField input').focus();
	    
	    $('.rn_SourceSearchField input').focus();
	})

});	
	
</script>