<div id="rn_ariaFooterUS">
	<div class="footer-container row">
	   <div class="footer-item col-md-4">
	      <div class="email-container">
	         <div class="content-asset content-asset-center">
	            <div class="footer-email-content rn_FooterLogoSection">
	               <div class="rn_FooterLogoImage"><img id="rn_footerImage" src="#rn:config:CUSTOM_CFG_BISSELL_LOGO#" alt="BISSELL Logo"></div>
	               <div class="rn_FooterSupportText"><img id="rn_footerSupport" src="#rn:config:CUSTOM_CFG_SUPPORT_LOGO#" alt="Support Icon"></div>
				   <? /* <p id="rn_footerSupport">SUPPORT</p> */ ?>
	               <a  class="text-secondary; display-2" id="rn_backToShop" href="<?=$this->data['attrs']['com_environment']; ?>"> <img id="rn_footerChevron" src="/euf/assets/images/chevron-left-footer.png" alt="back arrow icon">#rn:msg:CUSTOM_MSG_BACK_TO_SHOPPING#</a>
	            </div>
	         </div>
	         <div class="content-asset content-asset-center">
	            <div class="social-links">
	               <a href="#rn:config:CUSTOM_CFG_FACEBOOK_LINK#" target="_blank">
	               <img src="#rn:config:CUSTOM_CFG_FACEBOOK#" alt="Bissell Facebook">
	               </a>
	               <a href="#rn:config:CUSTOM_CFG_INSTAGRAM_LINK#" target="_blank">
	               <img src="#rn:config:CUSTOM_CFG_INSTAGRAM#" alt="Bissell Instagram">
	               </a>
	               <a href="#rn:config:CUSTOM_CFG_PINTEREST_LINK#" target="_blank">
	               <img src="#rn:config:CUSTOM_CFG_PINTEREST#" alt="Bissell Pintrest">
	               </a>
	               <a href="#rn:config:CUSTOM_CFG_TWITTER_LINK#" target="_blank">
	               <img src="#rn:config:CUSTOM_CFG_TWITTER#" alt="Bissell Twitter">
	               </a>
	               <a href="#rn:config:CUSTOM_CFG_YOUTUBE_LINK#" target="_blank">
	               <img src="#rn:config:CUSTOM_CFG_YOUTUBE#" alt="Bissell Youtube">
	               </a>
	            </div>
	         </div>
	      </div>
	   </div>
	   <div class="footer-item col-md">
	      <div class="content-asset">
	         <h3>#rn:msg:CUSTOM_MSG_SUPPORT_RESOURCES#</h3>
	         <ul id="footer-support" class="menu-footer content">
	            <li><a href="/app/home/" title="Customer Support">#rn:msg:CUSTOM_MSG_SR_CUSTOMER_SUPPORT#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/check-order/" title="Order Search">#rn:msg:CUSTOM_MSG_SR_ORDER_SEARCH#</a></li>
	            <li> <a href="<?=$this->data['attrs']['com_environment']; ?>/category/save-pets/" title="Activate a Donation">#rn:msg:CUSTOM_MSG_SR_ACTIVATE_DONATION#</a></li>
	            <li><a href="/app/ask/" title="Email Us">#rn:msg:CUSTOM_MSG_SR_EMAIL#</a></li>
	            <li><a href="/app/returns/a_id/5286" title="Returns">#rn:msg:CUSTOM_MSG_SR_RETURNS#</a></li>
	            <li><a href="/app/answers/detail/a_id/4677/kw/warranty" target="_blank">#rn:msg:CUSTOM_MSG_SR_WARRANTY#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/parts/" title="Parts">#rn:msg:CUSTOM_MSG_SR_PARTS#</a></li>
				<?php if ($this->data['attrs']['referrer'] == "US"): ?>
					<? /* US */ ?>
				<?php else : ?>
					<? /* CANADA */ ?>
	            	<li><a href="<?=$this->data['attrs']['com_environment']; ?>/service-center-locator/" title="Service Center Locator">#rn:msg:CUSTOM_MSG_SR_SERVICE_CENTER_LOC#</a></li>
				<?php endif; ?>
	            <li><a href="https://supplier.bissell.com/MSDS_Domestic/Default.aspx" title="SDS">#rn:msg:CUSTOM_MSG_SR_SDS#</a></li>
	            <li><a href="https://supplier.bissell.com/Ingredients_Domestic/Default.aspx" title="Ingredients List">#rn:msg:CUSTOM_MSG_SR_INGREDIENTS#</a></li>
	         </ul>
	      </div>
	   </div>
	   <div class="footer-item col-md">
	      <div class="content-asset">
	         <h3>#rn:msg:CUSTOM_MSG_MY_ACCOUNT#</h3>
	         <ul id="footer-account" class="menu-footer content">
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/account/" title="My Profile">#rn:msg:CUSTOM_MSG_MA_MY_PROFILE#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/account/my-rewards/" title="My Rewards">#rn:msg:CUSTOM_MSG_MA_MY_REWARDS#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/check-order/" title="My Orders">#rn:msg:CUSTOM_MSG_MA_MY_ORDERS#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/support/email-subscriptions" title="My Email Subscriptions">#rn:msg:CUSTOM_MSG_MA_EMAIL_SUBSCRIPTIONS#</a></li>
	         </ul>
	      </div>
	   </div>
	   <div class="footer-item col-md">
	      <div class="content-asset">
	         <h3>#rn:msg:CUSTOM_MSG_ABOUT#</h3>
	         <ul id="footer-about" class="menu-footer content">
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/about-us.html" title="About Us">#rn:msg:CUSTOM_MSG_ABOUT_US#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/about-us/our-history/" title="Our History">#rn:msg:CUSTOM_MSG_OUR_HISTORY#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/about-us/sustainability.html" title="Sustainability &amp; CSR">#rn:msg:CUSTOM_MSG_SUSTAINABILITY#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/about-us/community-involvement.html" title="Community Involvement">#rn:msg:CUSTOM_MSG_COMMUNITY#</a></li>
	            <li><a href="https://careers.bissell.com/" title="Careers">#rn:msg:CUSTOM_MSG_CAREERS#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/news/">#rn:msg:CUSTOM_MSG_NEWSROOM#</a></li>
	            <li><a href="<?=$this->data['attrs']['com_environment']; ?>/category/the-clean-list/">#rn:msg:CUSTOM_MSG_CLEANING#</a></li>
	         </ul>
	      </div>
	   </div>
	</div>
	<hr class="rn_footerDivider"/>
	<div id="rn_partnersFooter">
	   <div class="container menu-footer content">
	      <div class="footer-container row justify-content-md-center">
	         <div class="col col-11 text-center partner-content">
	            <div class="footer-container row">
	               <div class="col-6 col-md-12 col-xl d-none d-md-block">
	                  <h2 class="partner-title">#rn:msg:CUSTOM_MSG_OUR_PARTNERS#</h2>
	               </div>
	               <div class="col-6 col-md-4 col-xl"><a href="<?=$this->data['attrs']['com_environment']; ?>/category/products/carpet-and-floor-cleaning-formulas/woolite-cleaning-products/" target="_blank"><img alt="Woolite Logo" src="#rn:config:CUSTOM_CFG_WOOLITE_LOGO#" title="Woolite Logo"></a></div>
	               <div class="col-6 col-md-4 col-xl"><a href="#rn:config:CUSTOM_CFG_RUGDOCTOR_LINK#"><img alt="RugDoctor by BISSELL Logo" src="#rn:config:CUSTOM_CFG_RUGDOCTOR_BY_BISSELL#" title="RugDoctor by BISSELL"></a></div>
	               <div class="col-6 col-md-4 col-xl"><a href="#rn:config:CUSTOM_CFG_GREEN_LINK#" target="_blank"><img alt="BISSELL Commercial Logo" src="#rn:config:CUSTOM_CFG_GREEN#" title="BISSELL Commercial"></a></div>
	               <div class="col-6 col-md-4 col-xl"><a href="#rn:config:CUSTOM_CFG_BPF_LINK#" target="_blank"><img alt="BISSELL Pet Foundation Logo" src="#rn:config:CUSTOM_CFG_BPF#" title="BISSELL Pet Foundation"></a></div>
	               <div class="col-6 col-md-4 col-xl"><a href="#rn:config:CUSTOM_CFG_SANITAIR_LINK#" target="_blank"><img alt="Sanitaire Logo" height="50" src="#rn:config:CUSTOM_CFG_SANITAIR#" title="Sanitaire" width="90"></a></div>
	            </div>
	         </div>
	      </div>
	   </div>
	</div>
	<hr class="rn_footerDivider"/>
	
	<? if($this->data['attrs']['referrer'] == "US") { ?> 
	<? /* US LEGAL FOOTER */ ?>
	<div id="rn_legalFooter">
	   <div class="col copyright-notice">
	      <div class="content-asset">
	         <div class="postscript"><?/*<a class="item" href="https://global.bissell.com/" title="United States"></a>*/?><a href="<?=$this->data['attrs']['com_environment']; ?>/privacy-notice/">#rn:msg:CUSTOM_MSG_PRIVACY_POLICY#</a> | <a href="<?=$this->data['attrs']['com_environment']; ?>/terms-of-service/">#rn:msg:CUSTOM_MSG_TERMS_OF_SERVICE#</a> | <a href="<?=$this->data['attrs']['com_environment']; ?>/website-accessibility-statement/">#rn:msg:CUSTOM_MSG_ACCESSABILITY#</a> | <a href="<?=$this->data['attrs']['com_environment']; ?>/california-supply-chains-act/">#rn:msg:CUSTOM_MSG_CALI_SUPPLY_CHAIN#</a> | <a href="https://www.bissell.com/california-privacy-act.html">#rn:msg:CUSTOM_MSG_SELL_INFO#</a> 
	         </div>
	         <div class="copyright">
	            
	            <span display:="" inline-block;="">
	            <a class="item" href="https://global.bissell.com/" title="United States">
	            <i class="flag-icon flag-icon-us"></i>
	            #rn:msg:CUSTOM_MSG_UNITED_STATES#
	            </a></span>
	            </a>
	         </div>
	          <div class="copyright">
	            © <?= date("Y"); ?> #rn:msg:CUSTOM_MSG_BISSELL_RIGHTS_RESERVED#<br>
	          </div>
	      </div>
	   </div>
	</div>
	<? } else { ?>
	<? /* CANADA LEGAL FOOTER */ ?>
	<div id="rn_legalFooter">
      <div class="col copyright-notice">
         <div class="content-asset">  
            <div class="postscript"><?/*<a class="item" href="https://global.bissell.com/" title="Canada"></a>*/?><a href="<?=$this->data['attrs']['com_environment']; ?>/privacy-notice/">#rn:msg:CUSTOM_MSG_PRIVACY_POLICY#</a> | <a href="<?=$this->data['attrs']['com_environment']; ?>/terms-of-service/">#rn:msg:CUSTOM_MSG_TERMS_OF_SERVICE#</a> | <a href="<?=$this->data['attrs']['com_environment']; ?>/website-accessibility-statement/">#rn:msg:CUSTOM_MSG_ACCESSABILITY#</a>
               | <a class="ot-sdk-show-settings" href="#">#rn:msg:CUSTOM_MSG_COOKIE_SETTING#</a>
            </div>
            
            <div class="copyright">
               <span display:="" inline-block;=""><a class="item" href="https://global.bissell.com/" title="Canada">
               <i class="flag-icon flag-icon-ca"></i>
               #rn:msg:CUSTOM_MSG_CANADA#</a></span>
            </div>
            <div class="copyright">© <?= date("Y"); ?> #rn:msg:CUSTOM_MSG_BISSELL_RIGHTS_RESERVED#
            </div>
         </div>
      </div>
   </div>
	<? } ?>
</div>