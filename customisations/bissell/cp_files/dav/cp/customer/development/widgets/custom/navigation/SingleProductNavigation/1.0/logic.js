RightNow.namespace('Custom.Widgets.navigation.SingleProductNavigation');
Custom.Widgets.navigation.SingleProductNavigation = RightNow.ResultsDisplay.extend({ 
    
    overrides: {
        /**
         * Widget constructor.
         */
        constructor: function() {
            console.log(this.data);
            if(this.data.attrs.forward_on_ajax){
                
                this.parent();
                this.data.attrs.report_id || (this.data.attrs.report_id = 176);
                (RightNow.Event.isHistoryManagerFragment() && this._setLoading(true)); 
                this.searchSource(this.data.attrs.report_id).on("response", this._onReportChanged, this);

                this.searchSource(this.data.attrs.report_id).on("response", this._onReportChanged, this);
                
            }
        },
    },
    

    /**
     * Event handler received when report data is changed.
     * @param {String} type Event name
     * @param {Array} args Arguments passed with event
     */
    _onReportChanged: function(type, args) {
        
        if(args[0].data.data.length === 1){
            
            // get the whole string after the /<url_param_to_get>
            var post_param = args[0].data.data[0][this.data.attrs.column_index].split(this.data.attrs.defining_param+'/')[1];
            // get the param value from that string
            var param_value = post_param.split('/')[0];

            var url = '/app/answers/detail/'+this.data.attrs.defining_param+'/' + param_value + this.data.js.format.urlParms;
            window.location.href = url;
        }
        
    },

    /**
     * Displays a warning dialog when a report error is encountered.
     * @private
     * @param {null|String} error
     */
    _displayDialogIfError: function(error) {
        if (error) {
            RightNow.UI.Dialog.messageDialog(error, {"icon": "WARN"});
        }
    },

    /**
     * Updates the text for the ARIA alert div that appears above the results listings.
     * @private
     * @param {String} text The text to update the div with
     */
});