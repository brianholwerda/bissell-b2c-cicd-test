RightNow.namespace('Custom.Widgets.search.EventControlledKeywordText');
Custom.Widgets.search.EventControlledKeywordText = RightNow.Widgets.KeywordText.extend({
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.KeywordText#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();

            this.data.attrs.set_value_events = this.data.attrs.set_value_events.split(',');

            for(var index in this.data.attrs.set_value_events) {
              RightNow.Event.subscribe(this.data.attrs.set_value_events[index], this._setValueFromEvent, this);
            }
        }

        /**
         * Overridable methods from KeywordText:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // _onChange: function(evt)
        // _onGetFiltersRequest: function(type, args)
        // _setFilter: function()
        // _onChangedResponse: function(type, args)
        // _onResetRequest: function(type, args)
        // _decoder: function(value)
    },

    /**
     * Sample widget method.
     */
    _setValueFromEvent: function(eo, data) {
      var new_value = data[0];
      this._textElement.set('value', new_value);
    }
});
