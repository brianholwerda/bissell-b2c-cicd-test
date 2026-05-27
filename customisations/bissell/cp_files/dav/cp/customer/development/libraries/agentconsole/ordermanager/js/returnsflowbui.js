/* Workspace Interactions */
// localStorage.clear();

var CUSTOM_SCRIPTS_PATH = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/";
var PORTAL_SCRIPT_PATH = "";

//Set paths for sites other than localhost
if (!(location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
	PORTAL_SCRIPT_PATH = "../cp/customer/development/libraries/agentconsole/ordermanager/";
	CUSTOM_SCRIPTS_PATH = "/cgi-bin/bissell.cfg/php/custom/";
}

var AJAX_ENDPOINT = CUSTOM_SCRIPTS_PATH + "ordermanagerajaxhandler.php";
//var AJAX_ENDPOINT_ORDERMANAGER = CUSTOM_SCRIPTS_PATH + "contactorderhistory.php";
var AJAX_ENDPOINT_ORDERMANAGER = CUSTOM_SCRIPTS_PATH + "contactorderhistoryv2.php";
var order_details_tables = {};

var orderHeaderId = getParameterByName('oid');
var pageLocked = false;

function setFieldVisibility(){
	var val = parseInt(getSelectedValue('nochargereason'));
	var intval = isNaN(val) ? null : val;
	
	var showOrderTypeDiv = false;
	var showOrderLinkRowDiv = false;
	var showLinkedOrderTableDiv = false;
	var showOrderLinkSummaryRowDiv = false;
	var showAllContentsDiv = false;
	
	if(window.requireorderflow != null){
		if(window.requireorderflow == true){
			if(pageLocked){
				if(intval != null){
					var linktoorder = getSelectedDataAttribute('nochargereason', 'linktoordernumber');
					if(linktoorder){
						var body = $("#tblLinkedOrderData").find('tbody');
						var rows = body.find('tr');
						if(rows.length > 0){
							showOrderLinkSummaryRowDiv = true;
							showAllContentsDiv = true;
						} else {
							showOrderLinkRowDiv = true;
							showAllContentsDiv = true;
						}
					} else {
						showOrderTypeDiv = true;
						var nochargeval = parseInt(getSelectedValue('ralinktoorder'));
						var checkNoCharge = isNaN(nochargeval) ? null : nochargeval;
						if(checkNoCharge != null){
							if(checkNoCharge != 0){
								var body = $("#tblLinkedOrderData").find('tbody');
								var rows = body.find('tr');
								if(rows.length > 0){
									showOrderLinkSummaryRowDiv = true;
									showAllContentsDiv = true;
								} else {
									showAllContentsDiv = true;
								}
							} else {
								showAllContentsDiv = true;
							}
						} else {
							showAllContentsDiv = true;
						}
					}
				} else {
					showAllContentsDiv = true;
				}
			} else {
				if(intval != null){
					var linktoorder = getSelectedDataAttribute('nochargereason', 'linktoordernumber');
					if(linktoorder){
						var body = $("#tblLinkedOrderData").find('tbody');
						var rows = body.find('tr');
						if(rows.length > 0){
							showOrderLinkSummaryRowDiv = true;
							showAllContentsDiv = true;
							showOrderLinkRowDiv = true;
						} else {
							showOrderLinkRowDiv = true;
						}
					} else {
						showOrderTypeDiv = true;
						var nochargeval = parseInt(getSelectedValue('ralinktoorder'));
						var checkNoCharge = isNaN(nochargeval) ? null : nochargeval;
						if(checkNoCharge != null){
							if(checkNoCharge != 0){
								var body = $("#tblLinkedOrderData").find('tbody');
								var rows = body.find('tr');
								if(rows.length > 0){
									showOrderLinkSummaryRowDiv = true;
									showOrderLinkRowDiv = true;
									showAllContentsDiv = true;
								} else {
									showOrderLinkRowDiv = true;
								}
							} else {
								showAllContentsDiv = true;
							}
						} else {
						}
					}
				} else {
				}
			}
		} else {
		}
	} else {
	}
	
	if(showOrderTypeDiv){
		$('#divOrderType').css("display","");
	} else {
		$('#divOrderType').css("display","none");
	}
	
	if(showOrderLinkRowDiv){
		$('#divOrderLinkRow').css("display","");
	} else {
		$('#divOrderLinkRow').css("display","none");
	}
	
	if(showAllContentsDiv){
		$('#divAllContents').css("display","");
	} else {
		$('#divAllContents').css("display","none");
	}
	
	if(showLinkedOrderTableDiv){
		$('#divLinkedOrderTable').css("display","");
	} else {
		$('#divLinkedOrderTable').css("display","none");
	}
	
	if(showOrderLinkSummaryRowDiv){
		$("#divOrderLinkSummaryRow").css("display","");
	} else {
		$("#divOrderLinkSummaryRow").css("display","none");
	}
}
				
function getSelectedValue(controlId) {
	var val = $('#' + controlId + ' option:selected').val();
	return val;
}

function getSelectedDataAttribute(controlId,attr) {
	var text = $('#' + controlId + ' option:selected').data(attr);
	return text;
}

ORACLE_SERVICE_CLOUD.extension_loader.load("Bissell Order" , "1.0").then(function(extensionProvider) {
	extensionProvider.getGlobalContext().then(function(GlobalContext) {
		profileID = GlobalContext.getProfileId();
		extensionProvider.registerWorkspaceExtension(function(WorkspaceRecord) {	
			WorkspaceRecord.getFieldValues([
				'BUS$OrderHeader.LockTrans'
			]).then(function(fieldDetails) {
				
				pageLocked = fieldDetails.getField('BUS$OrderHeader.LockTrans').getValue();
				setFieldVisibility();				
				
				$(document).ready(function () {
					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getorderlocks&orderheaderid=' + orderHeaderId + '&sid=' + sid,
						cache: true,
						success: function (data) {
							if(data.length > 0){
								var selectedOrder = data[0].originalordernumber;
								var txtOrderList = '';
								
								for (var i = 0; i < data.length; i++){
									$("#tblLinkedOrderData").find('tbody')
										.append($('<tr>')
											.append($('<td>')
												.text(data[i].ebslineid)
												.addClass('clsLineID')
											).append($('<td>')
												.text(data[i].originalordernumber)
												.addClass('clsOrderNum')
											).append($('<td>')
												.text(data[i].id)
												.addClass('clsLockID')
											).append($('<td>')
												.text(data[i].sku)
												.addClass('clsPartNum')
											)
										);
									if(txtOrderList){
										txtOrderList += ', ' + data[i].sku;
									} else {
										txtOrderList = data[i].sku;
									}
								}
								
								$("#ordernumber").val(selectedOrder);
								$("#divOrderLinkSummaryOrderNum").html('<b>Original Order Number:</b> ' + selectedOrder);
								$("#divOrderLinkSummaryLines").html('<b>SKU(s):</b> ' + txtOrderList);
								$("#linkOrder").html('Modify Linked Order');
								setFieldVisibility();
							}
						},
						dataType: "json"
					});
					
					$('#ralinktoorder').change(function () {
						var nochargeval = parseInt(getSelectedValue('ralinktoorder'));
						var checkNoCharge = isNaN(nochargeval) ? null : nochargeval;
						setFieldVisibility();
					});
					
					$('#nochargereason').change(function () {
						var val = parseInt(getSelectedValue('nochargereason'));
						var intval = isNaN(val) ? null : val;
						var linktoorder = getSelectedDataAttribute('nochargereason', 'linktoordernumber');
						
						setFieldVisibility();
					});
					
					$('#linkOrder').click(function (e) {
						e.stopPropagation();
						var orders_table = $('#OrderData').DataTable({
							ajax: {
								"url": AJAX_ENDPOINT_ORDERMANAGER,
								"data": function (data) {
									return $.extend({}, data, {
										"Action": "getorderhistory",
										"CreatedAfterDate": "2014-01-01",
										"Session": sid,
										"ConsumerID": consumerId
									});
								},
								"language": {
									"emptyTable": "No orders were found.",
									"zeroRecords": "No orders were found."
								},
								"type": "POST",
								"dataSrc": function (json)
								{
									//what'd we get??
									var return_data = new Array();

									//error handling
									var errorEncountered = false;
									if (json.Order == null) {
										errorEncountered = true;
									}
									//successful response
									else {
										for (var x = 0; x < json.Order.length; x++) {
											if (json.Order[x].OrderNumber != null) {
												return_data.push({
													'OrderNumber': json.Order[x].OrderNumber,
													'OrderDate': json.Order[x].OrderDate,
													'PartNumbers': json.Order[x].PartNumbers
												});
											}
										}
									}

									//got data back
									if (return_data.length > 0) {
										$('#orders_error').hide();
										$('#orders_grid').show();
									}
									//no data received
									else {
										//error encountered
										if (errorEncountered == true) {
											$('#orders_grid').hide();
											$('#orders_error').html(json.ResponseMessage + '.');
											$('#orders_error').css('color', 'red');
											$('#orders_error').show();
										}
										//no orders found
										else {
											$('#orders_grid').hide();
											$('#orders_error').html('No orders were found for this contact.');
											$('#orders_error').show();
										}
									}
									return return_data;
								}
							},
							"destroy": true,
							"select": true,
							"pageLength": 50,
							"searching": false,
							"ordering": false,
							"info": false,
							"pagingType": "full_numbers",
							//"scrollX": true,
							//"responsive": true,
							"columns": [
								{
									"class": "details-control",
									"orderable": false,
									"data": null,
									"defaultContent": "",
									"width": "25px"
								},
								{
									"class": "orderNumber",
									"data": "OrderNumber"
								},
								{
									"data": "OrderDate",
									"defaultContent": "",
									"render": function (data) {
										var date = new Date(data);
										var month = date.getMonth() + 1;
										return (month > 9 ? month : "0" + month) + "-" + date.getDate() + "-" + date.getFullYear();
									}
								},
								{
									"data": "PartNumbers"
								}
							]
						});
						
						$('#linkToOrderNumber').modal('show');
						var detailRows = [];
						$('#OrderData tbody').on('click', 'td.details-control', function () {
							console.log('Button Clicked');
							var tr = $(this).closest('tr');
							var row = orders_table.row( tr );
							var idx = $.inArray( tr.attr('id'), detailRows );
				 
							if (row.child.isShown()) {
								//reset current order details
								currOrderNumber = "";
								currOrderTotal = "";
								currOrderStatus = "";

								//hide child row
								tr.removeClass('details');
								tr.removeClass('shown');
								row.child.hide();

								//hide buttons
								$('#order-action-buttons').hide();

								// Remove from the 'open' array
								detailRows.splice(idx, 1);
							}
							else {
								//show child row
								tr.addClass('details');
								tr.addClass('shown');
								row.child(format(row.data())).show();
				 
								// Add to the 'open' array
								if ( idx === -1 ) {
									detailRows.push( tr.attr('id') );
								}
							}
						});
						
						//Begin Format
						
						/**
						 * Get and format the child table html
						 * This is where we:
						 *  make the PaymentHistory and OrderDetail tables
						 *  set the click events
						 * @param {type} d
						 * @returns {type} 
						 */
						function format(d)
						{
							/* Order Details */
							//set current order info
							currOrderNumber = d.OrderNumber;
							
							//AJAX call
							var ajaxURL = AJAX_ENDPOINT_ORDERMANAGER;
							$.ajax({
								type: "POST",
								url: ajaxURL,
								data: {
									Action: "getlineitemhistory",
									Session: getParameterByName('sid'),
									OrderNumber: currOrderNumber,
									ConsumerID: getParameterByName('consumer_id')
								},
								dataType: "json"
							}).done(function (json) {
								//error handling
								if (json.ResponseMessage != "Success") {
									//order details grid
									$('#order_details_' + currOrderNumber).html(json.ResponseMessage + '.');
									$('#order_details_' + currOrderNumber).css('color', 'red');
									$('#order_details_' + currOrderNumber).css('font-weight', 'bold');

									//payment history grid
									$('#payments_grid_' + currOrderNumber + '_error').html('');
								}
								//successful call
								else {

									/* Order Details Grid */
									//populate order details datagrid
									CreateOrderDetailsGrid(currOrderNumber, json.LineItems, json.ShippingAddress);

									//hide loading message
									$('#order_details_' + currOrderNumber + '_error').hide();

									//show tables
									$('#order_details_' + currOrderNumber + '_table').show();
								}
							}).fail(function(jqxhr, textStatus, error){
								var errorMSG = textStatus + ": " + error;
								$('#order_details_' + currOrderNumber).html(errorMSG);
							});
							
							//make a div to alter
							var return_div = '<div id="div_' + currOrderNumber + '">';

							var htmlOrderDetails = '<div id="order_details_' + currOrderNumber + '" style="width:100%;margin:5px 0px;padding:10px;border:1px solid #AAAAAA;">' +
												'<div class="order_details_title"><b><u>Order Details</u></b></div>' +
												'<div id="order_details_' + currOrderNumber + '_error">Loading order details for Order #' + currOrderNumber + '...</div>' +
												'<div id="order_details_' + currOrderNumber + '_table" style="display:none;">' +
													'<table cellpadding="0" cellspacing="0" border="0" class="orderDetailsTable table table-striped display" id="OrderDetailsHeader_' + currOrderNumber + '" width="100%">' +
													'</table>' +
												'</div>' +
											 '</div>';

							
							//concatenate html
							return_div += htmlOrderDetails;
							//close off div
							return_div += '</div>';

							//show the people
							return return_div;
						}
						
						//End Format
						
						/**
						 * Create a grid for a specific order detai line items
						 */
						function CreateOrderDetailsGrid(gridID, json, shippingAddress) {
							order_details_tables['order_details_table_' + gridID] = $('#OrderDetailsHeader_' + gridID).DataTable({
								data: json,
								"destroy": true,
								"select": { style: 'multi' },
								"searching": false,
								"ordering": false,
								"info": false,
								"paging": false,
								/*"responsive": false,
								"scrollX": true,
								"scrollY": true,*/
								"columns": [
									{
										"class": "select-checkbox",
										"orderable": false,
										"data": null,
										"defaultContent": "",
										"width": "25px"
									},
									
									{
										"title": "Part Number",
										"data": "PartNo",
										"width": "50px"
									},
									{
										"title": "Part Description",
										"data": "PartDesc",
										"width": "100px"
									},
								]
							});
							
							$('#OrderDetailsHeader_' + gridID + ' thead th').removeClass('select-checkbox');
							
							// Add event listener for opening and closing details
							$('.orderDetailsTable tbody').on('click', 'td.details-control2', function (e) {
								e.stopImmediatePropagation();
								console.log('Details Control2 Clicked');
								var tr  = $(this).closest('tr'),
								//row = $('.orderDetailsTable').DataTable().row(tr);
								row = $(this).closest('.orderDetailsTable').DataTable().row(tr);
										 
								if ( row.child.isShown() ) {
									// This row is already open - close it
									row.child.hide();
									tr.removeClass('shown');
								}
								else {
									// Open this row
									row.child( formatdetail(row.data(), gridID) ).show();
									tr.addClass('shown');
								}
							} );
						}
						
					});
					
					$('#cancellinktoorder').click(function () {
						$('#linkToOrderNumber').modal('hide');
					});
					
					$('#submitlinktoorder').click(function () {
						var i = 0;
						
						let selectedOrderArray = [];
						var countOrdersWithRowsSelected = 0;
						
						$('#OrderData > tbody  > tr').each(function (e) {
							var $this = $(this);
							
							var roleOption = $.trim($(this).attr('role'));
							
							if(roleOption != 'row'){
								var selectedOrder = $(this).closest('tr').prev().find("td.orderNumber").text();
								
								var tblData = order_details_tables['order_details_table_' + selectedOrder].rows('.selected').data();
								
								if(tblData.length > 0){
									selectedOrderArray.push([selectedOrder,tblData.length]);
									countOrdersWithRowsSelected++;
								}
							}
							
							i++;
						});
						
						if(countOrdersWithRowsSelected == 0){
							alert('You must select at least one order');
						} else if (countOrdersWithRowsSelected > 1){
							alert('You may only link no charge orders to one existing order at a time');
						} else {
							
							var body = $("#tblLinkedOrderData").find('tbody');
							var rows = body.find('tr');
							for (var i = 0; i < rows.length; i++){
								console.log('Row Data: ' + rows[i]);
								var lineIDVal = $(rows[i]).find('td.clsLockID').val();
								var lineIDText = $(rows[i]).find('td.clsLockID').text();
								
								$.ajax({
									type: "POST",
									url: AJAX_ENDPOINT + '?action=deleteorderlock&orderlockid=' + lineIDText + '&sid=' + sid,
									cache: false,
									success: function (data) {
										
									},
									dataType: "json",
									error: function (request, status, error) {
									}
								});
								$(rows[i]).remove();
							}
								
							
							var selectedOrder = selectedOrderArray[0][0];
							
							var tblData = order_details_tables['order_details_table_' + selectedOrder].rows('.selected').data();
							
							var txtOrderList = '';
							var tmpData;
							  $.each(tblData, function(i, val) {
								tmpData = tblData[i];
								
								if(txtOrderList){
									txtOrderList += ', ' + tmpData['PartNo'];
								} else {
									txtOrderList = tmpData['PartNo'];
								}
								
								createOrderLock(tmpData['LineId'], selectedOrder, 0, tmpData['PartNo']);
								
							  }); 
							$("#ordernumber").val(selectedOrder);
							$("#divOrderLinkSummaryOrderNum").html('<b>Original Order Number:</b> ' + selectedOrder);
							$("#divOrderLinkSummaryLines").html('<b>SKU(s):</b> ' + txtOrderList);
							$("#linkOrder").html('Modify');
							
							setFieldVisibility();
							$('#linkToOrderNumber').modal('hide');
						}
					});
				});
				
				function createOrderLock(ebsLineID, origOrderNum, newOrderNum, partNum) {

					var info = {};
					info.Source = "Phone";
					info.SKU = partNum;
					info.EBSLineID = ebsLineID;
					info.NewOrderNumber = newOrderNum;
					info.OriginalOrderNumber = origOrderNum;
					info.IncidentID = incident.id;
					info.OrderHeaderID = orderHeaderId;

					$.ajax({
						type: "POST",
						url: AJAX_ENDPOINT + '?action=createorderlock&sid=' + sid,
						data: JSON.stringify(info),
						async: false,
						success: function (data) {
							$("#tblLinkedOrderData").find('tbody')
								.append($('<tr>')
									.append($('<td>')
										.text(ebsLineID)
										.addClass('clsLineID')
									).append($('<td>')
										.text(origOrderNum)
										.addClass('clsOrderNum')
									).append($('<td>')
										.text(data.OrderLockID)
										.addClass('clsLockID')
									).append($('<td>')
										.text(partNum)
										.addClass('clsPartNum')
									)
								);
							// }); 
						},
						dataType: "json",
						error: function(error){
							
						}

					});

				}
			});
		});
	});
});