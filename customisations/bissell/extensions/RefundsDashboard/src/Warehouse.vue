<template>
  <v-overlay :model-value="isLoading" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important;">
    <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
      <v-progress-circular indeterminate size="64" color="primary" />
      <div class="text-h6 mt-4" style="color: #000;">{{ loadingMessage }}</div>
    </div>
  </v-overlay>
  

  <v-row v-if="!showData && !showNoOrderForm && !isLoading" class="mb-4">
    <v-col cols="12">
      <v-card variant="outlined">
        <v-card-title class="BissellBanner">
          Order Search
        </v-card-title>
        <v-card-text class="pa-4">
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            @click:append="LoadOrderDetails()"
            @keyup.enter="LoadOrderDetails()"
            label="Order Number"
            placeholder="Enter Order Number"
            variant="outlined"
            density="compact"
            style="width: 250px; min-width: 250px;"
            clearable
          />
          <div>
            <a href="#" @click.prevent="openNoOrderForm" style="color: #1976d2; text-decoration: underline; font-weight: 500; cursor: pointer;">I don't have an Order Number</a>
          </div>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>

  <!-- Empty State -->
  <v-row v-if="!showData && !showNoOrderForm && !isLoading" class="mt-8">
    <v-col cols="12" class="text-center">
      <v-icon size="64" color="grey-lighten-1">mdi-package-variant-closed</v-icon>
      <div class="text-h6 text-grey mt-2">Search for an order to get started</div>
      <div class="text-body-2 text-grey">Enter an order number above or click "I don't have an Order Number"</div>
    </v-col>
  </v-row>

  <!-- No Order Number Form -->
  <v-row v-if="showNoOrderForm" class="mb-4">
    <v-col cols="12">
      <v-card variant="outlined">
        <v-card-title class="BissellBanner">
          <v-icon class="mr-2">mdi-file-document-outline</v-icon>
          Request Refund Without Order Number
        </v-card-title>
        <v-card-text class="pa-4">
          <div class="mb-3" style="color: #d32f2f; font-size: 0.95em;">
            * indicates a mandatory field
          </div>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderName"
                label="Name *"
                placeholder="Enter your name"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderName"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderPhone"
                label="Phone Number"
                placeholder="Enter your phone number"
                variant="outlined"
                density="compact"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="noOrderAddress"
                label="Address *"
                placeholder="Enter your address"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderAddress"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderCity"
                label="City *"
                placeholder="Enter your city"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderCity"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderZip"
                label="Zip/Postal *"
                placeholder="Enter your zip or postal code"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderZip"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-select
                v-model="noOrderCountry"
                :items="[{ value: 'US', title: 'United States' }, { value: 'CA', title: 'Canada' }]"
                label="Country *"
                variant="outlined"
                density="compact"
                item-title="title"
                item-value="value"
                :error="missingNoOrderFields.noOrderCountry"
              />
            </v-col>
            <v-col cols="6">
              <v-select
                v-model="noOrderState"
                :items="stateOptions"
                label="State/Province *"
                variant="outlined"
                density="compact"
                :disabled="stateOptions.length === 0"
                :error="missingNoOrderFields.noOrderState"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderItemNumbers"
                label="Item Number(s) *"
                placeholder="Enter item number(s)"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderItemNumbers"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="noOrderTracking"
                label="Tracking Number *"
                placeholder="Enter tracking number"
                variant="outlined"
                density="compact"
                :error="missingNoOrderFields.noOrderTracking"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-textarea
                v-model="noOrderComments"
                label="Comments"
                placeholder="Enter any additional comments"
                variant="outlined"
                density="compact"
                rows="3"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <div class="text-center" style="width: 100%;">
            <v-btn 
              color="success" 
              size="large"
              elevation="2"
              @click="submitNoOrderForm"
              prepend-icon="mdi-check-circle"
            >
              Submit Ad Hoc Request
            </v-btn>
            <v-btn 
              color="grey" 
              size="large"
              variant="outlined"
              @click="closeNoOrderForm"
              prepend-icon="mdi-close"
              class="ml-4"
            >
              Close
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-col>
  </v-row>
  
  <div v-if="showData">
    <v-row class="mb-4">
      <v-col cols="6">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-package-variant</v-icon>
            Order Details
          </div>
          <div class="text-body-2 mb-1"><strong>Order Number:</strong> {{ refundData?.OrderNumber || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Order Date:</strong> {{ formattedOrderDate || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Consumer ID:</strong> {{ refundData?.ConsumerID || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Oracle Account:</strong> {{ refundData?.OracleAccountNo || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Order Status:</strong> {{ refundData?.OrderStatus || '' }}</div>
          <div class="text-body-2 mb-1"><strong>Payment Type:</strong> {{ refundData?.PaymentType || '' }}</div>
          <div class="text-body-2"><strong>PO Number:</strong> {{ refundData?.PONumber || '' }}</div>
        </v-card>
      </v-col>
      <v-col cols="6">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-map-marker</v-icon>
            Addresses
          </div>
          <div class="mb-3">
            <div class="text-subtitle-1 font-weight-bold mb-2 text-primary d-flex align-center">
              <v-icon size="20" class="mr-1">mdi-truck-delivery</v-icon>
              Shipping Address
            </div>
            <div class="text-body-2 mb-1"><strong>{{ refundData.ShippingAddress.Name }}</strong></div>
            <div class="text-body-2">{{ refundData.ShippingAddress.Street1 }}</div>
            <div class="text-body-2">{{ refundData.ShippingAddress.City }}, {{ refundData.ShippingAddress.State }} {{ refundData.ShippingAddress.Postal }}</div>
          </div>
          <v-divider class="my-2"></v-divider>
          <div>
            <div class="text-subtitle-1 font-weight-bold mb-2 text-primary d-flex align-center">
              <v-icon size="20" class="mr-1">mdi-credit-card</v-icon>
              Billing Address
            </div>
            <div class="text-body-2 mb-1"><strong>{{ refundData.BillingAddress.Name }}</strong></div>
            <div class="text-body-2">{{ refundData.BillingAddress.Street1 }}</div>
            <div class="text-body-2">{{ refundData.BillingAddress.City }}, {{ refundData.BillingAddress.State }} {{ refundData.BillingAddress.Postal }}</div>
          </div>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mb-4">
      <v-col cols="12">
        <v-card variant="outlined">
          <div class="d-flex align-center justify-space-between pa-4 pb-0">
            <div class="text-h5">
              <v-icon class="mr-2">mdi-format-list-checkbox</v-icon>
              Select Items for Refund
            </div>
            <v-btn
              color="success"
              variant="outlined"
              prepend-icon="mdi-check-all"
              size="small"
              @click="selectAllItems"
            >
              Select All Items
            </v-btn>
          </div>
          <v-data-table
            :headers="RefundLineHeaders"
            :items="refundData.LineItems"
            class="elevation-0"
          >

          <template v-slot:headers="{columns}">
            <tr style="background-color: #f5f5f5;">
              <template v-for="column in columns" :key="column.key">
                <td class="text-subtitle-2 font-weight-bold pa-4">
                  {{column.title}}
                </td>
              </template>
            </tr>
          </template>

          <template v-slot:item="{ item }">
            <tr :class="{ 'selected-row': item.Selected, 'hover-row': true }">
              <td>
                <div class="d-flex align-center">
                  <v-checkbox
                    v-model="item.Selected"
                    @update:model-value="val => handleSelectLineItem(val, item)"
                    hide-details
                    density="compact"
                    color="success"
                  ></v-checkbox>
                </div>
              </td>
              <td>{{ item.PartNo }}</td>
              <td>{{ item.PartDesc }}</td>
              <td>{{ item.Qty }}</td>
              <td>
                <v-select
                  v-model="item.SelectedQty"
                  :items="getQuantityOptions(item.Qty)"
                  variant="outlined"
                  density="compact"
                  hide-details
                  style="max-width: 80px"
                ></v-select>
              </td>
            </tr>
          </template>

          <template v-slot:no-data>
            No line items found.
          </template>
        </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mb-4">
      <v-col cols="4">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-truck-delivery</v-icon>
            Return Label
          </div>
          <div class="text-body-2 mb-2">Did Consumer use our Return Label?</div>
          <v-radio-group v-model="usedReturnLabel" inline hide-details>
            <v-radio
              label="Yes"
              :value="true"
              :class="{ 'highlighted-yes': usedReturnLabel === true }"
              color="success"
            ></v-radio>
            <v-radio
              label="No"
              :value="false"
              :class="{ 'highlighted-no': usedReturnLabel === false }"
              color="error"
            ></v-radio>
          </v-radio-group>
          <div v-if="usedReturnLabel === true" class="mt-3 text-body-2" style="color: #d32f2f;">
            $4.95 will be deducted from the refund amount.
          </div>
        </v-card>
      </v-col>
      <v-col cols="8">
        <v-card variant="outlined" class="pa-4">
          <div class="text-h6 mb-3 text-primary">
            <v-icon class="mr-2">mdi-comment-text</v-icon>
            Additional Comments
          </div>
          <v-textarea
            label="Comments (optional)"
            v-model="comments"
            rows="3"
            variant="outlined"
            hide-details
          ></v-textarea>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mb-4">
      <v-col cols="12" class="text-center">
        <v-btn
          color="success"
          size="large"
          elevation="2"
          @click="SubmitWHRefund()"
          prepend-icon="mdi-check-circle"
          :disabled="!hasSelectedItems"
        >
          Submit Refund Request
        </v-btn>
        <v-btn
          color="grey"
          size="large"
          variant="outlined"
          @click="closeOrderDetails"
          prepend-icon="mdi-close"
          class="ml-4"
        >
          Close Order Details
        </v-btn>
      </v-col>
    </v-row>
  </div>

  <!-- Success Snackbar -->
  <v-snackbar
    v-model="displaySuccess"
    color="success"
    timeout="10000"
    location="top right"
  >
    <template v-slot:actions>
      <v-btn variant="text" @click="displaySuccess = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
    {{ displaySuccessMessage }}
  </v-snackbar>

  <!-- Error Snackbar -->
  <v-snackbar
    v-model="displayError"
    color="error"
    timeout="7000"
    location="top right"
  >
    <template v-slot:actions>
      <v-btn variant="text" @click="displayError = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
    {{ displayErrorMessage }}
  </v-snackbar>
</template>

<script setup>
  import { computed, ref, nextTick, watch } from 'vue';

    const showNoOrderForm = ref(false);
    const noOrderName = ref("");
    const noOrderAddress = ref("");
    const noOrderCity = ref("");
    const noOrderZip = ref("");
    const noOrderPhone = ref("");
    const noOrderItemNumbers = ref("");
    const noOrderTracking = ref("");
    const noOrderComments = ref("");
    const noOrderCountry = ref('US');
    const noOrderState = ref('');
    const regionsList = ref({});
    const stateOptions = ref([]);
    const missingNoOrderFields = ref({
      noOrderName: false,
      noOrderAddress: false,
      noOrderCity: false,
      noOrderState: false,
      noOrderZip: false,
      noOrderItemNumbers: false,
      noOrderTracking: false,
      noOrderCountry: false
    });

    function openNoOrderForm() {
      showNoOrderForm.value = true;
      console.log("No Order Number link clicked");
      // API call to get regions list
      if (!refundsIntegration.value || !refundsIntegration.value.PHPUrl || !refundsIntegration.value.Session) {
        console.error('Refunds integration details are missing.');
        return;
      }
      const body = {
        Action: 'GetRegionsList',
        Session: refundsIntegration.value.Session
      };
      fetch(refundsIntegration.value.PHPUrl, {
        method: 'POST',
        body: JSON.stringify(body)
      })
        .then(response => response.json())
        .then(data => {
          console.log('Regions list API response:', data);
          // Correctly extract countryid object
          if (data && data.countryid) {
            regionsList.value = data.countryid;
            updateStateOptions();
          } else {
            regionsList.value = {};
            stateOptions.value = [];
          }
        })
        .catch(error => {
          console.error('Regions list API error:', error);
        });
    }

    function updateStateOptions() {
      console.log('Country changed to:', noOrderCountry.value);
      if (noOrderCountry.value === 'US' && regionsList.value['1']) {
        stateOptions.value = Object.values(regionsList.value['1']);
        console.log('Populating State/Province dropdown with US states:', stateOptions.value);
      } else if (noOrderCountry.value === 'CA' && regionsList.value['2']) {
        stateOptions.value = Object.values(regionsList.value['2']);
        console.log('Populating State/Province dropdown with Canadian provinces:', stateOptions.value);
      } else {
        stateOptions.value = [];
        console.log('No states/provinces available for selected country.');
      }
      noOrderState.value = '';
    }

    watch(noOrderCountry, updateStateOptions);

    function resetNoOrderForm() {
      noOrderName.value = "";
      noOrderAddress.value = "";
      noOrderCity.value = "";
      noOrderZip.value = "";
      noOrderPhone.value = "";
      noOrderItemNumbers.value = "";
      noOrderTracking.value = "";
      noOrderComments.value = "";
      noOrderCountry.value = 'US';
      noOrderState.value = '';
    }

    function closeNoOrderForm() {
      showNoOrderForm.value = false;
      resetNoOrderForm();
    }

    function submitNoOrderForm() {
      // Validate all mandatory fields at once
      const missingFields = {
        noOrderName: !noOrderName.value.trim(),
        noOrderAddress: !noOrderAddress.value.trim(),
        noOrderCity: !noOrderCity.value.trim(),
        noOrderState: !noOrderState.value,
        noOrderZip: !noOrderZip.value.trim(),
        noOrderItemNumbers: !noOrderItemNumbers.value.trim(),
        noOrderTracking: !noOrderTracking.value.trim(),
        noOrderCountry: !noOrderCountry.value
      };
      const anyMissing = Object.values(missingFields).some(Boolean);
      missingNoOrderFields.value = missingFields;
      if (anyMissing) {
        alert('Please complete all mandatory fields.');
        return;
      }

      // API call to create ad hoc refund request
      if (!refundsIntegration.value || !refundsIntegration.value.PHPUrl || !oracleIntegration.value || !oracleIntegration.value.Session) {
        console.error('Integration details are missing.');
        return;
      }

      isLoading.value = true;
      loadingMessage.value = 'Submitting AdHoc Request...';

      const body = {
        Action: 'CreateAdHoc',
        Session: oracleIntegration.value.Session,
        Address: noOrderAddress.value,
        City: noOrderCity.value,
        Region: noOrderState.value,
        PostalCode: noOrderZip.value,
        Country: noOrderCountry.value,
        PhoneNumber: noOrderPhone.value,
        ItemNumber: noOrderItemNumbers.value,
        TrackingNumber: noOrderTracking.value,
        Name: noOrderName.value,
        Comments: noOrderComments.value
      };

      fetch(refundsIntegration.value.PHPUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
      })
        .then(response => response.json())
        .then(data => {
          isLoading.value = false;
          console.log('AdHoc refund API response:', data);
          ShowSuccess('Successfully Created Ad Hoc Request ID: ' + data.requestId);
            resetNoOrderForm();
            showNoOrderForm.value = false;
        })
        .catch(error => {
          isLoading.value = false;
          console.error('AdHoc refund API error:', error);
          ShowError(error);
        });
    }
  // Remove error formatting as soon as data is entered in each field
    watch(noOrderName, val => { if (val && missingNoOrderFields.value.noOrderName) missingNoOrderFields.value.noOrderName = false; });
    watch(noOrderAddress, val => { if (val && missingNoOrderFields.value.noOrderAddress) missingNoOrderFields.value.noOrderAddress = false; });
    watch(noOrderCity, val => { if (val && missingNoOrderFields.value.noOrderCity) missingNoOrderFields.value.noOrderCity = false; });
    watch(noOrderZip, val => { if (val && missingNoOrderFields.value.noOrderZip) missingNoOrderFields.value.noOrderZip = false; });
    watch(noOrderItemNumbers, val => { if (val && missingNoOrderFields.value.noOrderItemNumbers) missingNoOrderFields.value.noOrderItemNumbers = false; });
    watch(noOrderTracking, val => { if (val && missingNoOrderFields.value.noOrderTracking) missingNoOrderFields.value.noOrderTracking = false; });
    watch(noOrderCountry, val => { if (val && missingNoOrderFields.value.noOrderCountry) missingNoOrderFields.value.noOrderCountry = false; });
    watch(noOrderState, val => { if (val && missingNoOrderFields.value.noOrderState) missingNoOrderFields.value.noOrderState = false; });

  const RefundLineHeaders = [
    { key: "Selected", title: "" }, // Checkbox column
    { key: "PartNo", title: "Part Number" },
    { key: "PartDesc", title: "Description" },
    { key: "Qty", title: "Order Qty" },
    { key: "SelectedQty", title: "Refund Qty" }
  ];

  const search = ref("");
  const comments = ref("");
  const usedReturnLabel = ref(null);
  const refundData = ref(null);
  const showData = ref(false);
  const isLoading = ref(false);
  const loadingMessage = ref("Loading Order Details...");
  const displayError = ref(false);
  const displayErrorMessage = ref(null);
  const displaySuccess = ref(false);
  const displaySuccessMessage = ref(null);

  const formattedOrderDate = computed(() => {
    if (!refundData.value?.OrderDate) return null;
    // Remove the 'T00:00:00' portion from the date
    return refundData.value.OrderDate.split('T')[0];
  });

  const selectedItemsCount = computed(() => {
    if (!refundData.value?.LineItems) return 0;
    return refundData.value.LineItems.filter(item => item.Selected && item.SelectedQty > 0).length;
  });

  const hasSelectedItems = computed(() => {
    return selectedItemsCount.value > 0;
  });

  function handleSelectLineItem(val, item) {
    if (val && item.PartNo && item.PartNo.startsWith('DPFU')) {
      window.alert('BISSELL Pet Foundation Donations cannot be refunded.');
      nextTick(() => {
        item.Selected = false;
      });
      return;
    }
    item.Selected = val;
  }

  function selectAllItems() {
    if (refundData.value && refundData.value.LineItems) {
      refundData.value.LineItems.forEach(item => {
        if (!item.PartNo || !item.PartNo.startsWith('DPFU')) {
          item.Selected = true;
        }
      });
    }
  }

  function LoadOrderDetails() {
    console.log("Loading order details for: " + search.value);

    if (!search.value) {
      alert("Please enter an order number.");
      return;
    }
    // Validate that search.value is an integer
    if (!/^\d+$/.test(search.value)) {
      alert("Invalid Order Number Detected");
      return;
    }

    isLoading.value = true;
    loadingMessage.value = `Loading Details for Order Number ${search.value}...`;
    displayError.value = false;
    displayErrorMessage.value = null;
    displaySuccess.value = false;
    displaySuccessMessage.value = null;
    
    const webhookCall = new Request(oracleIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify({
        Action: "getorderdetails",
        OrderNumber: search.value,
        Session: oracleIntegration.value.Session
      })
    });

    fetch(webhookCall).then((webhookResponse) => {
        console.log(webhookResponse);
        
        isLoading.value = false;

        if(!webhookResponse.ok) {
          ShowError(webhookResponse.status + " - " + webhookResponse.statusText + ": " + webhookResponse.url);
          console.log(webhookResponse.status + " - " + webhookResponse.statusText + ": " + webhookResponse.url);
          return;
        }

        webhookResponse.json().then((webhookJson) => {
          console.log(webhookJson);

          if(!webhookJson || webhookJson == null) {
            ShowError("Error loading order details.");
            return;
          }

          if(webhookJson.ResponseCode == "Success") {
            showData.value = true;
            refundData.value = webhookJson;
          } else {
            ShowError("Error: " + webhookJson.ResponseMessage);
            console.log("Error from webhook: " + webhookJson.ResponseMessage);
            return;
          }
        });
      }).catch(error => {
        console.log(error);
        isLoading.value = false;
        ShowError(error);
      });
  }

  function SubmitWHRefund() {
    console.log("Entered submit WH refund");
    console.log(refundData.value);
    console.log('OrderNumber: ' + refundData.value.OrderNumber);
    console.log('ConsumerID: ' + refundData.value.ConsumerID);
    console.log('Line Items: ' + JSON.stringify(refundData.value.LineItems));
    console.log('URL: ' + refundsIntegration.value.PHPUrl);
    console.log('Body: ' + JSON.stringify({Action: "SubmitWHRefund",
        OrderNumber: refundData.value.OrderNumber,
        Session: oracleIntegration.value.Session,
        UsedOurReturnLabel: usedReturnLabel.value,
        CRMConsumerID: refundData.value.ConsumerID,
        OracleAccountNum: refundData.value.OracleAccountNo,
        LineItems: refundData.value.LineItems,
        Comments: comments.value,
        OrderDate: refundData.value.OrderDate,
        PaymentType: refundData.value.PaymentType,
        PONumber: refundData.value.PONumber}));
    console.log('Comments: ' + comments.value);
    console.log('OrderDate: ' + refundData.value.OrderDate);
    console.log('Payment Type: ' + refundData.value.PaymentType);

    let lineSelected = false;

    refundData.value.LineItems.forEach((item, index) => {
      console.log('Item ' + index + ': ' + JSON.stringify(item));
      console.log('Selected: ' + item.Selected);
      console.log('Selected Qty: ' + item.SelectedQty);
      if(item.Selected && item.SelectedQty > 0) {
        lineSelected = true;
      }
    });

    if(!lineSelected) {
      alert("Please select at least one line item with a quantity greater than 0 to refund.");
      return;
    }

    if(usedReturnLabel.value === null) {
      alert("Please specify if the consumer used our return label.");
      return;
    }

    isLoading.value = true;
    loadingMessage.value = "Submitting Refund...";
    displayError.value = false;
    displayErrorMessage.value = null;
    displaySuccess.value = false;
    displaySuccessMessage.value = null;
    const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
      method: "POST",
      body: JSON.stringify({
        Action: "SubmitWHRefund",
        OrderNumber: refundData.value.OrderNumber,
        Session: oracleIntegration.value.Session,
        UsedOurReturnLabel: usedReturnLabel.value,
        CRMConsumerID: refundData.value.ConsumerID,
        OracleAccountNum: refundData.value.OracleAccountNo,
        LineItems: refundData.value.LineItems,
        Comments: comments.value,
        OrderDate: refundData.value.OrderDate,
        PaymentType: refundData.value.PaymentType,
        PONumber: refundData.value.PONumber
      })
    });

    fetch(webhookCall).then((webhookResponse) => {
      console.log(webhookResponse);

      isLoading.value = false;

      webhookResponse.json().then((webhookJson) => {
        console.log(webhookJson);
        console.log("refundID: " + webhookJson.refundId);
        console.log("Incident: " + webhookJson.referenceNumber);
        ShowSuccess("Successfully submitted refund. Refund ID: " + webhookJson.refundId + ", Incident Number: " + webhookJson.referenceNumber);
        resetOrderState();
      });
    }).catch(error => {
      console.log(error);
      isLoading.value = false;
      ShowError(error);
    });
  }

  /***** INTEGRATION DETAILS *****/
  const oracleIntegration = ref(null);
  const refundsIntegration = ref(null);
  const WorkspaceContext = ref(null);

  /***** LOGIC *****/
  function getQuantityOptions(max) {
    return Array.from({ length: max }, (_, i) => i + 1);
  }

  function resetOrderState() {
    showData.value = false;
    refundData.value = null;
    search.value = "";
    comments.value = "";
    usedReturnLabel.value = null;
  }

  function closeOrderDetails() {
    resetOrderState();
    displayError.value = false;
    displayErrorMessage.value = null;
    displaySuccess.value = false;
    displaySuccessMessage.value = null;
  }

  function ShowError(error) {
    displayError.value = true;
    displayErrorMessage.value = error;
    displaySuccess.value = false;
    displaySuccessMessage.value = null;
  }

  function ShowSuccess(message) {
    displayError.value = false;
    displayErrorMessage.value = null;
    displaySuccess.value = true;
    displaySuccessMessage.value = message;
    
    // Hide success message after 10 seconds
    setTimeout(() => {
      displaySuccess.value = false;
      displaySuccessMessage.value = null;
    }, 10000);
  }

  ORACLE_SERVICE_CLOUD.extension_loader.load("RefundsDashboard", "1.0").then(function(extensionLibrary) {
    extensionLibrary.registerWorkspaceExtension(function(workspaceContext) {
      WorkspaceContext.value = workspaceContext;
      extensionLibrary.getGlobalContext().then(function(globalContext) {
        globalContext.invokeAction("GETOraceIntegrationSettings").then(function(integrationDetails) {
          oracleIntegration.value = integrationDetails.result[0];
        });
        globalContext.invokeAction("GETRefundsIntegrationSettings").then(function(integrationDetails) {
          refundsIntegration.value = integrationDetails.result[0];
        });
      });
    });
  });
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

.v-label:has(+ input[aria-required="true"]),
.v-label:has(+ .v-input__control input[aria-required="true"]) {
  color: #d32f2f;
}

.selected-row {
  background-color: #e8f5e9 !important;
  transition: background-color 0.2s ease;
}

.hover-row {
  transition: background-color 0.15s ease;
}

.hover-row:hover {
  background-color: #f5f5f5 !important;
}

.selected-row:hover {
  background-color: #dcedc8 !important;
}
</style>