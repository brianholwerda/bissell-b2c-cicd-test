<template>
    <v-overlay :model-value="isOtherRefundLoading" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important; z-index: 9999;">
      <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
        <v-progress-circular indeterminate size="64" color="primary" />
        <div class="text-h6 mt-4" style="color: #000;">
          <v-icon color="primary" class="mr-2">mdi-reload</v-icon>
          {{ otherRefundSpinnerLabel }}
        </div>
      </div>
    </v-overlay>
  <v-overlay :model-value="isLoading" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important;">
    <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
      <v-progress-circular indeterminate size="64" color="primary" />
      <div v-if="spinnerLabel" class="text-h6 mt-4" style="color: #000;">
        <v-icon color="primary" class="mr-2">mdi-reload</v-icon>
        {{ spinnerLabel }}
      </div>
    </div>
  </v-overlay>
    <v-snackbar v-model="showSuccessSnackbar" color="success" timeout="7000" location="top right">
      <template v-slot:actions>
        <v-btn variant="text" @click="showSuccessSnackbar = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
      {{ successMessage }}
    </v-snackbar>
  <div v-if="showLineItems">
    <v-row class="BUIExtensionRow">
      <v-col cols="10" v-if="OpenRefundItems.length > 0" class="text-right">
      </v-col>
      <v-col class="text-right">
        <v-btn prepend-icon="mdi-refresh" class="BissellButton" @click="LoadRefunds()">
          Refresh Open Refunds
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-data-table
        :headers="OpenRefundItemsHeaders"
        :items="OpenRefundItems"
        :loading="isFetchingRefunds"
        :items-per-page="100">

        <template v-slot:headers="{columns}">
          <tr class="BissellBanner">
            <template v-for="column in columns" :key="column.key">
              <td>
                {{column.title}}
              </td>
            </template>
          </tr>
        </template>

        <template v-slot:item="{ item }">
          <tr :class="item.isUrgentRefund ? 'urgent-row' : ''">
            <td>
              <v-icon v-if="item.isUrgentRefund" color="error" size="20" class="mr-1 pulse-icon-urgent">mdi-alert-circle</v-icon>
              {{item.refundId}}
              <v-btn
                icon="mdi-open-in-new"
                size="small"
                variant="text"
                @click="LoadOrderDetails(item)"
              ></v-btn>
            </td>
            <td>
              {{item.cRMReferenceNumber}}
              <v-btn
                icon="mdi-open-in-new"
                size="small"
                variant="text"
                @click="OpenIncidentbyRefNo(item.cRMReferenceNumber)"
              ></v-btn>
            </td>
            <td>{{item.orderNumber}}</td>
            <td>{{ formatDateET(item.creationDate) }}</td>
            <td>{{item.paymentType}}</td>
            <td>
              <v-chip v-if="item.isUrgentRefund" color="error" size="small" variant="flat" class="font-weight-bold">
                <v-icon start size="16">mdi-alert</v-icon>
                URGENT
              </v-chip>
              <v-chip v-else color="grey-lighten-2" size="small" variant="flat">
                Normal
              </v-chip>
            </td>
            <td>
              <v-btn
                aria-label="Open Refund"
                density="compact"
                prepend-icon="mdi-open-in-app"
                size="small"
                variant="text"
                @click="LoadOrderDetails(item)"
              >
                <template v-slot:prepend>
                  <v-icon color="success"></v-icon>
                </template>
                Open Refund
              </v-btn>
            </td>
          </tr>
        </template>

        <template v-slot:loading>
          <v-progress-circular indeterminate color="rgb(var(--v-theme-bissellButtonRed))"></v-progress-circular>
          &nbsp;Refreshing Refunds...
        </template>

        <template v-slot:no-data>
          <div v-if="WebhookError">
            {{WebhookErrorMessage}}
          </div>
          <div v-else-if="OpenRefundItems.length === 0 && !isFetchingRefunds" class="pa-8 text-center">
            <v-icon size="64" color="grey-lighten-1">mdi-information-outline</v-icon>
            <div class="text-h6 mt-4 text-grey">No Refunds Found</div>
            <div class="text-body-2 text-grey-darken-1 mt-2">There are currently no refunds pending review.</div>
          </div>
          <div v-else>
            Loading Refunds....
          </div>
        </template>
        
      </v-data-table>
    </v-row>
  </div>
  

    <div v-if="showData">
    <v-row>
      <v-col cols="12">
        <div class="text-h5 font-weight-bold mb-1">
          Refund ID: {{ refundInfo?.refundData?.refundId || '' }}
        </div>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <div class="text-body-1 font-weight-bold mb-1">
          OSvC Incident:
          <span
            v-if="refundInfo?.refundData?.cRMReferenceNumber"
            class="osvc-incident-link"
            @click="OpenIncidentbyRefNo(refundInfo.refundData.cRMReferenceNumber)"
            style="cursor:pointer; text-decoration:underline; color:#1976d2;"
          >
            {{ refundInfo.refundData.cRMReferenceNumber }}
          </span>
        </div>
      </v-col>
    </v-row>
    <v-row class="mb-4">
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-package-variant</v-icon>
            Order Details
          </div>
          <div class="text-body-2 mb-1"><strong>Order Number:</strong> {{ headerRefundData?.OrderNumber || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Order Date:</strong> {{ (headerRefundData?.OrderDate || '').replace('T00:00:00', '') || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Consumer ID:</strong> {{ headerRefundData?.ConsumerID || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Oracle Account:</strong> {{ headerRefundData?.OracleAccountNo || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Order Status:</strong> {{ headerRefundData?.OrderStatus || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Payment Type:</strong> {{ headerRefundData?.PaymentType || '' }}</div>
          <div class="text-body-2 mb-1"><strong>PO Number:</strong> {{ headerRefundData?.PONumber || '' }}</div>
          <div class="text-body-2"><strong>Order Source:</strong> {{ headerRefundData?.SalesChannel || '' }}</div>
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-currency-usd</v-icon>
            Order Summary
          </div>
          <div class="text-body-2 mb-1"><strong>Subtotal:</strong> {{ formatCurrency(headerRefundData?.SubTotal || 0) }}</div>
          <div class="text-body-2 mb-1"><strong>Shipping Charge:</strong> {{ formatCurrency(headerRefundData?.ShipAmount || 0) }}</div>
          <div class="text-body-2 mb-1"><strong>Estimated Shipping Tax:</strong> {{ formatCurrency(shippingTaxAmount || 0) }}</div>
          <div class="text-body-2 mb-1"><strong>Sales Tax:</strong> {{ formatCurrency(headerRefundData?.SalesTax?.Amount || 0) }}</div>
          <div class="text-body-2 mb-3"><strong>Estimated Tax Rate:</strong> {{ (parseFloat(taxPercentage) || 0).toFixed(2) }}%</div>
          <v-divider class="my-3"></v-divider>
          <div class="text-h6 font-weight-bold"><strong>Order Total:</strong> <span class="text-primary">{{ formatCurrency(headerRefundData?.OrderTotal || 0) }}</span></div>
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-truck-delivery</v-icon>
            Shipping Address
          </div>
          <div class="text-body-2 mb-1"><strong>{{ refundShippingAddress.Name }}</strong></div>
          <div class="text-body-2">{{ refundShippingAddress.Street1 }}</div>
          <div class="text-body-2">{{ refundShippingAddress.City }}, {{ refundShippingAddress.State }} {{ refundShippingAddress.Postal }}</div>
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-credit-card</v-icon>
            Billing Address
          </div>
          <div class="text-body-2 mb-1"><strong>{{ refundBillingAddress.Name }}</strong></div>
          <div class="text-body-2">{{ refundBillingAddress.Street1 }}</div>
          <div class="text-body-2">{{ refundBillingAddress.City }}, {{ refundBillingAddress.State }} {{ refundBillingAddress.Postal }}</div>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mb-4">
        <v-col cols="12">
          <v-card variant="outlined" class="pa-4">
            <div class="text-h6 mb-3 text-primary">
              <v-icon class="mr-2">mdi-file-document-multiple</v-icon>
              Other Refunds
            </div>
            <div class="text-body-1 mb-2">
                <template v-if="uniqueOtherRefunds.length === 0">
                  No other refunds found for this Consumer ID.
                </template>
                <template v-else>
                  {{ uniqueOtherRefunds.length }} other refunds related to this Consumer ID. Click below to view other refunds.
                </template>
            </div>
              <template v-if="uniqueOtherRefunds.length > 0">
                <v-btn
                  :color="showOtherRefundsTable ? 'accent' : 'primary'"
                  variant="elevated"
                  @click="showOtherRefundsTable = !showOtherRefundsTable"
                  class="mb-2"
                  prepend-icon="mdi-magnify"
                >
                  {{ showOtherRefundsTable ? 'Hide Other Refunds' : 'Show Other Refunds' }}
                </v-btn>
                <template v-if="showOtherRefundsTable">
                <v-data-table
                  :headers="OtherRefundsHeaders"
                  :items="uniqueOtherRefunds"
                  class="elevation-0"
                >
                  <template v-slot:headers="{ columns }">
                    <tr style="background-color: #f5f5f5;">
                      <template v-for="column in columns" :key="column.key">
                        <td class="text-subtitle-2 font-weight-bold pa-4">
                          {{column.title}}
                        </td>
                      </template>
                    </tr>
                  </template>
                  <template v-slot:item="{ item }">
                    <tr class="other-refund-row">
                      <td>{{ item.refundId }}</td>
                      <td>
                        {{ item.cRMReferenceNumber }}
                        <v-btn icon="mdi-open-in-new" size="small" variant="text" @click="OpenIncidentbyRefNo(item.cRMReferenceNumber)">
                          <v-icon>mdi-open-in-new</v-icon>
                        </v-btn>
                      </td>
                      <td>{{ item.orderNumber }}</td>
                      <td>{{ formatDateET(item.creationDate) }}</td>
                      <td>{{ item.status }}</td>
                    </tr>
                  </template>
                  <template v-slot:no-data>
                    <div class="pa-8 text-center">
                      <v-icon size="48" color="grey-lighten-1">mdi-information-outline</v-icon>
                      <div class="text-body-1 mt-3 text-grey">No other refunds found for this Consumer ID.</div>
                    </div>
                  </template>
                </v-data-table>
                </template>
              </template>
          </v-card>
        </v-col>
      </v-row>
    <v-row class="mb-3">
      <v-col cols="12">
        <v-card variant="outlined">
          <div class="d-flex align-center pa-4 pb-0">
            <div class="text-h5 mr-4">
              <v-icon class="mr-2">mdi-format-list-checkbox</v-icon>
              Refund Line Items
            </div>
            <v-btn
              color="success"
              variant="outlined"
              prepend-icon="mdi-check-all"
              @click="approveAllRows"
              class="mr-2"
            >
              Approve All Items
            </v-btn>
            <v-btn
              color="error"
              variant="outlined"
              prepend-icon="mdi-close-circle"
              @click="declineAllRows"
            >
              Decline All Items
            </v-btn>
          </div>
          <div class="d-flex align-center pa-4 pt-2 bg-grey-lighten-4">
            <div class="text-subtitle-2 font-weight-bold mr-3">
              <v-icon size="20" class="mr-1">mdi-auto-fix</v-icon>
              Apply Reason Code to All:
            </div>
            <v-select
              v-model="bulkApproveReasonCode"
              :items="getReasonCodeOptions('Approve')"
              item-title="display"
              item-value="value"
              variant="outlined"
              density="compact"
              hide-details
              placeholder="Select Reason Code"
              style="max-width: 200px"
              class="mr-2"
            />
            <v-btn
              color="success"
              size="small"
              variant="elevated"
              prepend-icon="mdi-arrow-down-bold"
              @click="applyReasonToAll('Approve')"
              :disabled="!bulkApproveReasonCode"
            >
              Apply to All Approved
            </v-btn>
            <v-divider vertical class="mx-4"></v-divider>
            <v-select
              v-model="bulkDeclineReasonCode"
              :items="getReasonCodeOptions('Decline')"
              item-title="display"
              item-value="value"
              variant="outlined"
              density="compact"
              hide-details
              placeholder="Select Reason Code"
              style="max-width: 200px"
              class="mr-2"
            />
            <v-btn
              color="error"
              size="small"
              variant="elevated"
              prepend-icon="mdi-arrow-down-bold"
              @click="applyReasonToAll('Decline')"
              :disabled="!bulkDeclineReasonCode"
            >
              Apply to All Declined
            </v-btn>
          </div>
          <v-data-table
            :headers="RefundLineHeaders"
            :items="refundLineItems"
            class="elevation-0"
          >
            <template v-slot:headers="{ columns }">
              <tr style="background-color: #f5f5f5;">
                <template v-for="column in columns" :key="column.key">
                  <td class="text-subtitle-2 font-weight-bold pa-4">
                    {{column.title}}
                  </td>
                </template>
              </tr>
            </template>
            <template v-slot:item="{ item }">
              <tr :class="getRowClass(item)" class="refund-item-row">
                <td>
                  <v-radio-group
                    v-model="item.ApprovalStatus"
                    inline
                    density="compact"
                    @update:model-value="val => handleApproveRadio(val, item)"
                  >
                    <v-radio label="Approve" value="Approve"></v-radio>
                    <v-radio label="Decline" value="Decline"></v-radio>
                  </v-radio-group>
                </td>
                <td>{{ item.PartNo }}</td>
                <td>{{ item.PartDesc }}</td>
                <td>
                  <v-select
                    v-model="item.Qty"
                    :items="buildQtyOptions(item.MaxQty)"
                    @update:model-value="val => {
                      item.Qty = val;
                      item.Price = parseFloat((item.Qty * item.UnitRefundTotal).toFixed(2));
                      item._editingPrice = formatCurrency(item.Price);
                    }"
                    variant="outlined"
                    density="compact"
                    hide-details
                    style="max-width: 80px"
                  />
                </td>
                <td> 
                  <v-text-field
                    :model-value="item._editingPrice !== undefined ? item._editingPrice : formatCurrency(item.Price)"
                    @focus="item._editingPrice = formatCurrency(item.Price)"
                    @blur="
                      if (item.ApprovalStatus === 'Decline') {
                        item.Price = 0;
                        item._editingPrice = formatCurrency(0);
                      } else {
                        const parsed = parseCurrency(item._editingPrice);
                        item.Price = isNaN(parsed) ? item.Price : parsed;
                        item._editingPrice = formatCurrency(item.Price);
                      }
                    "
                    @update:model-value="val => item._editingPrice = val"
                    :readonly="item.ApprovalStatus === 'Decline'"
                    hide-details
                    density="compact"
                    style="max-width: 120px"
                    type="text"
                  />
                </td>
                <td>
                  <v-select
                    v-model="item.ReasonCode"
                    :items="getReasonCodeOptions(item.ApprovalStatus)"
                    item-title="display"
                    item-value="value"
                    variant="outlined"
                    density="compact"
                    hide-details
                    style="min-width: 180px"
                  />
                </td>
              </tr>
            </template>
            <template v-slot:no-data>
              <div class="pa-8 text-center">
                <v-icon size="64" color="grey-lighten-1">mdi-package-variant-closed</v-icon>
                <div class="text-h6 mt-4 text-grey">No Line Items</div>
                <div class="text-body-2 text-grey-darken-1 mt-2">This is a shipping-only refund.</div>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
    
    <!-- Non-Refundable Line Items Table -->
    <v-row v-if="nonRefundableLineItems.length > 0" class="mb-4">
      <v-col cols="12">
        <v-card variant="outlined">
          <v-card-title class="text-h6 pa-4 bg-grey-lighten-4">
            <v-icon class="mr-2" color="warning">mdi-information-outline</v-icon>
            Add Additional Line Items to Refund
          </v-card-title>
          <v-data-table
            :headers="NonRefundableLineHeaders"
            :items="nonRefundableLineItems"
            :items-per-page="100"
            class="elevation-0">
          
            <template v-slot:headers="{columns}">
              <tr style="background-color: #fff3e0;">
                <td class="text-subtitle-2 font-weight-medium pa-4">Actions</td>
                <template v-for="column in columns" :key="column.key">
                  <td class="text-subtitle-2 font-weight-medium pa-4">
                    {{column.title}}
                  </td>
                </template>
              </tr>
            </template>
          
            <template v-slot:item="{ item }">
              <tr class="non-refundable-row">
                <td>
                  <v-btn
                    size="small"
                    color="success"
                    variant="elevated"
                    @click="moveToRefundable(item)"
                    prepend-icon="mdi-plus-circle"
                  >
                    Add to Refund
                  </v-btn>
                </td>
                <td>{{ item.PartNo }}</td>
                <td>{{ item.PartDesc || item.Description }}</td>
                <td>{{ item.Qty || item.Quantity }}</td>
                <td>{{ formatCurrency(item.Price || item.UnitPrice || 0) }}</td>
              </tr>
            </template>
          
            <template v-slot:no-data>
              <div class="pa-8 text-center">
                <v-icon size="48" color="success">mdi-check-circle-outline</v-icon>
                <div class="text-body-1 mt-3 text-grey">All order items are eligible for refund.</div>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mb-4">
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4 refund-summary-card">
          <div class="text-h6 mb-3 text-success">
            <v-icon class="mr-2">mdi-calculator</v-icon>
            Refund Summary
          </div>
          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2">Line Items:</span>
            <span class="text-body-2">{{ formatCurrency(approvedLineItemsTotal) }}</span>
          </div>
          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2">Shipping:</span>
            <span class="text-body-2">
              {{ includeShippingRefund
                ? formatCurrency((usedReturnLabelCC === true)
                    ? parseFloat(shippingRefundAmount) - 4.95
                    : parseFloat(shippingRefundAmount))
                : (usedReturnLabelCC === true
                    ? formatCurrency(-4.95)
                    : '$0.00') }}
            </span>
          </div>
          <v-divider class="my-3"></v-divider>
          <div class="d-flex justify-space-between total-refund-row">
            <span class="text-h6 font-weight-bold">Total Refund:</span>
            <span class="text-h6 font-weight-bold text-success">{{ formatCurrency(approvedRefundTotal) }}</span>
          </div>
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-truck-fast</v-icon>
            Shipping Details
          </div>
          <div class="d-flex justify-space-between mb-2">
            <span class="text-body-2">Base Amount:</span>
            <span class="text-body-2">{{ formatCurrency(shippingAmount) }}</span>
          </div>
          <div class="d-flex justify-space-between mb-3">
            <span class="text-body-2">Tax:</span>
            <span class="text-body-2">{{ formatCurrency(shippingTaxAmount) }}</span>
          </div>
          <div :class="['include-refund-checkbox-wrapper', { 'include-refund-selected': includeShippingRefund }]">
            <v-checkbox
              v-model="includeShippingRefund"
              label="Include in refund"
              hide-details
              density="compact"
              color="success"
            ></v-checkbox>
          </div>
          <v-text-field
            v-if="includeShippingRefund"
            :model-value="typeof shippingRefundAmountEditing !== 'undefined' ? shippingRefundAmountEditing : formatCurrency(shippingRefundAmount)"
            @focus="shippingRefundAmountEditing = shippingRefundAmount"
            @blur="
              shippingRefundAmount = parseFloat(shippingRefundAmountEditing) || 0;
              delete shippingRefundAmountEditing;
            "
            @update:model-value="val => shippingRefundAmountEditing = val"
            label="Refund Amount"
            hide-details
            density="compact"
            variant="outlined"
            class="mt-3"
            type="text"
          />
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-label</v-icon>
            Return Label
          </div>
          <div class="text-body-2 mb-2">Did Consumer use our Return Label?</div>
          <v-radio-group v-model="usedReturnLabelCC" inline hide-details>
            <v-radio 
              label="Yes" 
              :value="true"
              :class="{ 'highlighted-yes': usedReturnLabelCC === true }"
              color="success"
            ></v-radio>
            <v-radio 
              label="No" 
              :value="false"
              :class="{ 'highlighted-no': usedReturnLabelCC === false }"
              color="error"
            ></v-radio>
          </v-radio-group>
          <div v-if="usedReturnLabelCC === true" class="mt-3 text-body-2" style="color: #d32f2f;">
            ($4.95) will be auto-deducted from the shipping refund amount specified below.
          </div>
        </v-card>
      </v-col>
      <v-col cols="3">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-comment-text</v-icon>
            Comments
          </div>
          <v-textarea
            v-model="refundComments"
            label="Add Comments (optional)"
            auto-grow
            rows="4"
            hide-details
            variant="outlined"
            class="mt-2"
          />
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mt-4">
      <v-col cols="12" class="text-center">
        <v-btn 
          color="success" 
          size="large"
          elevation="2"
          @click="submitRefundRequest"
          prepend-icon="mdi-check-circle"
        >
          Submit Refund Request
        </v-btn>
        <v-btn 
          color="grey" 
          size="large"
          variant="outlined"
          @click="closeRefund"
          prepend-icon="mdi-close"
          class="ml-4"
        >
          Close Refund Details
        </v-btn>
      </v-col>
    </v-row>
  </div>

  <v-dialog v-model="showPrintLabelModal" width="auto" persistent>
    <v-card class="BissellCard" title="Print RA Label(s)" variant="outlined">

      <v-card-text>
        <div v-html="printLabelText"></div>
      </v-card-text>

      <v-card-actions>
        <v-btn prepend-icon="mdi-check" :loading="SetPrintLabelInProgress" @click="PrintLabel()">
          <template v-slot:loader>
            <v-progress-circular color="success" indeterminate></v-progress-circular>
          </template>
          <template v-slot:prepend>
            <v-icon color="success"></v-icon>
          </template>
          Yes
        </v-btn>
        <v-btn prepend-icon="mdi-close-circle-outline" @click="showPrintLabelModal = false">
          <template v-slot:prepend>
            <v-icon color="error"></v-icon>
          </template>
          No
        </v-btn>
      </v-card-actions>

    </v-card>
  </v-dialog>
</template>

<script setup>

const isOtherRefundLoading = ref(false);
const otherRefundSpinnerLabel = ref('Retrieving Refund Incident...');
const uniqueOtherRefunds = computed(() => {
  const seen = new Set();
  const currentId = refundInfo.value && refundInfo.value.refundData ? refundInfo.value.refundData.refundId : undefined;
  return otherRefunds.value.filter(r => {
    if (!r.refundId || (currentId !== undefined && r.refundId == currentId) || seen.has(r.refundId)) return false;
    seen.add(r.refundId);
    return true;
  });
});
const showOtherRefundsTable = ref(false);
const OtherRefundsHeaders = [
  { key: 'refundId', title: 'Refund ID' },
  { key: 'cRMReferenceNumber', title: 'Incident' },
  { key: 'orderNumber', title: 'Order Number' },
  { key: 'creationDate', title: 'Creation Date' },
  { key: 'status', title: 'Status' }
];

// This should be set from the API response
const otherRefunds = ref([]);

// When loading refund details, set otherRefunds.value = response.OtherRefunds || []
function formatToTwoDecimals(val) {
  let num = parseFloat(val);
  if (isNaN(num)) return '';
  return num.toFixed(2);
}

import { watch, ref, computed, nextTick } from 'vue';
// Add reactive value for Return Label radio group (boolean, matches Warehouse.vue)
const usedReturnLabelCC = ref(null);
const includeShippingRefund = ref(false);
const shippingRefundAmount = ref(0);
const shippingAmount = ref(0);
const shippingTaxAmount = ref(0);
const refundComments = ref('');
const bulkApproveReasonCode = ref('');
const bulkDeclineReasonCode = ref('');

// Recalculate shipping refund amount when checkbox is checked
watch(includeShippingRefund, (newVal, oldVal) => {
  if (newVal === true && oldVal === false) {
    shippingRefundAmount.value = (parseFloat(shippingAmount.value) || 0) + (parseFloat(shippingTaxAmount.value) || 0);
  }
});

    // Snackbar state for Option 1 confirmation
    const showSuccessSnackbar = ref(false);
    const successMessage = ref('');
    const showSuccessOverlay = ref(false);
  
  const OpenRefundItemsHeaders = [
    { key: "refundId", title: "Refund ID" },
    { key: "cRMReferenceNumber", title: "Incident" },
    { key: "orderNumber", title: "Order Number" },
    { key: "creationDate", title: "Date Submitted" },
    { key: "paymentType", title: "Payment Type" },
    { key: "Urgency", title: "Urgency" },
    { key: "OpenRefund", title: "Open Refund" }
  ];

  /***** TABLE ELEMENTS *****/
  const RefundLineHeaders = [
    { key: "ApprovalStatus", title: "Approval" },
    { key: "PartNo", title: "Part Number" },
    { key: "PartDesc", title: "Description" },
    { key: "Qty", title: "Quantity" },
    { key: "Price", title: "Refund Amount" },
    { key: "ReasonCode", title: "Reason Code" }
  ];

  const NonRefundableLineHeaders = [
    { key: "PartNo", title: "Part Number" },
    { key: "PartDesc", title: "Description" },
    { key: "Qty", title: "Quantity" },
    { key: "Price", title: "Price" }
  ];

  const refundBillingAddress = ref(null);
  const refundShippingAddress = ref(null);
  const refundLineItems = ref([]);
  const reasonCodes = ref([]);
  const refundData = ref(null);
  const refundInfo = ref(null);
  const showData = ref(false);
  const showLineItems = ref(true);
  const isLoading = ref(false);
  const OpenRefundItems = ref([]);
  const isFetchingRefunds = ref(true);
  const taxPercentage = ref(0);
  const refundTotals = ref(null);
  const spinnerLabel = ref('');

  const headerRefundData = computed(() => {
    return refundData.value || {};
  });

  const approvedLineItemsTotal = computed(() => {
    if (!refundLineItems.value || refundLineItems.value.length === 0) {
      return 0;
    }
    
    return refundLineItems.value
      .filter(item => item.ApprovalStatus === 'Approve')
      .reduce((total, item) => total + (parseFloat(item.Price) || 0), 0);
  });

  const approvedRefundTotal = computed(() => {
    const lineItemsTotal = approvedLineItemsTotal.value;
    let shippingTotal = includeShippingRefund.value ? (parseFloat(shippingRefundAmount.value) || 0) : 0;
    if (usedReturnLabelCC.value === true) {
      shippingTotal -= 4.95;
    }
    return lineItemsTotal + shippingTotal;
  });

  const nonRefundableLineItems = computed(() => {
    if (!refundData.value || !refundData.value.LineItems || !refundLineItems.value) {
      return [];
    }

    // Get part numbers from refund items for comparison
    const refundPartNumbers = refundLineItems.value.map(item => item.PartNo);
    
    // Filter OrderInfo.LineItems to find items not in refund data
    return refundData.value.LineItems.filter(orderItem => 
      !refundPartNumbers.includes(orderItem.PartNo)
    );
  });

  // Returns dropdown options for a given status (Approve/Decline)
  function getReasonCodeOptions(status) {
    const base = [{ display: '--Select Reason Code--', value: '' }];
    if (!status || !Array.isArray(reasonCodes.value)) return base;
    return base.concat(
      reasonCodes.value.filter(rc => rc.consumerCareAction === status)
    );
  }

  function handleApproveRadio(val, item) {
    console.log('ApprovalStatus changed to:', val, 'for item:', item);
    // Reset reason code when approval changes
    item.ReasonCode = '';
    if (val === 'Approve' && item.PartNo && item.PartNo.startsWith('DPFU')) {
      window.alert('BISSELL Pet Foundation Donations cannot be refunded.');
      nextTick(() => {
        item.ApprovalStatus = '';
      });
      return;
    }
    if (val === 'Approve') {
      if(typeof item.Price == 'undefined' || item.Price == 0){
        if (typeof item._previousPrice !== 'undefined' && item._previousPrice > 0) {
          item.Price = item._previousPrice;
          item.Qty = item._previousQty;
        } else if (typeof item._movedInitialRefundAmount !== 'undefined' && item._movedInitialRefundAmount > 0) {
          const qty = parseFloat(item.Qty) || 0;
          const unit = parseFloat(item._previousPrice) || 0;
          item.Price = parseFloat((qty * unit).toFixed(2));
        } else if (typeof item._originalLineRefundAmount !== 'undefined' && item._originalLineRefundAmount > 0) {
          item.Price = item._originalLineRefundAmount;
        } else if (!item.Price || item.Price === 0) {
          const qty = parseFloat(item.Qty) || 0;
          const unit = parseFloat(item.UnitRefundTotal) || 0;
          item.Price = parseFloat((qty * unit).toFixed(2));
        }
      }
      item._editingPrice = formatCurrency(item.Price);
    } else if (val === 'Decline') {
      item._previousPrice = item.Price;
      item._previousQty = item.Qty;
      item.Price = 0;
      item._editingPrice = formatCurrency(0);
    }
  }

  function resetRefundState() {
    showData.value = false;
    showLineItems.value = true;
    refundData.value = null;
    refundLineItems.value = [];
    refundBillingAddress.value = null;
    refundShippingAddress.value = null;
    includeShippingRefund.value = false;
    shippingRefundAmount.value = 0;
    shippingAmount.value = 0;
    shippingTaxAmount.value = 0;
    taxPercentage.value = 0;
    refundTotals.value = null;
    refundComments.value = '';
    bulkApproveReasonCode.value = '';
    bulkDeclineReasonCode.value = '';
    LoadRefunds();
  }

  function closeRefund() {
    resetRefundState();
  }

  function LoadOrderDetails(item) {
    showLineItems.value = false;
    spinnerLabel.value = `Retrieving Details for Refund ID ${item.refundId || ''}...`;
    isLoading.value = true;
    
    // Clear previous data to prevent mixing of old and new refund details
    refundLineItems.value = [];
    refundBillingAddress.value = null;
    refundShippingAddress.value = null;
    shippingRefundAmount.value = 0;
    shippingAmount.value = 0;
    shippingTaxAmount.value = 0;
    taxPercentage.value = 0;
    refundTotals.value = null;
    refundComments.value = '';

    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify({
        Action: "GetRefundDetails",
        OrderNumber: item.orderNumber,
        RefundID: item.refundId,
        Session: refundsIntegration.value.Session
      })
    });

    fetch(webhookCall).then((webhookResponse) => {
      console.log(webhookResponse);
      
      isLoading.value = false;
      spinnerLabel.value = '';

      if(!webhookResponse.ok) {
        console.log(webhookResponse.status + " - " + webhookResponse.statusText + ": " + webhookResponse.url);
        return;
      }

      webhookResponse.json().then((webhookJson) => {
        console.log(webhookJson);
        showData.value = true;
        showLineItems.value = false;
        refundData.value = webhookJson.OrderInfo;
        refundInfo.value = webhookJson.RefundInfo;

        // Set ReasonCodes from API
        reasonCodes.value = Array.isArray(webhookJson.ReasonCodes) ? webhookJson.ReasonCodes : [];

        // Set default for return label radio buttons
        usedReturnLabelCC.value = webhookJson.RefundInfo?.refundData?.usedOurReturnLabel ?? null;

        refundBillingAddress.value = webhookJson.OrderInfo.BillingAddress;
        refundShippingAddress.value = webhookJson.OrderInfo.ShippingAddress;
        refundTotals.value = webhookJson.RefundTotals;

        // Calculate shipping refund amount
        shippingAmount.value = parseFloat(webhookJson.OrderInfo.ShipAmount) || 0;
        shippingTaxAmount.value = parseFloat(webhookJson.RefundTotals.shipTaxAmount) || 0;
        shippingRefundAmount.value = shippingAmount.value + shippingTaxAmount.value;
        taxPercentage.value = parseFloat(webhookJson.RefundTotals.taxPercentage) || 0;

        // If manual shipping refund entry exists and > 0, set checkbox and amount
        const manualShippingRefund = parseFloat(webhookJson.RefundInfo?.refundData?.shippingRefundManualEntry) || 0;
        // Set OtherRefunds table data
        otherRefunds.value = webhookJson.OtherRefunds || [];
        if (manualShippingRefund > 0) {
          includeShippingRefund.value = true;
          shippingRefundAmount.value = manualShippingRefund;
        }

        webhookJson.RefundInfo.refundData.lineItems.forEach((nextLine, i) => {
          var lineRefundTotal = 0;
          var qtyReturned = parseFloat(nextLine.qtyReturned) || 0;
          var maxQty = 0;
          // Find the corresponding LineRefundTotal from RefundTotals
          webhookJson.RefundTotals.lineItems.forEach((totalLine) => {
            if(totalLine.PartNumber == nextLine.partNumber){
              lineRefundTotal = parseFloat(totalLine.LineRefundTotal) || 0;
            }
          });
          // Find the maximum quantity from OrderInfo.LineItems
          webhookJson.OrderInfo.LineItems.forEach((orderLine) => {
            if(orderLine.PartNo == nextLine.partNumber){
              maxQty = parseFloat(orderLine.Qty) || 0;
            }
          });
          // Use lineRefundAmount if present and > 0, else calculate
          var calculatedRefundAmount = (nextLine.lineRefundAmount && parseFloat(nextLine.lineRefundAmount) > 0)
            ? parseFloat(nextLine.lineRefundAmount)
            : qtyReturned * lineRefundTotal;
          refundLineItems.value.push({
            PartNo: nextLine.partNumber,
            PartDesc: nextLine.partDescription,
            Qty: qtyReturned,
            MaxQty: maxQty,
            UnitRefundTotal: lineRefundTotal,
            Price: calculatedRefundAmount,
            ApprovalStatus: null,
            ReasonCode: '',
            lineNumber: nextLine.lineNumber || null,
            lineRefundAmount: (typeof nextLine.lineRefundAmount !== 'undefined' && !isNaN(parseFloat(nextLine.lineRefundAmount))) ? parseFloat(nextLine.lineRefundAmount) : undefined,
            _originalLineRefundAmount: (typeof nextLine.lineRefundAmount !== 'undefined' && parseFloat(nextLine.lineRefundAmount) > 0) ? parseFloat(nextLine.lineRefundAmount) : undefined
          });
        });
      });
    }).catch(error => {
      console.log(error);
      isLoading.value = false;
      spinnerLabel.value = '';
    });
  }

  function LoadRefunds() {
    isFetchingRefunds.value = true;
    OpenRefundItems.value.splice(0,OpenRefundItems.value.length);
    console.log("Loading CC Review Refunds...");
    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify({
        Action: "GetCCReviewRefunds",
        Session: refundsIntegration.value.Session
      })
    });

    fetch(webhookCall).then((webhookResponse) => {
      isFetchingRefunds.value = false;

      if(!webhookResponse.ok) {
        return;
      }

      webhookResponse.json().then((webhookJson) => {
        console.log(webhookJson);
        webhookJson.forEach((nextRefund) => {
          OpenRefundItems.value.push(nextRefund);
        });
      });
    }).catch(error => {
      isFetchingRefunds.value = false;
    });
  }

  /***** INTEGRATION DETAILS *****/
  const oracleIntegration = ref(null);
  const refundsIntegration = ref(null);
  const WorkspaceContext = ref(null);

  function buildQtyOptions(maxQty) {
    const max = parseInt(maxQty) || 1;
    return Array.from({ length: max }, (_, i) => i + 1);
  }

  function getRowClass(item) {
    if (item.ApprovalStatus === 'Approve') {
      return 'row-approved';
    } else if (item.ApprovalStatus === 'Decline') {
      return 'row-denied';
    }
    return '';
  }

  function approveAllRows() {
    if (refundLineItems.value && refundLineItems.value.length > 0) {
      refundLineItems.value.forEach(item => {
        if (item.PartNo && item.PartNo.startsWith('DPFU')) {
          window.alert('BISSELL Pet Foundation Donations cannot be refunded.');
        } else {
          item.ApprovalStatus = 'Approve';
          handleApproveRadio('Approve', item);
        }
      });
    }
  }

  function declineAllRows() {
    if (refundLineItems.value && refundLineItems.value.length > 0) {
      refundLineItems.value.forEach(item => {
        if (item.PartNo && item.PartNo.startsWith('DPFU')) {
          window.alert('BISSELL Pet Foundation Donations cannot be refunded.');
        } else {
          item.ApprovalStatus = 'Decline';
          handleApproveRadio('Decline', item);
        }
      });
    }
  }

  function applyReasonToAll(approvalStatus) {
    if (!refundLineItems.value || refundLineItems.value.length === 0) {
      return;
    }

    // Get the selected reason code based on approval status
    const selectedReasonCode = approvalStatus === 'Approve'
      ? bulkApproveReasonCode.value
      : bulkDeclineReasonCode.value;

    if (!selectedReasonCode) {
      return;
    }

    // Count how many items will be affected
    let affectedCount = 0;

    // Apply the reason code to all items with matching approval status
    refundLineItems.value.forEach(item => {
      if (item.ApprovalStatus === approvalStatus) {
        item.ReasonCode = selectedReasonCode;
        affectedCount++;
      }
    });

    // Show success message
    if (affectedCount > 0) {
      const reasonDisplay = getReasonCodeOptions(approvalStatus).find(
        rc => rc.value === selectedReasonCode
      )?.display || selectedReasonCode;

      successMessage.value = `Applied "${reasonDisplay}" to ${affectedCount} ${approvalStatus.toLowerCase()}d item${affectedCount > 1 ? 's' : ''}`;
      showSuccessSnackbar.value = true;
    }
  }

  function moveToRefundable(orderItem) {
    console.log('Moving to refundable:', JSON.stringify(orderItem));
    // Find the corresponding LineRefundTotal from RefundTotals
    const partNo = orderItem.PartNo || orderItem.partNumber || '';
    if (partNo.startsWith('DPFU')) {
      alert('BISSELL Pet Foundation Donations cannot be refunded.');
      return;
    }
    let refundPrice = 0;
    if (refundTotals.value && refundTotals.value.lineItems) {
      const refundTotalItem = refundTotals.value.lineItems.find(
        totalLine => totalLine.PartNumber === orderItem.PartNo
      );
      if (refundTotalItem) {
        console.log('Found matching refund total:', JSON.stringify(refundTotalItem));
        refundPrice = parseFloat(refundTotalItem.LineRefundTotal) || 0;
      }
    }
    // If no refund total found, fall back to original price
    if (refundPrice === 0) {
      refundPrice = parseFloat(orderItem.Price || orderItem.UnitPrice || 0);
    }
    // Determine the correct max quantity from the order
    let maxQty = 1;
    if (refundData.value && refundData.value.LineItems) {
      const orderLine = refundData.value.LineItems.find(
        line => line.PartNo === orderItem.PartNo
      );
      if (orderLine && (orderLine.Qty || orderLine.Quantity)) {
        maxQty = parseInt(orderLine.Qty || orderLine.Quantity) || 1;
      }
    }
    // Create a refundable line item from the order item
    const refundableItem = {
      PartNo: orderItem.PartNo,
      PartDesc: orderItem.PartDesc || orderItem.Description,
      Qty: maxQty,
      MaxQty: maxQty,
      Price: refundPrice * maxQty,
      ApprovalStatus: null,
      lineNumber: orderItem.LineId,
      _originalLineRefundAmount: refundPrice * maxQty,
      UnitRefundTotal: refundPrice,
      _movedInitialRefundAmount: refundPrice
    };
    // Add to refundable items
    refundLineItems.value.push(refundableItem);
    // The item will automatically disappear from non-refundable list due to computed property
    console.log(`Moved item ${orderItem.PartNo} to refundable items with price $${refundPrice * maxQty}`);
  }

  function OpenIncidentbyRefNo(RefNum) {
    const body = {
      ReferenceNumber: RefNum,
      Action: "GetIncidentID",
      Session: refundsIntegration.value.Session
    };
    
    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify(body)
    });

    fetch(webhookCall)
      .then(response => response.json())
      .then(data => {
        console.log("OpenIncidentbyRefNo API response:", JSON.stringify(data));
        WorkspaceContext.value.editWorkspaceRecord('Incident', data);
      })
      .catch(error => {
        console.error("OpenIncidentbyRefNo API error:", JSON.stringify(error));
      });
  }

  function OpenOtherRefund(refundId) {
    console.log('OpenOtherRefund icon clicked for Refund ID:', refundId);
    isOtherRefundLoading.value = true;
    otherRefundSpinnerLabel.value = 'Retrieving Refund Incident...';
    const body = {
      RefundID: refundId,
      Action: "OpenOtherRefundIncident",
      Session: refundsIntegration.value.Session
    };

    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify(body)
    });

    fetch(webhookCall)
      .then(response => response.json())
      .then(data => {
        console.log("OpenOtherRefund API response:", JSON.stringify(data));
        WorkspaceContext.value.editWorkspaceRecord('Incident', data);
        isOtherRefundLoading.value = false;
        otherRefundSpinnerLabel.value = '';
      })
      .catch(error => {
        console.error("OpenOtherRefund API error:", JSON.stringify(error));
        isOtherRefundLoading.value = false;
        otherRefundSpinnerLabel.value = '';
      });
  }

  function formatDateET(dateString) {
    if (!dateString) return '';
    return new Date(dateString)
      .toLocaleString('sv-SE', { timeZone: 'America/New_York' })
      .slice(0, 19)
      .replace('T', '');
  }

  function formatCurrency(value) {
    if (value == null || value === '') return '';
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2
    }).format(value);
  }

  function parseCurrency(value) {
    if (typeof value !== 'string') return 0;
    return parseFloat(value.replace(/[^0-9.-]+/g, '')) || 0;
  }

  ORACLE_SERVICE_CLOUD.extension_loader.load("RefundsDashboard", "1.0").then(function(extensionLibrary) {
    extensionLibrary.registerWorkspaceExtension(function(workspaceContext) {
      WorkspaceContext.value = workspaceContext;
      extensionLibrary.getGlobalContext().then(function(globalContext) {
        globalContext.invokeAction("GETRefundsIntegrationSettings").then(function(integrationDetails) {
          refundsIntegration.value = integrationDetails.result[0];
          console.log("Refunds Integration Details:", JSON.stringify(refundsIntegration.value));
            LoadRefunds();
        });
        globalContext.invokeAction("GETOraceIntegrationSettings").then(function(integrationDetails) {
          oracleIntegration.value = integrationDetails.result[0];
        });
      });
    });
  });

  function submitRefundRequest() {
    // Calculate TotalShipAmount per requirements
    let totalShipAmount = 0;
    const shippingAmountDecimal = parseFloat(shippingRefundAmount.value) || 0;
    if (usedReturnLabelCC.value === true && includeShippingRefund.value === true) {
      totalShipAmount = shippingAmountDecimal - 4.95;
    } else if (usedReturnLabelCC.value === false && includeShippingRefund.value === true) {
      totalShipAmount = shippingAmountDecimal;
    } else if (usedReturnLabelCC.value === true && !includeShippingRefund.value) {
      totalShipAmount = -4.95;
    } else if (usedReturnLabelCC.value === false && !includeShippingRefund.value) {
      totalShipAmount = 0;
    }
    // Validation: All line items must have Approve or Decline selected
    const missingStatus = refundLineItems.value.some(line => !line.ApprovalStatus);
    if (missingStatus) {
      alert('Please select Approve or Decline for all refund line items.');
      return;
    }
    // Validation: All line items must have a Reason Code selected
    const missingReason = refundLineItems.value.some(line => !line.ReasonCode);
    if (missingReason) {
      alert('Please select a Reason Code for all refund line items.');
      return;
    }
    // Validation: If any ReasonCode is OA or OD, Comments are required
    const needsComments = refundLineItems.value.some(line => line.ReasonCode === 'OA' || line.ReasonCode === 'OD');
    if (needsComments && (!refundComments.value || !refundComments.value.trim())) {
      alert('Comments are required when Reason Code is "Other".');
      return;
    }
    // Validation: Refund Total must not exceed Order Total
    const orderTotal = parseFloat(headerRefundData.value?.OrderTotal) || 0;
    const orderStatus = (headerRefundData.value?.OrderStatus || '').toUpperCase();
    const isCanceled = orderStatus === 'CANCELED' || orderStatus === 'CANCELLED';
    if (!isCanceled && approvedRefundTotal.value > orderTotal) {
      alert(`Refund Total (${formatCurrency(approvedRefundTotal.value)}) cannot exceed the Order Total (${formatCurrency(orderTotal)}). Please adjust the refund amounts.`);
      return;
    }
    // Example: item is the refund being processed, refundLineItems is the array of order lines
    const item = headerRefundData.value;
    const refundTotal = approvedRefundTotal.value;
    const lineItemsSubtotal = approvedLineItemsTotal.value;
    const orderLines = refundLineItems.value.map(line => ({
      PartNo: line.PartNo,
      PartDesc: line.PartDesc || line.Description,
      SelectedQty: line.Qty,
      Approval: line.ApprovalStatus,
      ReasonCode: line.ReasonCode,
      LineRefundAmount: parseFloat(line.Price),
      LineID: line.lineNumber || null
    }));

    const refundId = refundInfo.value?.refundData?.refundId;
    const body = {
      RefundID: refundId,
      RefundAmount: parseFloat(refundTotal),
      LineItemTotal: parseFloat(lineItemsSubtotal),
      LineItems: orderLines,
      ShipAmount: includeShippingRefund.value === true ? shippingAmountDecimal : 0,
      TotalShipAmount: totalShipAmount,
      Action: "SubmitCCApproval",
      Session: refundsIntegration.value.Session
    };
    if (refundComments.value && refundComments.value.trim() !== '') {
      body.Comments = refundComments.value.trim();
    }
    
    // Show overlay spinner and label
    spinnerLabel.value = `Updating Refund ID ${refundId || ''}...`;
    isLoading.value = true;

    console.log("Submitting refund with body:", JSON.stringify(body));

    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify(body)
    });

    fetch(webhookCall)
      .then(response => response.json())
      .then(data => {
        console.log("Refund API response:", JSON.stringify(data));
        // Show snackbar confirmation
        successMessage.value = `Refund updated successfully!`;
        showSuccessSnackbar.value = true;
        isLoading.value = false;
        spinnerLabel.value = '';
        resetRefundState();
        // Hide overlay after 2 seconds
        setTimeout(() => {
          showSuccessOverlay.value = false;
        }, 2000);
      })
      .catch(error => {
        console.error("Refund API error:", JSON.stringify(error));
        // Hide spinner even on error
        isLoading.value = false;
        spinnerLabel.value = '';
      });
  }
</script>

<style scoped>
.highlighted-yes {
  background-color: #e8f5e8;
  border-radius: 8px;
  padding: 8px 12px;
  border: 2px solid #4caf50;
  margin-right: 12px;
}

.highlighted-no {
  background-color: #ffebee;
  border-radius: 8px;
  padding: 8px 12px;
  border: 2px solid #f44336;
  margin-left: 12px;
}

.highlighted-yes .v-label,
.highlighted-no .v-label {
  font-weight: 600;
}

.include-refund-checkbox-wrapper {
  border-radius: 8px;
  padding: 8px 0 8px 0;
  transition: background 0.2s, box-shadow 0.2s;
}
.include-refund-selected {
  background: #e3f2fd;
  box-shadow: 0 0 0 2px #1976d2;
}

.total-refund-row {
  background: #e8f5e9;
  border-radius: 6px;
  padding: 12px 16px;
  margin-top: 8px;
}

/* Urgent refund row styling */
.urgent-row {
  background: linear-gradient(90deg, #fff5f5 0%, #ffffff 100%) !important;
  border-left: 4px solid #f44336 !important;
  position: relative;
  transition: all 0.2s ease;
}
.urgent-row:hover {
  background: linear-gradient(90deg, #ffebee 0%, #ffffff 100%) !important;
  box-shadow: 0 2px 8px rgba(244, 67, 54, 0.15) !important;
}

/* Pulsing animation for urgent warning icon */
@keyframes pulseUrgent {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.15);
  }
}
.pulse-icon-urgent {
  animation: pulseUrgent 2s ease-in-out infinite;
}

/* Hover effects for refund line items */
.refund-item-row {
  transition: background-color 0.2s ease;
}
.refund-item-row:hover {
  background-color: #f5f5f5 !important;
}

/* Row approval status styling */
.row-approved {
  background-color: #e8f5e9 !important;
  transition: background-color 0.2s ease;
}
.row-approved:hover {
  background-color: #c8e6c9 !important;
}

.row-denied {
  background-color: #ffebee !important;
  transition: background-color 0.2s ease;
}
.row-denied:hover {
  background-color: #ffcdd2 !important;
}

/* Hover effects for other refunds table */
.other-refund-row {
  transition: background-color 0.2s ease;
}
.other-refund-row:hover {
  background-color: #f5f5f5 !important;
}

/* Hover effects for non-refundable items */
.non-refundable-row {
  transition: background-color 0.2s ease;
}
.non-refundable-row:hover {
  background-color: #fff9c4 !important;
}

/* Enhanced refund summary card with gradient */
.refund-summary-card {
  background: linear-gradient(135deg, #ffffff 0%, #e8f5e9 100%) !important;
  border: 2px solid #4caf50 !important;
}
</style>