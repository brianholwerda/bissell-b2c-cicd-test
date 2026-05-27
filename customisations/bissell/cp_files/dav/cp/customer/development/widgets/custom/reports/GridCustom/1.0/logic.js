RightNow.namespace('Custom.Widgets.reports.GridCustom');
Custom.Widgets.reports.GridCustom = RightNow.Widgets.Grid.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.Grid#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
			
			RightNow.Event.subscribe("on_before_ajax_request", this.collapseReportsOnSearch, this);
        }

        /**
         * Overridable methods from Grid:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // _setFilter: function()
        // _setSortData: function(columnID, sortDirection)
        // _searchInProgress: function(evt, args)
        // _setLoading: function(loading)
        // _onReportChanged: function(type, args)
        // _setTableData: function(headers)
        // _generateYUITable: function(headers)
        // _getDirectionToSort: function(columnID)
        // _setSortedColumn: function(columnID, dir)
        // _fireSortEvent: function()
        // _onSortTypeResponse: function(type, args)
        // _updateAriaAlert: function(text)
    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    },
	
	/**
	 * Hides reports after search has been initiated
	 */
	collapseReportsOnSearch: function(){
		alert("boom!");	
		$(".yui3-datatable-message").hide();
		$(".yui3-datatable-data").hide();
		$(".rn_AnswersLink").hide();		
	}	
});