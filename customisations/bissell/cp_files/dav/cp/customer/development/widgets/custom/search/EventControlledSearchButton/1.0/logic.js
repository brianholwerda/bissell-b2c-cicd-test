RightNow.namespace('Custom.Widgets.search.EventControlledSearchButton');
Custom.Widgets.search.EventControlledSearchButton = RightNow.Widgets.SearchButton.extend({
  /**
   * Place all properties that intend to
   * override those of the same name in
   * the parent inside `overrides`.
   */
  overrides: {
    /**
     * Overrides RightNow.Widgets.SearchButton#constructor.
     */
    constructor: function() {
      // Call into parent's constructor
      this.parent();

      this.data.attrs.start_search_events = this.data.attrs.start_search_events.split(',');

      for(var index in this.data.attrs.start_search_events) {
        RightNow.Event.subscribe(this.data.attrs.start_search_events[index], this._startSearch, this);
      }
    }

    /**
     * Overridable methods from SearchButton:
     *
     * Call `this.parent()` inside of function bodies
     * (with expected parameters) to call the parent
     * method being overridden.
     */
     ,

     /**
     * Event handler executed when the button is clicked
     * @param {Object} evt Event
     */
     _startSearch: function(evt) {
         if (this._requestInProgress) return;

         if (!this.data.attrs.popup_window && (!this.data.attrs.report_page_url || (this.data.attrs.target === '_self')))
             this._disableClickListener();

         if (this.Y.UA.ie) {
             // since the form is submitted by script, deliberately tell IE to do auto completion of the form data
             this._parentForm = this._parentForm || this.Y.one(this.baseSelector).ancestor("form");
             if (this._parentForm && window.external && "AutoCompleteSaveForm" in window.external) {
                 window.external.AutoCompleteSaveForm(this.Y.Node.getDOMNode(this._parentForm));
             }
         }
         var searchPage = this.data.attrs.report_page_url;
         this.searchSource().fire("search", new RightNow.Event.EventObject(this, {filters: {
             report_id: this.data.attrs.report_id,
             source_id: this.data.attrs.source_id,
             reportPage: searchPage,
             newPage: (this.data.attrs.force_page_flip || top !== self || (searchPage !== "" && searchPage !== "{current_page}" && !RightNow.Url.isSameUrl(searchPage))) && !this.data.attrs.force_ajax_request,
             target: this.data.attrs.target,
             popupWindow: this.data.attrs.popup_window,
             width: this.data.attrs.popup_window_width_percent,
             height: this.data.attrs.popup_window_height_percent
         }}));
     }
    // _enableClickListener: function()
    // _disableClickListener: function()
  }
});
