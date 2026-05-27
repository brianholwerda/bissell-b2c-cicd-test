<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
	<style>
		#rn:msg:CUSTOM_MSG_SPECIAL_ANNOUNCEMENT_CSS#
		/* 
			.rn_SpecialAlertDiv {
			 height: 70px;
			 background-color: #EC2027;
			 color: white;
			 font-weight: 700;
			 padding-top: 5px;
			}
			.rn_SpecialAlert .rn_SpecialAlertCloseButton {
			 background-color: #EC2027;
			 color: white;
			}
			.rn_SpecialAlert .rn_SpecialAlertCloseButton:hover, .rn_SpecialAlert .rn_SpecialAlertCloseButton:disabled, .rn_SpecialAlert .rn_SpecialAlertCloseButton:focus:not(:disabled) {
			 background-color: #EC2027;
			 color: white;
			 border: 2px solid white;
			 border-radius: 6px;
			}
			.rn_SpecialAlertDivText {
				font-style: normal;
				font-weight: 400;
				font-size: 18px;
				line-height: 22px;
			}
			.rn_SpecialAlertHeading {
				text-align: left;
				font-style: normal;
				font-weight: 700;
				font-size: 18px;
				line-height: 22px;
			}
		*/
		
		
	</style>
	<rn:condition config_check="CUSTOM:CUSTOM_CFG_SHOW_SPECIAL_ANNOUNCEMENT == 1">
	
	<? if (! isset($_COOKIE['rn_SpecialAlertDivOff'])) { ?>
	    <div class="rn_Padding rn_SpecialAlertDiv">
	        <div class="rn_Container rn_SpecialAlertDiv rn_SpecialAlertDivContent" id="SpecialAlert">
		        <? if("#rn:msg:CUSTOM_MSG_SPECIAL_ANNOUNCEMENT_HDR#" != "") { ?>
	            <div class="rn_SpecialAlertHeading"><h3 class="rn_SpecialAlertDivH3">#rn:msg:CUSTOM_MSG_SPECIAL_ANNOUNCEMENT_HDR#</h3></div>
	            <? } ?>
	            <div class="rn_SpecialAlertDivText" >
	                #rn:msg:CUSTOM_MSG_SPECIAL_ANNOUNCEMENT_TEXT#
	            </div>
	            <div id="rn_SpecialAlert_closebtn" class="rn_SpecialAlertCloseButtonDiv"><button class="rn_SpecialAlertCloseButton" aria-label="Close" onClick="setCloseCookie();"></button></div>
	        </div>     
	    </div>
    <? } ?>
    </rn:condition>
</div>

<script>
	
	function setCloseCookie() {
		//alert("this should set a cookie to stop the show of the alert banner");
		$(".rn_SpecialAlertDiv").addClass("rn_Hidden");
		var minutes = #rn:config:CUSTOM_CFG_COOKIEMINUTES_SPECIAL_ANNOUNCEMENT#;
		name = "rn_SpecialAlertDivOff";
		value="TurnOffBanner";
		var date = new Date();
        date.setTime(date.getTime()+(minutes*60*1000));
        var expires = "; expires="+date.toGMTString();
        
        document.cookie = name+"="+value+expires+"; path=/"; 
	}
	

</script>