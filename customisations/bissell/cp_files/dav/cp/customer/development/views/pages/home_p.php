<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="standard.php" clickstream="home" login_required="true" />


<div class="rn_PageContent rn_Home">
  
    <div class="rn_PopularSocial">
        <div class="rn_Container">
           	<rn:condition config_check="CUSTOM:CUSTOM_CFG_SHOW_BANNER == true">
			<rn:condition url_parameter_check="p == null">
			<h2>#rn:msg:CUSTOM_MSG_HOT_TOPICS#</h2>
			<rn:widget path="custom/reports/TopAnswersDate" limit="2" product_filter_id="#rn:url_param_value:p#" avatar_size="none" report_id="104168" category_filter_id="985" show_excerpt="true" />
			        <rn:condition_else/>
					<h2><rn:field name="ServiceProduct.Name" /> - #rn:msg:CUSTOM_MSG_HOT_TOPICS#</h2>
			<rn:widget path="custom/reports/TopAnswersDate" limit="2" product_filter_id="#rn:url_param_value:p#" report_id="104168" avatar_size="none" category_filter_id="985" show_excerpt="true" />
			</rn:condition>
			</rn:condition>
            <div class="rn_DiscussionsButton">
                <a href="/app/answers/list/p/#rn:url_param_value:p#/c/985">#rn:msg:CUSTOM_MSG_MORE_HOT_TOPICS#</a>
            </div >
        </div>
    </div>
	
  <div class="rn_PopularKB">
        <div class="rn_Container">
          <? /*  <h2>#rn:msg:POPULAR_PUBLISHED_ANSWERS_LBL#</h2> */ ?>
      <rn:widget path="custom/search/TopQuestionsByCategoryImg" report_page_url="/app/answers/list" data_type="categories" label_title="" only_display="#rn:config:CUSTOM_CFG_DISPLAY_HOME_PROD_CAT#"  num_of_columns="3"/>
        </div>
    </div>
    
    <?/*<div class="rn_AskSocial">
        <div class="rn_Container">
          <h2>#rn:msg:CUSTOM_MSG_ASK_SOCIAL_HDR#</h2>
		  #rn:msg:CUSTOM_MSG_ASK_SOCIAL_BODY#
            <div class="rn_DiscussionsButton">
                <a href="/app/social/ask">#rn:msg:CUSTOM_MSG_ASK_SOCIAL_BTN#</a>
            </div>
        </div>
    </div>*/?>

	<div class="rn_PopularKB">
	         <h2>#rn:msg:POPULAR_PUBLISHED_ANSWERS_LBL#</h2> 
        <div class="rn_Container">
      <rn:widget path="custom/search/TopQuestionsByCategory" report_page_url="/app/answers/list" data_type="categories" label_title=""  only_display="#rn:config:CUSTOM_CFG_DISPLAY_HOME_PROD_CAT#"  num_of_columns="2"/>
         <div class="rn_DiscussionsButton">
                <a href="/app/answers/list#rn:session#">#rn:msg:SHOW_MORE_PUBLISHED_ANSWERS_LBL#</a>
            </div>

        </div>
    </div>
	  <div class="rn_AdditionalKB">
        <div class="rn_Container">
      <rn:widget path="custom/search/TopQuestionsByCategoryImg" report_page_url="/app/answers/list" data_type="categories" label_title="" only_display="423,424,425,426,427,428"  num_of_columns="2"/>
        </div>
    </div>
  <div class="rn_AskQuestion">
        <div class="rn_Container">
          <h2>#rn:msg:CUSTOM_MSG_ASK_HDR#</h2>
		  #rn:msg:CUSTOM_MSG_ASK_DETAIL#
            <div class="rn_DiscussionsButton">
                <a href="/app/ask">#rn:msg:CUSTOM_MSG_ASK_BTN#</a>
            </div>

        </div>
    </div>


</div>
