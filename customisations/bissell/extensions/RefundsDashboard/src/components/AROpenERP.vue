<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue';
// Helper to handle currency display/edit for lineRefundAmount
function getLineRefundDisplay(line) {
  return line._editing ? line.lineRefundAmount : formatCurrency(line.lineRefundAmount);
}
function handleLineRefundFocus(line) {
  line._editing = true;
}
function handleLineRefundBlur(line, idx) {
  line._editing = false;
  let val = typeof line.lineRefundAmount === 'string' ? parseFloat(line.lineRefundAmount.replace(/[^0-9.\-]+/g, '')) : line.lineRefundAmount;
  if (isNaN(val)) val = 0;
  line.lineRefundAmount = val.toFixed(2);
  if (line._freight) {
    selectedRefund.value._freightEdit = val;
  } else {
    selectedRefund.value.lineItems[idx].lineRefundAmount = val.toFixed(2);
  }
  recalcRefundTotals();
}

function recalcRefundTotals() {
  if (selectedRefund.value) {
    const subtotal = (selectedRefund.value.lineItems || []).reduce((sum, li) => sum + (parseFloat(li.lineRefundAmount) || 0), 0);
    const freight = selectedRefund.value._freightEdit !== undefined ? selectedRefund.value._freightEdit : (parseFloat(selectedRefund.value.shippingRefundDeduction) || 0);
    selectedRefund.value.refundTotal = (subtotal + freight).toFixed(2);
  }
}

// Track missing comments for declined rows
const missingDeclineComments = reactive({});

// Progress bar computed value with debug
const progressBarValue = computed(() => {
  let total = processingRefundTotal.value || 1;
  let val = (processingRefundIndex.value / total) * 100;
  if (val < 0) val = 0;
  if (val > 100) val = 100;
  // Debug log
  if (isProcessingRefunds.value) {
    console.log('ProgressBar:', { idx: processingRefundIndex.value, total, val });
  }
  return val;
});

// Progress bar state for submit
const isProcessingRefunds = ref(false);
const processingRefundIndex = ref(0);
const processingRefundTotal = ref(0);
const processingRefundLabel = computed(() => {
  if (!isProcessingRefunds.value) return '';
  return `Processing Refund ${processingRefundIndex.value} of ${processingRefundTotal.value}...`;
});

// Processing time state
const processingTime = ref(0);
let processingTimer = null;

watch(isProcessingRefunds, (val) => {
  if (val) {
    processingTime.value = 0;
    if (processingTimer) clearInterval(processingTimer);
    processingTimer = setInterval(() => {
      processingTime.value++;
    }, 1000);
  } else {
    if (processingTimer) clearInterval(processingTimer);
    processingTimer = null;
  }
});

const showResultsModal = ref(false);
const refundResults = ref([]);

async function submitRefunds() {
  // Check if at least one row is approved or declined
  const hasSelection = OpenRefunds.value.some(refund => {
    const sel = rowSelections[refund.refundId];
    return sel === 'APPROVE' || sel === 'DENY' || sel === 'MANUAL';
  });
  if (!hasSelection) {
    window.alert('Please select Approve or Decline for at least one refund before submitting.');
    return;
  }

  // Check for declined rows with missing comments
  let missing = false;
  OpenRefunds.value.forEach(refund => {
    const sel = rowSelections[refund.refundId];
    if (sel === 'DENY') {
      if (!refund.comments || refund.comments.trim() === '') {
        missingDeclineComments[refund.refundId] = true;
        missing = true;
      } else {
        missingDeclineComments[refund.refundId] = false;
      }
    } else {
      missingDeclineComments[refund.refundId] = false;
    }
  });
  if (missing) {
    window.alert('Please enter comments for all declined refunds.');
    return;
  }
  const payload = {
    Refunds: OpenRefunds.value
      .filter(refund => {
        const sel = rowSelections[refund.refundId];
        return sel === 'APPROVE' || sel === 'DENY' || sel === 'MANUAL';
      })
      .map(refund => {
        const orig = originalRefunds.value.find(r => r.refundId === refund.refundId);
        const obj = {
          Action: 'SubmitARApproval',
          Session: refundsIntegration.value?.Session,
          RefundID: refund.refundId,
          ApprovalStatus: rowSelections[refund.refundId],
          RefundAmount: Number(parseFloat(refund.refundTotal).toFixed(2)),
          OriginalRefundAmount: Number(parseFloat(orig.refundTotal).toFixed(2)),
          ShipAmount: Number(parseFloat(refund.shippingRefundDeduction).toFixed(2)),
          OrderNumber: refund.orderNumber,
          LineItems: (refund.lineItems || []).map(line => ({
            PartNo: line.partNumber,
            LineRefundAmount: Number(parseFloat(line.lineRefundAmount).toFixed(2)),
            LineNumber: line.lineNumber
          }))
        };
        if (refund.comments && refund.comments.trim() !== '') {
          obj.Comments = refund.comments;
        }
        return obj;
      })
  };
  isProcessingRefunds.value = true;
  processingRefundTotal.value = payload.Refunds.length;
  refundResults.value = [];
  for (let i = 0; i < payload.Refunds.length; i++) {
    const refund = payload.Refunds[i];
    console.log('Processing Refund:', refund);
    processingRefundIndex.value = i + 1;
    await nextTick(); // Ensure UI updates before API call
    try {
      const response = await fetch(refundsIntegration.value.PHPUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(refund)
      });
      const result = await response.json();
      console.log('Refund API Response:', result);
      // Handle timeout response
      if (result && result.message === 'Endpoint request timed out') {
        refundResults.value.push({
          refundId: refund.RefundID,
          orderNumber: refund.OrderNumber || '',
          refundStatus: 'Error',
          submitStatusMessage: 'Request Timed Out - Manual Review Necessary.'
        });
      } else if (result && result.refundsStatus) {
        refundResults.value.push(result.refundsStatus);
      } else {
        // fallback if structure is not as expected
        refundResults.value.push({
          refundId: refund.RefundID,
          orderNumber: refund.OrderNumber || '',
          refundStatus: 'Unknown',
          submitStatusMessage: 'No response/refundsStatus returned.'
        });
      }
    } catch (error) {
      console.error('Refund API Error:', error);
      refundResults.value.push({
        refundId: refund.RefundID,
        orderNumber: refund.OrderNumber || '',
        refundStatus: 'Error',
        submitStatusMessage: error?.message || 'API Error'
      });
    }
  }
  // Ensure bar fills to 100% before hiding overlay
  processingRefundIndex.value = payload.Refunds.length;
  await nextTick();
  isProcessingRefunds.value = false;
  processingRefundIndex.value = 0;
  processingRefundTotal.value = 0;
  showResultsModal.value = true;
  console.log('Submit Refunds Payload:', payload);
}

function closeResultsModal() {
  showResultsModal.value = false;
  LoadRefunds();
}

// Summary labels for approved/declined/total
const approvedRefundsCount = computed(() => {
  // Count all refunds where the approve action icon is selected
  return OpenRefunds.value.filter(refund => rowSelections[refund.refundId] === 'APPROVE').length;
});

const declinedRefundsCount = computed(() => {
  // Count all refunds where the declined action icon is selected
  return OpenRefunds.value.filter(refund => rowSelections[refund.refundId] === 'DENY').length;
});

const manualRefundsCount = computed(() => {
  // Count all refunds where the manual action icon is selected
  return OpenRefunds.value.filter(refund => rowSelections[refund.refundId] === 'MANUAL').length;
});

const approvedRefundsTotal = computed(() => {
  // Sum all Refund Totals for each row where the approved action icon is selected
  return OpenRefunds.value.reduce((sum, refund) => {
    if (rowSelections[refund.refundId] === 'APPROVE' || rowSelections[refund.refundId] === 'MANUAL') {
      sum += parseFloat(refund.refundTotal) || 0;
    }
    return sum;
  }, 0);
});

// Row selection state and getRowClass function
const rowSelections = reactive({});
function selectRow(refundId, action) {
  if (action === 'approve') {
    rowSelections[refundId] = 'APPROVE';
    // Remove row-missing-comment if present
    missingDeclineComments[refundId] = false;
  } else if (action === 'cancel') {
    rowSelections[refundId] = 'DENY';
  } else if (action === 'manual') {
    rowSelections[refundId] = 'MANUAL';
  } else {
    rowSelections[refundId] = action;
  }
}
const getRowClass = (item) => {
  const sel = rowSelections[item.refundId];
  if (missingDeclineComments[item.refundId]) return 'row-missing-comment';
  if (sel === 'APPROVE') return 'row-approved';
  if (sel === 'DENY') return 'row-denied';
  if (sel === 'MANUAL') return 'row-manual';
  return '';
};

const lineItemsWithFreight = computed(() => {
  if (!selectedRefund.value) return [];
  const items = (selectedRefund.value.lineItems || []).map((li, idx) => ({ ...li, _idx: idx }));
  let freight = selectedRefund.value._freightEdit !== undefined
    ? selectedRefund.value._freightEdit
    : parseFloat(selectedRefund.value.shippingRefundDeduction) || 0;
  if (freight !== 0 || selectedRefund.value._freightEdit !== undefined) {
    items.push({
      partDescription: 'FREIGHT',
      partNumber: '',
      lineRefundAmount: freight,
      _freight: true
    });
  }
  return items;
});

const lineItemsSubTotal = computed(() => {
  if (!selectedRefund.value) return 0;
  return (selectedRefund.value.lineItems || []).reduce((sum, li) => sum + (parseFloat(li.lineRefundAmount) || 0), 0);
});

const refundModalTotal = computed(() => {
  if (!selectedRefund.value) return 0;
  const subtotal = lineItemsSubTotal.value;
  const freight = selectedRefund.value._freightEdit !== undefined ? selectedRefund.value._freightEdit : (parseFloat(selectedRefund.value.shippingRefundDeduction) || 0);
  return subtotal + freight;
});

const showOtherRefundsModal = ref(false);
const selectedOtherRefunds = ref([]);
const selectedOtherRefundsOrderNumber = ref('');

function openOtherRefundsModal(item) {
  selectedOtherRefunds.value = item.otherRefunds || [];
  selectedOtherRefundsOrderNumber.value = item.orderNumber;
  showOtherRefundsModal.value = true;
}

const showOpenRefundModal = ref(false);
const selectedRefund = ref(null);
const selectedRefundOriginal = ref(null);
const selectedRefundIndex = ref(null);

function openRefundModal(item) {
  selectedRefund.value = JSON.parse(JSON.stringify(item));
  selectedRefundOriginal.value = JSON.parse(JSON.stringify(item));
  selectedRefundIndex.value = OpenRefunds.value.findIndex(r => r.refundId === item.refundId);
  refundComments.value = item.comments || '';
  showCommentsView.value = false;
  refundCommentsList.value = [];
  showOpenRefundModal.value = true;
}

function closeRefundModal() {
  if (selectedRefundOriginal.value) {
    selectedRefund.value = JSON.parse(JSON.stringify(selectedRefundOriginal.value));
  }
  showCommentsView.value = false;
  refundCommentsList.value = [];
  showOpenRefundModal.value = false;
}

const showSuccessSnackbar = ref(false);
const successMessage = ref('');
const OpenRefundsHeaders = [
  { key: "actionIcons", title: "Actions" },
  { key: "refundId", title: "Refund ID" },
  { key: "cRMReferenceNumber", title: "Incident" },
  { key: "orderNumber", title: "Order Number" },
  { key: "oracleAccountNo", title: "Oracle Account Num" },
  { key: "pONumber", title: "PO Number" },
  { key: "arReviewedDate", title: "AR Date Reviewed" },
  { key: "refundTotal", title: "Refund Total" },
  { key: "paymentType", title: "Payment Type" },
  { key: "eRPSubmitMessage", title: "ERP Submit Message" },
  { key: "OpenRefund", title: "Update Totals/Add Comments" }
];
const isLoading = ref(false);
const isFetchingRefunds = ref(true);
const OpenRefunds = ref([]);
const originalRefunds = ref([]);
const spinnerLabel = ref('');

function LoadRefunds() {
  isFetchingRefunds.value = true;
  isLoading.value = true;
  spinnerLabel.value = 'Loading refunds...';
  OpenRefunds.value.splice(0, OpenRefunds.value.length);
  console.log("Loading AR Review Refunds...");
  const webhookCall = new Request(refundsIntegration.value.PHPUrl, {
    method: "POST",
    body: JSON.stringify({
      Action: "GetAROpenERP",
      Session: refundsIntegration.value.Session
    })
  });
  fetch(webhookCall).then((webhookResponse) => {
    isFetchingRefunds.value = false;
    isLoading.value = false;
    spinnerLabel.value = '';
    if (!webhookResponse.ok) {
      return;
    }
    webhookResponse.json().then((webhookJson) => {
      console.log(webhookJson);
      webhookJson.forEach((nextRefund) => {
        OpenRefunds.value.push(nextRefund);
      });
      // Store a deep copy for row resets
      originalRefunds.value = webhookJson.map(r => JSON.parse(JSON.stringify(r)));
    });
  }).catch(error => {
    isFetchingRefunds.value = false;
    isLoading.value = false;
    spinnerLabel.value = '';
  });
}

const oracleIntegration = ref(null);
const refundsIntegration = ref(null);
const WorkspaceContext = ref(null);

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

ORACLE_SERVICE_CLOUD.extension_loader.load("RefundsDashboard", "1.0").then(function(extensionLibrary) {
  extensionLibrary.registerWorkspaceExtension(function(workspaceContext) {
    WorkspaceContext.value = workspaceContext;
    extensionLibrary.getGlobalContext().then(function(globalContext) {
      globalContext.invokeAction("GETRefundsIntegrationSettings").then(function(integrationDetails) {
        refundsIntegration.value = integrationDetails.result[0];
        LoadRefunds();
        globalContext.invokeAction("GETOraceIntegrationSettings").then(function(integrationDetails) {
          oracleIntegration.value = integrationDetails.result[0];
        });

      });
    });
  });
});

function resetRow(item) {
  // Find the original data for this refund
  const orig = originalRefunds.value.find(r => r.refundId === item.refundId);
  if (orig) {
    item.shippingRefundDeduction = orig.shippingRefundDeduction;
    item.lineItems = JSON.parse(JSON.stringify(orig.lineItems));
    item.refundTotal = orig.refundTotal;
  }
  // Clear row selection and formatting
  if (rowSelections[item.refundId]) {
    delete rowSelections[item.refundId];
  }
  // Remove row-missing-comment class
  missingDeclineComments[item.refundId] = false;
  // Clear any saved comments for this row
  item.comments = '';
}

function approveAllRows() {
  OpenRefunds.value.forEach(item => {
    rowSelections[item.refundId] = 'APPROVE';
    missingDeclineComments[item.refundId] = false;
  });
}

function manualAllRows() {
  OpenRefunds.value.forEach(item => {
    rowSelections[item.refundId] = 'MANUAL';
    missingDeclineComments[item.refundId] = false;
  });
}

function declineAllRows() {
  OpenRefunds.value.forEach(item => {
    rowSelections[item.refundId] = 'DENY';
  });
}

function resetAllRows() {
  OpenRefunds.value.forEach(item => {
    resetRow(item);
    item.comments = '';
  });
}

const refundComments = ref('');
const showCommentsView = ref(false);
const refundCommentsList = ref([]);
const isLoadingComments = ref(false);

function loadRefundComments() {
  if (!selectedRefund.value) return;
  isLoadingComments.value = true;
  showCommentsView.value = true;
  refundCommentsList.value = [];
  fetch(refundsIntegration.value.PHPUrl, {
    method: 'POST',
    body: JSON.stringify({
      Action: 'GetRefundComments',
      Session: refundsIntegration.value?.Session,
      RefundID: selectedRefund.value.refundId
    })
  })
    .then(r => r.json())
    .then(data => {
      console.log('GetRefundComments response:', JSON.stringify(data));
      refundCommentsList.value = Array.isArray(data) ? data : [];
      isLoadingComments.value = false;
    })
    .catch(() => {
      isLoadingComments.value = false;
    });
}

function saveRefundComments() {
  if (!refundComments.value || refundComments.value.trim() === '') {
    window.alert('Please enter a comment before saving.');
    return;
  }
  if (selectedRefundIndex.value !== null && selectedRefund.value) {
    const refund = OpenRefunds.value[selectedRefundIndex.value];
    refund.comments = refundComments.value;
    refund.refundTotal = selectedRefund.value.refundTotal;
    refund.lineItems = (selectedRefund.value.lineItems || []).map(li => ({ ...li }));
    if (selectedRefund.value._freightEdit !== undefined) {
      refund.shippingRefundDeduction = selectedRefund.value._freightEdit;
      selectedRefund.value.shippingRefundDeduction = selectedRefund.value._freightEdit;
      delete selectedRefund.value._freightEdit;
    }
    missingDeclineComments[refund.refundId] = false;
    showOpenRefundModal.value = false;
  }
}

const showErrorDialog = ref(false);
const errorDialogMessage = ref('');
const errorDialogRefundId = ref('');

function openErrorDialog(message, refundId = '') {
  errorDialogMessage.value = message;
  errorDialogRefundId.value = refundId;
  showErrorDialog.value = true;
}
function closeErrorDialog() {
  showErrorDialog.value = false;
  errorDialogMessage.value = '';
  errorDialogRefundId.value = '';
}

function getTruncatedMessage(message) {
  if (!message) return { short: '', long: false };
  if (message.length > 30) {
    return { short: message.slice(0, 30) + '...', long: true };
  }
  return { short: message, long: false };
}
</script>

<template>
    <v-dialog v-model="showResultsModal" max-width="1450">
      <v-card>
        <v-card-title class="modal-title">
          <v-icon class="mr-2" color="primary">mdi-clipboard-check</v-icon>
          Refund Submission Results
        </v-card-title>
        <v-card-text>
          <v-table density="compact">
            <thead>
              <tr class="results-table-header">
                <th>Refund ID</th>
                <th>Order Number</th>
                <th>Refund Status</th>
                <th>Status Message</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="result in refundResults" :key="result.refundId"
                  :class="{
                    'result-success-row': result.refundStatus === 'AR_APPROVED_ERP_SUBMIT_SUCCESS' || result.refundStatus === 'AR_DENIED' || result.refundStatus === 'AR_MANUAL',
                    'result-error-row': result.refundStatus !== 'AR_APPROVED_ERP_SUBMIT_SUCCESS' && result.refundStatus !== 'AR_DENIED' && result.refundStatus !== 'AR_MANUAL'
                  }"
                  style="transition: box-shadow 0.2s, background 0.2s; box-shadow: 0 2px 8px rgba(25, 118, 210, 0.06); border-radius: 8px;">
                <td style="font-weight: 500;">{{ result.refundId }}</td>
                <td>{{ result.orderNumber }}</td>
                <td style="font-weight: bold; letter-spacing: 0.5px;">
                  <span v-if="result.refundStatus === 'AR_APPROVED_ERP_SUBMIT_SUCCESS' || result.refundStatus === 'AR_DENIED' || result.refundStatus === 'AR_MANUAL'" style="color: #388e3c;">Success</span>
                  <span v-else style="color: #c62828;">Error</span>
                </td>
                <td>{{ result.submitStatusMessage }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="closeResultsModal">Close Window</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  <v-row class="mb-4">
    <v-col cols="12" md="3">
      <v-card class="summary-card summary-card-approved" elevation="2">
        <div class="d-flex align-center pa-3">
          <v-icon color="success" size="32" class="mr-3">mdi-check-decagram</v-icon>
          <div>
            <div class="text-caption text-grey-darken-1">Refunds Approved</div>
            <div class="text-h5 font-weight-bold text-success">{{ approvedRefundsCount }}</div>
          </div>
        </div>
      </v-card>
    </v-col>
    <v-col cols="12" md="3">
      <v-card class="summary-card summary-card-manual" elevation="2">
        <div class="d-flex align-center pa-3">
          <v-icon color="warning" size="32" class="mr-3">mdi-flag</v-icon>
          <div>
            <div class="text-caption text-grey-darken-1">Marked Manual</div>
            <div class="text-h5 font-weight-bold text-warning">{{ manualRefundsCount }}</div>
          </div>
        </div>
      </v-card>
    </v-col>
    <v-col cols="12" md="3">
      <v-card class="summary-card summary-card-declined" elevation="2">
        <div class="d-flex align-center pa-3">
          <v-icon color="error" size="32" class="mr-3">mdi-cancel</v-icon>
          <div>
            <div class="text-caption text-grey-darken-1">Refunds Declined</div>
            <div class="text-h5 font-weight-bold text-error">{{ declinedRefundsCount }}</div>
          </div>
        </div>
      </v-card>
    </v-col>
    <v-col cols="12" md="3">
      <v-card class="summary-card summary-card-total" elevation="2">
        <div class="d-flex align-center pa-3">
          <v-icon color="primary" size="32" class="mr-3">mdi-cash-multiple</v-icon>
          <div>
            <div class="text-caption text-grey-darken-1">Total Approved</div>
            <div class="text-h6 font-weight-bold text-primary">{{ formatCurrency(approvedRefundsTotal) }}</div>
          </div>
        </div>
      </v-card>
    </v-col>
  </v-row>
  <v-overlay :model-value="isLoading" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important;">
    <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
      <v-icon size="80" color="primary" class="mb-4">mdi-file-document-multiple</v-icon>
      <v-progress-circular indeterminate size="64" color="primary" />
      <div v-if="spinnerLabel" class="text-h6 mt-4" style="color: #000;">{{ spinnerLabel }}</div>
    </div>
  </v-overlay>
  <v-snackbar v-model="showSuccessSnackbar" color="success" timeout="3000" location="top right">
    <template v-slot:actions>
      <v-btn variant="text" @click="showSuccessSnackbar = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
    {{ successMessage }}
  </v-snackbar>
  <v-row class="BUIExtensionRow">
    <v-col cols="12" class="mb-4">
      <v-btn-group divided>
        <v-btn color="success" prepend-icon="mdi-check-all" @click="approveAllRows">
          Approve All
        </v-btn>
        <v-btn color="warning" prepend-icon="mdi-flag-variant" @click="manualAllRows">
          Mark All as Manual
        </v-btn>
        <v-btn color="error" prepend-icon="mdi-close-box-multiple" @click="declineAllRows">
          Decline All
        </v-btn>
        <v-btn color="secondary" prepend-icon="mdi-restore" @click="resetAllRows">
          Reset All Rows
        </v-btn>
      </v-btn-group>
    </v-col>
      <!-- Other Refunds Modal -->
      <v-dialog v-model="showOtherRefundsModal" max-width="1350">
        <v-card class="modal-card">
          <v-card-title class="modal-title">
            <v-icon class="mr-2" color="warning">mdi-alert-circle</v-icon>
            Other Refunds for Order {{ selectedOtherRefundsOrderNumber }}
          </v-card-title>
          <v-card-text class="modal-content">
            <v-table density="compact" class="modal-table">
              <thead>
                <tr>
                  <th style="font-weight: bold;">Refund ID</th>
                  <th style="font-weight: bold;">Incident</th>
                  <th style="font-weight: bold;">Creation Date</th>
                  <th style="font-weight: bold;">Oracle Account</th>
                  <th style="font-weight: bold;">PO Number</th>
                  <th style="font-weight: bold;">Refund Amount</th>
                  <th style="font-weight: bold;">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in selectedOtherRefunds" :key="r.refundId">
                  <td>{{ r.refundId }}</td>
                  <td>
                    {{ r.cRMReferenceNumber }}
                    <v-btn
                      icon="mdi-open-in-new"
                      size="small"
                      variant="text"
                      @click="OpenIncidentbyRefNo(r.cRMReferenceNumber)"
                    ></v-btn>
                  </td>
                  <td style="white-space: nowrap;">{{ r.creationDate }}</td>
                  <td>{{ r.oracleAccountNo }}</td>
                  <td>{{ r.pONumber }}</td>
                  <td>{{ formatCurrency(r.refundAmount) }}</td>
                  <td>
                    <v-chip v-if="r.isUrgentRefund" color="error" size="small" variant="flat" class="mr-1">
                      <v-icon start size="14">mdi-alert</v-icon>URGENT
                    </v-chip>
                    {{ r.status }}
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="showOtherRefundsModal = false">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Open Refund Modal -->
      <v-dialog v-model="showOpenRefundModal" max-width="700">
        <v-card class="modal-card">
          <v-card-title class="modal-title">
            <v-icon class="mr-2">mdi-calculator</v-icon>
            Refund Summary / Add Comments
          </v-card-title>
          <!-- Main summary view -->
          <template v-if="!showCommentsView">
          <v-card-text class="modal-content">
            <v-row>
              <v-col cols="12" md="6">
                <div class="modal-section modal-summary-box">
                  <div class="modal-summary-row">
                    <span class="modal-label" style="font-weight: bold;">Refund ID:</span>
                    <span class="modal-summary-value">{{ selectedRefund.refundId }}</span>
                  </div>
                  <div class="modal-summary-row">
                    <span class="modal-label" style="font-weight: bold;">Order Number:</span>
                    <span class="modal-summary-value">{{ selectedRefund.orderNumber }}</span>
                  </div>
                  <div class="modal-summary-row">
                      <span class="modal-label" style="font-weight: bold;">Oracle Acct Num:</span>
                    <span class="modal-summary-value">{{ selectedRefund.oracleAccountNo }}</span>
                  </div>
                  <div class="modal-summary-row">
                        <span class="modal-label" style="font-weight: bold;">PO Number:</span>
                      <span class="modal-summary-value">{{ selectedRefund.pONumber }}</span>
                  </div>
                  <div class="modal-summary-row">
                    <span class="modal-label" style="font-weight: bold;">Line Items Subtotal:</span>
                    <span class="modal-summary-value">{{ formatCurrency(lineItemsSubTotal) }}</span>
                  </div>
                  <div class="modal-summary-row">
                    <span class="modal-label" style="font-weight: bold;">Shipping Refund:</span>
                    <span class="modal-summary-value">{{ formatCurrency(selectedRefund._freightEdit !== undefined ? selectedRefund._freightEdit : selectedRefund.shippingRefundDeduction) }}</span>
                  </div>
                  <div class="modal-summary-row modal-total-row">
                        <span class="modal-label" style="font-weight: bold; font-size: 1.15em; color: #1565c0; text-shadow: 0 1px 6px #e3f2fd;">Refund Total:</span>
                      <span class="modal-total-value" style="background: linear-gradient(90deg, #e3f2fd 60%, #c8e6c9 100%); font-size: 1.25em; font-weight: bold; color: #388e3c; padding: 4px 16px; border-radius: 8px; box-shadow: 0 2px 8px #1976d220; margin-left: 8px;">{{ formatCurrency(refundModalTotal) }}</span>
                  </div>
                </div>
              </v-col>
              <v-col cols="12">
                <div class="modal-section">
                    <v-table density="compact" class="modal-table">
                      <thead>
                        <tr>
                          <th style="font-weight: bold;">Part Description</th>
                          <th style="font-weight: bold;">Part Number</th>
                          <th style="font-weight: bold;">Refund Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(line, idx) in lineItemsWithFreight" :key="line.partDescription + line.partNumber + idx">
                          <td>{{ line.partDescription }}</td>
                          <td>{{ line.partNumber }}</td>
                          <td style="font-weight: 500; min-width: 120px;">
                            <v-text-field
                              :model-value="getLineRefundDisplay(line)"
                              @update:model-value="val => { line.lineRefundAmount = val }"
                              type="text"
                              density="compact"
                              variant="outlined"
                              hide-details
                              style="max-width: 110px;"
                              :step="0.01"
                              :min="0"
                              @focus="handleLineRefundFocus(line)"
                              @blur="() => handleLineRefundBlur(line, idx)"
                            />
                          </td>
                        </tr>
                      </tbody>
                    </v-table>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <div style="margin: 18px 0 0 0;">
            <label for="refundComments" class="modal-label" style="margin-bottom: 4px; display: block; font-weight: bold;">Comments:</label>
            <v-textarea
              id="refundComments"
              v-model="refundComments"
              rows="3"
              auto-grow
              placeholder="Enter any comments for this refund..."
              style="max-width: 100%;"
              hide-details
            />
          </div>
          <v-card-actions>
            <v-btn color="info" variant="outlined" prepend-icon="mdi-comment-text-multiple" @click="loadRefundComments">
                View Comments
              </v-btn>
            <v-spacer></v-spacer>
            <v-btn color="primary" text @click="saveRefundComments">Save Changes</v-btn>
            <v-btn color="secondary" text @click="closeRefundModal">Close</v-btn>
          </v-card-actions>
          </template>

          <!-- Comments history view -->
          <template v-else>
            <v-card-text class="modal-content">
              <div v-if="isLoadingComments" class="d-flex justify-center align-center pa-8">
                <v-progress-circular indeterminate color="primary" class="mr-3" />
                <span class="text-body-1">Loading comments...</span>
              </div>
              <template v-else>
                <div v-if="refundCommentsList.length === 0" class="pa-6 text-center">
                  <v-icon size="48" color="grey-lighten-1">mdi-comment-off-outline</v-icon>
                  <div class="text-body-1 mt-3 text-grey">No comments found for this refund.</div>
                </div>
                <v-table v-else density="compact" class="modal-table">
                  <thead>
                    <tr>
                      <th style="font-weight: bold;">Date</th>
                      <th style="font-weight: bold;">Created By</th>
                      <th style="font-weight: bold;">Comment</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(c, idx) in refundCommentsList" :key="idx">
                      <td style="white-space: nowrap;">{{ formatDateET(c.createddate) }}</td>
                      <td style="white-space: nowrap;">{{ c.createdby }}</td>
                      <td>{{ c.comment }}</td>
                    </tr>
                  </tbody>
                </v-table>
              </template>
            </v-card-text>
            <v-card-actions>
              <v-btn color="secondary" variant="outlined" prepend-icon="mdi-arrow-left" @click="showCommentsView = false">
                Go Back
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn color="secondary" text @click="closeRefundModal">Close</v-btn>
            </v-card-actions>
          </template>
        </v-card>
      </v-dialog>
    <v-col class="text-right">
      <v-btn prepend-icon="mdi-refresh" class="BissellButton" @click="LoadRefunds()">
        Refresh Open Refunds
      </v-btn>
    </v-col>
  </v-row>
  <v-row>
    <v-data-table
      :headers="OpenRefundsHeaders"
      :items="OpenRefunds"
      :loading="isFetchingRefunds"
      :items-per-page="100"
      class="refunds-data-table"
    >
      <template v-slot:headers="{columns}">
        <tr class="BissellBanner">
          <template v-for="column in columns" :key="column.key">
            <td>
              {{column.title}}
            </td>
          </template>
        </tr>
      </template>
      <!-- Custom body slot for full row rendering -->
      <template v-slot:body="{ items }">
        <template v-if="items.length === 0">
          <tr>
            <td :colspan="OpenRefundsHeaders.length" style="text-align:center; padding: 32px 0; color: #888; font-size: 1.15em; background: #f5f5f5; font-style: italic;">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-inbox</v-icon>
              <div>There are currently no open refunds awaiting A/R Approval.</div>
            </td>
          </tr>
        </template>
        <template v-else>
          <tr v-for="item in items" :key="item.refundId" :class="['refund-row', getRowClass(item)]">
            <td>
              <v-tooltip location="top" open-on-hover>
                <template #activator="{ props }">
                  <v-icon v-bind="props" class="mr-2 action-icon" :class="{ selected: rowSelections[item.refundId] === 'APPROVE' }" @click="selectRow(item.refundId, 'approve')" :color="rowSelections[item.refundId] === 'APPROVE' ? 'success' : undefined">mdi-check-decagram</v-icon>
                </template>
                <span>Approve Refund</span>
              </v-tooltip>
              <v-tooltip location="top" open-on-hover>
                <template #activator="{ props }">
                  <v-icon v-bind="props" class="mr-2 action-icon" :class="{ selected: rowSelections[item.refundId] === 'MANUAL' }" @click="selectRow(item.refundId, 'manual')" :color="rowSelections[item.refundId] === 'MANUAL' ? 'warning' : undefined">mdi-flag</v-icon>
                </template>
                <span>Manually Resolve Refund</span>
              </v-tooltip>
              <v-tooltip location="top" open-on-hover>
                <template #activator="{ props }">
                  <v-icon v-bind="props" class="mr-2 action-icon" :class="{ selected: rowSelections[item.refundId] === 'DENY' }" @click="selectRow(item.refundId, 'cancel')" :color="rowSelections[item.refundId] === 'DENY' ? 'error' : undefined">mdi-cancel</v-icon>
                </template>
                <span>Decline Refund</span>
              </v-tooltip>
              <v-tooltip location="top" open-on-hover>
                <template #activator="{ props }">
                  <v-icon v-bind="props" class="action-icon" @click="resetRow(item)">mdi-restore</v-icon>
                </template>
                <span>Reset Row</span>
              </v-tooltip>
            </td>
            <td>{{ item.refundId }}</td>
            <td>
              {{ item.cRMReferenceNumber }}
              <v-btn
                icon="mdi-open-in-new"
                size="small"
                variant="text"
                @click="OpenIncidentbyRefNo(item.cRMReferenceNumber)"
              ></v-btn>
            </td>
            <td>
              {{ item.orderNumber }}
              <v-tooltip v-if="item.otherRefunds && item.otherRefunds.length > 0" location="top" open-on-hover>
                <template #activator="{ props }">
                  <v-icon v-bind="props" color="warning" size="18" class="ml-1" style="cursor:pointer;" @click="openOtherRefundsModal(item)">mdi-alert-circle</v-icon>
                </template>
                <span>{{ item.otherRefunds.length }} other refund{{ item.otherRefunds.length > 1 ? 's' : '' }} associated with this order</span>
              </v-tooltip>
            </td>
            <td>{{ item.oracleAccountNo || '' }}</td>
            <td>{{ item.pONumber || '' }}</td>
            <td>{{ formatDateET(item.arReviewedDate) }}</td>
            <td>{{ formatCurrency(item.refundTotal) }}</td>
            <td>{{ item.paymentType }}</td>
            <td>
              <template v-if="getTruncatedMessage(item.eRPSubmitMessage).long">
                {{ getTruncatedMessage(item.eRPSubmitMessage).short }}
                <a href="#" @click.prevent="openErrorDialog(item.eRPSubmitMessage, item.refundId)">View Full Error Message</a>
              </template>
              <template v-else>
                {{ getTruncatedMessage(item.eRPSubmitMessage).short }}
              </template>
            </td>
            <td>
              <v-btn aria-label="Open Refund" density="compact" prepend-icon="mdi-open-in-app" size="small" variant="text" @click="openRefundModal(item)">
                <template v-slot:prepend>
                  <v-icon color="success"></v-icon>
                </template>
                Update Totals/Add Comments
                <v-icon v-if="item.comments && item.comments.trim() !== ''" color="success" size="18" class="ml-1">mdi-comment-check</v-icon>
                <v-icon v-if="missingDeclineComments[item.refundId]" color="error" size="20" class="ml-1 pulse-icon">mdi-alert-circle</v-icon>
              </v-btn>
            </td>
          </tr>
        </template>
      </template>
    </v-data-table>
  </v-row>
  <div class="d-flex flex-column align-end mt-8 mb-4">
    <v-btn color="primary" size="large" @click="submitRefunds" :disabled="isProcessingRefunds">
      <v-icon left>mdi-send</v-icon>
      Submit Refunds
    </v-btn>
  </div>



  <v-overlay :model-value="isProcessingRefunds" persistent class="overlay-center" style="z-index: 9999; background: #fff !important; box-shadow: none !important;">
    <div class="d-flex flex-column justify-center align-center" style="height: 100vh; min-width: 320px; background: #fff; overflow: visible; position: relative;">
      <!-- Native div-based progress bar for overlay (replaces Vuetify bar) -->
      <div style="height: 20px; width: 380px; background: #eee; border-radius: 10px; margin-bottom: 36px; box-shadow: 0 2px 12px #1976d2a0; position: relative; overflow: hidden;">
        <div :style="{height: '100%', width: (isProcessingRefunds ? progressBarValue : 100) + '%', background: '#1976d2', borderRadius: '10px', transition: 'width 0.3s', position: 'absolute', left: 0, top: 0}"></div>
      </div>
    
      <div class="text-h6 mb-2" style="color: #1976d2; font-weight: bold; letter-spacing: 0.5px; text-shadow: 0 1px 6px #e3f2fd;">{{ processingRefundLabel }}</div>
      <div style="color: #1976d2; font-size: 16px; font-weight: 500; margin-bottom: 8px;">Processing Time: {{ processingTime }}s</div>
      <div style="color: #888;">Please do not close or refresh your browser.</div>
    </div>
  </v-overlay>

  <!-- Error Message Dialog -->
  <v-dialog v-model="showErrorDialog" max-width="500">
    <v-card>
      <v-card-title class="modal-title">
        <v-icon class="mr-2">mdi-alert-circle</v-icon>
        Full Error Message
      </v-card-title>
      <v-card-text style="white-space: pre-wrap; word-break: break-word;">
        <div style="font-weight: bold; color: #1976d2; margin-bottom: 8px;">Refund ID: {{ errorDialogRefundId }}</div>
        {{ errorDialogMessage }}
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" text @click="closeErrorDialog">Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style>
.modal-card {
  background: #f9fbfd;
  border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
}
.modal-title {
  font-size: 1.3em;
  font-weight: 600;
  color: #1976d2;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
}
.modal-content {
  padding: 24px 16px 8px 16px;
}
.modal-section {
  margin-bottom: 18px;
  background: #fff;
  border-radius: 8px;
  padding: 16px 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.modal-summary-box {
  padding: 8px 12px 6px 12px;
  background: #e3eafc;
  border-radius: 10px;
  box-shadow: 0 1px 6px rgba(25, 118, 210, 0.07);
  margin-bottom: 8px;
  font-size: 0.97em;
  border: 2px solid #1976d2;
}
.modal-summary-row {
  display: flex;
  align-items: center;
  margin-bottom: 2px;
}
.modal-summary-row .modal-label {
  font-weight: 500;
  color: #333;
  margin-right: 6px;
}
.modal-summary-value {
  font-weight: 500;
  color: #1976d2;
  margin-right: 16px;
}
.modal-total-row {
  margin-top: 4px;
  font-size: 1.08em;
}
.modal-label {
  font-weight: 500;
  margin-right: 8px;
  color: #333;
}
.modal-table th {
  background: #e3eafc;
  color: #1976d2;
  font-weight: 600;
  border-bottom: 1px solid #e0e0e0;
}
.modal-table td {
  background: #fff;
}
.modal-total-value {
  color: #388e3c;
  font-weight: bold;
  margin-left: 8px;
}
.action-icon {
  cursor: pointer;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  border-radius: 50%;
  padding: 2px;
}
.action-icon:hover {
  background: #e0e0e0;
  box-shadow: 0 0 4px #bdbdbd;
}
.row-approved {
  background-color: #e8f5e9 !important;
}
.row-denied {
  background-color: #ffebee !important;
}
.row-manual {
  background-color: #fff3e0 !important;
}
.selected {
  font-weight: bold;
}
/* Force overlay scrim to white and fully opaque */
.v-overlay__scrim {
  background: #fff !important;
  opacity: 1 !important;
}

/* Results modal row coloring and formatting */
.result-success-row {
  background-color: #e8f5e9 !important;
  border-left: 6px solid #43a047;
}
.result-error-row {
  background-color: #ffebee !important;
  border-left: 6px solid #e53935;
}
.v-table tbody tr.result-success-row td,
.v-table tbody tr.result-error-row td {
  border-bottom: 1px solid #e0e0e0;
}

/* Results modal table header styling */
.results-table-header th {
    background: #1976d2;
    color: #fff;
    font-weight: bold;
    font-size: 1.08em;
    letter-spacing: 0.5px;
    border-bottom: 2.5px solid #1565c0;
    padding: 10px 8px;
}

.row-missing-comment {
  border: 2px solid #d32f2f !important;
  background: #fff0f0 !important;
}

.refunds-data-table table {
  border-collapse: collapse !important;
}

/* Summary cards styling */
.summary-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 12px !important;
}
.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
}
.summary-card-approved {
  border-left: 4px solid #4caf50 !important;
  background: linear-gradient(135deg, #ffffff 0%, #f1f8f4 100%) !important;
}
.summary-card-manual {
  border-left: 4px solid #f57c00 !important;
  background: linear-gradient(135deg, #ffffff 0%, #fff8f0 100%) !important;
}
.summary-card-declined {
  border-left: 4px solid #f44336 !important;
  background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%) !important;
}
.summary-card-total {
  border-left: 4px solid #1976d2 !important;
  background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%) !important;
}

/* Table row hover effects */
.refund-row {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.refund-row:hover {
  background-color: #f5f5f5 !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.row-approved {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.row-approved:hover {
  background-color: #c8e6c9 !important;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
}
.row-denied {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.row-denied:hover {
  background-color: #ffcdd2 !important;
  box-shadow: 0 2px 8px rgba(244, 67, 54, 0.2);
}
.row-manual {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.row-manual:hover {
  background-color: #ffe0b2 !important;
  box-shadow: 0 2px 8px rgba(245, 124, 0, 0.2);
}

/* Pulsing animation for missing comments */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.15);
  }
}
.pulse-icon {
  animation: pulse 2s ease-in-out infinite;
}

/* Button group styling */
.v-btn-group {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  border-radius: 8px !important;
}
.v-btn-group .v-btn {
  transition: all 0.2s ease;
}
.v-btn-group .v-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Modal table enhancements */
.modal-table tbody tr {
  transition: background-color 0.2s ease;
}
.modal-table tbody tr:hover {
  background-color: #f5f5f5 !important;
}

/* Button group styling */
.v-btn-group {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  border-radius: 8px !important;
}

</style>
