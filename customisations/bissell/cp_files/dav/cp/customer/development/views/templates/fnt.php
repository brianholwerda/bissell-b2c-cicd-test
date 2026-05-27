<!DOCTYPE html>
<html lang="#rn:language_code#">
<head>
    <meta charset="utf-8"/>
    <title>BISSELL® | Customer Support | <rn:page_title/></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!--[if lt IE 9]><script src="/euf/core/static/html5.js"></script><![endif]-->
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />
    <rn:theme path="/euf/assets/themes/standard-ext" css="site.css,chatPopup.css"/>
    <rn:head_content/>
    <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png"/>
    <rn:widget path="utils/ClickjackPrevention"/>
    <rn:widget path="utils/AdvancedSecurityHeaders"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>	
	<link rel="stylesheet" type="text/css" href="/euf/assets/javascript/chosen/chosen.min.css">
	<script type="text/javascript" src="/euf/assets/javascript/chosen/chosen.jquery.js"></script>
</head>
	<body class="yui-skin-sam yui3-skin-sam" itemscope itemtype="http://schema.org/WebPage">
		<a href="#rn_MainContent" class="rn_SkipNav rn_ScreenReaderOnly">#rn:msg:SKIP_NAVIGATION_CMD#</a>

		<header class="rn_header">
			<rn:widget path="utils/CapabilityDetector"/>
		</header>

		<div class="rn_Body">
			<div class="rn_Hero <rn:condition show_on_pages="home">rn_HeroHome<rn:condition_else/>rn_HeroNoImage</rn:condition>">
				<div class="rn_HeroInner">
					<div class="rn_SearchControls">
						<h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
						<form method="get" action="/app/results">
							<rn:container source_id="KFSearch">
								<div class="rn_SearchInput">
									<rn:widget path="searchsource/SourceSearchField" initial_focus="true"/>
								</div>
								<rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list"/>
							</rn:container>
						</form>
					</div>
				</div>
			</div>

			<div class="rn_MainColumn" role="main">
				<a id="rn_MainContent"></a>
				<rn:page_content/>
			</div>
		</div>
	</body>
</html>