<rn:meta title="#rn:msg:CUSTOM_MSG_WDMR_PAGE_TITLE#" template="standard.php" clickstream="incident_create" />
<div class="rn_PageContent">
<?
	$WDMRtype = getUrlParm("t");	
	//$type = "WDI"; //Report Wrong Damaged Item
	//$type = "RI";//Return Item
	//$type = "MI"; //Missing Item
	$FormDisabled = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_WDMR_DISABLE_FORMS);
?>
<style>
div[role=main] {
    min-height: auto !important;
}
</style>
<div class="rn_PageContent rn_AskQuestion rn_WDMRform rn_Container">

	<? if($FormDisabled == 0) { ?>
    <form id="rn_QuestionSubmit" method="post" action="/cc/ajaxCustom/sendFormWDMR">
        <div id="rn_ErrorLocation"></div>
     <? if($WDMRtype == "MI") {  ?>
         <? $qty = getUrlParm("q");  ?>
			
		 <? if(!isset($qty)) { ?>
				<!-- Missing Item, no quantity yet -->
				<rn:widget path="custom/input/MissingItemQtyForm" referrer="#rn:php:$referrer#" />
		 <? } else { ?>
		 	<!-- Missing Item, quantity set -->
	        	<rn:widget path="custom/input/MissingItemForm" quantity="#rn:php:$qty#" referrer="#rn:php:$referrer#" />
	     <? } ?>
     <? } elseif($WDMRtype == "WDI") { ?>
        	<!-- Wrong or Damaged Item -->
			<? $WDI_type = getUrlParm("wd"); ?>
			<? if(!isset($WDI_type) || $WDI_type == "") { ?>
				<!-- Must choose Refund or Reship -->
        		<p>
	        		<h3>#rn:msg:CUSTOM_MSG_WDMR_SORRY_WDI_HDR#</h3>
        		</p>
        		<p>
	        		#rn:msg:CUSTOM_MSG_WDMR_WDI_HELP_TEXT#
        		</p>
	        	<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_REFUND" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_REFUND_BTN#" button_1_url="javascript:void(0)" button_1_onclick="RefundReshipSelection('0')" />
				<rn:widget path="custom/navigation/CustomButton" class_name="rn_WDMR_RESHIP" button_1_text="#rn:msg:CUSTOM_MSG_WDMR_RESHIP_BTN#" button_1_url="javascript:void(0)" button_1_onclick="RefundReshipSelection('1')" />
			<? } else  { ?>
			 <? if($WDI_type == 0) { ?>					
						<div class="rn_WDI_REFUND">
							<!-- Refund -->
			        		<rn:widget path="custom/input/DamagedWrongItemRefundForm" what_flow="REFUND" referrer="#rn:php:$referrer#" />
						</div>
	 		 <? } else { ?>
						<div class="rn_WDI_RESHIP">
							<!-- Reship -->
			        		<rn:widget path="custom/input/DamagedWrongItemReshipForm" what_flow="RESHIP" referrer="#rn:php:$referrer#" />
						</div>
			 <? } ?>
			<? } ?>
     <? } elseif($WDMRtype == "RI") { ?>
        	<!-- Return Item -->
        	<rn:widget path="custom/input/ReturnItemForm" referrer="#rn:php:$referrer#" />
     <? } else { ?>
        	<div>
			#rn:msg:CUSTOM_MSG_WDMR_MISSING_PARAMETERS#
			</div>
     <? } ?>
    </form>
    <? } else { ?>
    	<div>
			#rn:msg:CUSTOM_MSG_WDMR_DISABLE_FORMS#
		</div>
    <? } ?>
</div>
<script>

$( document ).ready(function() {
    //console.log( "jquery ready!" );
    
});

function RefundReshipSelection(type) {
	//alert("in RefundReshipSelection");
	//alert(type);
	//alert(window.location.href);
	//newHref = window.location.href + "/wd/" + type;
	newHref = window.location.pathname + "/wd/" + type;
	//alert(newHref);
	window.location.href = newHref;
}	
</script>
</div>