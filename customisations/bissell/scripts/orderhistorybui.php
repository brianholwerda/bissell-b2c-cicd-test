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
        <title>Order History</title>

        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/datatables.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/datatables.bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/generator-base.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/editor.bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/components-rounded.css" />
        <!--<link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/components.min.css" />-->
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/layout.css" /><!--layout.min.css-->
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/custom.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $serverCSSPath;?>css/components.min.css" />
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
        <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/accounting.min.js"></script>
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
        <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/bootstrap.min.js"></script>
        <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js"></script>
		<script src="https://bissell--tst2.custhelp.com/AgentWeb/module/extensibility/js/client/core/extension_loader.js"></script>
    </head>

    <body class="bootstrap">
		<style>
			.change_due_date.active_payment:hover
			{
				text-decoration: underline;
				cursor: pointer;
			}
			.validation_error_div
			{
				color: red;
				font-weight: bold;
			}
		</style>
        <div class="page-wrapper">
			<div id="orders_error" style="margin:5% 2.5%;border:1px solid black;padding:10px;font-weight:bold;">Loading orders...</div>
			<div id="orders_grid" style="display:none;width=100%;">
				<!-- Orders Grid -->
				<div class="col-md-12 orderitems spacer" style="width=100%;overflow-x:scroll;">
					<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="OrderHeader" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Order #</th>
								<th>Date</th>
								<th>Status</th>
								<th>Type</th>
								<th>Total</th>
								<!--<th>Pay Method</th>-->
								<th>Part #'s</th>
								<!--<th>CSR Name</th>-->
								<th>PO #</th>
								<th>Payment Type</th>
								<th>Auto Replenishment</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
        </div>
        <!-- Change CC Details -->
        <div class="modal fade" id="pay_hist_change_cc_details" tabindex="-1" role="basic" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-dialog-creditdcard">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Credit Card Payment Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form id="cd_creditcardform" action="" method="get">
                                <div class="form-body">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="cd_nameoncreditcard" name="nameoncreditcard" class="form-control cc-field" required />
                                                <div id="cd_error_cd_nameoncreditcard"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Card Type</label>
                                            <div class="col-sm-9">
                                                <select id="cd_creditcardtype" name="creditcardtype" class="form-control cc-field" required>
                                                    <option value="">[+]</option>
                                                    <option value="A">American Express</option>
                                                    <option value="D">Discover</option>
                                                    <option value="M">Master Card</option>
                                                    <option value="V">Visa</option>
                                                </select>
                                                <div id="cd_error_cd_creditcardtype"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Card #</label>
                                            <div class="col-sm-9">
                                                <div class="input-icon">
                                                    <i class="fa fa-credit-card"></i>
                                                    <input type="text" id="cd_creditcardnumber" name="creditcardnumber"
                                                           class="form-control cc-field" placeholder="Credit card #" required />
                                                    <div id="cd_error_cd_creditcardnumber"class="validation_error_div"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Expiration</label>
                                            <div class="col-sm-4">
                                                <select id="cd_creditcardexpirationmonth" name="creditcardexpirationmonth" class="form-control cc-field" required>
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
                                                <div id="cd_error_cd_creditcardexpirationmonth"class="validation_error_div"></div>
                                            </div>
                                            <div class="col-sm-5">
                                                <select id="cd_creditcardexpirationyear" name="creditcardexpirationyear"
                                                        class="form-control cc-field " required></select>
                                                <div id="cd_error_cd_creditcardexpirationyear"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <!--<div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">CVV</label>
                                            <div class="col-sm-9">
                                                <input type="number" id="cd_creditcardcvv" name="creditcardcvv"
                                                       class="form-control cc-small-input cc-field" maxlength="3" required />
                                                <div id="cd_error_cd_creditcardcvv"class="validation_error_div"></div>
                                            </div>
                                        </div>-->
                                    </div>
                                    <div class="col-sm-6 address-column">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Address 1</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="cd_creditcardaddress1" name="creditcardaddress1"
                                                       class="form-control cc-field" placeholder="Address 1" required />
                                                <div id="cd_error_cd_creditcardaddress1"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Address 2</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="cd_creditcardaddress2" name="creditcardaddress2"
                                                       class="form-control cc-field" placeholder="Address 2" />
                                                <div id="cd_error_cd_creditcardaddress2"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">City</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="cd_creditcardcity" name="creditcardcity"
                                                       class="form-control cc-field" placeholder="City" required />
                                                <div id="cd_error_cd_creditcardcity"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">State</label>
                                            <div class="col-sm-9">
                                                <select id="cd_creditcardstate" name="creditcardstate"
                                                        class="form-control cc-field" required></select>
                                                <div id="cd_error_cd_creditcardstate"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label cc-field-label">Postal Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" id="cd_creditcardpostalcode" name="creditcardpostalcode"
                                                       class="form-control cc-field" placeholder="Postal Code" required />
                                                <div id="cd_error_cd_creditcardpostalcode"class="validation_error_div"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline gray" data-dismiss="modal" id="cancel_cc_details">Cancel</button>
                        <button type="button" class="btn btn-outline sbold blue" id="save_cc_details">Save</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
            </div>
            <!-- Make A Payment -->
            <div class="modal fade" id="make_payment_modal" tabindex="-1" role="basic" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-dialog-creditdcard">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Credit Card Payment Details</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <form id="creditcardform" action="" method="get">
                                    <div class="form-body">
                                        <div class="col-sm-3">
                                            <label for="paymentdropdown">Payment Type:</label>
                                            <select id="payment_dropdown" name="paymentdropdown" class="form-control cc-field" required>
                                                <option value="0">Pay in Full</option>
                                                <option value="1">Pay Amount</option>
                                                <option value="2">Select Payments</option>
                                            </select>
                                            <div id="error_paymentdropdown"></div>
                                        </div>
                                        <div class="col-sm-3">
                                            <label for="paymentamount">Amount:</label><input type="text" id="payment_amount" name="paymentamount" class="form-control cc-field" style="display:inline-block;" required />
                                            <div id="error_paymentamount"></div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                  <div id="select_payment_list_div" style="clear:both;display:none;">
                                                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered display" id="SelectPayments" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Pay</th>
                                                                <th>Payment ID</th>
                                                                <th>Amount</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                            <!--<div class="form-group" style="clear:both;">
                                                <label class="col-sm-3 control-label cc-field-label">Name</label>
                                                <div class="col-sm-9">
                                                    <input type="text" id="nameoncreditcard" name="nameoncreditcard" class="form-control cc-field" required />
                                                    <div id="error_nameoncreditcard"></div>
                                                </div>
                                            </div>-->
                                            <!--<div class="form-group">
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
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label cc-field-label">CVV</label>
                                                <div class="col-sm-9">
                                                    <input type="number" id="creditcardcvv" name="creditcardcvv"
                                                            class="form-control cc-small-input cc-field" maxlength="3" required />
                                                    <div id="error_creditcardcvv"></div>
                                                </div>
                                            </div>
                                        </div>-->
                                    <!--<div class="col-sm-6 address-column">
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
                                    </div>-->
                                </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline gray" data-dismiss="modal" id="cancel_payment">Cancel</button>
                        <button type="button" class="btn btn-outline sbold blue" id="queue_payment">Save</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- JavaScript/jQuery -->
        <!--<script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/app.js"></script>-->
        <script type="text/javascript" charset="utf-8" src="<?php echo $serverAssetPath;?>js/table.orderhistorybui.js"></script>
</body>
</html>