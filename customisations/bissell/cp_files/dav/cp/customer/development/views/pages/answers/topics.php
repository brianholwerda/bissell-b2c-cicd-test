<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('category', \RightNow\Utils\Url::getParameter('c'))# | #rn:php:\RightNow\Libraries\SEO::getDynamicTitle('product', \RightNow\Utils\Url::getParameter('p'))#" template="standard.php" clickstream="answer_list"/>
<div class="rn_PageContent rn_AnswerTopics">
<? if(getUrlParm('c') == 67098 || getUrlParm('c') == 67099 ) { ?>
<rn:container report_id="115752">
	
<div class="rn_Container">
    <div class="rn_PageContent rn_KBAnswerList">
        <div class="rn_HeaderContainer">
            <h2>Relevant Answers</h2>
            <? /* <rn:widget path="knowledgebase/RssIcon" /> */ ?>
        </div> 

        <rn:condition flashdata_value_for="info">
            <div class="rn_MessageBox rn_InfoMessage">
                #rn:flashdata:info#
            </div>
        </rn:condition>

        <div>
            <rn:widget path="standard/reports/ResultInfo" show_no_results_msg_without_search_term="true"/>
            <rn:widget path="custom/reports/VideoMultiline" hide_columns="answers.updated" per_page="20"/>
            <rn:widget path="standard/reports/Paginator"/>
        </div>
    </div>

    <?/*<aside class="rn_SideRail" role="complementary">
        <rn:widget path="utils/ContactUs"/>
        <rn:widget path="discussion/RecentlyViewedContent"/>
    </aside>*/?>
</div>
</rn:container>
<? }  else { ?>
<rn:container report_id="176">
	
<div class="rn_Container">
    <div class="rn_PageContent rn_KBAnswerList">
        <div class="rn_HeaderContainer">
            <h2>Relevant Answers</h2>
            <? /* <rn:widget path="knowledgebase/RssIcon" /> */ ?>
        </div> 

        <rn:condition flashdata_value_for="info">
            <div class="rn_MessageBox rn_InfoMessage">
                #rn:flashdata:info#
            </div>
        </rn:condition>

        <div>
            <rn:widget path="standard/reports/ResultInfo" show_no_results_msg_without_search_term="true"/>
            <rn:widget path="custom/reports/Multiline" hide_columns="answers.updated" />
            <rn:widget path="standard/reports/Paginator"/>
        </div>
    </div>

    <?/*<aside class="rn_SideRail" role="complementary">
        <rn:widget path="utils/ContactUs"/>
        <rn:widget path="discussion/RecentlyViewedContent"/>
    </aside>*/?>
</div>
</rn:container>

<? } ?>

<script type="text/javascript">
    $(document).ready(function() {
        //Update page meta description
		document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
		var pageTitle = $('.rn_HeaderContainer > h2').text();
        $('.rn_HeaderContainer > h2').hide();
        $('#pageHeading').text(pageTitle);
        setTimeout(function(){
            $('.rn_ProductCategoryBreadcrumb nav ol').append('<li class="rn_BreadcrumbLevel rn_Leve130" itemtype="http://data-vocabulary.org/Breadcrumb"><a class="bCrumb" href="/app/answers/topics" itemprop="url"><span itemprop="title">topics</span></a></li>');
            $('.rn_ListItemLink').each(function() {
                if($(this).css('font-weight') == 'bold') {
                    var categoryTitle = $(this).text();
                    $('#pageHeading').text(categoryTitle);
                }
            });
        }, 1000);
    });
    function getUrlParam(url, index, key) {
        return url.replace(/^https?:\/\//, '').split('/')[index];
    }
    var url = window.location.href;
    var thisKW = getUrlParam(url,5, "a_id");
    if(thisKW) {
        $('.rn_SourceResultDetails').append(' for <span style="text-decoration:underline;"><b>'+thisKW+'</b></span>');
    }
</script>
</div>