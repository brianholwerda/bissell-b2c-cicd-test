<rn:meta title="#rn:msg:FIND_ANSWERS_AND_DISCUSSIONS_LBL#" template="standard.php" clickstream="answer_list"/>
<div class="rn_PageContent">
<div class="rn_Container">
    <div class="rn_PageContent rn_ResultList">
        <div class="rn_KBAnswerResults">
            <rn:container source_id="KFSearch" per_page="5">
                <rn:widget path="searchsource/SourceResultDetails"/>
                <rn:widget path="searchsource/SourceResultListing" label_heading=""/>
            </rn:container>
        </div>

    </div>

    <?/*<aside class="rn_SideRail" role="complementary">
        <rn:widget path="utils/ContactUs"/>
        <rn:widget path="discussion/RecentlyViewedContent"/>
    </aside>*/?>
</div>

</div>