<?
   $CI = get_instance();
   $product = (\RightNow\Utils\Url::getParameter('product')? \RightNow\Utils\Url::getParameter('product') : null);
   //LogMessage($product);
   $issue =  (\RightNow\Utils\Url::getParameter('issue')? \RightNow\Utils\Url::getParameter('issue') : null);
   //LogMessage($issue);
   $policy = (\RightNow\Utils\Url::getParameter('policy')? \RightNow\Utils\Url::getParameter('policy'): "TroubleshootingRouter" );
   $referrer = "US";
   //LogMessage($referrer);
  
?>
<style>
#opa_interview_div {
    margin-top: -50px;
}
</style>


<rn:meta title="Troubleshooting OIA Interview | Support" template="standard_OIA.php" clickstream="home" login_required="false" />

<div id="rn_googleSEO">
	<h1><?=$policy . $product?> | Support</h1>
</div>




<!-- Prevents page from opening at the bottom with the cookie popup -->
	<div style="visibility: hidden;">
	 <input type="text" id="fname" name="autofocus" autofocus>
	</div>
<div class="rn_PageContent">
	 
	
<? if($referrer == "US") { ?>
	
<?if($product && $issue){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'product':'#rn:php:$product#', 'issue':'#rn:php:$issue#', 'country':'#rn:php:$referrer#'}" />

<?}else if($issue){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'issue':'#rn:php:$issue#', 'country':'#rn:php:$referrer#'}" />

<?}else if($product){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'product':'#rn:php:$product#', 'country':'#rn:php:$referrer#'}" />

<?}else {?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'country':'#rn:php:$referrer#'}" />

<?}?>

<? }else{?>


<?if($product && $issue){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'product':'#rn:php:$product#', 'issue':'#rn:php:$issue#' , 'country':'#rn:php:$referrer#'}" />

<?}else if($product){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'product':'#rn:php:$product#', 'country':'#rn:php:$referrer#'}" />

<?}else if($issue){?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'issue':'#rn:php:$issue#', 'country':'#rn:php:$referrer#'}" />

<?}else {?>

<rn:widget path="custom/OPA/OPAWidget" policy_model="#rn:php:$policy#" seed_data="{'country':'#rn:php:$referrer#'}" />

<?}?>

<?}?>


</div>
<style>
.floating-label {
  position: relative;
}

.floating-label label {
position: absolute;
left: 12px;
top: 14px;

font-size: 16px;
opacity: .6;

pointer-events: none;
transition: all .2s ease-in-out;
}

.floating-label.is-floating label {
  top: 5px;
  
  font-size: 12px;
  font-weight: bold;
  opacity: 1;
}

.floating-label.has-focus label {
  color: royalblue;
  opacity: 1 !important;
}

.floating-label input {
padding: 20px 10px 4px 12px;
width: 250px;
}
.googleHide{
	visibility: hidden;
}

</style>
<script>

$(document).ready(function(){
	$('#rn_googleSEO').addClass('googleHide');
	
});

		if (window.location.href.indexOf('troubleshooting') > 0){
			document.title = document.title.replace('| Product', '');
		} else {
			document.title = document.title.replace('BISSELL Support | ', '');
		}
	document.querySelector('meta[name="description"]').setAttribute("content", document.title.split(' |').join(''));
	document.querySelector('meta[property="og:title"]').setAttribute("content", document.title);
	document.querySelector('meta[property="og:description"]').setAttribute("content", document.title.split(' |').join(''));
    


window.addEventListener('DOMContentLoaded', function(e) {
  var container = document.getElementsByClassName('rn_SourceSearchField');
  var input = document.querySelector('input');
  
  // Make the label float when the field is focused
  input.addEventListener('focus', function(e) {
    container.classList.add('has-focus');
    container.classList.add('is-floating');
  });
});
</script>
