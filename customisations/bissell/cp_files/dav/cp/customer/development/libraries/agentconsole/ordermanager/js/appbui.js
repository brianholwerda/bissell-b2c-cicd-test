/* Workspace Interactions */
localStorage.clear();

var CUSTOM_SCRIPTS_PATH = window.location.protocol + "//" + window.location.hostname + "/cgi-bin/bissell.cfg/php/custom/";
var PORTAL_SCRIPT_PATH = "";

//Set paths for sites other than localhost
if (!(location.hostname === "localhost" || location.hostname === "127.0.0.1")) {
	PORTAL_SCRIPT_PATH = "../cp/customer/development/libraries/agentconsole/ordermanager/";
	CUSTOM_SCRIPTS_PATH = "/cgi-bin/bissell.cfg/php/custom/";
}

var addressValid = true;
var billAddrValid = true;
var shipAddrValid = true;
var AJAX_ENDPOINT = CUSTOM_SCRIPTS_PATH + "ordermanagerajaxhandler.php";
var ADDRESS_VALIDATION_ENDPOINT = CUSTOM_SCRIPTS_PATH + "validateaddress.php";
var LOYALTY_STATUS_ENDPOINT = CUSTOM_SCRIPTS_PATH + "loyaltydata.php";
var ORDER_SUBMIT_ENDPOINT = CUSTOM_SCRIPTS_PATH + "submitorder.php";
//var AJAX_ENDPOINT_ORDERMANAGER = CUSTOM_SCRIPTS_PATH + "contactorderhistory.php";
var AJAX_ENDPOINT_PAYMETRIC = CUSTOM_SCRIPTS_PATH + "paymetric.php";
var INCIDENT_OBJECT_NAME = "Incident";
var ORDER_HEADER_OBJECT_NAME = "BUS$OrderHeader";
var AU_COUNTRY_ID = 4;
var NZ_COUNTRY_ID = 3;
var US_COUNTRY_ID = 1;
var UK_COUNTRY_ID = 5;
var CA_COUNTRY_ID = 2;
var ORDER_TYPE_CONSUMER_ID = 1;
var consumerId = getParameterByName('consumer_id');
var lineNumber = 0;
var orderHeaderId = getParameterByName('oid'); //The only way to get the BUS.OrderHeader.ID is to pass it in the URL.
var profileID = 0;
var sid = getParameterByName('sid'); //Agent session id
var hasManualShippingOverride = false;
var ccDetails = {};
var paperCheckDetails = getStorageObject('paperCheckDetails') || {};
var countryList = [];
var provinceList = [];
var provinceAbbreviationList = [];
var conusList = [];
var reasonCodeList = [];
var channelList = [];
var paperCheckDenyList = [];
var orderTotals = {};
var salesTaxData = null;
var paymetricData = {};

// CYBERSOURCE INTEGRATION DETAILS
let cybersourceFlex = null;
let cybersourceMicroForm = null;
let cybersourceNumber = null;
let cybersourceCVV = null;
let cybersourceToken = null;

var lastItemSearchResponse = [];

/* custom validation rule attributes */
var requirePhysicalAddress = false;

/* Deferred Events */
var defCountryListLoaded = $.Deferred();
var defProvinceListLoaded = $.Deferred();
var defProvinceAbbreviationListLoaded = $.Deferred();
var defCONUSListLoaded = $.Deferred();
var defReasonCodeListLoaded = $.Deferred();
var defChannelListLoaded = $.Deferred();
var defPaymentMethodListLoaded = $.Deferred();

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

var REASON_WAIVE_PROMOTION = 32;

/* Payment types */
var PAY_NO_CHARGE_ID = 4;

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
	extensionProvider.getGlobalContext().then(function(GlobalContext) {
		profileID = GlobalContext.getProfileId();
		extensionProvider.registerWorkspaceExtension(function(WorkspaceRecord) {	
			WorkspaceRecord.getFieldValues([
			'BUS$OrderHeader.BillToCustomerName',
			'BUS$OrderHeader.BillToAddress1',
			'BUS$OrderHeader.BillToAddress2',
			'BUS$OrderHeader.BillToCity',
			'BUS$OrderHeader.BillToPostalCode',
			'BUS$OrderHeader.BillToCountry',
			'BUS$OrderHeader.BillToState',
			'BUS$OrderHeader.ShipToCustomerName',
			'BUS$OrderHeader.ShipToAddress1',
			'BUS$OrderHeader.ShipToAddress2',
			'BUS$OrderHeader.ShipToCity',
			'BUS$OrderHeader.ShipToPostalCode',
			'BUS$OrderHeader.ShipToCountry',
			'BUS$OrderHeader.ShipToState',
			'BUS$OrderHeader.OrderType',
			'BUS$OrderHeader.OrderBrand',
			'BUS$OrderHeader.NoChargeReason',
			'BUS$OrderHeader.OrderChannel',
			'BUS$OrderHeader.OrderNumber',
			'BUS$OrderHeader.Total',
			'BUS$OrderHeader.SubTotal',
			'BUS$OrderHeader.Tax',
			'BUS$OrderHeader.SecondaryTax',
			'BUS$OrderHeader.ProductCareEcoFee',
			'BUS$OrderHeader.ShippingTotal',
			'BUS$OrderHeader.LockTrans',
			'BUS$OrderHeader.FreeShipping',
			'BUS$OrderHeader.NoChargeOrder',
			'BUS$OrderHeader.ShipMethodReason',
			'BUS$OrderHeader.ExpectedDeliveryDate',
			'BUS$OrderHeader.PaymentType',
			'BUS$OrderHeader.ShipMethod',
			'BUS$OrderHeader.PaymentCheckNumber',
			'BUS$OrderHeader.Status',
			'Contact.Email.Addr',
			'Contact.Name.First',
			'Contact.Name.Last',
			'Contact.CId',
			'Contact.BI$tax_exempt',
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
			'Contact.BI$ship_country',
			'Incident.IId',
			'Incident.C$Brand',
			'Incident.RefNo'
			]).then(function(fieldDetails) {
				// ORDER HEADER FIELDS
				orderHeader.orderType = fieldDetails.getField('BUS$OrderHeader.OrderType').getLabel();
				orderHeader.orderTypeID = fieldDetails.getField('BUS$OrderHeader.OrderType').getValue();
				orderHeader.orderBrand = fieldDetails.getField('BUS$OrderHeader.OrderBrand').getValue();
				orderHeader.orderNoChargeReason = fieldDetails.getField('BUS$OrderHeader.NoChargeReason').getValue();
				orderHeader.orderChannel = fieldDetails.getField('BUS$OrderHeader.OrderChannel').getValue();
				var shipToPostal = fieldDetails.getField('BUS$OrderHeader.ShipToPostalCode').getLabel();
				var billToState = fieldDetails.getField('BUS$OrderHeader.BillToState').getLabel();
				var orderNumber = fieldDetails.getField('BUS$OrderHeader.OrderNumber').getLabel() || '';
				orderHeader.orderNumber = fieldDetails.getField('BUS$OrderHeader.OrderNumber').getValue() || null;
				orderHeader.orderTotal = fieldDetails.getField('BUS$OrderHeader.Total').getLabel() || '0.00';
				orderHeader.orderSubTotal = fieldDetails.getField('BUS$OrderHeader.SubTotal').getLabel() || '0.00';
				orderHeader.orderSalesTaxTotal = fieldDetails.getField('BUS$OrderHeader.Tax').getLabel() || '0.00';
				orderHeader.orderSecondarySalesTaxTotal = fieldDetails.getField('BUS$OrderHeader.SecondaryTax').getLabel() || '0.00';
				orderHeader.orderProductEcoCareFeeTotal = fieldDetails.getField('BUS$OrderHeader.ProductCareEcoFee').getLabel() || 0.00;
				orderHeader.shippingTotal = fieldDetails.getField('BUS$OrderHeader.ShippingTotal').getLabel() || '0.00';
				var pageLocked = fieldDetails.getField('BUS$OrderHeader.LockTrans').getValue();
				orderHeader.freeShipping = fieldDetails.getField('BUS$OrderHeader.FreeShipping').getValue();
				orderHeader.noCharge = fieldDetails.getField('BUS$OrderHeader.NoChargeOrder').getValue();
				orderHeader.shippingReason = fieldDetails.getField('BUS$OrderHeader.ShipMethodReason').getValue();
				var expectedDeliveryDate = fieldDetails.getField('BUS$OrderHeader.ExpectedDeliveryDate').getLabel();
				orderHeader.paymentMethod = fieldDetails.getField('BUS$OrderHeader.PaymentType').getValue();
				orderHeader.shippingMethod = fieldDetails.getField('BUS$OrderHeader.ShipMethod').getValue();
				orderHeader.paymentCheckNumber = fieldDetails.getField('BUS$OrderHeader.PaymentCheckNumber').getValue(); 
				orderHeader.status = fieldDetails.getField('BUS$OrderHeader.Status').getValue();
				
				// CONTACT FIELDS
				contact.emailAddress = fieldDetails.getField('Contact.Email.Addr').getLabel();
				contact.firstName = fieldDetails.getField('Contact.Name.First').getLabel();
				contact.lastName = fieldDetails.getField('Contact.Name.Last').getLabel();
				contact.contactId = fieldDetails.getField('Contact.CId').getValue();
				contact.taxStatus = fieldDetails.getField('Contact.BI$tax_exempt').getValue();
				
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
				contact.CountryLabel = fieldDetails.getField('Contact.Addr.CountryId').getLabel();
				contact.StateID = fieldDetails.getField('Contact.Addr.ProvId').getValue();
				contact.StateLabel = fieldDetails.getField('Contact.Addr.ProvId').getLabel();
				
				contact.shipAddress1 = fieldDetails.getField('Contact.BI$ship_street').getValue();
				contact.shipAddress2 = fieldDetails.getField('Contact.BI$ship_street2').getValue();
				contact.shipCity = fieldDetails.getField('Contact.BI$ship_city').getValue();
				contact.shipPostalCode = fieldDetails.getField('Contact.BI$ship_postalcode').getValue();
				contact.shipCountryID = fieldDetails.getField('Contact.BI$ship_country').getValue();
				contact.shipCountry = fieldDetails.getField('Contact.BI$ship_country').getLabel();
				contact.shipStateID = fieldDetails.getField('Contact.BI$ship_state').getValue();
				contact.shipStateCode = fieldDetails.getField('Contact.BI$ship_state').getLabel();
				
				// Add space to Canadian Postal Code, the Billing Address field on the Contact record does not keep the space!
				if(contact.CountryID == 2 && contact.PostalCode.indexOf(' ') == -1)
					contact.PostalCode = contact.PostalCode.substring(0, 3) + " " + contact.PostalCode.substring(3, 6);
				
				// INCIDENT FIELDS
				incident.id = fieldDetails.getField('Incident.IId').getValue();
				incident.brand = fieldDetails.getField('Incident.C$Brand').getValue();
				incident.refno = fieldDetails.getField('Incident.RefNo').getValue();
					
				// GET Billing Address
				billingAddress.Name = fieldDetails.getField('BUS$OrderHeader.BillToCustomerName').getValue();
				billingAddress.Address1 = fieldDetails.getField('BUS$OrderHeader.BillToAddress1').getValue();
				billingAddress.Address2 = fieldDetails.getField('BUS$OrderHeader.BillToAddress2').getValue();
				billingAddress.City = fieldDetails.getField('BUS$OrderHeader.BillToCity').getValue();
				billingAddress.PostalCode = fieldDetails.getField('BUS$OrderHeader.BillToPostalCode').getValue();
				billingAddress.CountryCode = fieldDetails.getField('BUS$OrderHeader.BillToCountry').getLabel();
				billingAddress.CountryCodeId = fieldDetails.getField('BUS$OrderHeader.BillToCountry').getValue();
				billingAddress.StateCode = fieldDetails.getField('BUS$OrderHeader.BillToState').getLabel();
				billingAddress.StateCodeId = fieldDetails.getField('BUS$OrderHeader.BillToState').getValue();
					
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
					
				//We need some async values before we can set the workspace values;
				$.when(defCountryListLoaded, defProvinceListLoaded, defProvinceAbbreviationListLoaded, defReasonCodeListLoaded).done(function () {
					
					if(!addressHasValues("BillTo"))
						setBillToAddressFromContact();
					if(!addressHasValues("ShipTo"))
						setShipToAddressFromContact();
				
					// GET State Abbreviation code after API load
					if(shippingAddress.StateCode == undefined || shippingAddress.StateCode == '[+]')
						shippingAddress.StateCode = contact.StateLabel;
					shippingAddress.StateAbbr = getProvinceAbbreviation(shippingAddress.CountryCodeId, shippingAddress.StateCode);
					
					WorkspaceRecord.updateFieldByLabel('BUS$OrderHeader.ShipToState', shippingAddress.StateCode);
					
					if(billingAddress.CountryCode == undefined || billingAddress.CountryCode == '[+]')
						billingAddress.CountryCode = contact.CountryLabel;
				
					if(billingAddress.StateCode == undefined)
						billingAddress.StateCode = contact.StateLabel;
					billingAddress.StateAbbr = getProvinceAbbreviation(billingAddress.CountryCodeId, billingAddress.StateCode);
					
					// Build these menus after the Billing/Shipping addresses are set as they are dependent
					buildOrderBrandList();
					buildOrderNoChargeReasonList();
					buildPaymentMethodList();
					buildShipMethodList();
					buildCreditCardBillingStateList();
					
					setWorkspaceValues();
					defOrderDataReady.resolve(true); //this deferred event is defined in the table.order.js file
					disableSubmittedOrder();
				});

				$(document).ready(function () {
					if (contact.contactId < 1 || contact.contactId == null || incident.id < 1) {
						$.blockUI({ message: $('#noContactSelected') });
						return;
					}
					else
						$.unblockUI;
					
					if(hasValue(contact.emailAddress))
						setLoyaltyStatus();

					$('.input-group.date').datepicker();
					var date = new Date();
					var isPartValid = false;
					
					var selectedPartData = [];
					
					Ladda.bind('.mt-ladda-btn', { timeout: 2000 });

					//Listen for the grid delete event and check for order lines
					$(document).on('ordertable.delete ordertable.rowadded', function () {
						setTimeout(function () {
							SetHazardMessage();
							AutoSelectShippingMethod();
							SetOrderTotals();
							if (!orderHasLines())
								$('#freeshipping').prop("checked", false);
						}, 500);
					});

					$(document).on('ordertable.init', function () {
						setTimeout(function () {
							SetHazardMessage();
							SetOrderTotals();
						}, 500);
					});
					
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
							$.ajax({
								type: "GET",
								url: CUSTOM_SCRIPTS_PATH + 'partsearch.php?sid=' + sid + '&_=' + date.getTime() + '&itemnumber=' + itemNumber + '&country=' + shippingAddress.CountryCodeId ,
								cache: false,
								success: function (data) {
								if(data.length == 0)
								{
									$('#unitprice, #adjustedprice').val('');
									$('#partname').val('');
									$('#selectedpartnumber').val('');
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
						$('#qtyActual').val(selectedData.actualinv);
						$('#unitprice, #adjustedprice').val(accounting.toFixed(selectedData.price, 2));
						$('#partname').val(selectedData.name);
						$('#selectedpartnumber').val(selectedData.name);
						$('#partdescription').val(selectedData.description);
						$('#modelnumber').val(selectedData.model);
						$('#uom').val(selectedData.uom);
						$('#qtyAvailable').val(selectedData.inv);
						$('#quantity').val('1');
						$('#partnumber').data('inv', selectedData.inv);
						$('#partnumber').data('aerosol', selectedData.aerosol);
						$('#quantity').trigger('focusout');
						
						if(selectedData.actualinv > 9){
							$('#qtyAvailable').removeClass('validation-error');
							$("#qtyActual-error").css("display", "none");
						}

						if (isNoChargeOrder()) {
							$('#nocharge').prop('checked', true).trigger('change');
						}
					}

					//Build menu controls

					//Credit card expiration year

					$('#creditcardexpirationyear').append("<option value=''>[+]</option>");
					var currentYear = (new Date()).getFullYear();
					var endYear = currentYear + 11;

					for (var i  = currentYear; i < endYear; i++) {
						$('#creditcardexpirationyear').append($("<option value=''></option>")
								.attr("value", i)
								.text(i));
					}

					//Country list
					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getcountryisolist&sid=' + sid,
						cache: false,
						success: function (data) {
							countryList = data;
							$(document).trigger('order.country_list_loaded');
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
						error:function(error){
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

					//Reason code list
					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getreasoncodelist&sid=' + sid,
						cache: false,
						success: function (data) {
							reasonCodeList = data;
							buildReasonLists('shippingreasoncode', 4, null);
							defReasonCodeListLoaded.resolve();
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
					
					// PaperCheck Configuration List
					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getconfiguration&configkey=CUSTOM_CFG_PAPER_CHECK_DENY_LIST&sid=' + sid,
						cache: false,
						success: function (data) {
							paperCheckDenyList = data.ProfileIDs. split(',');
						},
						error: function (error) {
							alert(error);
						},
						dataType: "json"
					});

					// EVENT HANDLER for Charge/No Charge order type
					$('#nochargeorder').change(function () {
						var nochargeval = parseInt(getSelectedValue('nochargeorder'));
						var checkNoCharge = isNaN(nochargeval) ? null : nochargeval;
						
						if(isNaN(nochargeval))
							$("#continueToShippingButton").css("display","none");
						else
							$("#continueToShippingButton").css("display","");
						
						if (checkNoCharge == 1)
						{
							$('#paymentmethod option[value="' + PAY_NO_CHARGE_ID + '"]').prop('selected', true);
							$('#paymentmethod').prop('disabled', true);
						} 
						else
						{
							$('#paymentmethod option[value=""]').prop('selected', true);
							$('#paymentmethod').prop('disabled', false);
						}
						
						var nochargeval = parseInt(getSelectedValue('nochargeorder'));
						var checkNoCharge = isNaN(nochargeval) ? null : nochargeval;

						WorkspaceRecord.updateField('BUS$OrderHeader.NoChargeOrder', checkNoCharge); 
						orderHeader.noCharge = checkNoCharge;
					});
					
					// EVENT HANDLER for Order Brand when changed on the UI
					$('#orderbrand').change(function () {
						var orderbrandval = parseInt(getSelectedValue('orderbrand'));
						var orderbrand = isNaN(orderbrandval) ? null : orderbrandval;
						WorkspaceRecord.updateField('BUS$OrderHeader.OrderBrand', orderbrand); 
						orderHeader.orderBrand = orderbrand;
					});
					
					$('#nochargereason').change(function () {
						var ordernochargereasonval = parseInt(getSelectedValue('nochargereason'));
						var ordernochargereason = isNaN(ordernochargereasonval) ? null : ordernochargereasonval;
						WorkspaceRecord.updateField('BUS$OrderHeader.NoChargeReason', ordernochargereason); 
						orderHeader.orderNoChargeReason = ordernochargereason;
					});

					// EVENT HANDLER for Ship Method
					$('#shippingmethod').change(function (evt,isInit) {
						var shipMethodCurrentValue = getSelectedValue('shippingmethod');
						var shipMethodText = getSelectedText('shippingmethod');
						var noChargeCheckbox = $("#nocharge");
						var defaultPrice = $("#adjustedprice").val()
						var adjustedPrice = $("#unitprice").val();
						
						// ShipMethodValue is set to empty string if not set, so we need to pass in 0 as the ID in this scenario
						if(shipMethodText == '[+]')
							shipMethodCurrentValue = 0;
						
						WorkspaceRecord.updateField('BUS$OrderHeader.ShipMethod', shipMethodCurrentValue); 
						orderHeader.shippingMethod = shipMethodCurrentValue;
						
						setTimeout(function () {
							if(!(isInit) && orderHasLines() == false)
								$('#freeshipping').prop("checked", false);
						}, 500);
						
						setShippingCost();

						//We will need to re-check the physical address requirement if the shippimg method changes
						setRequiredPhysicalAddress(shipMethodCurrentValue);                

						//Check if serial number and model number are required for the selected shipping option
						if ($('#shippingmethod').data('serialnumberrequired') == true) {

							//Toggle only if no other validation checks are active
							if (noChargeCheckbox.is(':checked') == false && defaultPrice == adjustedPrice) {
								toggleModelAndSerialNumber(true);
							}
						} else {
							if (noChargeCheckbox.is(':checked') == false && defaultPrice == adjustedPrice) {
								toggleModelAndSerialNumber(false);
							}
						}
						
						SetOrderTotals();
						toggleShippingReasonByCost();
						
						if (!isFreeShipping()){
							// ONLY trigger this event if we are on the Shipping/Payment/Checkout page
							if($('#continueToShippingButton:visible').length == 0 && !pageLocked)
								$('#btnShipping').trigger('click');
						}
					});


					$('#nochargereasoncode').change(function () {
						//Check if serial number and model number are required for the selected shipping option
						var $selectedOption = $('#nochargereasoncode').find(":selected");

						if ($selectedOption.data('requiresserialnumber') == true)
							toggleSerialNumber(true);
						else
							toggleSerialNumber(false);

						if ($selectedOption.data('requiresmodelnumber') == true)
							toggleModelNumber(true);
						else
							toggleModelNumber(false);
					});

					//Payment Methods
					$('#paymentmethod').change(function (e,showModal) {
						
						var selectedPaymentMethod = $('#paymentmethod').find(':selected');

						var payMethodCurrentValue = selectedPaymentMethod.val();
						payMethodCurrentValue = parseInt(payMethodCurrentValue) || -1;
						var payMethodText = selectedPaymentMethod.text();
						
						setTimeout(function () {
							WorkspaceRecord.updateField('BUS$OrderHeader.PaymentType', payMethodCurrentValue); 
						},1000);

						$('#launchcybersource').addClass('hidden');
						$('#setpapercheckdetails').addClass('hidden');

						//Display the credit card payment dialog
						if(payMethodText == 'Credit Card') {
							$('#cybersourcemodal').modal('show');
							$("#loadingoverlaycybersource").fadeIn();
							
							if(!cybersourceFlex)
								InitializeCyberSource();
							
							$("#loadingoverlaycybersource").fadeOut();
							$('#launchcybersource').removeClass('hidden');
						} 

						if (payMethodText == 'Paper Check') {

							$('#setpapercheckdetails').removeClass('hidden');

							setPapaerCheckDetails();

							if (hasValue(paperCheckDetails.CheckNumber) == true) {
								$('#setpapercheckdetails').removeClass('red');
								$('#setpapercheckdetails').addClass('green');
							} else {
								$('#setpapercheckdetails').removeClass('green');
								$('#setpapercheckdetails').addClass('red');
							}
						}
					});

					//Set paper check details button
					$('#setpapercheckdetails').click(function () {
						setPapaerCheckDetails();
						$('#papercheckpaymentmodal').modal('show');
					});
					
					$("#btnPaymetricSubmit").click(function () {
						var iframe = document.getElementsByName('dieCommFrame');  
						if (iframe) {  
							$XIFrame.submit({  
								iFrameId: 'dieCommFrame',  
								targetUrl: paymetricData.ModalURL,
								onSuccess: function (msg) {  
									GetResponsePacket();
								},
								onError: function (msg) {
									$('#spanPaymetricError').html('Error Initiating Credit Card Save: ' + msg);
									$('#spanPaymetricError').removeClass('hidden');
									$("#btnPaymetricSubmit").addClass('hidden');
								}
							});
						}
					});
					
					/***** CYBERSOURCE PAYMENT PROCESSING *****/
					function InitializeCyberSource() {
						let cybersourceInitPayload = {
							Action: "CaptureContext",
							Incident: incident.id,
							Session: sid,
							Country: billingAddress.CountryCodeId
						};
						
						$.ajax({
							type: "POST",
							async: false,
							url: CUSTOM_SCRIPTS_PATH + 'cybersourceintegration.php',
							data: JSON.stringify(cybersourceInitPayload),
							dataType: "json",
							success: function(response) {
								if(response.SUCCESS){
									
									// LOAD the returned CyberSource Microform library dynamically from the returned Capture Context (PROD vs. TEST)
									let captureContext = response.Message;
									let contextPieces = captureContext.split('.');
									let decodedContext = JSON.parse(atob(contextPieces[1]));
									let microformLibrary = decodedContext.ctx[0].data.clientLibrary;
									
									let scriptElement = document.createElement("script");
									scriptElement.setAttribute("src", microformLibrary);
									scriptElement.setAttribute("type", "text/javascript");
									document.body.appendChild(scriptElement);
									
									scriptElement. addEventListener("load", () => {
										cybersourceFlex = new Flex(captureContext);
										cybersourceMicroForm = cybersourceFlex.microform();
										
										cybersourceNumber = cybersourceMicroForm.createField('number', { placeholder: 'Enter card number' });
										cybersourceNumber.load('#number-container');
										cybersourceNumber.on('change', function(data) {
											if(data.card.length == 1) 
												$('#cardType-label').text("Card Type: " + data.card[0].brandedName);
											else
												$('#cardType-label').text("Card Type: -");
										});
										
										cybersourceCVV = cybersourceMicroForm.createField('securityCode', { placeholder: '•••' });
										cybersourceCVV.load('#securityCode-container');
									});
								}
							},
							error: function (error) {
								console.log("CYBERSOURCE INITIALIZATION ERROR");
							}
						});
					}
					
					$('#launchcybersource').click(function () {
						$('#cybersourcemodal').modal('show');
						$("#loadingoverlaycybersource").fadeIn();
						if(!cybersourceFlex)
							InitializeCyberSource();
						
						$("#loadingoverlaycybersource").fadeOut();
					});
					
					$("#btnCyberSourceSubmit").click(function () {
						$('#numberError').addClass('hidden');
						$('#securityCodeError').addClass('hidden');
						let options = {    
							expirationMonth: getSelectedValue('expMonth'),
							expirationYear: getSelectedValue('expYear')
						};      
		  
						cybersourceMicroForm.createToken(options, function(error, token) {
							if(error) {
								error.details.forEach((nextError) => {
									let errorLocation = nextError.location + "Error";
									$('#' + errorLocation).removeClass('hidden');
								});
							} 
							else {
								cybersourceToken = token;
								console.log("BRENDA TOKEN: " + cybersourceToken);
								$('#cybersourcemodal').modal('hide');
							}
						});
					});
					
					/***** END CYBERSOURCE *****/
					
					
					
					function RetrievePaymetricConfig(){
						$.ajax({
							type: "GET",
							async: false,
							url: AJAX_ENDPOINT + '?action=getconfiguration&configkey=CUSTOM_CFG_PAYMETRIC_INTEGRATION&sid=' + sid,
							cache: false,
							success: function (data) {
								if(data.DEV_ENABLED == 1) {
									paymetricData.MerchantGUID = data.DEV.MerchantGUID;
									paymetricData.BaseURL = data.DEV.BaseURL;
								} else {
									paymetricData.MerchantGUID = data.PROD.MerchantGUID;
									paymetricData.BaseURL = data.PROD.BaseURL;
								}
								
							},
							error: function (error) {
								alert(error);
							},
							dataType: "json"
						});
					}
					
					function GenerateAccessToken(){
						var encodeBillToName = btoa(billingAddress.Name);
					
						$.ajax({
							type: "POST",
							async: false,
							url: AJAX_ENDPOINT_PAYMETRIC + '?action=generateaccesstoken&billtoname=' + encodeBillToName + '&sid=' + sid,
							success: function (data) {
								if(data.RESULT){
									var json = parseXmlToJson(data.RESULT);
									if(json.AccessToken){
										$('#spanPaymetricError').html('');
										$("#spanPaymetricError").addClass('hidden');
										$('#btnPaymetricSubmit').removeClass('hidden');
										paymetricData.AccessToken = json.AccessToken;
										paymetricData.ModalURL = paymetricData.BaseURL + "/View/Iframe/" + paymetricData.MerchantGUID + "/" + json.AccessToken + "/True";
										$('#dieCommFrame').attr("src", paymetricData.ModalURL);
									} else {
										if(json.Message){
											$('#spanPaymetricError').html('Error Retrieving Access Token: ' + json.Message);
										} else {
											$('#spanPaymetricError').html('Unknown error retrieving access token');
										}
										
										$('#spanPaymetricError').removeClass('hidden');
										$("#btnPaymetricSubmit").addClass('hidden');
									}
								} else {
									$('#spanPaymetricError').html('Error calling endpoint to retrieve access token');
									$('#spanPaymetricError').removeClass('hidden');
									$("#btnPaymetricSubmit").addClass('hidden');
								}
							},
							error: function (error) {
								if(error){
									$('#spanPaymetricError').html('Error Retrieving Access Token: ' + error + ' (error function)');
								} else {
									$('#spanPaymetricError').html('General error retrieving access token (error function)');
								}
								
								$('#spanPaymetricError').removeClass('hidden');
								$("#btnPaymetricSubmit").addClass('hidden');
							}
						});
							
						$("#loadingoverlay").fadeOut();
					}
					
					function GetResponsePacket(){
						$.ajax({
							type: "POST",
							url: AJAX_ENDPOINT_PAYMETRIC + '?action=getresponsepacket&accesstoken=' + paymetricData.AccessToken + '&sid=' + sid,
							success: function (data) {
								var testccdata = parseCCXMLToJSON(data.RESULT);
								
								if(testccdata['CardNumber']){
									$('#spanPaymetricError').html('');
									$("#spanPaymetricError").addClass('hidden');
									$("#btnPaymetricSubmit").removeClass('hidden');
									
									//Credit card info is valid. Set the
									ccDetails.AccountNumber = testccdata['CardNumber'].Value;
									ccDetails.Name = testccdata['CardHolderName'].Value;
									ccDetails.Amount = getOrderAndShippingTotal().order;
									ccDetails.ExpMonth = testccdata['ExpirationMonth'].Value;
									ccDetails.ExpYear = testccdata['ExpirationYear'].Value;
									ccDetails.CardCode = testccdata['CardType'].Value.substring(0, 1).toUpperCase();
									ccDetails.CVC = testccdata['CardSecurityCode'].Value;
									ccDetails.TransactionDate = new Date(Date.now()).toISOString();
									$('#paymetricmodal').modal('hide');
								} else {
									$('#spanPaymetricError').html('Error Getting Response Packet: ' + data.RESULT);
									$('#spanPaymetricError').removeClass('hidden');
									$("#btnPaymetricSubmit").addClass('hidden');
								}
							},
							error: function (error) {
								$('#spanPaymetricError').html('Error Retrieving Response Packet: ' + error);
								$('#spanPaymetricError').removeClass('hidden');
								$("#btnPaymetricSubmit").addClass('hidden');
							}
						});
					}
					
					function parseXmlToJson(xml) {
						const json = {};
						for (const res of xml.matchAll(/(?:<(\w*)(?:\s[^>]*)*>)((?:(?!<\1).)*)(?:<\/\1>)|<(\w*)(?:\s*)*\/>/gm)) {
							const key = res[1] || res[3];
							const value = res[2] && parseXmlToJson(res[2]);
							json[key] = ((value && Object.keys(value).length) ? value : res[2]) || null;

						}
						return json;
					}
					
					function parseCCXMLToJSON(xml){
						const json = {};
						for (const reslevel1 of xml.matchAll(/(?:<(\w*)(?:\s[^>]*)*>)((?:(?!<\1).)*)(?:<\/\1>)|<(\w*)(?:\s*)*\/>/gm)) {
							for (const reslevel2 of reslevel1[2].matchAll(/(?:<(\w*)(?:\s[^>]*)*>)((?:(?!<\1).)*)(?:<\/\1>)|<(\w*)(?:\s*)*\/>/gm)) {
								for (const reslevel3 of reslevel2[2].matchAll(/(?:<(\w*)(?:\s[^>]*)*>)((?:(?!<\1).)*)(?:<\/\1>)|<(\w*)(?:\s*)*\/>/gm)) {
									var tempval = parseXmlToJson(reslevel3[2]);
									if(Object.keys(tempval).length == 3){
										json[tempval.Name.replace(/\s/g, '')] = tempval;
									}
								}
							}
						}
						return json;
					}
					
					//No charge check changed
					$("#nocharge").change(function () {

						var adjustedPrice = $("#adjustedprice").val();
						var defaultPrice = $("#unitprice").val();

						if ($(this).is(':checked') == true) {

							buildReasonLists('nochargereasoncode', 1, null);

							//toggleModelAndSerialNumber(true);
							//Set the adjusted price to 0
							$("#adjustedprice").val("0.00").trigger('focusout');
							$('#adjustedprice').attr('disabled', 'disabled');

						}
						else {

							$('#adjustedprice').removeAttr('disabled');
							$("#adjustedprice").val(defaultPrice).trigger('focusout');

							//toggleModelAndSerialNumber(false);

							if (window.orderLineValidator)
								window.orderLineValidator.resetForm();
						}

						togglePriceAdjustmentReasonCode();

					});

					//Free shipping check changed
					$("#freeshipping").change(function () {
						//Display the ship method reason code
						if(isFreeShipping())
						{
							$('#shippingreasoncolumn').removeClass('hidden');
							$('#shippingreasoncode').attr('required', 'required');

							//Set the shipping cost to 0.00
							$("#shippingcost")
								.val("0.00")
								.attr("disabled","disabled");
						}
						else 
						{
							$('#shippingcost').removeAttr("disabled");
							$('#shippingreasoncolumn').addClass('hidden');
							$('#shippingreasoncode').removeAttr('required');
							$('#shippingreasoncode option[value=""]').prop('selected', true);
							$('#shippingmethod').trigger("change");
						}
						
						$('#shippingcost').trigger("focusout");
						SetOrderTotals();
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

					//Check that shipping cost always has a value of 0 or greater.
					$("#shippingcost").focusout(function () {
						if ($(this).val() == "")
							$(this).val("0.00");

						//See if the shipping value was changed manually (shipping cost override)
						if ($(this).data('autoval') != $(this).val()) {

							hasManualShippingOverride = true;

							$('#shippingreasoncolumn').removeClass('hidden');
							$('#shippingreasoncode').attr('required', 'required');

						} else {

							if (!isFreeShipping()) {
								$("#shippingcost").removeAttr("disabled");
								$('#shippingreasoncolumn').addClass('hidden');
								$('#shippingreasoncode').removeAttr('required');
								$('#shippingreasoncode option[value=""]').prop('selected', true);
								$('#shippingmethod').trigger("change");
							}
						}
						
						// ONLY trigger this event if we are on the Shipping/Payment/Checkout page
						if($('#continueToShippingButton:visible').length == 0)
							$('#btnShipping').trigger('click');
					});

					//Add order button event handler
					$("#btnAddLine").click(function () {
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
									partnumberexists: true
								},
								quantity: {
									required: true,
									min: 1,
									max: {
										param: 9,
										depends: function(element){
											return ($("#nocharge").is(":checked") || $("#adjustedprice").val() == 0 || $("#adjustedprice").val() == 0.00);
										}
									}
								},
								qtyActual: {
									min: {
										param: 10,
										depends: function(element){
											return (!($("#nocharge").is(":checked")) && $("#adjustedprice").val() != 0 && $("#adjustedprice").val() != 0.00 && $("#partnumber").val().substring(0,4) !== "DPFU");
										}
									}
								}
							},
							messages: {
								partnumber: {
									required:"Select or enter a part number"
								},
								quantity: {
									required: "Qty required",
									min:"1 or greater",
									max:"9 or less for no charge line items"
								},
								qtyActual: {
									min:"Item is out of stock <br/><a href='https://bissellcdn.blob.core.windows.net/cdn-storage-container/Procedure/OOS%20Report.pdf' target='_blank'>Click Here for OOS Report</a>"
								},
								qtyAvailable: {
									min:"Item is out of stock"
								},
								nochargereasoncode: {
									required:"Reason required"
								},
								shippingreasoncode: {
									required:"Reason required"
								},
								serialnumber: {
									required: "Serial # required"
								},
								modelnumber: {
									required:"Model # required"
								},
								nochargeorder: {
									required:"Required"
								},
								orderbrand: {
									required:"Required"
								}
							}
						});
						
						window.orderLineValidator.form();

						if (window.orderLineValidator.valid() == false) {
							if($("#qtyActual").hasClass('validation-error'))
								$("#qtyAvailable").addClass('validation-error');
							
							return;
						}

						var btn = $(this);

						btn.button();

						var orderLine = {};
						var rows = window.OrderTable.rows().data();
						var price = $("#adjustedprice").val() || $("#unitprice").val();

						lineNumber = lineNumber + 1;
						
						saveOrderLine();

						btn.button('reset');

					});

					// Continue with Shipping and Payment button
					$('#btnShipping').click(function (e) {
						$("#divSubmitError").css("display","none");

						let lineItemData = window.OrderTable.rows().data();
						
						if(lineItemData.length == 0) {
							$("#divSubmitError").css("display","");
							$("#divSubmitError").html('At least one line item must be added to the order to proceed to checkout!');
							return;
						}
						
						window.OrderTable.column(0).visible(false);
						
						$("#continueToShippingButton").css("display","none");
						$("#orderlineform").css("display", "none");
						$("#ordersubmitform").css("display", "");
						$("#modifyOrderButton").css("display","");
						
						$('#ordersummary-tax').html("Calculating...");
						$('#ordersummary-tax2').html("Calculating...");
						
						// Submit tax calculation request
						let lineItems = [];
						for(let lineIndex = 0; lineIndex < lineItemData.length; lineIndex++) {
							let nextPart = {
								sku: lineItemData[lineIndex].part,
								listPrice: lineItemData[lineIndex].adjustedprice,
								qty: lineItemData[lineIndex].qty,
								total: (lineItemData[lineIndex].adjustedprice * lineItemData[lineIndex].qty)
								
							};
							lineItems.push(nextPart);
						}
						
						let salesTaxPayload = {
							Incident: incident.id,
							lineItems: lineItems,
							Session: sid,
							BillName: billingAddress.Name,
							BillStreet: billingAddress.Address1,
							BillStreet2: billingAddress.Address2,
							BillCity: billingAddress.City,
							BillState: billingAddress.StateAbbr,
							BillPostal: billingAddress.PostalCode,
							BillCountry:  billingAddress.CountryCode,
							ShipName: shippingAddress.Name,
							ShipStreet: shippingAddress.Address1,
							ShipStreet2: shippingAddress.Address2,
							ShipCity: shippingAddress.City,
							ShipState: shippingAddress.StateAbbr,
							ShipPostal: shippingAddress.PostalCode,
							ShipCountry: shippingAddress.CountryCode,
							ShipAmount: getOrderAndShippingTotal().shipping,
							TaxExempt: contact.taxStatus
						};	
						
						$.ajax({
							type: "POST",
							url: CUSTOM_SCRIPTS_PATH + 'vertexsalestax.php',
							data: JSON.stringify(salesTaxPayload),
							dataType: "json",
							success: function(taxResponse) {
								if(taxResponse.SUCCESS) {
									if(taxResponse.RecycleFee != 0.00)
										$('#productCareEcoFee').css("display", "");
									else
										$('#productCareEcoFee').css("display", "none");
									$('#ordersummary-tax').html(taxResponse.Tax);
									$('#ordersummary-tax2').html(taxResponse.SecondaryTax);
									$('#ordersummary-recyclefee').html(taxResponse.RecycleFee);
									SetOrderTotals();
								}
								else {
									console.log("TAX RESULT FAILED SHOW ERROR");
								}
							}
						});
					});
					
					// Return agent to main order UI
					$('#btnModifyOrder').click(function (e) {
						window.OrderTable.column(0).visible(true);
						$("#modifyOrderButton").css("display", "none");
						$("#ordersubmitform").css("display", "none");
						$("#orderlineform").css("display", "");
						$("#continueToShippingButton").css("display","");
					});
					
					//Submit order button
					$('#submitorder').click(function (e) {
						$(this).prop('disabled', true);
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
									rules:{
										shippingmethod: {
											required: true,
											physicalshippingaddress:true
										}
									},
									messages: {
										shippingmethod: {
											required: "Select a shipping method"
										},
										paymentmethod: {
											required:"Select a payment method"
										},
										shippingreasoncode: {
											required:"Select a shipping reason code"
										}
									}
								});

								window.orderSubmitFormValidator.form();

								if (window.orderSubmitFormValidator.valid() === false) {
									$('#submitorder').prop('disabled', false);
									$.unblockUI();
									return;
								}

								var orderValidationChecks = isOrderValid();
								var continueWithOrder = true;

								if (orderValidationChecks.orderValid == false) {
									$('#submitorder').prop('disabled', false);
									$.unblockUI();
									alert(orderValidationChecks.message);
									return;
								}
								if (orderValidationChecks.addressValid == false) {
									$('#submitorder').prop('disabled', false);
									$.unblockUI();
									alert(orderValidationChecks.addressMessage);
									return;
								}
								if (continueWithOrder) {
									
									var orderSubmitObj = getNewOrderSubmitObject();
									orderSubmitObj.order.ContactId = contact.contactId;
									orderSubmitObj.order.OnyxIndividualId = consumerId;
									orderSubmitObj.order.BillingAddress = getAddressForOrderProcessing('BillTo');
									orderSubmitObj.order.ShippingAddress = getAddressForOrderProcessing('ShipTo');
									orderSubmitObj.order.LineItems = getLineItemsForOrderProcessing();
									orderSubmitObj.order.SubTotal = orderTotals.subtotal;
									orderSubmitObj.order.OrderTotal = orderTotals.grandtotal;
									orderSubmitObj.order.OrderHeaderId = orderHeaderId;
									orderSubmitObj.order.IncidentId = incident.id;
									orderSubmitObj.order.ShipCode = getSelectedDataAttribute('shippingmethod', 'text');
									orderSubmitObj.order.SubmitBrandConfig = getSelectedDataAttribute('orderbrand', 'bwssubmitorderconfig');
							
									var paymentMethod = getSelectedText('paymentmethod').toLowerCase();

									if (paymentMethod != 'credit card' || paymentMethod != 'paper check') {
										delete orderSubmitObj.order.CheckPayment;
										delete orderSubmitObj.order.Payment;
									}

									if (paymentMethod == 'credit card') {
										orderSubmitObj.order.Payment = getCreditCardInfoForOrderProcessing();
										delete orderSubmitObj.order.CheckPayment;
									}

									if (paymentMethod == 'paper check') {
										orderSubmitObj.order.CheckPayment = getPaperCheckInfoForOrderProcessing();
										delete orderSubmitObj.order.Payment;
									}
									
									orderSubmitObj.order.SalesTax = orderTotals.salestax;
									orderSubmitObj.order.OrderDate = new Date(Date.now()).toISOString().split('T')[0].replaceAll("-","");
									orderSubmitObj.order.ShipAmount = getOrderAndShippingTotal().shipping;

									orderSubmitObj.order.CRMPurchaseOrderID = orderHeaderId;
									orderSubmitObj.order.IncidentRefNo = incident.refno;
									var salesChannelOverride = getSelectedDataAttribute('orderbrand', 'overridesaleschannel');
									if (salesChannelOverride){
										var salesChannelValue = getSelectedDataAttribute('orderbrand', 'saleschannel');
										orderSubmitObj.order.Channel = salesChannelValue;
									} 
									else
										orderSubmitObj.order.Channel = getChannelNameFromId(orderHeader.orderChannel);
									
									orderSubmitObj.noCharge = orderHeader.noCharge;
									
									// Submit the order to the endpoint for processing
									submitOrderForProcessing(orderSubmitObj, orderSubmitSuccessCallback);
								}
							}
						});
					});

					$('#cancelpaymetric').click(function () {
						paymetricData = {};
						ccDetails = {};
					});

					//Submit check
					$('#submitpapercheck').click(function () {

						window.paperCheckValidator = $("#papercheckform").validate({
							errorClass: 'validation-error',
							errorPlacement: function ($error, $element) {
								var id = $element.attr("id");

								$("#error_" + id).append($error);
							},
							rules: {
								paperchecknumber: {
									required: {
										depends: function () {
											$(this).val($.trim($(this).val()));
											return true;
										}
									}
								},
								papercheckamount: {
									required: true
								}
							},
							messages: {
								paperchecknumber: {
									required: "Check # is required"
								},
								papercheckamount: {
									required: "Check amount is required"
								}
							}
						});

						window.paperCheckValidator.form();

						if (window.paperCheckValidator.valid() === false)
							return;

						paperCheckDetails.CheckNumber = $('#paperchecknumber').val();
						paperCheckDetails.Amount = $('#papercheckamount').val();
						setStorageObject('paperCheckDetails', paperCheckDetails);
						
						WorkspaceRecord.updateField('BUS$OrderHeader.PaymentCheckNumber', paperCheckDetails.CheckNumber);
						orderHeader.paymentCheckNumber = paperCheckDetails.CheckNumber;

						//Change the button color and hide the window
						$('#setpapercheckdetails').removeClass('red');
						$('#setpapercheckdetails').addClass('green');
						$('#papercheckpaymentmodal').modal('hide');
					});

					$('#cancelpapercheck').click(function () {

						if (window.paperCheckValidator)
							window.paperCheckValidator.resetForm();
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
						$.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: {border:'none',backgroundColor:'transparent'}});
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
					if (pageLocked == false)
					{
						setOrderHeaderValuesBeforeSave();
						setPaperCheckStatus();
					}
				}
				
				// IF the Incident.PrimaryContact field changes, update the BUS$OrderAddress Billing/Shipping address for the new Contact
				var onContactChanged = function()
				{
					WorkspaceRecord.getFieldValues([
					'Contact.Email.Addr',
					'Contact.Name.First',
					'Contact.Name.Last',
					'Contact.CId',
					'Contact.BI$tax_exempt',
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
						contact.taxStatus = fieldDetails.getField('Contact.BI$tax_exempt').getValue();
						
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
							if(contact.PostalCode.length == 6)
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
						case 'BUS$OrderHeader.BillToCustomerName':
							billingAddress.Name = param.event.value;
							let names = billingAddress.Name.split(' ');
							$("#cardholderFirstName").val(names[0]);
							$("#cardholderLastName").val(names[1]);
							break;
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
							billingAddress.CountryCode = getCountryISONameById(billingAddress.CountryCodeId);
							break;
						case 'BUS$OrderHeader.BillToState':
							billingAddress.StateCodeId = param.event.value;
							WorkspaceRecord.getFieldValues(['BUS$OrderHeader.BillToState']).then(function(fieldDetails) {
								billingAddress.StateCode = fieldDetails.getField('BUS$OrderHeader.BillToState').getLabel();
								billingAddress.StateAbbr = getProvinceAbbreviation(billingAddress.CountryCodeId, billingAddress.StateCode);
							});
							break;
						case 'BUS$OrderHeader.BillToPhone':
							billingAddress.Phone = param.event.value;
							break;
						case 'BUS$OrderHeader.ShipToCustomerName': 
							shippingAddress.Name = param.event.value;
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
							shippingAddress.CountryCode = getCountryISONameById(shippingAddress.CountryCodeId);
							break;
						case 'BUS$OrderHeader.ShipToState':
							shippingAddress.StateCodeId = param.event.value;
							WorkspaceRecord.getFieldValues(['BUS$OrderHeader.ShipToState']).then(function(fieldDetails) {
								shippingAddress.StateCode = fieldDetails.getField('BUS$OrderHeader.ShipToState').getLabel();
								shippingAddress.StateAbbr = getProvinceAbbreviation(shippingAddress.CountryCodeId, shippingAddress.StateCode);
							});
							break;
						case 'BUS$OrderHeader.ShipToPhone':
							shippingAddress.Phone = param.event.value;
							break;
						default:
							break;
					}
				}
				
				WorkspaceRecord.addRecordSavingListener(onBeforeSave);
				
				WorkspaceRecord.addFieldValueListener('Incident.CId', onContactChanged);
				
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToCustomerName', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToAddress1', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToAddress2', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToCity', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToPostalCode', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToCountry', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToState', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.BillToPhone', onAddressChanged);
				
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToCustomerName', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToAddress1', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToAddress2', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToCity', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToPostalCode', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToCountry', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToState', onAddressChanged);
				WorkspaceRecord.addFieldValueListener('BUS$OrderHeader.ShipToPhone', onAddressChanged);
				
				// SET workspace values on initialization of workspace, only runs once workspace is done loading
				function setWorkspaceValues(skipOHUpdate){
					if (shippingAddress.CountryCodeId == UK_COUNTRY_ID && hasValue(expectedDeliveryDate)) {
						$('#expecteddeliverydate').datepicker("setDate",new Date(expectedDeliveryDate * 1000));
					}

					$('#ordernumber').val(orderNumber);

					if (!skipOHUpdate) {
						$('#nochargeorder option[value="' + orderHeader.noCharge + '"]').attr('selected', true).trigger('change');
						
						if (orderHeader.shippingReason > 0 || orderHeader.freeShipping == 1)
						{
							$('#freeshipping').prop('checked', true);
							$('#shippingcost').prop('disabled','disabled');
							$('#shippingreasoncode option[value="' + orderHeader.shippingReason + '"]').attr('selected', true);
							$('#shippingreasoncolumn').removeClass('hidden');
						}
						else if (orderHeader.freeShipping == 1)
						{
							$('#freeshipping').prop('checked', true);
							$('#shippingcost').prop('disabled','disabled');
						}
						else
						{
							$('#shippingcost').removeAttr("disabled");
							$('#shippingreasoncolumn').addClass('hidden');
							$('#shippingreasoncode').removeAttr('required');
							$('#shippingreasoncode option[value=""]').prop('selected', true);
							$('#freeshipping').prop("checked", false);
						}
						
						if (hasValue(orderHeader.shippingTotal)) {
							$('#shippingcost').val(orderHeader.shippingTotal);
						}
						$('#paymentmethod option[value="' + orderHeader.paymentMethod + '"]').attr('selected', true).trigger('change', false);							
					}
					toggleExpectedDeliveryDate();
				}

				// Set the loyalty status for the selected contact
				function setLoyaltyStatus() {
					$.ajax({
						type: "GET",
						url: LOYALTY_STATUS_ENDPOINT + '?sid=' + sid,
						data: { Email: contact.emailAddress },
						cache:false,
						success: function (data) {
							if (data.ResponseCode != null && data.ResponseCode.toLowerCase() == "ok") {
								var loyaltyInfo = data.LoyaltyRank + " (" + data.TotalPoints + ")";
								$('#loyaltystatus').val(loyaltyInfo);
							}
						},
						dataType: "json",
						error: function (request, status, error) {
							alert(error);
						}
					});
				}
				

				function setOrderHeaderValuesBeforeSave()
				{
					var shippingReason = 0;
					var expectedDeliveryDate = hasValue($('#expecteddeliverydate').val()) ? new Date($('#expecteddeliverydate').val()) : null;

					if (hasValue(getSelectedValue('shippingreasoncode')))
						shippingReason = parseInt(getSelectedValue('shippingreasoncode'));
					
					var freeShipping = isFreeShipping() == true ? 1 : 0;
					WorkspaceRecord.updateField('BUS$OrderHeader.FreeShipping', freeShipping); 
					WorkspaceRecord.updateField('BUS$OrderHeader.Contact', contact.contactId); 
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipMethodReason', shippingReason); 

					if (expectedDeliveryDate > 0) {
						var dte = expectedDeliveryDate.getTime() / 1000;
						WorkspaceRecord.updateField('BUS$OrderHeader.ExpectedDeliveryDate', dte); 
					}
				}

				/**
				 * Get the next line number for use as the Line enumerator for the grid rows
				 */
				function getNextLineNumber() {

					var rows = window.OrderTable.rows().data();
					var rowCount = rows.length;
					return rowCount + 1;
				}
				var actualShippingCost; // Declared a Global Variable to save the Shipping Cost.
				function getOrderAndShippingTotal() {
					var totals = {};
					if(typeof window.OrderTable !== 'undefined')
					{
						var rows = window.OrderTable.rows().data() || [];
						var shipTotal = $('#shippingcost').val() || 0.00;
						if(shipTotal != 0)
						{
							actualShippingCost = shipTotal;
						}
						var ordTotal = 0.00; //Order total includes shipping
						var subTotal = 0.00;
						var totalForShippingSelection = 0.00; //Determine the actual cost of the items even if a item is being offered for free

						if (rows.length > 0) {

							for (var i  = 0; i < rows.length; i++) {
								var row = rows[i];
								if (row.lineid != -2 && row.lineid != -1) {

									ordTotal = ordTotal + ((parseFloat(row.adjustedprice) || 0) * parseInt(row.qty));

									if (row.nocharge) {
										totalForShippingSelection = totalForShippingSelection + (parseFloat(row.unitsellingprice) * parseInt(row.qty));
									} else {
										totalForShippingSelection = totalForShippingSelection + ((parseFloat(row.adjustedprice) || 0) * parseInt(row.qty));
									}
								}
							}

							subTotal = ordTotal;
							ordTotal = ordTotal + parseFloat(shipTotal);
						}

						totals.shipping = accounting.toFixed(shipTotal,2);
						totals.order = accounting.toFixed(ordTotal, 2);
						totals.subtotal = accounting.toFixed(subTotal, 2);
						totals.totalforshippingselection = accounting.toFixed(totalForShippingSelection,2);
					}
					return totals;
				}

				function SetHazardMessage() {
					if (orderHasAerosol()) {
						$('#error_hazardousitems').html('Order contains an item that cannot be shipped by air');
					} else {
						$('#error_hazardousitems').html('');
					}
				}

				/**
				 * Set totals for the order
				 */
				function SetOrderTotals() {
					if (pageLocked) {
						$('#ordersummary-subtotal').html(accounting.toFixed(orderHeader.orderSubTotal, 2));
						$('#ordersummary-tax').html(accounting.toFixed(orderHeader.orderSalesTaxTotal, 2));
						$('#ordersummary-tax2').html(accounting.toFixed(orderHeader.orderSecondarySalesTaxTotal, 2));
						$('#ordersummary-recyclefee').html(accounting.toFixed(orderHeader.orderProductEcoCareFeeTotal, 2));
						// BUG 64472
						if(orderHeader.orderProductEcoCareFeeTotal != 0.00)
							$('#productCareEcoFee').css("display", "");
						$('#ordersummary-shipping').html(accounting.toFixed(orderHeader.shippingTotal, 2));
						$('#shippingcost').val(accounting.toFixed(orderHeader.shippingTotal, 2));
						$('#ordersummary-total').html(accounting.toFixed(orderHeader.orderTotal, 2));
						return;
					}

					let primaryTax = $('#ordersummary-tax').html();
					let secondaryTax = $('#ordersummary-tax2').html();
					let productCareEcoFee = $('#ordersummary-recyclefee').html(); 
					var totals = getOrderAndShippingTotal();
					var salesTax = {primary:primaryTax, secondary:secondaryTax, recycleFee:productCareEcoFee};
					var grandTotal = "0.00";
					
					/*
					if (totals.subtotal > 0)
						salesTax = getSalesTaxForOrder();
					*/
					
					var totalTax = parseFloat(salesTax.primary) + parseFloat(salesTax.secondary) + parseFloat(salesTax.recycleFee);

					grandTotal = accounting.toFixed(parseFloat(totals.order) + totalTax, 2);

					$('#ordersummary-subtotal').html(totals.subtotal);
					$('#ordersummary-tax').html(salesTax.primary);
					$('#ordersummary-tax2').html(salesTax.secondary);
					$('#ordersummary-recyclefee').html(salesTax.recycleFee); 
					$('#ordersummary-shipping').html(totals.shipping);
					$('#ordersummary-total').html(grandTotal);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.Total', grandTotal); 
					WorkspaceRecord.updateField('BUS$OrderHeader.ShippingTotal', totals.shipping); 
					WorkspaceRecord.updateField('BUS$OrderHeader.Tax', salesTax.primary); 
					WorkspaceRecord.updateField('BUS$OrderHeader.SecondaryTax', salesTax.secondary); 
					WorkspaceRecord.updateField('BUS$OrderHeader.ProductCareEcoFee', salesTax.recycleFee);
					WorkspaceRecord.updateField('BUS$OrderHeader.SubTotal', totals.subtotal); 
					WorkspaceRecord.updateField('BUS$OrderHeader.Contact', contact.contactId); 
					
					orderTotals.subtotal = totals.subtotal;
					orderTotals.shipping = totals.shipping;
					orderTotals.salestax = accounting.toFixed(totalTax,2);
					orderTotals.grandtotal = grandTotal;
				}

				function taxIncludesShipping(taxrate, taxamnt, shipping, total) {

					var includesShipping = true;
					var subtotal = (total - shipping);

					if (shipping == 0 || accounting.toFixed((taxrate * subtotal), 2) == taxamnt)
						includesShipping = false;

					return includesShipping;
				}

				function getTaxAmntFromData() {
					var salesTax = { primary: 0.00, secondary: 0.00 };
					var totals = getOrderAndShippingTotal();

					if (salesTaxData) {

						var ptax = 0.00;
						var stax = 0.00;

						//if (taxIncludesShipping(salesTaxData.Percent, salesTaxData.Amount, totals.shipping, totals.totalforshippingselection)) {
						if (salesTaxData.TaxOnShipping) {
							ptax = parseFloat(salesTaxData.Percent * totals.order);
							if (salesTaxData.SecondaryPercent) {
								stax =  parseFloat(salesTaxData.SecondaryPercent * totals.order);
							}
						}
						else {
							ptax = parseFloat(salesTaxData.Percent * totals.subtotal);
							if (salesTaxData.SecondaryPercent) {
								stax =  parseFloat(salesTaxData.SecondaryPercent * totals.subtotal);
							}
						}

						salesTax.primary = accounting.toFixed(ptax, 2);
						salesTax.secondary = accounting.toFixed(stax, 2);
					}
					return salesTax;
				}

				function getSalesTaxForOrder()
				{
					if(contact.taxStatus > 0)
						return { primary: 0.00, secondary: 0.00 };
					
					var salesTax = { primary: 0.00, secondary: 0.00 };
					var totals = getOrderAndShippingTotal();

					if(salesTaxData) 
						return getTaxAmntFromData();
					
					var calcData = {
						sid: sid,
						BillAddress1: billingAddress.Address1,
						BillAddress2: billingAddress.Address2,
						BillCity: billingAddress.City,
						BillStateCode: billingAddress.StateAbbr,
						BillCountryCode: billingAddress.CountryCode,
						BillPostalCode: billingAddress.PostalCode,
						ShipAddress1: shippingAddress.Address1,
						ShipAddress2: shippingAddress.Address2,
						ShipCity: shippingAddress.City,
						ShipStateCode: shippingAddress.StateAbbr,
						ShipCountryCode: shippingAddress.CountryCode,
						ShipPostalCode: shippingAddress.PostalCode,            
						ShipAmount: 15, //totals.shipping,
						OrderTotal: totals.order,
						SubTotal: totals.subtotal,
						IncidentId: incident.id,
						OrderHeaderId: orderHeaderId,
						ContactId:contact.contactId
					};

					$.ajax({
						type: "POST",
						url: CUSTOM_SCRIPTS_PATH + 'calculatesalestax.php',
						async:false,
						data: calcData,
						dataType: "json"
					}).done(function (data) {                
						//Save the order line once we have the tax calculated.
						salesTaxData = { Percent: data.Percent, SecondaryPercent: data.SecondaryPercent, TaxOnShipping: false };
						var valSubtotal = accounting.toFixed(calcData.SubTotal, 2);
						var valShipAmount = accounting.toFixed(calcData.ShipAmount, 2);
						var valSalesTaxPercent = accounting.toFixed(salesTaxData.Percent, 4);
						var valTotal = parseFloat(valSubtotal) + parseFloat(valShipAmount);
						var taxOrderTotal = parseFloat(valTotal * valSalesTaxPercent).toFixed(2);
						var taxOrderSubtotal = parseFloat(valSubtotal * valSalesTaxPercent).toFixed(2);
						
						if ((taxOrderTotal == data.Amount) && (data.Amount > taxOrderSubtotal)){
							salesTaxData.TaxOnShipping = true;
						}
						salesTax = getTaxAmntFromData();
					});

					return salesTax;
				}

				function AutoSelectShippingMethod() {
					var message = '';

					requirePhysicalAddress = false;

					if (pageLocked)
						return;

					if (orderHasAerosol())
						buildShipMethodList(true);
					else
						buildShipMethodList(false, true);

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
							},500);            
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
							if (shippingIsAPOMailbox() == false && addressIsPOBox(getAddressForOrderProcessing('ShipTo')))
								requirePhysicalAddress = true;
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
								setShipMethod(SHIP_PARCEL_POST, true,null,null);
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
								setShipMethod(SHIP_FEDEX_GROUND_ID, true, null,null);
								response.shippingSet = true;
							}
						}
					}

					return response;
				}

				function setShipMethod(value, enabled, availableOptions,excludeOptions) {
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

					$('#shippingmethod option[value="' + value + '"]').attr('selected', true).trigger('change');

					if (hasValue(value) == false) {
						$('#shippingreasoncolumn').addClass('hidden');
						$('#shippingreasoncode').removeAttr('required');
						buildReasonLists('shippingreasoncode', 4, null);
					}
				}

				function isNoChargeOrder() {
					var noCharge = false;

					if (getSelectedValue('nochargeorder').toLowerCase() == '1')
						noCharge = true;

				return noCharge
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
					var armedForcesStates = ["Armed Forces America", "Armed Forces Europe", "Armed Forces Pacific", "Armed Forces Middle East", "Armed Forces Canada", "Armed Forces Africa"];
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

					var rows = window.OrderTable.rows().data();

					if (rows.length > 0) {

						for (var i = 0; i < rows.length; i++) {

							var row = rows[i];

							if (row.part == "") //Rows like shipping or order totals won't have a part number and shouldn't be included in the order lines
								continue;

							var orderLineItem = {};

							orderLineItem.Description = row.description;
							orderLineItem.Sku = row.part;
							
							var expDate = null;
							
							if (hasValue($('#expecteddeliverydate').val()))
								expDate = new Date($('#expecteddeliverydate').val()).toISOString();

							orderLineItem.EstArrivalDate = expDate ||  new Date(Date.now()).toISOString();
							orderLineItem.EstShipDate = new Date(Date.now()).toISOString();
							orderLineItem.ReturnReason = getReturnReasonText(row);
							orderLineItem.ListPrice = row.adjustedprice;
							orderLineItem.Qty = row.qty;
							orderLineItem.FreeShipping = isFreeShipping();
							orderLineItem.SerialNo = row.serialnumber;
							orderLineItem.Total = (row.qty * row.adjustedprice);
							
							orderRows.push(orderLineItem);

						}
					}

					return orderRows;

				}

				function getReturnReasonText(gridRow) {
					var reasonText = "";
					if (hasValue(gridRow.reasoncode)) {

						$.each(reasonCodeList, function (idx, reason) {
							if (reason.id == gridRow.reasoncode) {
								reasonText = reason.code || reason.name;
							}
						});        
					}

					return reasonText;
				}
				
				function getCreditCardInfoForOrderProcessing() {

					let paymentInfo = {
						Currency: billingAddress.CountryCodeId == CA_COUNTRY_ID ? "CAD" : "USD",
						Name: $("#cardholderFirstName").val() + "|" + $("#cardholderLastName").val(),
						Token: cybersourceToken
					}
					
					return paymentInfo;
				}

			function getPaperCheckInfoForOrderProcessing() {

				var tax = getTaxAmntFromData();
				var taxTotal = parseFloat(tax.primary) + parseFloat(tax.secondary);
				var orderTotal = parseFloat(getOrderAndShippingTotal().order);
				var amnt = accounting.toFixed((taxTotal + orderTotal),2);
				var paymentInfo = {
					CheckNumber: paperCheckDetails.CheckNumber,
					Amount: amnt,
					TransactionDate: new Date(Date.now()).toISOString(),
					Electronic: false
				};

				return paymentInfo;
			}

			function getAddressForOrderProcessing(addressType) {
				var responseAddress = {};
				
				if(addressType == "BillTo")
				{
					responseAddress.EmailAddress = contact.emailAddress;
					responseAddress.Name = encodeURIComponent(billingAddress.Name);
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
					responseAddress.Name = encodeURIComponent(shippingAddress.Name);
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

			//TODO: get free shipping status
			function isFreeShipping(){
				return $("#freeshipping").is(":checked");
			}
				
			// ADDRESS VALIDATION
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
							if(btAddr.Address2 == null)
							{
								setAddressFromSuggestion('BillTo', btAddr.Address1, billingAddress.Address2, btAddr.City, btAddr.CountryCode, btAddr.StateCode, btAddr.PostalCode);
							}
							else
							{
								setAddressFromSuggestion('BillTo', btAddr.Address1, btAddr.Address2, btAddr.City, btAddr.CountryCode, btAddr.StateCode, btAddr.PostalCode);
							}	
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
							if(stAddr.Address2 == null)
							{
								setAddressFromSuggestion('ShipTo', stAddr.Address1, shippingAddress.Address2, stAddr.City, stAddr.CountryCode, stAddr.StateCode, stAddr.PostalCode);
							}
							else
							{
								setAddressFromSuggestion('ShipTo', stAddr.Address1, stAddr.Address2, stAddr.City, stAddr.CountryCode, stAddr.StateCode, stAddr.PostalCode);
							}
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

			// CAPTURE Paper check Number
			function setPapaerCheckDetails()
			{
				var checkNumber = orderHeader.paymentCheckNumber || paperCheckDetails.CheckNumber;

				if (hasValue(checkNumber) == true) {
					paperCheckDetails.CheckNumber = checkNumber;
					$('#paperchecknumber').val(checkNumber);
				}
			}

			// SET PaperCheck status to Pending
			function setPaperCheckStatus()
			{
				var paymentType = getSelectedText('paymentmethod');
				if (paymentType.toLowerCase() == 'paper check' && hasValue(orderHeader.paymentCheckNumber) == false) {
					WorkspaceRecord.updateField('Incident.Status.Id', INC_STATUS_ID_CLOSED);
					WorkspaceRecord.updateField('Incident.DispId', INC_DISP_ID_WAITING_CHECK);
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', 'Pending');
				}
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
				billingAddress.Name = contact.firstName + ' ' + contact.lastName;
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
				
				if(contact.shipAddress1 == null)
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress1', contact.Address1);
				else
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress1', contact.shipAddress1);
				
				if(contact.shipAddress2 == null)
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress2', contact.Address2);
				else
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToAddress2', contact.shipAddress2);
				
				if(contact.shipCity == null)
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCity', contact.City);
				else
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCity', contact.shipCity);
				
				if(contact.shipCountryID == '[+]')
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCountry', contact.CountryID);
				else
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToCountry', contact.shipCountryID);
				
				if(contact.shipPostalCode == null)
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToPostalCode', contact.PostalCode);
				else
					WorkspaceRecord.updateField('BUS$OrderHeader.ShipToPostalCode', contact.shipPostalCode);
				

				// GET Shipping Address
				shippingAddress.Name = contact.firstName + ' ' + contact.lastName;
				shippingAddress.Address1 = contact.shipAddress1 == null ? contact.Address1 : contact.shipAddress1;
				shippingAddress.Address2 = contact.shipAddress2 == null ? contact.Address2 : contact.shipAddress2;
				shippingAddress.City = contact.shipCity == null ? contact.City : contact.shipCity;
				shippingAddress.PostalCode = contact.shipPostalCode == null ? contact.PostalCode : contact.shipPostalCode;
				shippingAddress.CountryCodeId = contact.shipCountryID == null ? contact.CountryID : contact.shipCountryID;
				shippingAddress.CountryCode = contact.shipCountry == '[+]' ? contact.CountryLabel : contact.shipCountry;
				shippingAddress.StateCodeId = contact.shipStateID == null ? contact.StateID : contact.shipStateID;
				shippingAddress.StateCode = contact.shipStateCode == '[+]' ? contact.StateLabel : contact.shipStateCode;
				
				shippingAddress.StateAbbr = getProvinceAbbreviation(shippingAddress.CountryCodeId, shippingAddress.StateCode);
			
				setTimeout(function () {
					if(contact.shipStateID == null)
						WorkspaceRecord.updateField('BUS$OrderHeader.ShipToState', contact.StateID);
					else
						WorkspaceRecord.updateField('BUS$OrderHeader.ShipToState', contact.shipStateID);
					
					// HELIX Set our original Shipping address object for address validation if they are set on a new Order from the Contact
					orgShippingAddress.Address1 = shippingAddress.Address1;
					orgShippingAddress.Address2 = shippingAddress.Address2;
					orgShippingAddress.City = shippingAddress.City;
					orgShippingAddress.State = contact.shipStateID;
					orgShippingAddress.Country = shippingAddress.CountryCodeId;
					orgShippingAddress.Postal = shippingAddress.PostalCode;
				}, 2000);
			}
			
			// COPY Contact Billing/Shipping Address to OrderHeader
			function setBillToShipToAddressFromContact(override) {
				//Bill To
				if (!addressHasValues("BillTo")) {
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToCustomerName', contact.firstName + ' ' + contact.lastName);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress1', contact.Address1);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToAddress2', contact.Address2);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToCity', contact.City);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToCountry', contact.CountryID);
					WorkspaceRecord.updateField('BUS$OrderHeader.BillToPostalCode', contact.PostalCode);
					
					// GET Billing Address
					billingAddress.Name = contact.firstName + ' ' + contact.lastName;
					billingAddress.Address1 = contact.Address1
					billingAddress.Address2 = contact.Address2
					billingAddress.City = contact.City
					billingAddress.PostalCode = contact.PostalCode
					billingAddress.CountryCodeId= contact.CountryID
					billingAddress.StateCodeId = contact.StateID;
					
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

				if (!addressHasValues("ShipTo")) {
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
					shippingAddress.StateCodeId = contact.shipStateID;
				
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
			}

			function setAddressFromSuggestion(addresstype, address1, address2, city, countryiso, stateAbbr, postalcode)
			{
				var countryid = getCountryIdByISO(countryiso);
				var stateid = getProvinceIDByCountryId(countryid, stateAbbr);				
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
					
					// SET validated address as Billing Address for JSON payload to submitorder.php
					billingAddress.Address1 = address1;
					billingAddress.Address2 = address2;
					billingAddress.City = city;
					billingAddress.PostalCode = postalcode;
					billingAddress.CountryCode = countryiso;
					billingAddress.CountryCodeId = countryid;
					billingAddress.StateCode = getProvinceNameFromAbbreviation(countryid, stateAbbr);
					billingAddress.StateCodeId = stateid;
					billingAddress.StateAbbr = stateAbbr;
					
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
					
					// SET validated address as Billing Address for JSON payload to submitorder.php
					shippingAddress.Address1 = address1;
					shippingAddress.Address2 = address2;
					shippingAddress.City = city;
					shippingAddress.PostalCode = postalcode;
					shippingAddress.CountryCode = countryiso;
					shippingAddress.CountryCodeId = countryid;
					shippingAddress.StateCode = getProvinceNameFromAbbreviation(countryid, stateAbbr);
					shippingAddress.StateCodeId = stateid;
					shippingAddress.StateAbbr = stateAbbr;
					
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
				info.IncidentID = incident.id;
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
			
			function createOrderLock(ebsLineID, origOrderNum, newOrderNum) {

				var info = {};
				info.Source = "Phone";
				info.SKU = "TBD";
				info.EBSLineID = ebsLineID;
				info.NewOrderNumber = newOrderNum;
				info.OriginalOrderNumber = origOrderNum;
				info.IncidentID = incident.id;
				info.OrderHeaderID = orderHeaderId;

				$.ajax({
					type: "POST",
					url: AJAX_ENDPOINT + '?action=createorderlock&sid=' + sid,
					data: JSON.stringify(info),
					success: function (data) {
						console.log('Create Order Lock Success');
					},
					dataType: "json",
					error: function (error){
						console.log('Create Order Lock Error');
						console.log('Error: ' + error);
					}

				});

			}
			
			function updateOrderLock(lockID, newOrderNum) {

				var info = {};
				info.NewOrderNumber = newOrderNum;
				
				$.ajax({
					type: "POST",
					url: AJAX_ENDPOINT + '?action=updateorderlock&orderlockid=' + lockID + '&sid=' + sid,
					data: JSON.stringify(info),
					success: function (data) {
						console.log('Update Success: ' + data);
					},
					dataType: "json",
					error: function (request, status, error) {
						console.log('Delete Error: ' + error);
					}
				});

			}

			/**
			 * Call the address validation endpoint to verify that the address is valid
			 * Added a TODO: here since I'm making this call synchronously and it should be changed
			 * @param {type} addr
			 * @returns {type} 
			 */
			function validateAddress(addr) {

				var validatedAddress;

				$.ajax({
					type: "POST",
					url: ADDRESS_VALIDATION_ENDPOINT + '?action=validateaddress&sid=' + sid ,
					data: addr,
					cache: false,
					async:false, //TODO: Need to revisit this
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

			function submitOrderForProcessing(orderDetails,orderSubmitSuccessCallback) {
				$.ajax({
					type: "POST",
					url: ORDER_SUBMIT_ENDPOINT + '?action=submitorder&sid=' + sid,
					data: JSON.stringify(orderDetails),
					cache: false,
					success: orderSubmitSuccessCallback,
					dataType: "json",
					timeout:300000,
					error: function (request, status, error) {
						logSysIntegration("BWS", "", "", "Error", "An error occurred while trying to process the order. " + error);
						orderSubmitSuccessCallback(null);
					}
				});
			}

			function orderSubmitSuccessCallback(data) {
				$.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: { border: 'none', backgroundColor: 'transparent' } });
				
				// cURL Timeout error
				// Adding condition to check for Null in the object.
				if(data.OrderMessage != null && data.OrderMessage != undefined) 
				{
					if (data.OrderMessage.toLowerCase() === 'timeout was reached') {

						var message = 'cURL Timeout, order will be queued for retry. Please do not resubmit order.';

						alert(message);
						
						WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
						WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
						
						pageLocked = true;

						disableSubmittedOrder();
						setOrderHeaderValuesBeforeSave();
						$.unblockUI();
						return;
					}
				}
				
				//Failure that can't be handled
				if (data == null || $.isNumeric(data.OrderCode) || (data.OrderCode && data.OrderCode.toString().toLowerCase() == 'soap failure')) {//A response code will only exist if there is afailure

					alert('An error occurred while attempting to submit the order.');
					
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					pageLocked = true;
					disableSubmittedOrder();

					if (data.OrderCode)
					{
						if (data.OrderCode == 28)
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
				
				//Check for OrderCode other than known codes
				if (!(data.OrderCode.toLowerCase() === 'paymentauthorizationfailed' || data.OrderCode.toLowerCase() === 'success' || data.OrderCode.toLowerCase() === 'failure')) {

					var message = data.AgentMessage || "An error occurred while attempting to submit the order.";

					alert(message);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued");
					pageLocked = true;

					disableSubmittedOrder();
								
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}
				
				 //Failure that an agent can respond to
				if (data.OrderCode.toLowerCase() === 'paymentauthorizationfailed') {
					var message = data.AgentMessage;

					alert(message);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Credit Card Declined"); 
					
					setOrderHeaderValuesBeforeSave();
					$('#submitorder').prop('disabled', false);
					$.unblockUI();

					return;
				}
				
				// Failure that an agent cannot respond to
				if (data.OrderCode.toLowerCase() === 'failure') {

					var message = data.AgentMessage || "An error occurred while attempting to submit the order.";

					alert(message);
					
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1);
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', "Queued"); 
					pageLocked = true;

					disableSubmittedOrder();
					
					setOrderHeaderValuesBeforeSave();
					$.unblockUI();
					return;
				}

				//Successful order
				if (data.OrderCode.toLowerCase() === 'success') {

					//Update the order
					//alert('Order created successfully. The order # is ' + data.OrderNumber);
					$('#ordernumber').val(data.OrderNumber);

					orderNumber = data.OrderNumber;
					WorkspaceRecord.updateField('BUS$OrderHeader.LockTrans', 1); 
					pageLocked = true;

					disableSubmittedOrder();
					
					WorkspaceRecord.updateField('BUS$OrderHeader.OrderNumber', data.OrderNumber); 
					WorkspaceRecord.updateField('BUS$OrderHeader.Status', 'Submitted');
					
					setOrderHeaderValuesBeforeSave();
					
					$('#tblLinkedOrderData tbody tr').each(function() {
						var lineIDText = $(this).find('td.clsLockID').text();
						updateOrderLock(lineIDText, data.OrderNumber);
					});

					$.ajax({
						type: "POST",
						url: AJAX_ENDPOINT + '?action=updatelinetrakingnumber&orderheaderid=' + orderHeaderId + '&sid=' + sid,
						data: JSON.stringify(data.LineItems),
						cache: false,
						success: function (data) {

							window.OrderTable.ajax.reload();

						},
						dataType: "json",
						error: function (request, status, error) {
							alert(error);
						}

					});
				}
				$.unblockUI();
				return;
			}

			/**
			 * Validate the order fields before submitting.
			 * Required: Eamil Address, Order Type (consumer order) BillTo & ShipTo
			 * @returns {bool} 
			 */
			function isOrderValid() {

				var isvalid = true;
				var addressValid = true;
				var addressMessage = '';
				var message = '';
				var paymentType = getSelectedText('paymentmethod');
				var shippingmethod = getSelectedValue('shippingmethod');
				var validationResponse = { billToValidationResponse:null,shipToValidationResponse:null, addressValid: true, orderValid: true, message: "", addressMessage: "" };
				var totals = getOrderAndShippingTotal();
				var shippingTotal = totals.shipping;
				var orderTotal = totals.order;
				//Validate the lines
				var linesValidation = validateOrderLines();
				isvalid = linesValidation.valid;
				message = message + linesValidation.message;

				// Capture the current Billing/Shipping address values
				var orderBrand = getSelectedText('orderbrand');

				// Address validation update, move logic to Submit Order validation rather than manual agent process
				var checkBill = false;
				var checkShip = false;
				
				//  Compare original Billing/Shipping address values with values given by agent, if any changes are detected we need to validate
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
					addressMessage = addressMessage + 'Billing Address has not been validated!\n\n';

				if(!shipAddrValid)
					addressMessage = addressMessage + 'Shipping Address has not been validated!\n\n';
				
				if(!billAddrValid || !shipAddrValid)
					addressValid = false;
				else
					addressValid = true;
				
				if (!hasValue(billingAddress.Name)) {
					message = message + 'Enter a Bill To Name \n\n';
					isvalid = false;
				}

				if (!hasValue(shippingAddress.Name )) {
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
				
				if (hasValue(billingAddress.PostalCode) && ((billingAddress.CountryCodeId == CA_COUNTRY_ID) || (billingAddress.CountryCodeId== US_COUNTRY_ID))) {
					if(!postalCodeCheck(billingAddress.PostalCode, billingAddress.CountryCodeId)){
						message = message + 'Invalid Bill To Postal Entered \n\n';
						isvalid = false;
					}
				}
				
				if (hasValue(shippingAddress.PostalCode) && ((shippingAddress.CountryCodeId == CA_COUNTRY_ID) || (shippingAddress.CountryCodeId == US_COUNTRY_ID))) {
					if(!postalCodeCheck(shippingAddress.PostalCode, shippingAddress.CountryCodeId)){
						message = message + 'Invalid Ship To Postal Entered \n\n';
						isvalid = false;
					}
				}

				if ((!hasValue(billingAddress.StateCodeId) || billingAddress.StateCode == '[+]') && !(billingAddress.CountryCodeId == UK_COUNTRY_ID)) {
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
				
				//Shipping Check:
				if (orderHasAerosol() && (shippingIsUSTerritory() || shippingIsAPOMailbox() || shippingIsAlaskaHawaii())) {
					message = message + 'One or more items cannot be shipped to this customer to this location \n\n';
					isvalid = false;
				}

				//If the ship to country is the UK, the ship promise date is required
				if (shippingAddress.CountryCodeId == UK_COUNTRY_ID) {

					var shipPromise = $('#expecteddeliverydate').val();

					if (hasValue(shipPromise) == false) {
						message = message + 'The expected delivery date is required for UK orders\n\n';
						isvalid = false;
					}
				}

				//Check that consumer order is selected
				if (orderHeader.orderTypeID != ORDER_TYPE_CONSUMER_ID) {
					message = message + 'Select the correct Order Type\n\n';
					isvalid = false;
				}

				if (isNoChargeOrder() && (shippingTotal > 0 || orderTotal > 0)) {
					isvalid = false;
					message = message + "'No Charge Order' was selected, but there is a balance.\n\n";
				}

				if (paymentType.toLowerCase() == 'credit card') {
					if(!cybersourceToken) {
						isvalid = false;
						message = message + "Enter credit card details for this order\n\n";
					}
				}

				if (paymentType.toLowerCase() == 'paper check') {
					if (!(hasValue($("#paperchecknumber").val())) || !(hasValue($("#papercheckamount").val()))) {
						isvalid = false;
						message = message + "Check/Money Order is required before order can be submitted\n\n";
					}
				}
				
				if (!hasValue(orderBrand) || orderBrand == '[+]') {
					message = message + 'Order Brand must be selected \n\n';
					isvalid = false;
				}
				
				if(shippingIsAPOMailbox() && ($.inArray(parseInt(shippingmethod), [SHIP_PRIORITY_MAIL, SHIP_PARCEL_POST]) < 0)){
					message = message + 'Orders shipping to an APO address must use the Priority Mail shipping option\n\n';
					isvalid = false;
				}
				
				validationResponse.orderValid = isvalid;
				validationResponse.addressValid = addressValid;
				validationResponse.message = message;
				validationResponse.addressMessage = addressMessage;

				return validationResponse;
			}
			
			function postalCodeCheck (postalCode, type) {

				if (!postalCode) {
					return null;
				}

				postalCode = postalCode.toString().trim();

				var us = new RegExp("^\\d{5}(-{0,1}\\d{4})?$");
			   // var ca  = new RegExp(/^((?!.*[DFIOQU])[A-VXY][0-9][A-Z])|(?!.*[DFIOQU])[A-VXY][0-9][A-Z]\ ?[0-9][A-Z][0-9]$/i);
				var ca = new RegExp(/^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$/i);

				if(type == US_COUNTRY_ID){
					if (us.test(postalCode.toString())) {
						return postalCode;
					}
				}

				if(type == CA_COUNTRY_ID)
				{
					if (ca.test(postalCode.toString())) {
						return postalCode;
					}
				}
				return null;
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
							(hasValue(billingAddress.StateCode) == false || billingAddress.StateCode == "[+]" || billingAddress.StateCode == "[No Value]") &&
							hasValue(billingAddress.PostalCode) == false)
						return false;
					return true;
				}
				else
				{
					if(hasValue(shippingAddress.Address1) == false && 
							hasValue(shippingAddress.Address2) == false && 
							hasValue(shippingAddress.City) == false && 
							(hasValue(shippingAddress.StateCode) == false || shippingAddress.StateCode == "[+]" || shippingAddress.StateCode == "[No Value]") &&
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

				return accounting.toFixed(lineTotal,2);
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
				orderLine.AdjustedPrice = $("#adjustedprice").val();
				orderLine.SerialNumber = $("#serialnumber").val();
				orderLine.ModelNumber = $("#modelnumber").val();
				orderLine.CouponSavings = "";
				orderLine.TotalAfterDiscount = "";
				orderLine.EstSalesTax = taxTotal;
				orderLine.SummaryInformation;
				orderLine.SubTotal = getCurrentLineTotal();
				orderLine.Shipping = $("#shippingcost").val();
				orderLine.SalesTax = taxTotal;
				orderLine.Discounts;
				orderLine.Total = getCurrentLineTotal();
				orderLine.EstShipTax;
				orderLine.CouponCodeApplied;
				orderLine.SKU;
				orderLine.CouponCode;
				orderLine.RefundRequestOrderNum;
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
				orderLine.LineType = 1;
				orderLine.OrderType = orderHeader.orderType;
				orderLine.OrderNumber = $("#ordernumber").val();
				orderLine.Line = getNextLineNumber();
				orderLine.UnitOfMeasure = $("#uom").val();
				orderLine.Inventory = $("#partnumber").data("inv");
				orderLine.NoCharge = $("#nocharge").is(":checked") == true ? 1:0;
				orderLine.Aerosol = YesNoStringToBool($("#partnumber").data("aerosol"));
				orderLine.ReasonCode = getSelectedValue('nochargereasoncode');
				orderLine.ModelNumber = $("#modelnumber").val();
				orderLine.SeriallNumber = $("#serialnumber").val();

				// Add the line to the order lines object then add to the grid if all is good.
				$.ajax({
					type: "POST",
					url: AJAX_ENDPOINT + '?action=addorderline&sid=' + sid,
					data: orderLine,
					cache: false,
					success: function (data) {
						// A line object is returned ready to be added to the grid
						window.OrderTable.row.add(data.line).draw();
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
				var rows = window.OrderTable.rows().data();
				if (rows.length > 0) {
					for (var i = 0; i < rows.length; i++) {
						row = rows[i];
						if (row.aerosol)
							hasAerosol = true;
					}
				}
				return hasAerosol;
			}
			
			// ValidateOrderLines triggered during Submit Order process
			function validateOrderLines() {
				var valid = true;
				var message = "";
				var rows = window.OrderTable.rows().data();
				var isAirFreight = $('#shippingmethod').find(":selected").data("airfreight");
				var paymentType = getSelectedText('paymentmethod');

				if (rows.length > 0) {
					for (var i  = 0; i < rows.length; i++) {
						row = rows[i];
						//Check if any line is item number 1422
						if (row.part == "1422") {
							if (isAirFreight == true) {
								valid = false;
								message = message + "Order contains item 1422 and can't be shipped via air freight\n\n";
								continue;
							}
						}

						//Check for aerosol
						if (row.aerosol) {

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

						//Check no charge orders
						if (isNoChargeOrder()) {
							if (row.nocharge != true) {
								valid = false;
								message = message + "All items on the order should be marked no charge for No Charge orders\n\n";
							}
						} 

					}
				   return { valid: valid, message: message };
				}
				return { valid: false, message: "Add items to the order before submitting.\n\n" };
			}

			/**
			 * Add the shipping row to the grid
			 */
			function setShippingRow() {

				var orderLine = {};
				var rows = window.OrderTable.rows().data();
				var shippingRow = null;

				if (!orderHasLines())
					return;

				if (rows.length > 0) {
					for (var i  = 0; i < rows.length; i++) {
						row = rows[i];
						if (row.lineid == -1) {
							shippingRow = row;
							window.OrderTable.row(i).remove().draw(false);
						}
					}
				}

				lineNumber = getNextLineNumber();

				//Update the shipping cost if order amount changed and shipping cost was not overridden

				if (!hasManualShippingOverride)
					setShippingCost();

				if (shippingRow == null) {

					shippingRow = {
						"ordernumber": "",
						"line": lineNumber,
						"part": "",
						"qty": "1",
						"uom": "EA",
						"description": "Shipping",
						"unitsellingprice": $("#shippingcost").val(),
						"adjustedprice": $("#shippingcost").val(),
						"status": "",
						"linetype": "",
						"inv": "",
						"lineid": -1,
						"orderheaderid": orderHeaderId,
						"aerosol": "N",
						"nocharge": "N",
						"reasoncode": "",
						"modelnumber": "",
						"serialnumber":"",
					};
				} else {
					shippingRow.line == lineNumber;
					shippingRow.unitsellingprice = $("#shippingcost").val();
					shippingRow.adjustedprice = $("#shippingcost").val();
				}
			
				window.OrderTable.row.add(shippingRow).draw(false);

				setOrderHeaderValuesBeforeSave();
			}

			function getChannelNameFromId(id) {
				return channelList[id];
			}

			function getCountryISONameById(countryId){
				return countryList[countryId];
			}

			function getCountryIdByISO(val){
				var countryId = ''
				$.each(countryList, function (value, text) {

					if(text.toLowerCase() == val.toLowerCase())
						countryId =  value;
				});
					
				return countryId;
			}

			function getProvinceIDByCountryId(countryid,provinceabbr){
				
				var provinceList = getProvinceListForCountryId(countryid);
				var province = getProvinceNameFromAbbreviation(countryid, provinceabbr);
				var provinceId = ''

				if (province) {

					$.each(provinceList, function (value, text) {

						if (text.toLowerCase() == province.toLocaleLowerCase())
							provinceId = value;
					});
				}

				return provinceId;
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


			function orderHasLines() {

				if (window.OrderTable) {
					var rows = window.OrderTable.rows().data();

					if (rows.length > 0)
						return true;
				}

				return false;

			}

		/* Object Helpers */

			function disableSubmittedOrder() {
				if (pageLocked) {
					$("#continueToShippingButton").css("display","none");
					$("#btnShipping").css("display","none");
					$("#btnAddLine").css("display","none");
					$("#submitorder").css("display","none");
					
					$('input, textarea, table, select, button').attr('disabled', 'disabled');
					$('.editor_remove').remove();
					
					
					$("#ordersubmitform").css("display", "");
					
					SetOrderTotals();
				};
				
				if(orderHeader.orderNumber && orderHeader.status == 'Submitted'){
					$("#divSubmitError").css("display","none");
					$("#divSubmitSummary").css("display","");
					$("#divSubmitSummary").html('Order has been successfully processed. The Order # is ' + orderHeader.orderNumber + '.');
				} else if (!orderHeader.orderNumber && (orderHeader.status == 'Submitted' || orderHeader.status == 'Queued')){
					$("#divSubmitSummary").css("display","none");
					$("#divSubmitError").css("display","");
					$("#divSubmitError").html('Order has been successfully processed; however an Order Number is not available at this time. DO NOT RESUBMIT ANOTHER Order FOR THIS CONSUMER!');
				} else if (orderHeader.status == 'Credit Card Declined'){
					$("#divSubmitSummary").css("display","none");
					$("#divSubmitError").css("display","");
					$("#divSubmitError").html("The consumer's credit card has been declined and needs to be reprocessed. Always confirm the customer's card number and expiration date before submitting. If this information is confirmed and the card is still declined, please ask the customer if they have a different card they would like to use.");
				} else if (orderHeader.status == 'Failed'){
					$("#divSubmitSummary").css("display","none");
					$("#divSubmitError").css("display","");
					$("#divSubmitError").html('The Order was not processed due to a system communication error. Apologize to the customer and let the customer know their Order will be saved. Advise them you will call them back as soon as the systems are restored. DO NOT ATTEMPT TO PLACE ANOTHER Order FOR THE CONSUMER!');
				} else if (orderHeader.status == 'Cancelled'){
					$("#divSubmitSummary").css("display","none");
					$("#divSubmitError").css("display","");
					$("#divSubmitError").html('This Order has been cancelled.');
				}
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

			function setRequiredPhysicalAddress(shipMethodCurrentValue) {

				requirePhysicalAddress = false;

				if (($.inArray(shipMethodCurrentValue, [SHIP_FEDEX2DAY_ID, SHIP_FEDEX_GROUND_ID, SHIP_FEDEX_OVERNIGHT_ID]) >= 0)) {
					requirePhysicalAddress = true;
				}

				return requirePhysicalAddress;

			}
			
			/**** Control Helpers ****/

			/**
			 * Get the selected value of a select box
			 * @param {type} controlId
			 * @returns {type} 
			 */
			function getSelectedValue(controlId) {
				var val = $('#' + controlId + ' option:selected').val();
				return val;
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

				function getSelectedDataAttribute(controlId,attr) {
					if(attr == "text")
						return $('#' + controlId + ' option:selected').text();
					else
						return $('#' + controlId + ' option:selected').data(attr);
				}

				function getProvinceAbbreviation(countryId, province) {
					if (!hasValue(province))
						return '';

					if (provinceAbbreviationList.countryid[countryId]) {

						var countryAbbrObj = provinceAbbreviationList.countryid[countryId];

						for (var provKey in countryAbbrObj) {

							if (countryAbbrObj.hasOwnProperty(provKey)) {

								if (countryAbbrObj[provKey].indexOf(province) >= 0) {
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

					if (fld == undefined || fld == null || $.trim(fld) == '' || fld == -1)
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

				

				/**
				 * Toggle the price adjustment reason code field. Show and hide
				 * based on manual price changes or no charge selection.
				 */
				function togglePriceAdjustmentReasonCode() {

					var noChargeCheckbox = $("#nocharge");
					var defaultPrice = $("#adjustedprice").val()
					var adjustedPrice = $("#unitprice").val();

					if (noChargeCheckbox.is(':checked') == true) {
						buildReasonLists('nochargereasoncode', 1, null);
					} else {
						buildReasonLists('nochargereasoncode', 2, null);
					}

					if (noChargeCheckbox.is(':checked') == false && defaultPrice == adjustedPrice) {
						$('#adjustedprice').removeAttr('disabled');
						$('#nochargereasoncol').addClass('hidden');
						$('#nochargereasoncode').attr('disabled', 'disabled');
						$('#nochargereasoncode').removeClass('validation-error');
						$('#nochargereasoncode').removeAttr('required');
						$("#nochargereasoncode").val($("#nochargereasoncode option:first").val()).trigger('change');

					} else {
						$('#nochargereasoncode').removeAttr('disabled');
						$('#nochargereasoncode').attr('required', 'required');
						$('#nochargereasoncol').removeClass('hidden');

					}

				}

				function toggleExpectedDeliveryDate() {

					if (shippingAddress.CountryCodeId == UK_COUNTRY_ID) {

						$('#expecteddeliverydatecol').removeClass('hidden');
						$('#expecteddeliverydate').attr('required', 'required');

					} else {

						$('#expecteddeliverydatecol').addClass('hidden');
						$('#expecteddeliverydate').removeAttr('required');
					}
				}

				/**
				 * Set the shipping price based on the shipping method selected and order total.
				 */
				function setShippingCost() {
					if ($('#shippingmethod option:selected').data("charges") == undefined) {
						$('#shippingcost').val("");
						return;
					}

					var selectedValue = $('#shippingmethod option:selected').val();

					var optionCharges = $('#shippingmethod option:selected').data("charges").ShippingCost;
					
					if ((shippingIsAPOMailbox() || shippingIsUSTerritory()) && (selectedValue == SHIP_PRIORITY_MAIL || selectedValue == SHIP_FEDEX_OVERNIGHT_ID)) {
						optionCharges = $('#shippingmethod option:selected').data("charges").AltShippingCost
					}

					var shippingCharge;
					var subTotal = getOrderAndShippingTotal().totalforshippingselection;

					for (var i = 0; i < optionCharges.length; i++) {

						if (subTotal >= optionCharges[i].min && subTotal <= optionCharges[i].max) {

							shippingCharge = optionCharges[i].cost;

							if (shippingCharge == 0) {

								$('#shippingreasoncolumn').removeClass('hidden');
								$('#shippingreasoncode').attr('required', 'required');
							}
						}
					}

					$('#shippingcost').data('autoval', shippingCharge);
					$('#shippingcost').val(shippingCharge);
				}

				function toggleShippingReasonByCost() {

					var cost = actualShippingCost || 0;
					var shippingMethod = getSelectedValue('shippingmethod');
					if (cost == 0 && hasValue(shippingMethod)) {

						if (!isNoChargeOrder()) {
							if ((shippingIsCanada() && order100OrMore()) || (shippingIsUS() && order40OrMore())) {
								buildReasonLists('shippingreasoncode', 4, REASON_WAIVE_PROMOTION);
							}
						}
						$('#freeshipping').prop("checked", true);
						$('#shippingreasoncolumn').removeClass('hidden');
						$('#shippingreasoncode').attr('required', 'required');

					} else {
						$('#shippingreasoncolumn').addClass('hidden');
						$('#shippingreasoncode').removeAttr('required');
						$('#shippingreasoncode option[value=""]').prop('selected', true);
						$('#freeshipping').prop("checked", false);
					}
				}


				/**
				 * Build the ShipMethod list. An AJAX call is made to retrieve the list by country and order type
				 */
				function buildShipMethodList(limitToGround,reset) {
					var setOptions = function (data) {
						//Append blank options
						var blankOption = "<option value=''>[+]</option>";
						
						$('#shippingmethod')
							.empty()
							.append($(blankOption));

						$.each(window.menulists.shipping_methods, function (index, shipMethod) {
							
							if (pageLocked || (!pageLocked && shipMethod.active == true)){
								var option = $("<option></option>");
								option.attr("value", shipMethod.id)
								.text(shipMethod.label)
								.data("charges", shipMethod.charges)
								.data("airfreight", shipMethod.airfreight)
								.data("code", shipMethod.code);

								if (limitToGround == true && (shipMethod.airfreight == true || (shippingIsAlaskaHawaii() || shippingIsAPOMailbox() || shippingIsUSTerritory() ))) {
									option.prop('disabled', true);
									option.attr('title', "Not available: One or more items on order can't ship by Air.");
								}

								$('#shippingmethod').append(option);
							}
						});

						if (hasValue(orderHeader.shippingMethod)) {

							$('#shippingmethod option[value="' + orderHeader.shippingMethod + '"]')
								.attr('selected', true)
								.trigger('change',true);
						}
					};

					if(reset === true)
						setOptions(window.menulists.shipping_methods);
					else
					{

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
				
				function buildOrderBrandList(reset) {
					var setOptions = function (data) {
						
						var orderbrandset = false;
						var orderbrandid = 0;
						
						//Append blank options
						var blankOption = "<option value=''>[+]</option>";
						
						if (pageLocked == false)
							$('#orderbrand').empty();
						else
							$('#orderbrand').empty().append($(blankOption));

						$.each(window.menulists.order_brands, function (index, orderBrand) {
							
							if (pageLocked || (!pageLocked && orderBrand.active == true))
							{
								var option = $("<option></option>");
								option.attr("value", orderBrand.id)
								.text(orderBrand.label)
								.data("overridesaleschannel", orderBrand.overridesaleschannel)
								.data("bwssubmitorderconfig", orderBrand.bwssubmitorderconfig)
								.data("saleschannel", orderBrand.saleschannel);

								$('#orderbrand').append(option);
								
								// Populate Order Brand in the UI and set BUS$OrderHeader.OrderBrand
								if(orderBrand.incidentbrand)
									if (parseInt(orderBrand.incidentbrand) == incident.brand)
									{
										orderbrandset = true;
										orderbrandid = orderBrand.id;
										WorkspaceRecord.updateField('BUS$OrderHeader.OrderBrand', orderbrandid);
									}
							}
						});
						
						if (hasValue(orderHeader.orderBrand))
							$('#orderbrand option[value="' + orderHeader.orderBrand + '"]').attr('selected', true);
						else if(orderbrandset)
							$('#orderbrand option[value="' + orderbrandid + '"]').attr('selected', true);
					};
					
					if(reset === true)
						setOptions(window.menulists.order_brands);
					else
					{
						$.ajax({
							type: "GET",
							url: AJAX_ENDPOINT + '?action=getorderbrands&country=' + shippingAddress.CountryCodeId + '&sid=' + sid,
							cache: true,
							success: function (data) {

								if (window.menulists == undefined)
									window.menulists = {};
								window.menulists.order_brands = data;
								setOptions(data);
							},
							dataType: "json"
						});
					}
				}
				
				function buildOrderNoChargeReasonList(reset) {
					var setOptions = function (data) {
						//Append blank options
						var blankOption = "<option value=''>[+]</option>";
						
						$('#nochargereason')
							.empty()
							.append($(blankOption));

						$.each(window.menulists.order_nochargereasons, function (index, orderNoChargeReason) {
							if(orderNoChargeReason.ordertype == "Order"){
								if (pageLocked || (!pageLocked && orderNoChargeReason.active == true)){
									var option = $("<option></option>");
									option.attr("value", orderNoChargeReason.id)
									.text(orderNoChargeReason.label)
									.data("linktoordernumber", orderNoChargeReason.linktoordernumber);

									$('#nochargereason').append(option);
								}
							}
						});

						if (hasValue(orderHeader.orderNoChargeReason)) {
							$('#nochargereason option[value="' + orderHeader.orderNoChargeReason + '"]')
								.attr('selected', true)
								.trigger('change',true);
						}
					};
					
					if(reset === true)
						setOptions(window.menulists.order_nochargereasons);
					else
					{
						$.ajax({
							type: "GET",
							url: AJAX_ENDPOINT + '?action=getordernochargereasons&country=' + shippingAddress.CountryCodeId + '&sid=' + sid,
							cache: true,
							success: function (data) {
								if (window.menulists == undefined)
									window.menulists = {};
								window.menulists.order_nochargereasons = data;
								
								if(data.length == 0){
									window.requireorderflow = false;
									setFieldVisibility();
								} else {
									window.requireorderflow = true;
									setFieldVisibility();
								}
								
								setOptions(data);
							},
							error: function (error) {
								window.requireorderflow = false;
								setFieldVisibility();
							},
							dataType: "json"
						});
					}
				}

				function buildReasonLists(controlId,reasonType,selectedValue) {

					var blankOption = "<option value=''>[+]</option>";

					$('#' + controlId)
						.empty()
						.append(blankOption);

					$.each(reasonCodeList, function (idx,reason) {

						if (reason.reasontype != reasonType)
							return true;

						$('#' + controlId).append($("<option></option>")
							.attr("value", reason.id)
							.text(reason.name)
							.data("requiresserialnumber", reason.requiresserialnumber)
							.data("requiresmodelnumber", reason.requiresmodelnumber)
							.data("code",reason.code)
							);

						if (reason.id == selectedValue) {
							$('#' + controlId + ' option[value="' + reason.id + '"]')
								.attr('selected', true)
								.trigger('change');

						};

					});
					
					if(orderHeader.shippingReason > 0)
					{
						$('#shippingreasoncode option[value="' + orderHeader.shippingReason + '"]').attr('selected', true);
						$('#shippingreasoncolumn').removeClass('hidden');
					}

				}

				function buildCreditCardBillingStateList() {
					var billToCountryStateList = getProvinceListForCountryId(billingAddress.CountryCodeId);
					$('#creditcardstate').empty();
					$('#creditcardstate').append("<option value=''>[+]</option>");
					
					$.each(billToCountryStateList, function (value, text) {
						$('#creditcardstate').append($("<option></option>")
						.attr("value", value)
						.text(text));
					});
				}

				/**
				 * Toggle the searial number and model number state
				 * @param {bool} enabledAndRequired
				 */
				function toggleModelAndSerialNumber(enabledAndRequired) {

					if (enabledAndRequired == true) {
						$("#serialnumber").removeAttr('disabled');
						$("#modelnumber").removeAttr('disabled');
						$("#serialnumber").attr('required', 'required');
						$("#modelnumber").attr('required', 'required');
					}
					if (enabledAndRequired == false) {
						$("#serialnumber").attr('disabled', 'disabled');
						$("#modelnumber").attr('disabled', 'disabled');
						$("#serialnumber").removeAttr('required').removeClass('validation-error');
						$("#modelnumber").removeAttr('required').removeClass('validation-error');
					}
				}

				function toggleSerialNumber(enabledAndRequired) {

					if (enabledAndRequired == true) {
						$("#serialnumber").removeAttr('disabled');
						$("#serialnumber").attr('required', 'required');
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
					var selectedValue = $('#shippingmethod option:selected').val();

					if ((shippingIsAPOMailbox() || shippingIsUSTerritory()) && (selectedValue == SHIP_PRIORITY_MAIL || selectedValue == SHIP_FEDEX_OVERNIGHT_ID)) {
						optionCharges = $('#shippingmethod option:selected').data("charges").AltShippingCost
					}

					var shippingCharge;

					orderTotal = getOrderAndShippingTotal().order;

					for (var i = 0; i < optionCharges.length; i++) {

						if (orderTotal >= optionCharges[i].min && orderTotal < optionCharges[i].max) {
							shippingCharge = optionCharges[i].cost;
						}
					}
				}

				/**
				 * Build the Payment Method list by BillTo country id
				 */
				function buildPaymentMethodList() {
					//begin
					var setOptions = function (data) {
						//Append blank options
						var blankOption = "<option value=''>[+]</option>";
						
						$('#nochargereason')
							.empty()
							.append($(blankOption));

						$.each(window.menulists.order_nochargereasons, function (index, orderNoChargeReason) {
							if(orderNoChargeReason.ordertype == "Order"){
								if (pageLocked || (!pageLocked && orderNoChargeReason.active == true)){
									var option = $("<option></option>");
									option.attr("value", orderNoChargeReason.id)
									.text(orderNoChargeReason.label)
									.data("linktoordernumber", orderNoChargeReason.linktoordernumber);

									$('#nochargereason').append(option);
								}
							}
						});

						if (hasValue(orderHeader.orderNoChargeReason)) {
							$('#nochargereason option[value="' + orderHeader.orderNoChargeReason + '"]')
								.attr('selected', true)
								.trigger('change',true);
						}
					};
					//end
					
					$.ajax({
						type: "GET",
						url: AJAX_ENDPOINT + '?action=getpaymentmethods&country=' + billingAddress.CountryCodeId+ '&sid=' + sid,
						cache: false,
						success: function (data) {
							if (window.menulists == undefined)
								window.menulists = {};
							window.menulists.payment_methods = data;

							//Append blank options
							var blankOption = "<option value=''>[+]</option>";

							$('#paymentmethod')
								.empty()
								.append($(blankOption));

							$.each(window.menulists.payment_methods, function (id, label) {
								
								// Exclude Paper Check option for select Profiles								
								if(id == 3 && paperCheckDenyList.includes(profileID.toString()))
								{
									// Skip adding PaperCheck if the logged in Profile ID is in the CUSTOM_CFG_PAPER_CHECK_DENY_LIST setting
								}
								else
								{
									var option = $("<option></option>");
									option.attr("value", id)
									.text(label);

									if (id == PAY_NO_CHARGE_ID) {
										option.prop('disabled', true);
									}

									$('#paymentmethod').append(option);
								}
							});
							
							if (hasValue(orderHeader.paymentMethod)) {
								$('#paymentmethod option[value="' + orderHeader.paymentMethod + '"]').attr('selected', true);
								
								if(getSelectedText('paymentmethod') == 'Credit Card' && !pageLocked){
									$('#launchcybersource').removeClass('hidden');
								}
							}
							
							defPaymentMethodListLoaded.resolve();
						},
						dataType: "json"
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
				
				function formatAddress(addr) {
					var addr2 = hasValue(addr.Address2) ? addr.Address2 + "\n" : "";
					var formattedAddress = addr.Address1 + "\n" + addr2 + addr.City + ", " + addr.StateCode + " " + addr.CountryCode + " " + addr.PostalCode;
					return formattedAddress;
				}


				function findIndexOfObjWithAttr(array, attr, value) {
					for (var i = 0; i < array.length; i++) {
						if (array[i][attr] === value) {
							return i;
						}
					}
					return -1;
				}

				/**
				 * Reset all the controls on the form after adding a line item
				 */
				function resetFormAfterAddingLine() {
					$('.reset').each(function () {
						$(this).val('');
					});

					$("#nocharge").attr('checked', false);

					$("#adjustedprice").trigger('focusout');
					$("#shippingcost").trigger('focusout');
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
							Payment: {
								AccountNumber: "",
								Amount: "",
								Name: "",
								ExpMonth: "",
								ExpYear: "",
								CardCode: "",
								TransactionDate: ""

							},
							CheckPayment: {
								Amount: "",
								CheckNumber: "",
								Electronic: "",
								TransactionDate: ""
							},
							SalesTax: {
								Amount: "",
							},
							Channel: "",
							OnyxIndividualId: "",
							OrderDate: "",
							OrderTotal: "",
							ShipAmount: "",
							ShipCode: "",
							SubTotal: "",
							CRMPurchaseOrderID: "",
							OrderHeaderId: "",
							IncidentId: "",
							ContactId:"",
							SubmitBrandConfig:""
						}
					};

					return OrderObjectDefinition;
				}
			});
		});
	});
});


/**** FUNCTIONS *****/

function getStorageObject(key) {
	var storageVal = localStorage.getItem(key);
	if (storageVal != null) {
		return JSON.parse(storageVal);
	}
	return null
}