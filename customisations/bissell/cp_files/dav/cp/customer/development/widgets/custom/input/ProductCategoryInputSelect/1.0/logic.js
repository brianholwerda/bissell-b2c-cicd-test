RightNow.namespace('Custom.Widgets.input.ProductCategoryInputSelect');
Custom.Widgets.input.ProductCategoryInputSelect = RightNow.Widgets.ProductCategoryInput.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.ProductCategoryInput#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
			// cant subscribe to this event as it gets cached so have to make ajax call each time in function below
			//RightNow.Event.on("evt_menuFilterGetResponse", this._setSelectLevel2, this);
 			 
			this.selectObject1 = this.Y.one(this.baseSelector + "_" + this.data.js.data_type + "_SelectObject_lvl1");
			this.selectObject2 = this.Y.one(this.baseSelector + "_" + this.data.js.data_type + "_SelectObject_lvl2");
			this.parent();
  
			if (this.data.attrs.display_as_select) {
                this.selectObject1.on("change", this._setSelectLevel1, this);
				this.selectObject2.on("change", this._setProductTree2, this);
				$('.rn_ProductCategoryInput button').hide();
				$('.rn_ProductCategoryInput .rn_Label').hide();

				
            }
        } 
		/**
		 * Builds the EventObject for the next sub-level of items from the server.
		 * Overrides RightNow.ProductCategory.getSubLevelRequestEventObject.
		 * @param {object} expandingNode The node that's expanding
		 * @return {object|undefined} EventObject instance if the request should be made
		 
		getSubLevelRequestEventObject: function(expandingNode) {
			this._eo.data.level = expandingNode.depth + 1;
			this._eo.data.value = expandingNode.value;
			this._eo.data.label = expandingNode.label;

			this._eo.data.reset = false; //whether data should be reset for the current level
			if (this._eo.data.linking_on) {
				//prod linking
				if (this.data.js.data_type === "Category") {
					if (expandingNode.loaded) {
						//data's already been loaded
						return;
					}
					this._eo.data.reset = (this._eo.data.value < 1);
				}
				else if (this._eo.data.value < 1 && this.data.js.data_type === "Product") {
					//product was set back to all: fire event for categories to re-show all
					this._nodeBeingExpanded = false;
					RightNow.Event.fire("evt_menuFilterGetResponse", new RightNow.Event.EventObject(this, {data: {
						reset_linked_category: true,
						data_type: "Category",
						reset: true
					}}));
					return;
				}
			}

			if (this.data.js.link_map) {
				//pass link map (prod linking) to EventBus for first time
				this._eo.data.link_map = this.data.js.link_map;
				delete this.data.js.link_map;
			}

			this._requestingParent = this._eo.data.value;

			return this._eo;
		},
*/
        /**
         * Overridable methods from ProductCategoryInput:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // _initializeHint: function()
        // buildPanel: function ()
        // _resetProductCategoryMenu: function()
        // _updatePermissionedHierData: function (dataType)
        // displaySelectedNodesAndClose: function(focus, fireSelectionEvent)
        // selectNode: function(node)
        // getSubLevelRequest: function (expandingNode)
        // getSubLevelRequestEventObject: function(expandingNode)
        // getSubLevelResponse: function(type, args)
        // _setButtonClick: function()
        // _onValidate: function(type, args)
        // _createHintElement: function(visibility)
        // _toggleHint: function(hideOrShow)
        // _realignHint: function(delay)
        // swapLabel: function(container, requiredLevel, label, template)
        // updateRequiredLevel: function(evt, constraint)
        // _checkSelectionErrors: function()
        // _removeErrorMessages: function()
        // _displayErrorMessage: function(message, currentNode)
    },

    /**
     * Sample widget method.
     */
    _setSelectLevel1: function() {
		//var opt = this.selectObject1.options[this.selectObject1.selectedIndex];
		
		var sel = this.selectObject1;
		var that=this;
		var selValue =  sel.get("value");
		var selText =  sel.get("text");

		
		var selText =  sel.get('options').item(sel.get('selectedIndex')).get('text');
		this.tree.selectNodeWithValue(selValue, true);
		var sel2Options = $( this.baseSelector + "_" + this.data.js.data_type + "_SelectObject_lvl2")[0].options;
		sel2Options.length = 1;
		
		this.selectNode(this.tree.getFocusedNode());
		var tempNode = this.tree.getNodeByValue(selValue);
		if (tempNode)
			this.selectNode({node: tempNode});
 
		//setup event object
		if(selValue > 0)
		{
			var hierEvent = new RightNow.Event.EventObject(this, {data: {
			  
				linking: this.data.js.linkingOn,
				filter: this.data.js.data_type,
					id: selValue 
				 
			}});

			RightNow.Ajax.makeRequest("/ci/ajaxRequestMin/getHierValues", hierEvent.data, {
					successHandler: this._setSelectLevel2,
					data: hierEvent,
					scope: this,
					json: true,
					type: "GETPOST"
				});

	 
			//console.log("You selected " + selValue + " with text of " + selText );
		}
    }, 
 
  
	_setSelectLevel2: function(results, eventObject) {
        var  tempNode; 
 		results = results.result;
		if (!results) return;
  	 	var sel2 = this.selectObject2;
		var sel2Options = $( this.baseSelector + "_" + this.data.js.data_type + "_SelectObject_lvl2")[0].options;
		//	sel2Options.length = 0;
		var sel2Data = results[0];
	 
	 	for (var key in sel2Data) 
		{
 			var obj = sel2Data[key];
			$( this.baseSelector + "_" + this.data.js.data_type + "_SelectObject_lvl2").append($('<option>', {
				value: obj["id"] ,
				text: obj["label"] 
			}));
			//console.log(obj["label"]);
			
			//console.log("obj of key " + key + " is " + obj["id"] + " value of " + obj["label"]);
		 
		}
		//$("#rn_ProductCategoryInputSelect_2_Category_SelectObject_lvl2").val(27026).text();
		

	},
	_setProductTree2: function()
	{
		var sel2 = this.selectObject2;
		var that=this;
		var selValue2 =  sel2.get("value");
		var selText2 =  sel2.get('options').item(sel2.get('selectedIndex')).get('text');
		this.tree.selectNodeWithValue(selValue2, true);
	 
		this.selectNode(this.tree.getFocusedNode());
		var tempNode = this.tree.getNodeByValue(selValue2);
		if (tempNode)
			this.selectNode({node: tempNode});
			
 
	}
}); 