<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('answer', \RightNow\Utils\Url::getParameter('a_id'))#" template="standard.php" answer_details="true" clickstream="answer_view" />

<div class="rn_Hero rn_StainRemovalHero">
    <div class="rn_HeroInner">
		<form id="rn_SearchStainGuides" method="post" action="/app/stain_removal_list">
				<div id="rn_ErrorLocation"></div>
				
				<rn:widget path="custom/input/ProductCategorySelectMenu" 
					hierarchy_depth="3" 
					starting_level_id="170988" 
					name="Category" 
					required="true"
					default_label="Stain Removal Guide" 
					placeholder="" 
					final_selection_label=""
					auto_redirect_url = "/app/stain_removal_list" />
				
			<div class="rn_Hidden">		
			<rn:widget path="input/FormSubmit" label_button="go>>" error_location="rn_ErrorLocation"/>
			</div>
		</form>
	</div>
</div>

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
			
			<div class="rn_DetailTools rn_HideInPrint">
                <div class="rn_Links">
                    <rn:condition logged_in="true">
                    </rn:condition>
                    <rn:widget path="utils/EmailAnswerLink" label_link=" "/>
                    <rn:widget path="utils/PrintPageLink" label_link=" "/>
                </div>
            </div>
            <rn:widget path="feedback/AnswerFeedback" label_title="Rate this answer" options_count=5 dialog_threshold=2/>
        </div>
    </div>
    <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div>
</article>
<script type="text/javascript">
    var pageTitle = $('.rn_Summary').text();
    //$('.rn_Summary').hide();
    $('#pageHeading').text(pageTitle);

    $(document).ready(function() {
        //Prepend YouTube Self Help Video
        document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
		var ytId = $('#ytId').text();
		console.log(ytId);
        if(ytId) {
	        $('.rn_AnswerText').prepend('<div class="player"><iframe width="800" height="500" src="https://www.youtube.com/embed/'+ytId+'" frameborder="0" allowfullscreen></iframe></div>');
	        $('.rn_AnswerText').prepend('<div class="player_mobile rn_Hidden"><iframe width="400" height="250" src="https://www.youtube.com/embed/'+ytId+'" frameborder="0" allowfullscreen></iframe></div>');
		}	
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
        function selectCategory() {
            var cat = $('.rn_Level30').text();
            cat = cat.replace(/[^A-Za-z]+/g, '');
            $('[id^="rn_CategoryTopMenu"]').find('a').each(function() {
                var catText = $(this).text();
                catText = catText.replace(/[^A-Za-z]+/g, '');
                if(catText == cat) {
                    $(this).css('font-weight', 'bold').css('color','#000').after('<img class="up_TopNavArrow" src="images/arrow-up-grey.png">');
                    var $x = $('.rn_ListItemLink');
                    var $next = $x.eq($x.index(this) + 1);
                            $next.css('padding-left','0px').css('margin-left','-5px');
                    $x.last().css('border-right','none');
                }
            });
            $('[id^="rn_CategorySideMenu"]').find('a').each(function() {
                var catText2 = $(this).text();
                catText2 = catText2.replace(/[^A-Za-z]+/g, '');
                if(catText2 == cat) {
                    $(this).css('font-weight', 'bold');
                    $(this).parent().css('margin-right', '0px');
                }
            });
        }
        $('input[id$="InputSenderEmail"]').val('');
        /*jQuery.fn.titlecase = function() {
            return this.each(function() {
                var newText = jQuery(this).text().replace(/([\w&`'‘’"“.@:\/\{\(\[<>_]+-? *)/g,
                function(match, p1, index, title) {
                    if (index > 0 && title.charAt(index - 2) !== ":" && match.search(/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i) > -1) return match.toLowerCase();
                    if (title.substring(index - 1, index + 1).search(/['"_{(\[]/) > -1) return match.charAt(0) + match.charAt(1).toUpperCase() + match.substr(2);
                    if (match.substr(1).search(/[A-Z]+|&|[\w]+[._][\w]+/) > -1 || title.substring(index - 1, index + 1).search(/[\])}]/) > -1) return match;
                    return match.charAt(0).toUpperCase() + match.substr(1);
                });
                jQuery(this).text(newText)
            });
        };
        $(function() {
            $('.rn_AnswerQuestion').titlecase();
        });*/
        
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
