/* Workspace Interactions */
debugger;
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
var c = window.external.Contact;
var i = window.external.Incident;
var o = new CXObject('BUS.OrderHeader');
var currentCId = getParameterByName('cid') || i.CId;
var consumerId = getParameterByName('consumer_id');
var currentIId = getParameterByName('iid') || i.IId;
var orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
var orderChannel = getCustomObjectField("BUS", "OrderHeader", "OrderChannel");
var shipToCountry = hasValue(getCustomObjectField("BUS", "OrderHeader", "ShipToCountry")) == true ? getCustomObjectField("BUS", "OrderHeader", "ShipToCountry") : c.GetCustomFieldByName("BI$ship_country");
var billToCountry = hasValue(getCustomObjectField("BUS", "OrderHeader", "BillToCountry")) == true ? getCustomObjectField("BUS", "OrderHeader", "BillToCountry") : c.AddrCountryId;
var billToValid = false;
var shipToValid = false;
var lineNumber = 0;
var orderNumber = '';
var returnAuthorizationNumber = getCustomObjectField("BUS", "OrderHeader", "RANumber") || null;
var trackingNumber = getCustomObjectField("BUS", "OrderHeader", "RATrackingNumber") || null;
var orderHeaderId = getParameterByName('oid'); //The only way to get the BUS.OrderHeader.ID is to pass it in the URL.
var sid = getParameterByName('sid'); //Agent session id
var orderTotal = getCustomObjectField("BUS", "OrderHeader", "Total") || "0.00";
var orderSalesTaxTotal = 0.00;
var shippingTotal = getCustomObjectField("BUS", "OrderHeader", "ShippingTotal") || "0.00";
var hasManualShippingOverride = false;
var ccDetails = getStorageObject('ccDetails') || {};
var paperCheckDetails = getStorageObject('paperCheckDetails') || {};
var countryList = [];
var provinceList = [];
var provinceAbbreviationList = [];
var conusList = [];
window.reasonCodeList = [];
window.actionCodeList = [];
var channelList = [];
var returnTypeList = [];
var orderTotals = {};
var addPartType = "2";
var pageLocked = getCustomObjectField("BUS", "OrderHeader", "LockTrans");

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

//Init controls and other control specific event handlers

//We need some async values before we can set the workspace values;
$.when(defCountryListLoaded, defProvinceListLoaded, defProvinceAbbreviationListLoaded).done(function () {
    setWorkspaceValues();
    buildShipMethodList();
    getReasonList_FULL();
    getActionList_FULL();
    //defOrderDataReady.resolve(true); //this deferred event is defined in the table.order.js file
	// HELIX Set our original Billing and Shipping address objects for address validation
	orgBillingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
	orgBillingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress2");
	orgBillingAddress.City = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
	orgBillingAddress.State = getCustomObjectField("BUS", "OrderHeader", "BillToState");
	orgBillingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
	orgBillingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
	
	orgShippingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
	orgShippingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress2");
	orgShippingAddress.City = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
	orgShippingAddress.State = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
	orgShippingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
	orgShippingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
});

$.when(defActionCodeListLoaded,defReasonCodeListLoaded).done(function () {

    defOrderDataReady.resolve(true); //this deferred event is defined in the table.order.js file
	// HELIX Set our original Billing and Shipping address objects for address validation
	orgBillingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
	orgBillingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress2");
	orgBillingAddress.City = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
	orgBillingAddress.State = getCustomObjectField("BUS", "OrderHeader", "BillToState");
	orgBillingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
	orgBillingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
	
	orgShippingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
	orgShippingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress2");
	orgShippingAddress.City = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
	orgShippingAddress.State = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
	orgShippingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
	orgShippingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
});

$(document).on('replacementpartstable.updated replacementpartstable.delete', function (e) {

    buildShipMethodList(false);

    if (window.ReplacementPartsTable.rows().count() > 0) {

        $('#replacementGridContainer').show();
        $('#replacementButton').show();
        $('#fldSetShipping').removeClass('hidden');
        $('#shippingmethod').attr('required', 'required');

        if (!pageLocked) {
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

        if (!pageLocked) {
            if (orderHasAerosol()) {
                buildShipMethodList(true);
            }

        }
    }

    SetHazardMessage();

    toggleReturnReplacementParts(addPartType);

});

$(document).ready(function () {

    if (currentCId < 1 || currentCId == null || currentIId < 1) {
        $.blockUI({ message: $('#noContactSelected') });
        return;
    }
    else {
        $.unblockUI;
    }

    $('.input-group.date').datepicker();
						  

	
    buildPartSearchControl();

    //var selectedPartData = [];
											  
    ////Part lookup type ahead init
    //partLookupTypeAhead = new Bloodhound({
    //    datumTokenizer: function (datum) {
    //        return Bloodhound.tokenizers.whitespace(datum.value);
    //    },
    //    queryTokenizer: Bloodhound.tokenizers.whitespace,
    //    remote: {
    //        url: CUSTOM_SCRIPTS_PATH + 'partsearch.php?sid=' + sid + '&_=' + date.getTime(),
    //        cache: false,
    //        prepare: function (query, settings) {
    //            var retSearch =  addPartType == '2' ? '&returnsearch=true':'';
    //            settings.url += '&itemnumber=' + query + '&country=' + shipToCountry + '&cid=' + currentCId + retSearch;
    //            return settings;
    //        },
    //        filter: function (searchresponse) {
    //            $.extend(lastItemSearchResponse,searchresponse);
    //            return searchresponse;
    //        }
    //    }
    //});

    //partLookupTypeAhead.initialize();
	
    //$('#partnumber').typeahead({
    //    hint: false,
    //    hilight: true,
    //    minLength: 3
    //}, {
    //    name: 'partnumber_data',
    //    displayKey: 'value',
    //    source: partLookupTypeAhead.ttAdapter(),
    //    limit: 20,
    //    hint: true,
    //    templates: {
    //        suggestion: function (data) {
    //            var hassn = hasValue(data.serialnumber);
    //            var sncss = hassn === true ? "partsearch-customer-asset" : "";
    //            var snview = hassn === true ? '<h6>SN: <strong>' + data.serialnumber + '</strong></h6>' : ""
    //            var template = '<div class="tt-suggestion tt-selectable ' + sncss + '">' + 
    //                  '<div>' + 
    //                      '<h5>' + data.value + '</h5>' +
    //                      snview + 
    //                      '<h6>In-stock: <strong>' + data.inv + '</strong></h6>' +
    //                      '<p>' + data.description + '</p>' +
    //                  '</div>' +
    //            '</div>';
    //            return template;
    //        }
    //    }
    //});

    //Listen for the grid delete event and check for order lines
    $(document).on('ordertable.delete', function () {
        orderHasLines();
    });

    disableSubmittedOrder();

    $('#partnumber').bind('typeahead:selected', function (obj, selectedData, name) {
        setSelectedPart(selectedData);
    });

    $('#partnumber').focusout(function (e) {
        if ($(this).val() == '' ){//|| findIndexOfObjWithAttr(lastItemSearchResponse, "name", $('#partnumber').val()) == -1) {
            //e.preventDefault();
            $('#unitprice, #adjustedprice').val('');
            $('#partname').val('');
            $('#partdescription').val('');
            if (addPartType == "2") {
                $('#modelnumber').val('');
                $('#serialnumber').val('');
            }
            $('#quantity').val('');
            $('#uom').val('');
            $('#linetotal').val('0.00');
            $('#partnumber').data('inv', '');

        } else {
            if ($(this).val() != selectedPartData.value) {
                var partSearchIndex = findIndexOfObjWithAttr(lastItemSearchResponse, "name", $(this).val());
                if (partSearchIndex > -1)
                    setSelectedPart(lastItemSearchResponse[partSearchIndex]);
            }
        }
    });

    $('#partnumber').focusin(function (e) {
        //setTimeout(function () {
        //    $('#partnumber').typeahead('val', '');
        //}, 100);
        
        //if ($('#partnumber').val() == '') {
        //    partLookupTypeAhead.clear();
        //    partLookupTypeAhead.clearPrefetchCache();
        //    partLookupTypeAhead.clearRemoteCache();
        //    partLookupTypeAhead.initialize(true);
        //}
    });

    $('#partnumber').on('paste', function () {
        $("#partnumber").typeahead("setQuery", $(this).val()).focus();
    });

    $('#partnumber').on('change', function () {

        var currentItem = $("#partnumber").typeahead("getActive");

        if (currentItem){
            if (currentItem.name == $('#partnumber').val()) {

            } else {

            }
        } else {

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

    //Build default reason code list
    //$.when(defCountryListLoaded).done(function () {
    //    if (!pageLocked) {
    //        setTimeout(function () {
    //            buildReasonList(1);
    //        }, 50);
    //    }

    //});

    //Reason type change
    $('#returntype').change(function () {

        var returnType = getSelectedValue('returntype');

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

        o.setValue('ShipMethod', shipMethodCurrentValue);

        setRequiredPhysicalAddress(shipMethodCurrentValue);

    });

    $('#reasoncode').change(function () {
        //Check if serial number and model number are required for the selected shipping option

        var $selectedOption = $('#reasoncode').find(":selected");

        if (addPartType == "2") {

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
                    orderSubmitObj.order.ContactId = currentCId;
                    orderSubmitObj.order.OnyxIndividualId = consumerId;
                    orderSubmitObj.order.BillingAddress = getAddressForOrderProcessing('BillTo');
                    orderSubmitObj.order.ShippingAddress = getAddressForOrderProcessing('ShipTo');
                    orderSubmitObj.order.LineItems = getLineItemsForOrderProcessing();
                    orderSubmitObj.order.SubTotal = orderTotals.subtotal;
                    orderSubmitObj.order.OrderTotal = orderTotals.grandtotal;
                    orderSubmitObj.order.OrderHeaderId = orderHeaderId;
                    orderSubmitObj.order.IncidentId = currentIId;
                    orderSubmitObj.order.OrderDate = new Date(Date.now()).toISOString();
                    orderSubmitObj.order.ShipAmount = getOrderAndShippingTotal().shipping;
                    orderSubmitObj.order.CRMPurchaseOrderID = orderHeaderId;
                    orderSubmitObj.order.Channel = getChannelNameFromId(orderChannel);
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
        var valid = true;
        if (findIndexOfObjWithAttr(lastItemSearchResponse, "name", value) == -1)
            valid = false;

        return valid;

    }, 'Enter a valid part number');


    $(document).ajaxStart(function () {
        $.blockUI({ message: '<i class="fa fa-circle-o-notch fa-spin" style="font-size:38px"></i>', css: { border: 'none', backgroundColor: 'transparent' } });
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

    setOrderHeaderValuesBeforeSave();

    window.external.beforesavecomplete(success, message);
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

    var skipOHUpdate = false;

    if (obj == INCIDENT_OBJECT_NAME) // Modified object = Incident?
    {
        //The contact was changed
        if (i.CId != c.Id) {
            setBillToShipToAddressFromContact(true);
            skipOHUpdate = true;
        }
    }

    if (obj == ORDER_HEADER_OBJECT_NAME) {

        //Check to see if the shipToCountry changed
        var selectedShipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
        var selectedBillToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");

        if (shipToCountry != selectedShipToCountry) {

            shipToCountry = selectedShipToCountry;

            buildShipMethodList();

            getReasonList_FULL();
            getActionList_FULL();
        }

        if (billToCountry != selectedBillToCountry) {
            billToCountry = selectedBillToCountry;
            buildReturnTypeList();
        }            

        if (shipToCountry != selectedShipToCountry) {
            shipToCountry = selectedShipToCountry;
        }

        orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
    }

        setWorkspaceValues(skipOHUpdate);

}

/**
* Build the type ahead search control 
*/
function buildPartSearchControl() {

    $('#partnumber').typeahead("close").val('').trigger('focusout', document.getElementById('partnumber')).typeahead("destroy");

    var date = new Date();

    selectedPartData = [];

    //Part lookup type ahead init
    partLookupTypeAhead = new Bloodhound({
        datumTokenizer: function (datum) {
            return Bloodhound.tokenizers.whitespace(datum.value);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: CUSTOM_SCRIPTS_PATH + 'partsearch.php?sid=' + sid + '&_=' + date.getTime(),
            cache: false,
            prepare: function (query, settings) {
                var retSearch = addPartType == '2' ? '&returnsearch=true' : '';
                settings.url += '&itemnumber=' + query + '&country=' + shipToCountry + '&cid=' + currentCId + retSearch;
                return settings;
            },
            filter: function (searchresponse) {
                $.extend(lastItemSearchResponse, searchresponse);
                return searchresponse;
            }
        }
    });

    partLookupTypeAhead.initialize();

    $('#partnumber').typeahead({
        hint: false,
        hilight: true,
        minLength: 3
    }, {
        name: 'partnumber_data',
        displayKey: 'value',
        source: partLookupTypeAhead.ttAdapter(),
        limit: 20,
        hint: true,
        templates: {
            suggestion: function (data) {
                var hassn = hasValue(data.serialnumber);
                var sncss = hassn === true ? "partsearch-customer-asset" : "";
                var snview = hassn === true ? '<h6>SN: <strong>' + data.serialnumber + '</strong></h6>' : ""
                var template = '<div class="tt-suggestion tt-selectable ' + sncss + '">' +
                      '<div>' +
                          '<h5>' + data.value + '</h5>' +
                          snview +
                          '<h6>In-stock: <strong>' + data.inv + '</strong></h6>' +
                          '<p>' + data.description + '</p>' +
                      '</div>' +
                '</div>';
                return template;
            }
        }
    });
}

/**
 * Set values on the Workspace. This fires initially when the page loads
 */
function setWorkspaceValues(skipOHUpdate) {

    //If we have a billto, skip auto setting it
    var hasBillTo = addressHasValues("BillTo");

    if (!skipOHUpdate) {
        if (!hasBillTo)
            setBillToShipToAddressFromContact();
    }

    orderType = getCustomObjectField("BUS", "OrderHeader", "OrderType");
    orderChannel = getCustomObjectField("BUS", "OrderHeader", "OrderChannel");

    shipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
    billToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");

    $('#ordernumber').val(orderNumber);

    SetOrderTotals();

    var shippingMethod = getCustomObjectField("BUS", "OrderHeader", "ShipMethod");
    var returnType = getCustomObjectField("BUS", "OrderHeader", "RAReturnType");
    var reasonCode = getCustomObjectField("BUS", "OrderHeader", "RAReasonCode");
    var actionCode = getCustomObjectField("BUS", "OrderHeader", "RAActionCode");
    var returnLocation = getCustomObjectField("BUS", "OrderHeader", "RAReturnLocation");
    var returnMethod = getCustomObjectField("BUS", "OrderHeader", "RAReturnMethod");

    if (!skipOHUpdate){
        //Return if we don't have a return type since all other dropdowns are based on that.
        if (returnType > 0) {
            setTimeout(function () {
                buildReturnTypeList(returnType, true);
            });
        
        } else {
            return;
        }

    if (shippingMethod > 0)
        setSelectControl('shippingmethod', shippingMethod);

    if (reasonCode > 0) {
            
        setTimeout(function () {
            buildReasonList(returnType, reasonCode, true);
        }, 100);
    } else {
        buildReasonList(returnType,null,true);
    }

    if (actionCode > 0) {
        setTimeout(function () {
            buildActionList(returnType, actionCode, true);
        },100);
    } else {
        buildActionList(returnType, null, true);
    }

    if (returnLocation > 0) {
        setTimeout(function () {
            buildReturnLocationList(returnType, returnLocation, true)
        },100);
    } else {
        buildReturnLocationList(returnType, null, true)
    }

    if (returnMethod > 0) {
        setTimeout(function () {
            buildReturnMethodList(returnType, returnMethod, true);
        },100);
    } else {
        buildReturnMethodList(returnType, null, true);
    }
}
    $.unblockUI();
}

function setSelectControl(controlId, value) {

    $('#' + controlId + ' option[value="' + value + '"]')
                                .attr('selected', true);
}


function setOrderHeaderValuesBeforeSave() {
    var shipMethod = null;
    if (hasValue(getSelectedValue('shippingmethod')))
        shipMethod = parseInt(getSelectedValue('shippingmethod'));
    var returnType = parseInt(getSelectedValue('returntype'));
    var reasonCode = parseInt(getSelectedValue('reasoncode'));
    var actionCode = parseInt(getSelectedValue('actioncode'));
    var returnLocation = parseInt(getSelectedValue('returnlocation'));
    var returnMethod = parseInt(getSelectedValue('returnmethod'));

    o.setValue('ShipMethod', shipMethod);
    o.setValue('RAReturnType', returnType);
    o.setValue('RAReasonCode', reasonCode);
    o.setValue('RAActionCode', actionCode);
    o.setValue('RAReturnLocation', returnLocation);
    o.setValue('RAReturnMethod', returnMethod);

    o.setValue('Contact', c.Id);
    currentCId = c.Id;

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

/**
 * Set totals for the order
 */
function SetOrderTotals() {

    var salesTax = 0.00;
    var grandTotal = 0.00;

    $('#ordersummary-subtotal').html("0.00");
    $('#ordersummary-tax').html("0.00");
    $('#ordersummary-shipping').html("0.00");
    $('#ordersummary-total').html("0.00");

    o.setValue('Total', grandTotal);
    o.setValue('ShippingTotal', 0.00);

    orderTotals.subtotal = 0.00;
    orderTotals.shipping = 0.00;
    orderTotals.salestax = salesTax;
    orderTotals.grandtotal = grandTotal;

}

function getSalesTaxForOrder(subTotal) {
    /* Get the sales tax */
    var billToAddress = getCurrentAddressInfoByType('BillTo');
    var shipToAddress = getCurrentAddressInfoByType('ShipTo');
    var salesTax = 0.00;
    $.ajax({
        type: "POST",
        url: CUSTOM_SCRIPTS_PATH + 'calculatesalestax.php',
        async: false,
        data: {
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
            ShipAmount: subTotal || getOrderAndShippingTotal().subtotal,
            OrderTotal: 0,
            IncidentId: currentIId,
            OrderHeaderId: orderHeaderId,
            ContactId: currentCId

        },
        dataType: "json"
    }).done(function (data) {
        //Save the order line once we have the tax calculated.
        salesTax = parseFloat(data.Amount);
    });

    return salesTax;
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

    if (pageLocked)
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
        PostalCode: address.PostalCode,
        Phone:getCustomObjectField("BUS", "OrderHeader", addressType + "Phone")
    }

    return responseAddress;
}

/**
 * Check to see if the form is valid.
 */
function performAddressValidation(checkBill, checkShip) {
		var isValid = true;
		var formValidResponse = {valid:true,message:""};
		
		var billToAddress = getCurrentAddressInfoByType('BillTo');
		var shipToAddress = getCurrentAddressInfoByType('ShipTo');
		var billToValidatedResponse,shipToValidatedResponse;
		
		// Only validate billing and/or shipping, we don't always need to validate both
		// IF Order Header Billing Address has been changed by the agent, perform the validation
		if(checkBill)
		{
			billToValidatedResponse = validateAddress(billToAddress);
			
			if (billToValidatedResponse.Code == "1" || billToValidatedResponse.Code == "2") 
			{
				// IF the agent Street address is identical to the validated Street address
				// AND the agent zip 5 digits match the first 5 of the 9 validate zip
				// THEN ignore validation prompt
				if(billToValidatedResponse.Validated.Address1 != getCustomObjectField("BUS", "OrderHeader", "BillToAddress1") ||
					billToValidatedResponse.Validated.PostalCode.indexOf(getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode")) == -1)
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

				// Reset the original Billing address
				orgBillingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
				orgBillingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress2");
				orgBillingAddress.City = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
				orgBillingAddress.State = getCustomObjectField("BUS", "OrderHeader", "BillToState");
				orgBillingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
				orgBillingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
				
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
			shipToValidatedResponse = validateAddress(shipToAddress);
			
			if (shipToValidatedResponse.Code == "1" || shipToValidatedResponse.Code == "2") 
			{
				// IF the agent Street address is identical to the validated Street address
				// AND the agent zip 5 digits match the first 5 of the 9 validate zip
				// THEN ignore validation prompt
				if(shipToValidatedResponse.Validated.Address1 != getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1") ||
					shipToValidatedResponse.Validated.PostalCode.indexOf(getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode")) == -1)
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
					
				// HELIX reset the original Shipping address
				orgShippingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
				orgShippingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress2");
				orgShippingAddress.City = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
				orgShippingAddress.State = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
				orgShippingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
				orgShippingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
					
				shipAddrValid = true;
			}

			// Unable to validate Shipping Address, allow agent to either use the entered address anyway, or return to order
			else
			{
				var overrideAddress = confirm("Unable to validate Shipping Address.\n\nClick OK to submit order with current Shipping Address.\nClick Cancel to return to order");
				
				if(overrideAddress)
					shipAddrValid = false;
				else
					shipAddrValid = true;
			}
		}

		if (hasValue(formValidResponse.message))
			alert(formValidResponse.message);
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
function isAddressValid(addressType, friendlyName) {

    var address = getCurrentAddressInfoByType(addressType);
    var valMessage = '';

    validatedResponse = validateAddress(address);

    //If we get back -1 as the code, some other issue happened with the validation. Set it as valid, but send back a message
    if (validatedResponse.Code == "-1") {
        return {
            valid: true,
            message: friendlyName + ' error: ' + validatedResponse.Message + '\n'
        };
    }

    if (!(validatedResponse.Code == "1" || validatedResponse.Code == "2")) { // A -1 response means there was an issue with the call to validate the address
        return {
            valid: false,
            message: friendlyName + ' error: ' + validatedResponse.Message + '\n'
        };
    }

    return { valid: true, message: '' };
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
        countryId = "",
        phone = ""

    if (c.AddrStreet != undefined) {
        var addressArray = $jqAddr.split("\n");
        addr1 = addressArray[0];
        if (addressArray.length > 1)
            addr2 = addressArray[1];
    }

    customerName = c.FullName || customerName;
    phone = c.PhHomeRaw || c.PhMobileRaw || phone;
    street = c.AddrStreet || street;
    city = c.AddrCity || city;
    zip = c.AddrPostalCode || zip;
    proId = c.AddrProvId || proId;
    countryId = c.AddrCountryId || countryId;

	// Add space to Canadian Postal Code, the Billing Address field on the Contact record does not keep the space!
	if(countryId == 2)
		zip = zip.substring(0, 3) + " " + zip.substring(3, 6);

    //Bill To
    if (!hasBillTo) {

            o.setValue('BillToCustomerName', customerName);
            o.setValue('BillToAddress1', addr1);
            o.setValue('BillToAddress2', addr2);
            o.setValue('BillToCity', city);
            o.setValue('BillToCountry', countryId);
			o.setValue('BillToPostalCode', zip);
            setTimeout(function () {
                o.setValue('BillToState', proId);
				
				// HELIX Set our original Billing address object for address validation if they are set on a new Order from the Contact
				orgBillingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
				orgBillingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress2");
				orgBillingAddress.City = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
				orgBillingAddress.State = getCustomObjectField("BUS", "OrderHeader", "BillToState");
				orgBillingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
				orgBillingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
				
            },2000);

    }

    if (!hasShipTo) {

        //Check if the Ship To address is set. If not, set the ship using the bill to values
        var shipToAddress1 = c.GetCustomFieldByName("BI$ship_street") || "";
        var shipToAddress2 = c.GetCustomFieldByName("BI$ship_street2") || "";
        var shipToCity = c.GetCustomFieldByName("BI$ship_city") || "";
        var shipToState = c.GetCustomFieldByName("BI$ship_state") || "";
        var shipToPostalCode = c.GetCustomFieldByName("BI$ship_postalcode") || "";
        var shipToCountryId = c.GetCustomFieldByName("BI$ship_country") || "";

        if (shipToAddress1 == null || shipToAddress1 == undefined || shipToAddress1 == '') {
            shipToAddress1 = addr1;
            shipToAddress2 = addr2;
            shipToCity = city;
            shipToState = proId;
            shipToPostalCode = zip;
            shipToCountryId = countryId;
        }

        //Ship To
		o.setValue('ShipToCustomerName', customerName);
		o.setValue('ShipToAddress1', shipToAddress1);
		o.setValue('ShipToAddress2', shipToAddress2);
		o.setValue('ShipToCity', shipToCity);
		o.setValue('ShipToCountry', shipToCountry);
		o.setValue('ShipToPostalCode', shipToPostalCode);
		
		setTimeout(function () {
			o.setValue('ShipToState', shipToState);
			
			// HELIX Set our original Shipping address object for address validation if they are set on a new Order from the Contact
			orgShippingAddress.Address1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
			orgShippingAddress.Address2 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress2");
			orgShippingAddress.City = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
			orgShippingAddress.State = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
			orgShippingAddress.Country = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
			orgShippingAddress.Postal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
	
		}, 2000);

        shipToCountry = shipToCountryId;
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
    }, 2000);
    o.setValue(addressType + 'PostalCode', postalCode);

}

/**
 * Set the billto and shipto address after address validation
 * @param {type} billto
 * @param {type} shipto
 */
function setBillToShipToAddressFromAddressValidation(billto, shipto) {

    //Bill To
    o.setValue('BillToAddress1', billto.Address1 + '\n' + billtoAddress2);
    o.setValue('BillToCity', billto.City);
    o.setValue('BillToCountry', billto.CountryCode);
    setTimeout(function () {
        o.setValue('BillToState', billto.StateCode);
    }, 2000);
    o.setValue('BillToPostalCode', billto.PostalCode);

    //Ship To
    o.setValue('ShipToAddress1', shipto.Address1 + '\n' + shiptoAddress2);
    o.setValue('ShipToCity', shipto.City);
    o.setValue('ShipToCountry', shipto.CountryCode);
    setTimeout(function () {
        o.setValue('ShipToState', shipto.StateCode);
    }, 2000);
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

    $.ajax({
        type: "POST",
        url: ORDER_SUBMIT_ENDPOINT + '?action=submitorder&sid=' + sid,
        data: JSON.stringify(orderDetails),
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
    
    // cURL Timeout error
        if (data.ResponseMessage.toLowerCase() === 'timeout was reached') {

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
		
	if (hasValue(data.FedEx)) {
		if (hasValue(data.FedEx.TrackingNumber)) {
			setCustomObjectField('BUS', 'OrderHeader', 'FedExLabelSent', 1);
		}
    }
	
	//A response code will only exist if there is a failure
	if (data == null || $.isNumeric(data.ResponseCode) || data.ResponseCode.toString().toLowerCase() == 'soap failure') {

        alert('An error occurred while attempting to submit the RA.');

        setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
        pageLocked = true;

        disableSubmittedOrder();

        if (data.ResponseCode) {
            if (data.ResponseCode == 28) {
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

    if (hasValue(data.FedEx)) {
        if (hasValue(data.FedEx.TrackingNumber)) {
			setCustomObjectField('BUS', 'OrderHeader', 'FedExLabelSent', 1);
		}
    }

    //Check for ResponseCode other than known codes
    if (!(data.ResponseCode.toLowerCase() === 'failure' || data.ResponseCode.toLowerCase() === 'success')) {

        var message = data.AgentMessage || "An error occurred while attempting to submit the RA.";

        alert(message);

        setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
        pageLocked = true;

        disableSubmittedOrder(true);

        setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
								   
		
				
		 
	 

        setOrderHeaderValuesBeforeSave();
        $.unblockUI();

        return;
    }
    if (data.ResponseCode.toLowerCase() === 'failure') {

        alert('An error occurred while attempting to submit the RA.');

        setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
		pageLocked = true;
		disableSubmittedOrder(true);
        
        setCustomObjectField('BUS', 'OrderHeader', 'Status', "Queued");
		setOrderHeaderValuesBeforeSave();

        $.unblockUI();
        return;

    }

												 
    if (data.ResponseCode.toLowerCase() === 'success') {
        //Update the order
        //var message = 'RA created successfully. The Return # is ' + data.ReturnNumber + '.';
		var status = 'Submitted';
		alert('RA created successfully. The Return # is ' + data.ReturnNumber);

        returnAuthorizationNumber = parseInt(data.ReturnNumber);
        if(hasValue(data.FedEx)) {
			setCustomObjectField('BUS', 'OrderHeader', 'FedExMessage', data.FedEx.FedExMessage);
			trackingNumber = data.FedEx.TrackingNumber || '';
			if (data.FedEx.FedExResponse == 'SUCCESS' || data.FedEx.FedExResponse == 'WARNING'){
				//message += '\r\n\r\n FedEx Tracking Number: ' + data.FedEx.TrackingNumber + '.';
			} else {
				//message += '\r\n\r\n FedEx Error Response: ' + data.FedEx.FedExMessage + '.';
				alert('FedEx Error Response: ' + data.FedEx.FedExMessage + '.');
				//i.QueueId = 33;
				status = 'FedEx Error';
			}
		} else {
			trackingNumber = '';
		}
		
		//alert(message);
		
        setCustomObjectField('BUS', 'OrderHeader', 'LockTrans', 1);
        pageLocked = true;

        disableSubmittedOrder(true);

        setCustomObjectField('BUS', 'OrderHeader', 'RANumber', returnAuthorizationNumber);
        setCustomObjectField('BUS', 'OrderHeader', 'RATrackingNumber', trackingNumber);
																	
								   

        //setCustomObjectField('BUS', 'OrderHeader', 'Status', "Submitted");
		setCustomObjectField('BUS', 'OrderHeader', 'Status', status);

        setOrderHeaderValuesBeforeSave();

        $.unblockUI();
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

	// HELIX Capture the current Billing/Shipping address values
	var email = c.EmailAddr;
	var billToName = getCustomObjectField("BUS", "OrderHeader", "BillToCustomerName");
	var shipToName = getCustomObjectField("BUS", "OrderHeader", "ShipToCustomerName");
	var billToAddress1 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress1");
	var billToAddress2 = getCustomObjectField("BUS", "OrderHeader", "BillToAddress2");
	var shipToAddress1 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress1");
	var shipToAddress2 = getCustomObjectField("BUS", "OrderHeader", "ShipToAddress2");
	var billToCity = getCustomObjectField("BUS", "OrderHeader", "BillToCity");
	var shipToCity = getCustomObjectField("BUS", "OrderHeader", "ShipToCity");
	var billToState = getCustomObjectField("BUS", "OrderHeader", "BillToState");
	var shipToState = getCustomObjectField("BUS", "OrderHeader", "ShipToState");
	var billToPostal = getCustomObjectField("BUS", "OrderHeader", "BillToPostalCode");
	var shipToPostal = getCustomObjectField("BUS", "OrderHeader", "ShipToPostalCode");
	var billToCountry = getCustomObjectField("BUS", "OrderHeader", "BillToCountry");
	var shipToCountry = getCustomObjectField("BUS", "OrderHeader", "ShipToCountry");
		
	// HELIX address validation update, move logic to Submit Order validation rather than manual agent process
	var checkBill = false;
	var checkShip = false;
	
	// HELIX Compare original Billing/Shipping address values with values given by agent, if any changes are detected we need to validate
	if(orgBillingAddress.Address1 != billToAddress1 ||
		orgBillingAddress.Address2 != billToAddress2 ||
		orgBillingAddress.City != billToCity ||
		orgBillingAddress.State != billToState ||
		orgBillingAddress.Country != billToCountry ||
		orgBillingAddress.Postal != billToPostal )
		checkBill = true;
	
	if(orgShippingAddress.Address1 != shipToAddress1 ||
		orgShippingAddress.Address2 != shipToAddress2 ||
		orgShippingAddress.City != shipToCity ||
		orgShippingAddress.State != shipToState ||
		orgShippingAddress.Country != shipToCountry ||
		orgShippingAddress.Postal != shipToPostal )
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

    //Check that RA is selected
    if (orderType != ORDER_TYPE_RA_ID) {
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
        if (RET_LOCATION_GRANDRAPIDS_ID != getSelectedValue('returnlocation') && billToCountry == US_COUNTRY_ID) {
            message = message + 'All claims should have a return location of Grand Rapids.\n\n';
            isvalid = false;
        }
        if (getSelectedValue('returnmethod').toLowerCase() == 'email') {
            message = message + 'Email is not a valid return method for claims.\n\n';
            isvalid = false;
        }
    }

    if (!hasValue(getCustomObjectField("BUS", "OrderHeader", "ShipToPhone")) && (getSelectedText('returnmethod').toLowerCase() == 'email' || getSelectedText('returnmethod').toLowerCase() == 'mail')) {
        message = message + 'Ship to Phone number required for Email or Mail return methods.\n\n';
        isvalid = false;
    }

    if ((getSelectedText('returnmethod').toLowerCase() == 'email') && hasValue(email) == false) {
        message = message + 'Contact email address is required for Email return methods.\n';
        isvalid = false;
    }

    var isAirFreight = $('#shippingmethod').find(":selected").data("airfreight");

    if (orderHasAerosol()) {

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
function addressHasValues(addressType) {

    var address = getCurrentAddressInfoByType(addressType);

    if (hasValue(address.Address1) == false &&
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
    orderLine.OrderType = orderType;
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

    if (pageLocked) { 
        $('input, textarea, table, select, button').attr('disabled', 'disabled');
        $('.nav-tabs a[data-toggle-option="2"]').tab('show');
        $('.editor_remove').remove();
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

function getStorageObject(key) {
    var storageVal = localStorage.getItem(key);
    if (storageVal != null) {
        return JSON.parse(storageVal);
    }
    return null
}

function toggleReturnReplacementParts(selectedOption) {

    //partLookupTypeAhead.clear();
    //partLookupTypeAhead.clearPrefetchCache();
    //partLookupTypeAhead.clearRemoteCache();
    //partLookupTypeAhead.initialize(true);
    //$('#partnumber').val('').trigger('focusout');

    buildPartSearchControl();

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
        var selectedShipMethod = getCustomObjectField("BUS", "OrderHeader", "ShipMethod");
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

        if (hasValue(selectedShipMethod)) {

            $('#shippingmethod option[value="' + selectedShipMethod + '"]')
                .attr('selected', true)
                .trigger('change', true);
        }
    };

    if (reset === true) {

        setOptions(window.menulists.shipping_methods);

    } else {

        $.ajax({
            type: "GET",
            url: AJAX_ENDPOINT + '?action=getshipmethods&ordertype=' + orderType + '&country=' + shipToCountry + '&sid=' + sid,
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
        url: AJAX_ENDPOINT + '?action=getrareasonlist&returntypeid=0&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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

            //var refreshWindow = confirm("Error retrieving the Reason Code list.\nClick OK to reload this RA and try again.");

            //if (refreshWindow == true)
            //    location.reload();
        },
        dataType: "json"
    });

}

function getActionList_FULL() {

    $.ajax({
        type: "GET",
        url: AJAX_ENDPOINT + '?action=getraactionlist&returntypeid=0&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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

            //var refreshWindow = confirm("Error retrieving the Action Code list.\nClick OK to reload this RA and try again.");

            //if (refreshWindow == true)
            //    location.reload();
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
            if (billToCountry == CA_COUNTRY_ID && value == RET_INSPECTION_ID)
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

function buildReasonList(returnType, selectedValue,cancelTrigger) {

    $.ajax({
        type: "GET",
        url: AJAX_ENDPOINT + '?action=getrareasonlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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
        url: AJAX_ENDPOINT + '?action=getraactionlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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
				if (pageLocked || (!pageLocked && row.active == true)){
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
        url: AJAX_ENDPOINT + '?action=getrareturnlocationlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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
                if (pageLocked || (!pageLocked && row.active == true)){
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

            // // filter the location list based on the action code if we have more than one location
            // if (locationsList.length > 1 && actionSelected == true) {

                // var locationName;

                // $.each(locationsList, function (idx, lst) {
                    // if (lst.action.toLowerCase() == actionCode.toLowerCase())
                        // locationName = lst.location;
                // });

                // $("#returnlocation > option").each(function () {
                    // if (this.value != "" && locationName.toLowerCase() != this.text.toLowerCase()) {
                        // $(this).remove();
                    // }
                // });                
            // }

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
        url: AJAX_ENDPOINT + '?action=getrareturnmethodlist&returntypeid=' + returnType + '&countryiso=' + getCountryISONameById(shipToCountry) + '&sid=' + sid,
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

				if (pageLocked || (!pageLocked && row.active == true)){
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

    orderTotal = getOrderAndShippingTotal().order;

    for (var i = 0; i < optionCharges.length; i++) {

        if (orderTotal >= optionCharges[i].min && orderTotal < optionCharges[i].max) {
            shippingCharge = optionCharges[i].cost;
        }
    }
}


/**
 * Reset all the controls on the form after adding a line item
 */

function formatAddress(addr) {
    var addr2 = hasValue(addr.Address2) ? addr.Address2 + "\n" : "";
    var formattedAddress = addr.Address1 + "\n" + addr2 + addr.City + ", " + addr.StateCode + " " + addr.CountryCode + " " + addr.PostalCode;
    return formattedAddress;
};

function resetFormAfterAddingLine() {

    $('.reset').each(function () {
        $(this).val('');
    });


/*
    $("#nocharge").attr('checked', false);

    $("#adjustedprice").trigger('focusout');
*/
}

function findIndexOfObjWithAttr (array, attr, value) {
    for (var i = 0; i < array.length; i++) {
        if (array[i][attr].toLowerCase() === value.toLowerCase()) {
            return i;
        }
    }
    return -1;
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