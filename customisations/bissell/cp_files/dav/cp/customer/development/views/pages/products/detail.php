<rn:meta title="BISSELL Product Support | #rn:php:\RightNow\Libraries\SEO::getDynamicTitle('product', \RightNow\Utils\Url::getParameter('p'))#" template="standard.php" clickstream="product"/>
<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/ModalCss/dialog.css">
<div class="rn_PageContent rn_Product rn_ProductDetail rn_ProductDetailPage"> 
	<div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description">
		<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> <img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" ></button>
		#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
	</div>
<div class="rn_Hero rn_Product">
    <div class="rn_HeroInner">
	    <nav aria-label="breadcrumbs">
        <span class="rn_ProductCategoryBreadcrumbHome"><a href="/app/home" class="rn_ProductCategoryBreadcrumb">Home<?/*<i class="fas fa-angle-right" alt="" aria-hidden="true">*/?></a></i></span>
        <rn:widget path="navigation/ProductCategoryBreadcrumb" display_first_item="false"/>
        </nav>
        <div id="rn_SearchMagnifyIconHideShow" class="rn_SearchMagnifyIconHideShow rn_ProductSearchMagnifyControlsLink">
	        <a class="rn_SearchControlsMagnifyIconLink" href="javascript:;" ><i class="fa fa-search" aria-hidden="true"></i></a>
        </div>
        <div id="rn_SearchLinkHideShow" class="rn_SearchControlsLinkHideShow rn_ProductSearchControlsLink">
			#rn:msg:CUSTOM_MSG_FASTER_HELP# <a class="rn_SearchControlsTextLink" href="javascript:;">#rn:msg:CUSTOM_MSG_SEARCH_MODEL_LBL#</a>	
		</div>
        <div id="rn_SearchControlsProductPages" class="rn_SearchControls rn_SearchControlsProductPages">
			<div class="rn_SearchControls">
              <?/*h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>*/?>
              <span id="rn_ADALabel" class="rn_Hidden"> #rn:msg:CUSTOM_MSG_SEARCH_SUPPORT#</span>
              <form method="get" action="/app/answers/list">
                <rn:container source_id="KFSearch">
                  <div class="rn_SearchInput">
                    <rn:widget path="searchsource/SourceSearchField" initial_focus="false" label_placeholder="#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#" />
                  </div>
                  <rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list" />
                </rn:container>
              </form>
            </div>
            <? /* <div class="rn_AlignLeft rn_WhereIsModel">
			<a href='#' class="open-dialog">#rn:msg:CUSTOM_MSG_WHERE_IS_THIS#</a>
			</div>	 */ ?>        
		</div>
    </div>
</div>
	
    <div class="rn_ProductDetail">
	    <rn:widget path="custom/reports/CustProdList" image_path2="#rn:php:$cdn250#" referring_url="#rn:php:$com_environment#" referrer="#rn:php:$referrer#" />
    </div>

    <div class="rn_PopularKB">
        <div class="rn_Container rn_TopAnswers">
            <div class="rn_HeaderContainer">
                <h2 id="rn_h2-TopAnswers">#rn:msg:CUSTOM_MSG_TOP_ANSWERS#</h2>
                <? /* <rn:widget path="knowledgebase/RssIcon" /> */ ?>
            </div>
            <rn:widget path="reports/TopAnswers" limit="5" product_filter_id="#rn:url_param_value:p#" category_filter_id="655" show_excerpt="false" />
            <div class="rn_SeeMoreAnswersBtn">
            	<a ID="rn_SeeMoreAnswers" class="rn_AnswersLink" href="/app/answers/list/c/655/p/#rn:url_param_value:p#/#rn:session#">#rn:msg:CUSTOM_MSG_SEE_MORE_ANSWERS#</a>
            </div>
            <? /* use this if they decide they want more than just troubleshooting answers in the Top Answers section.
	        <rn:container report_id="101287">

		    <div class="rn_Container">
		        <div class="rn_PageContent rn_ProductTopAnswers">
		            <div>
		                <rn:widget path="standard/reports/Multiline" highlight="false" hide_columns="answers.updated" />
		                <a class="rn_AnswersLink" href="/app/answers/list/p/#rn:url_param_value:p#/#rn:session#/page/2">#rn:msg:SHOW_MORE_PUBLISHED_ANSWERS_LBL#</a>
		            </div>
		        </div>
		    </div>
		    </rn:container>  */ ?>
        </div>
    </div>



    <div class="rn_Container rn_ProductDetailPageContainer">
	    <div class="rn_HomeProductDisplayHead">
		<h1 class="rn_ProductDisplayHead"><rn:field name="ServiceProduct.Name"/></h1>
		<p class="rn_Bold rn_ProductDisplayHead ">#rn:msg:CUSTOM_MSG_PRODUCT_DETAIL_LIST_INSTR#</p>
		</div>
	    
	    <rn:widget path="custom/navigation/VisualProductCategorySelectorHighLimit" maximum_items="200" per_page="20" numbered_pagination="true" show_sub_items_for="#rn:url_param_value:p#" image_path2="#rn:php:$cdn250#" />
    </div>

<div class="dialog-overlay" tabindex="-1"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>


<script type="text/javascript">
$(document).ready(function(){
	$("#rn_SearchControlsProductPages .rn_SubmitButton").prepend(' <i class="fa fa-search" aria-hidden="true"></i>');
	
	$('.rn_SearchControlsMagnifyIconLink').on('click', function(event) {
		event.preventDefault();
	    //alert("in here rn_SearchControlsMagnifyIconLink");
	    $("#rn_SearchControlsProductPages").attr("style", "display:inline-block");
		$('#rn_SearchControlsProductPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#');
		$(".rn_SearchControlsMagnifyIconLink").attr("style", "display:none");
		//$('.rn_SourceSearchField input').focus();
	});
	
	$('.rn_SearchControlsTextLink').on('click', function(event) {
		event.preventDefault();
	    //alert("in here rn_SearchLinkHideShow");
	    $("#rn_SearchControlsProductPages").attr("style", "display:inline-block");
		$('#rn_SearchControlsProductPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#');
		$("#rn_SearchLinkHideShow").attr("style", "display:none");
		//$("#rn_SearchControlsProductPages").attr("style", "padding-top:16px; padding-bottom:32px;");
		//$("#rn_SearchControlsProductPages").removeClass("rn_Hidden");
		//$('.rn_SourceSearchField input').focus();
	});
	
	
	document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
	document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
	document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
    

    $('.rn_ProductCategoryBreadcrumbHome a:after').attr('alt',"");
    
});
		var navDialogEl = document.querySelector('.dialog');
		var dialogOverlay = document.querySelector('.dialog-overlay');
		
		var myDialog = new Dialog(navDialogEl, dialogOverlay);
		myDialog.addEventListeners('.open-dialog', '.close-dialog');
</script>
</div>