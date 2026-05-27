RightNow.namespace('Custom.Widgets.reports.HotTopics');
Custom.Widgets.reports.HotTopics = RightNow.Widgets.Multiline.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.Multiline#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
        }

        /**
         * Overridable methods from Multiline:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // _setFilter: function()
        // _searchInProgress: function(evt, args)
        // _setLoading: function(loading)
        // _onReportChanged: function(type, args)
        // _displayDialogIfError: function(error)
        // _updateAriaAlert: function(text)
    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});