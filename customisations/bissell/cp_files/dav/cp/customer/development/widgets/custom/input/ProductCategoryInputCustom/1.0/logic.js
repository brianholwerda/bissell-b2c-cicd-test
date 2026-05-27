RightNow.namespace('Custom.Widgets.input.ProductCategoryInputCustom');
Custom.Widgets.input.ProductCategoryInputCustom = RightNow.Widgets.ProductCategoryInput.extend({ 
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
			this.parent();
			this._eo2 = new RightNow.Event.EventObject(this, {data: {
                data_type: this.data.attrs.data_type,
                hm_type: this.data.js.hm_type,
                linking_on: this.data.js.linkingOn,
                linkingProduct: 0,
                table: "Incident",
                cache: [],
				selected_value: this.getValue(),
                name: ((this.data.attrs.data_type.indexOf('prod') > -1) ? 'prod' : 'cat')
            }});

			this.on('change', this.onChange, this);
        }

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
	onChange: function() {
        var value = this.getValue(),
            requiredFields = [],
            fieldsDisplayed = [];
            //setup event object
			this._eo2.data.selected_value = this.tree.get('value');
		RightNow.Event.fire("evt_productCategorySelected", this._eo2);
			RightNow.Event.fire("evt_productCategorySelected2", this._eo2);
	// console.log("the value selected on product is " + this.tree.get('value') );
    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});