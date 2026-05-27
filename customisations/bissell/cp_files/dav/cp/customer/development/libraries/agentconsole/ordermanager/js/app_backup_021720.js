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

var addressValidated = null;
var AJAX_ENDPOINT = CUSTOM_SCRIPTS_PATH + "ordermanagerajaxhandler.php";
var ADDRESS_VALIDATION_ENDPOINT = CUSTOM_SCRIPTS_PATH + "validateaddress.php";
var LOYALTY_STATUS_ENDPOINT = CUSTOM_SCRIPTS_PATH + "loyaltydata.php";
var ORDER_SUBMIT_ENDPOINT = CUSTOM_SCRIPTS_PATH + "submitorder.php";
var INCIDENT_OBJECT_NAME = "Incident";
var CONTACT_OBJECT_NAME = "Contact";
var ORDER_HEADER_OBJECT_NAME = "BUS$OrderHeader";
var AU_COUNTRY_ID = 4;
var NZ_COUNTRY_ID = 3;
var US_COUNTRY_ID = 1;
var UK_COUNTRY_ID = 5;
var CA_COUNTRY_ID = 2;
var ORDER_TYPE_CONSUMER_ID = 1;
var beforeSaveMessage;
var c = window.external.Contact;
var i = window.external.Incident;
var o = new CXObject('BUS.OrderHeader');
var currentCId = getParameterByName('cid') || i.CId;
var consumerId = getParameterByName('consumer_id');
var currentIId = getParameterByName('iid') || i.IId;
var orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
var orderBrand = getCustomObjectField("BUS", "OrderHeader", "OrderBrand");
var orderChannel = getCustomObjectField("BUS", "OrderHeader", "OrderChannel");
var shipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
var shipToState = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
var shipToPostal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
var billToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
var billToState = getCustomObjectField("BUS", "OrderHeader", "BillToState");
var lineNumber = 0;
var orderNumber = getCustomObjectField("BUS", "OrderHeader", "OrderNumber") || '';
var orderHeaderId = getParameterByName('oid'); //The only way to get the BUS.OrderHeader.ID is to pass it in the URL.
var sid = getParameterByName('sid'); //Agent session id
var orderTotal = getCustomObjectField("BUS", "OrderHeader", "Total") || "0.00";
var orderSubTotal = getCustomObjectField("BUS", "OrderHeader", "SubTotal") || "0.00";
var orderSalesTaxTotal = getCustomObjectField("BUS", "OrderHeader", "Tax") ||  "0.00";
var shippingTotal = getCustomObjectField("BUS", "OrderHeader", "ShippingTotal") || "0.00";
var hasManualShippingOverride = false;
var ccDetails = getStorageObject('ccDetails') || {};
var paperCheckDetails = getStorageObject('paperCheckDetails') || {};
var countryList = [];
var provinceList = [];
var provinceAbbreviationList = [];
var conusList = [];
var reasonCodeList = [];
var channelList = [];
var orderTotals = {};
var salesTaxData = null;
var pageLocked = getCustomObjectField("BUS", "OrderHeader", "LockTrans"); 
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


//Init controls and other control specific event handlers

//We need some async values before we can set the workspace values;
$.when(defCountryListLoaded, defProvinceListLoaded, defProvinceAbbreviationListLoaded, defPaymentMethodListLoaded).done(function () {
    setWorkspaceValues();
    defOrderDataReady.resolve(true); //this deferred event is defined in the table.order.js file
    disableSubmittedOrder();
});


$(document).ready(function () {

        if (currentCId < 1 || currentCId == null || currentIId < 1) {
            $.blockUI({ message: $('#noContactSelected') });
            return;
        }
        else {
            $.unblockUI;
        }

        if (hasValue(c.EmailAddr))
            setLoyaltyStatus();

        $('.input-group.date').datepicker();
        var date = new Date();

        var selectedPartData = [];

        //Part lookup type ahead init
        var partLookupTypeAhead = new Bloodhound({
            datumTokenizer: function (datum) {
                return Bloodhound.tokenizers.whitespace(datum.value);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {

                url: CUSTOM_SCRIPTS_PATH + 'partsearch.php?sid=' + sid + '&_=' + date.getTime(),
                cache: false,
                prepare: function (query, settings) {
                    settings.url += '&itemnumber=' + query + '&country=' + shipToCountry ;
                    return settings;
                },
                filter: function (searchresponse) {
                    $.extend(lastItemSearchResponse, searchresponse);
                    return searchresponse;
                }
            }
        });

        Ladda.bind('.mt-ladda-btn', { timeout: 2000 });

        partLookupTypeAhead.initialize();

        $('#partnumber').typeahead(
            {
                hint: true,
                hilight: true,
                minLength:3
        }, {
            name: 'partnumber_data',
            displayKey: 'value',
            source: partLookupTypeAhead.ttAdapter(),
            limit: 20,           
            templates: {
                suggestion: Handlebars.compile([
                  '<div class="tt-suggestion tt-selectable">',
                        '<div>',
                            '<h5>{{value}}</h5>',
                            '<h6>Inventory: <strong>{{inv}}</strong></h6>',
                            '<p>{{description}}</p>',
                        '</div>',
                  '</div>',
                ].join(''))
            }
        });

        //Listen for the grid delete event and check for order lines
        $(document).on('ordertable.delete ordertable.rowadded', function () {

            setTimeout(function () {

                SetHazardMessage();

                AutoSelectShippingMethod();

                SetOrderTotals();

                if (!orderHasLines()) {
                    $('#freeshipping').prop("checked", false);
                }

            }, 500);

        });

        $(document).on('ordertable.init', function () {

            setTimeout(function () {

                SetHazardMessage();

                SetOrderTotals();

            }, 500);

        });

        $('#partnumber').bind('typeahead:selected', function (obj, selectedData, name) {
            setSelectedPart(selectedData);
        });

        $('#partnumber').focusout(function () {
            if ($(this).val() == '' ){//|| $(this).val() != $('#selectedpartnumber').val()) {
                $('#unitprice, #adjustedprice').val('');
                $('#partname').val('');
                $('#partdescription').val('');
                $('#modelnumber').val('');
                $('#quantity').val('');
                $('#uom').val('');
                $('#linetotal').val('0.00');
                $('#partnumber').data('inv', '');
            } else {
                if ($(this).val() != selectedPartData.value) {
                    var partSearchIndex = findIndexOfObjWithAttr(lastItemSearchResponse, "name", $(this).val());
                    if(partSearchIndex > -1)
                        setSelectedPart(lastItemSearchResponse[partSearchIndex]);
                }
            }
        });

        $('#partnumber').on('paste', function () {
            $("#partnumber").typeahead("setQuery", $(this).val()).focus();
        });

        function setSelectedPart(selectedData) {
            selectedPartData = selectedData;
            $('#unitprice, #adjustedprice').val(accounting.toFixed(selectedData.price, 2));
            $('#partname').val(selectedData.name);
            $('#selectedpartnumber').val(selectedData.name);
            $('#partdescription').val(selectedData.description);
            $('#modelnumber').val(selectedData.model);
            $('#uom').val(selectedData.uom);
            $('#quantity').val('1');
            $('#partnumber').data('inv', selectedData.inv);
            $('#partnumber').data('aerosol', selectedData.aerosol);
            $('#quantity').trigger('focusout');

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
                buildCreditCardBillingStateList();
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
                defReasonCodeListLoaded.resolve();
                reasonCodeList = data;
                buildReasonLists('shippingreasoncode', 4, null);
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

        //Ship Methods
        buildShipMethodList();
		buildOrderBrandList();

        $('#nochargeorder').change(function () {

            var nochargeval = parseInt(getSelectedValue('nochargeorder'));
            var nocharge = isNaN(nochargeval) ? null : nochargeval;
                       
            if (nocharge == 1) {
                //$('#nocharge').prop('checked', true).trigger('change');
                $('#paymentmethod option[value="' + PAY_NO_CHARGE_ID + '"]').prop('selected', true);
                $('#paymentmethod').prop('disabled', true);
            } else {
                //$('#nocharge').prop('checked', false).prop('disabled',false).trigger('change');
                $('#paymentmethod option[value=""]').prop('selected', true);
                $('#paymentmethod').prop('disabled', false);
            }

            var nochargeval = parseInt(getSelectedValue('nochargeorder'));
            var nocharge = isNaN(nochargeval) ? -1 : nochargeval;
            o.setValue('NoChargeOrder', nocharge);
            
        });
		
		$('#orderbrand').change(function () {

            var orderbrandval = parseInt(getSelectedValue('orderbrand'));
            var orderbrand = isNaN(orderbrandval) ? null : orderbrandval;
            
            // var nochargeval = parseInt(getSelectedValue('nochargeorder'));
            // var nocharge = isNaN(nochargeval) ? -1 : nochargeval;
            o.setValue('OrderBrand', orderbrand);
            
        });

        //Ship method changed

        $('#shippingmethod').change(function (evt,isInit) {
            var shipMethodCurrentValue = getSelectedValue('shippingmethod');
            var shipMethodText = getSelectedText('shippingmethod');
            var noChargeCheckbox = $("#nocharge");
            var defaultPrice = $("#adjustedprice").val()
            var adjustedPrice = $("#unitprice").val();

            o.setValue('ShipMethod', shipMethodCurrentValue);
            
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

        });

        $('#nochargereasoncode').change(function () {
            //Check if serial number and model number are required for the selected shipping option
            var $selectedOption = $('#nochargereasoncode').find(":selected");

            if ($selectedOption.data('requiresserialnumber') == true) {

                toggleSerialNumber(true);

            } else {
                toggleSerialNumber(false);
            }

            if ($selectedOption.data('requiresmodelnumber') == true) {

                toggleModelNumber(true);

            } else {
                toggleModelNumber(false);
            }
        });

        //Payment Methods
        buildPaymentMethodList();

        $('#paymentmethod').change(function (e,showModal) {

            var selectedPaymentMethod = $('#paymentmethod').find(':selected');

            var payMethodCurrentValue = selectedPaymentMethod.val();
            payMethodCurrentValue = parseInt(payMethodCurrentValue) || -1;
            var payMethodText = selectedPaymentMethod.text();

            setTimeout(function () {
                o.setValue('PaymentType', payMethodCurrentValue);
            },1000);

            $('#setcreditcarddetails').addClass('hidden');
            $('#setpapercheckdetails').addClass('hidden');

            //Display the credit card payment dialog
            if (payMethodText == 'Credit Card') {

                setCreditCardAddressFromBillTo();

                if(showModal != false)
                $('#ccpaymentmodal').modal('show');

                $('#setcreditcarddetails').removeClass('hidden');

                if (hasValue(ccDetails.AccountNumber) == true) {
                    $('#setcreditcarddetails').removeClass('red');
                    $('#setcreditcarddetails').addClass('green');
                } else {
                    $('#setcreditcarddetails').removeClass('green');
                    $('#setcreditcarddetails').addClass('red');
                }
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

        //CC payment button
        $('#setcreditcarddetails').click(function () {

            setCreditCardAddressFromBillTo();
            setCreditCardAccountDetails();

            $('#ccpaymentmodal').modal('show');

        });

        //Set paper check details button
        $('#setpapercheckdetails').click(function () {

            setPapaerCheckDetails();

            $('#papercheckpaymentmodal').modal('show');

        });
    
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
            if (isFreeShipping()) {

                $('#shippingreasoncolumn').removeClass('hidden');
                $('#shippingreasoncode').attr('required', 'required');

                //Set the shipping cost to 0.00
                $("#shippingcost")
                    .val("0.00")
                    .attr("disabled","disabled")
                    .trigger("focusout");

            } else {

                $("#shippingcost").removeAttr("disabled");
                $('#shippingreasoncolumn').addClass('hidden');
                $('#shippingreasoncode').removeAttr('required');
                $('#shippingreasoncode option[value=""]').prop('selected', true);
                $('#shippingmethod').trigger("change");
            }

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

                SetOrderTotals();

            } else {

                if (!isFreeShipping()) {

                    $("#shippingcost").removeAttr("disabled");
                    $('#shippingreasoncolumn').addClass('hidden');
                    $('#shippingreasoncode').removeAttr('required');
                    $('#shippingreasoncode option[value=""]').prop('selected', true);
                    $('#shippingmethod').trigger("change");
                }
            }

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
                    }
                },
                messages: {
                    partnumber: {
                        required:"Select or enter a part number"
                    },
                    quantity: {
                        required: "Qty required",
                        min:"1 or greater"
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

        //Submit order button
        $('#submitorder').click(function (e) {

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
                        $.unblockUI();
                        return;
                    }

                    var orderValidationChecks = isOrderValid();
                    var continueWithOrder = true;

                    if (orderValidationChecks.orderValid == false) {
                        $.unblockUI();
                        alert(orderValidationChecks.message);
                        return;
                    }


                    if (continueWithOrder) {

                        var orderSubmitObj = getNewOrderSubmitObject();
                        orderSubmitObj.order.ContactId = currentCId;
                        orderSubmitObj.order.OnyxIndividualId = consumerId;
                        orderSubmitObj.order.BillingAddress = getAddressForOrderProcessing('BillTo');
                        orderSubmitObj.order.ShippingAddress = getAddressForOrderProcessing('ShipTo');
                        orderSubmitObj.order.LineItems = getLineItemsForOrderProcessing();
                        orderSubmitObj.order.SubTotal = orderTotals.subtotal;
                        orderSubmitObj.order.OrderTotal = orderTotals.grandtotal;
                        orderSubmitObj.order.OrderHeaderId = orderHeaderId;
                        orderSubmitObj.order.IncidentId = currentIId;
                        orderSubmitObj.order.ShipCode = getSelectedDataAttribute('shippingmethod', 'code');// getSelectedText('shippingmethod');
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
                        orderSubmitObj.order.OrderDate = new Date(Date.now()).toISOString();
                        orderSubmitObj.order.ShipAmount = getOrderAndShippingTotal().shipping;

                        orderSubmitObj.order.CRMPurchaseOrderID = orderHeaderId;
                        var salesChannelOverride = getSelectedDataAttribute('orderbrand', 'overridesaleschannel');
						if (salesChannelOverride){
							var salesChannelValue = getSelectedDataAttribute('orderbrand', 'saleschannel');
							orderSubmitObj.order.Channel = salesChannelValue;
						} else {
							orderSubmitObj.order.Channel = getChannelNameFromId(orderChannel);
						}

                        //Submit the order to the endpoint for processing
                        submitOrderForProcessing(orderSubmitObj, orderSubmitSuccessCallback);

                    }

                }
            });
        });

        //Submit credit card
        $('#submitcreditcard').click(function () {

            window.creditCardValidator = $("#creditcardform").validate({
                ignore:[],
                errorClass: 'validation-error',
                errorPlacement: function ($error, $element) {
                    var id = $element.attr("id");

                    $("#error_" + id).append($error);
                },
                rules: {
                    creditcardnumber: {
                        required: true,
                        creditcard: {
                            depends: function () {
                                $(this).val($.trim($(this).val()));
                                return true;
                            }
                        },
                        creditcardadditional:true
                    },
                    creditcardtype: {
                        cardtypematchesnumber: true,
                        required:true
                    },
                    expirationdate: {
                        cardexpirationdate: true
                    }

                },
                messages: {
                    nameoncreditcard: {
                        required: "Name on card is required"
                    },
                    creditcardnumber: {
                        required: "Credit card # required",
                        creditcard: "Enter a valid credit card #",
                        creditcardadditional: "Enter a valid credit card #"
                    },
                    creditcardexpirationmonth: {
                        required:"Required"
                    },
                    creditcardexpirationyear: {
                        required:"Required"
                    },
                    creditcardaddress1: {
                        required:"Address 1 required"
                    },
                    creditcardcity: {
                        required:"City required"
                    },
                    creditcardstate: {
                        required:"State required"
                    },
                    creditcardpostalcode: {
                        required:"Postal code required"
                    },
                    creditcardtype:{
                        required:"Credit card type required"
                    }

                }
            });

            window.creditCardValidator.form();

            if (window.creditCardValidator.valid() === false)
                return;

            //Credit card info is valid. Set the
            ccDetails.AccountNumber = $('#creditcardnumber').val();
            ccDetails.Name = $('#nameoncreditcard').val();
            ccDetails.Amount = getOrderAndShippingTotal().order;
            ccDetails.ExpMonth = $('#creditcardexpirationmonth').val();
            ccDetails.ExpYear = $('#creditcardexpirationyear').val();
            ccDetails.CardCode = getSelectedValue('creditcardtype');
            ccDetails.TransactionDate = new Date(Date.now()).toISOString();
            setStorageObject('ccDetails', ccDetails);

            //Change the button color and hide the window
            $('#setcreditcarddetails').removeClass('red');
            $('#setcreditcarddetails').addClass('green');
            
            $('#ccpaymentmodal').modal('hide');

        });

        $('#cancelcreditcard').click(function () {

            if (window.creditCardValidator)
                window.creditCardValidator.resetForm();
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

            o.setValue("PaymentCheckNumber", paperCheckDetails.CheckNumber);

            //Change the button color and hide the window
            $('#setpapercheckdetails').removeClass('red');
            $('#setpapercheckdetails').addClass('green');

            $('#papercheckpaymentmodal').modal('hide');

        });

        $('#cancelpapercheck').click(function () {

            if (window.paperCheckValidator)
                window.paperCheckValidator.resetForm();
        });

        //Custom credit card expiration validator
        jQuery.validator.addMethod("cardexpirationdate", function (value, element) {

            var valid = true;

            $('#creditcardexpirationmonth, #creditcardexpirationyear').off('change', checkExpirationValidation);

            //Determine the last day of the month
            var mm = getSelectedValue("creditcardexpirationmonth");
            var yy = getSelectedValue("creditcardexpirationyear");
            var jsMonth = parseInt(mm);
            var jsDay = 0;
            var jsYear = yy;
            var ccDate = new Date(jsYear, jsMonth, jsDay);
            var today = new Date();

            if (isNaN(ccDate.getTime()) || ccDate < today)
                valid = false;

            if (!valid)
                $('#creditcardexpirationmonth, #creditcardexpirationyear').on('change', checkExpirationValidation);

            return valid;

        }, 'Card expiration invalid');

        jQuery.validator.addMethod("creditcardadditional", function (value, element) {

            var valid = false;

            //Amex
            if ((value.substr(0, 2) == "34" || value.substr(0, 2) == "37") && value.length == 15)
                valid = true;

            //Discover
            if (value.substr(0, 4) == "6011" && value.length == 16)
                valid = true;

            //Mastercard
            if ((value.substr(0, 2) == "51" || value.substr(0, 2) == "52" || value.substr(0, 2) == "53" || value.substr(0, 2) == "54" || value.substr(0, 2) == "55") && value.length == 16)
                valid = true;

            //Visa
            if (value.substr(0, 1) == "4" && (value.length == 13 || value.length == 16))
                valid = true;

            return valid;

        }, 'Card number invalid');

    //Handler for checking expiration after it becomes invalid
        function checkExpirationValidation() {
            $('#expirationdate').valid();
        }

        jQuery.validator.addMethod("cardtypematchesnumber", function (value, element) {
            var valid = true;
            var typeFromNumber;
            var cardNumber = $("#creditcardnumber").val();
            var cardType = getSelectedValue("creditcardtype");

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

        jQuery.validator.addMethod("physicalshippingaddress", function (value, element) {
            var valid = true;
            if (requirePhysicalAddress == true && addressIsPOBox(getAddressForOrderProcessing('ShipTo')))
                valid = false;

            return valid;

        }, 'A physical shipping address is required');

        jQuery.validator.addMethod("partnumberexists", function (value, element) {
            var valid = true;
            if (findIndexOfObjWithAttr(lastItemSearchResponse, "name", value) == -1)
                valid = false;

            return valid;

        }, 'Enter a valid part number');

        $(document).ajaxStart(function () {
            $.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: {border:'none',backgroundColor:'transparent'}});
        });

        $(document).ajaxComplete(function () {
            $.unblockUI();
        });

});


// End Init //

//Declare Workspace event handlers

/**
 * Handle the onBeforeSave event.
 * Perform any data validation in this step
 */
function onbeforesave() {
    var success = true;
    var message = '';

    //If the page is locked, skip validation
    if (pageLocked == false) {

        setOrderHeaderValuesBeforeSave();
        setPaperCheckStatus();

        //Don't block the save
        window.external.beforesavecomplete(true, message);

    }

}

/**
 * This event fires after save is called from the workspace
 */
function onsave() {
    var success = true;
    var message;
    window.external.savecomplete(success, message);
}

/**
 * Called when the workspace is closed, but before it closes.
 */
function oncancel() {

    // Used to cancel a close operation (success = false)
    var success = true;
    var message;
    window.external.cancelcomplete(success, message);
}

/**
 * This function is called from the workspace when data on the workspace is changed
 * @param {type} obj
 */
function ondataupdated(obj) {
    
    if (obj == INCIDENT_OBJECT_NAME) // Modified object = Incident?
    {
        var currentAddrVal = addressValidated;
        var newAddrVal = i.GetCustomFieldByName("BUS$AddressValidated");

        if (newAddrVal == -1)
            addressValidated = null;

        if (currentAddrVal != newAddrVal && newAddrVal == 0) {
            performAddressValidation();
            
        } else {
            if (addressValidated != 1)
            addressValidated = i.GetCustomFieldByName("BUS$AddressValidated");
        }

        //The contact was changed
        if (i.CId != c.id) {
            currentCId = i.CId;
            setBillToShipToAddressFromContact(true);
            if (hasValue(c.EmailAddr))
                setLoyaltyStatus();
        }
    }

    
    if (obj == ORDER_HEADER_OBJECT_NAME) {

        //Check to see if the shipToCountry changed
        var selectedShipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
        var selectedBillToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
        var selectedShipToState = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
        var selectedBillToState = getCustomObjectField("BUS", "OrderHeader", "BillToState");

		if (!shipToPostal){
			shipToPostal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
		}
		
		var selectedShipToPostal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
		
		var shipToPostalSubstring = shipToPostal.substring(0,5).replace(" ", "").replace("-", "");
		var shipToPostalSelectedSubstring = selectedShipToPostal.substring(0,5).replace(" ", "").replace("-", "");
		
        //if the state changes invalidate the tax data
        if (shipToState != selectedShipToState || billToState != selectedBillToState) {
            salesTaxData = null;
            shipToState = selectedShipToState;
            billToState = selectedBillToState;
            SetOrderTotals();
        }
		
		if (shipToPostalSubstring != shipToPostalSelectedSubstring){
			if ((selectedShipToCountry == "1" && (selectedShipToPostal.replace(" ", "").replace("-", "").length == "5" || selectedShipToPostal.replace(" ", "").replace("-", "").length == "9")) || (selectedShipToCountry == "1" && selectedShipToPostal.replace(" ", "").replace("-", "").length == "6")){
				salesTaxData = null;
				shipToPostal = selectedShipToPostal;
				SetOrderTotals();
			}
		}

        if (shipToCountry != selectedShipToCountry) {
            shipToCountry = selectedShipToCountry;
            buildShipMethodList();
			buildOrderBrandList();
            toggleExpectedDeliveryDate();
        }

        if (billToCountry != selectedBillToCountry) {
            billToCountry = selectedBillToCountry;
            buildPaymentMethodList();
            buildCreditCardBillingStateList();
        }

    }

    setWorkspaceValues(true);
}

/**
 * Set values on the Workspace. This fires initially when the page loads
 */
function setWorkspaceValues(skipOHUpdate) {

    orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
    orderChannel = getCustomObjectField("BUS", "OrderHeader", "OrderChannel");
    shipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
    billToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
	shipToPostalCode = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");

    //If we have a billto, skip auto setting it
    var hasBillTo = addressHasValues("BillTo");
    var freeShipping = getCustomObjectField("BUS", "OrderHeader", "FreeShipping");
    var shippingTotal = getCustomObjectField("BUS", "OrderHeader", "ShippingTotal");
    var noCharge = getCustomObjectField("BUS", "OrderHeader", "NoChargeOrder");
    var shippingReason = getCustomObjectField("BUS", "OrderHeader", "ShipMethodReason");
    var expectedDeliveryDate = getCustomObjectField("BUS", "OrderHeader", "ExpectedDeliveryDate");
    var paymentMethod = getCustomObjectField("BUS", "OrderHeader", "PaymentType");

    var noChargeVal = "";
    if (noCharge == true)
        noChargeVal = "1";
    if (noCharge == false)
        noChargeVal = "0";

    if (!skipOHUpdate) {
        if (!hasBillTo)
            setBillToShipToAddressFromContact();
    }

    if (shipToCountry == UK_COUNTRY_ID && hasValue(expectedDeliveryDate)) {
        $('#expecteddeliverydate').datepicker("setDate",new Date(expectedDeliveryDate * 1000));
    }

    $('#ordernumber').val(orderNumber);

    if (!skipOHUpdate) {

        $('#nochargeorder option[value="' + noChargeVal + '"]').attr('selected', true).trigger('change');

        if (shippingReason > 0 || freeShipping == 1) {
            $('#shippingreasoncode option[value="' + shippingReason + '"]').attr('selected', true);
            $('#shippingreasoncolumn').removeClass('hidden');
        }

        if (freeShipping == 1) {
            $('#freeshipping').prop('checked', true);
            $('#shippingcost').prop('disabled','disabled');
        }

        if (hasValue(shippingTotal)) {
            $('#shippingcost').val(shippingTotal);
        }

        $('#paymentmethod option[value="' + paymentMethod + '"]').attr('selected', true);

    }

    toggleExpectedDeliveryDate();
    
}

/**
 * Set the loyalty status for the selected contact
 */
function setLoyaltyStatus() {

    $.ajax({
        type: "GET",
        url: LOYALTY_STATUS_ENDPOINT + '?sid=' + sid,
        data: { Email: c.EmailAddr },
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

function setOrderHeaderValuesBeforeSave() {

    var shipMethod = parseInt(getSelectedValue('shippingmethod'));
	var orderBrand = parseInt(getSelectedValue('orderbrand'));
    var paymentType = parseInt(getSelectedValue('paymentmethod'));
    var nochargeval = parseInt(getSelectedValue('nochargeorder'));
    var nocharge = isNaN(nochargeval) ? -1 : nochargeval;
    var shippingReason = 0;
    var expectedDeliveryDate = hasValue($('#expecteddeliverydate').val()) ? new Date($('#expecteddeliverydate').val()) : null;

    if (hasValue(getSelectedValue('shippingreasoncode'))) {
        shippingReason = parseInt(getSelectedValue('shippingreasoncode'));
    } else {
        shippingReason = 0;
    }

    if(shipMethod > 0)
        o.setValue('ShipMethod', shipMethod);
    
    if(paymentType > 0)
        o.setValue('PaymentType', paymentType);
	
	if(orderBrand > 0)
		o.setValue('OrderBrand', orderBrand);

    var freeShipping = isFreeShipping() == true ? 1 : 0;

    o.setValue('FreeShipping', freeShipping);

    o.setValue('Contact', parseInt(currentCId));

    o.setValue('NoChargeOrder', nocharge);

    o.setValue('ShipMethodReason', shippingReason);

    if (expectedDeliveryDate > 0) {
        var dte = expectedDeliveryDate.getTime() / 1000;
        o.setValue('ExpectedDeliveryDate', dte);
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


function getOrderAndShippingTotal() {

    var totals = {};
    var rows = window.OrderTable.rows().data() || [];
    var shipTotal = $('#shippingcost').val() || 0.00;
    var ordTotal = 0.00; //Order total includes shipping
    var subTotal = 0.00;
    var totalForShippingSelection = 0.00; //Determine the actual cost of the items even if a item is being offered for free

    if (rows.length > 0) {

        for (var i  = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.lineid != -2 && row.lineid != -1) {

                ordTotal = ordTotal + (parseFloat(row.adjustedprice) * parseInt(row.qty));

                if (row.nocharge) {
                    totalForShippingSelection = totalForShippingSelection + (parseFloat(row.unitsellingprice) * parseInt(row.qty));
                } else {
                    totalForShippingSelection = totalForShippingSelection + (parseFloat(row.adjustedprice) * parseInt(row.qty));
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

        var orderTotal = getCustomObjectField("BUS", "OrderHeader", "Total") || "0.00";
        var orderSubTotal = getCustomObjectField("BUS", "OrderHeader", "SubTotal") || "0.00";
        var orderSalesTaxTotal = getCustomObjectField("BUS", "OrderHeader", "Tax") || "0.00";
        var orderSecondarySalesTaxTotal = getCustomObjectField("BUS", "OrderHeader", "SecondaryTax") || "0.00";
        var shippingTotal = getCustomObjectField("BUS", "OrderHeader", "ShippingTotal") || "0.00";

        $('#ordersummary-subtotal').html(accounting.toFixed(orderSubTotal, 2));
        $('#ordersummary-tax').html(accounting.toFixed(orderSalesTaxTotal, 2));
        $('#ordersummary-tax2').html(accounting.toFixed(orderSecondarySalesTaxTotal, 2));
        $('#ordersummary-shipping').html(accounting.toFixed(shippingTotal, 2));
        $('#shippingcost').val(accounting.toFixed(shippingTotal, 2));
        $('#ordersummary-total').html(accounting.toFixed(orderTotal, 2));

        return;
    }

    var totals = getOrderAndShippingTotal();
    var salesTax = {primary:"0.00",secondary:"0.00"};
    var grandTotal = "0.00";

    if (totals.subtotal > 0)
        salesTax = getSalesTaxForOrder();

    var totalTax = parseFloat(salesTax.primary) + parseFloat(salesTax.secondary);

    grandTotal = accounting.toFixed(parseFloat(totals.order) + totalTax, 2);

    $('#ordersummary-subtotal').html(totals.subtotal);
    $('#ordersummary-tax').html(salesTax.primary);
    $('#ordersummary-tax2').html(salesTax.secondary);
    $('#ordersummary-shipping').html(totals.shipping);
    $('#ordersummary-total').html(grandTotal);

    o.setValue('Total', grandTotal);
    o.setValue('ShippingTotal', totals.shipping);
    o.setValue('Tax', salesTax.primary);
    o.setValue('SecondaryTax', salesTax.secondary);
    o.setValue('SubTotal', totals.subtotal);
    o.setValue('Contact', currentCId);

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
		
		// salesTaxData.Amount = accounting.toFixed((accounting.toFixed(ptax, 2) + accounting.toFixed(stax, 2)) , 2);

    }

    return salesTax;

}

function getSalesTaxForOrder() {
    /* Get the sales tax */
    if (contactIsTaxExempt())
        return { primary: 0.00, secondary: 0.00 };

    var billToAddress = getCurrentAddressInfoByType('BillTo');
    var shipToAddress = getCurrentAddressInfoByType('ShipTo');
    var salesTax = { primary: 0.00, secondary: 0.00 };
    var totals = getOrderAndShippingTotal();

    if (salesTaxData) {

        return getTaxAmntFromData();

    }

    var calcData = {
        sid: sid,
        BillAddress1: billToAddress.Address1,
        BillAddress2: billToAddress.Address2,
        BillCity: billToAddress.City,
        BillStateCode: billToAddress.StateCode,
        BillCountryCode: billToAddress.CountryCode,
        BillPostalCode: billToAddress.PostalCode,
        ShipAddress1: shipToAddress.Address1,
        ShipAddress2: shipToAddress.Address2,
        ShipCity: shipToAddress.City,
        ShipStateCode: shipToAddress.StateCode,
        ShipCountryCode: shipToAddress.CountryCode,
        ShipPostalCode: shipToAddress.PostalCode,            
        ShipAmount: 15, //totals.shipping,
        OrderTotal: totals.order,
        SubTotal: totals.subtotal,
        IncidentId: currentIId,
        OrderHeaderId: orderHeaderId,
        ContactId:currentCId

    };

    $.ajax({
        type: "POST",
        url: CUSTOM_SCRIPTS_PATH + 'calculatesalestax.php',
        async:false,
        data: calcData,
        dataType: "json"
    }).done(function (data) {                
        //Save the order line once we have the tax calculated.
        //salesTaxData = data;
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

    if (orderHasAerosol()) {
        buildShipMethodList(true);
    }
    else{
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
    var shipToState = getAddressForOrderProcessing("ShipTo").StateCode.toLowerCase();
    if (shipToState == 'ak' || shipToState == 'hi')
        return true;
    return false;
}

function shippingIsUSTerritory() {
    var shipTo = getAddressForOrderProcessing("ShipTo");
    var shipToCity = shipTo.City.toLowerCase();
    var shipToCountry = shipTo.CountryCode.toLowerCase();
    if (shipToCountry == 'us' && shipToStateIsCONUS() == false && shippingIsAlaskaHawaii() == false && shippingIsAPOMailbox() == false)
        return true;
    return false;
}

function shippingIsAPOMailbox() {
    var armedForcesStates = ["aa", "ae", "ap"];
    var shipToState = getAddressForOrderProcessing("ShipTo").StateCode.toLowerCase();
    if ($.inArray(shipToState, armedForcesStates) >= 0)
        return true;
    return false;
}

function shippingIsUK() {
    if (shipToCountry == UK_COUNTRY_ID)
        return true;
    return false;
}

function shippingIsCanada() {
    if (shipToCountry == CA_COUNTRY_ID)
        return true;
    return false;
}

function shippingIsUS() {
    if (shipToCountry == US_COUNTRY_ID)
        return true;
    return false;
}

function shippingIsAustralia() {
    if (shipToCountry == AU_COUNTRY_ID)
        return true;
    return false;
}

function shippingIsNewZealand() {
    if (shipToCountry == NZ_COUNTRY_ID)
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

    var paymentInfo = {
        AccountNumber: ccDetails.AccountNumber,
        Amount: orderTotals.grandtotal,
        Name: ccDetails.Name,
        ExpMonth: ccDetails.ExpMonth,
        ExpYear: ccDetails.ExpYear,
        CVC: ccDetails.CVC,
        CardCode: ccDetails.CardCode,
        TransactionDate:new Date(Date.now()).toISOString()
    };

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

    var address = getCurrentAddressInfoByType(addressType);

    var responseAddress = {
        EmailAddress: c.EmailAddr,
        Name: address.CustomerName,
        FirstName: address.CustomerFirstName,
        LastName: address.CustomerLastName,
        Address1: address.Address1,
        Address2: address.Address2,
        City: address.City,
        StateCode: address.StateCode,
        CountryCode: address.CountryCode,
        PostalCode: address.PostalCode
    }

    return responseAddress;
}

//TODO: get free shipping status
function isFreeShipping(){
    return $("#freeshipping").is(":checked");
}

    /**
     * Check to see if the form is valid.
     */
    function performAddressValidation() {

        var isValid = true;
        var formValidResponse = {valid:true,message:""};
        //Check billto address
        var billToAddress = getCurrentAddressInfoByType('BillTo');
        var shipToAddress = getCurrentAddressInfoByType('ShipTo');
        var billToValidatedResponse,shipToValidatedResponse;

        billToValidatedResponse = validateAddress(billToAddress);
        shipToValidatedResponse = validateAddress(shipToAddress);

        //If the validation returns 1- unknown error, return, but don't prevent saving
        if (billToValidatedResponse.Code == "-1") {
            formValidResponse.valid = true;
            formValidResponse.message = "Currently unable to validate addresses.";
            return formValidResponse;
        }

        if (billToValidatedResponse.Code == "2" || billToValidatedResponse.Code == "1") {

            var fbt = formatAddress(billToValidatedResponse.Validated);
            var useSuggestedBillTo = confirm("A different bill to address was suggested.\n Click OK to use this address:\n\n" + fbt);

            if (useSuggestedBillTo) {
                var btAddr = billToValidatedResponse.Validated;
                setAddressFromSuggestion('BillTo', btAddr.Address1, btAddr.Address2, btAddr.City, btAddr.CountryCode, btAddr.StateCode, btAddr.PostalCode);
            }
        }
        if (shipToValidatedResponse.Code == "2" || shipToValidatedResponse.Code == "1") {

            var fst = formatAddress(shipToValidatedResponse.Validated);
            var useSuggestedShipTo = confirm("A different ship to address was suggested.\n Click OK to use this address:\n\n" + fst);

            if (useSuggestedShipTo) {
                var stAddr = shipToValidatedResponse.Validated;
                setAddressFromSuggestion('ShipTo', stAddr.Address1, stAddr.Address2, stAddr.City, stAddr.CountryCode, stAddr.StateCode, stAddr.PostalCode);
            }
        }

        if (!(billToValidatedResponse.Code == "1" || billToValidatedResponse.Code == "2")) {
            isValid = false;
            formValidResponse.valid = isValid;
            formValidResponse.message = 'Invalid Bill To address: ' + billToValidatedResponse.Message;
        }

        if (!(shipToValidatedResponse.Code == "1" || shipToValidatedResponse.Code == "2")) {
            isValid = false;
            formValidResponse.valid = isValid;
            formValidResponse.message = formValidResponse.message + '\n' + 'Invalid Ship To address: ' + shipToValidatedResponse.Message;
        }

        if (hasValue(formValidResponse.message)) {
            alert(formValidResponse.message);
        } else {
            alert('Address validation complete');
        }            

        addressValidated = 1;
        setTimeout(function () {
            i.SetCustomFieldByName("BUS$AddressValidated", 1);
        }, 500)
        
    }

    /**
     * Get the BillTo or ShipTo address from the workspace for use in the address validation calls
     * @param {string} type
     * @returns {address} 
     */
    function getCurrentAddressInfoByType(type) {

        var address = {};
        var customerName = getCustomObjectField("BUS", "OrderHeader", type + "CustomerName");
        var customerNameArray = [];

        address.CustomerFirstName = '';
        address.CustomerLastName = '';

        if (hasValue(customerName)) {
            customerNameArray = customerName.split(' ');
        }

        if (customerNameArray.length > 0)
            address.CustomerFirstName = customerNameArray[0];
        if (customerNameArray.length > 1)
            address.CustomerLastName = customerNameArray[1];

        address.CustomerName = getCustomObjectField("BUS", "OrderHeader", type + "CustomerName")
        address.Address1 = getCustomObjectField("BUS", "OrderHeader", type + "Address1");
        address.Address2 = getCustomObjectField("BUS", "OrderHeader", type + "Address2");
        address.City = getCustomObjectField("BUS", "OrderHeader", type + "City");
        address.PostalCode = getCustomObjectField("BUS", "OrderHeader", type + "PostalCode");
        address.CountryCodeId = getCustomObjectField("BUS", "OrderHeader", type + "Country");
        address.CountryCode = getCountryISONameById(address.CountryCodeId);
        address.StateCode = getProvinceAbbreviation(address.CountryCodeId, getNameFromId("AddrProvId", getCustomObjectField("BUS", "OrderHeader", type + "State")));
        address.StateCodeId = getCustomObjectField("BUS", "OrderHeader", type + "State");

        return address;
    }

    /**
     * Determine if an address is valid. This is used primarily before submitting an order as a final check
     * @param {string} addressType (BillTo|ShipTo)
     * @param {type} friendlyName - Message friendly name used in error messages
     * @returns {type} 
     */
    function isAddressValid(addressType,friendlyName) {

        var address = getCurrentAddressInfoByType(addressType);
        var valMessage = '';

        validatedResponse = validateAddress(address);

        //If we get back -1 as the code, some other issue happened with the validation. Set it as valid, but send back a message
        if (validatedResponse.Code == "-1") {
            return {
                valid: true,
                message: friendlyName + ' error: ' + validatedResponse.Message + '\n',
                validateResponse: validatedResponse
            };
        }

        if (validatedResponse.Code == "2") { 
            return {
                valid: false,
                message: friendlyName + ' error: A different address is being suggested\n',
                validateResponse: validatedResponse
            };
        }

        if (!(validatedResponse.Code == "1" )) { // A -1 response means there was an issue with the call to validate the address
            return {
                valid: false,
                message: friendlyName + ' error: ' + validatedResponse.Message + '\n',
                validateResponse:validatedResponse
            };
        }



        if (validatedResponse.Code == "3") { // A -1 response means there was an issue with the call to validate the address
            return {
                valid: false,
                message: friendlyName + ' error: Multiple possible addresses found\n',
                validateResponse: validatedResponse
            };
        }

        return {
            valid: true,
            message: '',
            validateResponse: validatedResponse
        };
    }

    function setCreditCardAddressFromBillTo() {
        
        var addressInfo = getCurrentAddressInfoByType('BillTo');

        $('#nameoncreditcard').val(addressInfo.CustomerName);
        $('#creditcardaddress1').val(addressInfo.Address1);
        $('#creditcardaddress2').val(addressInfo.Address2);
        $('#creditcardcity').val(addressInfo.City);
        $('#creditcardpostalcode').val(addressInfo.PostalCode);
        $('#creditcardstate option[value="' + addressInfo.StateCodeId + '"]')
                        .attr('selected', true)
                        .trigger('change');
    }

    function setCreditCardAccountDetails() {

        if (hasValue(ccDetails.AccountNumber) == true) {
            $('#creditcardnumber').val(ccDetails.AccountNumber);
            $('#nameoncreditcard').val(ccDetails.Name);

            $('#creditcardexpirationmonth option[value="' + ccDetails.ExpMonth + '"]')
            .attr('selected', true);

            $('#creditcardexpirationyear option[value="' + ccDetails.ExpYear + '"]')
            .attr('selected', true);

            //$('#creditcardcvv').val(ccDetails.CVC);

            $('#creditcardtype option[value="' + ccDetails.CardCode + '"]')
                        .attr('selected', true)
        }
    }

    function setPapaerCheckDetails() {

        var checkNumber = getCustomObjectField("BUS", "OrderHeader", "PaymentCheckNumber") || paperCheckDetails.CheckNumber;

        if (hasValue(checkNumber) == true) {
            paperCheckDetails.CheckNumber = checkNumber;
            $('#paperchecknumber').val(checkNumber);
        }
    }

    function setPaperCheckStatus() {

        var paymentType = getSelectedText('paymentmethod');
        var checkNumber = getCustomObjectField("BUS", "OrderHeader", "PaymentCheckNumber");

        if (paymentType.toLowerCase() == 'paper check' && hasValue(checkNumber) == false) {
            i.Status = INC_STATUS_ID_CLOSED;
            i.Disposition = INC_DISP_ID_WAITING_CHECK;
            setCustomObjectField('BUS', 'OrderHeader', 'Status', 'Pending');
        }
    }

    /**
     * Set the BillTo and ShipTo address on the workspace
     */
    function setBillToShipToAddressFromContact(override) {

        var hasBillTo = addressHasValues("BillTo");
        var hasShipTo = addressHasValues("ShipTo");

        if ((hasBillTo && hasShipTo) && override != true)
            return;

        var c = window.external.Contact;
        if (c == null || c.Id == 0) {
            return;
        }

        //The default contact address is used as the BillTo
        var $jqAddr = c.AddrStreet;

        var customerName = "",
            addr1 = "",
            addr2 = "",
            city = "",
            street = "",
            zip = "",
            proId = "",
            countryId = ""

        if (c.AddrStreet != undefined) {
            var addressArray = $jqAddr.split("\n");
            addr1 = addressArray[0];
            if(addressArray.length > 1)
                addr2 = addressArray[1];
        }

        customerName = c.FullName || customerName;
        street = c.AddrStreet || street;
        city = c.AddrCity || city;
        zip = c.AddrPostalCode || zip;
        proId = c.AddrProvId || proId;
        countryId = c.AddrCountryId || countryId;

        //Bill To
        if (!hasBillTo) {

            o.setValue('BillToCustomerName', customerName);
            o.setValue('BillToAddress1', addr1);
            o.setValue('BillToAddress2', addr2);
            o.setValue('BillToCity', city);
            o.setValue('BillToCountry', countryId);
            setTimeout(function () {
                o.setValue('BillToState', proId);
            },2000);

            o.setValue('BillToPostalCode', zip);

            //Set the default credit card payment fields also
            setCreditCardAddressFromBillTo();

        }

        if (!hasShipTo) {

            //Check if the Ship To address is set. If not, set the ship using the bill to values
            var shipToAddress1 = c.GetCustomFieldByName("BI$ship_street") || "";
            var shipToAddress2 = c.GetCustomFieldByName("BI$ship_street2") || "";
            var shipToCity = c.GetCustomFieldByName("BI$ship_city") || "";
            var shipToState = c.GetCustomFieldByName("BI$ship_state") || "";
            var shipToPostalCode = c.GetCustomFieldByName("BI$ship_postalcode") || "";
            var shipToCountry = c.GetCustomFieldByName("BI$ship_country") || "";

            if (shipToAddress1 == null || shipToAddress1 == undefined || shipToAddress1 == '') {
                shipToAddress1 = addr1;
                shipToAddress2 = addr2;
                shipToCity = city;
                shipToState = proId;
                shipToPostalCode = zip;
                shipToCountry = countryId;
            }

            //Ship To
            o.setValue('ShipToCustomerName', customerName);
            o.setValue('ShipToAddress1', shipToAddress1);
            o.setValue('ShipToAddress2', shipToAddress2);
            o.setValue('ShipToCity', shipToCity);
            o.setValue('ShipToCountry', shipToCountry);
            setTimeout(function () {
                o.setValue('ShipToState', shipToState);
            }, 2000);

            o.setValue('ShipToPostalCode', shipToPostalCode);

        }

    }

    function setAddressFromSuggestion(addresstype, address1, address2, city, countryiso, statecode, postalcode) {

        var countryid = getCountryIdByISO(countryiso);
        var stateid = getProvinceIDByCountryId(countryid, statecode);
        stateid = parseInt(stateid) || null;

        setAddress(addresstype, address1, address2, city, countryid, stateid, postalcode);

    }

    function setAddress(addressType, address1, address2, city, countryid, stateid, postalCode) {

        o.setValue(addressType + 'Address1', address1);
        o.setValue(addressType + 'Address2', address2);
        o.setValue(addressType + 'City', city);
        o.setValue(addressType + 'Country', countryid);
        setTimeout(function () {
            o.setValue(addressType + 'State', stateid);
        },2000);
        o.setValue(addressType + 'PostalCode', postalCode);

    }


    /**
     * Set the billto and shipto address after address validation
     * @param {type} billto
     * @param {type} shipto
     */
    function setBillToShipToAddressFromAddressValidation(billto,shipto) {

        //Bill To
        o.setValue('BillToAddress1', billto.Address1 + '\n' + billtoAddress2);
        o.setValue('BillToCity', billto.City);
        o.setValue('BillToCountry', billto.CountryCode);
        setTimeout(function () {
            o.setValue('BillToState', billto.StateCode);
        },2000);

        o.setValue('BillToPostalCode', billto.PostalCode);

        //Ship To
        o.setValue('ShipToAddress1', shipto.Address1 + '\n' + shiptoAddress2);
        o.setValue('ShipToCity', shipto.City);
        o.setValue('ShipToCountry', shipto.CountryCode);
        setTimeout(function () {
            o.setValue('ShipToState', shipto.StateCode);
        },2000);

        o.setValue('ShipToPostalCode', shipto.PostalCode);

    }

    function logSysIntegration(extSystem, requestMessage, responseMessage, error, description) {

        var info = {};
        info.ExtSystem = extSystem;
        info.RequestMsg = requestMessage;
        info.ResponseMsg = responseMessage;
        info.Error = error;
        info.Description = description;
        info.IncidentID = currentIId;
        info.ContactID = currentCId;
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

				setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");

				setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
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

            setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
            pageLocked = true;

            disableSubmittedOrder();

            if (data.OrderCode) {
                if (data.OrderCode == 28) {
                    setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
                } else {
                    setCustomObjectField('BUS', 'OrderHeader', 'Status', "Failed");
                }
                
            } else {
                setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
            }
            setOrderHeaderValuesBeforeSave();
            $.unblockUI();
            return;
        }
        
		//Check for OrderCode other than known codes
        if (!(data.OrderCode.toLowerCase() === 'paymentauthorizationfailed' || data.OrderCode.toLowerCase() === 'success' || data.OrderCode.toLowerCase() === 'failure')) {

            var message = data.AgentMessage || "An error occurred while attempting to submit the order.";

            alert(message);

            setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
            pageLocked = true;

            disableSubmittedOrder();
                        
            setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
            setOrderHeaderValuesBeforeSave();
            $.unblockUI();
            return;
        }
		
		 //Failure that an agent can respond to
        if (data.OrderCode.toLowerCase() === 'paymentauthorizationfailed') {

            var message = data.AgentMessage;

            alert(message);

            setCustomObjectField('BUS', 'OrderHeader', 'Status', "Credit Card Declined");

            setOrderHeaderValuesBeforeSave();

            $.unblockUI();

            return;
        }
		
		// Failure that an agent cannot respond to
        if (data.OrderCode.toLowerCase() === 'failure') {

            var message = data.AgentMessage || "An error occurred while attempting to submit the order.";

            alert(message);

            setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
            pageLocked = true;

            disableSubmittedOrder();

            setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
            setOrderHeaderValuesBeforeSave();
            $.unblockUI();
            return;
        }

        //Successful order
        if (data.OrderCode.toLowerCase() === 'success') {

            //Update the order
            alert('Order created successfully. The order # is ' + data.OrderNumber);

            //var paymentType = getSelectedText('paymentmethod');

            $('#ordernumber').val(data.OrderNumber);

            orderNumber = data.OrderNumber;

            setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
            pageLocked = true;

            disableSubmittedOrder();

            setCustomObjectField('BUS', 'OrderHeader', 'OrderNumber', data.OrderNumber);
            setCustomObjectField('BUS', 'OrderHeader', 'Status', "Submitted");

            setOrderHeaderValuesBeforeSave();

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
        var validationResponse = { billToValidationResponse:null,shipToValidationResponse:null, addressValid: true, orderValid: true, message: "", addressMessage: "" };
        var totals = getOrderAndShippingTotal();
        var shippingTotal = totals.shipping;
        var orderTotal = totals.order;
        //Validate the lines
        var linesValidation = validateOrderLines();
        isvalid = linesValidation.valid;
        message = message + linesValidation.message;

        var email = c.EmailAddr;
        var billToName = getCustomObjectField("BUS", "OrderHeader", "BillToCustomerName");
        var shipToName = getCustomObjectField("BUS", "OrderHeader", "ShipToCustomerName");
        var billToAddress1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
        var shipToAddress1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
        var billToCity = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
        var shipToCity = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
        var billToState = getCustomObjectField("BUS", "OrderHeader", "BillToState");
        var shipToState = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
        var billToPostal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
        var shipToPostal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
		var orderBrand = getSelectedText('orderbrand');

        if (!hasValue(billToName)) {
            message = message + 'Enter a Bill To Name \n\n';
            isvalid = false;
        }

        if (!hasValue(shipToName)) {
            message = message + 'Enter a Ship To Name \n\n';
            isvalid = false;
        }

        if (!hasValue(billToAddress1)) {
            message = message + 'Enter a Bill To Address \n\n';
            isvalid = false;
        }

        if (!hasValue(shipToAddress1)) {
            message = message + 'Enter a Ship To Address \n\n';
            isvalid = false;
        }

        if (!hasValue(billToCity)) {
            message = message + 'Enter a Bill To City \n\n';
            isvalid = false;
        }

        if (!hasValue(shipToCity)) {
            message = message + 'Enter a Ship To City \n\n';
            isvalid = false;
        }

        if (!hasValue(billToPostal)) {
            message = message + 'Enter a Bill To Postal Code \n\n';
            isvalid = false;
        }

        if (!hasValue(shipToPostal)) {
            message = message + 'Enter a Ship To Postal Code \n\n';
            isvalid = false;
        }

        if (!hasValue(billToState) && !(shipToCountry == UK_COUNTRY_ID)) {
            message = message + 'Enter a Bill To State/Province \n\n';
            isvalid = false;
        }

        if (!hasValue(shipToState) && !(shipToCountry == UK_COUNTRY_ID)) {
            message = message + 'Enter a Ship To State/Province \n\n';
            isvalid = false;
        }

        if (addressValidated != 1) {
            message = message + 'Validate addresses before placing the order \n\n';
            isvalid = false;
        }

        //Shipping Check:
        if (orderHasAerosol() && (shippingIsUSTerritory() || shippingIsAPOMailbox() || shippingIsAlaskaHawaii())) {
            message = message + 'One or more items cannot be shipped to this customer to this location \n\n';
            isvalid = false;
        }

        //If the ship to country is the UK, the ship promise date is required
        if (shipToCountry == UK_COUNTRY_ID) {

            var shipPromise = $('#expecteddeliverydate').val();

            if (hasValue(shipPromise) == false) {
                message = message + 'The expected delivery date is required for UK orders\n\n';
                isvalid = false;
            }
        }

        //Check that consumer order is selected
        if (orderType != ORDER_TYPE_CONSUMER_ID) {
            message = message + 'Select the correct Order Type\n\n';
            isvalid = false;
        }

        if (isNoChargeOrder() && (shippingTotal > 0 || orderTotal > 0)) {
            isvalid = false;
            message = message + "'No Charge Order' was selected, but there is a balance.\n\n";
        }

        if (paymentType.toLowerCase() == 'credit card') {

            if (hasValue(ccDetails.AccountNumber) == false) {
                isvalid = false;
                message = message + "Enter credit card details for this order\n\n";
            }

            //if (!$.validator.methods.creditcard.call(this, $("#creditcardnumber").val(), document.getElementById("creditcardnumber"))) {
            //    isvalid = false;
            //    message = message + "Credit card number is invalid\n\n";
            //}
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

        validationResponse.orderValid = isvalid;
        validationResponse.addressValid = addressValid;
        validationResponse.message = message;
        validationResponse.addressMessage = addressMessage;

        return validationResponse;
    }

    /**
     * Determine if an address has any values already set
     * @param {type} addressType (BillTo | ShipTo)
     * @returns boolean
     */
    function addressHasValues(addressType) {

        var address = getCurrentAddressInfoByType(addressType);

        if(hasValue(address.Address1) == false && 
            hasValue(address.Address2) == false && 
            hasValue(address.City) == false && 
            hasValue(address.CountryCode) == false && 
            hasValue(address.StateCode) == false &&
            hasValue(address.PostalCode) == false)
            return false;
        return true;
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
        orderLine.Total = getCurrentLineTotal();//accounting.toFixed(parseFloat(getCurrentLineTotal()) + parseFloat(orderLine.Shipping) + parseFloat(taxTotal),2); //Including tax and shipping for the line
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
        orderLine.OrderType = orderType;
        orderLine.OrderNumber = $("#ordernumber").val();
        orderLine.Line = getNextLineNumber();
        orderLine.UnitOfMeasure = $("#uom").val();
        orderLine.Inventory = $("#partnumber").data("inv");
        orderLine.NoCharge = $("#nocharge").is(":checked") == true ? 1:0;
        orderLine.Aerosol = YesNoStringToBool($("#partnumber").data("aerosol"));
        orderLine.ReasonCode = getSelectedValue('nochargereasoncode');
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

    /**
     * Validate lines before submit
     */
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

                    if (shipToCountry == US_COUNTRY_ID) {

                        if (isAirFreight == true) {
                            valid = false;
                            message = message + "One or more items cannot be shipped via air freight.\n\n";

                        } else {
                            
                            if (shipToCountry == US_COUNTRY_ID && shipToStateIsCONUS() == false) {
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

    function contactIsTaxExempt() {
        var taxStatus = c.GetCustomFieldByName("BI$tax_exempt");
        if (taxStatus > 0)
            return true;
        return false;
    }

/* Object Helpers */

    function disableSubmittedOrder() {

        if (pageLocked) {
            $('input, textarea, table, select, button').attr('disabled', 'disabled');
            $('.editor_remove').remove();
            SetOrderTotals();
        };
    }

    function shipToStateIsCONUS() {

        var shipToState = getNameFromId("AddrProvId", getCustomObjectField("BUS", "OrderHeader", "ShipToState"));;

        for (var key in conusList) {
            if (conusList.hasOwnProperty(key)) {
                if (key == shipToState)
                    return true;
            }
        }

        return false;
    }

    function setRequiredPhysicalAddress(shipMethodCurrentValue) {

        requirePhysicalAddress = false;

        if (($.inArray(shipMethodCurrentValue, [SHIP_FEDEX2DAY_ID, SHIP_FEDEX_GROUND_ID, SHIP_FEDEX_OVERNIGHT_ID]) >= 0)) {
            requirePhysicalAddress = true;
        }

        return requirePhysicalAddress;

    }


    /**
     * Get a custom object by package and object name
     * @param {type} packageName
     * @param {type} objectName
     * @returns {type} 
     */
    function getCustomObject(packageName, objectName) {
        // Get the Custom Object instance
        var co = window.external.GetCustomObject(packageName, objectName);

        // If the Custom Object is not found on the system or if there is no 
        // record of that type currently being edited, then co will be null.
        if (co == null) {
            alert("No Custom Object " + packageName + "$" + objectName + " found");
        }

        return co;
    }

    /**
     * Get a custom object field
     * @param {type} packageName
     * @param {type} objectName
     * @param {type} fieldName
     * @returns {type} 
     */
    function getCustomObjectField(packageName, objectName, fieldName) {
        // Get the Custom Object instance
        co = getCustomObject(packageName, objectName);
        var cf = co.GetCustomFieldByName(fieldName);

        return co.GetCustomFieldByName(fieldName);
    }

    /**
     * Set a custom object field
     * @param {type} packageName
     * @param {type} objectName
     * @param {type} fieldName
     * @param {type} value
     */
    function setCustomObjectField(packageName, objectName, fieldName, value) {
        // Get the Custom Object instance
        co = getCustomObject(packageName, objectName);

        // Use the standard SetCustomFieldByName call to set the field value.
        co.SetCustomFieldByName(fieldName, value);
    }

    /**
     * Get a menu item id by name for a specific list
     * @param {type} list
     * @param {type} name
     * @returns {type} 
     */
    function getIdFromName(list, name, object) {
        //Default to the contact object
        var o = window.external.Contact;

        if (object) {
            o = object;
        }
            var xml = o.GetNameValues(list);

            var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
            xmlDoc.async = "false";
            xmlDoc.loadXML(xml);

            var x = xmlDoc.getElementsByTagName("Item");
            for (var i = 0; i < x.length; i++) {
                var attlist = x.item(i).attributes;
                var att = attlist.getNamedItem("Name");
                if (att.value == name)
                    return parseInt(attlist.getNamedItem("Id").value);
            }

            return -1;
        }

        /**
         * Get a menu item name by if for a specific list
         * @param {type} list
         * @param {type} id
         * @returns {type} 
         */
    function getNameFromId(list, id, object) {

            var o = window.external.Contact;

            if (object) {
                o = object;
            }

            var xml = o.GetNameValues(list);

            if (id < 1)
                return '';

            var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");

            xmlDoc.async = "false";

            xmlDoc.loadXML(xml);

            var x = xmlDoc.getElementsByTagName("Item");

            for (var i = 0; i < x.length; i++) {
                var attlist = x.item(i).attributes;
                var att = attlist.getNamedItem("Id");
                if (att.value == id)
                    return attlist.getNamedItem("Name").value;
            }

            return '';
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

        function getStorageObject(key) {
            var storageVal = localStorage.getItem(key);
            if (storageVal != null) {
                return JSON.parse(storageVal);
            }
            return null
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

            if (shipToCountry == UK_COUNTRY_ID) {

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
                        //buildReasonLists('shippingreasoncode', 4, REASON_WAIVE_PROMOTION);
                        
                    }

                }
            }

            $('#shippingcost').data('autoval', shippingCharge);
            $('#shippingcost').val(shippingCharge);

        }

        function toggleShippingReasonByCost() {

            var cost = $('#shippingcost').val() || 0;
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
                var selectedShipMethod = getCustomObjectField("BUS", "OrderHeader", "ShipMethod");
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

                if (hasValue(selectedShipMethod)) {

                    $('#shippingmethod option[value="' + selectedShipMethod + '"]')
                        .attr('selected', true)
                        .trigger('change',true);
                }
            };

            if(reset === true){

                setOptions(window.menulists.shipping_methods);

            } else {

                $.ajax({
                    type: "GET",
                    url: AJAX_ENDPOINT + '?action=getshipmethods&ordertype=' + orderType + '&country=' + shipToCountry + '&sid=' + sid,
                    cache: true,
                    success: function (data) {

                        if (window.menulists == undefined)
                            window.menulists = {};

                        window.menulists.shipping_methods = data;

                        setOptions(data);
                    
                    },
                    dataType: "json"
                });
            }
        }
		
		function buildOrderBrandList(reset){
			var setOptions = function (data) {
                
				var valincidentbrand = i.GetCustomFieldByName("c$brand");
				
				var orderbrandset = false;
				var orderbrandid = 0;
				
				//Append blank options
                var blankOption = "<option value=''>[+]</option>";
                var selectedOrderBrand = getCustomObjectField("BUS", "OrderHeader", "OrderBrand");
                
				if (pageLocked == false){
					$('#orderbrand')
                    .empty();
				} else {
					$('#orderbrand')
                    .empty()
                    .append($(blankOption));
				}
				
                $.each(window.menulists.order_brands, function (index, orderBrand) {
                    
                    if (pageLocked || (!pageLocked && orderBrand.active == true)){
						var option = $("<option></option>");
						option.attr("value", orderBrand.id)
						.text(orderBrand.label)
						.data("overridesaleschannel", orderBrand.overridesaleschannel)
						.data("bwssubmitorderconfig", orderBrand.bwssubmitorderconfig)
						.data("saleschannel", orderBrand.saleschannel);

						$('#orderbrand').append(option);
						
						if (orderBrand.incidentbrand){
							if (parseInt(orderBrand.incidentbrand) == valincidentbrand)
							{
								orderbrandset = true;
								orderbrandid = orderBrand.id;
							}
						}
					}
					
                });

                if (hasValue(selectedOrderBrand)) {

                    $('#orderbrand option[value="' + selectedOrderBrand + '"]')
                        .attr('selected', true);
                        //.trigger('change',true);
						} else if (orderbrandset) {
					$('#orderbrand option[value="' + orderbrandid + '"]')
                        .attr('selected', true);
                }
            };

            if(reset === true){

                setOptions(window.menulists.order_brands);

            } else {

                $.ajax({
                    type: "GET",
                    url: AJAX_ENDPOINT + '?action=getorderbrands&country=' + shipToCountry + '&sid=' + sid,
                    cache: true,
                    success: function (data) {

                        if (window.menulists == undefined)
                            window.menulists = {};

						var tempval = 'placeholder';
						
                        window.menulists.order_brands = data;

                        setOptions(data);
                    
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

        }

        function buildCreditCardBillingStateList() {

            var stateList = getListValues('AddrProvId');

            var billToCountryStateList = getProvinceListForCountryId(billToCountry);
            $('#creditcardstate').empty();
            $('#creditcardstate').append("<option value=''>[+]</option>");

            if (Object.keys(billToCountryStateList).length > 0) {

                $.each(billToCountryStateList, function (value, text) {

                    $('#creditcardstate').append($("<option></option>")
                    .attr("value", value)
                    .text(text));
                });

            } else {
                $.each(stateList, function (index, stateItem) {

                    $('#creditcardstate').append($("<option></option>")
                    .attr("value", stateItem.id)
                    .text(stateItem.label));
                });
            }
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
            $.ajax({
                type: "GET",
                url: AJAX_ENDPOINT + '?action=getpaymentmethods&country=' + billToCountry + '&sid=' + sid,
                cache: false,
                success: function (data) {
                    if (window.menulists == undefined)
                        window.menulists = {};
                    window.menulists.payment_methods = data;

                    //Append blank options
                    var blankOption = "<option value=''>[+]</option>";
                    var selectedPaymentType = getCustomObjectField("BUS", "OrderHeader", "PaymentType");

                    $('#paymentmethod')
                        .empty()
                        .append($(blankOption));

                    $.each(window.menulists.payment_methods, function (id, label) {

                        var option = $("<option></option>");
                        option.attr("value", id)
                        .text(label);

                        if (id == PAY_NO_CHARGE_ID) {
                            option.prop('disabled', true);
                        }

                        $('#paymentmethod').append(option);

                        if (id == selectedPaymentType) {
                            $('#paymentmethod option[value="' + id + '"]')
                                .attr('selected', true)
                                .trigger('change', false);
                        }
                    });

                    defPaymentMethodListLoaded.resolve();
                },
                dataType: "json"
            });
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
  

