function CategoryDisplay(dat)
{
    //Data object contains all widget attributes, values, etc.
    var data = dat;
    var Dom = YAHOO.util.Dom;
    var Event = YAHOO.util.Event;

    // since these don't change after initial load, load them all once
//    var paddleEls = Dom.getElementsByClassName('paddle_item', 'div', 'CategoryPaddle');
//    var sectionEls = Dom.getElementsByClassName('subitem', 'div', 'CategoryPaddleContainer');

    this.init = function()
    {
//        for (var i = 0; i < paddleEls.length; ++i) {
//            Event.on(paddleEls[i], 'mouseover', function() {
//                setPaddle(this.id);
//            }, paddleEls[i], true);
//        }
//
//        // set initial focus
//        setPaddle(paddleEls[0].id);
    }

    function setPaddle(el_id)
    {
        // get the category id, then use it to show the various divs
        el_id = el_id.split('_')[1];

        // paddle handle
        for (var i = 0; i < paddleEls.length; ++i) {
            var cur_id = paddleEls[i].id.split('_')[1];

            if (paddleEls[i].id == 'CategoryPaddle_' + el_id) {
                // add the hover class
                if (!Dom.hasClass(paddleEls[i], 'item_over')) {
                    Dom.addClass(paddleEls[i], 'item_over');
                    
                    // var iconEl = paddleEls[i].getElementById('CategoryPaddleIcon');
                    var iconEl = Dom.getFirstChild(paddleEls[i]);
                    Dom.removeClass(iconEl, 'categoryPaddleIcon_' + el_id);
                    Dom.addClass(iconEl, 'categoryPaddleIconOver_' + el_id);
                }
            }
            else {
                // remove the hover class
                if (Dom.hasClass(paddleEls[i], 'item_over')) {
                    Dom.removeClass(paddleEls[i], 'item_over');
                    
                    // var iconEl = paddleEls[i].getElementById('CategoryPaddleIcon');
                    var iconEl = Dom.getFirstChild(paddleEls[i]);
                    Dom.removeClass(iconEl, 'categoryPaddleIconOver_' + cur_id);
                    Dom.addClass(iconEl, 'categoryPaddleIcon_' + cur_id);
                }
            }
        }

        // paddles
        for (var x = 0; x < sectionEls.length; ++x) {
            if (sectionEls[x].id == 'CategoryPaddleSub_' + el_id) {
                // add the hover class
                if (!Dom.hasClass(sectionEls[x], 'subitem_over'))
                    Dom.addClass(sectionEls[x], 'subitem_over');
            }
            else {
                // remove the hover class
                if (Dom.hasClass(sectionEls[x], 'subitem_over'))
                    Dom.removeClass(sectionEls[x], 'subitem_over');
            }
        }
    }

}   