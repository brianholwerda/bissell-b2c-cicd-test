<?
//print('host=' . $_SERVER['HTTP_HOST']);
//print('server=' . $_SERVER['HTTP_REFERER']);
//print(strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']));
if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) == false){
    //print('in here');
    try{
        //print($_SERVER['HTTP_REFERER']);
        if(strpos($_SERVER['HTTP_REFERER'], 'canada')!==false  ){
            //print('is Canada');
            $cookie_name = "RN_REFERER";
            $cookie_value = "CANADA";
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
        }else{
            //print('is not canada');
            $cookie_name = "RN_REFERER";
            $cookie_value = "US";
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
            
        }
    }catch(Exception $e){
        
    }
}

$siteParam = getUrlParm("site");
if(isset($siteParam)){
	if($siteParam == 'canada') {
		//print('is Canada');
	    $cookie_name = "RN_REFERER";
	    $cookie_value = "CANADA";
	    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");	
	} else {
		//print('is not canada');
	    $cookie_name = "RN_REFERER";
	    $cookie_value = "US";
	    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
	}
}
if($_COOKIE['RN_REFERER'] == "CANADA" || (strpos($_SERVER['HTTP_REFERER'], 'canada')!==false  ) || $siteParam == 'canada'){
    //print(' Canada set');
    $environment = "https://canada.bissell.com";
    $com_environment = "https://canada.bissell.com";
    $referrer = 'CA';
    $cdn150 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/Canada%20Product%20Pages/150X150";
    $cdn250 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/Canada%20Product%20Pages/250X250";  
    $stateorprovinceLbl = "Province";    
    $country_cd = 2;  
    $site='canada';
}else{
    //print(' US set');
    $environment = "https://www.bissell.com";
    $com_environment = "https://www.bissell.com";
    $referrer = 'US';
    $cdn150 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/US%20Product%20Pages/150X150";
    $cdn250 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/US%20Product%20Pages/250X250";
    $stateorprovinceLbl = "State";
    $country_cd = 1;
    $site='us';
}
?>

  <!DOCTYPE html>
  <html lang="#rn:language_code#">
  <rn:meta clickstream="chat_landing" javascript_module="standard" />

  <head>
    <meta charset="utf-8" />
    <title><rn:page_title/></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PQL5NBT');</script>
<!-- End Google Tag Manager -->





    <!--[if lt IE 9]><script src="/euf/core/static/html5.js"></script><![endif]-->
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />
		<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/site.css">
		<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/site_OIA.css">
		<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/global.css">
		<link href="https://fonts.googleapis.com/css?family=Montserrat:400,600,700,900&amp;display=swap" rel="stylesheet">
		<link rel="stylesheet" href="//www.bissell.com/on/demandware.static/Sites-bissell-Site/-/en_US/v1579042226702/css/sprites.css">
		<link href="//ui.powerreviews.com/tag-builds/10067/4.0/styles.css" type="text/css" rel="stylesheet">
		<script defer="" type="text/javascript" src="//www.bissell.com/on/demandware.static/Sites-bissell-Site/-/en_US/v1609855894762/lib/jquery.zoom.min.js"></script>
	
	<!--<rn:theme path="/euf/assets/themes/standard" css="site.css" />-->
	<!--<rn:theme path="/euf/assets/themes/standard" css="global.css" />-->
	<!--<link href="//staging.www.bissell.com/on/demandware.static/Sites-bissell-Site/-/en_US/v1578095807761/css/global.css" rel="stylesheet">-->
	<rn:head_content/>
    <link rel="icon" href="/euf/assets/images/favicon.png" type="image/png" />
    <rn:widget path="utils/ClickjackPrevention" />
    <rn:widget path="utils/AdvancedSecurityHeaders" />
    <?/*<rn:widget path="custom/meta/AriaHeadContent" referring_url="#rn:php:$environment#" />*/?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/euf/assets/javascript/chosen/chosen.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script type="text/javascript" src="/euf/assets/javascript/chosen/chosen.jquery.js"></script>
    <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
    <meta name="google-site-verification" content="ZbfQGhaHfkEmiwdPZur_gciobHqSHwgpj3vo2qZOgTk" />
	<!-- OneTrust Cookies Consent Notice start for support.bissell.com -->

	<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-domain-script="ae48f0ac-b56b-429b-803b-b2d30cca9491" ></script>
	<script type="text/javascript">
	function OptanonWrapper() { }
	</script>
	<!-- OneTrust Cookies Consent Notice end for support.bissell.com -->
	
	<!--Sprinklr-->

	<?if($environment == "//www.canada.bissell.com"){?>
		<script> 
			var Cookie = {
				set: function(key, value) {
					var domain = "bissell.com";
					document.cookie = key + "=" + value + "; path=/; domain=" + domain;
				},
				get: function(key) {
					var key2 = key + "=";
					var ca = document.cookie.split(';');
					for (var i = 0; i < ca.length; i++) {
						var c = ca[i];
						while (c.charAt(0) == ' ') {
							c = c.substring(1, c.length);
						}
						if (c.indexOf(key2) == 0) return c.substring(key2.length, c.length);
					}
					return null;
				},
				erase: function(key) {
					Cookie.set(key, '', -1);
				}
			};
			var myStorage = {
				getItem: function(key){
					return Cookie.get(key);
				},
				setItem: function(key, value) {
					Cookie.set(key,value);
				},
				removeItem: function(key){
					Cookie.erase(key);
				},
			};
			
			window.sprChatSettings = window.sprChatSettings || {}; 
			window.sprChatSettings = { 
				appId: "5fac3f2d2115df5952a2e1e9_app_622222",
				sessionStorage: myStorage,
				clientContext: {
					"_c_5f986f5d6c9986069637c997": ["CA"],
					"_c_5f986fca6c9986069637da38": ["EN"],
				},
			};
		</script> 
		<script> 
			(function(){var w=window;var spr=w.sprChat;if(typeof spr==="function"){spr('update',w.sprChatSettings)}else{var d=document;var c=function(){c.m(arguments)};c.q=[];c.m=function(args){c.q.push(args)};w.sprChat=c;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=!0;s.src='https://prod-live-chat.sprinklr.com/api/livechat/handshake/widget/'+w.sprChatSettings.appId;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x)};if(d.attachEvent?d.readyState==="complete":d.readyState!=="loading"){l()}else{d.addEventListener('DOMContentLoaded',l)}}})() 
		</script>

    <?}else{?>
		<script> 
			var Cookie = {
				set: function(key, value) {
					var domain = "bissell.com";
					document.cookie = key + "=" + value + "; path=/; domain=" + domain;
				},
				get: function(key) {
					var key2 = key + "=";
					var ca = document.cookie.split(';');
					for (var i = 0; i < ca.length; i++) {
						var c = ca[i];
						while (c.charAt(0) == ' ') {
							c = c.substring(1, c.length);
						}
						if (c.indexOf(key2) == 0) return c.substring(key2.length, c.length);
					}
					return null;
				},
				erase: function(key) {
					Cookie.set(key, '', -1);
				}
			};
			var myStorage = {
				getItem: function(key){
					return Cookie.get(key);
				},
				setItem: function(key, value) {
					Cookie.set(key,value);
				},
				removeItem: function(key){
					Cookie.erase(key);
				},
			};
			
			window.sprChatSettings = window.sprChatSettings || {}; 
			window.sprChatSettings = { 
				appId: "5fac3f2d2115df5952a2e1e9_app_622222",
				sessionStorage: myStorage,
				clientContext: {
					"_c_5f986f5d6c9986069637c997": ["US"],
					"_c_5f986fca6c9986069637da38": ["EN"],
				},
			};
		</script> 
		<script> 
			(function(){var w=window;var spr=w.sprChat;if(typeof spr==="function"){spr('update',w.sprChatSettings)}else{var d=document;var c=function(){c.m(arguments)};c.q=[];c.m=function(args){c.q.push(args)};w.sprChat=c;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=!0;s.src='https://prod-live-chat.sprinklr.com/api/livechat/handshake/widget/'+w.sprChatSettings.appId;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x)};if(d.attachEvent?d.readyState==="complete":d.readyState!=="loading"){l()}else{d.addEventListener('DOMContentLoaded',l)}}})() 
		</script>
    <?}?>


	<!--End Sprinklr-->
	
	<script type="text/javascript" src="https://www.bissell.com/BHISites/_Themes/BissellCom2013/_Design/js/vendor/jquery.fancybox.js"></script>
	<!--<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard/BissellFonts/fonts.css">-->
	
	<!--<script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>-->
	<!--<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>-->
	<!--<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>-->
	<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>-->
	<style>
		/*div.dropdown-menu.dropdown-country-selector{
			display: none;
		}
		div.dropdown.country-selector:hover div.dropdown-menu.dropdown-country-selector{
			display: block;
		}
		div.dropdown-menu{
			display: none;
		}
		li.dropdown:hover div.dropdown-menu{
			display: block;
		}*/
	</style>
  </head>

  <body  class="yui-skin-sam yui3-skin-sam" itemscope itemtype="http://schema.org/WebPage" >
	 <!-- Google Tag Manager (noscript) -->
	<noscript><iframe src=https://www.googletagmanager.com/ns.html?id=GTM-PQL5NBT
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<a href="#rn_MainContent" class="rn_SkipNav rn_ScreenReaderOnly">#rn:msg:SKIP_NAVIGATION_CMD#</a>

	<header class="rn_header">
	    <rn:widget path="utils/CapabilityDetector" />
		<nav>
		<rn:widget path="custom/navigation/AriaHeader" referring_url="#rn:php:$environment#" referrer="#rn:php:$referrer#" com_environment="#rn:php:$environment#" />
		</nav> 
	</header>

    <div class="rn_Body">
      <rn:condition show_on_pages="home">
        <div class="rn_HeroText" id="rn_MainContent" tabindex="-1">
          <div class="rn_HeroInner">
            <div class="rn_HeroCopy">
              <h1>#rn:msg:CUSTOM_MSG_THINGS_GETTING_MESSY#</h1>
              <span class="rn_subHeader">#rn:msg:WERE_HERE_TO_HELP_LBL#</span>
            </div>
          </div>
        </div>
      </rn:condition>
      <div class="rn_Hero <rn:condition show_on_pages=" home ">rn_HeroHome<rn:condition_else/>rn_HeroNoImage</rn:condition>">
        <div class="rn_HeroInner">
          <? /* <div class="rn_HeroCopy">
<h1>#rn:msg:WERE_HERE_TO_HELP_LBL#</h1>
</div> */ ?>
            <div class="rn_SearchControls">
              <?/*h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>*/?>
              <span id="rn_ADALabel" class="rn_Hidden"> Search Support</span>
              <form method="get" action="/app/results">
                <rn:container source_id="KFSearch">
                  <div class="rn_SearchInput">
                    <rn:widget path="searchsource/SourceSearchField" initial_focus="true" label_placeholder="#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#" />
                  </div>
                  <rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list" />
                </rn:container>
              </form>
            </div>
        </div>
      </div>

      <div class="rn_MainColumn" role="main" >
	    <? /* <rn:condition hide_on_pages=" home ">
		   <div class="rn_HomeBackControl">
		   <a href="/app/home" class="titleText">Home</a> | <a href="javascript:history.back()" class="titleText">Back</a>
		   </div>
		</rn:condition>  */ ?>
        <rn:condition hide_on_pages="home,products/detail">
          <rn:widget path="custom/navigation/Breadcrumbs" />
        </rn:condition>
        
        <rn:page_content/>
      </div>
      <rn:condition hide_on_pages="contact">
        <div id="rn_ContactUsSection" class="rn_Container">
            <rn:widget path="custom/navigation/CallToActionBox" class_name="rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="#rn:msg:CUSTOM_MSG_STILL_NEED_HELP#" subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SUBTEXT#" button_1_text="#rn:msg:CONTACT_US_LBL#"
            button_1_url="/app/contact" button_2_text="" button_2_url="" />
        </div>
      </rn:condition>
      <?if($environment == "//www.staging.canada.bissell.com"){?>
        <link rel="stylesheet" type="text/css" href="https://bissell-ext.custhelp.com/euf/assets/themes/standard/canada.css">
      <?}?>
    </div>

    <footer class="rn_Footer">
      <div class="rn_Container_ft">
        <div class="rn_Misc" style="display:none;">
          <rn:widget path="utils/PageSetSelector" />
          <rn:widget path="utils/OracleLogo" />
        </div>
        <rn:widget path="custom/navigation/AriaFooter" referring_url="#rn:php:$environment#" referrer="#rn:php:$referrer#" com_environment="#rn:php:$environment#" />
		  
      </div>
    </footer>
	<div class="modal-background" style="display: none;"></div>
  </body>

  </html>
<style>
.toggleClass{
  display: block;
}
</style>
	  

	<?/*<script type="text/javascript" src="/euf/assets/javascript/site_test.js"></script>*/?>
 <script>
	  
$(document).ready(function(){
	$(".rn_SubmitButton").prepend(' <i class="fa fa-search" aria-hidden="true"></i>');
});
$( "input[id^='rn_SourceSearchField_']" ).click(function() {
	$( '#rn_ADALabel' ).toggleClass( "rn_Hidden" );
});

$('a.menu-link.dropdown-toggle').on('click', function(){
	event.preventDefault();		
	$('.dropdown-menu').toggleClass("toggleClass");
});


	  //document.getElementById('rn_focus').focus();
	 /* window.onload = function() {
		  document.getElementById("rn_MainContent").focus();
	  };*/
	  
	  $('input[id$="_SearchInput"]').attr('autocomplete','Search');

	/* FIXES A BUG WHERE THE MODALS FOR THE BISSELL HEADER WON'T POPUP  */
    $(document).ready(function(){
		/*$('.fancybox').fancybox({
			helpers: {
				title: null
			}
		});
		$('.fancybox').click(function (e) { 
			e.preventDefault();
			//console.log('Fancy click');
			//console.log($(this)[0].dataset.target);
			$.fancybox( $("#" + $(this)[0].dataset.target) );
		});*/
		
		$(function () {
			$("header .nav-btn").click(function () {
			    $("header .nav").toggleClass("open");
			    $("header .nav-btn").toggleClass("open");
			    $(".top-nav").css("top", (($('.main-nav').outerHeight()) + ($('.main-nav').offset().top)));
			});
		 
		    $('.global-icons--mobile-toggle').click(function (e) {
		        e.preventDefault();
				//console.log('clicked toggler');
		        $(this).closest('.global-icons--header').toggleClass('global-icons--show');
		    });
		    
		    $(".modelNum").click(function (e) {
			 
			    //console.log('in model-number');
			 
			});
			
			$(".serialNum").click(function (e) {
			 
			    //console.log('in serial-number');
			 
			});   
			
		});
	});
  </script>