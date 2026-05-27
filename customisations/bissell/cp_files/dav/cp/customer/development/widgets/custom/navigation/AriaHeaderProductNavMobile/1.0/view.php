<? /* Overriding ProductCategoryList's view */ ?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>
    <div id="rn_Mobile_Products_Back">
    	<a href="javascript:;" class="menu-link dropdown-toggle" id="rn_Mobile_productLink_back" role="button" data-toggle="dropdown" aria-expanded="false" aria-controls="Products-menu">
	    	<img class="rn_chevronLeft" src="/euf/assets/images/chevronRight.png">
	    	Products
	    </a>
    </div>
    
	<? $myProd = \RightNow\Utils\Url::getParameter('p'); /*print($myProd);*/ ?>
    <? if($this->data['attrs']['label_title']): ?>
        <h2><?= $this->data['attrs']['label_title'] ?></h2>
    <? endif; ?>

    <div class="rn_Mobile_ListColumns">
        <? $columnClasses = array('rn_LeftColumnMobile', 'rn_MiddleColumnMobile', 'rn_RightColumnMobile');
           foreach($this->data['results'] as $resultIndex => $resultGroup): 
            ?>
            
            <ul aria-controls="rn_Mobile_ProdCatSub2_li_<?=$subItem2['id'] ?>" class="<?= $columnClasses[$resultIndex] ?>">
                <? foreach ($resultGroup as $index => $item): ?>
                    <li class="rn_Mobile_ProductCategoryItem">
                        <rn:block id="preItem"/>
                        
                        <?= $this->render('item', array('item' => $item)) ?>
                        <rn:block id="postItem"/>
                    </li>
                <? endforeach; ?>
            </ul>
        <? endforeach; ?>
    </div>

    <rn:block id="bottom"/>
</div>

	

<script type="text/javascript">

$(document).ready(function() {
	
	
	//$('a.rn_ClosedMenu').attr('aria-hidden', 'true');
	$('#rn_Mobile_ProdCatSub2_li_<?=$myProd; ?>').parent().removeClass( "rn_Hidden" ).addClass(" rn_Mobile_OpenMenu rn_Mobile_SelectedMenu ");
	
	$('[id^="rn_AriaHeaderMobile_"]').attr("role","region").attr("aria-label", "Product Filters");
	
	$('#myLink_<?=$myProd; ?>').removeClass( "rn_ClosedMenu" ).addClass(" rn_Mobile_OpenMenu rn_Mobile_SelectedMenu ");
	
	$('[id^="productLink_"]').on('click', function(){
		$('#productLink_<?=$myProd; ?>').attr("aria-current", "page");
	});

	if (window.location.href.indexOf(<?=$myProd; ?>) > 1){
      $('#productLink_<?=$myProd; ?>').attr("aria-current", "page"); 
      
    }
    
    $('#rn_Mobile_productLink_back').on('click', function(){
		$('#rn_Mobile_productNav').toggleClass('rn_Hidden');
		$('#rn_Mobile_TopLeveleMenu').toggleClass('rn_Hidden');
		$('#rn_Mobile_ProdSupport').toggleClass('rn_Hidden');
	}); 
     
});

function backSubMenu(section) {
	
	myID = "rn_Mobile_ProdCatSub2_" + section;
	var menu = document.getElementById(myID);
	menu.classList.add('rn_Hidden');
	
	}

function showHideSubmMenu(section) {
	myID = "rn_Mobile_ProdCatSub2_" + section;
	//myLinkID = "myLink_" + section;
	myAriaLink = "Mobile_productLink_" + section;
	var menu = document.getElementById(myID);
	pID = "rn_Mobile_subItem_" + section;
	var parentMenu = document.getElementById(pID);
	var LChevron = "#rn_Mobile_subItem_" + section + "  a img.rn_chevronLeft";
	var RChevron = "#rn_Mobile_subItem_" + section + "  a img.rn_chevronRight";
	//alert(LChevron);
	//alert(RChevron);
	//console.log(e.classList);
	//var e2 = document.getElementById(myLinkID);
	var ariaLink = document.getElementById(myAriaLink);
	
	
	
	
	
	if(menu.classList.contains('rn_Hidden'))
	{
		//Hide the Products Back link when SubMenu Clicked
		document.getElementById ("rn_Mobile_Products_Back").classList.add('rn_Hidden');
	
		//Hide all the First Level Products
		$('.rn_Mobile_ProductCategorySubItem').addClass(" rn_Hidden");
		//Show parent menu for selected
		parentMenu.classList.remove('rn_Hidden');
		//Toggle the Arrows
		$(LChevron).removeClass('rn_Hidden');
		$(RChevron).addClass('rn_Hidden');
		
		console.log('contains rn_Hidden');
		menu.classList.remove('rn_Hidden');
		menu.classList.remove('rn_Mobile_ClosedMenu');
		menu.classList.add('rn_Mobile_OpenMenu');
		menu.setAttribute("aria-expanded", "true");
	}
	else
	{
		//Show the Products Back link when SubMenu Clicked to close the ist
		document.getElementById("rn_Mobile_Products_Back").classList.remove('rn_Hidden');
		
		//Show all the First Level Products
		$('.rn_Mobile_ProductCategorySubItem').removeClass("rn_Hidden");
		//Toggle the Arrows
		$(LChevron).addClass('rn_Hidden');
		$(RChevron).removeClass('rn_Hidden');
		
		console.log('Does NOT contain rn_Hidden ');
		menu.classList.add('rn_Hidden');
		
		menu.classList.remove('rn_Mobile_OpenMenu');
		menu.classList.add('rn_Mobile_ClosedMenu');
		menu.setAttribute("aria-expanded", "false");
	}
	
}

/*
    function OpenCloseLeftNav(section) {    
	   myID = "rn_ProdCatSub2_" + section;
	   myLinkID = "myLink_" + section;
	   myAriaLink = "productLink_" + section;
	  
       var e = document.getElementById(myID);
       //console.log(e.classList);
       var e2 = document.getElementById(myLinkID);
       var e3 = document.getElementById(myAriaLink);
       

       //console.log(e2.classList);
       
       if(e.classList.contains('rn_Hidden'))
       {
			console.log('contains rn_Hidden');
			e.classList.remove('rn_Hidden');
			e2.classList.remove('rn_ClosedMenu');
			e2.classList.add('rn_OpenMenu');
			e2.setAttribute("aria-expanded", "true");
			//e3.setAttribute("aria-current", "page");
			
			//$('.rn_rightCaret').on('click', function(){
				//alert(' Contains rn_Hidden ');
				//$('i.fa.fa-caret-right').addClass('rotate');
				
			//});
			
			
       }
       else
       {
			console.log('Does NOT contain rn_Hidden ');
			e.classList.add('rn_Hidden');
			e2.classList.remove('rn_OpenMenu');
			e2.classList.add('rn_ClosedMenu');
			e2.setAttribute("aria-expanded", "false");

		}
	}
*/
</script>