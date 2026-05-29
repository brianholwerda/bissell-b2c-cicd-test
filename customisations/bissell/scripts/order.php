<?php

$serverAssetPath = "";
$serverCSSPath = "";
$localhost = array(
    '127.0.0.1',
    '::1'
);

if(!in_array($_SERVER['REMOTE_ADDR'], $localhost)){
    require_once('agentsessionauth.php');
    $serverAssetPath = "../cp/customer/development/libraries/agentconsole/ordermanager/";
    $serverCSSPath = "/euf/assets/agentconsole/ordermanager/";
}

?>

<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /> 
    <title>Bissell Order Header & Details</title>

    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/datatables.bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/generator-base.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/editor.bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/components-rounded.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/layout.min.css" />
    <!--<link rel="stylesheet" type="text/css" href="css/themes/blue.min.css" />-->
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/custom.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/plugins.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/typeahead.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/ladda-themeless.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/app.css" />

    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/jquery.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/util.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/datatables.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/dataTables.editor.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/editor.bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/jquery.blockui.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/jquery.formatCurrency.js"></script>
    <script src="<?php echo $serverAssetPath;?>js/plugins/typeahead/handlebars.min.js" type="text/javascript"></script>
    <script src="<?php echo $serverAssetPath;?>js/plugins/typeahead/typeahead.bundle.js" type="text/javascript"></script>
    <script src="<?php echo $serverAssetPath;?>js/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/jquery.validate.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/additional-methods.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/accounting.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/plugins/ladda/spin.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/plugins/ladda/ladda.min.js"></script>
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
</head>
<body class="bootstrap">
    <div class="page-wrapper">
        <div id="errorlist">
            <ul></ul>
        </div>
        <form id="orderlineform" method="get" action="">
            <div class="orderheader row spacer form-row">
                <div class="col-sm-2 form-group">
                    <label for="rewardbalance" class="control-label">Order Type</label>
                    <div>
                        <select id="nochargeorder" name="nochargeorder" class="form-control" required>
                            <option value="">[+]</option>
                            <option value="0">Charge</option>
                            <option value="1">No Charge</option>
                                                        
                        </select>
                    </div>
                    <div id="error_nochargeselect"></div>
                </div>
				<div class="col-sm-2 form-group">
					<label for="orderbrand" class="control-label">Order Brand</label>
                    <div>
                        <select id="orderbrand" name="orderbrand" class="form-control" required="required"></select>
                    </div>
                    <div id="error_orderbrand"></div>
                </div>
                <div class="col-sm-2 form-group">
                    <label for="rewardbalance" class="control-label">Loyalty Status</label>
                    <div><input id="loyaltystatus" type="text" class="form-control" placeholder="Loyalty Status" disabled /></div>
                </div>
                <div class="col-sm-2 form-group">
                    <label for="ordernumber" class="control-label">Order #</label>
                    <div><input id="ordernumber" type="text" class="form-control" placpeholder="Order #" disabled /></div>
                </div>
                <div class="col-sm-2 form-group hidden" id="expecteddeliverydatecol">
                    <label for="expecteddeliverydate" class="control-label">Expected Delivery Date</label>
                    <div class="input-group date form_datetime form_datetime bs-datetime" data-date-start-date="+0d">
                        <input id="expecteddeliverydate" type="text" size="16" class="form-control datepicker ignore-line-validation" placeholder="Expected Delivery Date" />
                        <span class="input-group-addon">
                            <button class="btn default date-set calendar-button" type="button">
                                <i class="fa fa-calendar"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="search-form row form-row">
                <div class="col-sm-2 form-group">
                    <label for="partlookupnumber" class="control-label">Part Lookup</label>
                    <div>
                        <input type="text" id="partnumber" name="partnumber" class="form-control reset" required />
                    </div>
                    <div id="error_partnumber"></div>
                </div>
                <div class="col-sm-2 form-group">
                    <label class="control-label" for="enterpartdescription">Description</label>
                    <div>
                        <input id="partdescription" type="text" class="form-control reset" placeholder="Description" disabled />
                    </div>
                </div>
                <div class="col-sm-1 form-group">
                    <label class="control-label " for="price">Price</label>
                    <div>
                        <input id="unitprice" type="number" step="0.01" class="form-control reset" placeholder="Price" disabled />
                    </div>
                </div>
                <div class="col-sm-1 form-group">
                    <label class="control-label" for="quantity">Qty</label>
                    <div><input id="quantity" name="quantity" type="number" class="form-control reset" placeholder="Qty" min="1" step="1" required /></div>
                    <div id="error_quantity"></div>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-sm-1 form-group">
                    <label class="control-label" for="nocharge">No Charge</label>
                    <div>
                        <input id="nocharge" type="checkbox" value="">
                    </div>
                </div>
                <div class="col-sm-1 form-group">
                    <label class="control-label" for="adjustedprice">Adj Price</label>
                    <div>
                        <input id="adjustedprice" type="number" step="0.01" class="form-control reset" placeholder="Adj Price" />
                    </div>
                </div>

                <div class="col-sm-2 form-group hidden" id="nochargereasoncol">
                    <label class="control-label" for="nochargereasoncode">Reason Code</label>
                    <div>
                        <select id="nochargereasoncode" name="nochargereasoncode" class="form-control reset" placeholder="Reason Code" disabled></select>
                    </div>
                    <div id="error_nochargereasoncode"></div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Model #</label>
                        <div><input id="modelnumber" name="modelnumber" type="text" class="form-control reset" placeholder="Model #" disabled></div>
                        <div id="error_modelnumber"></div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Serial #</label>
                        <div><input id="serialnumber" name="serialnumber" type="text" class="form-control reset" placeholder="Serial #" disabled></div>
                        <div id="error_serialnumber"></div>
                    </div>
                </div>
                <div class="col-sm-1 form-group">
                    <lable class="control-label">Line Total</lable>
                    <div>
                        <input id="linetotal" type="text" class="form-control input-view-only reset" value="0.00" disabled />
                    </div>
                </div>
                <div class="col-sm-1 form-group">
                    <lable class="control-label">&nbsp;</lable>
                    <div><button type="button" class="btn blue mt-ladda-btn ladda-button" data-style="slide-right" id="btnAddLine">Add Item</button></div>
                </div>
            </div>
        </form>
            <!-- Grid -->
            <div class="col-sm-12 orderitems spacer">
                <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="Order" width="100%">
                    <thead>
                        <tr>
                            <th>Delete</th>
                            <th>Line</th>
                            <th>Part</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Line Total</th>
                            <th>Reason Code</th>
                            <!--<th>Adjusted Price</th>
                            <th>Line Type</th>
                            <th>Inv</th>-->
                        </tr>
                    </thead>
                    <tfoot class="hidden">
                        <tr>
                            <th colspan="7" class="order-grid-footer">Order Total</th>
                            <th colspan="4" id="ordertotaldisplay" class="order-grid-footer">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <form id="ordersubmitform">
            <!-- New shipping and payment row-->
            <div class="row form-row">
                <div class="col-sm-9">
                    <fieldset class="col-sm-12 order-fieldset">
                        <!--<legend>Shipping</legend>-->
                        <div class="panel panel-default">
                            <div class="panel-body order-panel-body">
                                <div class="col-sm-4">
                                    <label>Shipping method</label>
                                    <select class="form-control" id="shippingmethod" name="shippingmethod" required="required"></select>
                                    <div id="error_shippingmethod"></div>
                                    <div id="error_hazardousitems" class="validation-error" style="min-width:320px"></div>
                                </div>
                                <div class="col-sm-3">
                                    <label>Free Shipping</label>
                                    <div>
                                        <input id="freeshipping" type="checkbox" value="">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Cost</label>
                                            <div>
                                                <input id="shippingcost" type="text" class="form-control reset" placeholder="Shp Cost" min="0" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-8 hidden" id="shippingreasoncolumn">
                                        <label>Reason</label>
                                        <div>
                                            <select class="form-control reset" id="shippingreasoncode" name="shippingreasoncode"></select>
                                        </div>
                                        <div id="error_shippingreasoncode"></div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-sm-3 center-block" style="float:right;background-color:#f1f1f1;">
                    <div class="row">
                        <div class="col-sm-12 center-block order-summary">
                            <table class="order-summary" cellspacing="5" style="width:100%">
                                <tr>
                                    <td>
                                        Sub total:
                                    </td>
                                    <td class="order-summary-value">
                                        <span class="order-summary" id="ordersummary-subtotal"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Tax:
                                    </td>
                                    <td class="order-summary-value">
                                        <span class="order-summary" id="ordersummary-tax"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Secondary Tax:
                                    </td>
                                    <td class="order-summary-value">
                                        <span class="order-summary" id="ordersummary-tax2"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Shipping:
                                    </td>
                                    <td class="order-summary-value">
                                        <span class="order-summary" id="ordersummary-shipping"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Total:
                                    </td>
                                    <td class="order-summary-value">
                                        <span class="order-summary" id="ordersummary-total"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="text-align:center;">
                            <button type="button" class="btn btn-default" id="submitorder">Submit Order</button>
                        </div>
                    </div>
                    <div class="col-sm-1 pull-right align-bottom">
                    </div>
                </div>
                <div class="col-sm-6">
                    <fieldset class="col-sm-12 order-fieldset">
                        <!--<legend>Payment</legend>-->
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="col-sm-12">
                                    <label>Payment</label><br />
                                    <select class="form-control input-inline payment-type" id="paymentmethod" name="paymentmethod" required></select>
                                    <button type="button" id="setcreditcarddetails" class="btn hidden" title="Set credit card details">
                                        <i class="fa fa-credit-card"></i>
                                        Edit
                                    </button>
                                    <button type="button" id="setpapercheckdetails" class="btn hidden" title="Set paper check details">
                                        <i class="fa fa-pencil-square-o"></i>
                                        Edit
                                    </button>
                                    <div id="error_checkpaymentmethod"></div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </form>

        <div id="noContactSelected" style="display:none;">
            <h3>Please save this Incident and select a Contact before creating an order.</h3>
        </div>
    </div>
    <!--Hidden fields-->
    <!-- Unit of measuer for the selected item-->
    <input type="text" hidden id="uom" />
    <!--Dialogs-->

    <div class="modal fade" id="papercheckpaymentmodal" tabindex="-1" role="basic" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Paper Check Payment Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form id="papercheckform" action="" method="get">
                            <div class="form-body">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label cc-field-label">Check #</label>
                                        <div class="col-sm-8">
                                                <input type="number" id="paperchecknumber" name="paperchecknumber"
                                                       class="form-control cc-field" placeholder="Check #" required />
                                                <div id="error_paperchecknumber"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label cc-field-label">Amount</label>
                                        <div class="col-sm-8">
                                            <input type="number" step="0.01" id="papercheckamount" name="papercheckamount"
                                                   class="form-control cc-field" placeholder="Check Amount" required />
                                            <div id="error_papercheckamount"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline gray" data-dismiss="modal" id="cancelpapercheck">Cancel</button>
                    <button type="button" class="btn btn-outline sbold blue" id="submitpapercheck">Save</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
    </div>

    <div class="modal fade" id="ccpaymentmodal" tabindex="-1" role="basic" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-creditdcard">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Credit Card Payment Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form id="creditcardform" action="" method="get">
                                <div class="form-body">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="nameoncreditcard" name="nameoncreditcard" class="form-control cc-field" required />
                                                <div id="error_nameoncreditcard"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Card Type</label>
                                            <div class="col-sm-9">
                                                <select id="creditcardtype" name="creditcardtype" class="form-control cc-field" required>
                                                    <option value="">[+]</option>
                                                    <option value="A">American Express</option>
                                                    <option value="D">Discover</option>
                                                    <option value="M">Master Card</option>
                                                    <option value="V">Visa</option>
                                                </select>
                                                <div id="error_creditcardtype"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Card #</label>
                                            <div class="col-sm-9">
                                                <div class="input-icon">
                                                    <i class="fa fa-credit-card"></i>
                                                    <input type="text" id="creditcardnumber" name="creditcardnumber"
                                                           class="form-control cc-field" placeholder="Credit card #" required />
                                                    <div id="error_creditcardnumber"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Expiration</label>
                                            <div class="col-sm-4">
                                                <select id="creditcardexpirationmonth" name="creditcardexpirationmonth" class="form-control cc-field" required>
                                                    <option value="">[+]</option>
                                                    <option value="01">01</option>
                                                    <option value="02">02</option>
                                                    <option value="03">03</option>
                                                    <option value="04">04</option>
                                                    <option value="05">05</option>
                                                    <option value="06">06</option>
                                                    <option value="07">07</option>
                                                    <option value="08">08</option>
                                                    <option value="09">09</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                </select>
                                                <div id="error_creditcardexpirationmonth"></div>
                                            </div>
                                            <div class="col-sm-5">
                                                <select id="creditcardexpirationyear" name="creditcardexpirationyear"
                                                        class="form-control cc-field " required></select>
                                                <div id="error_creditcardexpirationyear"></div>
                                            </div>
                                            <div class="hidden"><input type="text" name="expirationdate" id="expirationdate" /></div>
                                        </div>
                                        <div id="error_expirationdate"></div>                                     
                                        <!--<div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">CVV</label>
                                            <div class="col-sm-9">
                                                <input type="number" id="creditcardcvv" name="creditcardcvv"
                                                       class="form-control cc-small-input cc-field" maxlength="3" />
                                                <div id="error_creditcardcvv"></div>
                                            </div>
                                        </div>-->
                                    </div>
                                    <div class="col-sm-6 address-column">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Address 1</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="creditcardaddress1" name="creditcardaddress1"
                                                       class="form-control cc-field" placeholder="Address 1" required />
                                                <div id="error_creditcardaddress1"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Address 2</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="creditcardaddress2" name="creditcardaddress2"
                                                       class="form-control cc-field" placeholder="Address 2" />
                                                <div id="error_creditcardaddress2"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">City</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="creditcardcity" name="creditcardcity"
                                                       class="form-control cc-field" placeholder="City" required />
                                                <div id="error_creditcardcity"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">State</label>
                                            <div class="col-sm-9">
                                                <select id="creditcardstate" name="creditcardstate"
                                                        class="form-control cc-field" required></select>
                                                <div id="error_creditcardstate"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Postal Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="creditcardpostalcode" name="creditcardpostalcode"
                                                       class="form-control cc-field" placeholder="Postal Code" required />
                                                <div id="error_creditcardpostalcode"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline gray" data-dismiss="modal" id="cancelcreditcard">Cancel</button>
                        <button type="button" class="btn btn-outline sbold blue" id="submitcreditcard">Save</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
</body>
</html>
<script src="<?php echo $serverAssetPath;?>js/cxobject.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/table.order.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/app.js"></script>

