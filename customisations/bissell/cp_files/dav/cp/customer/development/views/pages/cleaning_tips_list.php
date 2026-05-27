<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="standard.php" clickstream="answer_list"/>

<rn:container report_id="176">

<div class="rn_Container">
    <div class="rn_PageContent rn_KBAnswerList">
        <?/*<div class="rn_HeaderContainer">
            <h2>#rn:msg:PUBLISHED_ANSWERS_LBL#</h2>
            <rn:widget path="knowledgebase/RssIcon" />
        </div>*/?>

        <rn:condition flashdata_value_for="info">
            <div class="rn_MessageBox rn_InfoMessage">
                #rn:flashdata:info#
            </div>
        </rn:condition>

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
