<!DOCTYPE html>
<html lang="#rn:language_code#">
<rn:meta javascript_module="standard"/>
<head>
    <meta charset="utf-8"/>
    <title><rn:page_title/></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!--[if lt IE 9]><script src="/euf/core/static/html5.js"></script><![endif]-->
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />
    <rn:theme path="/euf/assets/themes/standard-ext" css="site.css,,bissell2022.css" />
    <link rel="stylesheet" type="text/css" href="/euf/assets/fonts/fonts.css" />
    <rn:head_content/>
    <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png"/>
    <rn:widget path="utils/ClickjackPrevention"/>
    <rn:widget path="utils/AdvancedSecurityHeaders"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="yui-skin-sam yui3-skin-sam">

<br/>
<div id="rn_Container" >
    <div id="rn_Header" role="banner">
        <rn:widget path="utils/CapabilityDetector"/>
        <div id="rn_Logo"></a></div>
    </div>
    <div id="rn_Body">
        <div id="rn_MainColumn" role="main">
            <a id="rn_MainContent"></a>
            <rn:page_content/>
        </div>
    </div>
    <div id="rn_Footer" role="contentinfo">
        
    </div>
</div>
</body>
<footer class="rn_Footer">
    <div class="rn_Container">
		<rn:widget path="custom/navigation/IconList" icon_info="#rn:config:CUSTOM_CFG_ICON_LIST_STRING#" max_icons_in_row="7"/> 
	
	</div>
</footer>
</html>
