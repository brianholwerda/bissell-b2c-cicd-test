RightNow.namespace('Custom.Widgets.input.FormSubmit');
Custom.Widgets.input.FormSubmit = RightNow.Widgets.FormSubmit.extend({     
	/**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.FormSubmit#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
        },     
         /**
         * Overridable methods from FormSubmit:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */        
         // _onButtonClick: function(evt)        
         // _fireSubmitRequest: function()        
         // _onFormValidated: function()        
         // _onFormValidationFail: function()        
         // _clearFlashData: function()        
         // _absoluteOffset: function(element)        
         //_displayErrorMessages: function(messageArea)  
         
         _displayErrorMessages: function(messageArea) {
	    
	    console.log(messageArea);
        messageArea.addClass("rn_MessageBox").addClass("rn_ErrorMessage").removeClass("rn_Hidden");
        this._clearFlashData();
        if (!this.Y.DOM.inViewportRegion(this.Y.Node.getDOMNode(messageArea), true)) {
            (new this.Y.Anim({
                node: this.Y.one(document.body),
                to:   { scrollTop: this._absoluteOffset(messageArea) - 40 },
                duration: 0.5
            })).run();
        }
        
        var firstField = messageArea.one("a");
        
        
        if (firstField) {
            // Focus first link in the error box.
            firstField.focus();
            firstField.setAttribute('role', 'alert');
            // If tabIndex had previously been set via the
            // else case (during a different failure) then remove it now.
            messageArea.removeAttribute('tabIndex');
        }
        else {
            // The error box doesn't have any links, so focus on the box itself.
            // Setting tabIndex to 0 on an element that's not normally tab-focusable gives
            // it normal tab flow in the document.
            messageArea.set('tabIndex', 0);
            messageArea.setAttribute('role', 'alert');
            messageArea.focus();
        }
        var errorLbl = messageArea.all("div").size() > 1 ? RightNow.Interface.getMessage("ERRORS_LBL") : RightNow.Interface.getMessage("ERROR_LBL");
        messageArea.prepend("<h2>" + errorLbl + "</h2>");
        //messageArea.one("h2").setAttribute('role', 'alert');
        
    	},


               
         // _defaultFormSubmitResponse: function(type, args)        
         // _formSubmitResponse: function(type, args)        
         // _handleFormResponseSuccess: function(result)        
         // _handleFormResponseFailure: function(responseObject)        
         // _navigateToUrl: function(result)        
         // _confirmOnNavigate : function(result)        
         // fn: function()        
         // _resetFormForSubmission: function()        
         // _onFormUpdated: function()        
         // _onErrorResponse: function(response)        
         // _resetFormButton: function()        
         // _removeFormErrors: function()        
         // _displayErrorDialog: function(message)        
         // _toggleLoadingIndicators: function(turnOn)        
         // _toggleClickListener: function(enable)
    },
    /**
     * Sample widget method.
     */
    /**
     * For the given node,
     * - adds and removes classes
     * - scrolls to it, if it's not in the viewport
     * - focuses on the first <a> child, or on
     *   the element itself (by setting tabindex)
     * @param  {Object} messageArea Y.Node
     */
        
});