RightNow.namespace('Custom.Widgets.input.ProductCategorySelectMenu');
Custom.Widgets.input.ProductCategorySelectMenu = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
    	var that = this;
    	this.Y.augment(this, RightNow.Field);
    	this.Y.augment(this, RightNow.ProductCategory);

    	// Set up the first chosen menu
    	$('.chosen-product').chosen({ 
    		width: $('.chosen-product').css('width'),
    		disable_search: that.data['attrs']['disable_chosen_search'],
    		max_shown_results: that.data['attrs']['max_shown_results']
    	});
    	$('#'+this.data['attrs']['id_prefix']+'1_chosen').addClass('chosen_required');
		
		$('.chosen-product').each(function() {
			$(this).change(function() {
				var menuNumber = $(this).attr('id').replace(that.data['attrs']['id_prefix'], '');
				//console.log(menuNumber);
				var menuSelection = $(this).val();
				var menuText = $('.chosen-product option:selected').text();
				that.menuSelection(menuNumber, menuSelection, menuText);
				$(this).attr('aria-label', menuText);
				console.log(this);
			});
		});
		
		var selectionContainer = $('#'+this.data['attrs']['id_prefix']+'selectionLimitContainer');
		var finalInput = $('#'+this.data['attrs']['id_prefix']+'Selection');
		$('#'+this.data['attrs']['id_prefix']+this.data['attrs']['hierarchy_depth']).change(function() {
			selectionContainer.show();
			finalInput.val($(this).val());
			console.log('final=' + $(this).val())
			console.log('redirect=' + that.data['attrs']['auto_redirect_url']);
			if(that.data['attrs']['auto_redirect_url'] != "")
				window.location.href = that.data['attrs']['auto_redirect_url'] + "/c/" + $(this).val();
		});

		this._preloadingComplete = false;

		//setup event object
        this._eo = new RightNow.Event.EventObject(this, {data: {
            data_type: '',
            table: this.data.attrs.table,
            cache: [],
            name: this.data.attrs.name
        }});
		/* RightNow.Field.prototype.parentForm($('form').attr('id')).on('submit', this.onValidate, this); */
		
		RightNow.Event.subscribe("evt_resetMenus", this.resetMenus, this);
		RightNow.Event.subscribe("evt_preset"+this.data['attrs']['name']+"MenuValues", this.presetMenuValues, this);
    },

    resetMenus: function() {
    	$('.'+this.data['attrs']['id_prefix']).each(function() {
			$(this).val('').trigger("chosen:updated");
		});
		$('.'+this.data['attrs']['id_prefix']).not('#'+this.data['attrs']['id_prefix']+'1').each(function() {
			$(this).html('').trigger("chosen:updated");
		});
		$('.chosen-container[id^="'+this.data['attrs']['id_prefix']+'"]').show();
		$('#'+this.data['attrs']['id_prefix']+'selectionLimitContainer').hide();
		$('[id^='+this.data['attrs']['id_prefix']+']').removeClass('chosen_required');
		$('.'+this.data['attrs']['id_prefix']).attr('data-placeholder', this.data['attrs']['placeholderText']).prop('disabled', true).trigger("chosen:updated");
		$('#'+this.data['attrs']['id_prefix']+'1').prop('disabled', false).trigger("chosen:updated");
		$('#'+this.data['attrs']['id_prefix']+'1_chosen').addClass('chosen_required');
    },

    presetMenuValues: function(evt, args) {
    	this.data['attrs']['menuReady'] = true;
    	var that = this;
    	var eventData = args[0].data;
        if (eventData) {
        	for (h = 0; h < eventData.hierarchy.length; ++h) {
        		(function(index) {
        			var intervalId = setInterval(function() {
				        if(that.data['attrs']['menuReady']) {
				            that.data['attrs']['menuReady'] = false;
				            $('#'+that.data['attrs']['id_prefix']+(index+1)).val(eventData.hierarchy[index]).trigger('change').trigger("chosen:updated");
				             clearInterval(intervalId);
				        }
				    }, 100);
			    })(h);
			}
        }
    },

    menuSelection: function(menuNumber, selection) {
    	if(menuNumber < this.data['attrs']['hierarchy_depth']) {
    		var nextMenu = parseInt(menuNumber) + 1;
    		this.clearNextMenus(nextMenu);
    		this.getSubsequentMenus(nextMenu, selection);
    	}
    	$('#'+this.data['attrs']['id_prefix']+menuNumber+'_chosen').removeClass('chosen_required');
    },

    clearNextMenus: function(nextMenu) {
    	var i;
    	for (i = parseInt(nextMenu); i < (this.data['attrs']['hierarchy_depth']+1); ++i) {
			$('#'+this.data['attrs']['id_prefix']+i+'_chosen').find('span').html(this.data['attrs']['placeholder']);
			$('#'+this.data['attrs']['id_prefix']+i+'_chosen').addClass('chosen-disabled');
			$('#'+this.data['attrs']['id_prefix']+i).prop( "disabled", true);
		}
    },

    getSubsequentMenus: function(targetMenu, rootSelection) {
    	var that = this;
    	$.ajax({
			type: "POST",
			url: "/cc/ProdCatMenu/getSubs",
			data: {productID : rootSelection, name: that.data['attrs']['name'], model: this.data['attrs']['ajax_model']},
			datatype: "json",
			success: function(json) {
				var visibleOptions = false;
				// Check the show_ids array for the value, if the widget attribute is set
				$.each(json, function(ji, jv){
					// Check to see if ID's are specified to be shown and the rootSelection specifically
					(!that.data['attrs']['filter_ids'] || (that.data['attrs']['show_ids'].indexOf(json[ji]['id']) > -1)) ? (json[ji]['show'] = true, visibleOptions = true) : json[ji]['show'] = false;	
				});
				that.buildNewMenu(targetMenu, rootSelection, json, visibleOptions);
			},
			error: function() {
				console.log('an error occurred');
			}
		});
    },

    buildNewMenu: function(targetMenu, rootSelection, json, visibleOptions) {
    	// If the json object is not null or there are in fact visible menu options
    	if((json !== null) && (visibleOptions)) {
    		// Show the next menu
			for (k = targetMenu; k < (this.data['attrs']['hierarchy_depth']+1); ++k) {
				$('#'+this.data['attrs']['id_prefix']+k+'_chosen').show();
			}

			// Hide the selectionLimitContainer and enable the next select menu and empty it to prepare for the appropriate options to be appended
			$('#'+this.data['attrs']['id_prefix']+'selectionLimitContainer').hide();
    		$('#'+this.data['attrs']['id_prefix']+targetMenu).prop('disabled', false).html('');

    		var i;
    		// Add the empty option element to hold the chosen placeholder
			$('#'+this.data['attrs']['id_prefix']+targetMenu).append('<option value=""></option>');

			// Traverse the json object and append the respective option values to the select menu
			for (i = 0; i < json.length; ++i) {
				if(json[i]['show'] == true)
			    	$('#'+this.data['attrs']['id_prefix']+targetMenu).append('<option value="'+json[i]['id']+'">'+json[i]['label']+'</option>');
			}

			this._eo.data.label = $('#'+this.data['attrs']['id_prefix']+(targetMenu-1)+'_chosen a span').text();
			//$('selectionLimitContainer').attr('aria-label', this._eo.data.label)
			console.log(this._eo.data.label );
			
			// Update the chosen menu placeholder
			$('#'+this.data['attrs']['id_prefix']+targetMenu).attr('data-placeholder', "Select a "+$('#'+this.data['attrs']['id_prefix']+(targetMenu-1)+'_chosen a span').text() + " " + this.data['attrs']['default_label']).trigger("chosen:updated");
			
			// Empty the input field
			$('#'+this.data['attrs']['id_prefix']+'Selection').val(null);

			// If a selection is required add the required class to the chosen element
			if(this.data['attrs']['required']) {
				$('#'+this.data['attrs']['id_prefix']+targetMenu+'_chosen').addClass('chosen_required');

				// Set the validation label for form field validation
				$('#'+this.data['attrs']['id_prefix']+'Selection').attr('validation_label',$('#'+this.data['attrs']['id_prefix']+(targetMenu-1)+'_chosen').find('span').text() + " " + this.data['attrs']['default_label']);
				//console.log(('#'+this.data['attrs']['id_prefix']+(targetMenu-1)+'_chosen'));
			}
			this.data['attrs']['menuReady'] = true;
    	} else {
			// Check if a preset product or Category is defined and fire the event to preset the menus
	        if((Object.keys(this.data['attrs']['incident_product_preset_array']).length > 1) && (this.data['attrs']['incident_id'] > 0) && !this._preloadingComplete) {
	        	var ma = new RightNow.Event.EventObject();
	            ma.data.hierarchy = this.data['attrs']['incident_product_preset_array'];
	        	RightNow.Event.fire("evt_preset"+this.data['attrs']['name']+"MenuValues", ma);
	        	//console.log("evt_preset"+this.data['attrs']['name']+"MenuValues", ma);
	        }
    		var j;
    		// Hide the remaining chosen menus since the last available selection has been made for that root
			for (j = targetMenu; j < (this.data['attrs']['hierarchy_depth']+1); ++j) {
				$('#'+this.data['attrs']['id_prefix']+j+'_chosen').hide();
				//$('#'+this.data['attrs']['id_prefix']+j+'_chosen').attr('aria-label', '#'+this.data['attrs']['id_prefix']+(targetMenu-1)+'_chosen');
				
			}

			// Clean up the environment and set the value of the input field for submission
			$('#'+this.data['attrs']['id_prefix']+targetMenu+'_chosen').removeClass('chosen_required');
			$('#'+this.data['attrs']['id_prefix']+'selectionLimitContainer').show();
			$('#'+this.data['attrs']['id_prefix']+'Selection').val(rootSelection);
			this._preloadingComplete  = true;
    	}
    },

    checkSelectionErrors: function() {
    	if(this.data['attrs']['required'] && ($('#'+this.data['attrs']['id_prefix']+'Selection').val() == "undefined")) {
    		return false;
    	}
    	return true;
    },

    onValidate: function(type, args) {
    	var formEventObject = this.createEventObject();
    	this._errorLocation = this.lastErrorLocation = args[0].data.error_location;

    	if (this.checkSelectionErrors()) {
	        formEventObject.data.value = $('#'+this.data['attrs']['id_prefix']+'Selection').val() || null;

	        if (formEventObject.data.required) {
	            formEventObject.data.required = false;
	        }

	        RightNow.Event.fire("evt_formFieldValidatePass", formEventObject);
	        return formEventObject;
	    } else {
	    	RightNow.Event.fire("evt_formFieldValidateFailure", this._eo);
        	return false;
	    }
    },

    _addEventHandler: function(eventName, handler) {
        this._eventHandlers[eventName] = handler;
        return this;
    }
    
});