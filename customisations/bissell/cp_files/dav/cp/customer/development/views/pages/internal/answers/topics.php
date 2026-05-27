<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="standard_internal.php" login_required="true" clickstream="answer_list"/>

<rn:widget path="custom/search/TopicBrowse" max_topics="8" default_category="8" max_answers="100"/>

<script type="text/javascript">
    $(document).ready(function() {
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