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
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Model #</label>
                        <div>
                            <input id="modelnumber" name="modelnumber" type="text" class="form-control" placeholder="Model #" disabled />
                        </div>
                        <div id="error_modelnumber"></div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Serial #</label>
                        <div>
                            <input id="serialnumber" name="serialnumber" type="text" class="form-control" placeholder="Serial #" disabled />
                        </div>
                        <div id="error_serialnumber"></div>
                    </div>
                </div>
                <div class="col-sm-2 form-group">
                    <label for="expecteddeliverydate" class="control-label">Purchase Date</label>
                    <div class="input-group date form_datetime form_datetime bs-datetime">
                        <input id="purchasedate" type="text" size="16" class="form-control datepicker reset" placeholder="Purchase Date" />
                        <span class="input-group-addon">
                            <button class="btn default date-set calendar-button" type="button">
                                <i class="fa fa-calendar"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-sm-2 form-group">
                    <label for="ordernumber" class="control-label">Order #</label>
                    <div>
                        <input id="ordernumber" type="text" class="form-control" placpeholder="Order #" />
                    </div>
                </div>
            </div>

            <!--Parts rows-->

            <div class="row row-padding-left-25">
                <div class="col-sm-2">
                    <!--<div class="btn-group-vertical" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" class="toggle"> Return Parts
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" class="toggle"> Replacement
                        </label>
                    </div>-->
                    <ul class="nav nav-tabs tabs-left">
                        <li class="active">
                            <a href="#returnparts" data-toggle="tab" data-toggle-option="2">Return Parts </a>
                        </li>
                        <li id="replacementButton" style="display:none;">
                            <a href="#replacement" data-toggle="tab" data-toggle-option="3">Replacement </a>
                            <!--<a href="#replacement">Replacement</a>-->
                        </li>
                    </ul>
                </div>
                <div class="col-sm-10">
                    <div class="search-form row form-row">
                        <div class="col-sm-2 form-group">
                            <label for="partlookupnumber" class="control-label">Part Lookup</label>
                            <div>
                                <input type="text" onfocus="this.value=''" id="partnumber" name="partnumber" class="form-control reset" required />
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
                            <div>
                                <input id="quantity" name="quantity" type="number" class="form-control reset" placeholder="Qty" min="1" step="1" required disabled />
                            </div>
                            <div id="error_quantity"></div>
                        </div>
                        <div class="col-sm-1 form-group">
                            <lable class="control-label">Line Total</lable>
                            <div>
                                <input id="linetotal" type="text" class="form-control input-view-only reset" value="0.00" disabled />
                            </div>
                        </div>
                        <div class="col-sm-1 form-group">
                            <lable class="control-label">&nbsp;</lable>
                            <div>
                                <button type="button" class="btn blue mt-ladda-btn ladda-button" data-style="slide-right" id="btnAddLine">Add Return Item</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-sm-2 form-group">
                            <label class="control-label" for="returntype">Return Type</label>
                            <div>
                                <select id="returntype" name="returntype" class="form-control" placeholder="Return Type" required></select>
                            </div>
                            <div id="error_returntype"></div>
                        </div>
                        <div class="col-sm-2 form-group">
                            <label class="control-label" for="reasoncode">Reason Code</label>
                            <div>
                                <select id="reasoncode" name="reasoncode" class="form-control" placeholder="Reason Code" required></select>
                            </div>
                            <div id="error_reasoncode"></div>
                        </div>
                        <div class="col-sm-2 form-group returnfield">
                            <label class="control-label" for="actioncode">Action Code</label>
                            <div>
                                <select id="actioncode" name="actioncode" class="form-control returnfield" placeholder="Action Code" required></select>
                            </div>
                            <div id="error_actioncode"></div>
                        </div>
                        <div class="col-sm-2 form-group returnfield">
                            <label class="control-label" for="returnlocation">Return Location</label>
                            <div>
                                <select id="returnlocation" name="returnlocation" class="form-control returnfield" placeholder="Return Location" required></select>
                            </div>
                            <div id="error_returnlocation"></div>
                        </div>
                        <div class="col-sm-2 form-group returnfield">
                            <label class="control-label" for="returnmethod">Return Method</label>
                            <div>
                                <select id="returnmethod" name="returnmethod" class="form-control returnfield" placeholder="Return Method" required></select>
                            </div>
                            <div id="error_returnmethod"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!--End parts rows-->

        </form>
        <!-- Return Parts Grid -->
        <div class="row spacer">
            <h5 style="text-align:center">Return Parts</h5>
        </div>
        <div class="col-sm-12 returnparts spacer">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="returnparts" width="100%">
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
                        <th>Action Code</th>
                        <!--<th>Line Type</th>
                            <th>Inv</th>-->
                    </tr>
                </thead>
            </table>
        </div>
        <div class="row spacer">&nbsp;</div>
        <!-- Replacement Parts Grid -->
        <div id="replacementGridContainer" style="display:none;">
            <div class="row replacement-view hidden spacer" id="replacement-parts-title">
                <h5 style="text-align:center">Replacement Parts</h5>
            </div>
            <div class="col-sm-12 replacementparts spacer replacement-view hidden" id="replacement-parts-row">
                <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="replacementparts" width="100%">
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
                            <!--<th>Action Code</th>-->
                            <!--<th>Line Type</th>
                        <th>Inv</th>-->
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <form id="ordersubmitform">
            <!-- New shipping and payment row-->
            <div class="row form-row">
                <div class="col-sm-9">
                    <fieldset class="col-sm-12 order-fieldset hidden" id="fldSetShipping">
                        <!--<legend>Shipping</legend>-->
                        <div class="panel panel-default">
                            <div class="panel-body order-panel-body">
                                <div class="col-sm-4">
                                    <label>Shipping method</label>
                                    <select class="form-control" id="shippingmethod" name="shippingmethod" ></select>
                                    <div id="error_shippingmethod"></div>
                                    <div id="error_hazardousitems" class="validation-error" style="min-width:320px"></div>
                                </div>
                                <!--<div class="col-sm-3">
                                    <label>Free Shipping</label>
                                    <div>
                                        <input id="freeshipping" type="checkbox" checked disabled value="" />
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Cost</label>
                                            <div>
                                                <input id="shippingcost" type="text" class="form-control reset" placeholder="Shp Cost" min="0" value="0.00" required disabled />
                                            </div>
                                        </div>
                                    </div>
                                </div>-->
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
                            <button type="button" class="btn btn-default" id="submitorder">Submit RA</button>
                        </div>
                    </div>
                    <div class="col-sm-1 pull-right align-bottom"></div>
                </div>
            </div>
        </form>

        <div id="noContactSelected" style="display:none;">
            <h3>Please save this Incident and select a Contact before creating a return.</h3>
        </div>
    </div>
    <!--Hidden fields-->
    <!-- Unit of measuer for the selected item-->
    <input type="text" hidden id="uom" />

</body>
</html>
<script src="<?php echo $serverAssetPath;?>js/cxobject.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/table.returns.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/returns.js"></script>