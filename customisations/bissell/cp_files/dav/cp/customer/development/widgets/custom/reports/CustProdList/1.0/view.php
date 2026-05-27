<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<? 
print '<input type="hidden" id="p_id" value="' . $this->data['p_id'] . '">';
$mySKU = $this->data['results']['SKU'];
$myURL = $this->data['results']['PROD_PAGE_SHORT_URL'];
?>
<br>
	<div id="container_<?=$this->instanceID;?>">
	<? if ($this->data['results']['PRODUCT_TITLE'] != "") { ?>
			<style>
				.rn_PageContent.rn_Product.rn_ProductSelector {
					display: none;
				}
				.rn_PageContent.rn_ProductDetailPage .rn_PopularKB {
				display: block;
				}
			</style>	
			<div>
				<div id="rn_ProdDetailImage" class="rn_floatLeft"><img src="<?=$this->data['attrs']['image_path2']?>/<?=$this->data['results']['SKU'];?>.jpg" alt="<?= $this->data['results']['PRODUCT_TITLE']; ?>" /></div>
				<div class="rn_ProductHero">
			        <h1>
			            <rn:condition url_parameter_check="p != null">
			                <rn:field name="ServiceProduct.Name"/>
			            <rn:condition_else/>
			                #rn:msg:PRODUCT_NOT_FOUND_LBL#
			            </rn:condition>
			        </h1>
			        <div class="rn_ProdDetailSimilar">Similar Models: <?= $this->data['results']['ASSOCIATED_PRODS']; ?></div>
			    </div>
				<!-- <div>SVC_PROD_ID: <?= $this->data['results']['SVC_PROD_ID']; ?></div>
		        <div>SKU: <?= $this->data['results']['SKU']; ?></div>
		        <div>PRODUCT TITLE: <?= $this->data['results']['PRODUCT_TITLE']; ?></div>
		        <div>SHORT_URL: <?= $this->data['results']['PROD_PAGE_SHORT_URL']; ?></div>
		        <div>PROD URL: <?= $this->data['results']['PROD_PAGE_URL']; ?></div>
				<div>REFERRER: <?= $this->data['attrs']['referrer'] ?></div>
				<div>REFURL: <?= $this->data['attrs']['referring_url'] ?></div> -->
			</div>
			<div class="rn_floatLeft">    
		        <? if($this->data['attrs']['referrer'] == 'US') { ?>
					<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_ProdDetailMoreInfo rn_CallToActionBox25"
					image_url="images/icons/bissell/PDP-Icons_more-info.png"
					image_alt="View More Information"
					headline="View More Information"
					subtext="See more about your BISSELL product and where to buy"
					button_1_text="View Information"
					button_1_url="#rn:php:$this->data['attrs']['referring_url']#/#rn:php:$this->data['results']['SKU']#.html"
					button_2_text=""
					button_2_url=""
					/>
				<? } else { ?>
					<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_ProdDetailMoreInfo rn_CallToActionBox25"
					image_url="images/icons/bissell/PDP-Icons_more-info.png"
					image_alt="View More Information"
					headline="View More Information"
					subtext="See more about your BISSELL product and where to buy"
					button_1_text="View Information"
					button_1_url="#rn:php:$this->data['results']['PROD_PAGE_URL']#"
					button_2_text=""
					button_2_url=""
					/>
				<? } ?>
				<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_ProdDetailUserGuide rn_CallToActionBox25"
				image_url="images/icons/bissell/PDP-Icons_user-guides.png"
				image_alt="User Guides & Tips"
				headline="User Guides & Tips"
				subtext="Helpful information to get the most out of your BISSELL machine"
				button_1_text="View Guides"
				button_1_url="/app/answers/list/p/#rn:php:$this->data['results']['SVC_PROD_ID']#/c/26115"
				button_2_text=""
				button_2_url=""
				/>
				<? if($this->data['attrs']['referrer'] == 'US') { ?>
					<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_ProdDetailParts rn_CallToActionBox25"
					image_url="images/icons/bissell/PDP-Icons-replacement-parts.png"
					image_alt="Replacement Parts"
					headline="Replacement Parts"
					subtext="Genuine BISSELL parts to keep your machine running smoothly"
					button_1_text="View Parts"
					button_1_url="#rn:php:$this->data['attrs']['referring_url']#/#rn:php:$this->data['results']['SKU']#.html#product-parts-anchor"
					button_2_text=""
					button_2_url=""
					/>
				<? } else { ?>
					<rn:widget path="custom/navigation/CallToActionBox" 
					class_name="rn_ProdDetailParts rn_CallToActionBox25"
					image_url="images/icons/bissell/PDP-Icons-replacement-parts.png"
					image_alt="Replacement Parts"
					headline="Replacement Parts"
					subtext="Genuine BISSELL parts to keep your machine running smoothly"
					button_1_text="View Parts"
					button_1_url="#rn:php:$this->data['results']['PROD_PAGE_URL']#/parts"
					button_2_text=""
					button_2_url=""
					/>
				<? } ?>
				<rn:widget path="custom/navigation/CallToActionBox" 
				class_name="rn_ProdDetailRegister rn_CallToActionBox25"
				image_url="images/icons/bissell/PDP-Icons-register.png"
				image_alt="Register Your Product"
				headline="Register Your Product"
				subtext="Register today to find all the information you need"
				button_1_text="Register Here"
				button_1_url="#rn:php:$this->data['attrs']['referring_url']#/product-registration"
				button_2_text=""
				button_2_url=""
				/>
			</div>
			<div class="ProdDetailVideos">
				<rn:widget path="custom/search/TopicBrowse" max_topics="8" default_category="67098" max_answers="100" show_filter=false is_video=true />
			</div>
	<? } else { ?>
		<style>
			.rn_PageContent.rn_Product.rn_ProductSelector {
				display: block;
			}
			.rn_PageContent.rn_ProductDetailPage .rn_PopularKB {
				display: none;
			}
		</style>
	<? } ?>
	</div>
</div>