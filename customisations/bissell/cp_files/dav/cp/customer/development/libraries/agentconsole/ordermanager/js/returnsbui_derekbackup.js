/* Workspace Interactions */
localStorage.clear();

var validationArray = {};
var CUSTOM_SCRIPTS_PATH = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/";
var PORTAL_SCRIPT_PATH = "";

//Set paths for sites other than localhost
if (!(location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
    PORTAL_SCRIPT_PATH = "../cp/customer/development/libraries/agentconsole/ordermanager/";
    CUSTOM_SCRIPTS_PATH = "/cgi-bin/bissell.cfg/php/custom/";
}

var billAddrValid = true;
var shipAddrValid = true;
var AJAX_ENDPOINT = CUSTOM_SCRIPTS_PATH + "ordermanagerajaxhandler.php";
var ADDRESS_VALIDATION_ENDPOINT = CUSTOM_SCRIPTS_PATH + "validateaddress.php";
var ORDER_SUBMIT_ENDPOINT = CUSTOM_SCRIPTS_PATH + "submitreturnauth.php";
var INCIDENT_OBJECT_NAME = "Incident";
var CONTACT_OBJECT_NAME = "Contact";
var ORDER_HEADER_OBJECT_NAME = "BUS$OrderHeader";
var AU_COUNTRY_ID = 4;
var NZ_COUNTRY_ID = 3;
var US_COUNTRY_ID = 1;
var UK_COUNTRY_ID = 5;
var CA_COUNTRY_ID = 2;
var ORDER_TYPE_RA_ID = 2;
var beforeSaveMessage;
var consumerId = getParameterByName('consumer_id');
var currentIId = getParameterByName('iid') || i.IId;
var billToValid = false;
var shipToValid = false;
var lineNumber = 0;
var orderNumber = '';
var orderHeaderId = getParameterByName('oid'); //The only way to get the BUS.OrderHeader.ID is to pass it in the URL.
var sid = getParameterByName('sid'); //Agent session id
var orderSalesTaxTotal = 0.00;
var hasManualShippingOverride = false;
var ccDetails = getStorageObject('ccDetails') || {};
var paperCheckDetails = getStorageObject('paperCheckDetails') || {};
var countryList = [];
var provinceList = [];
var provinceAbbreviationList = [];
var conusList = [];
window.List = [];
window.actionCodeList = [];
var channelList = [];
var returnTypeList = [];
var orderTotals = {};
var addPartType = "2";

/* custom validation rule attributes */
var requirePhysicalAddress = false;

/* Deferred Events */
var defCountryListLoaded = $.Deferred();
var defProvinceListLoaded = $.Deferred();
var defProvinceAbbreviationListLoaded = $.Deferred();
var defCONUSListLoaded = $.Deferred();
var defReasonCodeListLoaded = $.Deferred();
var defActionCodeListLoaded = $.Deferred();
var defReturnLocationListLoaded = $.Deferred();
var defChannelListLoaded = $.Deferred();
var defReturnTypeListLoaded = $.Deferred();

/* ID List */
var INC_DISP_ID_WAITING_CHECK = 32140;
var INC_DISP_ID_WAITING_NEW_CARD = 32139;
var INC_DISP_ID_PROCESSED_PAYMENT = 609;
var INC_STATUS_ID_CLOSED = 2;

/* Shipping Option List */
var SHIP_FEDEX_OVERNIGHT_ID = 34;
var SHIP_FEDEX2DAY_ID = 30;
var SHIP_FEDEX_GROUND_ID = 31;
var SHIP_FEXED_INTERNATIONAL_ECONOMY_ID = 32;
var SHIP_EXPEDITED_ID = 29;
var SHIP_BESTWAY_ID = 28;
var SHIP_PARCEL_POST = 36;
var SHIP_PRIORITY_MAIL = 37;
var SHIP_STANDARD = 38;
var SHIP_POST = 2;

/* Return Type List */
var RET_REFUND_ID = 7;
var RET_CLAIM_ID = 4;
var RET_LOCATION_GRANDRAPIDS_ID = 7;
var RET_INSPECTION_ID = 3;

/* Show/Hide 'Replacement' Control */
var requireReplacement = false;
var actionsWithReplacement = [];
var lastItemSearchResponse = [];

var retryListCount = 0;

/* Billing and Shipping address objects */
var orgBillingAddress = {};
var orgShippingAddress = {};

// Primary Objects
var contact = {};
var incident = {};
var orderHeader = {};
var billingAddress = {};
var shippingAddress = {};
		
// Initialize Workspace variables
ORACLE_SERVICE_CLOUD.extension_loader.load("Bissell Order" , "1.0").then(function(extensionProvider) {
	extensionProvider.registerWorkspaceExtension(function(WorkspaceRecord) {	
		WorkspaceRecord.getFieldValues([
		'BUS$OrderHeader.BillToCustomerName',
		'BUS$OrderHeader.BillToAddress1',
		'BUS$OrderHeader.BillToAddress2',
		'BUS$OrderHeader.BillToCity',
		'BUS$OrderHeader.BillToPostalCode',
		'BUS$OrderHeader.BillToCountry',
		'BUS$OrderHeader.BillToState',
		'BUS$OrderHeader.BillToPhone',
		'BUS$OrderHeader.ShipToCustomerName',
		'BUS$OrderHeader.ShipToAddress1',
		'BUS$OrderHeader.ShipToAddress2',
		'BUS$OrderHeader.ShipToCity',
		'BUS$OrderHeader.ShipToPostalCode',
		'BUS$OrderHeader.ShipToCountry',
		'BUS$OrderHeader.ShipToState',
		'BUS$OrderHeader.ShipToPhone',
		'BUS$OrderHeader.OrderType',
		'BUS$OrderHeader.OrderBrand',
		'BUS$OrderHeader.OrderChannel',
		'BUS$OrderHeader.OrderNumber',
		'BUS$OrderHeader.RANumber',
		'BUS$OrderHeader.RATrackingNumber',
		'BUS$OrderHeader.RAReturnType',
		'BUS$OrderHeader.RAReasonCode',
		'BUS$OrderHeader.RAActionCode',
		'BUS$OrderHeader.RAReturnLocation',
		'BUS$OrderHeader.RAReturnMethod',
		'BUS$OrderHeader.Total',
		'BUS$OrderHeader.SubTotal',
		'BUS$OrderHeader.Tax',
		'BUS$OrderHeader.SecondaryTax',
		'BUS$OrderHeader.ShippingTotal',
		'BUS$OrderHeader.LockTrans',
		'BUS$OrderHeader.FreeShipping',
		'BUS$OrderHeader.NoChargeOrder',
		'BUS$OrderHeader.ShipMethodReason',
		'BUS$OrderHeader.ExpectedDeliveryDate',
		'BUS$OrderHeader.PaymentType',
		'BUS$OrderHeader.ShipMethod',
		'BUS$OrderHeader.PaymentCheckNumber',
		'Contact.Email.Addr',
		'Contact.Name.First',
		'Contact.Name.Last',
		'Contact.CId',
		'Contact.Addr.Street',
		'Contact.Addr.City',
		'Contact.Addr.PostalCode',
		'Contact.Addr.CountryId',
		'Contact.Addr.ProvId',
		'Contact.PhHome',
		'Contact.BI$ship_street',
		'Contact.BI$ship_street2',
		'Contact.BI$ship_city',
		'Contact.BI$ship_state',
		'Contact.BI$ship_postalcode',
		'Contact.BI$ship_country',
		'Incident.IId',
		'Incident.C$Brand'
		]).then(function(fieldDetails) {
			orderHeader.orderType = fieldDetails.getField('BUS$OrderHeader.OrderType').getLabel();
			orderHeader.orderTypeID = fieldDetails.getField('BUS$OrderHeader.OrderType').getValue();
			orderHeader.orderChannel = fieldDetails.getField('BUS$OrderHeader.OrderChannel').getValue();
			orderHeader.shipToCountry = hasValue(fieldDetails.getField('BUS$OrderHeader.ShipToCountry').getValue()) == true ? fieldDetails.getField('BUS$OrderHeader.ShipToCountry').getValue() : fieldDetails.getField('Contact.BI$ship_country').getValue();
			orderHeader.billToCountry = hasValue(fieldDetails.getField('BUS$OrderHeader.BillToCountry').getValue()) == true ? fieldDetails.getField('BUS$OrderHeader.BillToCountry').getValue() : fieldDetails.getField('Contact.Addr.CountryId').getValue();
			orderHeader.returnAuthorizationNumber = fieldDetails.getField('BUS$OrderHeader.RANumber').getValue() || null;
			orderHeader.trackingNumber = fieldDetails.getField('BUS$OrderHeader.RATrackingNumber').getValue() || null;
			orderHeader.orderTotal = fieldDetails.getField('BUS$OrderHeader.Total').getValue() || "0.00";
			orderHeader.pageLocked = fieldDetails.getField('BUS$OrderHeader.LockTrans').getValue();
			orderHeader.shippingMethod = fieldDetails.getField('BUS$OrderHeader.ShipMethod').getValue();
			orderHeader.returnType = fieldDetails.getField('BUS$OrderHeader.RAReturnType').getValue();
			orderHeader.reasonCode = fieldDetails.getField('BUS$OrderHeader.RAReasonCode').getValue();
			orderHeader.actionCode = fieldDetails.getField('BUS$OrderHeader.RAActionCode').getValue();
			orderHeader.returnLocation = fieldDetails.getField('BUS$OrderHeader.RAReturnLocation').getValue();
			orderHeader.returnMethod = fieldDetails.getField('BUS$OrderHeader.RAReturnMethod').getValue();
			
			// CONTACT FIELDS
			contact.emailAddress = fieldDetails.getField('Contact.Email.Addr').getLabel();
			contact.firstName = fieldDetails.getField('Contact.Name.First').getLabel();
			contact.lastName = fieldDetails.getField('Contact.Name.Last').getLabel();
			contact.contactId = fieldDetails.getField('Contact.CId').getValue();
			
			var tempStreet = fieldDetails.getField('Contact.Addr.Street').getValue();
			var street1 = '';
			var street2 = '';
			
			if(tempStreet != undefined) {
				var addressArray = tempStreet.split("\n");
				street1 = addressArray[0];
				if(addressArray.length > 1)
					vstreet2 = addressArray[1];
			}
			
			contact.Address1 = street1;
			contact.Address2 = street2;
			contact.City = fieldDetails.getField('Contact.Addr.City').getValue();
			contact.PostalCode = fieldDetails.getField('Contact.Addr.PostalCode').getValue();
			contact.CountryID = fieldDetails.getField('Contact.Addr.CountryId').getValue();
			contact.StateID = fieldDetails.getField('Contact.Addr.ProvId').getValue();
			contact.Phone = fieldDetails.getField('Contact.PhHome').getValue();
			
			contact.shipAddress1 = fieldDetails.getField('Contact.BI$ship_street').getValue();
			contact.shipAddress2 = fieldDetails.getField('Contact.BI$ship_street2').getValue();
			contact.shipCity = fieldDetails.getField('Contact.BI$ship_city').getValue();
			contact.shipPostalCode = fieldDetails.getField('Contact.BI$ship_postalcode').getValue();
			contact.shipCountryID = fieldDetails.getField('Contact.BI$ship_country').getValue();
			contact.shipCountry = fieldDetails.getField('Contact.BI$ship_country').getLabel();
			contact.shipStateID = fieldDetails.getField('Contact.BI$ship_state').getValue();
			contact.shipStateCode = fieldDetails.getField('Contact.BI$ship_state').getLabel();
			
			// Add space to Canadian Postal Code, the Billing Address field on the Contact record does not keep the space!
			if(contact.CountryID == 2)
				contact.PostalCode = contact.PostalCode.substring(0, 3) + " " + contact.PostalCode.substring(3, 6);
			
			// INCIDENT FIELDS
			incident.id = fieldDetails.getField('Incident.IId').getValue();
			incident.brand = fieldDetails.getField('Incident.C$Brand').getLabel();
				
			// GET Billing Address
			billingAddress.CustomerName = fieldDetails.getField('BUS$OrderHeader.BillToCustomerName').getValue();
			billingAddress.Address1 = fieldDetails.getField('BUS$OrderHeader.BillToAddress1').getValue();
			billingAddress.Address2 = fieldDetails.getField('BUS$OrderHeader.BillToAddress2').getValue();
			billingAddress.City = fieldDetails.getField('BUS$OrderHeader.BillToCity').getValue();
			billingAddress.PostalCode = fieldDetails.getField('BUS$OrderHeader.BillToPostalCode').getValue();
			billingAddress.CountryCode = fieldDetails.getField('BUS$OrderHeader.BillToCountry').getLabel();
			billingAddress.CountryCodeId = fieldDetails.getField('BUS$OrderHeader.BillToCountry').getValue();
			billingAddress.StateCode = fieldDetails.getField('BUS$OrderHeader.BillToState').getLabel();
			billingAddress.StateCodeId = fieldDetails.getField('BUS$OrderHeader.BillToState').getValue();
			billingAddress.Phone = fieldDetails.getField('BUS$OrderHeader.BillToPhone').getValue();
				
			// GET Shipping Address
			shippingAddress.Name = fieldDetails.getField('BUS$OrderHeader.ShipToCustomerName').getValue();
			shippingAddress.Address1 = fieldDetails.getField('BUS$OrderHeader.ShipToAddress1').getValue();
			shippingAddress.Address2 = fieldDetails.getField('BUS$OrderHeader.ShipToAddress2').getValue();
			shippingAddress.City = fieldDetails.getField('BUS$OrderHeader.ShipToCity').getValue();
			shippingAddress.PostalCode = fieldDetails.getField('BUS$OrderHeader.ShipToPostalCode').getValue();
			shippingAddress.CountryCode = fieldDetails.getField('BUS$OrderHeader.ShipToCountry').getLabel();
			shippingAddress.CountryCodeId = fieldDetails.getField('BUS$OrderHeader.ShipToCountry').getValue();
			shippingAddress.StateCode = fieldDetails.getField('BUS$OrderHeader.ShipToState').getLabel();
			shippingAddress.StateCodeId = fieldDetails.getField('BUS$OrderHeader.ShipToState').getValue();
			shippingAddress.Phone = fieldDetails.getField('BUS$OrderHeader.ShipToPhone').getValue();
			
			// HELIX Set our original Billing and Shipping address objects for address validation
			orgBillingAddress.Address1 = billingAddress.Address1;
			orgBillingAddress.Address2 = billingAddress.Address2;
			orgBillingAddress.City = billingAddress.City;
			orgBillingAddress.State = billingAddress.StateCode;
			orgBillingAddress.Country = billingAddress.CountryCode;
			orgBillingAddress.Postal = billingAddress.PostalCode
		
			orgShippingAddress.Address1 = shippingAddress.Address1;
			orgShippingAddress.Address2 = shippingAddress.Address2;
			orgShippingAddress.City = shippingAddress.City;
			orgShippingAddress.State = shippingAddress.StateCode;
			orgShippingAddress.Country = shippingAddress.CountryCode;
			orgShippingAddress.Postal = shippingAddress.PostalCode;
			
			if(!addressHasValues("BillTo"))
				setBillToAddressFromContact();
			if(!addressHasValues("ShipTo"))
				setShipToAddressFromContact();

			//We need some async values before we can set the workspace values;
			$.when(defCountryListLoaded, defProvinceListLoaded, defProvinceAbbreviationListLoaded).done(function () {
				// GET State Abbreviation code after API load
				if(shippingAddress.StateCode == undefined)
					shippingAddress.StateCode = contact.shipStateCode;
				shippingAddress.StateAbbr = getProvinceAbbreviation(shippingAddress.CountryCodeId, shippingAddress.StateCode);
				
				if(billingAddress.StateCode == undefined)
					billingAddress.StateCode = contact.shipStateCode;
				billingAddress.StateAbbr = getProvinceAbbreviation(billingAddress.CountryCodeId, billingAddress.StateCode);
				
				setWorkspaceValues();
				buildShipMethodList();
				getReasonList_FULL();
				getActionList_FULL();
			});

			$.when(defActionCodeListLoaded,defReasonCodeListLoaded).done(function () {
				defOrderDataReady.resolve(true); //this deferred event is defined in the table.order.js file
			});

			$(document).on('replacementpartstable.updated replacementpartstable.delete', function (e) {

				buildShipMethodList(false);

				if (window.ReplacementPartsTable.rows().count() > 0) {

					$('#replacementGridContainer').show();
					$('#replacementButton').show();
					$('#fldSetShipping').removeClass('hidden');
					$('#shippingmethod').attr('required', 'required');

					if (!orderHeader.pageLocked) {
						if (orderHasAerosol()) {
							buildShipMethodList(true);
						}

						AutoSelectShippingMethod();

					}
				}

				SetHazardMessage();

				toggleReturnReplacementParts(addPartType);

			});

			$(document).on('replacementpartstable.init', function (e) {

				buildShipMethodList(false);

				if (window.ReplacementPartsTable.rows().count() > 0) {

					$('#replacementGridContainer').show();
					$('#replacementButton').show();
					$('#fldSetShipping').removeClass('hidden');
					$('#shippingmethod').attr('required', 'required');

					if (!orderHeader.pageLocked) {
						if (orderHasAerosol()) {
							buildShipMethodList(true);
						}
					}
				}
				
				SetHazardMessage();
				toggleReturnReplacementParts(addPartType);
				disableSubmittedOrder();
			});

			// Remove the 'Delete' link option if order has been submitted!
			$(document).on('returnpartstable.init', function (e) {
				disableSubmittedOrder();
			});
			
			$(document).ready(function () {

				if (contact.contactId < 1 || contact.contactId == null || currentIId < 1) {
					$.blockUI({ message: $('#noContactSelected') });
					return;
				}
				else {
					$.unblockUI;
				}

				$('.input-group.date').datepicker();
				
				var date = new Date();
				var isPartValid = false;
				selectedPartData = [];
				
				//Listen for the grid delete event and check for order lines
				$(document).on('ordertable.delete', function () {
					orderHasLines();
				});

				disableSubmittedOrder();
				
				// BUI ENHANCEMENT: Auto-Populate PartNumber form if valid PartNumber is passed in, otherwise display warning
				$('#partnumber').focusout(function () {
					var itemNumber = $(this).val();
						
					// CLEAR form if empty PartNumber is passed in
					if(itemNumber == '' )
					{
						$('#unitprice, #adjustedprice').val('');
						$('#partname').val('');
						$('#partdescription').val('');
						$('#modelnumber').val('');
						$('#serialnumber').val('');
						$('#qtyAvailable').val('');
						$('#quantity').val('');
						$('#uom').val('');
						$('#linetotal').val('0.00');
						$('#partnumber').data('inv', '');
						isPartValid = false;
					}
					
					// OTHERWISE, perform search against entered PartNumber
					else
					{
						var retSearch = addPartType == '2' ? '&returnsearch=true' : '';
						
						$.ajax({
							type: "GET",
							url: CUSTOM_SCRIPTS_PATH + 'partsearch.php?sid=' + sid + '&_=' + date.getTime() + '&itemnumber=' + itemNumber + '&country=' + shippingAddress.CountryCodeId + retSearch,
							cache: false,
							success: function (data) {
								if(data.length == 0)
								{
									$('#unitprice, #adjustedprice').val('');
									$('#partname').val('');
									$('#partdescription').val('');
									$('#modelnumber').val('');
									$('#serialnumber').val('');
									$('#qtyAvailable').val('');
									$('#quantity').val('');
									$('#uom').val('');
									$('#linetotal').val('0.00');
									$('#partnumber').data('inv', '');
									isPartValid = false;
									alert("Invalid Part Number");
								}
								else
								{
									isPartValid = true;
									setSelectedPart(data[0]);
								}
							},
							error: function (error) {
								alert(error);
							},
							dataType: "json"
						});
					}
				});

				function setSelectedPart(selectedData) {
					selectedPartData = selectedData;
					$('#unitprice, #adjustedprice').val(accounting.toFixed(selectedData.price, 2));
					$('#partname').val(selectedData.name);
					$('#partdescription').val(selectedData.description);
					if (addPartType == "2") {
						var mn = selectedData.partname;
						$('#modelnumber').val(mn);
						$('#serialnumber').val(selectedData.serialnumber);
					}
					$('#uom').val(selectedData.uom);
					$('#qtyAvailable').val(selectedData.inv);
					$('#quantity').val('1');
					$('#partnumber').data('inv', selectedData.inv);
					$('#partnumber').data('aerosol', selectedData.aerosol);
					$('#quantity').trigger('focusout');
				}

				//Build menu controls

				//Credit card expiration year

				$('#creditcardexpirationyear').append("<option value=''>[+]</option>");
				var currentYear = (new Date()).getFullYear();
				var endYear = currentYear + 11;

				for (var i = currentYear; i < endYear; i++) {
					$('#creditcardexpirationyear').append($("<option value=''></option>")
							.attr("value", i)
							.text(i));
				}

				//Return Type
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getmenulist&menu=BUS.OrderLine.ReturnType&sid=' + sid,
					cache: false,
					success: function (data) {
						defReturnTypeListLoaded.resolve();
						returnTypeList = data;
						buildReturnTypeList();
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

				//Country list
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getcountryisolist&sid=' + sid,
					cache: false,
					success: function (data) {
						countryList = data;
						defCountryListLoaded.resolve();
					},
					dataType: "json"
				});

				//Province list
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getprovincelist&sid=' + sid,
					cache: false,
					success: function (data) {
						provinceList = data;
						defProvinceListLoaded.resolve();
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

				//Province abbreviations
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getconfiguration&configkey=CUSTOM_CFG_PROVINCE_ABBREVIATION_JSON&sid=' + sid,
					cache: false,
					success: function (data) {
						provinceAbbreviationList = data;
						defProvinceAbbreviationListLoaded.resolve();
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

				//CONUS list
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getconfiguration&configkey=CUSTOM_CFG_CONUS_LIST_JSON&sid=' + sid,
					cache: false,
					success: function (data) {
						defCONUSListLoaded.resolve();
						conusList = data;
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});


				//Channel list from CBO
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getmenulist&menu=BUS.OrderHeader.OrderChannel&sid=' + sid,
					cache: false,
					success: function (data) {
						defChannelListLoaded.resolve();
						channelList = data;
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

				//Reason type change
				$('#returntype').change(function () {

					var returnType = getSelectedValue('returntype');
					orderHeader.returnType = returnType;
					buildReasonList(returnType);

					if (addPartType == "2") {

						buildActionList(returnType);
						buildReturnLocationList(returnType);
						buildReturnMethodList(returnType);

						if (RET_REFUND_ID == getSelectedValue('returntype')) {
							toggleOrderNumberRequired(true);
						} else {
							toggleOrderNumberRequired(false);
						}
					}

				});

				//action code change
				$('#actioncode').change(function () {
					var actionSelected = $('#actioncode').prop('selectedIndex');
					
					var actionCodeValue = parseInt(getSelectedValue('actioncode'));
					orderHeader.actionCode = actionCodeValue;
					
					actionSelected -= 1; //fix for [+] option @ index 0
					if (actionSelected >= 0) {
						requireReplacement = actionsWithReplacement[actionSelected];
						if (requireReplacement == true || window.ReplacementPartsTable.rows().count() > 0) {
							$('#replacementGridContainer').show();
							$('#replacementButton').show();
							$('#fldSetShipping').removeClass('hidden');
							$('#shippingmethod').attr('required', 'required');
						}
						else {
							$('#replacementGridContainer').hide();
							$('#replacementButton').hide();
							$('#fldSetShipping').addClass('hidden');
							$('#shippingmethod').removeAttr('required');
						}
					}
					else {
						$('#replacementGridContainer').hide();
						$('#replacementButton').hide();
						$('#fldSetShipping').addClass('hidden');
						$('#shippingmethod').removeAttr('required');
					}

					buildReturnLocationList(getSelectedValue('returntype'));
				}
				);

				//Ship method changed
				$('#shippingmethod').change(function () {
					var shipMethodCurrentValue = parseInt(getSelectedValue('shippingmethod')) || 0;
					var shipMethodText = getSelectedText('shippingmethod');
					var noChargeCheckbox = $("#nocharge");
					var defaultPrice = $("#adjustedprice").val()
					var adjustedPrice = $("#unitprice").val();
	
					orderHeader.shippingMethod = shipMethodCurrentValue;

					setRequiredPhysicalAddress(shipMethodCurrentValue);
				});
				
				// Return location changed
				$('#returnlocation').change(function () {
					var returnLocationValue = parseInt(getSelectedValue('returnlocation')) || 0;
					orderHeader.returnLocation = returnLocationValue;
				});
				
				// Return method changed
				$('#returnmethod').change(function () {
					var returnMethodValue = parseInt(getSelectedValue('returnmethod')) || 0;
					orderHeader.returnMethod = returnMethodValue;
				});
				
				//Check if serial number and model number are required for the selected shipping option
				$('#reasoncode').change(function () {
					var $selectedOption = $('#reasoncode').find(":selected");
					
					var reasonCodeValue = parseInt(getSelectedValue('reasoncode')) || 0;
					orderHeader.reasonCode = reasonCodeValue;

					if (addPartType == "2")
					{
						if($selectedOption.data('requiresserialnumber') == true)
							toggleSerialNumber(true);
						else
							toggleSerialNumber(false);

						if($selectedOption.data('requiresmodelnumber') == true)
							toggleModelNumber(true);
						else
							toggleModelNumber(false);
					}
				});

				$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
					var target = $(e.target).attr("data-toggle-option");
					toggleReturnReplacementParts(target);
				});

				//Calculate line total
				$("#quantity, #unitprice, #adjustedprice").focusout(function () {
					var price = $("#adjustedprice").val() || $("#unitprice").val();
					var qty = $("#quantity").val();

					if (price !== '' && qty != '') {
						var lineTotal = (price * qty);
						$("#linetotal").val(lineTotal);
						$("#linetotal").formatCurrency();
					}
				});

				//Adjusted price  - set required fields if changed
				$("#adjustedprice").focusout(function () {

					togglePriceAdjustmentReasonCode();

				});

				//Add order button event handler
				$("#btnAddLine").click(function () {

					//Double check that we have the return reason and action lists
					if (window.actionCodeList.length < 1) {
						getActionList_FULL();
					}

					if (window.reasonCodeList.length < 1) {
						getReasonList_FULL();
					}

					window.orderLineValidator = $("#orderlineform").validate({
						errorClass: 'validation-error',
						errorPlacement: function ($error, $element) {
							var id = $element.attr("id");

							$("#error_" + id).append($error);
						},
						ignore: ".ignore-line-validation",
						rules: {
							partnumber: {
								required: true,
								partnumberexists:true
							}
						},
						messages: {
							partnumber: {
								required: "Select or enter a part number"
							},
							quantity: {
								required: "Qty required",
								min: "1 or greater"
							},
							nochargereasoncode: {
								required: "Reason required"
							},
							shippingreasoncode: {
								required: "Reason required"
							},
							serialnumber: {
								required: "Serial # required"
							},
							modelnumber: {
								required: "Model # required"
							}

						}
					});

					window.orderLineValidator.form();

					if (window.orderLineValidator.valid() === false)
						return;

					var orderLine = {};

					var rows;

					if (addPartType == "2")
						rows = window.ReturnPartsTable.rows().data();
					if (addPartType == "3")
						rows = window.ReplacementPartsTable.rows().data();

					var price = $("#adjustedprice").val() || $("#unitprice").val();

					lineNumber = lineNumber + 1;

					setTimeout(function () {
						saveOrderLine();
					}, 500);

				});

				//Submit order button
				$('#submitorder').click(function () {

					$.blockUI({
						message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>',
						css: { border: 'none', backgroundColor: 'transparent' },
						timeout: 0,
						onBlock: function () {
							//Validate form fields
							window.orderSubmitFormValidator = $("#ordersubmitform").validate({
								errorClass: 'validation-error',
								errorPlacement: function ($error, $element) {
									var id = $element.attr("id");

									$("#error_" + id).append($error);
								},
								rules: {
									shippingmethod: {
										required: true,
										physicalshippingaddress: true
									}
								},
								messages: {
									shippingmethod: {
										required: "Select a shipping method"
									}
								}
							});

							window.orderSubmitFormValidator.form();

							if (window.orderSubmitFormValidator.valid() === false) {
								$.unblockUI();
								return;
							}

							if (isOrderValid() === false) {
								$.unblockUI();
								return;
							}

							//Double check that we have the return reason and action lists
							if (window.actionCodeList.length < 1) {
								getActionList_FULL();
							}

							if (window.reasonCodeList.length < 1) {
								getReasonList_FULL();
							}

							if (confirm('Are you sure you would like to submit this RA?')) {

								$.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: { border: 'none', backgroundColor: 'transparent' } });

								var orderSubmitObj = getNewOrderSubmitObject();
								orderSubmitObj.order.ContactId = contact.contactId;
								orderSubmitObj.order.OnyxIndividualId = consumerId;
								orderSubmitObj.order.BillingAddress = getAddressForOrderProcessing('BillTo');
								orderSubmitObj.order.ShippingAddress = getAddressForOrderProcessing("ShipTo");
								orderSubmitObj.order.LineItems = getLineItemsForOrderProcessing();
								orderSubmitObj.order.SubTotal = orderTotals.subtotal;
								orderSubmitObj.order.OrderTotal = orderTotals.grandtotal;
								orderSubmitObj.order.OrderHeaderId = orderHeaderId;
								orderSubmitObj.order.IncidentId = currentIId;
								orderSubmitObj.order.OrderDate = new Date(Date.now()).toISOString();
								orderSubmitObj.order.ShipAmount = getOrderAndShippingTotal().shipping;
								orderSubmitObj.order.CRMPurchaseOrderID = orderHeaderId;
								orderSubmitObj.order.Channel = getChannelNameFromId(orderHeader.orderChannel);
								orderSubmitObj.order.ReturnWarehouse = getSelectedDataAttribute('returnlocation', 'code');
								orderSubmitObj.order.ReturnWarehouseDetails = getSelectedDataAttribute('returnlocation', 'warehouse');
								orderSubmitObj.order.ActionCode = getActionCodeText(window.ReturnPartsTable.rows().data()[0]);
								orderSubmitObj.order.ReturnMethod = getSelectedText('returnmethod');

								if (requireReplacement) {
									orderSubmitObj.order.ShipCode = getSelectedDataAttribute('shippingmethod', 'code');
								} else {
									delete orderSubmitObj.order.ShipCode;
								}

								//Submit the order to the endpoint for processing
								submitOrderForProcessing(orderSubmitObj, orderSubmitSuccessCallback);
							}
						}
					});
				});

				jQuery.validator.addMethod("physicalshippingaddress", function (value, element) {
					var valid = true;
					if (requirePhysicalAddress == true && addressIsPOBox(getAddressForOrderProcessing('ShipTo')))
						valid = false;

					return valid;

				}, 'A physical shipping address is required');

				jQuery.validator.addMethod("partnumberexists", function (value, element) {
					return isPartValid;
				}, 'Enter a valid part number');


				$(document).ajaxStart(function () {
					$.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: { border: 'none', backgroundColor: 'transparent' } });
				});

				$(document).ajaxComplete(function () {
					$.unblockUI();
				});

			});
			
			/***** INITIALIZATION END *****/
			/***** WORKSPACE EVENT HANDLERS BEGIN *****/
			// Validation check before record is saved
			var onBeforeSave = function()
			{
				//If the page is locked, skip validation
				if (orderHeader.pageLocked == false)
					setOrderHeaderValuesBeforeSave();
			}
			
			// IF the Incident.PrimaryContact field changes, update the BUS$OrderAddress Billing/Shipping address for the new Contact
			var onContactChanged = function()
			{
				WorkspaceRecord.getFieldValues([
				'Contact.Email.Addr',
				'Contact.Name.First',
				'Contact.Name.Last',
				'Contact.CId',
				'Contact.Addr.Street',
				'Contact.Addr.City',
				'Contact.Addr.PostalCode',
				'Contact.Addr.CountryId',
				'Contact.Addr.ProvId',
				'Contact.BI$ship_street',
				'Contact.BI$ship_street2',
				'Contact.BI$ship_city',
				'Contact.BI$ship_state',
				'Contact.BI$ship_postalcode',
				'Contact.BI$ship_country'
				]).then(function(fieldDetails) {
					
					// CONTACT FIELDS
					contact.emailAddress = fieldDetails.getField('Contact.Email.Addr').getLabel();
					contact.firstName = fieldDetails.getField('Contact.Name.First').getLabel();
					contact.lastName = fieldDetails.getField('Contact.Name.Last').getLabel();
					contact.contactId = fieldDetails.getField('Contact.CId').getValue();
					
					var tempStreet = fieldDetails.getField('Contact.Addr.Street').getValue();
					var street1 = '';
					var street2 = '';
					
					if(tempStreet != undefined) {
						var addressArray = tempStreet.split("\n");
						street1 = addressArray[0];
						if(addressArray.length > 1)
							vstreet2 = addressArray[1];
					}
					
					contact.Address1 = street1;
					contact.Address2 = street2;
					contact.City = fieldDetails.getField('Contact.Addr.City').getValue();
					contact.PostalCode = fieldDetails.getField('Contact.Addr.PostalCode').getValue();
					contact.CountryID = fieldDetails.getField('Contact.Addr.CountryId').getValue();
					contact.StateID = fieldDetails.getField('Contact.Addr.ProvId').getValue();
					
					contact.shipAddress1 = fieldDetails.getField('Contact.BI$ship_street').getValue();
					contact.shipAddress2 = fieldDetails.getField('Contact.BI$ship_street2').getValue();
					contact.shipCity = fieldDetails.getField('Contact.BI$ship_city').getValue();
					contact.shipPostalCode = fieldDetails.getField('Contact.BI$ship_postalcode').getValue();
					contact.shipCountryID = fieldDetails.getField('Contact.BI$ship_country').getValue();
					contact.shipStateID = fieldDetails.getField('Contact.BI$ship_state').getValue();
					
					// Add space to Canadian Postal Code, the Billing Address field on the Contact record does not keep the space!
					if(contact.CountryID == 2)
						contact.PostalCode = contact.PostalCode.substring(0, 3) + " " + contact.PostalCode.substring(3, 6);
					
					// RESET Billing/Shipping address and loyalty status
					setBillToAddressFromContact();
					setShipToAddressFromContact();
					if(hasValue(contact.emailAddress))
						setLoyaltyStatus();
				});
			}
			
			// IF Any BUS$OrderHeader Address fields change, capture new field and toggle address validation
			var onAddressChanged = function(param)
			{
				switch(param.event.field)
				{
					case 'BUS$OrderHeader.BillToAddress1':
						billingAddress.Address1 = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToAddress2':
						billingAddress.Address2 = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToCity':
						billingAddress.City = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToPostalCode':
						billingAddress.PostalCode = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToCountry':
						billingAddress.CountryCodeId = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToState':
						billingAddress.StateCodeId = param.event.value;
						break;
					case 'BUS$OrderHeader.BillToPhone':
						billingAddress.Phone = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToAddress1':
						shippingAddress.Address1 = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToAddress2':
						shippingAddress.Address2 = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToCity':
						shippingAddress.City = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToPostalCode':
						shippingAddress.PostalCode = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToCountry':
						shippingAddress.CountryCodeId = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToState':
						shippingAddress.StateCodeId = param.event.value;
						break;
					case 'BUS$OrderHeader.ShipToPhone':
						shippingAddress.Phone = param.event.value;
						break;
					default:
						break;
				}
			}

			/**
			 * This function is called from the workspace when data on the workspace is changed
			 * @param {type} obj
			 */
			function ondataupdated(obj) {

				var skipOHUpdate = false;

				if (obj == ORDER_HEADER_OBJECT_NAME) {

					//Check to see if the shipToCountry changed
					var selectedShipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
					var selectedBillToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");

					if (shippingAddress.CountryCodeId != selectedShipToCountry) {

						shippingAddress.CountryCodeId = selectedShipToCountry;

						buildShipMethodList();

						getReasonList_FULL();
						getActionList_FULL();
					}

					if (orderHeader.billToCountry != selectedBillToCountry) {
						orderHeader.billToCountry = selectedBillToCountry;
						buildReturnTypeList();
					}            

					if (shippingAddress.CountryCodeId != selectedShipToCountry) {
						shippingAddress.CountryCodeId = selectedShipToCountry;
					}

					orderHeader.orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
				}

					setWorkspaceValues(skipOHUpdate);

			}
			
			WorkspaceRecord.addRecordSavingListener(onBeforeSave);
			
			WorkspaceRecord.addFieldValueListener('Incident.CId', onContactChanged);
			
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToAddress1', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToAddress2', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToCity', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToPostalCode', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToCountry', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToState', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToPhone', onAddressChanged);
			
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToAddress1', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToAddress2', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToCity', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToPostalCode', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToCountry', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToState', onAddressChanged);
			WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToPhone', onAddressChanged);

			/**
			 * Set values on the Workspace. This fires initially when the page loads
			 */
			function setWorkspaceValues(skipOHUpdate) {

				//If we have a billto, skip auto setting it
				var hasBillTo = addressHasValues("BillTo");

				if (!skipOHUpdate) {
					if (!hasBillTo)
					{
						setBillToAddressFromContact();
						setShipToAddressFromContact();
					}
				}

				$('#ordernumber').val(orderNumber);

				SetOrderTotals();

				if (!skipOHUpdate){
					//Return if we don't have a return type since all other dropdowns are based on that.
					if (orderHeader.returnType > 0) {
						setTimeout(function () {
							buildReturnTypeList(orderHeader.returnType, true);
						});
					
					} else {
						return;
					}

				if (orderHeader.shippingMethod > 0)
					setSelectControl('shippingmethod', orderHeader.shippingMethod);

				if (orderHeader.reasonCode > 0) {
						
					setTimeout(function () {
						buildReasonList(orderHeader.returnType, orderHeader.reasonCode, true);
					}, 100);
				} else {
					buildReasonList(orderHeader.returnType,null,true);
				}

				if (orderHeader.actionCode > 0) {
					setTimeout(function () {
						buildActionList(orderHeader.returnType, orderHeader.actionCode, true);
					},100);
				} else {
					buildActionList(orderHeader.returnType, null, true);
				}

				if (orderHeader.returnLocation > 0) {
					setTimeout(function () {
						buildReturnLocationList(orderHeader.returnType, orderHeader.returnLocation, true)
					},100);
				} else {
					buildReturnLocationList(orderHeader.returnType, null, true)
				}

				if (orderHeader.returnMethod > 0) {
					setTimeout(function () {
						buildReturnMethodList(orderHeader.returnType, orderHeader.returnMethod, true);
					},100);
				} else {
					buildReturnMethodList(orderHeader.returnType, null, true);
				}
			}
				$.unblockUI();
			}

			
			
			function setSelectControl(controlId, value) {

				$('#' + controlId + ' option[value="' + value + '"]')
											.attr('selected', true);
			}


			function setOrderHeaderValuesBeforeSave() {
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipMethod', orderHeader.shippingMethod );
				WorkspaceRecord.updateField('BUS$OrderHeader.RAReturnType', orderHeader.returnType);
				WorkspaceRecord.updateField('BUS$OrderHeader.RAReasonCode', orderHeader.reasonCode);
				WorkspaceRecord.updateField('BUS$OrderHeader.RAActionCode', orderHeader.actionCode);
				WorkspaceRecord.updateField('BUS$OrderHeader.RAReturnLocation', orderHeader.returnLocation);
				WorkspaceRecord.updateField('BUS$OrderHeader.RAReturnMethod', orderHeader.returnMethod);
				WorkspaceRecord.updateField('BUS$OrderHeader.Contact', contact.contactId);
			}

			/**
			 * Get the next line number for use as the Line enumerator for the grid rows
			 */
			function getNextLineNumber(partType) {

				var rows;

				if (partType == "2")
					rows = window.ReturnPartsTable.rows().data();
				if (partType == "3")
					rows = window.ReplacementPartsTable.rows().data();
				var rowCount = rows.length;
				return rowCount + 1;
			}


			function getOrderAndShippingTotal() {

				var totals = {};
				var rows = window.ReplacementPartsTable.rows().data();
				var shipTotal = 0.00;
				var ordTotal = 0.00; //Order total includes shipping
				var subTotal = 0.00;
				var totalForShippingSelection = 0.00; //Determine the actual cost of the items even if a item is being offered for free

				if (rows.length > 0) {

					for (var i = 0; i < rows.length; i++) {
						var row = rows[i];

						if (row.lineid != -2 && row.lineid != -1) {

							ordTotal = ordTotal + (parseFloat(row.adjustedprice) * parseInt(row.qty));

							totalForShippingSelection = totalForShippingSelection + (parseFloat(row.unitsellingprice) * parseInt(row.qty));
						}
					}

					subTotal = ordTotal;
					ordTotal = ordTotal + parseFloat(shipTotal);
				}

				totals.shipping = accounting.toFixed(shipTotal, 2);
				totals.order = accounting.toFixed(ordTotal, 2);
				totals.subtotal = accounting.toFixed(subTotal, 2);
				totals.totalforshippingselection = accounting.toFixed(totalForShippingSelection, 2);

				return totals;
			}
			
			// SET Order Totals on the UI and OrderHeader
			function SetOrderTotals() {
				var salesTax = 0.00;
				var grandTotal = 0.00;

				$('#ordersummary-subtotal').html("0.00");
				$('#ordersummary-tax').html("0.00");
				$('#ordersummary-shipping').html("0.00");
				$('#ordersummary-total').html("0.00");
				
				//WorkspaceRecord.updateField('BUS$OrderHeader.Total', grandTotal); 
				//WorkspaceRecord.updateField('BUS$OrderHeader.ShippingTotal', 0.00); 

				orderTotals.subtotal = 0.00;
				orderTotals.shipping = 0.00;
				orderTotals.salestax = salesTax;
				orderTotals.grandtotal = grandTotal;
			}
			
			function SetHazardMessage() {
				if (orderHasAerosol()) {
					$('#error_hazardousitems').html('Order contains an item that cannot be shipped by air');
				} else {
					$('#error_hazardousitems').html('');
				}
			}

			function AutoSelectShippingMethod() {

				var message = '';

				requirePhysicalAddress = false;

				if (orderHeader.pageLocked)
					return;

				if (orderHasAerosol()) {

					buildShipMethodList(true);

					if ((shippingIsAlaskaHawaii() || shippingIsAPOMailbox() || shippingIsUSTerritory()))
						return;

				}
				else {
					buildShipMethodList(false, true);
				}

				setShipMethod("", true);

				if (!orderHasLines()) {
					setShipMethod("", true);
					return;
				}

				if (shippingIsAustralia()) {
					setShipMethod(SHIP_POST, true);
					return;
				}

				if (shippingIsNewZealand()) {
					setShipMethod(SHIP_POST, true);
					return;
				}

				if (shippingIsUK()) {
					setTimeout(function () {
						setShipMethod(SHIP_STANDARD, true); return;
					}, 500);

				}

				//Check free shipping option
				if (!isNoChargeOrder()) {
					if ((shippingIsCanada() && order100OrMore()) || (shippingIsUS() && order40OrMore())) {
						buildReasonLists('shippingreasoncode', 4, REASON_WAIVE_PROMOTION);
						setTimeout(function () {
							$('#freeshipping').prop('checked', true).trigger('change');
						}, 500);
					}
				}

				if (shippingIsCanada() == true) {

					requirePhysicalAddress = true;
					setShipMethod(SHIP_FEDEX_GROUND_ID, false);

					return;

				}

				//Alaska/Hawaii
				if (shippingIsAlaskaHawaii()) {

					if (isNoChargeOrder()) { //No Charge shipping - True
						requirePhysicalAddress = true;
						setShipMethod(SHIP_FEDEX2DAY_ID, false);

					} else { //No Charge shipping - False 

						var poResponse = autoSelectShippingMethod_BillingPO();

						if (poResponse.shippingSet)
							return;
						message = message + poResponse.message;

					}

				} else {//Shipping is not Alaska/Hawaii

					if (shippingIsUSTerritory() == false && shippingIsAPOMailbox() == false) {

						var poResponse = autoSelectShippingMethod_BillingPO();

						if (poResponse.shippingSet)
							return;
						message = message + poResponse.message;

					}

					if (shippingIsUSTerritory() == true || shippingIsAPOMailbox() == true) {

						if (shippingIsAPOMailbox() == false && addressIsPOBox(getAddressForOrderProcessing('ShipTo'))) {

							requirePhysicalAddress = true;

						}

						setShipMethod(SHIP_PRIORITY_MAIL, true, [SHIP_PRIORITY_MAIL, SHIP_PARCEL_POST]);

					}

				}

				if (message.length > 0) {

					alert(message);
					setShipMethod("", false);
				}
			}

			function autoSelectShippingMethod_BillingPO() {

				var response = { message: "", shippingSet: false };

				//Is Billing a PO Box
				if (addressIsPOBox(getAddressForOrderProcessing('BillTo'))) { //Billing PO Box - True

					//Is PO Box in Shipping
					if (addressIsPOBox(getAddressForOrderProcessing('ShipTo'))) {

						if (orderLessThan25()) {//Shipping cost < $25 - True
							requirePhysicalAddress = true;
							setShipMethod(SHIP_PARCEL_POST, true, null, null);
							response.shippingSet = true;

						} else {//Shipping cost > $25
							requirePhysicalAddress = true;
							setShipMethod(SHIP_FEDEX_GROUND_ID, true, null, null);
							response.shippingSet = true;
						}

					} else {

						requirePhysicalAddress = true;
						setShipMethod(SHIP_FEDEX_GROUND_ID, true, null);

					}

				} else {//Billing PO Box - False

					if (addressIsPOBox(getAddressForOrderProcessing('ShipTo')) == false) {

						if (orderLessThan25()) {//Shipping cost < $25 - True

							setShipMethod(SHIP_BESTWAY_ID, true, null, null);
							response.shippingSet = true;

						} else {//Shipping cost < $25 - False
							requirePhysicalAddress = true;
							setShipMethod(SHIP_FEDEX_GROUND_ID, true, null, null);
							response.shippingSet = true;
						}
					}
				}

				return response;
			}

			function setShipMethod(value, enabled, availableOptions, excludeOptions) {

				if (enabled == true) {
					$('#shippingmethod').removeAttr('disabled');
				}

				if (availableOptions) {
					$('#shippingmethod > option').each(function () {
						if ($.inArray(parseInt(this.value), availableOptions) < 0 && this.value != "") {
							$(this).remove();
						}
					});
				}

				if (excludeOptions) {
					$('#shippingmethod > option').each(function () {
						if ($.inArray(parseInt(this.value), excludeOptions) >= 0 && this.value != "") {
							$(this).remove();
						}
					});
				}

				setTimeout(function () {
					$('#shippingmethod option[value="' + value + '"]').attr('selected', true).trigger('change');
				}, 1000);


			}

			function setRequiredPhysicalAddress(shipMethodCurrentValue) {

				requirePhysicalAddress = false;

				if (($.inArray(shipMethodCurrentValue, [SHIP_FEDEX2DAY_ID, SHIP_FEDEX_GROUND_ID, SHIP_FEDEX_OVERNIGHT_ID]) >= 0)) {
					requirePhysicalAddress = true;
				}

				return requirePhysicalAddress;

			}

			function isNoChargeOrder() {
				return true;
			}

			function shippingIsAlaskaHawaii() {
				if(shippingAddress.StateCode == 'Alaska' || shippingAddress.StateCode == 'Hawaii')
					return true;
				return false;
			}

			function shippingIsUSTerritory() {
				if(shippingAddress.CountryCode == 'US' && shipToStateIsCONUS() == false && shippingIsAlaskaHawaii() == false && shippingIsAPOMailbox() == false)
					return true;
				return false;
			}

			function shippingIsAPOMailbox() {
				var armedForcesStates = ["Armed Forces America", "Armed Forces Europe", "Armed Forces Pacific"];
				if ($.inArray(shippingAddress.StateCode, armedForcesStates) >= 0)
					return true;
				return false;
			}

			function shippingIsUK() {
				if (shippingAddress.CountryCodeId == UK_COUNTRY_ID)
					return true;
				return false;
			}

			function shippingIsCanada() {
				if (shippingAddress.CountryCodeId == CA_COUNTRY_ID)
					return true;
				return false;
			}

			function shippingIsUS() {
				if (shippingAddress.CountryCodeId == US_COUNTRY_ID)
					return true;
				return false;
			}

			function shippingIsAustralia() {
				if (shippingAddress.CountryCodeId == AU_COUNTRY_ID)
					return true;
				return false;
			}

			function shippingIsNewZealand() {
				if (shippingAddress.CountryCodeId == NZ_COUNTRY_ID)
					return true;
				return false;
			}

			function orderLessThan25() {
				if (getOrderAndShippingTotal().totalforshippingselection < 25)
					return true;
				return false;
			}

			function order100OrMore() {
				if (getOrderAndShippingTotal().totalforshippingselection >= 100)
					return true;
				return false;
			}

			function order40OrMore() {
				if (getOrderAndShippingTotal().totalforshippingselection >= 40)
					return true;
				return false;
			}

			function addressIsPOBox(address) {

				//var regex = /(?:P(?:ost(?:al)?)?[\.\-\s]*(?:(?:O(?:ffice)?[\.\-\s]*)?B(?:ox|in|\b|\d)|o(?:ffice|\b)(?:[-\s]*\d)|code)|box[-\s\b]*\d)/i;
				var regex = /\b(?:p\.?\s*o\.?|post\s+office)\s+box\b/i;

				var isPOBox = regex.test(address.Address1);

				return isPOBox;

			}


			function getLineItemsForOrderProcessing() {

				var orderRows = [];

				var rows = window.ReturnPartsTable.rows().data();

				//Add return parts
				if (rows.length > 0) {

					for (var i = 0; i < rows.length; i++) {

						var row = rows[i];

						var orderLineItem = {};

						orderLineItem.Sku = row.part;
						orderLineItem.Description = row.description;
						orderLineItem.Qty = "-" + row.qty; //A negative qty is required by BWS
						orderLineItem.EstShipDate = new Date(Date.now()).toISOString();
						orderLineItem.EstArrivalDate = new Date(Date.now()).toISOString();
						orderLineItem.ReturnLineType = getActionCodeText(row);
						orderLineItem.ReturnReason = getReturnReasonText(row);
						orderLineItem.Warehouse = row.returnlocation;
						orderLineItem.Total = 0;

						orderRows.push(orderLineItem);

					}
				}

				//Add replacement parts

				var rows = window.ReplacementPartsTable.rows().data();

				//Add return parts
				if (rows.length > 0) {

					for (var i = 0; i < rows.length; i++) {

						var row = rows[i];

						var orderLineItem = {};

						orderLineItem.Sku = row.part;
						orderLineItem.Description = row.description;
						orderLineItem.Qty = row.qty;
						orderLineItem.EstShipDate = new Date(Date.now()).toISOString();
						orderLineItem.EstArrivalDate = new Date(Date.now()).toISOString();
						orderLineItem.ReturnLineType = getActionCodeText(row);
						orderLineItem.ReturnReason = getReturnReasonText(row);
						orderLineItem.Warehouse = row.returnlocation;
						orderLineItem.Total = 0;

						orderRows.push(orderLineItem);

					}
				}

				return orderRows;

			}

			function getReturnReasonText(gridRow) {
				var reasonText = "";
				if (hasValue(gridRow.reasoncode)) {

					$.each(window.reasonCodeList, function (idx, reason) {
						if (reason.id == gridRow.reasoncode) {
							reasonText = reason.code || reason.label;
						}
					});
				}

				//If the text is still empty, make a call to get it directly from the cbo

				return reasonText;
			}

			function getActionCodeText(gridRow) {
				var actionText = "";
				if (gridRow.actioncode != null) {
					$.each(window.actionCodeList, function (idx, action) {
						if (action.id == gridRow.actioncode) {
							actionText = action.code || action.label;
						}
					});
				}

				return actionText;
			}

			function getCreditCardInfoForOrderProcessing() {

				var paymentInfo = {
					AccountNumber: ccDetails.AccountNumber,
					Amount: ccDetails.Amount,
					Name: ccDetails.Name,
					ExpMonth: ccDetails.ExpMonth,
					ExpYear: ccDetails.ExpYear,
					CVC: ccDetails.CVC,
					CardCode: ccDetails.CardCode,
					TransactionDate: new Date(Date.now()).toISOString()
				};

				return paymentInfo;
			}

			function getPaperCheckInfoForOrderProcessing() {

				var paymentInfo = {
					CheckNumber: paperCheckDetails.CheckNumber,
					Amount: getOrderAndShippingTotal().order,
					TransactionDate: new Date(Date.now()).toISOString(),
					Electronic: false
				};

				return paymentInfo;
			}
			
			// GET Billing/Shipping address for the order submission
			function getAddressForOrderProcessing(addressType) {
				var responseAddress = {};
				
				if(addressType == "BillTo")
				{
					responseAddress.EmailAddress = contact.emailAddress;
					responseAddress.Name = billingAddress.CustomerName;
					responseAddress.FirstName = contact.firstName;
					responseAddress.LastName = contact.lastName;
					responseAddress.Address1 = billingAddress.Address1;
					responseAddress.Address2 = billingAddress.Address2;
					responseAddress.City = billingAddress.City;
					responseAddress.StateCode = billingAddress.StateAbbr;
					responseAddress.CountryCode = billingAddress.CountryCode;
					responseAddress.PostalCode = billingAddress.PostalCode;
					responseAddress.Phone = billingAddress.Phone;
				}
				
				else if(addressType == "ShipTo")
				{
					responseAddress.EmailAddress = contact.emailAddress;
					responseAddress.Name = shippingAddress.Name;
					responseAddress.FirstName = contact.firstName;
					responseAddress.LastName = contact.lastName;
					responseAddress.Address1 = shippingAddress.Address1;
					responseAddress.Address2 = shippingAddress.Address2;
					responseAddress.City = shippingAddress.City;
					responseAddress.StateCode = shippingAddress.StateAbbr;
					responseAddress.CountryCode = shippingAddress.CountryCode;
					responseAddress.PostalCode = shippingAddress.PostalCode;
					responseAddress.Phone = shippingAddress.Phone;
				}
				return responseAddress;
			}

			// ADRESS VALIDATION
			function performAddressValidation(checkBill, checkShip) {
				var isValid = true;
				var formValidResponse = {valid:true,message:""};
				
				var billToValidatedResponse;
				var shipToValidatedResponse;
				
				// Only validate billing and/or shipping, we don't always need to validate both
				// IF Order Header Billing Address has been changed by the agent, perform the validation
				if(checkBill)
				{
					billToValidatedResponse = validateAddress(billingAddress);
					
					if (billToValidatedResponse.Code == "1" || billToValidatedResponse.Code == "2") 
					{
						// IF the agent Street address is identical to the validated Street address
						// AND the agent zip 5 digits match the first 5 of the 9 validate zip
						// THEN ignore validation prompt
						if(billToValidatedResponse.Validated.Address1 != billingAddress.Address1 ||
							billToValidatedResponse.Validated.PostalCode.indexOf(billingAddress.PostalCode) == -1)
						{
							var fbt = formatAddress(billToValidatedResponse.Validated);
							var useSuggestedBillTo = confirm("A different bill to address was suggested.\n Click OK to use this address:\n\n" + fbt);
						}
						else
							useSuggestedBillTo = true;
						
						if (useSuggestedBillTo) 
						{
							var btAddr = billToValidatedResponse.Validated;
							setAddressFromSuggestion('BillTo', btAddr.Address1, btAddr.Address2, btAddr.City, btAddr.CountryCode, btAddr.StateCode, btAddr.PostalCode);
						}	
						billAddrValid = true;
					}
					
					// Unable to validate Billing Address, allow agent to either use the entered address anyway, or return to order
					else
					{
						var overrideAddress = confirm("Unable to validate Billing Address.\n\nClick OK to submit order with current Billing Address.\nClick Cancel to return to order");
						
						if(overrideAddress)
							billAddrValid = true;
						else
							billAddrValid = false;
					}
				}
				
				// IF Order Header Shipping Address has been changed by the agent, perform the validation
				if(checkShip)
				{
					shipToValidatedResponse = validateAddress(shippingAddress);
					
					if (shipToValidatedResponse.Code == "1" || shipToValidatedResponse.Code == "2") 
					{
						// IF the agent Street address is identical to the validated Street address
						// AND the agent zip 5 digits match the first 5 of the 9 validate zip
						// THEN ignore validation prompt
						if(shipToValidatedResponse.Validated.Address1 != shippingAddress.Address1 ||
							shipToValidatedResponse.Validated.PostalCode.indexOf(shippingAddress.PostalCode) == -1)
						{
							var fst = formatAddress(shipToValidatedResponse.Validated);
							var useSuggestedShipTo = confirm("A different ship to address was suggested.\n Click OK to use this address:\n\n" + fst);
						}
						else
							var useSuggestedShipTo = true;
						
						if (useSuggestedShipTo) {
							var stAddr = shipToValidatedResponse.Validated;
							setAddressFromSuggestion('ShipTo', stAddr.Address1, stAddr.Address2, stAddr.City, stAddr.CountryCode, stAddr.StateCode, stAddr.PostalCode);
						}

						shipAddrValid = true;
					}

					// Unable to validate Shipping Address, allow agent to either use the entered address anyway, or return to order
					else
					{
						var overrideAddress = confirm("Unable to validate Shipping Address.\n\nClick OK to submit order with current Shipping Address.\nClick Cancel to return to order");
						
						if(overrideAddress)
							shipAddrValid = true;
						else
							shipAddrValid = false;
					}
				}

				if (hasValue(formValidResponse.message))
					alert(formValidResponse.message);
			}

			// COPY Contact Billing Address to OrderHeader
			function setBillToAddressFromContact()
			{
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToCustomerName', contact.firstName + ' ' + contact.lastName);
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress1', contact.Address1);
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress2', contact.Address2);
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToCity', contact.City);
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToCountry', contact.CountryID);
				WorkspaceRecord.updateField('BUS$OrderHeader.BillToPostalCode', contact.PostalCode);
					
				// GET Billing Address
				billingAddress.CustomerName = contact.firstName + ' ' + contact.lastName;
				billingAddress.Address1 = contact.Address1
				billingAddress.Address2 = contact.Address2
				billingAddress.City = contact.City
				billingAddress.PostalCode = contact.PostalCode
				billingAddress.CountryCodeId = contact.CountryID
				billingAddress.CountryCode = contact.shipCountry;
				billingAddress.StateCodeId = contact.StateID;
				billingAddress.StateCode = contact.StateCode;
				billingAddress.StateAbbr = getProvinceAbbreviation(billingAddress.CountryCodeId, billingAddress.StateCode);
				
				setTimeout(function () {
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToState', contact.StateID);
					
					// HELIX Set our original Billing address object for address validation if they are set on a new Order from the Contact
					orgBillingAddress.Address1 = contact.Address1;
					orgBillingAddress.Address2 = contact.Address2;
					orgBillingAddress.City = contact.City;
					orgBillingAddress.State = contact.StateID;
					orgBillingAddress.Country = contact.CountryID;
					orgBillingAddress.Postal = contact.PostalCode;
				},2000);
			}
			
			// COPY Contact Shipping Address to OrderHeader
			function setShipToAddressFromContact()
			{
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCustomerName', contact.firstName + ' ' + contact.lastName);
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress1', contact.shipAddress1);
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress2', contact.shipAddress2);
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCity', contact.shipCity);
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCountry', contact.shipCountryID);
				WorkspaceRecord.updateField('BUS$OrderHeader.ShipToPostalCode', contact.shipPostalCode);
				
				// GET Shipping Address
				shippingAddress.Name = contact.firstName + ' ' + contact.lastName;
				shippingAddress.Address1 = contact.shipAddress1;
				shippingAddress.Address2 = contact.shipAddress2;
				shippingAddress.City = contact.shipCity;
				shippingAddress.PostalCode = contact.shipPostalCode;
				shippingAddress.CountryCodeId = contact.shipCountryID;
				shippingAddress.CountryCode = contact.shipCountry;
				shippingAddress.StateCodeId = contact.shipStateID;
				shippingAddress.StateCode = contact.StateCode;
				shippingAddress.StateAbbr = getProvinceAbbreviation(shippingAddress.CountryCodeId, shippingAddress.StateCode);
			
				setTimeout(function () {
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToState', contact.shipStateID);
					
					// HELIX Set our original Shipping address object for address validation if they are set on a new Order from the Contact
					orgShippingAddress.Address1 = contact.shipAddress1;
					orgShippingAddress.Address2 = contact.shipAddress2;
					orgShippingAddress.City = contact.shipCity;
					orgShippingAddress.State = contact.shipStateID;
					orgShippingAddress.Country = contact.shipCountryID;
					orgShippingAddress.Postal = contact.shipPostalCode;
				}, 2000);
			}
		
			function setAddressFromSuggestion(addresstype, address1, address2, city, countryiso, statecode, postalcode)
			{
				var countryid = getCountryIdByISO(countryiso);
				var stateid = getProvinceIDByCountryId(countryid, statecode);
				stateid = parseInt(stateid) || null;
				
				if(addresstype == "BillTo")
				{
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress1', address1);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress2', address2);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToCity', city);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToCountry', countryid);
					setTimeout(function () {
						WorkspaceRecord.updateField('BUS$OrderHeader.BillToState', stateid);
					},2000);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToPostalCode', postalcode);
					
					// Reset the original Billing Address
					orgBillingAddress.Address1 = address1;
					orgBillingAddress.Address2 = address2;
					orgBillingAddress.City = city;
					orgBillingAddress.State = stateid;
					orgBillingAddress.Country = countryid;
					orgBillingAddress.Postal = postalcode;
				}
				
				else if(addresstype == "ShipTo")
				{
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress1', address1);
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress2', address2);
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCity', city);
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCountry', countryid);
					setTimeout(function () {
						WorkspaceRecord.updateField('BUS$OrderHeader.ShipToState', stateid);
					},2000);
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToPostalCode', postalcode);
					
					// Reset the original Shipping address
					orgShippingAddress.Address1 = address1;
					orgShippingAddress.Address2 = address2;
					orgShippingAddress.City = city;
					orgShippingAddress.State = stateid;
					orgShippingAddress.Country = countryid;
					orgShippingAddress.Postal = postalcode;
				}
			}

			function logSysIntegration(extSystem, requestMessage, responseMessage, error, description) {

				var info = {};
				info.ExtSystem = extSystem;
				info.RequestMsg = requestMessage;
				info.ResponseMsg = responseMessage;
				info.Error = error;
				info.Description = description;
				info.IncidentID = currentIId;
				info.ContactID = contact.contactId;
				info.OrderHeaderID = orderHeaderId;

				$.ajax({
					type: "POST",
					url: AJAX_ENDPOINT + '?action=logsysintegration&sid=' + sid,
					data: JSON.stringify(info),
					success: function (data) {
					},
					dataType: "json"
				});	
			}

			/**
			 * Call the address validation endpoint to verify that the address is valid
			 * Added a TODO: here since I'm making this call synchronously and it should be changed
			 * @param {type} addr
			 * @returns {type} 
			 */
			function validateAddress(addr) {

				var validatedAddress

				$.ajax({
					type: "POST",
					url: ADDRESS_VALIDATION_ENDPOINT + '?action=validateaddress&sid=' + sid,
					data: addr,
					cache: false,
					async: false, //TODO: Need to revisit this
					success: function (data) {

						validatedAddress = data;

					},
					dataType: "json",
					error: function (request, status, error) {
						alert(error);
					}
				});
				return validatedAddress;
			}
			
			function submitOrderForProcessing(orderDetails, orderSubmitSuccessCallback) {
				var data = {
					"Action": "submitorder",
					"ReturnAuth": orderDetails,
					"Session": sid
				};
				
				$.ajax({
					type: "POST",
					url: ORDER_SUBMIT_ENDPOINT + '?action=submitorder&sid=' + sid,
					data: JSON.stringify(data),
					cache: false,
					success: orderSubmitSuccessCallback,
					dataType: "json",
					timeout:360000,
					error: function (request, status, error) {
						logSysIntegration("BWS", "", "", "Error", "An error occurred while trying to process the RA. " + error);
						orderSubmitSuccessCallback(null);
					},

				});
			}


			function orderSubmitSuccessCallback(data) {

				$.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: { border: 'none', backgroundColor: 'transparent' } });
				
				WorkspaceRecord.updateField('BUS$OrderHeader.Total', 0.00); 
				WorkspaceRecord.updateField('BUS$OrderHeader.ShippingTotal', 0.00); 
				
				// cURL Timeout error
				if (data.ResponseMessage.toLowerCase() === 'timeout was reached') {

					var message = 'cURL Timeout, order will be queued for retry. Please do not resubmit order.';

					alert(message);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					
					orderHeader.pageLocked = true;

					disableSubmittedOrder();
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}
					
				if (hasValue(data.FedEx)) {
					if (hasValue(data.FedEx.TrackingNumber)) {
						WorkspaceRecord.updateField('BUS$OrderHeader.FedExLabelSent', 1);
					}
				}
				
				//A response code will only exist if there is a failure
				if (data == null || $.isNumeric(data.ResponseCode) || data.ResponseCode.toString().toLowerCase() == 'soap failure') {

					alert('An error occurred while attempting to submit the RA.');
					
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					
					orderHeader.pageLocked = true;
					
					disableSubmittedOrder();

					if (data.ResponseCode)
					{
						if (data.ResponseCode == 28)
							WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
						else
							WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Failed");
					} 
					else
						WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");

					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}

				if (hasValue(data.FedEx)) {
					if (hasValue(data.FedEx.TrackingNumber)) {
						WorkspaceRecord.updateField('BUS$OrderHeader.FedExLabelSent', 1);
					}
				}

				//Check for ResponseCode other than known codes
				if (!(data.ResponseCode.toLowerCase() === 'failure' || data.ResponseCode.toLowerCase() === 'success')) {

					var message = data.AgentMessage || "An error occurred while attempting to submit the RA.";

					alert(message);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
			
					orderHeader.pageLocked = true;
					disableSubmittedOrder(true);
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}
				
				if (data.ResponseCode.toLowerCase() === 'failure') {

					alert('An error occurred while attempting to submit the RA.');
					
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					
					orderHeader.pageLocked = true;
					disableSubmittedOrder(true);
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}

															 
				if (data.ResponseCode.toLowerCase() === 'success') {
					//Update the order
					var status = 'Submitted';
					alert('RA created successfully. The Return # is ' + data.ReturnNumber);

					orderHeader.returnAuthorizationNumber = parseInt(data.ReturnNumber);
					if(hasValue(data.FedEx)) {
						WorkspaceRecord.updateField('BUS$OrderHeader.FedExMessage', data.FedEx.FedExMessage);
						orderHeader.trackingNumber = data.FedEx.TrackingNumber || '';
						if (data.FedEx.FedExResponse == 'SUCCESS' || data.FedEx.FedExResponse == 'WARNING'){
							//message += '\r\n\r\n FedEx Tracking Number: ' + data.FedEx.TrackingNumber + '.';
						} else {
							//message += '\r\n\r\n FedEx Error Response: ' + data.FedEx.FedExMessage + '.';
							alert('FedEx Error Response: ' + data.FedEx.FedExMessage + '.');
							//i.QueueId = 33;
							status = 'FedEx Error';
						}
					}
					else
						orderHeader.trackingNumber = '';
					
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					WorkspaceRecord.updateField('BUS$OrderHeader.RANumber', orderHeader.returnAuthorizationNumber);
					WorkspaceRecord.updateField('BUS$OrderHeader.RATrackingNumber', orderHeader.trackingNumber);
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', status);
					
					orderHeader.pageLocked = true;
					disableSubmittedOrder(true);
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					location.reload();
					return;
				}
				return;
			}

			/**
			 * Validate the order fields before submitting.
			 * Required: Eamil Address, Order Type (consumer order) BillTo & ShipTo
			 * @returns {bool} 
			 */
			function isOrderValid() {
				var isvalid = true;
				var message = '';

				// HELIX address validation update, move logic to Submit Order validation rather than manual agent process
				var checkBill = false;
				var checkShip = false;
				
				// HELIX Compare original Billing/Shipping address values with values given by agent, if any changes are detected we need to validate
				if(orgBillingAddress.Address1 != billingAddress.Address1 ||
					orgBillingAddress.Address2 != billingAddress.Address2 ||
					orgBillingAddress.City != billingAddress.City ||
					orgBillingAddress.State != billingAddress.StateCode ||
					orgBillingAddress.Country != billingAddress.CountryCode ||
					orgBillingAddress.Postal != billingAddress.PostalCode )
					checkBill = true;
				
				if(orgShippingAddress.Address1 != shippingAddress.Address1 ||
					orgShippingAddress.Address2 != shippingAddress.Address2 ||
					orgShippingAddress.City != shippingAddress.City ||
					orgShippingAddress.State != shippingAddress.StateCode ||
					orgShippingAddress.Country != shippingAddress.CountryCode ||
					orgShippingAddress.Postal != shippingAddress.PostalCode )
					checkShip = true;
					
				// Only validate if we have discrepancy between original and agent provided addresses
				if(checkBill || checkShip)
					performAddressValidation(checkBill, checkShip);

				if(!billAddrValid)
					message = message + 'Billing Address has not been validated!\n\n';
				
				if(!shipAddrValid)
					message = message + 'Shipping Address has not been validated!\n\n';
				
				if(!billAddrValid || !shipAddrValid)
					isvalid = false;
				else
					isvalid = true;

				if (!hasValue(billingAddress.CustomerName)) {
					message = message + 'Enter a Bill To Name \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.Name)) {
					message = message + 'Enter a Ship To Name \n\n';
					isvalid = false;
				}

				if (!hasValue(billingAddress.Address1)) {
					message = message + 'Enter a Bill To Address \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.Address1)) {
					message = message + 'Enter a Ship To Address \n\n';
					isvalid = false;
				}

				if (!hasValue(billingAddress.City)) {
					message = message + 'Enter a Bill To City \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.City)) {
					message = message + 'Enter a Ship To City \n\n';
					isvalid = false;
				}

				if (!hasValue(billingAddress.PostalCode)) {
					message = message + 'Enter a Bill To Postal Code \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.PostalCode)) {
					message = message + 'Enter a Ship To Postal Code \n\n';
					isvalid = false;
				}

				if (!hasValue(billingAddress.StateCodeId) && !(shippingAddress.CountryCodeId == UK_COUNTRY_ID)) {
					message = message + 'Enter a Bill To State/Province \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.StateCodeId) && !(shippingAddress.CountryCodeId == UK_COUNTRY_ID)) {
					message = message + 'Enter a Ship To State/Province \n\n';
					isvalid = false;
				}

				// Force Billing/Shipping country to be the same
				if(billingAddress.CountryCodeId != shippingAddress.CountryCodeId)
				{
					message = message + 'Billing and Shipping Country must be the same. \n\n';
					isvalid = false;
				}
				
				// Do not allow Canadian Shipping Addresses to use a PO BOX
				if(addressIsPOBox(shippingAddress) && shippingAddress.CountryCodeId == CA_COUNTRY_ID)
				{
					message = message + 'Cannot ship to PO BOX for Canadian shipments. \n\n';
					isvalid = false;
				}
			
				//Check that RA is selected
				if (orderHeader.orderTypeID != ORDER_TYPE_RA_ID) {
					message = message + 'Select the correct Order Type\n\n';
					isvalid = false;
				}

				//Check number of return parts
				if (window.ReturnPartsTable.rows().count() == 0) {
					message = message + 'Add at least one return part.\n\n';
					isvalid = false;
				}

				if (requireReplacement == true && window.ReplacementPartsTable.rows().count() == 0) {
					message = message + 'A replacement part is required.\n\n';
					isvalid = false;
				}

				if (RET_CLAIM_ID == getSelectedValue('returntype')) {
					if (RET_LOCATION_GRANDRAPIDS_ID != getSelectedValue('returnlocation') && orderHeader.billToCountry == US_COUNTRY_ID) {
						message = message + 'All claims should have a return location of Grand Rapids.\n\n';
						isvalid = false;
					}
					if (getSelectedValue('returnmethod').toLowerCase() == 'email') {
						message = message + 'Email is not a valid return method for claims.\n\n';
						isvalid = false;
					}
				}
				
				if (!hasValue(shippingAddress.Phone) && (getSelectedText('returnmethod').toLowerCase() == 'email' || getSelectedText('returnmethod').toLowerCase() == 'mail')) {
					message = message + 'Ship to Phone number required for Email or Mail return methods.\n\n';
					isvalid = false;
				}

				if ((getSelectedText('returnmethod').toLowerCase() == 'email') && hasValue(contact.emailAddress) == false) {
					message = message + 'Contact email address is required for Email return methods.\n';
					isvalid = false;
				}

				var isAirFreight = $('#shippingmethod').find(":selected").data("airfreight");

				if (orderHasAerosol()) {

					if (shippingAddress.CountryCodeId == US_COUNTRY_ID) {

						if (isAirFreight == true) {
							valid = false;
							message = message + "One or more items cannot be shipped via air freight.\n\n";

						} else {

							if (shippingAddress.CountryCodeId == US_COUNTRY_ID && shipToStateIsCONUS() == false) {
								valid = false;
								message = message + "One or more items cannot be shipped to this location.\n\n";
							}
						}

					}
				}

				if (RET_REFUND_ID == getSelectedValue('returntype')) {
					//TODO: validate the original Order# and check that it is not an eBay order.
				}

				if (isvalid == false && hasValue(message) == true)
					alert(message);

				return isvalid;
			}

			/**
			 * Determine if an address has any values already set
			 * @param {type} addressType (BillTo | ShipTo)
			 * @returns boolean
			 */
			function addressHasValues(addressType)
			{
				if(addressType == "BillTo")
				{
					if(hasValue(billingAddress.Address1) == false && 
							hasValue(billingAddress.Address2) == false && 
							hasValue(billingAddress.City) == false && 
							(hasValue(billingAddress.StateCode) == false || billingAddress.StateCode == '[+]') &&
							hasValue(billingAddress.PostalCode) == false)
						return false;
					return true;
				}
				else
				{
					if(hasValue(shippingAddress.Address1) == false && 
							hasValue(shippingAddress.Address2) == false && 
							hasValue(shippingAddress.City) == false &&
							(hasValue(shippingAddress.StateCode) == false || shippingAddress.StateCode == '[+]') &&
							hasValue(shippingAddress.PostalCode) == false)
						return false;
					return true;
				}
			}

			/**
			 * Get the line total for the values currently entered
			 */
			function getCurrentLineTotal() {

				var price = $("#adjustedprice").val() || $("#unitprice").val();
				var qty = $("#quantity").val();
				var lineTotal = 0;

				if (price !== '' && qty != '') {
					lineTotal = (price * qty);
				}

				return accounting.toFixed(lineTotal, 2);
			}

			/**
			 * Process the order line and add it to the OrderLines table and update the grid
			 */
			function saveOrderLine() {

				var taxTotal = 0.00;
				var orderLine = {};
				orderLine.OrderHeaderId = orderHeaderId;
				orderLine.Status = "Entered";
				orderLine.TrackingNumber = "";
				orderLine.PartNumber = $("#partnumber").val();
				orderLine.PartDescription = $("#partdescription").val();
				orderLine.Quantity = $("#quantity").val();
				orderLine.UnitPrice = $("#unitprice").val();
				orderLine.AdjustedPrice = "0.00";
				orderLine.SerialNumber = $("#serialnumber").val();
				orderLine.ModelNumber = $("#modelnumber").val();
				orderLine.CouponSavings = "";
				orderLine.TotalAfterDiscount = "";
				orderLine.EstSalesTax = taxTotal;
				orderLine.SummaryInformation;
				orderLine.SubTotal = getCurrentLineTotal();
				orderLine.Shipping = 0.00;
				orderLine.SalesTax = taxTotal;
				orderLine.Discounts;
				orderLine.Total = accounting.toFixed(parseFloat(getCurrentLineTotal()) + parseFloat(orderLine.Shipping) + parseFloat(taxTotal), 2); //Including tax and shipping for the line
				orderLine.EstShipTax;
				orderLine.CouponCodeApplied;
				orderLine.SKU;
				orderLine.CouponCode;
				orderLine.RefundRequestOrderNum = $("#ordernumber").val();
				orderLine.RefundId;
				orderLine.RefundStatus;
				orderLine.RefundUrgency;
				orderLine.DRTVPaymentInfo;
				orderLine.PaymentNumber;
				orderLine.PaymentDueDate;
				orderLine.OriginalPaymentAmount;
				orderLine.AmountStillOwed;
				orderLine.CreditCardInfo;
				orderLine.SettlementResponse;
				orderLine.LineType = addPartType;
				orderLine.OrderType = orderHeader.orderType;
				orderLine.OrderNumber;
				orderLine.Line = getNextLineNumber(addPartType);
				orderLine.UnitOfMeasure = $("#uom").val();
				orderLine.Inventory = $("#partnumber").data("inv");
				orderLine.NoCharge = $("#nocharge").is(":checked");
				orderLine.Aerosol = YesNoStringToBool($("#partnumber").data("aerosol"));
				orderLine.ReasonCode = getSelectedValue('reasoncode');
				orderLine.ActionCode = getSelectedValue('actioncode');
				orderLine.ModelNumber = $("#modelnumber").val();
				orderLine.SeriallNumber = $("#serialnumber").val();

				//Add the line to the order lines object then add to the grid if all is good.

				$.ajax({
					type: "POST",
					url: AJAX_ENDPOINT + '?action=addorderline&sid=' + sid,
					data: orderLine,
					cache: false,
					success: function (data) {
						//A line object is returned ready to be added to the grid
						switch (addPartType) {
							case "2":
								window.ReturnPartsTable.row.add(data.line).draw();
								break;
							case "3":
								window.ReplacementPartsTable.row.add(data.line).draw();
								$(document).trigger('replacementpartstable.updated');
								break;
						}

						SetOrderTotals();

						resetFormAfterAddingLine();
					},
					dataType: "json",
					error: function (request, status, error) {
						alert(error);
					}
				});
			}

			function orderHasAerosol() {
				var hasAerosol = false;
				var rows = window.ReplacementPartsTable.rows().data();
				if (rows.length > 0) {
					for (var i = 0; i < rows.length; i++) {
						var row = rows[i];
						if (row.aerosol)
							hasAerosol = true;
					}
				}
				return hasAerosol;
			}

			function getChannelNameFromId(id) {
				return channelList[id];
			}

			function getCountryISONameById(countryId) {

				return countryList[countryId];
			}

			function getCountryIdByISO(val) {
				var countryId = ''
				$.each(countryList, function (value, text) {

					if (text.toLowerCase() == val.toLowerCase())
						countryId = value;
				});

				return countryId;
			}

			function getProvinceListForCountryId(countryId) {

				var provList = {};

				for (var i = 0; i < provinceList.length; i++) {
					if (provinceList[i].countryid == countryId) {
						provList[provinceList[i].provinceid] = provinceList[i].provincename;
					}
				}

				return provList;
			}

			function getProvinceIDByCountryId(countryid, provinceabbr) {

				var provinceList = getProvinceListForCountryId(countryid);
				var province = getProvinceNameFromAbbreviation(countryid, provinceabbr);
				var provinceId = ''

				$.each(provinceList, function (value, text) {

					if (text.toLowerCase() == province.toLocaleLowerCase())
						provinceId = value;
				});

				return provinceId;
			}


			function orderHasLines() {

				var rows = window.ReturnPartsTable.rows().data();
				var hasLines = false;

				if (rows.length < 1)
					return false;

				for (var i = 0; i < rows.length; i++) {
					var row = rows[i];
					if (row.lineid != -1 && row.lineid != -2) {
						hasLines = true;
						return hasLines;
					}
				}

				//If we make it here there are shipping and order total lines so remove them
				window.ReturnPartsTable.clear().draw(false);

				return hasLines;
			}

			/* Object Helpers */
			function disableSubmittedOrder(submissionDisable) {
				if (orderHeader.pageLocked) { 
					$('input, textarea, table, select, button').attr('disabled', 'disabled');
					$('.nav-tabs a[data-toggle-option="2"]').tab('show');
					$(".editor_remove").remove();
				};
			}

			// CHECK if Shipping Address State is part of the Continental United States
			function shipToStateIsCONUS()
			{
				for(var key in conusList)
					if(conusList.hasOwnProperty(key))
						if(key == shippingAddress.StateCode)
							return true;
				return false;
			}
			
			/* Control Helpers */

			/**
			 * Get the selected value of a select box
			 * @param {type} controlId
			 * @returns {type} 
			 */
			function getSelectedValue(controlId) {
				var val = $('#' + controlId + ' option:selected').val();
				return val;
			}

			function getSelectedDataAttribute(controlId, attrName) {
				var selectedOption = $('#' + controlId).find(":selected");
				return selectedOption.data(attrName);
			}

			/**
			 * Get the selected text of a select box
			 * @param {type} controlId
			 * @returns {type} 
			 */
			function getSelectedText(controlId) {
				var text = $('#' + controlId + ' option:selected').text();
				return text;
			}

			function getSelectedDataAttribute(controlId, attr) {
				var text = $('#' + controlId + ' option:selected').data(attr);
				return text;
			}

			function getProvinceAbbreviation(countryId, province) {

				if (!hasValue(province))
					return '';

				if (provinceAbbreviationList.countryid[countryId]) {

					var countryAbbrObj = provinceAbbreviationList.countryid[countryId];

					for (var provKey in countryAbbrObj) {

						if (countryAbbrObj.hasOwnProperty(provKey)) {

							if (countryAbbrObj[provKey] == province) {
								return provKey;
							}
						}
					}
				}

				return "";
			}

			function getProvinceNameFromAbbreviation(countryId, abbreviation) {

				var countryAbbrObj = provinceAbbreviationList.countryid[countryId];

				for (var provKey in countryAbbrObj) {

					if (countryAbbrObj.hasOwnProperty(provKey)) {

						if (abbreviation == provKey)
							return countryAbbrObj[provKey];
					}
				}
				return "";
			}

			/**
			 * Determine if a field has a value
			 * @param {string} fld
			 * @returns {bool} 
			 */
			function hasValue(fld) {
				if (fld == undefined || fld == null || fld == '' || fld == -1)
					return false;
				return true;
			}

			function YesNoStringToBool(val) {
				if (val.toLowerCase() == 'yes' || val.toLowerCase() == 'y')
					return 1;
				return 0;
			}

			function setStorageObject(key, value) {
				localStorage.setItem(key, JSON.stringify(value));
			}

			function toggleReturnReplacementParts(selectedOption) {
				selectedPartData = [];

				addPartType = selectedOption;
				var replacementRequired = false;
				var returnRows = window.ReturnPartsTable.rows().data();

				if (returnRows.length > 0) {

					for (var i = 0; i < returnRows.length; i++) {

						var row = returnRows[i];
						$.each(window.actionCodeList, function (idx,action) {
							if (action.id == row.actioncode) {
								if (action.requirereplacement == true)
									replacementRequired = true;
							}
						});

					}

				}
				
				if (selectedOption == '2') {

					$('#btnAddLine').html('Add Return Item');
					$('#qtyAvailableDiv').addClass('hidden');
					$('.returnfield').each(function () {
						$(this)
							.removeClass('hidden')
							.attr('required', 'required');
					});

					//Set the quantity to 1 for the return part
					$('#quantity').val("1").attr('disabled', 'disabled');

					if (window.ReplacementPartsTable.rows().count() > 0 || replacementRequired) {
						$('.replacement-view').each(function () {
							$(this)
								.removeClass('hidden');
						});

						$('#replacementButton').show();
						$('#replacementGridContainer').show();

					} else {
						$('.replacement-view').each(function () {
							$(this)
								.addClass('hidden');
						});
					}
				}

				if (selectedOption == '3') {

					$('#btnAddLine').html('Add Replacement Item');
					$('#qtyAvailableDiv').removeClass('hidden');
					$('#quantity').val("1").removeAttr('disabled');

					$('.returnfield').each(function () {
						$(this)
							.addClass('hidden')
							.removeAttr('required');
					});
					$('.replacement-view').each(function () {
						$(this)
							.removeClass('hidden');
					});
				}
			}

			function toggleOrderNumberRequired(required) {
				if (required == true) {
					$('#ordernumber').attr('required', 'required');
				} else {
					$('#ordernumber').removeAttr('required');
				}
			}

			function buildShipMethodList(limitToGround, reset) {
				var setOptions = function (data) {
					//Append blank options
					var blankOption = "<option value=''>[+]</option>";
					
					$('#shippingmethod')
						.empty()
						.append($(blankOption));

					$.each(window.menulists.shipping_methods, function (index, shipMethod) {

						var option = $("<option></option>");
						option.attr("value", shipMethod.id)
						.text(shipMethod.label)
						.data("charges", shipMethod.charges)
						.data("airfreight", shipMethod.airfreight)
						.data("code", shipMethod.code);

						if (limitToGround == true && (shipMethod.airfreight == true || (shippingIsAlaskaHawaii() || shippingIsAPOMailbox() || shippingIsUSTerritory()))) {
							option.prop('disabled', true);
							option.prop('selected', false);
							option.attr('title', "Not available: One or more items on order can't ship by Air.");
						}

						$('#shippingmethod').append(option);

					});
					if (hasValue(orderHeader.shippingMethod)) {

						$('#shippingmethod option[value="' + orderHeader.shippingMethod + '"]')
							.attr('selected', true)
							.trigger('change', true);
					}
				};

				if (reset === true) {

					setOptions(window.menulists.shipping_methods);

				} else {

					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getshipmethods&ordertype=' + orderHeader.orderType + '&country=' + shippingAddress.CountryCodeId + '&sid=' + sid,
						cache: true,
						success: function (data) {

							if (window.menulists == undefined)
								window.menulists = {};
							
					//REQ0006920: Added the below code by Subbu to sort the shipping methods based on displayOrder property
					data.sort(GetSortOrder("displayOrder"));
							window.menulists.shipping_methods = data;

							setOptions(data);

						},
						dataType: "json"
					});
				}
			}

			function getReasonList_FULL() {

				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getrareasonlist&returntypeid=0&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: false,
					retryCount:0,
					retryLimit:3,
					success: function (data) {

						window.reasonCodeList = data;

						defReasonCodeListLoaded.resolve();
					},
					error: function (xhr, status, error) {
						this.retryCount++;
						if (this.retryCount <= this.retryLimit) {
							$.ajax(this);
							return;
						}

					},
					dataType: "json"
				});
			}

			function getActionList_FULL() {

				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getraactionlist&returntypeid=0&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: false,
					retryCount: 0,
					retryLimit: 3,
					success: function (data) {

						window.actionCodeList = data;
						defActionCodeListLoaded.resolve();

					},
					error: function (xhr, status, error) {
						this.retryCount++;
						if (this.retryCount <= this.retryLimit) {
							$.ajax(this);
							return;
						}
					},
					dataType: "json"
				});
			}

			function buildReturnTypeList(selectedValue,cancelTrigger) {

				$('#returntype').empty();
				$('#returntype').append("<option value=''>[+]</option>");

				if (Object.keys(returnTypeList).length > 0) {

					$.each(returnTypeList, function (value, text) {

						//Don't add inspection for CA
						if (orderHeader.billToCountry == CA_COUNTRY_ID && value == RET_INSPECTION_ID)
							return;

						$('#returntype').append($("<option></option>")
						.attr("value", value)
						.text(text));
					});
				}

				if (selectedValue) {
					$('#returntype option[value="' + selectedValue + '"]')
								.attr('selected', true);
						if(cancelTrigger != true)
							$('#returntype').trigger('change');
				}

			}
			
			// BUILD ReasonList UI Menu
			function buildReasonList(returnType, selectedValue,cancelTrigger) {
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getrareasonlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: true,
					success: function (data) {

						var reasons = data;

						var blankOption = "<option value=''>[+]</option>";

						$('#reasoncode')
							.empty()
							.append(blankOption);

						$.each(reasons, function (idx, row) {
							//add options to drop-down
							$('#reasoncode').append($("<option></option>")
								.attr("value", row.id)
								.text(row.label)
								.data("requiresserialnumber", row.requiresserialnumber)
								.data("requiresmodelnumber", row.requiresmodelnumber)
								.data("code", row.code)
								);
						});

						if (reasons.length == 1) {
							$('#reasoncode option[value="' + reasons[0].id + '"]')
								.attr('selected', true);
								if(cancelTrigger != true)
									$('#reasoncode').trigger('change');
						}

						if (selectedValue) {
							$('#reasoncode option[value="' + selectedValue + '"]')
										.attr('selected', true);
							if(cancelTrigger != true)
								$('#reasoncode').trigger('change');
						}

					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});
			}

			function buildActionList(returnType, selectedValue,cancelTrigger) {
				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getraactionlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: false,
					success: function (data) {
						//reset replacements grid items
						requireReplacement = false;
						actionsWithReplacement = [];

						if (!selectedValue) {
							$('#replacementGridContainer').hide();
							$('#replacementButton').hide();
						}

						var x = 0;

						var actionsList = data;
						
						var blankOption = "<option value=''>[+]</option>";

						$('#actioncode')
							.empty()
							.append(blankOption);

						$.each(actionsList, function (idx, row) {
							// Added this code by Nayan for the Request# REQ0008298. 
							if (orderHeader.pageLocked || (!orderHeader.pageLocked && row.active == true)){
							//set drop-down list entry
							$('#actioncode').append($("<option></option>")
								.attr("value", row.id)
								.text(row.label)
								.data("code", row.code)
								.data("requirereplacement",row.requirereplacement)
								);

							//show replacement grid?
							if (row.requirereplacement == "true" || row.requirereplacement == true) {
								actionsWithReplacement[x] = true;
							}
							else {
								actionsWithReplacement[x] = false;
							}
							x += 1;
							}
						});

						if (actionsList.length == 1) {
							$('#actioncode option[value="' + actionsList[0].id + '"]')
								.attr('selected', true);
							if(cancelTrigger != true)
								$('#actioncode').trigger('change');
						}

						if (selectedValue) {
							$('#actioncode option[value="' + selectedValue + '"]')
								.attr('selected', true);
							if (cancelTrigger != true)
								$('#actioncode').trigger('change');
						}
					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

			}

			function buildReturnLocationList(returnType, selectedValue,cancelTrigger) {

				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getrareturnlocationlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: false,
					success: function (data) {
						defReturnLocationListLoaded.resolve();
						var locationsList = data;

						var blankOption = "<option value=''>[+]</option>";

						$('#returnlocation')
							.empty()
							.append(blankOption);

						var actionSelected = hasValue(getSelectedValue("actioncode"));
						var actionCode = getSelectedText("actioncode");

						$.each(locationsList, function (idx, row) {
							if (orderHeader.pageLocked || (!orderHeader.pageLocked && row.active == true)){
								var htmlToParse = $('#returnlocation').html();
								var optionAlreadyExists = htmlToParse.indexOf(row.location);

								if (optionAlreadyExists == -1) {

									$('#returnlocation').append($("<option></option>")
										.attr("value", row.id)
										.text(row.location)
										.data("action", row.action)
										.data("code", row.code)
										.data("warehouse", row.warehouse)
									);

								}
							}
						});

						if ($('#returnlocation option').length == 2) {
							$("#returnlocation option:eq(1)")
								.attr('selected', true);
							if(cancelTrigger != true)
								$('#returnlocation').trigger('change');
						}

						if (selectedValue) {
							$('#returnlocation option[value="' + selectedValue + '"]')
										.attr('selected', true);
							if (cancelTrigger != true)
								$('#returnlocation').trigger('change');
						}

					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

			}

			function buildReturnMethodList(returnType, selectedValue,cancelTrigger) {

				$.ajax({
					type: "GET",
					url: AJAX_ENDPOINT + '?action=getrareturnmethodlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shippingAddress.CountryCodeId) + '&sid=' + sid,
					cache: false,
					success: function (data) {

						var returnsList = data;
						
						var activeReturnsListCount = 0;
						var returnIndex = 0;

						var blankOption = "<option value=''>[+]</option>";

						$('#returnmethod')
							.empty()
							.append(blankOption);

						$.each(returnsList, function (idx, row) {

							if (orderHeader.pageLocked || (!orderHeader.pageLocked && row.active == true)){
								activeReturnsListCount = activeReturnsListCount + 1;
								returnIndex = idx;
								
								$('#returnmethod').append($("<option></option>")
								.attr("value", row.id)
								.text(row.label)
								.data("code", row.code)
								);
							}
						});

						//if (returnsList.length == 1) {
						if (activeReturnsListCount == 1) {
							//$('#returnmethod option[value="' + returnsList[0].id + '"]')
							$('#returnmethod option[value="' + returnsList[returnIndex].id + '"]')
								.attr('selected', true);
							if(cancelTrigger != true)
								$('#returnmethod').trigger('change');
						}

						if (selectedValue) {
							$('#returnmethod option[value="' + selectedValue + '"]')
										.attr('selected', true);
							if (cancelTrigger != true)
								$('#returnmethod').trigger('change');
						}

					},
					error: function (error) {
						alert(error);
					},
					dataType: "json"
				});

			}

			function toggleSerialNumber(enabledAndRequired) {

				if (enabledAndRequired == true) {
					$("#serialnumber").removeAttr('disabled');
					$("#serialnumber").attr('required', 'required');
					$("#serialnumber").addClass('validation-error');
				}
				if (enabledAndRequired == false) {
					$("#serialnumber").attr('disabled', 'disabled');
					$("#serialnumber").removeAttr('required').removeClass('validation-error');
				}
			}

			function toggleModelNumber(enabledAndRequired) {

				if (enabledAndRequired == true) {
					$("#modelnumber").removeAttr('disabled');
					$("#modelnumber").attr('required', 'required');
					$("#modelnumber").addClass('validation-error');
				}
				if (enabledAndRequired == false) {
					$("#modelnumber").attr('disabled', 'disabled');
					$("#modelnumber").removeAttr('required').removeClass('validation-error');
				}
			}


			/**
			 * Check to see if the shipping cost entered matches the amount based on the selected
			 * shipping method and order total.
			 */
			function hasShippingOveride() {

				var optionCharges = $('#shippingmethod option:selected').data("charges").ShippingCost;
				var shippingCharge;

				orderHeader.orderTotal = getOrderAndShippingTotal().order;

				for (var i = 0; i < optionCharges.length; i++) {

					if (orderHeader.orderTotal >= optionCharges[i].min && orderHeader.orderTotal < optionCharges[i].max) {
						shippingCharge = optionCharges[i].cost;
					}
				}
			}


			function formatAddress(addr) {
				var addr2 = hasValue(addr.Address2) ? addr.Address2 + "\n" : "";
				var formattedAddress = addr.Address1 + "\n" + addr2 + addr.City + ", " + addr.StateCode + " " + addr.CountryCode + " " + addr.PostalCode;
				return formattedAddress;
			};

			function resetFormAfterAddingLine() {
				$('.reset').each(function () {
					$(this).val('');
				});
			}

			//REQ0006920: Added the below function by Subbu to use this in the buildShipMethodList function to sort the Shipping methods based on displayOrder
			function GetSortOrder(prop) {  
				return function(array1, array2) {  
					if (array1[prop] > array2[prop]) {  
						return 1;  
					} else if (array1[prop] < array2[prop]) {  
						return -1;  
					}  
						return 0;  
				}  
			}

			/**
			 * Get a new instance of the order submission object
			 * @returns {type} 
			 */
			function getNewOrderSubmitObject() {

				var OrderObjectDefinition = {
					order: {
						BillingAddress: {
							Name: "",
							FirstName: "",
							LastName: "",
							Address1: "",
							City: "",
							StateCode: "",
							PostalCode: "",
							Country: "",
							CountryCode: "",
							EmailAddress: "",
							Phone: ""
						},
						ShippingAddress: {
							Name: "",
							FirstName: "",
							LastName: "",
							Address1: "",
							City: "",
							StateCode: "",
							PostalCode: "",
							Country: "",
							CountryCode: "",
							EmailAddress: "",
							Phone: ""
						},
						LineItems:
							[
								{
									Description: "",
									Sku: "",
									EstArrivalDate: null,
									EstShipDate: null,
									ReturnReason: "",
									ListPrice: "",
									Qty: "",
									FreeShipping: "",
									Total: "",
									SerialNo: "",
									AdjustedTotal: ""
								}
							],
						Channel: "",
						OnyxIndividualId: "",
						OrderDate: "",
						OrderTotal: "",
						ShipAmount: "",
						ShipCode: null,
						SubTotal: "",
						CRMPurchaseOrderID: "",
						OrderHeaderId: "",
						IncidentId: "",
						ContactId: "",
						ReturnWarehouse: "",
						ReturnWarehouseDetails: null,
						ActionCode: null,
						ReturnMethod: null
					}
				};

				return OrderObjectDefinition;
			}
		});
	});
});

function getStorageObject(key) {
	var storageVal = localStorage.getItem(key);
	if (storageVal != null) {
		return JSON.parse(storageVal);
	}
	return null
}