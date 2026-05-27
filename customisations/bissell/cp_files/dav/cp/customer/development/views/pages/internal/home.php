<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="standard_internal.php" clickstream="home" login_required="true" />


<div class="rn_PageContent rn_Home">
  
    <div class="rn_PopularSocial">
        <div class="rn_Container">
           	<?/*<rn:condition config_check="CUSTOM:CUSTOM_CFG_SHOW_BANNER == true">*/?>
           	<div id="rn_HotTopics">
			<?/*<rn:condition url_parameter_check="p == null">*/?>
			<h2>#rn:msg:CUSTOM_MSG_HOT_TOPICS_LBL#</h2>
			<rn:widget path="reports/HotTopics" report_id="100141" show_excerpt="true" limit="2">
			<?/* <rn:widget path="reports/TopAnswers" limit="2" avatar_size="none" category_filter_id="25865" show_excerpt="true" /> */?>
			<?/*<rn:condition_else/>
			<h2><rn:field name="ServiceProduct.Name" /> - #rn:msg:CUSTOM_MSG_HOT_TOPICS_LBL#</h2>
			<rn:widget path="reports/TopAnswers" limit="2" product_filter_id="#rn:url_param_value:p#" avatar_size="none" category_filter_id="25865" show_excerpt="true" />
			</rn:condition>*/?>
        </div>
			<?/*</rn:condition>*/?>
            <div class="rn_DiscussionsButton">
           <!-- <a href="/app/answers/list/#rn:session/kw/null/c/25865">#rn:msg:CUSTOM_MSG_MORE_HOT_TOPICS_LBL#</a>
            --> </div >
        </div>
    </div>
            
	<div class="rn_PopularKB">
	    <?/*<h2>#rn:msg:POPULAR_PUBLISHED_ANSWERS_LBL#</h2> */?>
        <div class="rn_Container">
        	<rn:widget path="custom/search/TopQuestionsByCategoryImg" report_page_url="/app/answers/list" data_type="categories" label_title=""  only_display="#rn:config:CUSTOM_CFG_DISPLAY_HOME_INTERNAL_PROD_CAT#" num_of_columns="3"/>
        </div>
    </div>
    
    <div class="rn_TopQuestions">
	         <h2>#rn:msg:CUSTOM_MSG_TOP_FREQ_ASKED_QUESTIONS_LBL#</h2> 
        <div class="rn_Container">
	        <rn:widget path="reports/TopAnswers" show_excerpt="true"/>
            <div class="rn_DiscussionsButton">
                <a href="/app/answers/list#rn:session#">#rn:msg:CUSTOM_MSG_MORE_TOP_FREQ_ASKED_QUESTIONS_LBL#</a>
            </div>
        </div>
    </div>
	
	<?/*<div class="rn_AskQuestion">
    	<div class="rn_Container">
	        <h2>#rn:msg:CUSTOM_MSG_ASK_HDR#</h2>
	    	<div class="rn_AskQuestionText">#rn:msg:CUSTOM_MSG_ASK_DETAIL#</div>
		<div class="rn_DiscussionsButton"><a href="/app/ask" target="_self">#rn:msg:CUSTOM_MSG_ASK_BTN#</a>
        </div>
    </div>*/?>
    
    </div>
