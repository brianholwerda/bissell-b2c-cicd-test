<rn:meta title="BISSELL Support | #rn:php:\RightNow\Libraries\SEO::getDynamicTitle('category', \RightNow\Utils\Url::getParameter('c'))#" template="standard.php" clickstream="category_view"/>

<?  
	//If we get here on the stain removal guide category, redirect to the stain_removal page.
	if (getUrlParm("c") == "171523") { 
		$header_string = "location: /app/stain_removal/c/" . getUrlParm("c") . "/a_id/1380";
		header($header_string);
	} else if (getUrlParm("c") == "171524") { 
		$header_string = "location: /app/cleaning_tips/c/" . getUrlParm("c") . "/a_id/2295";
		header($header_string);
	} else { ?>

<div class="rn_Hero rn_Product">
    <div class="rn_HeroInner">
        <rn:widget path="navigation/ProductCategoryBreadcrumb" type="category" link_url="/app/#rn:config:CP_CATEGORIES_DETAIL_URL#" display_first_item="false"/>
        <div class="rn_ProductHero">
            <h1>
                <rn:condition url_parameter_check="c != null">
                    <rn:field name="ServiceCategory.Name"/>
                <rn:condition_else/>
                    #rn:msg:CATEGORY_NOT_FOUND_LBL#
                </rn:condition>
            </h1>
        </div>
    </div>
</div>

<div class="rn_PageContent rn_Product">
    <div class="rn_Container">
        <rn:widget path="navigation/VisualProductCategorySelector" type="category" landing_page_url="/app/#rn:config:CP_CATEGORIES_DETAIL_URL#" show_sub_items_for="#rn:url_param_value:c#" numbered_pagination="true"/>
    </div> 

    <div class="rn_PopularKB">
        <div class="rn_Container">
            <div class="rn_HeaderContainer">
                <h2>#rn:msg:POPULAR_PUBLISHED_ANSWERS_LBL#</h2>
                <? /* <rn:widget path="knowledgebase/RssIcon" /> */ ?>
            </div>
            <rn:widget path="reports/TopAnswers" show_excerpt="true" per_page="10" category_filter_id="#rn:url_param_value:c#"/>
            <a class="rn_AnswersLink" href="/app/answers/list/c/#rn:url_param_value:c#/#rn:session#">#rn:msg:SHOW_MORE_PUBLISHED_ANSWERS_FOR_LBL# <rn:field name="ServiceCategory.Name"/></a>
        </div>
    </div>

</div>
<? } ?>

<script type="text/javascript">

    $(document).ready(function() {
		document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
    });
</script>