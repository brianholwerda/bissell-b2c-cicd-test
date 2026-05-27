RightNow.namespace('Custom.Widgets.search.TopicBrowse');
Custom.Widgets.search.TopicBrowse = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
		console.log(this.data.attrs.category);
        if(this.data.attrs.category = "67098") {
            
			var originalAnswerGroup = new Array();
			$('.rn_AnswerGroup').each(function(index) {
				originalAnswerGroup[index] = $(this).html();
			});
			
			console.log(originalAnswerGroup[0]);
			
			this.openModalVideo();
            this.divideSections();
            this.rightScrollClick();
            this.leftScrollClick();
			widgetScope = this;
			cat = [];
			buttons = [];
			$( ".rn_topicContainer" ).each(function( index ) {
				console.log("JGTEST");
				cat[index] = $(this);
				buttons[index] = ( '<a indexValue="' + index + '">' + $(this).find("h1").text() + '</a>' );
				$( "#filterDropdown" ).append(buttons[index]);
			});
			
			$( ".filterBtn" ).click(function() {
					$('#filterDropdown').toggle();
			});
			
			$( "#searchInput" ).keyup(function() {
					var input, filter, ul, li, a, i;
					input = document.getElementById("searchInput");
					filter = input.value.toUpperCase();
					div = document.getElementById("filterDropdown");
					a = div.getElementsByTagName("a");
					for (i = 0; i < a.length; i++) {
						if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
							a[i].style.display = "";
						} else {
							a[i].style.display = "none";
						}
					}
			});
			
			$( ".rn_topicContainer" ).each(function( index ) {
				$( "a[indexvalue="+ index +"]" ).click(function() {
					cat[index].toggle();
				});
			});
			
			 $( window ).resize(function() {
				$('.rn_AnswerGroup').each(function(index) {
					 $(this).html(originalAnswerGroup[index]);
				});
			    widgetScope.divideSections();
			 });
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
			console.log('RN ANSWER GROUP EACH');
            var vids = $(this).children('.rn_PaddleVideoList');
			console.log($(this).children('.rn_PaddleVideoList'));
            var v = "0";
            if($(window).width() <= 880 && $(window).width() >= 660) {
				for(var i = 0; i < vids.length; i+=3) {
					v++;
					vids.slice(i, i+3).wrapAll('<section no="'+v+'"></div>');
					console.log('Section wrapping');
				}
			} else if($(window).width() <= 660 && $(window).width() >= 460) {
				for(var i = 0; i < vids.length; i+=2) {
					v++;
					vids.slice(i, i+2).wrapAll('<section no="'+v+'"></div>');
					console.log('Section wrapping');
				}
			} else if($(window).width() <= 1080 && $(window).width() >= 880) {
				for(var i = 0; i < vids.length; i+=4) {
					v++;
					vids.slice(i, i+4).wrapAll('<section no="'+v+'"></div>');
					console.log('Section wrapping');
				}
			} else if($(window).width() <= 460) {
				for(var i = 0; i < vids.length; i+=1) {
					v++;
					vids.slice(i, i+1).wrapAll('<section no="'+v+'"></div>');
					console.log('Section wrapping');
				}
			} else{
				for(var i = 0; i < vids.length; i+=5) {
					v++;
					vids.slice(i, i+5).wrapAll('<section no="'+v+'"></div>');
					console.log('Section wrapping');
				}
			}
        });
        this.setupSections();
    },

    displayNextArrow: function(visibleSection, ansCat) {
        var moreSections = visibleSection + 1;
        if ($(ansCat).children('section[no="'+moreSections+'"]').length) {
            $(ansCat).next().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleftDark.png').addClass('active').removeClass('rn_inactiveArrow');
        } else {
            $(ansCat).next().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleft.png').removeClass('active').addClass('rn_inactiveArrow');
        }
    },

    displayPreviousArrow: function(visibleSection, ansCat) {
        var lessSections = visibleSection - 1;
        if ($(ansCat).children('section[no="'+lessSections+'"]').length) {
            $(ansCat).prev().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleftDark.png').addClass('activeLeft').removeClass('rn_inactiveLeftArrow');
        } else {
            $(ansCat).prev().find('img').not('.rn_answerRightArrow').attr('src','images/arrowleft.png').removeClass('activeLeft').addClass('rn_inactiveLeftArrow');
        }
    },

    rightScrollClick: function() {
        var that = this;
        $('button#rn_forwardButton').click(function() {
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
        $('button#rn_backButton').click(function() {
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