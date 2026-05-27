<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="standard_internal.php" login_required="true" clickstream="answer_list"  />

<rn:widget path="custom/reports/ListAnswerNavigation"/>

<rn:condition url_parameter_check="kw != null">
    
    </rn:container>

    <div class="rn_Container">
        <div class="rn_PageContent rn_KBAnswerList">
            <div class="rn_HeaderContainer">
                <h2>#rn:msg:PUBLISHED_ANSWERS_LBL#</h2>
            </div>
            <rn:condition flashdata_value_for="info">
                <div class="rn_MessageBox rn_InfoMessage">
                    #rn:flashdata:info#
                </div>
            </rn:condition>
            <rn:widget path="search/DisplaySearchFilters" show_filter_label="true" />

            <rn:container source_id="KFSearch" per_page="10" history_source_id="KFSearch">
            <div>
				<rn:widget path="reports/ResultInfo"/>
				<!-- <rn:widget path="searchsource/SourceResultDetails"/> -->
                <rn:widget path="searchsource/SourceResultListing" hide_when_no_results="true" more_link_url=""/>
                <rn:widget path="searchsource/SourcePagination"/>
            </div>
            </rn:container>
        </div>
        
    </div>
<rn:condition_else/>
    <rn:container report_id="176">
    <div class="rn_Hero">
        
    </div>

    <div class="rn_Container">
        <div class="rn_PageContent rn_KBAnswerList">
            <div class="rn_HeaderContainer">
                <h2>#rn:msg:PUBLISHED_ANSWERS_LBL#</h2>
            </div>

            <rn:condition flashdata_value_for="info">
                <div class="rn_MessageBox rn_InfoMessage">
                    #rn:flashdata:info#
                </div>
            </rn:condition>
            
            <rn:widget path="custom/reports/ListAnswerNavigation"/>
            
            <rn:widget path="search/DisplaySearchFilters" show_filter_label="true" />
			
            <div>
				
                <rn:widget path="reports/ResultInfo" show_no_results_msg_without_search_term="true"/>
                <rn:widget path="reports/Multiline" hide_columns="answers.updated"/>
                <rn:widget path="reports/Paginator"/>
            </div>
        </div>

    </div>
    </rn:container>
</rn:condition>
