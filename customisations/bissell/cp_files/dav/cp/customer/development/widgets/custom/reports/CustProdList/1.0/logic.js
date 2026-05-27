RightNow.namespace('Custom.Widgets.reports.CustProdList');
Custom.Widgets.reports.CustProdList = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() 
	{

		this.p_id = document.getElementById('p_id').value;
		this.w_id = this.instanceID;
 
		var that = this;
		
    },

  	/***
	  * On AJAX Response success this method is called
	  * which either present the error or reloads the page
	  */
	  
	  OnSuccess : function(o)
	  {
		  return true;
	  }
});