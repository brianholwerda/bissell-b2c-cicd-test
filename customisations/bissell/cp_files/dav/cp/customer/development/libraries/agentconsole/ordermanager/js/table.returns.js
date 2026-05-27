var AJAX_ENDPOINT = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/ordermanagerajaxhandler.php";
var defOrderDataReady = $.Deferred();

(function ($) {

    $.when(defOrderDataReady).done(function (skipTrigger) {

    /* Returns Data Table */

    var editorReturnParts = new $.fn.dataTable.Editor({
	    ajax: AJAX_ENDPOINT + '?action=editorderline&sid=' + getParameterByName('sid') + '&id=_id_',
	    table: '#returnparts',
        idSrc: 'line',
        dataType:"json",
        fields: [
			{
				"label": "Qty:",
				"name": "qty"
			},
            {
            	"label": "AdjustedPrice:",
            	"name": "adjustedprice",
                "type":"hidden"
            },
			{
				"label": "Line:",
				"name": "line",
                "type": "hidden"
			},
			{
				"label": "Part:",
				"name": "part",
				"type": "hidden"
			},

			{
				"label": "UOM:",
				"name": "uom",
				"type": "hidden"
			},
			{
				"label": "Description:",
				"name": "description",
				"type": "hidden"
			},
			{
				"label": "UnitSellingPrice:",
				"name": "unitsellingprice",
				"type": "hidden"
			},

            {
                "label":"Status",
                "name": "status",
                "type": "hidden"
            },
            {
                "label":"LineType",
                "name": "linetype",
                "type": "hidden"
            },
            {
                "label":"Inv",
                "name": "inv",
                "type": "hidden"
            },
            {
                "label": "LineID",
                "name": "lineid",
                "type": "hidden"
            },
            {
                "label": "OrderHeaderID",
                "name": "orderheaderid",
                "type": "hidden"
            },
            {
                "label": "Aerosol",
                "name": "aerosol",
                "type": "hidden"
            },
            {
                "label": "NoCharge",
                "name": "nocharge",
                "type": "hidden"
            },
            {
                "label": "ReasonCode",
                "name": "reasoncode",
                "type": "hidden"
            },
            {
                "label": "SerialNumber",
                "name": "serialnumber",
                "type":"hidden"
            },
            {
                "label": "ModelNumber",
                "name": "modelnumber",
                "type": "hidden"
            }
		]
	});

	var returnPartsTable = $('#returnparts').DataTable({
	    searching: false,
	    ajax: AJAX_ENDPOINT + '?action=getorderlinedata&linetype=2&orderheaderid=' + getParameterByName('oid') + '&sid=' + sid,
	    paging: false,
	    info: false,
        columns: [
            {
                data: null,
                className: "center",
                defaultContent: '<a href="" class="editor_remove">Delete</a>',
                width: "80px",
                render: function (data, type, row) {
                    var editable = '<a href="" class="editor_remove">Delete</a>';
                    if (row.trackingnumber) {
                        return row.trackingnumber;
                    }
                    if (window.pageLocked == true) {
                        return '';
                    }
                    return editable;
                }
            },
			{
			    "data": "line",
                "width":"20px"
			},
			{
			    "data": "part",
                "width":"20px"
			},
			{
			    "data": "description",
			    "width": "250px"
			},
			{
			    "data": "qty",
                "width":"15px"
			},
			{
			    "data": "unitsellingprice",
                "width":"50px"
			},
			{
			    "data": "linetotal",
			    "width": "50px",
                "render": function (data, type, row) {
                    //return accounting.toFixed(parseInt(row.qty) * parseFloat(row.adjustedprice),2);
                    return accounting.toFixed("0.00", 2);
			                    }
			},
			{
			    "data": null,
			    "width": "200px",
			    "render": function (data, type, row) {
			        var reasonText = "";
			        if (row.reasoncode != null) {
			            $.each(window.reasonCodeList, function (idx, reason) {
			                if (reason.id == row.reasoncode) {
			                    reasonText = reason.code || reason.label;
			                    if (row.serialnumber)
			                        reasonText = reasonText + ' SN: ' + row.serialnumber;
			                    if (row.modelnumber)
			                        reasonText = reasonText + ' MN: ' + row.modelnumber;
			                }
			            });
			        }

                    return reasonText;
			    }
			},
            {
            	"data": null,
            	"width": "200px",
            	"render": function (data, type, row) {
					var actionText = "";
            		if (row.actioncode != null) {
            			$.each(window.actionCodeList, function (idx, action) {
							if (action.id == row.actioncode) {
								if (action.requirereplacement == true){
									requireReplacement = true;
								}
								
								actionText = action.label;
            			    }
            			});
            		}

            		return actionText;
            	}
            },
			{
			    "data": "adjustedprice",
			    "width": "100px",
			    
			},
            {
                "data": "linetype",
                "width": "20px"
            },
            {
                "data": "inv",
                "width": "10px"
            },
            {
                "data":"lineid"
            },
            {
                "data":"orderheaderid"
            },
            {
                "data":"aerosol"
            },
            {
                "data":"nocharge"
            },
            {
                "data":"reasoncode"
            },
            {
                "data":"serialnumber"
            },
            {
                "data":"modelnumber"
            },
            {
                "data":"trackingnumber"
            }

        ],
        columnDefs: [{
            "targets": [9,10,11,12,13,14,15,16,17,18,19],
            "visible":false
        }],
		select: false,
		lengthChange: false,
		"rowCallback": function (row, data, index) {
		    if (data.lineid == -1) {//shipping row has a line id of -1
		        $('td:eq(0)', row).html('<a href="" class="editor_remove">Delete</a>');
		    }
		    if (data.lineid == -2) {// Order total has a line id of -2
		        $('td:eq(0)', row).html('');
		        $('td:eq(5)', row).html('<strong>' + data.description + '</strong>');
		        $('td:eq(7)', row).html('<strong>' + data.adjustedprice + '</strong>');
		    }

		    if (skipTrigger != true)
		        $(document).trigger('returnpartstable.updated');
		},
		"footerCallback": function (tfoot, data, start, end, display) {//Using the footer callback event to perform calculations. It fires for all the events needed to calculate values correctly.
		    var api = this.api();
		    var total = 0.00;

            //Get item row totals
		    for (var i = 0; i < data.length; i++) {
		        total = total + (data[i].qty * data[i].adjustedprice);
		    }

		    //if (data.length > 0) {
		    //    setTimeout(function () {
		    //        window.AutoSelectShippingMethod();
		    //        window.SetOrderTotals();

		    //    }, 500);
		    //}

            ////Get sales tax and shipping
		    //if (total > 0) {

		    //    salesTax = window.SetOrderTotals();
            //    shippingTotal
		    //    total = total + parseFloat(salesTax);
		    //}

		    //$(api.column(7).footer()).html(accounting.toFixed(total, 2));

		},
		"headerCallback": function (thead, data, start, end, display) {
		    if (data.length > 0) {
		        if (data[0].trackingnumber) {
		            $(thead).find('th').eq(0).html( 'Tracking Number' );
		        }
		    }
		},
		"initComplete": function () {
		    
		    $(document).trigger('returnpartstable.init');

		}

	});

	window.ReturnPartsTable = returnPartsTable;

	//$(document).trigger('returnpartstable.updated');

    // Edit record
	$('#returnparts').on('click', 'a.editor_edit', function (e) {
	    e.preventDefault();

	    editorReturnParts.edit($(this).closest('tr'), {
            submit:'all',
	        title: 'Edit record',
	        buttons: [{
	            label: 'Cancel',
	            fn: function () {
	                this.close();
	            }
	        },'Update']
	    });
	});

    // Delete a record
	$('#returnparts').on('click', 'a.editor_remove', function (e) {

	    e.preventDefault();

	    editorReturnParts.remove($(this).closest('tr'), {
	        title: 'Delete record',
	        message: 'Are you sure you wish to remove this record?',
	        buttons: [{
	            label: 'Cancel',
	            fn: function () {	                
	                this.close();
	            }
	        }, {
	            label: 'Delete',
	            fn: function () {
	                this.submit();
	                setTimeout(function () {
	                    $(document).trigger('returnpartstable.delete');
	                }, 500);
	                //setTimeout(function () {
	                //    window.SetOrderTotals();
	                //    window.AutoSelectShippingMethod();
	                //}, 100);
	            }
	        }]
	    });
	});
        
	returnPartsTable.on('order.dt search.dt', function () {
	    returnPartsTable.column(1, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
	        cell.innerHTML = i + 1;
	    });
	}).draw();

    /* Replacement Parts Table */

	var editorReplacementParts = new $.fn.dataTable.Editor({
	    ajax: AJAX_ENDPOINT + '?action=editorderline&sid=' + getParameterByName('sid') + '&id=_id_',
	    table: '#replacementparts',
	    idSrc: 'line',
	    dataType: "json",
	    fields: [
			{
			    "label": "Qty:",
			    "name": "qty"
			},
            {
                "label": "AdjustedPrice:",
                "name": "adjustedprice",
                "type":"hidden"
            },
			{
			    "label": "Line:",
			    "name": "line",
			    "type": "hidden"
			},
			{
			    "label": "Part:",
			    "name": "part",
			    "type": "hidden"
			},

			{
			    "label": "UOM:",
			    "name": "uom",
			    "type": "hidden"
			},
			{
			    "label": "Description:",
			    "name": "description",
			    "type": "hidden"
			},
			{
			    "label": "UnitSellingPrice:",
			    "name": "unitsellingprice",
			    "type": "hidden"
			},

            {
                "label": "Status",
                "name": "status",
                "type": "hidden"
            },
            {
                "label": "LineType",
                "name": "linetype",
                "type": "hidden"
            },
            {
                "label": "Inv",
                "name": "inv",
                "type": "hidden"
            },
            {
                "label": "LineID",
                "name": "lineid",
                "type": "hidden"
            },
            {
                "label": "OrderHeaderID",
                "name": "orderheaderid",
                "type": "hidden"
            },
            {
                "label": "Aerosol",
                "name": "aerosol",
                "type": "hidden"
            },
            {
                "label": "NoCharge",
                "name": "nocharge",
                "type": "hidden"
            },
            {
                "label": "ReasonCode",
                "name": "reasoncode",
                "type": "hidden"
            },
            {
                "label": "SerialNumber",
                "name": "serialnumber",
                "type": "hidden"
            },
            {
                "label": "ModelNumber",
                "name": "modelnumber",
                "type": "hidden"
            }
	    ]
	});

	var replacementPartsTable = $('#replacementparts').DataTable({
	    searching: false,
	    ajax: AJAX_ENDPOINT + '?action=getorderlinedata&linetype=3&orderheaderid=' + getParameterByName('oid') + '&sid=' + sid,
	    paging: false,
	    info: false,
	    columns: [
            {
                data: null,
                className: "center",
                defaultContent: '<a href="" class="editor_remove">Delete</a>',
                width: "80px",
                render: function (data, type, row) {
                    var editable = '<a href="" class="editor_remove">Delete</a>';
                    if (row.trackingnumber) {
                        return row.trackingnumber;
                    }
                    if (window.pageLocked == true) {
                        return '';
                    }
					if (row.part == '1635561'){
						return '';
					}
                    return editable;
                }
            },
			{
			    "data": "line",
			    "width": "20px"
			},
			{
			    "data": "part",
			    "width": "20px"
			},
			{
			    "data": "description",
			    "width": "180px"
			},
			{
			    "data": "qty",
			    "width": "15px"
			},
			{
			    "data": "unitsellingprice",
			    "width": "50px"
			},

			{
			    "data": "linetotal",
			    "width": "50px",
			    "render": function (data, type, row) {
			        //return accounting.toFixed(parseInt(row.qty) * parseFloat(row.adjustedprice), 2);
			        return accounting.toFixed("0.00", 2);
			    }
			},
			{
			    "data": null,
			    "width": "200px",
			    "render": function (data, type, row) {
			        var reasonText = "";
			        if (row.reasoncode != null) {
			            $.each(window.reasonCodeList, function (idx, reason) {
			                if (reason.id == row.reasoncode) {
			                    reasonText = reason.label;
			                    if (row.serialnumber)
			                        reasonText = reasonText + ' SN: ' + row.serialnumber;
			                    if (row.modelnumber)
			                        reasonText = reasonText + ' MN: ' + row.modelnumber;
			                }
			            });
			        }

			        return reasonText;
			    }
			},
			{
			    "data": "adjustedprice",
			    "width": "100px",

			},
            {
                "data": "linetype",
                "width": "20px"
            },
            {
                "data": "inv",
                "width": "10px"
            },
            {
                "data": "lineid"
            },
            {
                "data": "orderheaderid"
            },
            {
                "data": "aerosol"
            },
            {
                "data": "nocharge"
            },
            {
                "data": "reasoncode"
            },
            {
                "data": "serialnumber"
            },
            {
                "data": "modelnumber"
            },
            {
                "data": "trackingnumber"
            }

	    ],
	    columnDefs: [{
	        "targets": [8,9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
	        "visible": false
	    }],
	    select: false,
	    lengthChange: false,
	    "rowCallback": function (row, data, index) {
	        if (data.lineid == -1) {//shipping row has a line id of -1
	            $('td:eq(0)', row).html('<a href="" class="editor_remove">Delete</a>');
	        }
	        if (data.lineid == -2) {// Order total has a line id of -2
	            $('td:eq(0)', row).html('');
	            $('td:eq(5)', row).html('<strong>' + data.description + '</strong>');
	            $('td:eq(7)', row).html('<strong>' + data.adjustedprice + '</strong>');
	        }

	        if (skipTrigger != true)
	            $(document).trigger('replacementpartstable.updated');
	    },
	    "footerCallback": function (tfoot, data, start, end, display) {//Using the footer callback event to perform calculations. It fires for all the events needed to calculate values correctly.
	        var api = this.api();
	        var total = 0.00;

	        //Get item row totals
	        for (var i = 0; i < data.length; i++) {
	            total = total + (data[i].qty * data[i].adjustedprice);
	        }

	        //if (data.length > 0) {
	        //    setTimeout(function () {
	        //        window.AutoSelectShippingMethod();
	        //        window.SetOrderTotals();

	        //    }, 500);
	        //}

	        ////Get sales tax and shipping
	        //if (total > 0) {

	        //    salesTax = window.SetOrderTotals();
	        //    shippingTotal
	        //    total = total + parseFloat(salesTax);
	        //}

	        //$(api.column(7).footer()).html(accounting.toFixed(total, 2));

	    },
	    "headerCallback": function (thead, data, start, end, display) {
	        if (data.length > 0) {
	            if (data[0].trackingnumber) {
	                $(thead).find('th').eq(0).html('Tracking Number');
	            }
	        }
	    },
	    "initComplete": function () {
	        
	        $(document).trigger('replacementpartstable.init');
	        skipTrigger = false;
	    }

	});

	editorReplacementParts.on('remove', function (e, json, data) {
	    $(document).trigger('replacementpartstable.delete');
	});

	window.ReplacementPartsTable = replacementPartsTable;

    // Edit record
	$('#replacementparts').on('click', 'a.editor_edit', function (e) {
	    e.preventDefault();

	    editorReplacementParts.edit($(this).closest('tr'), {
	        submit: 'all',
	        title: 'Edit record',
	        buttons: [{
	            label: 'Cancel',
	            fn: function () {
	                this.close();
	            }
	        }, 'Update']
	    });
	});

    // Delete a record
	$('#replacementparts').on('click', 'a.editor_remove', function (e) {

	    e.preventDefault();

	    editorReplacementParts.remove($(this).closest('tr'), {
	        title: 'Delete record',
	        message: 'Are you sure you wish to remove this record?',
	        buttons: [{
	            label: 'Cancel',
	            fn: function () {
	                this.close();
	            }
	        }, {
	            label: 'Delete',
	            fn: function () {
	                this.submit();
	                setTimeout(function () {
	                    $(document).trigger('replacementpartstable.delete');
	                }, 500);	                
	            }
	        }]
	    });
	});

	replacementPartsTable.on('order.dt search.dt', function () {
	    replacementPartsTable.column(1, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
	        cell.innerHTML = i + 1;
	    });
	}).draw();

} );

}(jQuery));

