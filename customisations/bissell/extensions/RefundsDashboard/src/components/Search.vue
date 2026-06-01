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
  <v-overlay :model-value="isSearching" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important; z-index: 9999;">
    <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
      <v-progress-circular indeterminate size="64" color="primary" />
      <div class="text-h6 mt-4" style="color: #000;">
        <v-icon color="primary" class="mr-2">mdi-magnify</v-icon>
        {{ searchLabel }}
      </div>
    </div>
  </v-overlay>

  <!-- Snackbar for errors and notifications -->
  <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="4000" location="top right">
    <template v-slot:actions>
      <v-btn variant="text" @click="showSnackbar = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
    {{ snackbarMessage }}
  </v-snackbar>

  <!-- Search Options -->
  <div class="search-options-container">
    <!-- Consumer ID Search -->
    <div class="search-row">
      <label for="consumerIdInput" class="search-label">Consumer ID:</label>
      <v-text-field
        id="consumerIdInput"
        v-model="consumerId"
        type="text"
        density="compact"
        variant="outlined"
        placeholder="Enter Consumer ID"
        hide-details
        class="search-input-field"
        :disabled="isSearching"
        @keyup.enter="searchByConsumerId"
      />
      <v-btn
        color="primary"
        prepend-icon="mdi-magnify"
        @click="searchByConsumerId"
        :disabled="isSearching"
        class="search-btn-vuetify"
      >
        Search Consumer ID
      </v-btn>
    </div>

    <!-- Order Number Search -->
    <div class="search-row">
      <label for="orderNumberInput" class="search-label">Order Number:</label>
      <v-text-field
        id="orderNumberInput"
        v-model="orderNumber"
        type="text"
        density="compact"
        variant="outlined"
        placeholder="Enter Order Number"
        hide-details
        class="search-input-field"
        :disabled="isSearching"
        @keyup.enter="searchByOrderNumber"
      />
      <v-btn
        color="primary"
        prepend-icon="mdi-magnify"
        @click="searchByOrderNumber"
        :disabled="isSearching"
        class="search-btn-vuetify"
      >
        Search Order Number
      </v-btn>
    </div>

    <!-- Start Date / End Date Search -->
    <div class="search-row">
      <label for="startDateInput" class="search-label">Start Date:</label>
      <v-text-field
        id="startDateInput"
        v-model="startDate"
        type="date"
        density="compact"
        variant="outlined"
        hide-details
        class="search-input-field"
        :disabled="isSearching"
      />
      <label for="endDateInput" class="search-label" style="margin-left: 12px;">End Date:</label>
      <v-text-field
        id="endDateInput"
        v-model="endDate"
        type="date"
        density="compact"
        variant="outlined"
        hide-details
        class="search-input-field"
        :disabled="isSearching"
        @keyup.enter="searchByDateRange"
      />
      <v-btn
        color="primary"
        prepend-icon="mdi-magnify"
        @click="searchByDateRange"
        :disabled="isSearching"
        class="search-btn-vuetify"
      >
        Search Date Range
      </v-btn>
    </div>

    <!-- Clear/Reset Button -->
    <div class="search-row">
      <v-btn
        color="secondary"
        prepend-icon="mdi-refresh"
        @click="clearSearch"
        :disabled="isSearching"
        variant="outlined"
      >
        Clear Search
      </v-btn>
    </div>
  </div>

  <!-- Empty state message when search has been performed but no results -->
  <div v-if="searchPerformed && searchResults.length === 0" class="empty-results-container">
    <v-icon size="64" color="grey-lighten-1" class="mb-3">mdi-magnify-close</v-icon>
    <div class="empty-results-text">No refunds found for your search criteria</div>
    <div class="empty-results-subtext">Try adjusting your search parameters</div>
  </div>

  <!-- Results Table -->
  <div v-if="searchResults.length > 0" class="search-results-table-container">
    <div class="search-results-heading">
      {{ resultsHeading }}
    </div>
    <v-table density="compact" class="search-results-table">
      <thead>
        <tr>
          <th>Refund ID</th>
          <th>OSvC Incident</th>
          <th>Order Number</th>
          <th>Consumer ID</th>
          <th>Oracle Account Num</th>
          <th>PO Number</th>
          <th>Creation Date</th>
          <th>Refund Amount</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="result in searchResults" :key="result.refundId" class="result-row">
          <td>{{ result.refundId }}</td>
          <td>
            <v-icon
              size="18"
              color="primary"
              class="mr-1"
              style="cursor:pointer; vertical-align: middle;"
              title="Open Refund Incident"
              @click="OpenIncidentbyRefNo(result.cRMReferenceNumber)"
            >mdi-open-in-new</v-icon>
            {{ result.cRMReferenceNumber }}
          </td>
          <td>{{ result.orderNumber }}</td>
          <td>{{ result.cRMConsumerID }}</td>
          <td>{{ result.oracleAccountNo }}</td>
          <td>{{ result.pONumber }}</td>
          <td>{{ result.creationDate ? result.creationDate.replace(/Z$/, '') : '' }}</td>
          <td>{{ formatCurrency(result.refundAmount) }}</td>
          <td>{{ result.status }}</td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup>
import { computed, ref, nextTick } from 'vue';

const isOtherRefundLoading = ref(false);
const otherRefundSpinnerLabel = ref('Retrieving Refund Incident...');
const lastSearchType = ref('');
const lastSearchValue = ref('');
const searchPerformed = ref(false);

// Snackbar state
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('error');

function showError(message) {
  snackbarMessage.value = message;
  snackbarColor.value = 'error';
  showSnackbar.value = true;
}

function showSuccess(message) {
  snackbarMessage.value = message;
  snackbarColor.value = 'success';
  showSnackbar.value = true;
}

const resultsHeading = computed(() => {
  if (!searchResults.value.length) return '';
  const count = searchResults.value.length;
  const resultText = count === 1 ? 'result' : 'results';
  if (lastSearchType.value === 'consumerId') return `Refunds for Consumer ID ${lastSearchValue.value} (${count} ${resultText})`;
  if (lastSearchType.value === 'orderNumber') return `Refunds for Order Number ${lastSearchValue.value} (${count} ${resultText})`;
  if (lastSearchType.value === 'dateRange') return `Refunds from ${lastSearchValue.value} (${count} ${resultText})`;
  return `Refunds (${count} ${resultText})`;
});

const isSearching = ref(false);
const searchLabel = ref('Searching...');
const searchResults = ref([]);

function formatCurrency(value) {
  if (value == null || value === '') return '';
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(value);
}

// Search input refs
const consumerId = ref('');
const orderNumber = ref('');
const startDate = ref('');
const endDate = ref('');

// Clear search function
function clearSearch() {
  consumerId.value = '';
  orderNumber.value = '';
  startDate.value = '';
  endDate.value = '';
  searchResults.value = [];
  lastSearchType.value = '';
  lastSearchValue.value = '';
  searchPerformed.value = false;
}

// Search action implementations
async function searchByConsumerId() {
  lastSearchType.value = 'consumerId';
  lastSearchValue.value = consumerId.value;
  if (!consumerId.value) {
    showError('Please enter a Consumer ID.');
    return;
  }
  // Integer validation
  if (!/^\d+$/.test(consumerId.value)) {
    showError('Invalid Consumer ID. Please enter only numbers.');
    return;
  }
  isSearching.value = true;
  searchLabel.value = `Searching Refunds for Consumer ID ${consumerId.value}...`;
  searchResults.value = [];
  searchPerformed.value = true;
  const body = {
    Action: 'GetOtherRefunds',
    ConsumerID: consumerId.value,
    Session: refundsIntegration.value?.Session
  };
  try {
    const response = await fetch(refundsIntegration.value.PHPUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const result = await response.json();
    console.log('Consumer ID search result:', result);
    searchResults.value = Array.isArray(result) ? result : (result?.OtherRefunds || []);
    if (searchResults.value.length > 0) {
      showSuccess(`Found ${searchResults.value.length} refund${searchResults.value.length === 1 ? '' : 's'}`);
    }
  } catch (error) {
    console.error('Consumer ID search error:', error);
    showError('Search failed. Please try again.');
  } finally {
    isSearching.value = false;
  }
}

async function searchByOrderNumber() {
  lastSearchType.value = 'orderNumber';
  lastSearchValue.value = orderNumber.value;
  if (!orderNumber.value) {
    showError('Please enter an Order Number.');
    return;
  }
  // Integer validation
  if (!/^\d+$/.test(orderNumber.value)) {
    showError('Invalid Order Number. Please enter only numbers.');
    return;
  }
  isSearching.value = true;
  searchLabel.value = `Searching Refunds for Order Number ${orderNumber.value}...`;
  searchResults.value = [];
  searchPerformed.value = true;
  const body = {
    Action: 'GetOtherRefundsByOrderNumber',
    OrderNumber: orderNumber.value,
    Session: refundsIntegration.value?.Session
  };
  try {
    const response = await fetch(refundsIntegration.value.PHPUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const result = await response.json();
    console.log('Order Number search result:', result);
    searchResults.value = Array.isArray(result) ? result : (result?.OtherRefunds || []);
    if (searchResults.value.length > 0) {
      showSuccess(`Found ${searchResults.value.length} refund${searchResults.value.length === 1 ? '' : 's'}`);
    }
  } catch (error) {
    console.error('Order Number search error:', error);
    showError('Search failed. Please try again.');
  } finally {
    isSearching.value = false;
  }
}

async function searchByDateRange() {
  lastSearchType.value = 'dateRange';
  lastSearchValue.value = `${startDate.value} to ${endDate.value}`;
  if (!startDate.value || !endDate.value) {
    showError('Please enter both Start Date and End Date.');
    return;
  }
  // Validate that end date is not before start date
  const start = new Date(startDate.value);
  const end = new Date(endDate.value);
  if (end < start) {
    showError('End Date cannot be before Start Date.');
    return;
  }
  // Ensure date format is YYYY-MM-DD
  const formatDate = (d) => {
    if (!d) return '';
    const date = new Date(d);
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  };
  // Add +1 day to end date
  const addOneDay = (d) => {
    if (!d) return '';
    const date = new Date(d);
    date.setDate(date.getDate() + 1);
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  };
  isSearching.value = true;
  searchLabel.value = `Searching Refunds from ${startDate.value} to ${endDate.value}...`;
  searchResults.value = [];
  searchPerformed.value = true;
  const body = {
    Action: 'GetOtherRefundsByDateRange',
    StartDate: formatDate(startDate.value),
    EndDate: addOneDay(endDate.value),
    Session: refundsIntegration.value?.Session
  };
  try {
    const response = await fetch(refundsIntegration.value.PHPUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const result = await response.json();
    console.log('Date range search result:', result);
    searchResults.value = Array.isArray(result) ? result : (result?.OtherRefunds || []);
    if (searchResults.value.length > 0) {
      showSuccess(`Found ${searchResults.value.length} refund${searchResults.value.length === 1 ? '' : 's'}`);
    }
  } catch (error) {
    console.error('Date range search error:', error);
    showError('Search failed. Please try again.');
  } finally {
    isSearching.value = false;
  }
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
      showError('Failed to open incident.');
    });
}

/***** INTEGRATION DETAILS *****/
const oracleIntegration = ref(null);
const refundsIntegration = ref(null);
const WorkspaceContext = ref(null);
ORACLE_SERVICE_CLOUD.extension_loader.load("RefundsDashboard", "1.0").then(function (extensionLibrary) {
  extensionLibrary.registerWorkspaceExtension(function (workspaceContext) {
    WorkspaceContext.value = workspaceContext;
    extensionLibrary.getGlobalContext().then(function (globalContext) {
      globalContext.invokeAction("GETOraceIntegrationSettings").then(function (integrationDetails) {
        oracleIntegration.value = integrationDetails.result[0];
      });
      globalContext.invokeAction("GETRefundsIntegrationSettings").then(function (integrationDetails) {
        refundsIntegration.value = integrationDetails.result[0];
      });
    });
  });
});
</script>

<style scoped>
.search-results-table-container {
  margin: 32px 0 0 0;
  width: 100%;
  background: #fff;
  padding: 0 0 24px 0;
  box-sizing: border-box;
}
.search-results-table {
  width: 100%;
  min-width: 900px;
  border-collapse: collapse;
}
.search-results-table th {
  background: #1976d2;
  color: #fff;
  font-weight: bold;
  font-size: 1.05em;
  letter-spacing: 0.5px;
  border-bottom: 2.5px solid #1565c0;
  padding: 10px 8px;
}
.search-results-table td {
  background: #fff;
  font-size: 1em;
  padding: 10px 8px;
  border-bottom: 1px solid #e0e0e0;
}
.result-row {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.result-row:hover {
  background-color: #f5f5f5 !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.search-options-container {
  display: flex;
  flex-direction: column;
  gap: 18px;
  width: 100%;
  margin: 32px 0 0 0;
  padding: 24px 18px;
  background: linear-gradient(135deg, #f9fbfd 0%, #ffffff 100%);
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(25, 118, 210, 0.1);
  align-items: flex-start;
  border-left: 4px solid #1976d2;
}
.search-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 6px;
}
.search-label {
  font-weight: 600;
  color: #1976d2;
  min-width: 110px;
}
.search-input-field {
  min-width: 200px;
  max-width: 250px;
}
.search-btn-vuetify {
  margin-left: 8px;
}
.search-results-heading {
  font-size: 1.25em;
  font-weight: bold;
  margin-bottom: 16px;
  color: #1976d2;
  display: flex;
  align-items: center;
}
.empty-results-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 32px;
  background: #f5f5f5;
  border-radius: 12px;
  margin-top: 32px;
}
.empty-results-text {
  font-size: 1.25em;
  font-weight: 600;
  color: #666;
  margin-bottom: 8px;
}
.empty-results-subtext {
  font-size: 1em;
  color: #999;
  font-style: italic;
}
</style>
