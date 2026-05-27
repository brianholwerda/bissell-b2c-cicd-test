RightNow.namespace('Custom.Widgets.input.TextInputDynamic');
Custom.Widgets.input.TextInputDynamic = RightNow.Widgets.TextInput.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.TextInput#constructor.
         */
        constructor: function() {
             // Call into parent's constructor
			this._prependedText         = false;
            this._callJSFuncOnce        = true;
            this._tooltip               = null;
            this._overlay               = null;
            this._moveTooltipBreakpoint = 700;
            this._tooltipPosition       = (window.innerWidth > this._moveTooltipBreakpoint) ? 'right top' : 'top right';
            this._tooltipMaxWidth       = '300px';

            $(window).resize(this.renderOverlay.bind(this));
            document.addEventListener('DOMContentLoaded', this.renderOverlay.bind(this));

			this.parent();
			//this.input.setAttribute('placeholder', this.data.attrs.label_input);
			//subscribe to custom event dyanmic prod cat selection.  To set required Attribute
			RightNow.Event.subscribe("evt_dynamicProductCategorySelected", this._setAttributes, this);
        },
		onValidate: function(type, args) {
			//added code to set two custom fields based on category select custom widget.  Easiest way to do it
			var inputNameID = this._inputSelector;
			if(inputNameID.indexOf('cp_subject') > -1)
			{
				var sel1ObjectText = $("select[name='Category_SelectObject_lvl1'] option:selected" ).text();
				$("input[name='Incident.CustomFields.c.cp_subject']").val(sel1ObjectText);
				//console.log("Value of input is " + $("input[name='Incident.CustomFields.c.cp_subject']").val() + " tyring to set to " + sel1ObjectText);
			}
			else if(inputNameID.indexOf('cp_topic') > -1)
			{
				var sel2ObjectText = $("select[name='Category_SelectObject_lvl2'] option:selected" ).text();
				$("input[name='Incident.CustomFields.c.cp_topic']").val(sel2ObjectText);
				//console.log("Value of input is " + $("input[name='Incident.CustomFields.c.cp_subject']").val() + " tyring to set to " + sel1ObjectText);
			}



			var eventObject = this.createEventObject(),
				errors = [];

			this.toggleErrorIndicator(false);

			if(!this.validate(errors) || (this.data.attrs.require_validation && !this._validateVerifyField(errors)) || !this._compareInputToMask(true)) {
				this.lastErrorLocation = args[0].data.error_location;
				this._displayError(errors, this.lastErrorLocation);
				RightNow.Event.fire("evt_formFieldValidateFailure", eventObject);
				return false;
			}

			RightNow.Event.fire("evt_formFieldValidatePass", eventObject);
			this._seenValues.push(this._massageValueForModificationCheck(eventObject.data.value));
			return eventObject;
		},
        /**
        *   Using tether.io's tooltip class
        */
        _initializeHint: function() {
            if(!this.data.attrs.always_show_hint)
            {
                if(this.Y.Overlay) {
                    this._overlay = this.Y.Node.create("<span id='" + this.baseDomID + "_Hint' class='rn_HintBox' aria-hidden='true'>" + this.data.attrs.hint + "</span>");

                    this.renderOverlay();
                }
                else {
                    //display hint inline if YUI container code isn't being included or the overlay is always being shown
                    this.input.get("parentNode").append(this.Y.Node.create("<span class='rn_HintText' aria-hidden='true'>" + this.data.attrs.hint + "</span>"));
                }
            }
        }

        /**
         * Overridable methods from TextInput:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // onValidate: function(type, args)
        // _displayError: function(errors, errorLocation)
        // _toggleErrorIndicator: function(showOrHide, fieldToHighlight, labelToHighlight)
        // _toggleFormSubmittingFlag: function(event)
        // _blurValidate: function(event, validateVerifyField)
        // _validateVerifyField: function(verifyField, errors)
        // _checkExistingAccount: function()
        // _massageValueForModificationCheck: function(value)
        // _onAccountExistsResponse: function(response, originalEventObject)
        // onProvinceChange: function(type, args)
        // _initializeMask: function()
        // _createMaskArray: function(mask)
        // _getSimpleMaskString: function()
        // _compareInputToMask: function(submitting)
        // _showMaskMessage: function(error)
        // _setMaskMessage: function(message)
        // _showMask: function()
        // _hideMaskMessage: function()
        // _onValidateFailure: function()
    },

    renderOverlay: function(){
        if(!this._overlay)
            return;

        if(this._tooltip){
            var clone = this._overlay.cloneNode(true);
            this._tooltip.destroy();
            this._overlay = clone;
        }


        if(window.innerWidth < this._moveTooltipBreakpoint){
            this._tooltip = new Tooltip({
                target: this.input._node,
                content: this._overlay._node,
                position: 'top right',
                classes: 'tooltip-theme-arrows',
                tetherOptions: {
                    constraints: [{
                        to: document.getElementById('rn_QuestionSubmit')
                    }]
                }
            });
            this._overlay.setStyle('maxWidth', '');
        }
        else{
            this._tooltip = new Tooltip({
                target: this.input._node,
                content: this._overlay._node,
                position: 'right top',
                classes: 'tooltip-theme-arrows',
                tetherOptions: {
                    constraints: [{
                        to: document.getElementById('rn_QuestionSubmit')
                    }]
                }
            });
            this._overlay.setStyle('maxWidth', this._tooltipMaxWidth);
        }
        this._overlay.setStyle('width', this.input._node.clientWidth + 'px');

    },

    /**
     * Sample widget method.
     */
    _setAttributes: function(type, args)
    {
		var evtObj = args[0],
            tempNode;
		var hierMenuID = evtObj.data.hierChain + '';
		var CFfieldConfig = evtObj.data.CFfieldConfig;
		var CFfieldMeta = evtObj.data.CFfieldMeta;
 		//console.log("In Text Dynamic listner and have " + evtObj.data.toSource());
		var cfList = reqList = new Array();

		this._setDisplay(false);
		this._clearHint();
        this.renderOverlay();

		//this._callJSFuncOnce = true;
		var foundLookup = false;


		var returnNotFound = "NOT FOUND:" + this._fieldName + " ";
		if(typeof(CFfieldConfig) != 'undefined')
		{
			for(var i = 0; i < CFfieldConfig.length; i++)
			{
				var hierMenus = hierMenuID.split(",");
				var cfID = CFfieldConfig[i]['field_list'];
				// console.log("TEXT WIDGET: "  + this.baseDomID + " Comparing evetData " + CFfieldConfig[i][evtObj.data.dataType] + " TO hierMenu" + hierMenuID + " and looking at " + cfID);
				for(var h = 0, type; h < hierMenus.length; h++)
				{
					//console.log("in for loop # " + h + " and checking field config i = " + i + " of " + CFfieldConfig[i][dataTypeLower] + " to " + hierMenus[h]);
					if(CFfieldConfig[i][evtObj.data.dataType]  == hierMenus[h])
					{
						//console.log("in TEXTINPUT for loop and checking field config i = " + i + " of " + CFfieldConfig[i][evtObj.data.dataType] + " to " + hierMenus[h] + " so should be trying to show fieldlist= " + CFfieldConfig[i]['field_list'] + " and meta data is " +  CFfieldMeta[cfID].toSource() + " and field name is " + this._fieldName + " with required list " + CFfieldConfig[i]['required_list'] + " with displayHint of " + CFfieldConfig[i]['displayHintFields']);
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
									 //console.log("field name is " + this._fieldName + " With HINT OF " + showHint);
									this._setHint(showHint); //this.data.attrs.required = true;
								}

							}
							else
								returnNotFound = returnNotFound + " " + cfFieldLookup + " ";

						}
						catch(error)  {
							//alert(" Error Handling did not work");
							//console.log(this.data.js.fieldMeta.toSource());
							//console.log(this.data.js.fieldConfig.toSource());

						}
					}
				}
			} //end of for
			//if(!foundLookup)
			//	console.log(returnNotFound);
		}
		//else
		//	console.log("For some reason evt was undefined");

    },
	_setDisplay: function(doShow) {

		this.data.attrs.required = doShow;
		var parentDiv = $('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').closest("div");
		var hasSpan = false;
 		if($('label span.rn_Required',parentDiv).length == 1)
			hasSpan=true;

		if(doShow)
		{
			if(hasSpan)
			{
				console.log('inside hasSpan');
				$('label span.rn_Required',parentDiv).removeClass( "rn_Hidden" );
			}
			else
			{
				console.log('inside hasSpan else');	
				$('label',parentDiv).append( '<span class="rn_Required"> *</span>' );

			}
			//console.log("inside doshow");
			/* pHT = $('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').attr('placeholder');
			console.log(pHT);
			
			if (pHT.indexOf(" *") >= 0)
				$('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').attr("placeholder", pHT);
			else {
				pHT = pHT + " *";
				$('input[name=\'' + this._fieldName + '\'], textarea[name=\'' + this._fieldName + '\']').attr("placeholder", pHT);
			} */
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
		if(dynamicID == 'Incident.CustomFields.c.item__PrependText'){
			var elem = document.getElementById(dynamicID);
			if(typeof elem !== 'undefined' && elem !== null) {
				document.getElementById(dynamicID).innerHTML = "";
 				//console.log("In clear with dynamic ID of " + dynamicID + " WITH HTML OF " + document.getElementById(dynamicID).innerHTML );
			}
		}
		//$("#" + dynamicID).html(" ");
	},
    /**
     * Makes an AJAX request for `get_config_ajax`.
     */
    getGet_config_ajax: function($hierChain) {
        // Make AJAX request:


        var eventObj = new RightNow.Event.EventObject(this, {data:{
            w_id: this.data.info.w_id,
			fieldName: 'HI',
            // Parameters to send
        }});
        RightNow.Ajax.makeRequest(this.data.attrs.get_config_ajax, eventObj.data, {
            successHandler: this.get_config_ajaxCallback,
            scope:          this,
            data:           eventObj,
            json:           true
        });
    },

    /**
     * Handles the AJAX response for `get_config_ajax`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getGet_config_ajax
     */
    get_config_ajaxCallback: function(response, originalEventObj) {
        // Handle response
    },
	testFunction: function()
	{
		alert("In Test Function");

	}
});
