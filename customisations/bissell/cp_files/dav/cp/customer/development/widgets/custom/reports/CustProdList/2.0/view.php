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
				.rn_HomeProductDisplayHead {
					display:none;
				}
				.rn_PageContent.rn_ProductDetailPage .rn_PopularKB {
				display: block;
				}
				.rn_ProductDetailPage .rn_Product .rn_HeroInner nav {
					width: 80%;
				}
				
				@media all and (max-width : 767px) {
					.rn_ProductDetailPage .rn_Product .rn_HeroInner nav {
					    width: 95%;
					}
					/* On mobile, once we have a product, we only show the magnify glass to toggle display */
					.rn_ProductDetailPage #rn_SearchMagnifyIconHideShow {
						display: inline-block;
						padding-top: 16px;
					}
					.rn_ProductDetailPage #rn_SearchLinkHideShow {
						display: none;
					}
				}
			</style>
			<div class="rn_product-top-section">
				<div id="rn_ProdDetailImage" class="rn_floatLeft"><img src="<?=$this->data['attrs']['image_path2']?>/<?=$this->data['results']['SKU'];?>.jpg" alt="<?= $this->data['results']['PRODUCT_TITLE']; ?>" /></div>
				<div class="rn_ProductHero">
			        <h1>
			            <rn:condition url_parameter_check="p != null">
			                <rn:field name="ServiceProduct.Name"/>
			            <rn:condition_else/>
			                #rn:msg:PRODUCT_NOT_FOUND_LBL#
			            </rn:condition>
			        </h1>
							<!-- <p class='similar-model-space'></p> -->
			        <div class="rn_ProdDetailSimilar">
								Similar Models: 
								<span id="similar-model-years">
									<?= str_replace(",","",$this->data['results']['ASSOCIATED_PRODS']); ?>
								</span>
							</div>

							<div id="rn_ProdDetailImage_mobile" class=""><img src="<?=$this->data['attrs']['image_path2']?>/<?=$this->data['results']['SKU'];?>.jpg" alt="<?= $this->data['results']['PRODUCT_TITLE']; ?>" /></div>


							<div class="rn_floatLeft rn_ProductSupportNav">
								<img src="/euf/assets/images/pdf-image.png" alt="">
								<a class="rn_link_divider" href="/app/answers/list/p/<?= $this->data['results']['SVC_PROD_ID']; ?>/c/26115">#rn:msg:CUSTOM_MSG_USER_MANUAL#</a>
								<a class="rn_link_divider" href="/app/answers/topics/p/<?= $this->data['results']['SVC_PROD_ID']; ?>/c/67098">#rn:msg:CUSTOM_MSG_VIDEO_SUPPORT#</a>
								<!-- <a class="rn_link_divider" href="<?= $this->data['attrs']['referring_url'] ?>/<?= $this->data['results']['SKU']; ?>.html#product-parts-anchor" target="_blank">#rn:msg:CUSTOM_MSG_PARTS#</a> -->
								<? if($this->data['attrs']['referrer'] == "CA") { ?>
									<a class="rn_link_divider" href="https://www.bissell.com/en-ca/parts/<?= $this->data['results']['SKU']; ?>" target="_blank">#rn:msg:CUSTOM_MSG_PARTS#</a>
								<? } else { ?>
									<a class="rn_link_divider" href="https://www.bissell.com/en-us/parts/<?= $this->data['results']['SKU']; ?>" target="_blank">#rn:msg:CUSTOM_MSG_PARTS#</a>
								<? } ?>
								<? if($this->data['attrs']['Warranty'] != "") { ?>
									<a class="rn_link_divider" href="<?=$this->data['attrs']['Warranty']; ?>" target="_blank">#rn:msg:CUSTOM_MSG_WARRANTY#</a>
								<? } ?>
								<a class="rn_link_divider_noBorder" href="<?= $this->data['attrs']['referring_url'] ?>/<?= $this->data['results']['SKU']; ?>.html">#rn:msg:CUSTOM_MSG_SHOP_PRODUCT#</a>
							</div>
			    </div>
				<!-- <div>SVC_PROD_ID: <?= $this->data['results']['SVC_PROD_ID']; ?></div>
		        <div>SKU: <?= $this->data['results']['SKU']; ?></div>
		        <div>PRODUCT TITLE: <?= $this->data['results']['PRODUCT_TITLE']; ?></div>
		        <div>SHORT_URL: <?= $this->data['results']['PROD_PAGE_SHORT_URL']; ?></div>
		        <div>PROD URL: <?= $this->data['results']['PROD_PAGE_URL']; ?></div>
				<div>REFERRER: <?= $this->data['attrs']['referrer'] ?></div>
				<div>REFURL: <?= $this->data['attrs']['referring_url'] ?></div> -->
			</div>

			<?  //print_r($this->data['reportData']); 
				//print($this->data['attrs']['foundArticles']);	
				
			?>
			<? if ($this->data['attrs']['foundArticles'] == "YES") { ?>
			<div class="instructional-guide">
				<h2 id='rn_h2-InstructionalGuide'>
					#rn:msg:CUSTOM_MSG_INSTRUCTIONAL_GUIDE#
				</h2>
			</div>
			<div class="grid-container">

			<?
				$ReportIds = \RightNow\Utils\Config::getConfig(CUSTOM_CFG_PROD_TROUBLESHOOT_REPORTS); //115885,115892,115887,115890,115889,115883,115886,115882,115888,115891,115884
				//print($ReportIds);
				$array = explode(',', $ReportIds); //split string into array separated by ', '
				$numReports = 1;
				foreach($array as $value) //loop over values
				{
					//print_r($numReports);
					if($numReports > 12) {
						break;
					}
					//print_r($value);
					//print_r($this->data['reportData'][$value][0]);
					if(isset($this->data['reportData'][$value][0]))
					{
			
						if(isset($this->data['reportData'][$value][0][0]) && $this->data['reportData'][$value][0][3] == 'Yes' && !isset($this->data['reportData'][$value][1]))
						{
							$buildClass = "rn_" . str_replace(' ', '', $this->data['reportData'][$value][0][1]);
							
					 	?>
								<div class="grid-item">
										<rn:widget path="custom/navigation/CallToActionBox"
										class_name="rn_ProdDetailRegister rn_CallToActionBox25 #rn:php:$buildClass#"
										headline="#rn:php:$this->data['reportData'][$value][0][1]#"
										image_url_below_header="/euf/assets/images/ProductDetailTroubleshooting/#rn:php:$value#.svg"
										button_1_text="#rn:msg:CUSTOM_MSG_WATCH_VIDEO#"
										button_1_url="/app/answers/detail/a_id/#rn:php:$this->data['reportData'][$value][0][0]#"
										button_2_text=""
										button_2_url=""
										/>
								</div>
						<?} else {  
								if(isset($this->data['reportData'][$value][0][0]) && $this->data['reportData'][$value][0][2] == 'Yes' && !isset($this->data['reportData'][$value][1]))
								{
									$buildClass = "rn_" . str_replace(' ', '', $this->data['reportData'][$value][0][1]);
								
						?>
									<div class="grid-item">
											<rn:widget path="custom/navigation/CallToActionBox"
											class_name="rn_ProdDetailRegister rn_CallToActionBox25 #rn:php:$buildClass#"
											headline="#rn:php:$this->data['reportData'][$value][0][1]#"
											image_url_below_header="/euf/assets/images/ProductDetailTroubleshooting/#rn:php:$value#.svg"
											button_1_text="#rn:msg:CUSTOM_MSG_READ_GUIDE#"
											button_1_url="/app/answers/detail/a_id/#rn:php:$this->data['reportData'][$value][0][0]#"
											button_2_text=""
											button_2_url=""
											/>
									</div>
								<?} else {  ?>
						
									<div class="grid-item">
													<rn:widget path="custom/navigation/CallToActionBox"
													class_name="rn_ProdDetailRegister rn_CallToActionBox25 #rn:php:$buildClass#"
													headline="#rn:php:$this->data['reportData'][$value][0][1]#"
													image_url_below_header="/euf/assets/images/ProductDetailTroubleshooting/#rn:php:$value#.svg"
													button_1_text="#rn:msg:CUSTOM_MSG_READ_GUIDE#"
													button_1_url="/app/answers/detail/a_id/#rn:php:$this->data['reportData'][$value][0][0]#"
													button_2_text="#rn:msg:CUSTOM_MSG_WATCH_VIDEO#"
													button_2_url="/app/answers/detail/a_id/#rn:php:$this->data['reportData'][$value][1][0]#"
													/>
											</div>
											
									<? /* <div class="grid-item">
											<rn:widget path="custom/navigation/CallToActionBox"
											class_name="rn_ProdDetailRegister rn_CallToActionBox25"
											headline="#rn:php:$this->data['reportData'][$value][0][1]#"
											image_url_below_header="/euf/assets/images/ProductDetailTroubleshooting/#rn:php:$value#.svg"
											button_1_text="#rn:msg:CUSTOM_MSG_READ_GUIDE#"
											button_1_url="/app/answers/detail/a_id/#rn:php:$this->data['reportData'][$value][0][0]#"
											/>
									</div> */ ?>
								<?} 
						}
						$numReports++;
					}
				}
				?>
	    </div>
	    <? } else { ?>
	    	<style>
		    	.rn_PageContent .rn_PopularKB .rn_Container.rn_TopAnswers {
				    border-top: 0px solid #C4C4C4;
				    border-bottom: 0px solid #C4C4C4;
				    margin-top: 0px;
				    padding-top: 0px;
				}
			</style>
	    <? } ?>
			<!-- <div class="ProdDetailVideos">
				<rn:widget path="custom/search/TopicBrowse" max_topics="8" default_category="67098" max_answers="100" show_filter=false is_video=true />
			</div> -->
	<? } else { ?>
		<style>
			.rn_PageContent.rn_Product.rn_ProductSelector {
				display: block;
			}
			.rn_HomeProductDisplayHead {
				display:block;
			}
			.rn_PageContent.rn_ProductDetailPage .rn_PopularKB {
				display: none;
			}
			@media all and (max-width : 767px) {
				/* On mobile, Until we have a product, we only show the text to open the search area */
				.rn_ProductDetailPage #rn_SearchMagnifyIconHideShow {
					display: none;
				}
				.rn_ProductDetailPage #rn_SearchLinkHideShow {
					display: inline-block;
				}
				.rn_ProductDetailPage .rn_Product .rn_HeroInner nav {
				    width: 100%;
				}
			}
		</style>
	<? } ?>
	</div>
</div>
