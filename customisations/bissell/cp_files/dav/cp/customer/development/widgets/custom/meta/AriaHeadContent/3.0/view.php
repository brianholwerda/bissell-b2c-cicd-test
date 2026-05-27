	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<rn:condition show_on_pages="repairinstructions,replaceinstructions,oowpopcheck,repair-submission-form,replace-submission-form,oow-submission-form">
		<!-- prevent specified pages from appearing in search results -->
		<meta name="robots" content="noindex">
	</rn:condition>

	<?php if (substr($_SERVER['REQUEST_URI'], 0, strlen("/app/products/detail")) === "/app/products/detail" && !in_array(getUrlParm('p'),str_getcsv(\RightNow\Utils\Config::getConfig(CUSTOM_CFG_INDEX_PRODUCT_PAGES)))): ?>
		<!-- hides internal products from search results -->
		<meta name="robots" content="noindex">
	<?php endif; ?>
	
	<!-- Ketch Cookie Script -->
	<script>!function(){window.semaphore=window.semaphore||[],window.ketch=function(){window.semaphore.push(arguments)};var e=document.createElement("script");e.type="text/javascript",e.src="https://global.ketchcdn.com/web/v3/config/bissell/bissell_noam/boot.js",e.defer=e.async=!0,document.getElementsByTagName("head")[0].appendChild(e)}();</script>
	<!-- End Ketch Cookie Script -->

	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-PQL5NBT');</script>
	<!-- End Google Tag Manager -->

	<meta name="google-site-verification" content="ZbfQGhaHfkEmiwdPZur_gciobHqSHwgpj3vo2qZOgTk" />
	
	<rn:widget path="utils/ClickjackPrevention" />
    <rn:widget path="utils/AdvancedSecurityHeaders" />	
    <!--[if lt IE 9]><script src="/euf/core/static/html5.js"></script><![endif]-->
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />

	
	<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/ModalCss/dialog.css">
	<link rel="shortcut icon" href="/euf/assets/images/favicon-16x16.png" type="image/x-icon"/>


<!-- EXTERNAL SCRIPTS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script type="text/javascript" src="/euf/assets/javascript/chosen/chosen.jquery.js"></script>
    <script src="https://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/euf/assets/javascript/chosen/chosen.min.css">
	<script src="/euf/assets/themes/standard/ModalJS/dialog.js"></script>
	
	<script type="text/javascript" src="https://cdn.weglot.com/weglot.min.js"></script>
	<script>
		Weglot.initialize({
			api_key: 'wg_133666f3aaf8c74ad5964a86beee23221'
		});
	</script>
