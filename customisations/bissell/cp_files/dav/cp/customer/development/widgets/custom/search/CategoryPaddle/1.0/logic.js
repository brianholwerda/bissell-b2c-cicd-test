RightNow.namespace('Custom.Widgets.search.CategoryPaddle');
Custom.Widgets.search.CategoryPaddle = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.renderListItem();
        this.openModalVideo();
    },

    /**
     * Sample widget method.
     */
    renderListItem: function() {
        var that = this;
        $('.rn_PaddleCategoryItem').append('<div class="category_arrow"><img src="images/arrow-right-grey.png" class="paddleArrow"/></div>').hover(function() {
            that.categoryHover(this);
        });
        $('.rn_AnswerGroup:first').show();
        $('.rn_PaddleCategoryItem:first').addClass('paddleSelected');
    },

    retnum: function(str) { 
        var num = str.replace(/[^0-9]/g, ''); 
        return num; 
    },

    categoryHover: function(that) {
        $('.rn_PaddleCategoryItem').each(function() {
            $(this).removeClass('paddleSelected');
        });
        var catHref = $(that).find('a').attr('href');
        var catId = this.retnum(catHref);
        $(that).addClass('paddleSelected').find('a').attr('href','/app/answers/topics/c/'+catId);
        var catTitle = $(that).text();
        this.showAnswerResults(catId, catTitle);
    },

    showAnswerResults: function(catId, catTitle) {
        $('.rn_AnswerGroup').hide();
        $('.rn_answerContainerTitle').text(catTitle);
        $('#rn_answerContainerMore a').attr('href', '/app/answers/topics/c/'+catId);
        $('.rn_CatList'+catId).show();
    },

    openModalVideo: function() {
        $('.playVideo').click(function() {
            $('#modalVideoContainer').empty();
            var videoId = $(this).parent().attr('videoId');
            var answerTitle = $(this).parent().find('.rn_PaddleVideoTitle').text();
            $('#modalVideoContainer').html('<div class="player"><iframe width="535" height="300" src="https://www.youtube.com/embed/'+videoId+'" frameborder="0" allowfullscreen></iframe></div>')
                .dialog({
                  width: 575,
                  title: answerTitle,
                  close: function( event, ui ) {$('#modalVideoContainer').empty();}
                });
        })
    }
});