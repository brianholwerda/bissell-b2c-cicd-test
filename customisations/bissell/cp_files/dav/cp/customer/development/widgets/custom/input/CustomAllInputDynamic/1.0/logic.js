RightNow.namespace('Custom.Widgets.input.CustomAllInputDynamic');
Custom.Widgets.input.CustomAllInputDynamic = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {

		RightNow.Event.subscribe("evt_productCategorySelected", this._setDynamicForm, this);
		this.custDiv = this.baseDomID;//this.data.attrs.div_id;
		//console.log("FieldMeta:" + this.data.js.fieldMeta.toSource());
		//console.log("Field Config:" + this.data.js.fieldConfig.toSource());
		jQuery("#rn_submitAAQ_section").hide();
		this._runFunctionBoolean = true;
		this._divObject = document.getElementById(this.custDiv);
		this._callJSFuncOnce = true;
		this._dispMsg = document.getElementById(this.baseDomID + "_DisplayMessage");
		this._hideAllElements();
     },

    /**
     * _setDynamicForm   method to hide or display certain fields based on CO relationship.
     */
    _setDynamicForm: function(type, args)
    { 
		if(this._runFunctionBoolean)
		{	
			//console.log("Inside Set Dynamic Form");
			this._runFunctionBoolean = false;
			jQuery("#rn_submitAAQ_section").hide();
 			var evtObj = args[0],
				tempNode;
	 
			var cfFields = this.data.fields;
			var dataType = this.data.attrs.data_type;
			var CFfieldMeta = this.data.js.fieldMeta;
			var CFfieldConfig = this.data.js.fieldConfig;
			var hierMenuID = evtObj.data.hierChain + '';
			var dataTypeLower = dataType.toLowerCase(); 
			var cfList,reqList='';
			var dispMsg = '';
			this._hideAllElements();
			
			//console.log("IN select node with " + hierMenuID + " and cflied config " + CFfieldConfig.length);
			//first call hide all elements.  Then check to see if selected hierMenu has configs to display certain fields
			if(typeof(CFfieldConfig) != 'undefined' && typeof(hierMenuID) != 'undefined')
			{
				for(var i = 0; i < CFfieldConfig.length; i++) 
				{
					 
					var hierMenus = hierMenuID.split(",");
					//console.log("hier menu length is " + hierMenus.length);
					for(var h = 0, type; h < hierMenus.length; h++) 
					{
						 
						//console.log("in for loop and checking field config i = " + i + " of " + CFfieldConfig[i][dataTypeLower] + " to " + hierMenus[h] + " so should be trying to show fieldlist= " + CFfieldConfig[i]['field_list'] );
						if(CFfieldConfig[i][dataTypeLower] == hierMenus[h])
						{
							 
							dispMsg = CFfieldConfig[i]['prodcatDisplayMsg'];
					
							try {
								if(CFfieldConfig[i]['field_list'] != null)
									this._showElement(CFfieldConfig[i]['field_list'],CFfieldMeta);
								
								//console.log("You chose index " + hierMenus[h] + " and fields of " + CFfieldConfig[i]['field_list'] + " with required" + CFfieldConfig[i]['required_list']); //evtObj.data.value
								//Added Code to call custom JS function within this Logic.js file
								var JSFunctionCall = CFfieldConfig[i]['callJSFunction'];
								//console.log("Field is is " + CFfieldConfig[i]['field_list']+ " func call is " + JSFunctionCall.length);
								if(JSFunctionCall != null )
								{ 
									 //console.log("Field is is " + CFfieldConfig[i]['field_list']+ " func call is " + JSFunctionCall.length);
								//	this._callJSFuncOnce = false;
									try {
										window[JSFunctionCall]();
									} 
									catch(error)  
									{
									}
								}
								else if (JSFunctionCall == null )
								{
									//show the submit section for any row that does NOT have a specific FUNCTION Call
									//console.log("Found NULL JS function for " + CFfieldConfig[i][dataTypeLower] + " with JS func call = " + JSFunctionCall);							
									jQuery("#rn_submitAAQ_section").show();
								}
							} 
							catch(error)  {
								//console.log(" Error Handling did not work");
								//console.log(this.data.js.fieldMeta.toSource());
								//console.log(this.data.js.fieldConfig.toSource());

							}
						}
					}
				}
				jQuery(this._divObject).show();
				if(dispMsg	!= null)
					this._dispMsg.innerHTML = dispMsg;
				
			 
			}
			var eo = new RightNow.Event.EventObject();
			eo.data = {"hierChain": hierMenuID,"CFfieldConfig":CFfieldConfig,"CFfieldMeta":CFfieldMeta,"dataType":dataTypeLower};
			RightNow.Event.fire("evt_dynamicProductCategorySelected", eo);

		}//end check for this._runFunctionBoolean
		else
		{
			this._runFunctionBoolean = true;
		}
    },
	_showElement: function(cfName,CFfieldMeta)
	{   
		//console.log('inside _showElement');
		var showElem = CFfieldMeta[cfName];
		//console.log("trying to show " + showElem + " and parent ID is : " + jQuery('select[name=\'' + showElem + '\']').closest("div").attr('id') + " and cfName = " + cfName  + " and MetaData = " + CFfieldMeta.toSource());
		jQuery('select[name=\'' + showElem + '\']').closest("div").show();
		jQuery('input[name=\'' + showElem + '\']').closest("div").show();
		jQuery('textarea[name=\'' + showElem + '\']').closest("div").show();
		//special case for DATE widget
		jQuery('select[name=\'' + showElem + '.year\']').closest("div").show();
 
	},
	_hideAllElements: function()
	{
		//console.log('inside _hideAllElements');
		this._dispMsg.innerHTML = "";

		var foundForm = false;
		var formID = '';
		this._clear_form_elements();
		jQuery("#" + this.custDiv + " :input").each(function (i) {
			jQuery(this).closest("div").hide();
		});
		if(jQuery('#rn_ErrorMessageText').css('display') == 'block') {
			jQuery('#rn_ErrorMessageText').css('display', 'none');
			jQuery('.rn_ErrorField').removeClass('rn_ErrorField');
		}
		if(jQuery('#rn_ErrorLocation').css('display') == 'block')
		{
			jQuery('#rn_ErrorLocation').empty(); 
			jQuery('#rn_ErrorLocation').removeClass( "rn_MessageBox" );
			jQuery('#rn_ErrorLocation').removeClass( "rn_ErrorMessage" );
			 
		}
		

	},
	_clear_form_elements: function () { 
		//console.log('inside _clear_form_elements');
		//document.getElementById("rn_QuestionSubmit").reset();
		//jQuery("#" + this.custDiv).find(':input').each(function() {
	 
	 	jQuery("#rn_DynamicForm").find(':input').each(function() {
			  
			switch(this.type) {
				case 'password':
				case 'text':
				case 'textarea':
				case 'select-one':
				case 'select-multiple':
				case 'file':
					jQuery(this).val('');
					jQuery(this).removeAttr('required');
					break;
				case 'checkbox':
				case 'radio':
					this.checked = false;
					break;
				case 'file':
					console.log("In case == FILE and LENGTH == " + jQuery(this).val());
					if (jQuery(this).val()) {
						
						//jQueryCloneValue = jQuery(this).val('').clone(true);
						 jQuery(this).replaceWith(jQuery(this).val('').clone(true));
						//jQuery(this).removeAttr('required');
						//jQuery(this).replaceWith(jQueryCloneValue );
						
					}
					 break;
			}
		});
		 
		 
	},
	_clearFileInput: function () {
		var input = jQuery( "input:file" )
	  if (!input) {
		return;
	  }

	  // standard way - works for IE 11+, Chrome, Firefox, webkit Opera
	  input.value = null;

	  if (input.files && input.files.length && input.parentNode) {
		// workaround for IE 10 and lower, pre-webkit Opera

		var form = document.createElement('form');
		input.parentNode.insertBefore(form, input);

		form.appendChild(input);
		form.reset();

		form.parentNode.insertBefore(input, form);
		input.parentNode.removeChild(form);
	  }

	},
	
	_getFieldNameID: function(fieldName) {
        //returns ID of last element with that NAME
        var foundElemArray = document.getElementsByName(fieldName);
        if (foundElemArray.length > 0) {
            var lastElemIndex = foundElemArray.length - 1;
            return foundElemArray[lastElemIndex].id;
        } else
            return false;

    },
    _getFieldNameValue: function(fieldName) {
        //this assumes LAST element with name and returns its value
        var foundElemArray = document.getElementsByName(fieldName);
        if (foundElemArray.length > 0) {
            var lastElemIndex = foundElemArray.length - 1;
            return foundElemArray[lastElemIndex].value;
        } else
            return false;
    },
    _setFieldName: function(fieldName, value) {
        //this LOOPS thru ALL elements with same name (in case more than 1) and sets them to value
        if (typeof(value) != 'undefined') {
            var foundElemArray = document.getElementsByName(fieldName);
            if (foundElemArray.length > 0) {
                for (l = 0; l < foundElemArray.length; l++) {
                    var lastElemIndex = l; //foundElemArray.length -1;
                    if (this._debug)
                        console.log("In loop for setfieldName of " + fieldName + " at l with ID of " + foundElemArray[lastElemIndex].id);

                    if (foundElemArray[lastElemIndex].type == "text") {
                        foundElemArray[lastElemIndex].value = value;
                    }
                    if (foundElemArray[lastElemIndex].type == "checkbox") {
                        if (value == 1)
                            foundElemArray[lastElemIndex].checked = true;
                    }
                    if (foundElemArray[lastElemIndex].type == "radio") {
                        jQuery(function() {
                            var $radios = jQuery('input:radio[name="' + fieldName + '"]');
                            if ($radios.is(':checked') === false) {
                                $radios.filter('[value=' + value + ']').prop('checked', true);
                            }
                        });
                    }
                    var elemID = foundElemArray[lastElemIndex].getAttribute('id');
                    //call that elements CHANGE function

                    jQuery("#" + elemID).trigger("change"); //.change();
                }
            }
        }
    }

}); 