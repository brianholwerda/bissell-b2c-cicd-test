RightNow.namespace('Custom.Widgets.input.SelectionInputDynamic');
Custom.Widgets.input.SelectionInputDynamic = RightNow.Widgets.SelectionInput.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.SelectionInput#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
			//subscribe to custom event dyanmic prod cat selection.  To set required Attribute
			RightNow.Event.subscribe("evt_dynamicProductCategorySelected", this._setAttributes, this);
			//Added Logic to display this menu as radio button
			if(this._fieldName == 'Incident.CustomFields.c.preferred_solution')
				this._displayAsRadio();
        },

        /**
         * Overridable methods from SelectionInput:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // onValidate: function(type, args)
        // displayError: function(errors, errorLocation)
        /**
	     * Adds / removes the error indicators on the
	     * field and label.
	     * @param {Boolean} showOrHide T to add, F to remove
	     */
	    toggleErrorIndicator: function(showOrHide) {
	        var method = ((showOrHide) ? "addClass" : "removeClass");
	        this.input[method]("rn_ErrorField");
	        this.Y.one(this.baseSelector + "_Label")[method]("rn_ErrorLabel");
	        console.log(method);
	        if(this._fieldName == 'Incident.CustomFields.c.preferred_solution') {
		        $(".rn_radio_option_span")[method]("rn_ErrorField");
		    }
		    
    }

        // blurValidate: function()
        // countryChanged: function()
        // successHandler: function(response)
        // onProvinceResponse: function(type, args)
    },

	_setAttributes: function(type, args)
    {
		var evtObj = args[0],
            tempNode;
		var hierMenuID = evtObj.data.hierChain  + '';
		var CFfieldConfig = evtObj.data.CFfieldConfig;
		var CFfieldMeta = evtObj.data.CFfieldMeta;
 		//console.log("In Text Dynamic listner and have " + evtObj.data.toSource());
		var cfList = reqList = new Array();
						 
		this._setDisplay(false);
		this._clearHint();

							 
		var foundLookup = false;
		var returnNotFound = "NOT FOUND:" + this._fieldName + " ";
		if(typeof(CFfieldConfig) != 'undefined')
		{
			for(var i = 0; i < CFfieldConfig.length; i++) 
			{
				var cfID = CFfieldConfig[i]['field_list'];
				var hierMenus = hierMenuID.split(",");
				//console.log("hier menu length is " + hierMenus.length);
				for(var h = 0, type; h < hierMenus.length; h++) 
				{
					//console.log("in for loop # " + h + " and checking field config i = " + i + " of " + CFfieldConfig[i][dataTypeLower] + " to " + hierMenus[h]);
					if(CFfieldConfig[i][evtObj.data.dataType]  == hierMenus[h])
					{ 
						//console.log("in SELECTINPUT for loop and checking field config i = " + i + " of " + CFfieldConfig[i][evtObj.data.dataType] + " to " + hierMenus[h] + " so should be trying to show fieldlist= " + CFfieldConfig[i]['field_list'] + " and meta data is " +  CFfieldMeta[cfID].toSource() + " and field name is " + this._fieldName + " with required list " + CFfieldConfig[i]['required_list']);
						try {
							var cfFieldLookup = CFfieldMeta[cfID]; 
							if(cfFieldLookup == this._fieldName)
							{
								foundLookup = true;
								var isRequired = CFfieldConfig[i]['required_list'];
								if(isRequired > 0)
								{
									this._setDisplay(true); //this.data.attrs.required = true;
								}
								//Added Code to show hint
								var showHint = CFfieldConfig[i]['displayHintFields'];
								if(showHint.length > 0)
								{
									//console.log(showHint);
									this._setHint(showHint); //this.data.attrs.required = true;
								}

								//console.log("In Selection # " + this.baseDomID + " FieldName: " + this._fieldName + " Compared to Lookup " + cfFieldLookup + " and required: " + isRequired);
							}
							else
								returnNotFound = returnNotFound + " " + cfFieldLookup + " ";
								 
						}
						catch(error)  {
							//alert(" Error Handling did not work");
						//	console.log(this.data.js.fieldMeta.toSource());
							//console.log(this.data.js.fieldConfig.toSource());

						}
					}
				}
			} //end of for
			//if(!foundLookup)
			//	console.log(returnNotFound);
		}
		//else
			//console.log("For some reason evt was undefined");
 		
    },
	_setHint: function(hintHTML) { 
		var parentDiv = $('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').closest("div");
		//var hintDiv = $('input[name=\'' + this._fieldName + '\']').closest("span .rn_HintBox");
		var dynamicID =  this._fieldName + "__PrependText";
		var displayHTML = "<div id=\"" + dynamicID + "\" class=\"rn_prependText\" >" + hintHTML + "</div>";
	
		var elementExists = document.getElementById(dynamicID);
		//console.log("In set hint for " + dynamicID + " and TYPE OF IS " + elementExists + " and length is " + $("#" + dynamicID).length   );
	 
		if(elementExists  == null){
			$(parentDiv).prepend(displayHTML);
 		}
		else
		{
 			elementExists.innerHTML = hintHTML;
		}
		 
	},
	_clearHint: function() { 
		var parentDiv = $('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').closest("div");
 		var dynamicID =  this._fieldName + "__PrependText";
		$("#" + dynamicID).html(" ");
	},
	_setDisplay: function(doShow) {
	
		this.data.attrs.required = doShow;
		var parentDiv = $('select[name=\'' + this._fieldName + '\']').closest("div");
		var hasSpan = false;
 		if($('label span.rn_Required',parentDiv).length == 1) 
			hasSpan=true;
		 
		if(doShow)
		{
			if(hasSpan)
			{
				$('label span.rn_Required',parentDiv).removeClass( "rn_Hidden" );
			}
			else
			{
				if($('label span.rn_Required',parentDiv).length == 0)
					$('label',parentDiv).append( '<span class="rn_Required"> *</span>' );
				 
			}
		}
		else
		{
			//hiding span if existing
			//$( foundDiv + ":has(span)" ).addClass( "rn_Hidden" );
			if(hasSpan)
			{
				$('span.rn_Required',parentDiv).addClass( "rn_Hidden" );
			}
		}
	},
	_displayAsRadio: function() {

	     //Get Exising Select Options    
		 
		
		$('select[name=\'' + this._fieldName + '\']').each(function(i, select){
		var $select = $(select);
		var RadioName = 'RADIO_' + $select.attr('name');
		var SelectName = $select.attr('name');
		$select.after( $("<span id='rn_radio_span' />"));
		$select.find('option').each(function(j, option){
			var $option = $(option);
			// Create a radio:
			//console.log("option val is "  + $option.val());
			//console.log("option txt is "  + $option.text());
			if($option.val() != '')
			{
				var radioOptSpan = 'rn_radio_option_span_' + $option.val();
				$('#rn_radio_span').append($("<span class='rn_radio_option_span' id='" + radioOptSpan + "' />"));
				var $radio = $('<input type="radio" />');
				// Set name and value:
				$radio.attr('name',RadioName).attr('value', $option.val());
 				// Set checked if the option was selected
				if ($option.attr('selected')) $radio.attr('checked', 'checked');
				// Insert radio before select box:
				$('#' + radioOptSpan).append($radio);
				// Insert a label:
				$('#' + radioOptSpan).append(
				  $("<label class='rn_radio_label' />").attr('for', RadioName).text($option.text())
				);
				//$('#rn_radio_span').append('<br />');
				// Insert a <br />:
				//$select.before("<br/>");
			}
		});
		 
		var that = this;
 		$('input[name=\'' + RadioName + '\']').change(function(){
			$('input[name=\'' + RadioName + '\']').each(function(){
				$(this).parent().css( "background-color", "#eeeeee" );

			});
			$(this).parent().css( "background-color", "white" );
			var value = $( 'input[name=\'' + RadioName + '\']:checked' ).val();
			//console.log("Got radio value of " + value + " for radio " + RadioName + " now setting field name " + SelectName);
			$('select[name=\'' + SelectName + '\']').val(value);
		});

		 
		 $select.hide();
});
	}

});