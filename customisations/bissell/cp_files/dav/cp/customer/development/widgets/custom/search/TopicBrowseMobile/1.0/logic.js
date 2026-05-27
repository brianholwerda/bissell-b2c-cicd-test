RightNow.namespace('Custom.Widgets.search.TopicBrowseMobile');
Custom.Widgets.search.TopicBrowseMobile = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        if(this.data.attrs.category = "43") {
            this.openModalVideo();
            this.divideSections();
            this.rightScrollClick();
            this.leftScrollClick();
        }
    },

    openModalVideo: function() {
        $('.playVideo').click(function() {
            $('#modalVideoContainer').empty();
            var videoId = $(this).parent().attr('videoId');
            var answerTitle = $(this).parent().find('.rn_PaddleVideoTitle').text();
            $('#modalVideoContainer').html('<div class="player"><iframe width="535" height="300" src="https://www.youtube.com/embed/'+videoId+'?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe></div>')
                .dialog({
                  width: 575,
                  title: answerTitle,
                  close: function( event, ui ) {$('#modalVideoContainer').empty();}
                });
        })
    },

    setupSections: function() {
        var that = this;
        $('.rn_AnswerGroup').each(function() {
            var more = false;
            $(this).children('section').each(function() {
                var sectionNo = $(this).attr('no');
                //Show the first section but hide subsequent sections
                if(sectionNo !== "1") {$(this).hide();$('.rn_AnswerGroup').show();}
            });
            that.displayNextArrow(1, this);
        });
    },

    divideSections: function() {
        $('.rn_AnswerGroup').each(function() {
            var vids = $(this).children('.rn_PaddleVideoList');
            var v = "0";
            for(var i = 0; i < vids.length; i+=4) {
                v++;
                vids.slice(i, i+4).wrapAll('<section no="'+v+'"></div>');
            }
        });
        this.setupSections();
    },

    displayNextArrow: function(visibleSection, ansCat) {
        var moreSections = visibleSection + 1;
        if ($(ansCat).children('section[no="'+moreSections+'"]').length) {
            $(ansCat).next().find('img').attr('src','images/arrowleftDark.png').addClass('active');
        } else {
            $(ansCat).next().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleft.png').removeClass('active');
        }
    },

    displayPreviousArrow: function(visibleSection, ansCat) {
        var lessSections = visibleSection - 1;
        if ($(ansCat).children('section[no="'+lessSections+'"]').length) {
            $(ansCat).prev().find('img').attr('src','images/arrowleftDark.png').addClass('active');
        } else {
            $(ansCat).prev().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleft.png').removeClass('active');
        }
    },

    rightScrollClick: function() {
        var that = this;
        $('.rightScrollArrow').click(function() {
            var subCategory = $(this).prev();
            $(subCategory).children().each(function() {
                if($(this).is(':visible')) {
                    var visibleSection = Number($(this).attr('no'));
                    var nextSection = visibleSection + 1;
                    if($(this).siblings('[no="'+nextSection+'"]').length) {
                        $(this).hide("slide", { direction: "left" }, 400);
                        setTimeout(function(){
                            $(subCategory).find('[no="'+nextSection+'"]').show("slide", { direction: "right" }, 400);
                            setTimeout(function(){
                                that.displayNextArrow(nextSection, subCategory);
                                that.displayPreviousArrow(nextSection, subCategory);
                            }, 400);
                        }, 400);
                    }
                }
            });
        });
    },

    leftScrollClick: function() {
        var that = this;
        $('.leftScrollArrow').click(function() {
            var subCategory = $(this).next();
            $(subCategory).children().each(function() {
                if($(this).is(':visible')) {
                    var visibleSection = Number($(this).attr('no'));
                    var prevSection = visibleSection - 1;
                    if($(this).siblings('[no="'+prevSection+'"]').length) {
                        $(this).hide("slide", { direction: "right" }, 400);
                        setTimeout(function(){
                            $(subCategory).find('[no="'+prevSection+'"]').show("slide", { direction: "left" }, 400);
                            setTimeout(function(){
                                that.displayNextArrow(prevSection, subCategory);
                                that.displayPreviousArrow(prevSection, subCategory);
                            }, 400);
                        }, 400);
                    }
                }
            });
        });
    }
});