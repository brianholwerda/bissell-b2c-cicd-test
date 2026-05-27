<rn:meta title="BISSELL Support | #rn:url_param_value:kw# | #rn:php:\RightNow\Libraries\SEO::getDynamicTitle('category', \RightNow\Utils\Url::getParameter('c'))# | #rn:php:\RightNow\Libraries\SEO::getDynamicTitle('product', \RightNow\Utils\Url::getParameter('p'))#" template="standard.php" clickstream="answer_list"/>

<div class="rn_PageContent rn_AnswersList">
<? if($referrer == 'US') { ?><!-- US Search Results--> <? } else { ?> <!-- Canada Search Results -->  <? } ?>
<rn:condition url_parameter_check="kw != null">
	
	<rn:condition logged_in="false">
		<!-- not logged in -->
		<? if($referrer == 'US') { ?><rn:container report_id="115720"> <!-- 115720 (US) --> <? } else { ?> <rn:container report_id="115719"> <!-- 115719 (CA) -->  <? } ?>
	<rn:condition_else/>
		<!-- logged in -->
		<? if($referrer == 'US') { ?><rn:container report_id="115720"> <!-- 115720 (US) --> <? } else { ?> <rn:container report_id="115719"> <!-- 115719 (CA) -->  <? } ?>
	</rn:condition>
	<div class="rn_Container">
	    <div class="rn_PageContent rn_KBAnswerList">
	        <? /* <div class="rn_HeaderContainer">
	            <h2>Product Detail Pages</h2>
	            <rn:widget path="knowledgebase/RssIcon" /> 
	        </div> */ ?>
	
	        <rn:condition flashdata_value_for="info">
	            <div class="rn_MessageBox rn_InfoMessage">
	                #rn:flashdata:info#
	            </div>
	        </rn:condition>
	
	        <div class="rn_ProductDescSearchTerm">
	            <rn:widget path="custom/reports/ResultInfoHeader" show_no_results_msg_without_search_term="false" result_headline="Product Detail Pages" />
	             <rn:widget path="custom/reports/icon_multiline" hide_columns="answers.updated" highlight="false" image_path2="#rn:php:$cdn150#"/>
	            <rn:widget path="reports/Paginator"/>
	        </div>
	    </div>
	
	    <?/*<aside class="rn_SideRail" role="complementary">
	        <rn:widget path="utils/ContactUs"/>
	        <rn:widget path="discussion/RecentlyViewedContent"/>
	    </aside>*/?>
	</div>
	</rn:container>
	<rn:condition logged_in="false">
		<!-- not logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115722"> <!-- 115722 (US) --> <? } else { ?> <rn:container report_id="115721"> <!-- 115721 (CA) -->  <? } ?>
	<rn:condition_else/>
		<!-- logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115722"> <!-- 115722 (US) --> <? } else { ?> <rn:container report_id="115721"> <!-- 115721 (CA) -->  <? } ?>
	</rn:condition>
	
	<div class="rn_Container">
	    <div class="rn_PageContent rn_KBAnswerList">
	        <?/* <div class="rn_HeaderContainer">
	            <h2>Product Detail Pages</h2>
	            <rn:widget path="knowledgebase/RssIcon" />
	        </div> */ ?> 
	
	        <rn:condition flashdata_value_for="info">
	            <div class="rn_MessageBox rn_InfoMessage">
	                #rn:flashdata:info#
	            </div>
	        </rn:condition>
	
	        <div class="rn_ProductSKUSearchTerm">
	            <rn:widget path="reports/ResultInfoHeader" show_no_results_msg_without_search_term="true" result_headline="Product Detail Pages"  />
	            <rn:widget path="custom/navigation/SingleProductNavigation" highlight="false" />
	            <rn:widget path="custom/reports/icon_multiline" hide_columns="answers.updated" highlight="false" image_path2="#rn:php:$cdn150#"/>
	            
	            <rn:widget path="reports/Paginator"/>
	        </div>
	    </div>
	
	    <?/*<aside class="rn_SideRail" role="complementary">
	        <rn:widget path="utils/ContactUs"/>
	        <rn:widget path="discussion/RecentlyViewedContent"/>
	    </aside>*/?>
	</div>
	</rn:container>
	<rn:condition logged_in="false">
		<!-- not logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115718"> <!-- 115718 (US) --> <? } else { ?> <rn:container report_id="115717"> <!-- 115717 (CA) -->  <? } ?>
	<rn:condition_else/>
		<!-- logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115715"> <!-- 115715 (US) --> <? } else { ?> <rn:container report_id="115716"> <!-- 115716 (CA) -->  <? } ?>
	</rn:condition>
		
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
			<rn:widget path="custom/reports/ListAnswerNavigation"/>
			<style>
				/*Have to put this here so answerID doesn't show. */
				.rn_StandardSearch .rn_Multiline .rn_ElementsHeader,  .rn_StandardSearch .rn_Multiline .rn_ElementsData {
					display: none;
				}
			</style>
			
	        <div class="rn_StandardSearch">
	            <rn:widget path="standard/reports/ResultInfo" show_no_results_msg_without_search_term="true"/>
	            <rn:widget path="standard/reports/Multiline" hide_columns="answers.updated"/>
	            <rn:widget path="reports/Paginator"/>
	        </div>
	    </div>
	
	    <?/*<aside class="rn_SideRail" role="complementary">
	        <rn:widget path="utils/ContactUs"/>
	        <rn:widget path="discussion/RecentlyViewedContent"/>
	    </aside>*/?>
	</div>
	</rn:container>
<rn:condition_else/>
	<rn:condition logged_in="false">
		<!-- not logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115718"> <!-- 115718 (US) --> <? } else { ?> <rn:container report_id="115717"> <!-- 115717 (CA) -->  <? } ?>
	<rn:condition_else/>
		<!-- logged in -->
		<? if($referrer == 'US') { ?> <rn:container report_id="115715"> <!-- 115715 (US) --> <? } else { ?> <rn:container report_id="115716"> <!-- 115716 (CA) -->  <? } ?>
	</rn:condition>
    
		
    <div class="rn_Container">
        <div class="rn_PageContent rn_KBAnswerList rn_StandardSearch">
            <?/*<div class="rn_HeaderContainer">
                <h2>#rn:msg:PUBLISHED_ANSWERS_LBL#</h2>
                <rn:widget path="knowledgebase/RssIcon" />
            </div>*/?>

            <rn:condition flashdata_value_for="info">
                <div class="rn_MessageBox rn_InfoMessage">
                    #rn:flashdata:info#
                </div>
            </rn:condition>
	
			<rn:widget path="custom/reports/ListAnswerNavigation"/>
			<style>
				/*Have to put this here so answerID doesn't show. */
				.rn_StandardSearch .rn_Multiline .rn_ElementsHeader,  .rn_StandardSearch .rn_Multiline .rn_ElementsData {
					display: none;
				}
			</style>
            <div>
				<rn:widget path="reports/ResultInfo" show_no_results_msg_without_search_term="true"/>
                <rn:widget path="standard/reports/Multiline" hide_columns="answers.updated" />
                <rn:widget path="reports/Paginator"/>
            </div>
        </div>

        <?/*<aside class="rn_SideRail" role="complementary">
            <rn:widget path="utils/ContactUs"/>
            <rn:widget path="discussion/RecentlyViewedContent"/>
        </aside>*/?>
    </div>
    </rn:container>
</rn:condition>


<style>
.visibility{
	visibility: hidden;
}
.rn_Paginator a {
	color: white;
}
</style>

<script type="text/javascript">
    $(document).ready(function() {
	    
	    //alert($(".rn_StandardSearch .rn_ResultInfo .rn_Results").hasClass("rn_Hidden"));
	   if($(".rn_ProductDescSearchTerm .rn_ResultInfoHeader .rn_Results").hasClass("rn_Hidden")) {
		    $(".rn_ProductDescSearchTerm .rn_Paginator").addClass("rn_Hidden");
		    $(".rn_ProductDescSearchTerm .rn_ResultInfo .rn_Suggestion ").addClass("rn_Hidden");
	    } else {
		    $(".rn_ProductDescSearchTerm .rn_Paginator a").css("color", "#00205b");
	    }
	    if($(".rn_ProductSKUSearchTerm .rn_ResultInfoHeader .rn_Results").hasClass("rn_Hidden")) {
		    $(".rn_ProductSKUSearchTerm .rn_Paginator").addClass("rn_Hidden");
		    $(".rn_ProductSKUSearchTerm rn_ResultInfo .rn_Suggestion ").addClass("rn_Hidden");
	    } else {
		    $("rn_ProductSKUSearchTerm .rn_ResultInfoHeader .rn_Paginator a").css("color", "#00205b");
	    }
	    if($(".rn_StandardSearch .rn_ResultInfo .rn_Results").hasClass("rn_Hidden")) {
		    $(".rn_StandardSearch .rn_Paginator").addClass("rn_Hidden");
	    } else {
		    $(".rn_StandardSearch .rn_Paginator a").css("color", "#00205b");
	    }
        /*if (window.location.href.indexOf("2599") > 0) {
		    $('#rn_answerContentOIA').removeClass('visibility');
		}*/
		/*if (window.location.href.indexOf("3845") > 0) {
		    $('#rn_answerContentOIA').removeClass('visibility');
		}*/
		if (window.location.href.indexOf('/kw/') == -1){
			document.title = document.title.substring(0, 17) + document.title.substring(19, document.title.length);
		}
		if (window.location.href.indexOf('/c/') == -1){
			document.title = document.title.replace('| Category', '');
		}
		if (window.location.href.indexOf('/p/') == -1){
			document.title = document.title.replace('| Product', '');
		} else {
			document.title = document.title.replace('BISSELL Support | ', '');
		}
		document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
		document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
		document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
		
		/*$('li.rn_NextPage').prepend("<span aria-hidden='true'>");
		$('li.rn_NextPage').append("</span>");*/
		
		/*$('li.rn_PreviousPage').attr("aria-hidden", "true");*/
    });
    
</script>
</div>