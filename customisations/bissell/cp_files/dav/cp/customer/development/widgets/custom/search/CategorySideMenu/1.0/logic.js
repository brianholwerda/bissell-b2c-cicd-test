RightNow.namespace('Custom.Widgets.search.CategorySideMenu');
Custom.Widgets.search.CategorySideMenu = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.findCategory();
    },

    getUrlParam: function(url, key) {
        retVal = RightNow.Url.getParameter(key, url);
        return retVal;
    },

    findCategory: function() {
        var url = window.location.href;
        var thisCategory = this.getUrlParam(url, "c");
        this.selectMenuItem(thisCategory);
    },

    selectMenuItem: function(thisCategory) {
        $('[id^="rn_CategorySideMenu"]').find('a').each(function() {
            var href = $(this).attr('href');
            if(href.indexOf(thisCategory) > -1) {
                $(this).css('font-weight', 'bold');
                $(this).parent().css('margin-right', '0px');
            }
        });
    }
});