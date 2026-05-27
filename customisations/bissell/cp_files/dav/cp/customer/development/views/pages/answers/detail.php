<rn:meta title="#rn:php:\RightNow\Libraries\SEO::getDynamicTitle('answer', \RightNow\Utils\Url::getParameter('a_id'))#" template="standard.php" answer_details="true" clickstream="answer_view" />
<?php if (getUrlParm('a_id') == 5696 || getUrlParm('a_id') == 5697 ) {?>
	<meta name="robots" content="noindex, nofollow" />
	
<? header("Location: https://support.bissell.com/app/crosswave-cordless-safety-recall/site/" . $site);
	
	} ?>
	
<?php
	$referrer = "US"; 
	$thisProduct = getUrlParm('p');
	$thisSKU = getUrlParm('sku')    
?>
<div class="rn_PageContent">
	
	<? /* If I get a productID on teh URL, then we can send them back to the product details page from the answer */ ?>
	<rn:condition url_parameter_check="p != null">
       <a href="/app/products/detail/p/<?=$thisProduct;?>" alt="#rn:msg:BACK_TO_LBL# #rn:msg:PRODUCT_DETAILS_LBL#">#rn:msg:BACK_TO_LBL# <rn:field name="ServiceProduct.Name"/></a>
    </rn:condition>

<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
    <div class="rn_ContentDetail">
        <div class="rn_PageTitle rn_RecordDetail rn_LeftArea">
            <span class="rn_Summary" itemprop="name"><h1><rn:field name="Answer.Summary" highlight="true"/></h1></span>
		    <div class="rn_AnswerQuestion">
	                <rn:field name="Answer.Question" highlight="true"/>
	        </div>
        
            <div class="rn_RecordText rn_AnswerText" itemprop="articleBody">
            	<div id=scroll></div>
                <span id="rn_ariaSolution"><rn:field name="Answer.Solution" highlight="true"/></span>
                <div id="ytId" style="display:none;"><rn:field name="Answer.CustomFields.c.youtube_id" highlight="true"/></div>
                
	        <? if($referrer == "US") { ?>   
	            <? if (getUrlParm('a_id') == 2599) {?>
				<rn:widget path="custom/OPA/OPAWidget" policy_model="No-Power_CrossWave-Cordless" seed_data="{'product':'CrossWave-Cordless', 'country':'#rn:php:$referrer#'}" />
				<? } ?>
				
				<? if (getUrlParm('a_id') == 3845) {?>
				<rn:widget path="custom/OPA/OPAWidget" policy_model="No-Suction_CrossWave-Cordless-Max" seed_data="{'product':'CrossWave-Cordless-Max', 'country':'#rn:php:$referrer#'}" />
	
				<? } ?>
			<? } ?>
		
				<div id="bissellcomURL" style="display:none;"><rn:field name="Answer.CustomFields.c.bissellcom_video_url" highlight="true"/></div>
				
				<? /* THIS IS FOR TROUBLESHOOTING ANSWERS, DO NOT REMOVE */ ?>
				
				<div class="rn_TS_FinishedSection rn_Hidden" id="rn_TS_FinishedSection">
					<div class="rn_TS_StepSectionHeadline">#rn:msg:CUSTOM_MSG_TROUBLESHOOTING_FINISH_HEADLINE#</div>
					<div id="rn_TS_FinishedSectionContent" class="rn_TS_FinishedSectionContent">#rn:msg:CUSTOM_MSG_TROUBLESHOOTING_FINISH_CONTENT#</div>
				</div>
            </div>
            <rn:widget path="knowledgebase/GuidedAssistant"/>
            <div class="rn_FileAttach">
                <rn:widget path="output/DataDisplay" name="Answer.FileAttachments" label="#rn:msg:ATTACHMENTS_LBL#"/>
            </div>
			
			<div class="rn_DetailTools rn_HideInPrint">
                <div class="rn_Links">
                    <ul id="rn_ulDetail">
	                    <li id="rn_emailLink">
                    <rn:widget path="custom/utils/EmailAnswerLink" label_link=" "/>
	                    </li>
	                    <li id="rn_printLink">
                    <rn:widget path="utils/PrintPageLink" label_link=" "/>
	                    </li>
                    </ul>
                </div>
            </div>
            <rn:widget path="feedback/AnswerFeedback" dialog_threshold="1" label_dialog_title="#rn:msg:THANKS_FOR_YOUR_FEEDBACK_MSG#" label_dialog_description="#rn:msg:CUSTOM_MSG_TELL_MORE_EXPERIENCE#" label_title="#rn:msg:WAS_THIS_ANSWER_HELPFUL_MSG#"/>
        </div>
        <div class="rn_PageContent rn_RecordDetail rn_RightArea">
	    	<rn:widget path="knowledgebase/RelatedAnswers" label_title="#rn:msg:CUSTOM_MSG_RELATED_CONTENT#" limit=10 />
	    </div>
    </div>
    <? /* <div id="rn_ReportGrid" style="display:none;"><rn:widget path="reports/Grid" report_id="100033"/></div> */ ?>
</article>




<script type="text/javascript">
/* TROUBLESHOOTING ANSWER FUNCTIONS */

	function showItWorked(currentStep) {
		//alert(currentStep);
		$(".rn_TS_StepSectionContent").addClass('rn_Hidden');
		$("#rn_TS_FinishedSection").removeClass('rn_Hidden');
		var element_to_scroll_to = document.getElementById('scroll');
		element_to_scroll_to.scrollIntoView();
}
	function DidNotWork(currentStep, NextStep) {	
		currentDiv = "#rn_TS_StepSectionContent_" + currentStep;
		//alert(currentDiv);	
		nextDiv = "#rn_TS_StepSectionContent_" + NextStep;
		//alert(nextDiv);
		$(nextDiv).removeClass('rn_Hidden');
	    $(currentDiv).addClass('rn_Hidden');
	    var element_to_scroll_to = document.getElementById('scroll');
		element_to_scroll_to.scrollIntoView();
	}
	function StartTroubleshooting(prodIssue){
		var urlredirect = window.location.origin + '/app/productcheck';
		
		if(prodIssue){
			//urlredirect += '/i/' + prodIssue;
			sessionStorage.setItem('TTProdIssue', prodIssue);
		}
		
		location.href = urlredirect;
	}
	function getUrlParam(url, index, key) {
		return url.replace(/^https?:\/\//, '').split('/')[index];
	}
	
	
$(document).ready(function() {
	function checkForChanges(){
    if ($('#rnDialog1_c').hasClass('yui3-panel-focused'))
        $('#rnDialog1_c').attr('aria-modal','true');
    else
        setTimeout(checkForChanges,500);
	}	
	$(checkForChanges);	
	
}); 
	
    var pageTitle = $('.rn_Summary').text();
    //$('.rn_Summary').hide();
    $('#pageHeading').text(pageTitle);

$(document).ready(function() {
        //Update page meta description
    if(document.querySelector('meta[name="description"]')){
		document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
	}
	if(document.querySelector('meta[property="og:title"]')){
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
	}
	if(document.querySelector('meta[property="og:description"]')){
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
	}
		//Prepend YouTube Self Help Video
        var url = window.location.href;
        var thisAnswer = getUrlParam(url,5, "a_id");
		var ytId = $('#ytId').text();
		var bissellcomURL = $('#bissellcomURL').text();
		var linksList = document.querySelectorAll('head > link');
		for (var i=0; i < linksList.length; i++){
			console.log(linksList[i].rel);
		}
		
		for (var i=0; i < linksList.length; i++){
			if (linksList[i].rel == "canonical"){
				if (linksList[i].href.indexOf(thisAnswer) >= 0){
					if(bissellcomURL){
						linksList[i].setAttribute("href", bissellcomURL);
					}
				}
			}
		}
		
        if(ytId) {
	        var downloadLinkHTML = '<div class="video-transcript-link text-center pb-1" data-downloadsrc="https://www.bissell.com/on/demandware.static/-/Library-Sites-shared-library-bissell/default/dwd02b34ce/us/12-other/video-descriptions/description_'+ytId+'.pdf">';
					downloadLinkHTML += '<a href="https://www.bissell.com/on/demandware.static/-/Library-Sites-shared-library-bissell/default/dwd02b34ce/us/12-other/video-descriptions/description_'+ytId+'.pdf" class="link-tertiary" title="#rn:msg:CUSTOM_MSG_DOWNLOAD_VIDEO_TRANSCRIPT#" aria-label="#rn:msg:CUSTOM_MSG_DOWNLOAD_VIDEO_TRANSCRIPT#" download="videotranscript">';
					downloadLinkHTML += '#rn:msg:CUSTOM_MSG_DOWNLOAD_VIDEO_TRANSCRIPT#</a></div>';
			
			$('.rn_AnswerText').prepend(downloadLinkHTML);
			$('.rn_AnswerText').prepend('<div class="player_flex"><iframe src="https://www.youtube.com/embed/'+ytId+'" frameborder="0" allowfullscreen></iframe></div>');
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
        // var url = window.location.href;
        // var thisAnswer = getUrlParam(url,5, "a_id");
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
    
    /* TROUBLESHOOTING ANSWER FUNCTIONS */
    $('.rn_TS_StepSectionHeader').on('click', function(event) {
		event.preventDefault();
	    //alert(this.id);
	    //alert(this.id.split(/\_/)[3]);
	    theSection = this.id.split(/\_/)[3];
	    //alert(theSection);	
	    //answerTitle = "#AnswerTitle_" + theSection + " a";
	    contentSection ="#rn_TS_StepSectionContent_" + theSection;
	    //alert(contentSection);
	    //answerSection = "#AnswerText_" + theSection;
	    //alert(answerSection);
	    if ($(contentSection).hasClass('rn_Hidden'))
	        $(contentSection).removeClass('rn_Hidden');
	    else
	        $(contentSection).addClass('rn_Hidden');
	    
	    $(contentSection).focus();
	  })
    
		$('.rn_ReturnAnswerLink').on('click', function(event) {
			event.preventDefault();
			//alert(this.id);
			//alert(this.id.split(/\_/)[1]);
			theSection = this.id.split(/\_/)[1];
			//alert(theSection);	
			answerTitle = "#AnswerTitle_" + theSection + " a";
			//alert(answerTitle);
			answerSection = "#AnswerText_" + theSection;
			//alert(answerSection);
			if ($(answerSection).hasClass('rn_Hidden'))
				$(answerSection).removeClass('rn_Hidden');
			else
				$(answerSection).addClass('rn_Hidden');
			
			$(answerTitle).focus();
		  })
        
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
	$('a[id^="rn_EmailAnswerLink_"]').attr('aria-label', '#rn:msg:EMAIL_ANS_LBL#');
	$('a[title="Print this page"]').attr('aria-label', '#rn:msg:PRINT_THIS_PAGE_CMD#');
	
	
	
	$('h3.rn_answerVideo').replaceWith(function(){
    	return $("<p>", {html: $(this).html()}, "</p>");
	});
    
</script>
</div>