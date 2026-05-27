<!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard"/>
	<rn:condition hide_on_pages="utils/login_form">
	​  ​<rn:condition logged_in="false">
	   <?
		// Redirect to ADFS login IF contact is not logged in
		$is_dev = false;

		if (preg_match("/development/i", $_COOKIE['location']))
			$is_dev = true;

		if(!$is_dev)
		{
			$sso_location = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_SSO_REDIRECT);
			LogMessage($sso_location);
			header("location: $sso_location");
		} else {
			$sso_location = "/app/utils/login_form";
			LogMessage($sso_location);
			header("location: $sso_location");
		}
	   ?>
	​  ​</rn:condition>
	</rn:condition>
<head>
    <meta charset="utf-8"/>
    <title><rn:page_title/></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!--[if lt IE 9]><script src="/euf/core/static/html5.js"></script><![endif]-->
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />
    <rn:theme path="/euf/assets/themes/standard-internal" css="site.css" />
    <link rel="stylesheet" type="text/css" href="/euf/assets/fonts/fonts.css" />
    <rn:head_content/>
    <link rel="icon" href="/euf/assets/images/favicon-16x16.png" type="image/png"/>
    <rn:widget path="utils/ClickjackPrevention"/>
    <rn:widget path="utils/AdvancedSecurityHeaders"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
</head>
<body class="yui-skin-sam yui3-skin-sam" itemscope itemtype="http://schema.org/WebPage">
<a href="#rn_MainContent" class="rn_SkipNav rn_ScreenReaderOnly">#rn:msg:SKIP_NAVIGATION_CMD#</a>

<header>
    <rn:widget path="utils/CapabilityDetector"/>
    <nav>
        <div class="rn_NavigationBar">
            <input type="checkbox" id="rn_NavigationMenuButtonToggle" class="rn_ScreenReaderOnly" />
            <label class="rn_NavigationMenuButton" for="rn_NavigationMenuButtonToggle">#rn:msg:MENU_LWR_LBL#</label>
		</div>

        <div class="rn_LoginStatus">
	    <rn:condition logged_in="false">
	        <? /* <rn:widget path="login/AccountDropdown" subpages="#rn:msg:ACCOUNT_OVERVIEW_LBL# > account/overview"
	        sub:input_Contact.Emails.PRIMARY.Address:label_input="#rn:msg:EMAIL_ADDR_LBL#"
	        sub:input_Contact.Emails.PRIMARY.Address:required="true"
	        sub:input_Contact.Emails.PRIMARY.Address:validate_on_blur="false"
	        sub:input_Contact.Login:label_input="#rn:msg:USERNAME_LBL#"
	        sub:input_Contact.Login:required="true"
	        sub:input_Contact.Login:validate_on_blur="false"
	        sub:input_Contact.Name.First:required="true"
	        sub:input_Contact.Name.First:label_input="#rn:msg:FIRST_NAME_LBL#"
	        sub:input_Contact.Name.Last:required="true"
	        sub:input_Contact.Name.Last:label_input="#rn:msg:LAST_NAME_LBL#"
	        sub:input_SocialUser.DisplayName:label_input="#rn:msg:DISPLAY_NAME_LBL#"
	        sub:input_Contact.NewPassword:label_input="#rn:msg:PASSWORD_LBL#"
	        /> */ ?>
	    <rn:condition_else/>
	    	<? /* <rn:widget path="login/AccountDropdown" subpages="#rn:msg:CUSTOM_MSG_HOT_TOPICS# > internal/home, #rn:msg:ACCOUNT_OVERVIEW_LBL# > account/overview, #rn:msg:SUPPORT_HISTORY_LBL# > account/questions/list "/> */ ?>
		    	<rn:widget path="login/AccountDropdown" subpages="#rn:msg:CUSTOM_MSG_HOT_TOPICS_LBL# > internal/home"/>
	    </rn:condition>
		</div>
    </nav>
 </header>

<div class="rn_Hero">
    <div class="rn_HeroInner">
	<div class="rn_HeroCopy">

        <div class="rn_SearchControls">
            <form method="get" action="/app/results">
                <rn:container source_id="KFSearch">
                    <div class="rn_SearchInput">
                        <rn:widget path="searchsource/SourceSearchField" label_placeholder="#rn:msg:FIND_THE_ANSWER_TO_YOUR_QUESTION_CMD#" initial_focus="false"/>
                    </div>
                    <rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list"/>
                </rn:container>
            </form>
        </div>

	<div class="ResourceCenter">
    	<span class="rn_PageTitle">
    		<a href="/app/home" target="_self">#rn:msg:SUPPORT_HOME_TAB_HDG#</a>
    	</span>
    </div>

	</div>
	  	<?/*<div class="rn_Container">
			<rn:widget path="custom/navigation/IconList" icon_info="#rn:config:CUSTOM_CFG_ICON_LIST_STRING#" max_icons_in_row="7"/>
	    </div>*/?>
	</div>
    </div>

<div class="rn_Body">
	<div class="rn_MainColumn" role="main">
        <a id="rn_MainContent"></a>
        <rn:page_content/>

    </div>
</div>
</div>

<footer class="rn_Footer">
    <div class="rn_Container">
		<rn:widget path="custom/navigation/IconList" icon_info="#rn:config:CUSTOM_CFG_ICON_LIST_STRING#" max_icons_in_row="7"/>
	<div class="CopyRight">#rn:msg:CUSTOM_MSG_FOOTER_COPY#</div>
	</div>
</footer>
</body>
</html>
