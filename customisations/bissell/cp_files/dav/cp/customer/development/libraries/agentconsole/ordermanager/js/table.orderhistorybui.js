ORACLE_SERVICE_CLOUD.extension_loader.load("BissellOrderHistory" , "1.0").then(function(extensionProvider) {
	extensionProvider.registerWorkspaceExtension(function(WorkspaceRecord) {
		
		//local storage object
		localStorage.clear();
		
		//set paths
		var CUSTOM_SCRIPTS_PATH = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/";
		var PORTAL_SCRIPT_PATH = "";
		//Set paths for sites other than localhost
		if (!(location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
			CUSTOM_SCRIPTS_PATH = "/cgi-bin/bissell.cfg/php/custom/";
			PORTAL_SCRIPT_PATH = "../cp/customer/development/libraries/agentconsole/ordermanager/";
		}

		//AJAX endpoints
		//var AJAX_ENDPOINT_ORDERMANAGER = CUSTOM_SCRIPTS_PATH + "ordermanagerajaxhandler.php";
		var AJAX_ENDPOINT_ORDERMANAGER = CUSTOM_SCRIPTS_PATH + "contactorderhistory.php";
		var AJAX_ENDPOINT_PAYMENTSERVICE = CUSTOM_SCRIPTS_PATH + "paymentservice.php";
		var AJAX_ENDPOINT_TAXSERVICE = CUSTOM_SCRIPTS_PATH + "calculatesalestax.php";

		//DataTables
		var payment_tables = {};
		var payment_tables_editors = {};
		var order_details_tables = {};
		var order_line_item_total_tables = {};
		var order_line_item_coupon_tables = {};

		//current OSvC object ID
		var workspaceType = WorkspaceRecord.getWorkspaceRecordType();
		var isIncident = false;
		
		if(workspaceType == 'Contact')
			var contactID = WorkspaceRecord.getWorkspaceRecordId();
		else if(workspaceType == 'Incident')
		{
			var incidentID = WorkspaceRecord.getWorkspaceRecordId();
			isIncident = true;
		}
		
		//current Customer Transaction
		var customerTRX = "";
		//current OrderHeader ID
		var orderHeaderID = "";
		//currently selected order's info
		var currOrderNumber = "";
		var currOrderHeaderID = "";
		var currOrderTotal = "";
		var currOrderBalance = 0;
		var currOrderStatus = "";
		var currOrderOracleAccountNo = "";
		//currently selected payment's info
		var currPaymentSettlementResponse = "";
		var currPaymentBalance = "";
		var currPaymentDate = "";
		var currPaymentRowIndex = -1;
		//outstanding payment in the past
		var pastDuePayment = false;

		//tax
		var currSalesTax = "";

		//shipping/billing address - for calculating sales tax @~1420
		var addrBilling = [];
		var addrShipping = [];

		//storage object for CC details
		var ccDetails = getStorageObject('ccDetails') || {};

		//array of payments for batch processing
		var paymentTotal = 0;
		var paymentScheduleIDs = [];
		var paymentSelectionTable;
		var paymentSelectionOptions = [];

		//list of US state abbreviations
		var stateListManual = [
			{
				"name": "Alabama",
				"abbreviation": "AL"
			},
			{
				"name": "Alaska",
				"abbreviation": "AK"
			},
			{
				"name": "American Samoa",
				"abbreviation": "AS"
			},
			{
				"name": "Arizona",
				"abbreviation": "AZ"
			},
			{
				"name": "Arkansas",
				"abbreviation": "AR"
			},
			{
				"name": "California",
				"abbreviation": "CA"
			},
			{
				"name": "Colorado",
				"abbreviation": "CO"
			},
			{
				"name": "Connecticut",
				"abbreviation": "CT"
			},
			{
				"name": "Delaware",
				"abbreviation": "DE"
			},
			{
				"name": "District Of Columbia",
				"abbreviation": "DC"
			},
			{
				"name": "Federated States Of Micronesia",
				"abbreviation": "FM"
			},
			{
				"name": "Florida",
				"abbreviation": "FL"
			},
			{
				"name": "Georgia",
				"abbreviation": "GA"
			},
			{
				"name": "Guam",
				"abbreviation": "GU"
			},
			{
				"name": "Hawaii",
				"abbreviation": "HI"
			},
			{
				"name": "Idaho",
				"abbreviation": "ID"
			},
			{
				"name": "Illinois",
				"abbreviation": "IL"
			},
			{
				"name": "Indiana",
				"abbreviation": "IN"
			},
			{
				"name": "Iowa",
				"abbreviation": "IA"
			},
			{
				"name": "Kansas",
				"abbreviation": "KS"
			},
			{
				"name": "Kentucky",
				"abbreviation": "KY"
			},
			{
				"name": "Louisiana",
				"abbreviation": "LA"
			},
			{
				"name": "Maine",
				"abbreviation": "ME"
			},
			{
				"name": "Marshall Islands",
				"abbreviation": "MH"
			},
			{
				"name": "Maryland",
				"abbreviation": "MD"
			},
			{
				"name": "Massachusetts",
				"abbreviation": "MA"
			},
			{
				"name": "Michigan",
				"abbreviation": "MI"
			},
			{
				"name": "Minnesota",
				"abbreviation": "MN"
			},
			{
				"name": "Mississippi",
				"abbreviation": "MS"
			},
			{
				"name": "Missouri",
				"abbreviation": "MO"
			},
			{
				"name": "Montana",
				"abbreviation": "MT"
			},
			{
				"name": "Nebraska",
				"abbreviation": "NE"
			},
			{
				"name": "Nevada",
				"abbreviation": "NV"
			},
			{
				"name": "New Hampshire",
				"abbreviation": "NH"
			},
			{
				"name": "New Jersey",
				"abbreviation": "NJ"
			},
			{
				"name": "New Mexico",
				"abbreviation": "NM"
			},
			{
				"name": "New York",
				"abbreviation": "NY"
			},
			{
				"name": "North Carolina",
				"abbreviation": "NC"
			},
			{
				"name": "North Dakota",
				"abbreviation": "ND"
			},
			{
				"name": "Northern Mariana Islands",
				"abbreviation": "MP"
			},
			{
				"name": "Ohio",
				"abbreviation": "OH"
			},
			{
				"name": "Oklahoma",
				"abbreviation": "OK"
			},
			{
				"name": "Oregon",
				"abbreviation": "OR"
			},
			{
				"name": "Palau",
				"abbreviation": "PW"
			},
			{
				"name": "Pennsylvania",
				"abbreviation": "PA"
			},
			{
				"name": "Puerto Rico",
				"abbreviation": "PR"
			},
			{
				"name": "Rhode Island",
				"abbreviation": "RI"
			},
			{
				"name": "South Carolina",
				"abbreviation": "SC"
			},
			{
				"name": "South Dakota",
				"abbreviation": "SD"
			},
			{
				"name": "Tennessee",
				"abbreviation": "TN"
			},
			{
				"name": "Texas",
				"abbreviation": "TX"
			},
			{
				"name": "Utah",
				"abbreviation": "UT"
			},
			{
				"name": "Vermont",
				"abbreviation": "VT"
			},
			{
				"name": "Virgin Islands",
				"abbreviation": "VI"
			},
			{
				"name": "Virginia",
				"abbreviation": "VA"
			},
			{
				"name": "Washington",
				"abbreviation": "WA"
			},
			{
				"name": "West Virginia",
				"abbreviation": "WV"
			},
			{
				"name": "Wisconsin",
				"abbreviation": "WI"
			},
			{
				"name": "Wyoming",
				"abbreviation": "WY"
			}
		];

		//list of Canadian province abbreviations
		var provinceListManual = [
			{
				"name": "Alberta",
				"abbreviation": "AB"
			},
			{
				"name": "British Columbia",
				"abbreviation": "BC"
			},
			{
				"name": "Manitoba",
				"abbreviation": "MB"
			},
			{
				"name": "New Brunswick",
				"abbreviation": "NB"
			},
			{
				"name": "Newfoundland and Labrador",
				"abbreviation": "NL"
			},
			{
				"name": "Northwest Territories",
				"abbreviation": "NT"
			},
			{
				"name": "Nova Scotia",
				"abbreviation": "NS"
			},
			{
				"name": "Nunavut",
				"abbreviation": "NU"
			},
			{
				"name": "Ontario",
				"abbreviation": "ON"
			},
			{
				"name": "Prince Edward Island",
				"abbreviation": "PE"
			},
			{
				"name": "Quebec",
				"abbreviation": "QC"
			},
			{
				"name": "Saskatchewan",
				"abbreviation": "SK"
			},
			{
				"name": "Yukon Territory",
				"abbreviation": "YT"
			}
		];

		//list of Canadian province abbreviations
		var warehouseListManual = [
			{
				"name": "Elwood",
				"abbreviationList": ["TEL", "ELW", "LEL"]
			},
			{
				"name": "Mesquite",
				"abbreviationList": ["TME", "MES", "LME"]
			},
			{
				"name": "Rialto",
				"abbreviationList": ["TRI", "RIA", "LRI"]
			},
			{
				"name": "RVirginia",
				"abbreviationList": ["TRV", "RVA", "LRV"]
			},
			{
				"name": "Seattle",
				"abbreviationList": ["TSE", "SEA", "LSE"]
			},
			{
				"name": "Pharr",
				"abbreviationList": ["TPH", "PHA"]
			},
			{
				"name": "Walker",
				"abbreviationList": ["TWA", "WAL"]
			},
			{
				"name": "Milton",
				"abbreviationList": ["TBR", "BRM"]
			},
			{
				"name": "Richmond",
				"abbreviationList": ["TRC", "RIC"]
			}
		];

		//list of states/provinces to use for CC payment
		var ccStateList = [];

		//conditional var to show/hide payment buttons
		var showButtons = "hidden";

		//message to show when the payments/orderlineitem datagrids load
		var preLoadedMessage = "";

		(function ($)
		{
			$(document).ready(function ()
			{
				//set "waiting" cursor
				$(document).ajaxStart(function () {
					$(document.body).css({ 'cursor': 'wait' });
				}).ajaxStop(function () {
					$(document.body).css({ 'cursor': 'default' });
					});

				//Main "Orders" DataTable
				var orders_table = $('#OrderHeader').DataTable({
					ajax: {
						"url": AJAX_ENDPOINT_ORDERMANAGER + '?action=getorderhistory',
						"data": function (data) {
							return $.extend({}, data, {
								"sid": getParameterByName('sid'),
								"ConsumerID": getParameterByName('consumer_id')
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
											'OrderStatus': json.Order[x].OrderStatus,
											'OrderType': json.Order[x].OrderType,
											'OrderTotal': json.Order[x].OrderTotal,
											'PartNumbers': json.Order[x].PartNumbers,
											'EBayOrderId': json.Order[x].EBayOrderId,
											'EBayOrderNum': json.Order[x].EBayOrderNum,
											'PaymentType': json.Order[x].PaymentType,
											'IsSubscription': json.Order[x].IsSubscription
											//'paymethod': parsedJSON.Order[x].paymethod,
											//'csrname': parsedJSON.Order[x].csrname,
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
					"select": true,
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
							"data": "OrderNumber",
							"render": function (data) {
								if (data) {
									var orderLink = '<a href="javascript:void(0);" class="editor_remove" data-container="body" data-placement="right" data-toggle="confirmation" data-title="Associate order # ' + data + ' to Incident?"' + ' data-order-number="' + data + '">' + data + '</a>';
									if (isIncident)
										return orderLink;
									return data;
								}
								return '';
							}
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
							"data": "OrderStatus"
						},
						{
							"data": "OrderType"
						},
						{
							"data": "OrderTotal",
							"render": function (data, type, row) {
								var amount = accounting.toFixed(parseFloat(row.OrderTotal), 2);
								var return_data = "$" + amount;
								return return_data;
							}
						},
						/*{
							"data": "paymethod"
						},*/
						{
							"data": "PartNumbers"
						},
						/*{
							"data": "csrname"
						},*/
						{
							"data": "EBayOrderNum"
						},
						{
							"data": "PaymentType"
						},
						{
							"data": "IsSubscription",
							"render": function (data) {
								if (data === "Y") {
									return "Yes";
								}
								return '';
							}
						}
					]
				});

				//click handler for main "Orders" DataTable
				var detailRows = [];
				$('#OrderHeader tbody').on('click', 'td.details-control', function () {
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
						//pre-populated message
						if (preLoadedMessage != "")
						{
							//set/show alert
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="message" style="color:green;font-weight:bold;">' + preLoadedMessage + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}

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
				
				//instantiate payment selection datatable
				paymentSelectionTable = $('#SelectPayments').DataTable({
					"select": {
						"style": "multi",
						"selector": 'td:first-child'
						},
					"searching": false,
					"ordering": false,
					"paging": false,
					//"responsive": true,
					//"scrollX": true,
					//"scrollY": true,
					"columns": [
						{
							"data": null,
							"title": "Pay?",
							"render": function (data, type, row)
							{
								return null;
							}
						},
						{
							"data": "paymentID",
							"title": "Payment ID",
						},
						{
							"data": "amount",
							"tield": "Amount",
						}
					],
					"columnDefs": [{
						"orderable": false,
						"className": 'select-checkbox',
						"targets": 0
					}],
					});

				//validation
				jQuery.validator.addMethod("cardtypematchesnumber", function (value, element) {
					var valid = true;
					var typeFromNumber;
					var cardNumber = $("#cd_creditcardnumber").val();
					var cardType = getSelectedValue("cd_creditcardtype");

					if (cardNumber.match("^5"))
						typeFromNumber = "M";
					if (cardNumber.match("^4"))
						typeFromNumber = "V";
					if (cardNumber.match("^3"))
						typeFromNumber = "A";
					if (cardNumber.match("^6"))
						typeFromNumber = "D";
					if (cardType != typeFromNumber)
						valid = false;

					return valid;

				}, 'Card type and number mismatch');

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
					currOrderStatus = d.OrderStatus;
					currOrderTotal = d.OrderTotal;
					
					//only show buttons if the order has a total > 0
					if (currOrderBalance != 0 && currOrderBalance != "0")
					{
						showButtons = "block";
					}

					//return variable
					var finalHTML = '';
					//AJAX call
					var ajaxURL = AJAX_ENDPOINT_ORDERMANAGER + '?action=getlineitemhistory';
					$.ajax({
						type: "POST",
						url: ajaxURL,
						data: {
							sid: getParameterByName('sid'),
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
							//first, set currOrderOracleAccountNo
							currOrderOracleAccountNo = json.OracleAccountNo;

							//set current order total
							currOrderTotal = json.OrderTotal;

							/* General Updates now that HTML has been added */
							//set button click functionality here
							$('#make_payment_' + d.OrderNumber).click(function () {
								if ($(this).hasClass('disabled')) {
									//set alert
									var errorMSG = 'Please save the payment schedule updates in order to apply funds.';
									$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
									$('#payments_grid_' + currOrderNumber + '_error').show();
									//exit
									return;
								}
								else
									MakePayment(currOrderNumber);
							});
							$('#change_card_details_' + currOrderNumber).click(function () {
								//check for overdue payments
								CheckPastDuePayments(currOrderNumber);
								//alert agent if payments are overdue
								if (pastDuePayment == true) {
									var warningMSG = 'Past due payments detected with a due date in the past.<br/>' +
										'These payments will need to be updated to a date in the future for future payment attempts to be submitted.';
									$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + warningMSG + '</div>');
									$('#payments_grid_' + currOrderNumber + '_error').show();
								}
								else {
									ChangeCardDetails_ShowDialog(currOrderNumber);
								}
							});
							$('#save_schedule_' + currOrderNumber).click(function () {
								SavePaymentScheduleChanges(currOrderNumber);
							});
							$('#cancel_schedule_' + currOrderNumber).click(function () {
								if ($(this).hasClass('disabled'))
									return;
								ResetPaymentGrid(currOrderNumber);
							});
							
							//set click events for 'change cc details'
							$('#save_cc_details').click(function () {
								ChangeCardDetails_SaveData(currOrderNumber);
							});
							$('#cancel_cc_details').click(function () {
								if (window.creditCardValidator)
									window.creditCardValidator.resetForm();
								$('#pay_hist_change_cc_details').modal('hide');
							});
							//set click events for 'make a payment'
							$('#queue_payment').click(function () {
								SubmitPayment();
							});
							$('#cancel_payment').click(function () {
								paymentTotal = 0;
								$('#payment_amount').val('');
								$('#make_payment_modal').modal('hide');
							});

							//drop-down lists for modal forms
							$('#cd_creditcardexpirationyear').append("<option value=''>[+]</option>");
							$('#creditcardexpirationyear').append("<option value=''>[+]</option>");
							var currentYear = (new Date()).getFullYear();
							var endYear = currentYear + 11;

							for (var i = currentYear; i < endYear; i++) {
								$('#cd_creditcardexpirationyear').append($("<option value=''></option>")
									.attr("value", i)
									.text(i));
								$('#creditcardexpirationyear').append($("<option value=''></option>")
									.attr("value", i)
									.text(i));
							}

							//set States in drop-down list
							//var stateList = getListValues('AddrProvId');
							/*$('#cd_creditcardstate').empty();
							$('#cd_creditcardstate').append("<option value=''>[+]</option>");
							$('#creditcardstate').empty();
							$('#creditcardstate').append("<option value=''>[+]</option>");
							$.each(stateListManual, function (index, stateItem) {
								$('#cd_creditcardstate').append($("<option></option>")
									.attr("value", stateItem.abbreviation)
									.text(stateItem.name));
								$('#creditcardstate').append($("<option></option>")
									.attr("value", stateItem.abbreviation)
									.text(stateItem.name));
							});*/

							/* Address Details */
							CreateAddressInfo(currOrderNumber, json);

							//payment options (modal window)
							$('#payment_dropdown').change(function () {
								var selectedOption = $(this).val();
								switch (selectedOption) {
									//pay in full
									case "0":
										$('#payment_amount').val(accounting.toFixed(currOrderBalance, 2));
										$('#select_payment_list_div').hide();
										break;
									//enter amount manually
									case "1":
										$('#payment_amount').val('0.00');
										$('#select_payment_list_div').hide();
										break;
									//coolness (select payments)
									case "2":
										$('#payment_amount').val(accounting.toFixed(paymentTotal, 2));
										$('#select_payment_list_div').show();
										break;
								}
							});


							/* Payments Grid */
							//populate payments datagrid
							CreatePaymentsGrid(currOrderNumber);

							/* Order Details Grid */
							//populate order details datagrid
							CreateOrderDetailsGrid(currOrderNumber, json.LineItems, json.ShippingAddress);

							//populate coupon, total/tax info
							FillInOrderLineItemDetails(currOrderNumber, json);

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

					//bill to and ship to address details
					var addressInfo = '<div id="address_info_' + currOrderNumber + '" style="width:100%;margin:5px 0px;padding:10px;border:1px solid #AAAAAA;">' +
										'<div class="order_details_title"><b><u>Address Details</u></b></div>' +
										'<div id="address_info_' + currOrderNumber + '_error" style="color:red;font-weight:bold;display:none;"></div>' +
										'<div id="address_info_billto_' + currOrderNumber + '" style="float:left;margin-right:20px;margin-top:10px"></div>' +
										'<div id="address_info_shipto_' + currOrderNumber + '" style="float:left;margin-right:20px;margin-top:10px"></div>' +
										'<div class="spacer" style="clear: both;"></div>' +
									  '</div>';

					//actionable buttons
					var htmlButtons = '<div id="order-action-buttons" style="display:' + showButtons + ';margin:10px 0px;">' +
										'<div id="details_payment_' + currOrderNumber + '" style="float:left;display:none;">' +
											'<button type="button"' +
												'class="btn blue mt-ladda-btn ladda-button"' +
												'data-style="slide-right"' +
												'id="change_card_details_' + currOrderNumber + '">' +
													'Change Card Details' +
											'</button>' +
										'</div>' +
										'<div id="payment_buttons_' + currOrderNumber + '" style="float:left;display:none;">' +
											'<button type="button"' +
												'class="btn blue mt-ladda-btn ladda-button"' +
												'data-style="slide-right"' +
												'id="make_payment_' + currOrderNumber + '">' +
													'Make a Payment' +
											'</button>' +
										'</div>' +
										'<div id="schedule_changes_' + currOrderNumber + '" style="float:left;display:none;">' +
											'<button type="button"' +
												'class="btn blue mt-ladda-btn ladda-button disabled"' +
												'data-style="slide-right"' +
												'id="save_schedule_' + currOrderNumber + '">' +
													'Save Payment Schedule' +
											'</button>' +
										'</div>' +
									  '</div>';
					//payments grid
					var htmlPaymentsGrid = '<div id= "payment_grid_' + currOrderNumber + '" style="width:100%;margin:5px 0px;padding:10px;border:1px solid #AAAAAA;"> ' +
											   '<div class="order_details_title"><b><u>Payment History Details</u></b></div>' +
											   '<div id="payments_grid_' + currOrderNumber + '_error">Loading payments for Order #' + currOrderNumber + '...</div>' +
											   htmlButtons +
											   '<br/><br/><div id="order_total_' + currOrderNumber + '" style="clear:both;font-weight:bold;">' +
												   'Remaining Balance:' +
											   '</div>' +
											   '<div id="payment_table_' + currOrderNumber + '" style="display:none;">' +
												   '<table cellpadding="0" cellspacing="0" border="0" class="table table-striped display" id="PaymentsHeader_' + currOrderNumber + '" width="100%">' +
												   '</table>' +
												'</div>' +
										   '</div>' +
									   '</div>';
					//order details (pre-populate with a loading message)
					var htmlOrderDetails = '<div id="order_details_' + currOrderNumber + '" style="width:100%;margin:5px 0px;padding:10px;border:1px solid #AAAAAA;">' +
												'<div class="order_details_title"><b><u>Order Details</u></b></div>' +
												'<div id="order_details_' + currOrderNumber + '_error">Loading order details for Order #' + currOrderNumber + '...</div>' +
												'<div id="order_details_' + currOrderNumber + '_table" style="display:none;">' +
													'<table cellpadding="0" cellspacing="0" border="0" class="orderDetailsTable table table-striped display" id="OrderDetailsHeader_' + currOrderNumber + '" width="100%">' +
													'</table>' +
												'</div>' +
												'<div id="coupon_codes_' + currOrderNumber + '" style="width:22.5%;margin:5px 0px;padding:10px;display:inline-block;">' +
													'<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="CouponCodes_table_' + currOrderNumber + '" width="100%">' +
														'<thead>' +
															'<tr>' +
																'<th>SKU</th>' +
																'<th>Coupon Code</th>' +
															'</tr>' +
														'</thead>' +
													'</table>' +
												'</div>' +
												'<div id="lineitem_total_' + currOrderNumber + '" style="width:72.5%;margin:5px 0px;padding:10px;display:inline-block;">' +
													'<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="LineItemTotal_table_' + currOrderNumber + '" width="100%">' +
														'<tr>' +
															'<td>Sub-Total:</td>' +
															'<td><div id="subtotal_' + currOrderNumber + '"></div></td>' +
														'</tr>' +
														'<tr>' +
															'<td>Shipping:</td>' +
															'<td><div id="shipping_' + currOrderNumber + '"></div></td>' +
														'</tr>' +
														'<tr>' +
															'<td>Sales Tax:</td>' +
															'<td id="salestax_' + currOrderNumber + '"></td>' +
														'</tr>' +
														'<tr style="display:none;">' +
															'<td>Secondary Tax:</td>' +
															'<td id="secondarytax_' + currOrderNumber + '"></td>' +
														'</tr>' +
														'<tr>' +
															'<td>Discounts:</td>' +
															'<td id="discounts_' + currOrderNumber + '"></td>' +
														'</tr>' +
														'<tr>' +
															'<td><b>Total:</b></td>' +
															'<td id="total_' + currOrderNumber + '"></td>' +
														'</tr>' +
													'</table>' +
												'</div>' +
											 '</div>';
					//concatenate html
					return_div += addressInfo + htmlPaymentsGrid + htmlOrderDetails;
					//close off div
					return_div += '</div>';

					//show the people
					return return_div;
				}

				/* Formatting function for row details - modify as you need */
				function formatdetail ( d, gridID ) {
					// `d` is the original data object for the row
					
					var trackingLink = '';
					if (d.OnlineReturnTrackingNo)
						trackingLink = '<a href= "https://www.fedex.com/fedextrack/?tracknumbers=' + d.OnlineReturnTrackingNo +'" target="_blank">' + d.OnlineReturnTrackingNo + "</a>";
					
					var rowHeader = '';
					var rowReturnDate = '';
					var rowReturnDeliveryDate = '';
					var rowReturnShipDate = '';
					var rowTrackingNumber = '';
					var rowReturnReason = '';
					var rowReturnQuantity = '';
					
					for (var i = 0; i < d.OnlineReturnArray.length; i++) {
						var backgroundColor = 'lightCyan';
						
						if((i%2)==0){
							backgroundColor = 'lightGoldenrodYellow';
						}
						
						var lastRowBorder = '';
						
						if((i+1)==d.OnlineReturnArray.length)
							lastRowBorder = 'border-right:1px solid gray;';
						
						var returnDate = '';
						if (d.OnlineReturnArray[i].OnlineReturnDate)
							returnDate = d.OnlineReturnArray[i].OnlineReturnDate.substr(4,2) + '/' + d.OnlineReturnArray[i].OnlineReturnDate.substr(6,2) + '/' + d.OnlineReturnArray[i].OnlineReturnDate.substr(0,4);
						
						rowReturnDate += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; text-align:center; background-color:' + backgroundColor +';">'+returnDate+'</td>';
						
						var returnDeliveryDate = '';
						if (d.OnlineReturnArray[i].OnlineReturnDeliveryDate)
							returnDeliveryDate = d.OnlineReturnArray[i].OnlineReturnDeliveryDate.substr(4,2) + '/' + d.OnlineReturnArray[i].OnlineReturnDeliveryDate.substr(6,2) + '/' + d.OnlineReturnArray[i].OnlineReturnDeliveryDate.substr(0,4);
						
						rowReturnDeliveryDate += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; text-align:center; background-color:' + backgroundColor +';">'+returnDeliveryDate+'</td>';
						
						var returnShipDate = '';
						if (d.OnlineReturnArray[i].OnlineReturnShipDate)
							returnShipDate = d.OnlineReturnArray[i].OnlineReturnShipDate.substr(4,2) + '/' + d.OnlineReturnArray[i].OnlineReturnShipDate.substr(6,2) + '/' + d.OnlineReturnArray[i].OnlineReturnShipDate.substr(0,4);
						
						rowReturnShipDate += '<td style="' + lastRowBorder + ' border-bottom:1px solid gray; font-size:0.8em; padding:2px; text-align:center; background-color:' + backgroundColor +';">'+returnShipDate+'</td>';
						
						var trackingLink = '';
						if (d.OnlineReturnArray[i].OnlineReturnTrackingNo){
							trackingLink = '<a href= "https://www.fedex.com/fedextrack/?tracknumbers=' + d.OnlineReturnArray[i].OnlineReturnTrackingNo + '" target="_blank">' + d.OnlineReturnArray[i].OnlineReturnTrackingNo + "</a>";
							rowTrackingNumber += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; text-align:center; background-color:' + backgroundColor +';">'+trackingLink+'</td>';
						}
						
						rowReturnReason += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; padding-left:5px; padding-right:5px; text-align:center; background-color:' + backgroundColor +';">'+d.OnlineReturnArray[i].OnlineReturnReason+'</td>';
						
						rowReturnQuantity += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; text-align:center; background-color:' + backgroundColor +';">'+d.OnlineReturnArray[i].OnlineReturnQty+'</td>';
						
						rowHeader += '<td style="' + lastRowBorder + ' border-bottom:1px solid lightgray; font-size:0.7em; padding:2px; color:white; font-style:italic; font-weight:bold; text-align:center; background-color:darkSlateGray;">Return ' + (i+1) + '</td>';
					}
					
					return '<table id="' + gridID + '_' + d.PartNo + '" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							'<td colspan="' + (d.OnlineReturnArray.length + 1) + '" style="font-weight:bold; text-align:center; font-size: 0.8em; background-color:darkSlateBlue; color:white; border:1px solid gray;">Narvar Return Details</td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-left:1px solid gray; border-bottom:1px solid lightgray; padding:2px; background-color:darkSlateGray;"></td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; color:white; font-style:italic; font-weight:bold; text-align:center; background-color:darkSlateGray;">Return 1</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px; color:white; font-style:italic; font-weight:bold; text-align:center; background-color:darkSlateGray; border-right:1px solid gray;">Return 2</td>'+
							rowHeader+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-bottom:1px solid white; border-left:1px solid gray; font-size:0.8em; padding:2px; font-weight:bold; background-color:lightGray;">Return Date:</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px;">'+returnDate+'</td>'+
							rowReturnDate+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-bottom:1px solid white; border-left:1px solid gray; font-size:0.8em; padding:2px; font-weight:bold; background-color:lightGray;">Return Delivery Date:</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px;">'+returnDeliveryDate+'</td>'+
							rowReturnDeliveryDate+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-bottom:1px solid white; border-left:1px solid gray; font-size:0.8em; padding:2px; font-weight:bold; background-color:lightGray;">Return Qty:</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px;">'+d.OnlineReturnQty+'</td>'+
							rowReturnQuantity+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-bottom:1px solid white; border-left:1px solid gray; font-size:0.8em; padding:2px; font-weight:bold; background-color:lightGray;">Return Reason:</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px;">'+d.OnlineReturnReason+'</td>'+
							rowReturnReason+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-left:1px solid gray;"></td>'+
							'<td style="border-bottom:1px solid white; border-left:1px solid gray; font-size:0.8em; padding:2px; font-weight:bold; background-color:lightGray;">Return Tracking Number:</td>'+
							//'<td style="border-bottom:1px solid lightgray; font-size:0.8em; padding:2px;">'+trackingLink+'</td>'+
							rowTrackingNumber+
							//'<td style="width:3px; border-right:1px solid gray;"></td>'+
						'</tr>'+
						'<tr>'+
							'<td style="width:10%;"></td>'+
							//'<td style="width:3px; border-bottom:1px solid gray; border-left:1px solid gray;"></td>'+
							'<td style="font-size:0.8em; border-bottom:1px solid gray; border-left:1px solid gray; padding:2px; font-weight:bold; background-color:lightGray;">Return Ship Date:</td>'+
							//'<td style="font-size:0.8em; border-bottom:1px solid gray; padding:2px;">'+returnShipDate+'</td>'+
							rowReturnShipDate+
							//'<td style="width:3px; border-bottom:1px solid gray; border-right:1px solid gray;"></td>'+
						'</tr>'+
					'</table>';
				}

				/**
				 * Get the child row html
				 * @param {data row} d
				 * @returns {child table html} 
				 */
				function getChildRow(d) {

					var childTable = $('#OrderHeaderDetail').clone();
					var childRowData = $('#childRowData').clone();

					if (d.childTableHTML)
						return d.childTableHTML;

					$.each(d.LineItems, function (line) {
						var rowHTML = childRowData.prop('outerHTML');
						childTable.removeClass('hidden');

						$.each(d.LineItems[line], function (key, value) {
							rowHTML = rowHTML.replace('{{lines.' + key + '}}', value || '');
						});

						rowHTML = $($.parseHTML(rowHTML));
						rowHTML.removeAttr('id');
					
						childTable.find('tbody').append(rowHTML.prop('outerHTML'));
					});

					childTable.find('#childRowData').remove();

					d.childTableHTML = childTable.prop('outerHTML');

					return d.childTableHTML;
				}

				//"on draw" event for the orders table
				orders_table.on('draw', function () {

					$.each(detailRows, function (i, id) {
						$('#' + id + ' td.details-control').trigger('click');
					});

					if (isIncident) {
						//Select the row of the associated incident order number
						var incOrderNumber = 0;
						WorkspaceRecord.getFieldValues(['Incident.C$order_number']).then(function(incFieldDetails) {
							incOrderNumber = incFieldDetails.getField('Incident.C$order_number').getValue();
							
							orders_table.rows().every(function () {
								if (incOrderNumber == this.data().OrderNumber)
									this.select();
							});

							//initialize the popup order to incident association dialogs
							$('[data-toggle=confirmation]').confirmation({
								rootSelector: '[data-toggle=confirmation]',
								singleton: true,
								copyAttributes: 'data-order-number',
								onConfirm: function (e) {
									WorkspaceRecord.updateField('Incident.C$order_number', parseInt(this.attr('data-order-number'))); 
								},
								popout: true
							});
						});
					}
				});

				/**
				 * Create a grid for a specific order detai line items
				 */
				function CreateOrderDetailsGrid(gridID, json, shippingAddress) {
					order_details_tables['order_details_table_' + gridID] = $('#OrderDetailsHeader_' + gridID).DataTable({
						data: json,
						"destroy": true,
						"select": true,
						"searching": false,
						"ordering": false,
						"info": false,
						"paging": false,
						/*"responsive": false,
						"scrollX": true,
						"scrollY": true,*/
						"columns": [
							{
								/*"class": "details-control2",*/
								"orderable": false,
								"data": "OnlineReturnTrackingNo",
								"defaultContent": "",
								"width": "25px"
							},
							{
								"title": "Status",
								"data": "Status",
								"width": "65px"
							},
							{
								"title": "Tracking Number",
								"data": "TrackingNo",
								"width": "125px",
								"render": function (data, type, row) {
									var trackingLink = '<a href= "https://www.fedex.com/fedextrack/?tracknumbers=' + data + '" target="_blank">' + data + "</a>";
									
									if (data)
										if(data.substring(0,2) == "1Z")
											trackingLink = '<a href= "http://wwwapps.ups.com/WebTracking/ track?track=yes&trackNums=' + data + '" target="_blank">' + data + "</a>";
									
									if (shippingAddress != null)
										if (data && (shippingAddress.Country.toLowerCase() == 'us' || shippingAddress.Country.toLowerCase() == 'ca'))
											return trackingLink;
									return data;
								}
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
							{
								"title": "Qty",
								"data": "Qty",
								"width": "25px"
							},
							{
								"title": "Price",
								"data": "Price",
								"render": function (data, type, row) {
									var amount = accounting.toFixed(parseFloat(row.Price), 2);
									var return_data = "$" + amount;
									return return_data;
								},
								"width": "50px"
							},
							{
								"title": "Warehouse",
								"data": "Warehouse",
								"width": "75px",
								"render": function (data, type, row) {
									/*var trackingLink = '<a href= "https://www.fedex.com/apps/fedextrack/?tracknumbers=' + data + '&language=en&cntry_code=us" target="_blank">' + data + "</a>";
									if (shippingAddress != null)
										if (data && shippingAddress.Country.toLowerCase() == 'us')
											return trackingLink;
									return data;*/
									//return warehouseListManual[0].abbreviationList[1];
									if (data)
										return GetWarehouseName(data);
									return "";
								}
							},
							{
								"title": "Total",
								"data": "Total",
								"render": function (data, type, row) {
									var rawTotal = accounting.toFixed(parseFloat(row.TotalAdjusted), 2);
									var return_data = "$" + rawTotal;
									return return_data;
								},
								"width": "50px"
							},
							{
								"title": function (data, type, row) {
									return '<a href="https://support.bissell.com/app/answers/detail/a_id/4283/kw/Narvar%20Ship" target="_blank">Initial Est. Delivery</a>';
								},
								"data": "ExpDeliveryDateTo",
								"render": function (data, type, row) {
									if(data && row.ExpDeliveryDateFrom)
										return row.ExpDeliveryDateFrom.substring(4,6) + "/" + row.ExpDeliveryDateFrom.substring(6,8) + "/" + row.ExpDeliveryDateFrom.substring(0,4) + " - " + data.substring(4,6) + "/" + data.substring(6,8) + "/" + data.substring(0,4);
									return "";
								},
								"width": "125px"
							},
							{
								"title": "Auto Replenishment",
								"data": "IsSubscription",
								"render": function (data, type, row) {
									if(data == "Y")
										return '<a href="https://support.bissell.com/app/answers/detail/a_id/4393" target="_blank">Yes</a>';
									return "No";
								},
								"width": "75px"
							}/*,
							{
								"title": "Sales Tax",
								"data": "SalesTax"
							}*/
						],
						"columnDefs":[{
							"targets":[0],
							"createdCell": function(td, cellData, rowData, row, col) {
								if(cellData){
									$(td).addClass('details-control2');
									//$(td).style.backgroundPositionY = '.65em';
									//$(td).setAttribute("style", "background-position-y:.65em;");
								}
								$(td).html('');
							}
						}]
					});
					
					// Add event listener for opening and closing details
					$('.orderDetailsTable tbody').on('click', 'td.details-control2', function (e) {
						e.stopImmediatePropagation();
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

				/**
				 * Create a Payments Grid for a specific order
				 */
				function CreatePaymentsGrid() {
					payment_tables['payments_table_' + currOrderNumber] = $('#PaymentsHeader_' + currOrderNumber).DataTable({
						ajax: {
							"url": AJAX_ENDPOINT_PAYMENTSERVICE + '?action=getpaymenthistory',
							"data": function (data) {
								return $.extend({}, data, {
									"sid": getParameterByName('sid'),
									"OrderNumber": currOrderNumber,
									"OracleAccountNo": currOrderOracleAccountNo,
									"ContactID": contactID
								})
							},
							"type": "POST",
							"dataSrc": function (json) {
								//make sure there are records
								if (json.ResponseMessage == "0 payments found for this order") {
									$('#payments_grid_' + currOrderNumber + '_error').html('No payments were found for this order.');
									var remainingBalanceColor = (0 > 0 ? 'red' : 'green');
									$('#order_total_' + currOrderNumber).html('Remaining Balance: <span style="color:' + remainingBalanceColor + '">$0</span>');
									$(document.body).css({ 'cursor': 'default' });
								}
								else {
									if (json.ResponseCode == "Success") {
										//reset current order balance
										currOrderBalance = 0.00;
										//set customerTRX
										customerTRX = json.CustomerTrxID;
										//reset available payments
										paymentSelectionOptions = [];

										//parse payment history line-items
										var return_data = new Array();
										for (var x = 0; x < json.Payments.length; x++) {
											//set datatable data
											return_data.push({
												'PaymentNumber': json.Payments[x].PaymentNumber,
												'DisplayPaymentDate': json.Payments[x].DisplayPaymentDate,
												'AmountDueOriginal': json.Payments[x].AmountDueOriginal,
												'AmountDueRemaining': json.Payments[x].AmountDueRemaining,
												'DisplayCardInformation': json.Payments[x].DisplayCardInformation,
												'SettlementResponse': json.Payments[x].SettlementResponse,
												'PaymentScheduleID': json.Payments[x].PaymentScheduleID,
												'OrderTotal': currOrderTotal
											});

											//add paymentScheduleID to array
											paymentScheduleIDs.push(json.Payments[x].PaymentScheduleID);

											//set payment selection array
											if (json.Payments[x].AmountDueRemaining != 0 && json.Payments[x].AmountDueRemaining != null) {
												//set payment selections
												var paymentID = json.Payments[x].PaymentScheduleID;
												var paymentAmount = json.Payments[x].AmountDueRemaining;
												paymentSelectionOptions.push({ "pay": "", "paymentID": paymentID, "amount": paymentAmount });
												//running tally of order balance
												currOrderBalance += parseFloat(json.Payments[x].AmountDueRemaining);
											}
										}

										//current order balance
										$('#payment_amount').val(accounting.toFixed(parseFloat(currOrderBalance), 2));
										var remainingBalanceColor = (currOrderBalance > 0 ? 'red' : 'green');
										$('#order_total_' + currOrderNumber).html('Remaining Balance: <span style="color:' + remainingBalanceColor + '">$' + currOrderBalance.toFixed(2) + '</span>');

										//create editor
										CreatePaymentsEditor();

										//load payment selection datatable
										var clearedRows = paymentSelectionTable.rows().remove().draw();
										paymentSelectionTable.rows.add(paymentSelectionOptions).draw();

										//Unbind any previous event handlers before applying the click handler
										$('#SelectPayments').off("click");

										//set data when a row is selected
										$('#SelectPayments').on('click', 'td', function (e) {
											//only first column
											if ($(this).index() != 0)
												return;

											//set current row index
											currPaymentRowIndex = $(this).parent().index();
											//get current row data
											var currRow = paymentSelectionTable.row(currPaymentRowIndex).node();

											//DESELECTING row
											if ($(this).parent().hasClass('selected')) {
												//remove payment amount from paymentTotal
												var amountToRemove = parseFloat(currRow.cells[2].innerText);
												paymentTotal -= amountToRemove;
											}
											//selecting row
											else {
												//add payment amount to paymentTotal
												var amountToAdd = parseFloat(currRow.cells[2].innerText);
												paymentTotal += amountToAdd;
											}

											//update payment amount
											$('#payment_amount').val(accounting.toFixed(paymentTotal, 2));
										});

										//get pre-populated message
										var loadingMessage = $('#payments_grid_' + currOrderNumber + '_error').text().indexOf("Loading");
										//reset error message
										if (loadingMessage >= 0) {
											$('#payments_grid_' + currOrderNumber + '_error').hide();
											$('#payments_grid_' + currOrderNumber + '_error').html('<div class="empty"></div>');
										}
										else {
											setTimeout(function ClearErrorMessage() {
												$('#payments_grid_' + currOrderNumber + '_error').hide();
												$('#payments_grid_' + currOrderNumber + '_error').html('<div class="empty"></div>');
											}, 30000);
										}

										//show buttons (incident workspace only)
										if (isIncident && currOrderBalance > 0.00) {
											$('#details_payment_' + currOrderNumber).show();
											$('#payment_buttons_' + currOrderNumber).show();
											//$('#schedule_changes_' + currOrderNumber).show();
										}

										//show table
										$('#payment_table_' + currOrderNumber).show();

										//done
										return return_data;
									}
									else {
										if (json.ResponseCode == "Failure") {
											$('#payments_grid_' + currOrderNumber + '_error').html(json.ResponseMessage);
											$('#payments_grid_' + currOrderNumber + '_error').css('color', 'red');
											$('#payments_grid_' + currOrderNumber + '_error').css('font-weight', 'bold');
											return;
										}
										else if (json.ResponseMessage == null && json.ResponseCode == null) {
											$('#payments_grid_' + currOrderNumber + '_error').html('No data was found for this order.');
											return;
										}
										else {
											$('#payments_grid_' + currOrderNumber + '_error').html('No payments were found for this order.');
											return;
										}
									}
								}
							}
						},
						"destroy": true,
						"select": true,
						"searching": false,
						"ordering": false,
						"info": false,
						"paging": false,
						"responsive": false,
						"scrollX": false,
						"scrollY": false,
						"columns": [
							{
								"title": "Payment Number",
								"data": "PaymentNumber",
							},
							{
								"title": "Current Due Date",
								"data": "DisplayPaymentDate",
								"render": function (data, type, row) {
									//underline if the date is editable
									var className = "change_due_date";
									var style = "";

									//watch out for alpha characters!
									var rawCellData = row.DisplayPaymentDate.split(' (');
									//parse payment date
									var targetDate = new Date(rawCellData[0]);
									//get today's date
									var today = new Date();

									//compare the dates
									//if (targetDate >= today) {
									//check payment total
									var amount = accounting.toFixed(parseFloat(row.AmountDueRemaining), 2);
									//payment total has a value
									if (amount > 0) {
										//check settlement response
										/*var settlementResponse = row.SettlementResponse;
										var okToPay = settlementResponse.indexOf("Pending");
										//this payment can be made
										if (okToPay == -1) {*/
										style = "style=\"color:blue;font-weight:bold;\"";
										className += " active_payment";
										/*}*/
									}
									//}

									//extra message
									var extraMessage = "";
									if (rawCellData.length > 1) {
										for (var x = 1; x < rawCellData.length; x++) {
											if (x == 1)
												extraMessage += "(" + rawCellData[x];
											else
												extraMessage += rawCellData[x];
										}
									}

									//display the info
									return '<div id="date" class="' + className + '" ' + style + '>' + rawCellData[0].replace(/\-/g, '/') + '</div><div>' + extraMessage + '</div>';
								}
							},
							{
								"title": "Order Total",
								"data": "OrderTotal",
								"render": function (data, type, row) {
									var amount = accounting.toFixed(parseFloat(currOrderTotal), 2);
									var return_data = "$" + amount;
									return return_data;
								}
							},
							{
								"title": "Amount",
								"data": "AmountDueOriginal",
								"render": function (data, type, row) {
									var amount = accounting.toFixed(parseFloat(row.AmountDueOriginal), 2);
									var return_data = "$" + amount;
									return return_data;
								}
							},
							{
								"title": "Balance Due",
								"data": "AmountDueRemaining",
								"render": function (data, type, row) {
									var amount = accounting.toFixed(parseFloat(row.AmountDueRemaining), 2);
									var return_data = "$" + amount;
									return return_data;
								}
							},
							{
								"title": "Credit Card Info",
								"data": "DisplayCardInformation",
							},
							{
								"title": "Settlement Response",
								"data": "SettlementResponse",
							}
						]
					});
				}

				/**
				 * Create the bill to ship to address display
				 */
				function CreateAddressInfo(orderid, json) {
					//error handling
					if (json.ResponseMessage != "Success") {
						$('#address_info_' + currOrderNumber + '_error').html(json.ResponseMessage + '.');
						$('#address_info_' + currOrderNumber + '_error').css('color', 'red');
						$('#address_info_' + currOrderNumber + '_error').css('font-weight', 'bold');
						$('#address_info_' + currOrderNumber + '_error').show();
					}
					var billTo = '<strong>Bill To</strong><br/>' +
						json.BillingAddress.Name + '<br/>' +
						json.BillingAddress.Street1 + '<br/>';

					if(json.BillingAddress.Street2)
						billTo += json.BillingAddress.Street2 + '<br/>';

					billTo +=  json.BillingAddress.City + ', ' + json.BillingAddress.State + ' ' + json.BillingAddress.Postal + ', ' + json.BillingAddress.Country + '<br/>';

					if (json.BillingAddress.Email)
						billTo += 'Email: ' + json.BillingAddress.Email + '<br/>';

					if (json.BillingAddress.Phone)
						billTo += 'Phone: ' + json.BillingAddress.Phone + '<br/>';

					var shipTo = '<strong>Ship To</strong><br/>' +
						json.ShippingAddress.Name + '<br/>' +
						json.ShippingAddress.Street1 + '<br/>';

					if (json.ShippingAddress.Street2)
						shipTo += json.ShippingAddress.Street2 + '<br/>';

					shipTo += json.ShippingAddress.City + ', ' + json.ShippingAddress.State + ' ' + json.ShippingAddress.Postal + ', ' + json.ShippingAddress.Country + '<br/>';

					if (json.ShippingAddress.Email)
						shipTo += 'Email: ' + json.ShippingAddress.Email + '<br/>';

					if (json.ShippingAddress.Phone)
						shipTo += 'Phone: ' + json.ShippingAddress.Phone + '<br/>';                    

					$("#address_info_billto_" + orderid).html(billTo);
					$("#address_info_shipto_" + orderid).html(shipTo);
					
					//Canada - use provinces
					if (json.BillingAddress.Country == "CA")
						ccStateList = provinceListManual;
					//US - use states
					else
						ccStateList = stateListManual;
					//Change CC-: clear state/province drop-down list
					$('#cd_creditcardstate').empty();
					$('#cd_creditcardstate').append("<option value=''>[+]</option>");
					//Make CC Payment: clear state/province drop-down list
					$('#creditcardstate').empty();
					$('#creditcardstate').append("<option value=''>[+]</option>");
					//add state/province names and abbreviations
					$.each(ccStateList, function (index, stateItem) {
						$('#cd_creditcardstate').append($("<option></option>")
							.attr("value", stateItem.abbreviation)
							.text(stateItem.name));
						$('#creditcardstate').append($("<option></option>")
							.attr("value", stateItem.abbreviation)
							.text(stateItem.name));
					});

					//add to "change cc" modal form
					$('#cd_nameoncreditcard').val(json.BillingAddress.Name);
					$('#cd_creditcardaddress1').val(json.BillingAddress.Street1);
					$('#cd_creditcardaddress2').val(json.BillingAddress.Street2);
					$('#cd_creditcardcity').val(json.BillingAddress.City);
					$('#cd_creditcardstate').val(json.BillingAddress.State).change();
					$('#cd_creditcardpostalcode').val(json.BillingAddress.Postal);

					//set global address variables
					addrBilling[0] = json.BillingAddress.Street1;
					addrBilling[1] = json.BillingAddress.Street2;
					addrBilling[2] = json.BillingAddress.City;
					addrBilling[3] = json.BillingAddress.State;
					addrBilling[4] = json.BillingAddress.Postal;
					addrBilling[5] = json.BillingAddress.Country;
					addrShipping[0] = json.ShippingAddress.Street1;
					addrShipping[1] = json.ShippingAddress.Street2;
					addrShipping[2] = json.ShippingAddress.City;
					addrShipping[3] = json.ShippingAddress.State;
					addrShipping[4] = json.ShippingAddress.Postal;
					addrShipping[5] = json.ShippingAddress.Country;
				}

				/**
				 * Create an editor for a specific order's payments grid
				 */
				function CreatePaymentsEditor()
				{
					//get yesterday's date
					$today = new Date();
					$yesterday = new Date($today);
					$yesterday.setDate($today.getDate() - 1);
					$beginningOfToday = $today.getDate();
					
					//set data when a row is selected
					$('#PaymentsHeader_' + currOrderNumber).on('click', 'tr', function (e) {
						//DESELECTING row
						if ($(this).hasClass('selected')) {
							//reset currPayment variables
							currPaymentSettlementResponse = "";
						}
						//selecting row
						else {
							//get previously selected row data
							var currSelectedRow = payment_tables['payments_table_' + currOrderNumber].row('.selected').node();
							if (currSelectedRow != null)
								$(currSelectedRow).removeClass('selected');
							//set current row index
							currPaymentRowIndex = $(this).index();
							//get current row data
							var currRow = payment_tables['payments_table_' + currOrderNumber].row(this).node();
							if (currRow != null) {
								//set currPayment variables
								currPaymentBalance = currRow.cells[4].innerText;
								currPaymentSettlementResponse = currRow.cells[6].innerText;
								//grab date
								var tempDate = currRow.cells[1].innerText;
								var dateParts = tempDate.split(' ');
								currPaymentDate = dateParts[0];
							}
						}
					});

					// Activate the inline editor on click of a table cell
					$('#PaymentsHeader_' + currOrderNumber).on('click', 'td', function (e) {
						//reset alert
						$('#payments_grid_' + currOrderNumber + '_error').html('');
						$('#payments_grid_' + currOrderNumber + '_error').hide();

						//only 2nd cell in each row
						var col = $(this).parent().children().index($(this));
						if (col != 1)
							return;

						//only allow changes to be made from an incident workspace
						if (!isIncident) {
							//set alert
							var errorMSG = 'Edits can only be made from an incident workspace.';
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
							//exit
							return;
						}
						//first, reset the error message
						$('#payments_grid_' + currOrderNumber + '_error').hide();
						$('#payments_grid_' + currOrderNumber + '_error').html('<div class="empty"></div>');

						//get current row data
						var currRow = payment_tables['payments_table_' + currOrderNumber].row($(this).parent()).node();
						if (currRow != null) {
							//set currPayment variables
							currPaymentBalance = currRow.cells[4].innerText;
							currPaymentSettlementResponse = currRow.cells[6].innerText;
						}
						//no need to change date if the order has a zero balance
						if (currPaymentBalance.indexOf("$0.00") >= 0) {
							//set alert
							var errorMSG = 'This payment has a balance of $0.00.  Unable to update scheduled payment date at this time.';
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
							//exit
							return;
						}
						
						//show the datepicker
						$(this).datepicker({
								startDate: "+1d",
								autoclose: true
							}).on('changeDate', function (date) {
							//allowed to change date, switch the buttons
							$('#change_card_details_' + currOrderNumber).addClass('disabled');
							$('#make_payment_' + currOrderNumber).addClass('disabled');
							$('#save_schedule_' + currOrderNumber).removeClass('disabled');
							$('#schedule_changes_' + currOrderNumber).show();

							//update the table date - LOCALLY ONLY
							/*
							var modifier = payment_tables['payments_table_' + currOrderNumber].modifier();
							var currRow = payment_tables['payments_table_' + currOrderNumber].row(modifier).node();
							$('td', currRow).eq(1).css({ "color": "orange", "font-weight": "bold" });
							$('td', currRow).eq(1).addClass('payment_date_updated');
							*/

							$(this).find('#date').css({ "color": "orange", "font-weight": "bold" });
							$(this).find('#date').addClass('payment_date_updated');

							//format the date
							var newDate = new Date(date.date.toISOString);
							var shortDate = (date.date.getMonth() + 1) + "/" + date.date.getDate() + "/" + date.date.getFullYear();
							//!!!!!
							//UPDATE CELL WITH SELECTED DATE
							//!!!!!
							$(this).find('#date').text(shortDate);

							//update the table
							//payment_tables_editors['payments_tables_editor_' + currOrderNumber].submit();

							//set alert
							var successMSG = 'Scheduled payment date(s) successfully queued.<br/>' +
								'Please click the "Save Payment Schedule" button to update the date(s) in the system.';
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:green;font-weight:bold;">' + successMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}).show();
					});
				}

				/**
				 * Fill in the line item additional details (coupon codes, total/tax info)
				 */
				function FillInOrderLineItemDetails(orderID, json)
				{
					//fill in coupon table
					order_line_item_coupon_tables['order_line_item_coupon_table_' + orderID] = $('#CouponCodes_table_' + orderID).DataTable({
						data: json.LineItems,
						"destroy": true,
						"select": true,
						"searching": false,
						"ordering": false,
						"info": false,
						"paging": false,
						"columns": [
							{
								"data": "CouponSKU",
								"width": "75px"
							},
							{
								"data": "CouponCode",
								"width": "75px"
							}
						]
					});

					//calculate amounts
					var subtotal = 0;
					var shipping = parseFloat(json.ShipAmount);
					var shipping_discount = "";
					var salestax = parseFloat(json.SalesTax.Amount);
					//var secondarytax = parseFloat(json.SalesTax.SecondaryAmount);
					var discounts = 0.0;
					for (var x = 0; x < json.LineItems.length; x++)
					{
						var price = parseFloat(json.LineItems[x].Price);
						var qty = parseInt(json.LineItems[x].Qty);
						//subtotal += parseFloat(price * qty);
						//subtotal += parseFloat(json.LineItems[x].TotalAfterDiscount);
						subtotal += parseFloat(json.LineItems[x].TotalAdjusted);
						//discounts += parseFloat(json.LineItems[x].LineDiscount);
					}
					if (json.ShipCode != null)
						shipping_discount = '&nbsp;&nbsp;|&nbsp;&nbsp;Shipping Code:&nbsp;<b>' + json.ShipCode + '</b>';

					//set current order total
					//currOrderTotal = subtotal + shipping;
					//AJAX endpoint
					// var ajaxURL = AJAX_ENDPOINT_TAXSERVICE;
					// $.ajax({
						// type: 'POST',
						// url: ajaxURL,
						// data: {
							// sid: getParameterByName('sid'),
							// ShipAmount: shipping,
							// OrderTotal: subtotal + shipping,
							// SubTotal: subtotal,
							// BillAddress1: addrBilling[0],
							// BillAddress2: addrBilling[1],
							// BillCity: addrBilling[2],
							// BillStateCode: addrBilling[3],
							// BillPostalCode: addrBilling[4],
							// BillCountryCode: addrBilling[5],
							// ShipAddress1: addrShipping[0],
							// ShipAddress2: addrShipping[1],
							// ShipCity: addrShipping[2],
							// ShipStateCode: addrShipping[3],
							// ShipPostalCode: addrShipping[4],
							// ShipCountryCode: addrShipping[5],
							// ContactID: currContact.id
						// },
						// dataType: "json",
					// }).done(function (data) {
						// //error handling
						// /*if (data.ResponseCode != "Success") {
							// $('#payments_grid_' + orderID + '_error').html(data.ResponseMessage + '.');
							// $('#payments_grid_' + orderID + '_error').css('color', 'red');
							// $('#payments_grid_' + orderID + '_error').css('font-weight', 'bold');
							// $('#payments_grid_' + orderID + '_error').show();
							// //exit function
							// return;
						// }*/
						// //set updated amounts
						// //salestax = parseFloat(data.Amount);
						// secondarytax = parseFloat(data.SecondaryAmount);
						// //discounts = (subtotal + shipping + salestax + secondarytax) - currOrderTotal;
						// discounts = (subtotal + shipping + salestax) - currOrderTotal;
						// //currOrderTotal += parseFloat(data.Amount) + secondarytax;

						// //format amounts
						// var subtotal_formatted = accounting.toFixed(subtotal, 2);
						// var shipping_formatted = accounting.toFixed(shipping, 2);
						// var salestax_formatted = accounting.toFixed(salestax, 2);
						// //var secondarytax_formatted = accounting.toFixed(secondarytax, 2);
						// var discounts_formatted = accounting.toFixed(discounts, 2);
						// var order_total_formatted = accounting.toFixed(currOrderTotal, 2);

						// //show subtotal
						// $('#subtotal_' + orderID).html('$' + subtotal_formatted);
						// //show shipping
						// $('#shipping_' + orderID).html('$' + shipping_formatted + shipping_discount);
						// //show tax
						// $('#salestax_' + orderID).html('$' + salestax_formatted);
						// //show secondary tax
						// //$('#secondarytax_' + orderID).html('$' + secondarytax_formatted);
						// //show discounts
						// $('#discounts_' + orderID).html('($' + discounts_formatted + ')');
						// //show total
						// $('#total_' + orderID).html('<b>$' + order_total_formatted + '</b>');
					// }).fail(function (jqxhr, textStatus, error) {
						// var errorMSG = textStatus + ": " + error;
						// $('#payments_grid_' + orderID + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						// $('#payments_grid_' + orderID + '_error').show();
					// });
					
					//New Code - BDH
					//set updated amounts
						
						discounts = (subtotal + shipping + salestax) - currOrderTotal;

						//format amounts
						var subtotal_formatted = accounting.toFixed(subtotal, 2);
						var shipping_formatted = accounting.toFixed(shipping, 2);
						var salestax_formatted = accounting.toFixed(salestax, 2);
						var discounts_formatted = accounting.toFixed(discounts, 2);
						var order_total_formatted = accounting.toFixed(currOrderTotal, 2);

						//show subtotal
						$('#subtotal_' + orderID).html('$' + subtotal_formatted);
						//show shipping
						$('#shipping_' + orderID).html('$' + shipping_formatted + shipping_discount);
						//show tax
						$('#salestax_' + orderID).html('$' + salestax_formatted);
						//show discounts
						$('#discounts_' + orderID).html('($' + discounts_formatted + ')');
						//show total
						$('#total_' + orderID).html('<b>$' + order_total_formatted + '</b>');
					//End New Code - BDH
					
					
				}

				//save the accumulated SCHEDULE CHANGES for a specific order's payments
				function SavePaymentScheduleChanges(orderID)
				{
					if ($('#save_schedule_' + currOrderNumber).hasClass('disabled'))
						return;

					//reset error message
					setTimeout(function ClearErrorMessage() {
						$('#payments_grid_' + orderID + '_error').hide();
						$('#payments_grid_' + orderID + '_error').html('<div class="empty"></div>');
					}, 30000);

					//AJAX call
					var paymentScheduleUpdates = {};
					//iterator
					var x = 0;
					payment_tables['payments_table_' + orderID].rows().every(function (rowIdx, tableLoop, rowLoop)
					{
						//get the row
						var rowNode = this.node();
						//first, get updated dates
						$(rowNode).find(".payment_date_updated").each(function ()
						{
							//get cell contents
							var cellData = $(this).text();
							//only continue if it's been changed
							if (cellData != "Change Due Date")
							{
								//data to save
								var paymentID = '';
								//parse date (can have text for some reason)
								var tempDate = cellData.split(" ");
								var paymentDate = tempDate[0];

								//get payment id
								paymentID = paymentScheduleIDs[rowIdx];

								//add data to array
								paymentScheduleUpdates[x] = { 'Payment_Schedule_ID': paymentID, 'Payment_Due_Date': paymentDate };
							}
						});
						//increase iterator
						x++;
					});

					//AJAX endpoint
					var ajaxURL = AJAX_ENDPOINT_PAYMENTSERVICE + '?action=updatepaymentschedule';
					$.ajax({
						type: 'POST',
						url: ajaxURL,
						data: {
							sid: getParameterByName('sid'),
							CustomerTRX: customerTRX,
							PaymentSchedule: paymentScheduleUpdates,
							ContactID: contactID
						},
						dataType: "json",
					}).done(function (data) {
						//error handling
						if (data.ResponseCode != "Success") {
							$('#payments_grid_' + orderID + '_error').html(data.ResponseMessage + '.');
							$('#payments_grid_' + orderID + '_error').css('color', 'red');
							$('#payments_grid_' + orderID + '_error').css('font-weight', 'bold');
							$('#payments_grid_' + orderID + '_error').show();
							//exit function
							return;
						}
						//refresh table data
						payment_tables['payments_table_' + orderID].ajax.url(AJAX_ENDPOINT_PAYMENTSERVICE + '?action=getpaymenthistory').load(function () {
							//reset all updated dates
							$('.payment_date_updated').removeClass('payment_date_updated');
							//show success message
							var successMSG = "The schedule has been successfully changed.";
							$('#payments_grid_' + orderID + '_error').html('<div class="error" style="color:green;font-weight:bold;margin-bottom:10px;">' + successMSG + '</div>');
							$('#payments_grid_' + orderID + '_error').show();
							//enable the appropriate buttons
							$('#change_card_details_' + orderID).removeClass('disabled');
							$('#make_payment_' + orderID).removeClass('disabled');
							$('#cancel_schedule_' + orderID).addClass('disabled');
							$('#save_schedule_' + orderID).addClass('disabled');
							$('#schedule_changes_' + orderID).hide();
						}, false);
					}).fail(function (jqxhr, textStatus, error) {
						var errorMSG = textStatus + ": " + error;
						$('#payments_grid_' + orderID + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + orderID + '_error').show();
					});
				}

				//reset the payment grid
				function ResetPaymentGrid(gridID)
				{
					payment_tables['payments_table_' + gridID].ajax.reload(function () {
						//enable the appropriate buttons
						$('#change_card_details_' + currOrderNumber).removeClass('disabled');
						$('#make_payment_' + currOrderNumber).removeClass('disabled');
						$('#save_schedule_' + currOrderNumber).addClass('disabled');
						//show the message
						if (preLoadedMessage != "")
						{
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:green;font-weight:bold;">' + preLoadedMessage + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}
					});
				}

				//show the modal window to change the credit card details for an upcoming payment
				function ChangeCardDetails_ShowDialog(orderID)
				{
					//enable button
					$('#save_cc_details').removeClass('disabled');
					//show modal window
					$('#pay_hist_change_cc_details').modal('show');
				}

				//save updated CC details
				function ChangeCardDetails_SaveData(orderID)
				{
					//disable button
					$('#save_cc_details').addClass('disabled');

					//reset messages
					$('.validation_error_div').each(function () {
						$(this).empty();
					})

					//create form validator
					window.creditCardValidator = $("#cd_creditcardform").validate({
						errorClass: 'validation-error',
						errorPlacement: function ($error, $element) {
							//grab element name
							var id = $element.attr("id");
							//set error message
							$("#cd_error_" + id).html($error[0].innerHTML);
						},
						rules: {
							cd_creditcardnumber: {
								required: true,
								creditcard: true
							},
							cd_creditcardtype: {
								cardtypematchesnumber: true,
								required: true
							}/*,
							expirationdate: {
								cardexpirationdate: true
							},
							creditcardcvv: {
								minlength: "3"
							}*/
						},
						messages: {
							cd_nameoncreditcard: {
								required: "Name on card is required."
							},
							cd_creditcardtype: {
								required: "Select a credit card type.",
								cardtypematchesnumber: ""
							},
							cd_creditcardnumber: {
								required: "Credit card # is required.",
								creditcard: "Enter a valid credit card #."
							},
							cd_creditcardexpirationmonth: {
								required: "Expiration month is required."
							},
							cd_creditcardexpirationyear: {
								required: "Expiration year is required."
							},
							/*cd_creditcardcvv: {
								required: "Card CVV required"
							},*/
							cd_creditcardaddress1: {
								required: "Address 1 is required."
							},
							cd_creditcardcity: {
								required: "City is required."
							},
							cd_creditcardstate: {
								required: "State is required."
							},
							cd_creditcardpostalcode: {
								required: "Postal code is required."
							}
						}
					});

					window.creditCardValidator.form();

					if (window.creditCardValidator.valid() === false)
						return;

					//Credit card info is valid. Set the
					ccDetails.AccountNumber = $('#cd_creditcardnumber').val();
					ccDetails.Name = $('#cd_nameoncreditcard').val();
					ccDetails.ExpMonth = $('#cd_creditcardexpirationmonth').val();
					ccDetails.ExpYear = $('#cd_creditcardexpirationyear').val();
					//ccDetails.CVC = $('#cd_creditcardcvv').val();
					ccDetails.CardCode = getSelectedValue('cd_creditcardtype');

					setStorageObject('ccDetails', ccDetails);

					//Change the button color and hide the window
					$('#setcreditcarddetails').removeClass('red');
					$('#setcreditcarddetails').addClass('green');

					//AJAX endpoint
					var ajaxURL = AJAX_ENDPOINT_PAYMENTSERVICE + '?action=updatepaymentdetails';
					//AJAX call
					$.ajax({
						type: 'POST',
						url: ajaxURL,
						data: {
							sid: getParameterByName('sid'),
							CustomerTrxID: customerTRX,
							Name: ccDetails.Name,
							Address1: $('#cd_creditcardaddress1').val(),
							Address2: $('#cd_creditcardaddress2').val(),
							City: $('#cd_creditcardcity').val(),
							PostalCode: $('#cd_creditcardpostalcode').val(),
							StateCode: $('#cd_creditcardstate').val(),
							CCExpMonth: ccDetails.ExpMonth,
							CCExpYear: ccDetails.ExpYear,
							CCNum: ccDetails.AccountNumber,
							CCType: ccDetails.CardCode,
							ContactID: contactID
						},
						dataType: "json",
					}).done(function (data) {
						//error handling
						if (data.ResponseCode != "Success") {
							$('#payments_grid_' + orderID + '_error').html(data.ResponseMessage + '.');
							$('#payments_grid_' + orderID + '_error').css('color', 'red');
							$('#payments_grid_' + orderID + '_error').css('font-weight', 'bold');
							$('#payments_grid_' + orderID + '_error').show();
							//hide modal window
							$('#pay_hist_change_cc_details').modal('hide');
							//exit function
							return;
						}
						else {
							//refresh table data
							payment_tables['payments_table_' + orderID].ajax.url(AJAX_ENDPOINT_PAYMENTSERVICE + '?action=getpaymenthistory').load(function () {
								//set alert
								var successMSG = 'Credit Card details were successfully updated.';
								$('#payments_grid_' + orderID + '_error').html('<div class="error" style="color:green;font-weight:bold;">' + successMSG + '</div>');
								//$('#payments_grid_' + orderID + '_error').css('color', 'green');
								$('#payments_grid_' + orderID + '_error').show();
								//hide modal window
								$('#pay_hist_change_cc_details').modal('hide');
								//reset error message in 15s
								setTimeout(function ClearErrorMessage() {
									$('#payments_grid_' + orderID + '_error').hide();
									$('#payments_grid_' + orderID + '_error').html('<div class="empty"></div>');
								}, 30000);
							}, false);
						}
					}).fail(function (jqxhr, textStatus, error) {
						var errorMSG = textStatus + ": " + error;
						$('#payments_grid_' + orderID + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + orderID + '_error').show();
					});
				}

				//make a one-time payment for an order
				function MakePayment(orderID)
				{
					alert('Make a Payment functionality has been temporarily disabled. Please change any outstanding payments below to a date in the future to schedule a new payment.');
					return false;
					//Temporarily removed
					// //gently remind the agent to update cc info first
					// alert("Payment will process immediately.  If the credit card needs to be changed, make that change before processing the payment.");
					// //first, enable the save button
					// $('#queue_payment').removeClass('disabled');
					// //show modal
					// $('#make_payment_modal').modal('show');
					//End temporarily removed
				}

				//submit a payment
				function SubmitPayment()
				{
					//first, disable the save button
					$('#queue_payment').addClass('disabled');

					//validate the form
					window.paymentValidator = $("#creditcardform").validate({
						errorClass: 'validation-error',
						errorPlacement: function ($error, $element) {
							var id = $element.attr("id");

							$("#error_" + id).append($error);
						},
						rules: {
							creditcardnumber: {
								required: true,
								creditcard: true
							},
							creditcardcvv: {
								minlength: "3"
							}
						},
						messages: {
							creditcardtype: {
								required: "Select a credit card type"
							},
							creditcardnumber: {
								required: "Credit card # required",
								creditcard: "Enter a valid credit card #"
							},
							/*nameoncreditcard: {
								required: "Name on card is required"
							},
							creditcardexpirationmonth: {
								required: "Required"
							},
							creditcardexpirationyear: {
								required: "Required"
							},
							creditcardcvv: {
								required: "Card CVV required"
							},
							creditcardaddress1: {
								required: "Address 1 required"
							},
							creditcardcity: {
								required: "City required"
							},
							creditcardstate: {
								required: "State required"
							},
							creditcardpostalcode: {
								required: "Postal code required"
							}*/
						}
					});

					window.paymentValidator.form();

					if (window.paymentValidator.valid() === false)
						return;

					//set current payment amount
					paymentTotal = accounting.toFixed(parseFloat($('#payment_amount').val()), 2);

					//make sure there are payments to submit
					if (paymentTotal <= 0)
					{
						var errorMSG = 'No queued payments found to submit.';
						$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + currOrderNumber + '_error').show();
						return;
					}

					//AJAX endpoint
					var ajaxURL = AJAX_ENDPOINT_PAYMENTSERVICE + '?action=makepayment';
					//AJAX call
					$.ajax({
						type: 'POST',
						url: ajaxURL,
						data: {
							sid: getParameterByName('sid'),
							CustomerTrxID: customerTRX,
							PaymentAmount: paymentTotal,
							ccTransactionDate: new Date(Date.now()).toISOString(),
							ContactID: contactID
							/*ccNumber: $('#creditcardnumber').val(),
							ccName: $('#nameoncreditcard').val(),
							ccAddr1: $('#creditcardaddress1').val(),
							ccAddr2: $('#creditcardaddress2').val(),
							ccCity: $('#creditcardcity').val(),
							ccState: getSelectedValue('creditcardstate'),
							ccZip: $('#creditcardpostalcode').val(),
							ccExpMonth: $('#creditcardexpirationmonth').val(),
							ccExpYear: $('#creditcardexpirationyear').val(),
							ccCVV: $('#creditcardcvv').val(),
							ccType: getSelectedValue('creditcardtype'),*/
						},
						dataType: "json",
					}).done(function (data) {
						//error handling
						if (data.ResponseCode != "Success") {
							$('#payments_grid_' + currOrderNumber + '_error').html(data.ResponseMessage + '.');
							$('#payments_grid_' + currOrderNumber + '_error').css('color', 'red');
							$('#payments_grid_' + currOrderNumber + '_error').css('font-weight', 'bold');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}
						//error making payment
						else if (data.ResponseCode == "Failure") {
							var errorMSG = data.ResponseCode + ": " + data.ResponseMessage;
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}
						//null response
						else if (data.ResponseCode == null && data.ResponseMessage == null) {
							//set alert
							var errorMSG = 'No response was received from the server.';
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}
						//successful payment
						else {
							//set alert
							var successMSG = 'Payment of $' + $('#payment_amount').val() + ' was successfully processed.';
							preLoadedMessage = successMSG;
							$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:green;font-weight:bold;">' + successMSG + '</div>');
							$('#payments_grid_' + currOrderNumber + '_error').show();
						}
						//clear out payment amount
						$('#payment_amount').val('');
						//hide modal window
						$('#make_payment_modal').modal('hide');
						//reset table
						ResetPaymentGrid(currOrderNumber);
					}).fail(function (jqxhr, textStatus, error) {
						var errorMSG = textStatus + ": " + error;
						$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + currOrderNumber + '_error').show();
						$('#make_payment_modal').modal('hide');
					});
				}

				//check for outstanding payment(s) in the past
				function CheckPastDuePayments(gridID)
				{
					//clear past due payments
					pastDuePayment = false;
					//iterate through rows of payments table
					var table = $('#PaymentsHeader_' + currOrderNumber).DataTable();
					table.rows().every(function () {
						var d = this.data();
						/*if (d.AmountDueRemaining == "0")
							return;*/
						if (d.AmountDueRemaining != "0") {
							var todayRaw = new Date(Date.now()).toISOString();
							var rawDate = d.DisplayPaymentDate;
							var dateArray = rawDate.split(' ');
							var dpg = $.fn.datepicker.DPGlobal;
							//var date = dpg.parseDate(dateArray[0], dpg.parseFormat("yy-mm-dd"));
							//var date = new Date(rawDate);
							var date = new Date(dateArray[0]);
							var today = dpg.parseDate(todayRaw, dpg.parseFormat("yy-mm-dd"));
							if (date < today) {
								pastDuePayment = true;
							}
						}
					});
				}

			});
		}(jQuery));

		/**
		 * Get the selected value of a select box
		 * @param {type} controlId
		 * @returns {type} 
		 */
		function getSelectedValue(controlId) {
			var val = $('#' + controlId + ' option:selected').val();
			return val;
		}

		function setStorageObject(key, value) {
			localStorage.setItem(key, JSON.stringify(value));
		}

		function getStorageObject(key) {
			var storageVal = localStorage.getItem(key);
			if (storageVal != null) {
				return JSON.parse(storageVal);
			}
			return null
		}

		function getListValues(list) {
			var c = window.external.Contact;
			var xml = c.GetNameValues(list);
			var listValues = [];
			var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
			xmlDoc.async = "false";
			xmlDoc.loadXML(xml);

			var x = xmlDoc.getElementsByTagName("Item");
			for (var i = 0; i < x.length; i++) {
				var attlist = x.item(i).attributes;
				var attid = attlist.getNamedItem("Id").value;
				var attname = attlist.getNamedItem("Name").value
				listValues.push({ id: attid, label: attname })
			}

			return listValues;
		}

		function GetStateAbbrIndex(abbr) {
			for (var i = 0; i < stateListManual.length; i++) {
				if (stateListManual[i].abbreviation === abbr) {
					return i;
				}
			}
		}

		function GetWarehouseName(abbr) {
			for (var i = 0; i < warehouseListManual.length; i++) {
				for (var j = 0; j < warehouseListManual[i].abbreviationList.length; j++){
					if (warehouseListManual[i].abbreviationList[j] === abbr){
						return warehouseListManual[i].name;
					}
				}
			}
			
			return abbr;
		}

		function ShowPaymentCalendar(inputElem) {
			//alert("Cell clicked!");
			inputElem.datepicker('show');
			/*$(this).children('input[type="text"]').datepicker();{
				minDate: new Date($today.getFullYear(), $today.getMonth(), $today.getDate()),
				defaultDate: 0,
				onSelect: function (e) {
					alert('Date changed!');
					//reset alert
					$('#payments_grid_' + currOrderNumber + '_error').html('');
					$('#payments_grid_' + currOrderNumber + '_error').hide();

					//only 2nd cell in each row
					var col = $(this).parent().children().index($(this));
					if (col != 1)
						return;

					//only allow changes to be made from an incident workspace
					if (!isIncident) {
						//set alert
						var errorMSG = 'Edits can only be made from an incident workspace.';
						$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + currOrderNumber + '_error').show();
						//exit
						return;
					}

					//first, reset the error message
					$('#payments_grid_' + currOrderNumber + '_error').hide();
					$('#payments_grid_' + currOrderNumber + '_error').html('<div class="empty"></div>');

					//get current row data
					var currRow = payment_tables['payments_table_' + currOrderNumber].row($(this).parent()).node();
					if (currRow != null) {
						//set currPayment variables
						currPaymentBalance = currRow.cells[4].innerText;
						currPaymentSettlementResponse = currRow.cells[6].innerText;
					}

					//no need to change date if the order has a zero balance
					if (currPaymentBalance.indexOf("$0.00") >= 0) {
						//set alert
						var errorMSG = 'This payment has a balance of $0.00.  Unable to update scheduled payment date at this time.';
						$('#payments_grid_' + currOrderNumber + '_error').html('<div class="error" style="color:red;font-weight:bold;">' + errorMSG + '</div>');
						$('#payments_grid_' + currOrderNumber + '_error').show();
						//exit
						return;
					}
				}
			});*/
		}
	});
});