var AJAX_ENDPOINT = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/ordermanagerajaxhandler.php";
var defOrderDataReady = $.Deferred();

(function ($) {

    $.when(defOrderDataReady).done(function (skipTrigger) {

	var editor = new $.fn.dataTable.Editor( {
	    ajax: AJAX_ENDPOINT + '?action=editorderline&sid=' + getParameterByName('sid') + '&id=_id_',
        table: '#Order',
        idSrc: 'line',
        dataType:"json",
        fields: [
			{
				"label": "Qty:",
				"name": "qty"
			},
            {
            	"label": "AdjustedPrice:",
            	"name": "adjustedprice"
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

	editor.on('edit', function (e, json, data) {

	});

	editor.on('remove', function (e, json, data) {
	    
	});

	var orderTable = $('#Order').DataTable( {
	    searching: false,
	    ajax: AJAX_ENDPOINT + '?action=getorderlinedata&orderheaderid=' + getParameterByName('oid') + '&sid=' + sid,
	    paging: false,
	    info: false,
        columns: [
            {
                data: null,
                className: "center",
                defaultContent: '<a href="" class="editor_remove">Delete</a>',
                width: "65px",
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
			    "data": "adjustedprice",
                "width":"100px"
			},

			{
			    "data": "linetotal",
			    "width": "15px",
                "render": function (data, type, row) {
			                        return accounting.toFixed(parseInt(row.qty) * parseFloat(row.adjustedprice),2);
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
			                    reasonText = reason.code || reason.name;
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
			    "data": "unitsellingprice",
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
            "targets": [8,9,10,11,12,13,14,15,16,17,18],
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

            if(skipTrigger != true)
                $(document).trigger('ordertable.rowadded');

		},
		"footerCallback": function (tfoot, data, start, end, display) {//Using the footer callback event to perform calculations. It fires for all the events needed to calculate values correctly.
		    //var api = this.api();
		    //var total = 0.00;

            ////Get item row totals
		    //for (var i = 0; i < data.length; i++) {
		    //    total = total + (data[i].qty * data[i].adjustedprice);
		    //}

		    //if (data.length > 0) {
		    //    setTimeout(function () {
            //        if(!window.pageLocked)
		    //        window.AutoSelectShippingMethod();
		    //        window.SetOrderTotals();

		    //    }, 1000);
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
		    skipTrigger = false;
		    $(document).trigger('ordertable.init');

		}

	});

	window.OrderTable = orderTable;

    /*
	function addRow() {
	    OrderTable.row.add({
            "ordernumber":"1",
	        "line": "my line",
	        "part": "my part",
	        "qty": "2",
	        "uom": "each",
	        "description": "my description",
	        "unitsellingprice": "$1.07",
	        "adjustedprice": "$2.14",
	        "status": "ok",
	        "linetype": "my line type",
	        "inv": "on hand"
	    }).draw();
	};

	addRow();
    */

    // Edit record
	$('#Order').on('click', 'a.editor_edit', function (e) {
	    e.preventDefault();

	    editor.edit($(this).closest('tr'), {
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
	$('#Order').on('click', 'a.editor_remove', function (e) {

	    e.preventDefault();

	    editor.remove($(this).closest('tr'), {
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
	                    $(document).trigger('ordertable.delete');
	                }, 1000);
	            }
	        }]
	    });
	});

        
	orderTable.on('order.dt search.dt', function () {
	    orderTable.column(1, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
	        cell.innerHTML = i + 1;
	    });
	}).draw();

} );

}(jQuery));

