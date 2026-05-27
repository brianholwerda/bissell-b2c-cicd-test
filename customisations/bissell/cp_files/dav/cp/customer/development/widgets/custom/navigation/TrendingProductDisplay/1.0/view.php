<div id="rn_<?= $this->instanceID ?>" class="rn_VisualProductCategorySelector rn_TrendingProductDisplay">

	<? 
	
	//https://bissell--tst1.custhelp.com/app/products/detail/p/67216/~/barkbath%E2%84%A2-portable-dog-grooming-bathing-system-1st-gen-1844
	//SVC_PROD_ID
	//PRODUCT_TITLE
	//SKU
	//<img src="https://bissellcdn.blob.core.windows.net/cdn-storage-container/Web%20Portal%20Product%20Pages/US%20Product%20Pages/150X150/1844.jpg" alt="">
	//$this->data['results']
	
	$svcProdID = $this->data['results']['SVC_PROD_ID'];
	$mySKU = $this->data['results']['SKU'];
	$myProdTitle = $this->data['results']['PRODUCT_TITLE'];
	
	/* 
		Array ( [ASSOCIATED_PRODS] => 2554D, 2596Y [Answer] => [ID] => 829 [LookupName] => 829 [PRODUCT_SEARCH_TITLE] => CrossWave® Cordless Max Multi-Surface Wet Dry Vac 2554A [PRODUCT_TITLE] => CrossWave® Cordless Max Multi-Surface Wet Dry Vac | 2554A [PROD_PAGE_SHORT_URL] => https://www.bissell.com/products?sku=2554A [PROD_PAGE_URL] => https://www.bissell.com/crosswave-cordless-max-multi-surface-wet-dry-vac-2554a [SELL_STATUS] => Add to Cart [SKU] => 2554A [SVC_PROD_ID] => 173018 [CreatedTime] => [UpdatedTime] => )
			*/

	?>

	

    <div class="rn_ItemGroup rn_ItemLevel1 rn_BaseGroup rn_Item_Base_SubItems" id="rn_TrendingProductDisplay_Base_SubItems">
        <ul id="yui_3_18_1_13_1663089832514_157">
	       	<? $rowTypeStyle = "rn_Odd"; ?>
		   	<? for($i=0; $i<count($this->data['js']['items']); $i++) { ?>
	      
		    <?  
			    //Create Cannonical Product Title - all lowercase, replace space with -, replace | with -
			    $cannonicalTitle = "bissell®-";
			  
			    $cannonicalTitle += str_replace(" ", "-", strtolower($this->data['js']['items'][$i]['PRODUCT_TITLE']));
			    $cannonicalTitle  = str_replace("|", "-", $cannonicalTitle);
			    
			?>
			    
	        <li class="rn_Item <?=$rowTypeStyle; ?> rn_ItemWithID1<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?> <?=$cannonicalTitle; ?> ">  
	        	<div class="rn_VisualItemContainer" data-id="<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?>" >  
		        	<? /* <div class="rn_ImageContainer"> 
		        		<a href="/app/products/detail/p/<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?>/~/<?=$cannonicalTitle; ?>" class="rn_ItemLink" data-id="<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?>">  
			        		<img src="<?=$this->data['attrs']['image_path2']; ?>/<?=$this->data['js']['items'][$i]['SKU']; ?>.jpg" alt=""> 
			        	</a> 
			        </div> */ ?> 
			        <div class="rn_ActionContainer" >  
				        <a href="/app/products/detail/p/<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?>/~/<?=$cannonicalTitle; ?>" class="rn_ItemLink" data-id="<?=$this->data['js']['items'][$i]['SVC_PROD_ID']; ?>" >
					       <?=$this->data['js']['items'][$i]['SHORT_PRODUCT_TITLE']; ?>
					    </a>   
					</div>
				</div> 
			</li>
			<? if($rowTypeStyle == "rn_Odd"){
				$rowTypeStyle = "rn_Even";
			} else {
				$rowTypeStyle ="rn_Odd";
			} ?>
			<? } ?>
		</ul>
	</div>
    
        
</div>