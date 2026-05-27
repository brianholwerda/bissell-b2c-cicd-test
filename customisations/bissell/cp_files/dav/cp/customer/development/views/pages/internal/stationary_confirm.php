<rn:meta title="#rn:msg:QUESTION_SUBMITTED_LBL#" template="standard_internal.php" clickstream="incident_confirm" login_required="true" />

<div id="rn_PageTitle" class="rn_AskQuestionConfirm">
    <h3>#rn:msg:QUESTION_SUBMITTED_HDG#</h3>
</div>

<div id="rn_PageContent" class="rn_AskQuestionConfirm">
    <div class="rn_Padding">
            #rn:msg:SUBMITTING_QUEST_REFERENCE_FOLLOW_LBL# 
            <p />
            <b>
                <rn:condition url_parameter_check="i_id == null">
                    #rn:url_param_value:refno#
                <rn:condition_else/>
                    <a href="/app/#rn:config:CP_INCIDENT_RESPONSE_URL#/i_id/#rn:url_param_value:i_id#">#<rn:field name="Incident.ReferenceNumber" /></a>
                </rn:condition>
            </b>
        </p>
        <p>
            #rn:msg:SUPPORT_TEAM_SOON_MSG#
        </p>
        <p />
        <div id="rn_moreHelpDiv" class="moreHelpClass" >
		<input type="button" id="rn_HelpBtn" class="rn_primaryBtn" name="rn_moreHelpBtn" value="#rn:msg:CUSTOM_MSG_NEED_HELP_BTN#" onclick="needMoreHelp();">
	</div>
	<div id="rn_shoppingBtnDiv" class="shoppingBtnClass" >
		<input type="button" id="rn_shoppingBtn" class="rn_primaryBtn" name="rn_contShopBtn" value="#rn:msg:CUSTOM_MSG_CONT_SHOPPING_BTN#" onclick="continueShopping();">
	</div>
    </div>
</div>
<? 
	/* Set cookies for values passed on URL */
	$storeID_cookie_name = "StoreIDCookie";
	$catalogID_cookie_name = "CatalogIDCookie";
	$langCode_cookie_name = "LangIDCookie";
	
	$passedStoreId = getUrlParm('sId');
	if($passedStoreId===null) { 
		if($_COOKIE[$storeID_cookie_name]===null)
			$passedStoreId = 10051; 
		else
			$passedStoreId = $_COOKIE[$catalogID_cookie_name];
	}
	$passedCatalogId = getUrlParm('cId');
	if($passedCatalogId===null) { 
		if($_COOKIE[$catalogID_cookie_name]===null)
			$passedCatalogId = 10901; 
		else
			$passedCatalogId = $_COOKIE[$catalogID_cookie_name]; 
	}
	$passedLangCode = getUrlParm('lId');
	if($passedLangCode===null) { 
		if($_COOKIE[$langID_cookie_name]===null)
			$passedLangCode = -1; 
		else
			$passedLangCode = $_COOKIE[$langID_cookie_name]; 
	}
	$passedLangCode = -1; 
?>

<script type="text/javascript">
function needMoreHelp() {
	//alert("redirect to Help page");
	/* https://www.abercrombie.com/webapp/wcs/stores/servlet/Help?catalogId=10901&langId=116&pageName=help&storeId=10051 $_COOKIE[$l_cookie_name] */
	var helpURL = "#rn:msg:CUSTOM_MSG_MORE_HELP_URL#&catalogId=<?= $passedCatalogId ?>&langId=<?= $passedLangCode ?>&storeId=<?= $passedStoreId ?>";
	//alert(bagURL);
	window.top.location = helpURL;
	//window.open(bagURL);
	return true;
}
function continueShopping() {
	//alert("redirect to continue shopping");
	/* https://www.abercrombie.com/shop/us */
	var shoppingURL = "#rn:msg:CUSTOM_MSG_CONTINUE_SHOPPING_URL#?&catalogId=<?= $passedCatalogId ?>&langId=<?= $passedLangCode ?>&storeId=<?= $passedStoreId ?>";
	window.top.location = shoppingURL;
}

</script>
