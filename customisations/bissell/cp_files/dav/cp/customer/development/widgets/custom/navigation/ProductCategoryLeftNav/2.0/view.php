<? /* Overriding ProductCategoryList's view */ ?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>

	<? $myProd = \RightNow\Utils\Url::getParameter('p'); /*print($myProd);*/ ?>
    <? if($this->data['attrs']['label_title']): ?>
        <h2><?= $this->data['attrs']['label_title'] ?></h2>
    <? endif; ?>

    <div class="rn_ListColumns">
        <? $columnClasses = array('rn_LeftColumn', 'rn_MiddleColumn', 'rn_RightColumn');
           foreach($this->data['results'] as $resultIndex => $resultGroup): 
            ?>
            
            <ul aria-controls="rn_ProdCatSub2_li_<?=$subItem2['id'] ?>" class="<?= $columnClasses[$resultIndex] ?>">
                <? foreach ($resultGroup as $index => $item): ?>
                    <li class="rn_ProductCategoryItem">
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
	$('#rn_ProdCatSub2_li_<?=$myProd; ?>').parent().removeClass( "rn_Hidden" ).addClass(" rn_OpenMenu rn_SelectedMenu ");
	
	$('[id^="rn_ProductCategoryLeftNav_"]').attr("role","region").attr("aria-label", "Product Filters");
	
	$('#myLink_<?=$myProd; ?>').removeClass( "rn_ClosedMenu" ).addClass(" rn_OpenMenu rn_SelectedMenu ");
	
	$('[id^="productLink_"]').on('click', function(){
		$('#productLink_<?=$myProd; ?>').attr("aria-current", "page");
	});

	if (window.location.href.indexOf(<?=$myProd; ?>) > 1){
      $('#productLink_<?=$myProd; ?>').attr("aria-current", "page"); 
      
    }
});



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
 			//e3.removeAttribute("aria-current", "page");
			/*$('.rn_rightCaret').on('click', function(){
				//alert('Does Not contain rn_Hidden');
				$('i.fa.fa-caret-right').removeClass('rotate');
			});*/
		}
	}
</script>