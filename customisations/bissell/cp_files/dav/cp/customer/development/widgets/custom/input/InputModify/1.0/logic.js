RightNow.namespace('Custom.Widgets.input.InputModify');
Custom.Widgets.input.InputModify = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        if($('#NAV').length == 0){
            if(this.data.attrs.modify_asterisk) {this.modifyAsterisk();}
            if(this.data.attrs.pholder_label) {this.insertPlaceholder();}
            if(this.data.attrs.modify_validation) {this.modifyFields();}
        }
    },

    placeHolderText: function(pText) {
        //Remove newlines, multiple spaces at the beginning, end, and middle of the string, and all other special characters (for the *).
        pText = pText.replace(/\r?\n|\r/g, '').replace(/\s+/g, ' ').replace(/[^\w\s]/gi, '');
        pText = pText.replace('  Required','*');
        return pText;
    },

    insertPlaceholder: function() {
        var that = this;
        $('#rn_QuestionSubmit').find('input,textarea').each(function(){ //Loop through all inputs and textareas
            //Find the label for the input, hide and assign the variable
            var pText = $(this).prev().find('label').hide().text();
            pText = that.placeHolderText(pText);
            //Add the label text to the input placeholder attribute
            $(this).attr("placeholder", pText);
        });
    },

    modifyAsterisk: function() {
        $('.rn_Required').each(function() {
            var rQ = $(this).html();
            $(this).parent().prepend('<span class="rn_Required">'+rQ+'</span>');
            $(this).hide();
        });
    },

    checkVowel: function(char) {
        char = char.toLowerCase();
        if (char.length == 1) {
            var vowels = new Array('a','e','i','o','u');
            var isVowel = false;
            for(e in vowels) {
                if(vowels[e] == char) {
                    return "an";
                }
            }
            return "a";
        }
    },

    validateEmail: function(email) {
        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },

    fieldError: function(that,el,validation) {
        elClass = $(el).attr('id');
        inputVal = $(el).val();
        if((inputVal == "") || (inputVal == "--") || (inputVal == null) || (($(el).is(':radio')) && (!$(el).prop('checked')))) {
            $(el).css({'border-color':'#'+that.data.attrs.border_color});
            var elClass = $(el).attr('id');
            if(!$(el).hasClass(validation)) {
                var pText = $(el).parent().find('label').text();
                pText = that.placeHolderText(pText);
                pText = pText.trim();
                if(that.data.attrs.alt_text) {
                    var nounFL = pText.charAt(0);
                    var determiner = that.checkVowel(nounFL);
                    pText = pText.replace('  Required','');
                    pText = that.data.attrs.alt_text+' '+pText;
                    pText = pText.replace(' a ',' '+determiner+' ');
                }
                $(el).addClass(validation);
                if(that.data.attrs.pholder_validate) {
                    $(el).attr('placeholder',pText);
                } else {
                    if((!$(el).hasClass('blurValidated')) || (!$(el).hasClass('submitValidated'))) {
                        $('<div class="'+elClass+'"><span class="requiredLabel" style="color:#'+that.data.attrs.text_color+';">'+pText+'</span></div>').insertAfter($(el).parent());
                    }
                }
            }
        } else {
            this.fieldValidateBlur(that,el,validation,elClass,inputVal);
        }
    },

    fieldValidateBlur: function(that,el,validation,elClass,inputVal) {
        if(($(el).hasClass('blurValidated')) || ($(el).hasClass('submitValidated'))) {
            if(elClass.indexOf('Emails.PRIMARY.Address') > -1) {
                if(that.validateEmail(inputVal)) {
                    if(!that.data.attrs.pholder_validate) {
                        $(el).parent().next().remove();
                    }
                    $(el).removeClass('blurValidated submitValidated');
                    $(el).css({'border-color':'#'+that.data.attrs.orig_border_color});
                } else {
                    if(!that.data.attrs.pholder_validate) {
                        $(el).parent().next().find('span').text('"'+inputVal+'" is not a valid Email Address');
                    } else {
                        $(el).val('').attr('placeholder','"'+inputVal+'" is not a valid Email Address');
                    }
                }
            } else {
                if(!that.data.attrs.pholder_validate) {
                    $(el).parent().next().remove();
                }
                $(el).removeClass('blurValidated submitValidated');
                $(el).css({'border-color':'#'+that.data.attrs.orig_border_color});
            } 
        }
    },

    modifyFields: function() {
        var that = this;
        $('#rn_ErrorLocation').wrap('<div style="display:none;"></div>');
        if(this.data.attrs.validate_on_blur) {
            $('.'+this.data.attrs.class+'').find('input,textarea,select').each(function() {
                $(this).blur(function() {
                    that.fieldError(that,this,'blurValidated');
                });
            });
        } else {
            // Validate on form submit click
            $('input:submit').click(function() {
                $('.'+that.data.attrs.class+'').find('input,textarea,select').each(function() {
                    that.fieldError(that,this,'submitValidated');
                    $(this).blur(function() {
                        that.fieldError(that,this,'blurValidated');
                    });
                });
            }); 
        }
    }
});