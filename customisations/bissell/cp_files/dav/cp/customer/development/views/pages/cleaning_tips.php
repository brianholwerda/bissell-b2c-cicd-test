<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('answer', \RightNow\Utils\Url::getParameter('a_id'))#" template="standard.php" answer_details="true" clickstream="answer_view" />


<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
    <div class="rn_ContentDetail">
        <div class="rn_PageTitle rn_RecordDetail">
            <span class="rn_Summary" itemprop="name"><h1><rn:field name="Answer.Summary" highlight="true"/></h1></span>
	    <div class="rn_AnswerQuestion">
                <rn:field name="Answer.Question" highlight="true"/>
            </div>
        </div>
        <div class="rn_PageContent rn_RecordDetail rn_LeftArea">
            <div class="rn_RecordText rn_AnswerText" itemprop="articleBody">
                <rn:field name="Answer.Solution" highlight="true"/>
                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true"/></div>
            </div>
            <rn:widget path="knowledgebase/GuidedAssistant"/>
            <div class="rn_FileAttach">
                <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#"/>
            </div>
        </div>
    </div>
    <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div>
</article>
<div id="rn_CleaningTipsCategoryDisplay" class="rn_Container">
        <rn:widget path="standard/navigation/VisualProductCategorySelector" type="category" landing_page_url="/app/cleaning_tips_list" image_path="/euf/assets/images/prodcat-images/cleaning-tips" prefetch_sub_items="false" show_sub_items_for="171524" maximum_items="40" />
        <!-- 171762,171763,171764,171765,171766,171767,171768,171769,171770 -->
    </div>
<script type="text/javascript">
    var pageTitle = $('.rn_Summary').text();
    //$('.rn_Summary').hide();
    $('#pageHeading').text(pageTitle);

    $(document).ready(function() {
        document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
        //Convert button shortcode
        $('.rn_AnswerText p').each(function() {
            var pText = $(this).text();
            if(pText == "##:Button") {
                var linkText = $(this).next().hide().text();
                var textText = $(this).next().next().hide().text();
                $(this).next().next().next().hide();
                linkText = linkText.replace(':link:','');
                textText = textText.replace(':text:','');
                var button = '<button type="button"><a href="'+linkText+'">'+textText+'</a></button>';
                $(this).html(button);
            }
        });
        //Get Views
        function getUrlParam(url, index, key) {
            return url.replace(/^https?:\/\//, '').split('/')[index];
        }
        var url = window.location.href;
        var thisAnswer = getUrlParam(url,5, "a_id");
        $('.yui3-datatable-data').find('.yui3-datatable-cell ').each(function(i, obj) {
            var cellText = $(this).text();
            cellText = cellText.replace(/[^0-9.]/g, "");
            if(cellText == thisAnswer) {
                var cats = $(this).next().text();
                setTimeout(function(){
                    $('.rn_ProductCategoryBreadcrumb nav ol').append('<li class="rn_BreadcrumbLevel rn_Level30" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/app/answers/list/'+cats+'" itemprop="url"><span itemprop="title">'+cats+'</span></a></li>');
                    selectCategory();
                }, 1000);
                return false;
            }
        });
 
    function OpenHiddenText(section) {    
	   myID = "rn_AnswerHiddenText" + section;
	   //alert(myID) 
       var e = document.getElementById(myID);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
	}
    
</script>
