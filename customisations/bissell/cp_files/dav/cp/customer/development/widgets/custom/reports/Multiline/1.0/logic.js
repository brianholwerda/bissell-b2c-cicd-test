RightNow.namespace('Custom.Widgets.reports.Multiline');
Custom.Widgets.reports.Multiline = RightNow.Widgets.Multiline.extend({ 
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
     * Event handler received when report data is changed.
     * @param {String} type Event name
     * @param {Array} args Arguments passed with event
     */
    _onReportChanged: function(type, args) {
        var newdata = args[0].data,
            ariaLabel, firstLink,
            newContent = "";

        this._displayDialogIfError(newdata.error);

        if (!this._contentDiv) return;

        if(newdata.total_num > 0) {
            ariaLabel = this.data.attrs.label_screen_reader_search_success_alert;
            newdata.hide_empty_columns = this.data.attrs.hide_empty_columns;
            newdata.hide_columns = this.data.js.hide_columns;
            newContent = new EJS({text: this.getStatic().templates.view}).render(newdata);
        }
        else {
            ariaLabel = this.data.attrs.label_screen_reader_search_no_results_alert;
        }

        this._updateAriaAlert(ariaLabel);
        this._contentDiv.set("innerHTML", newContent);
        alert(newContent);

        if (this.data.attrs.hide_when_no_results) {
            this.Y.one(this.baseSelector)[((newContent) ? 'removeClass' : 'addClass')]('rn_Hidden');
        }

        this._setLoading(false);
        RightNow.Url.transformLinks(this._contentDiv);

        if (newdata.total_num && (firstLink = this._contentDiv.one('a'))) {
            //focus on the first result
            firstLink.focus();
        }
    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});