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
    // $environment = "https://canada.bissell.com";
    // $com_environment = "https://canada.bissell.com";
	$environment = "https://www.bissell.com/en-ca";
    $com_environment = "https://www.bissell.com/en-ca";
    $referrer = 'CA';
    $cdn150 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/Canada%20Product%20Pages/150X150";
    $cdn250 = "https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/Canada%20Product%20Pages/250X250";  
    $stateorprovinceLbl = "Province";    
    $country_cd = 2;  
    $site='canada';
}else{
    //print(' US set');
    $environment = "https://www.bissell.com/en-us";
    $com_environment = "https://www.bissell.com/en-us";
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
    	
    <rn:widget path="custom/meta/AriaHeadContent" referring_url="#rn:php:$environment#" />
	
	<rn:theme path="/euf/assets/themes/standard-ext" css="site.css,bissell2022.css,OracleDigitalAssistantEngagementEngine.css" />
	<?if($environment == "https://www.canada.bissell.com"){?>
		<link rel="stylesheet" type="text/css" href="https://bissell.custhelp.com/euf/assets/themes/standard-ext/canada.css">

	<?}?>
	<rn:condition show_on_pages="answers/detail,productcheck,repair-submission-form,repair-submission-form_confirm,oow-submission-form,oow-submission-form_confirm,replace-submission-form,replace-submission-form_confirm,repairinstructions,oowpopcheck,replaceinstructions">
		<link rel="stylesheet" type="text/css" href="/euf/assets/themes/standard-ext/troubleshooting.css">
	</rn:condition>

	<rn:head_content/>
        
  	<script type="text/javascript">
	//function OptanonWrapper() { }
	</script>
	
	<!-- AB Tasty Synchronous Tag -->
	<script type="text/javascript" src=https://try.abtasty.com/add5de787d380bfea1334e1a2d4eb8d1.js></script>

	<!-- loqate setup code -->
	<script type="text/javascript">(function(n,t,i,r){var u,f;n[i]=n[i]||{},n[i].initial={accountCode:"BISSE11111",host:"BISSE11111.pcapredict.com"},n[i].on=n[i].on||function(){(n[i].onq=n[i].onq||[]).push(arguments)},u=t.createElement("script"),u.async=!0,u.src=r,f=t.getElementsByTagName("script")[0],f.parentNode.insertBefore(u,f)})(window,document,"pca","//BISSE11111.pcapredict.com/js/sensor.js")</script>
	<!-- loqate setup code	 -->
	
  </head>

  <body class="yui-skin-sam yui3-skin-sam" itemscope itemtype="http://schema.org/WebPage">
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PQL5NBT"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
		
	<a href="#rn_MainContent" class="rn_SkipNav rn_ScreenReaderOnly">#rn:msg:SKIP_NAVIGATION_CMD#</a>
	
    <header class="rn_header">
	    <rn:widget path="utils/CapabilityDetector" />
		
		<div id="rn_desktopHeaderDisplay" >
			<rn:widget path="custom/navigation/AriaHeader" referring_url="#rn:php:$environment#" referrer="#rn:php:$referrer#" com_environment="#rn:php:$environment#" /> 
		</div>
		<div id="rn_mobileHeaderDisplay" >
			<rn:widget path="custom/navigation/AriaHeaderMobile" referring_url="#rn:php:$environment#" referrer="#rn:php:$referrer#" com_environment="#rn:php:$environment#" />
		</div>
    </header>

    <div class="rn_Body">
	    <div class="rn_HeroInner">
        	<rn:condition hide_on_pages="">
        	<div class="rn_LoginStatus">
			    <rn:condition logged_in="false">
			    <? if(isset($_COOKIE['location'])) { ?>
			    	<style>
				    	.rn_HeroInner {
							/* margin-top: -56px; */
						}
				    </style>
			        <rn:widget path="login/AccountDropdown" subpages="#rn:msg:ACCOUNT_OVERVIEW_LBL# > account/overview"
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
			        />
			    <? } ?> 
			    <rn:condition_else/>
					<style>
				    	.rn_HeroInner {
							/* margin-top: -56px; */
						}
				    </style>
					<rn:widget path="login/AccountDropdown" subpages="#rn:msg:CUSTOM_MSG_HOT_TOPICS_LBL# > internal/home"/>
			    </rn:condition>
			</div>
        	</rn:condition>
            
        </div>
		<rn:widget path="custom/display/SpecialAlert" />
        <? /* <div class="rn_Hero <rn:condition show_on_pages="returns">rn_HeroReturnsImage<rn:condition_else/>rn_HeroDefaultImage</rn:condition>">*/ ?>
        <rn:condition show_on_pages="home">
        <div class="rn_Hero <rn:condition show_on_pages="home">rn_HeroDefaultImage</rn:condition>">
	        <div id="rn_HeroMobileText" class="">
		        <div class="rn_HeroMobileHead">
				<h3 class="rn_HeroMobileSectionHead">#rn:msg:CUSTOM_MSG_MOBILE_SUPPORT_HDR#</h3>
				<span class="rn_HeroMobileSectionHead">#rn:msg:CUSTOM_MSG_MOBILE_SUPPORT_SUBHDR#</span>
				</div>
	        </div>
    	</div>
        </rn:condition>

      <div class="rn_MainColumn" role="main">

        <rn:condition hide_on_pages="home,products/detail,wdmr,wdmr_confirm">
        <div class="rn_Hero rn_Product rn_OtherSearchPages rn_PageContent">
		    <div class="rn_HeroInner">
			    
			    <nav aria-label="breadcrumbs">
		        <div class="rn_PageContent rn_Breadcrumbs">
			    
		          <rn:widget path="custom/navigation/Breadcrumbs" />
		        
		        </div>
		        </nav>
			    
		        <div id="rn_SearchMagnifyIconHideShow" class="rn_SearchMagnifyIconHideShow rn_OtherSearchMagnifyControlsLink">
			        <a class="rn_SearchControlsMagnifyIconLink" href="javascript:;" ><i class="fa fa-search" aria-hidden="true"></i></a>
		        </div>
				<? /*   We are not currently showing search via the text link on interior pages or the home page - only product details, so we don't need this now.
					<div id="rn_SearchLinkHideShow" class="rn_SearchControlsLinkHideShow rn_ProductSearchControlsLink">
					#rn:msg:CUSTOM_MSG_FASTER_HELP# <a class="rn_SearchControlsTextLink link-tertiary" href="javascript:;">#rn:msg:CUSTOM_MSG_SEARCH_MODEL_LBL#</a>	
				</div> */ ?>
		        <div id="rn_SearchControlsOtherPages" class="rn_SearchControls rn_SearchControlsOtherPages rn_SearchControlsProductPages">
					<div class="rn_SearchControls">
		              <?/*h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>*/?>
		              <span id="rn_ADALabel" class="rn_Hidden"> #rn:msg:CUSTOM_MSG_SEARCH_SUPPORT#</span>
		              <form method="get" action="/app/answers/list">
		                <rn:container source_id="KFSearch">
		                  <div class="rn_SearchInput">
		                    <rn:widget path="searchsource/SourceSearchField" initial_focus="true" label_placeholder="#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#" />
		                  </div>
		                  <rn:widget path="searchsource/SourceSearchButton" search_results_url="/app/answers/list" />
		                </rn:container>
		              </form>
		            </div>
		            <? /* We are not currently showing the where is this link on interior pages only the home page, so we don't need this here now.
			        <div class="rn_AlignLeft rn_WhereIsModel">
					<a href='#' class="open-dialog">#rn:msg:CUSTOM_MSG_WHERE_IS_THIS#</a>
					</div>	 */ ?>        
				</div>
		    </div>
		</div>
        </rn:condition>
        
        <rn:page_content/>
      </div>
      <? /* <rn:condition hide_on_pages="contact">
        <div id="rn_ContactUsSection" class="rn_Container">
            <rn:widget path="custom/navigation/CallToActionBox" class_name="rn_ContactUsTemplateSection rn_CallToActionBox100" image_url="" image_alt="" headline="#rn:msg:CUSTOM_MSG_STILL_NEED_HELP#" subtext="#rn:msg:CUSTOM_MSG_CONTACTUS_SUBTEXT#" button_1_text="#rn:msg:CONTACT_US_LBL#"
            button_1_url="/app/contact" button_2_text="" button_2_url="" />
        </div>
      </rn:condition> */ ?>
	<rn:condition hide_on_pages="contact,contact2022">
		<rn:widget path="custom/navigation/ContactUsFooter" site_referrer="#rn:php:$referrer#" />
	</rn:condition>	

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
	
	<rn:condition hide_on_pages="home,products/detail,ask,ask_confirm,crosswave-cordless-safety-recall,crosswave-cordless-safety-recall_confirm,cordless-safety-recall,cordless-safety-recall_confirm,multireach-safety-recall,multireach-safety-recall_confirm,steamshot-safety-recall,steamshot-safety-recall_confirm,wdmr,wdmr_confirm,product-issue-submission-form,product-issue-submission-form_confirm,productcheck,repair-submission-form,repair-submission-form_confirm,oow-submission-form,oow-submission-form_confirm,replace-submission-form,replace-submission-form_confirm">
	<div class="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" aria-describedby="dialog-description" aria-hidden="true">
	<button  class="close-button" type="button" aria-label="Close Navigation" class="close-dialog"> <img alt=" " id="rn_modalClose" src="/euf/assets/themes/standard/images/icons/bissell/modal_close.png" ></button>
	#rn:msg:CUSTOM_MSG_FIND_MODEL_HTML#
	</div>
	</rn:condition>
  </body>


<rn:condition hide_on_pages="home,products/detail,ask,ask_confirm,crosswave-cordless-safety-recall,crosswave-cordless-safety-recall_confirm,cordless-safety-recall,cordless-safety-recall_confirm,multireach-safety-recall,multireach-safety-recall_confirm,steamshot-safety-recall,steamshot-safety-recall_confirm,wdmr,wdmr_confirm,product-issue-submission-form,product-issue-submission-form_confirm,productcheck,repair-submission-form,repair-submission-form_confirm,oow-submission-form,oow-submission-form_confirm,replace-submission-form,replace-submission-form_confirm">
<div class="dialog-overlay" tabindex="-1"></div>



<script type="text/javascript">
$(document).ready(function(){
	$("#rn_SearchControlsOtherPages .rn_SubmitButton").prepend(' <i class="fa fa-search" aria-hidden="true"></i>');
	$('#rn_SearchControlsOtherPages input').val();
	//$('#rn_SearchControlsProductPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_MODEL_PLACEHOLDER#');
	
	$('.rn_SearchControlsMagnifyIconLink').on('click', function(event) {
		event.preventDefault();
	    //alert("in here rn_SearchControlsMagnifyIconLink");
	    $("#rn_SearchControlsOtherPages").attr("style", "display:inline-block");
		$('#rn_SearchControlsOtherPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#');
		$(".rn_SearchControlsMagnifyIconLink").attr("style", "display:none");
		$('.rn_SourceSearchField input').focus();
	    
	    $('.rn_SourceSearchField input').focus();
	});
	
	$('.rn_SearchControlsTextLink').on('click', function(event) {
		event.preventDefault();
	    //alert("in here rn_SearchLinkHideShow");
	    $("#rn_SearchControlsOtherPages").attr("style", "display:inline-block");
		$('#rn_SearchControlsOtherPages input').attr('placeholder', '#rn:msg:CUSTOM_MSG_SEARCH_PLACEHOLDER#');
		$("#rn_SearchLinkHideShow").attr("style", "display:none");
		//$("#rn_SearchControlsProductPages").attr("style", "padding-top:16px; padding-bottom:32px;");
		//$("#rn_SearchControlsProductPages").removeClass("rn_Hidden");
		$('.rn_SourceSearchField input').focus();
	    
	    $('.rn_SourceSearchField input').focus();
	});
});
		var navDialogEl = document.querySelector('.dialog');
		var dialogOverlay = document.querySelector('.dialog-overlay');
		
		var myDialog = new Dialog(navDialogEl, dialogOverlay);
		myDialog.addEventListeners('.open-dialog', '.close-dialog');
</script>
</rn:condition>

<script>

	$( "input[id^='rn_SourceSearchField_']" ).click(function() {
		$( '#rn_ADALabel' ).toggleClass( "rn_Hidden" );
	});
	
	$('input[id$="_SearchInput"]').attr('autocomplete','Search'); 
	  
	//window.onload = function() {
	  //document.getElementById("rn_MainContent").focus();
	//};
	  
	  

	/* FIXES A BUG WHERE THE MODALS FOR THE BISSELL HEADER WON'T POPUP  */
    $(document).ready(function(){
		/* $('.fancybox').fancybox({
			helpers: {
				title: null
			}
		});
		$('.fancybox').click(function (e) { 
			e.preventDefault();
			//console.log('Fancy click');
			//console.log($(this)[0].dataset.target);
			$.fancybox( $("#" + $(this)[0].dataset.target) );
		}); */
		
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
		});
	});

	//Removes the Warning text from the search banner
	var observer = new MutationObserver(function(mutations) {
	    mutations.forEach(function(mutation) {
	        //console.log(mutation)
	        if (mutation.addedNodes && mutation.addedNodes.length > 0) {
	            // element added to DOM
	            var hasClass = [].some.call(mutation.addedNodes, function(el) {
		            if(el.classList){
			            //console.log(el.classList);
		                return el.classList.contains('rn_WarningAlert');
		            } else {
			            return false;
		            }
	            });
	            //console.log(hasClass);
	            if (hasClass) {
	                // element has class `MyClass`
	                c//onsole.log('element ".rn_Alert.rn_WarningAlert.rn_BannerAlert" added');
	                $(".rn_Alert.rn_WarningAlert.rn_BannerAlert span").text($(".rn_Alert.rn_WarningAlert.rn_BannerAlert span").text().replace("Warning: ", ""));
	            }
	        }
	    });
	});
	
	var config = {
	    attributes: true,
	    childList: true,
	    characterData: true
	};
	
	observer.observe(document.body, config);
	
	'use strict';

	// Handle Sprinklr Chat icon visibility according to chat availability
	/*
	const chatLabelOpen = $('.chat-trigger-label').text();
	const chatLabelClose = 'EXIT';
	
	$('#spr-chat-trigger').on('click', function () {
	    if ($(this).hasClass('spr-chat-open')) {
	        window.sprChat('close');
	        $('.chat-trigger-label').text(chatLabelOpen);
	        $(this).toggleClass('spr-chat-open');
	    } else {
	        window.sprChat('open');
	        // Toggle the trigger label if not on mobile
	        if (parseInt($('.spr-chat__box').css('width'), 10) < $(window).width()) {
	            $('.chat-trigger-label').text(chatLabelClose);
	            $(this).toggleClass('spr-chat-open');
	        }
	    }
	});
	
	/**
	 * Check for Sprinklr Chat Authorization
	 * @param {string} sprHandshake - API endpoint to check Sprinklr visibility.
	 * @returns {xmlHttp} - Chat available true/false/error
	 *
	function sprChatAvailability(sprHandshake) {
	    var xmlHttp = new XMLHttpRequest();
	    xmlHttp.open('GET', sprHandshake, false);
	    xmlHttp.send(null);
	    return xmlHttp.responseText;
	}
	
	var isChatVisible = sprChatAvailability('https://prod-live-chat.sprinklr.com/api/livechat/handshake/check-visibility/5fac3f2d2115df5952a2e1e9_app_622222');
	if (isChatVisible === 'true') {
	    $('#spr-chat-trigger').show();
	} else {
	    $('#spr-chat-trigger').hide();
	}*/

	
</script>

</html>