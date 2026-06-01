<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue';

const selectedCount = computed(() => OpenRefundItems.value.filter(item => item.resolved).length);
const totalCount = computed(() => OpenRefundItems.value.length);

// Snackbar state
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

function showError(message) {
  snackbarMessage.value = message;
  snackbarColor.value = 'error';
  snackbar.value = true;
}

function showSuccess(message) {
  snackbarMessage.value = message;
  snackbarColor.value = 'success';
  snackbar.value = true;
}

const isLoading = ref(false);
const isFetchingRefunds = ref(true);
const OpenRefundItems = ref([]);
const spinnerLabel = ref('');
const FetchingRefunds = computed(() => isFetchingRefunds.value);
const OpenRefunds = computed(() => OpenRefundItems.value);

const detailsDialog = ref(false);
const selectedDetails = ref(null);

const tableHeaders = [
  { key: 'resolved', title: '', sortable: false },
  { key: 'name', title: 'Name' },
  { key: 'address', title: 'Address' },
  { key: 'city', title: 'City' },
  { key: 'regionCode', title: 'Region' },
  { key: 'postalCode', title: 'Postal Code' },
  { key: 'country', title: 'Country' },
  { key: 'details', title: '', sortable: false }
];

function showDetails(item) {
  selectedDetails.value = item;
  detailsDialog.value = true;
}

function LoadRefunds() {
  if (!refundsIntegration.value || !refundsIntegration.value.PHPUrl || !refundsIntegration.value.Session) {
    console.error('Refunds integration details are missing.');
    showError('Integration settings are missing. Please contact support.');
    return;
  }
  isLoading.value = true;
  isFetchingRefunds.value = true;
  spinnerLabel.value = 'Retrieving Open Ad Hoc Requests...';
  fetch(refundsIntegration.value.PHPUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      Action: 'GetAdHocRequests',
      Session: refundsIntegration.value.Session
    })
  })
    .then(response => response.json())
    .then(data => {
      OpenRefundItems.value = Array.isArray(data)
        ? data.map(item => ({
            requestId: item.requestId,
            resolved: false,
            name: item.name,
            address: item.address,
            city: item.city,
            regionCode: item.regionCode,
            postalCode: item.postalCode,
            country: item.country,
            createdBy: item.createdBy,
            creationDate: item.creationDate,
            trackingNumber: item.trackingNumber,
            itemNumber: item.itemNumber,
            comments: item.comments
          }))
        : [];
      isLoading.value = false;
      isFetchingRefunds.value = false;
      if (OpenRefundItems.value.length > 0) {
        showSuccess(`Loaded ${OpenRefundItems.value.length} Ad Hoc request${OpenRefundItems.value.length === 1 ? '' : 's'}`);
      }
    })
    .catch(error => {
      console.error('Error loading AdHoc requests:', error);
      showError('Failed to load Ad Hoc requests. Please try again.');
      isLoading.value = false;
      isFetchingRefunds.value = false;
    });
}

async function submitResolvedRequests() {
  // Collect requestIds for resolved rows
  const resolvedIds = OpenRefundItems.value
    .filter(item => item.resolved)
    .map(item => item.requestId);

  if (!refundsIntegration.value || !refundsIntegration.value.PHPUrl || !refundsIntegration.value.Session) {
    console.error('Refunds integration details are missing.');
    showError('Integration settings are missing. Please contact support.');
    return;
  }

  if (resolvedIds.length === 0) {
    showError('Please select at least one request to mark as resolved.');
    return;
  }

  isLoading.value = true;
  spinnerLabel.value = 'Marking Ad Hoc Requests as Resolved...';

  try {
    const response = await fetch(refundsIntegration.value.PHPUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        Action: 'MarkAdHocsResolved',
        Session: refundsIntegration.value.Session,
        RefundIDs: resolvedIds
      })
    });
    const data = await response.json();
    console.log('MarkAdHocsResolved API response:', data);

    if (data && data.returnMessage === 'SUCCESS') {
      showSuccess(`Successfully marked ${resolvedIds.length} request${resolvedIds.length === 1 ? '' : 's'} as resolved.`);
    } else {
      showError('Some requests failed to update. Please check the logs.');
    }
  } catch (error) {
    console.error('Error marking AdHoc requests as resolved:', error);
    showError('Failed to mark requests as resolved. Please try again.');
  } finally {
    isLoading.value = false;
    spinnerLabel.value = 'Retrieving Open Ad Hoc Requests...';
    LoadRefunds();
  }
}

const oracleIntegration = ref(null);
const refundsIntegration = ref(null);
const WorkspaceContext = ref(null);

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

</script>

<template>
  <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="5000" location="top right">
    <template v-slot:actions>
      <v-btn variant="text" @click="snackbar = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </template>
    {{ snackbarMessage }}
  </v-snackbar>

  <div>
    <!-- Summary Cards Row -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-card class="summary-card summary-card-selected" elevation="2">
          <div class="d-flex align-center pa-3">
            <v-icon color="success" size="32" class="mr-3">mdi-checkbox-marked-circle</v-icon>
            <div>
              <div class="text-caption text-grey-darken-1">Requests Selected</div>
              <div class="text-h5 font-weight-bold text-success">{{ selectedCount }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card class="summary-card summary-card-total" elevation="2">
          <div class="d-flex align-center pa-3">
            <v-icon color="primary" size="32" class="mr-3">mdi-inbox-multiple</v-icon>
            <div>
              <div class="text-caption text-grey-darken-1">Total Open Requests</div>
              <div class="text-h5 font-weight-bold text-primary">{{ totalCount }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Action Buttons Row -->
    <div class="d-flex justify-end align-center mb-4">
      <v-btn
        color="primary"
        prepend-icon="mdi-refresh"
        size="large"
        elevation="2"
        @click="LoadRefunds"
        :disabled="isLoading"
      >
        Refresh Open Ad Hoc Requests
      </v-btn>
    </div>

    <v-overlay :model-value="isLoading" persistent class="overlay-center" style="background: #fff !important; opacity: 1 !important;">
      <div class="d-flex flex-column justify-center align-center" style="height: 100%;">
        <v-progress-circular indeterminate size="64" color="primary" />
        <div class="text-h6 mt-4" style="color: #000;">
          <v-icon color="primary" class="mr-2">mdi-reload</v-icon>
          {{ spinnerLabel }}
        </div>
      </div>
    </v-overlay>

    <v-card v-if="OpenRefunds.length > 0" class="mt-6">
      <v-card-text>
        <v-data-table
          :headers="tableHeaders"
          :items="OpenRefunds"
          :loading="FetchingRefunds"
          :items-per-page="100"
          class="adhoc-data-table"
        >
          <template v-slot:headers="{columns}">
            <tr class="adhoc-table-header">
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
                <td :colspan="tableHeaders.length" class="empty-state-cell">
                  <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-inbox</v-icon>
                  <div>There are currently no open Ad Hoc Requests.</div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="item in items" :key="item.requestId" :class="['adhoc-row', { 'resolved-row': item.resolved }]">
                <td>
                  <v-tooltip text="Mark as Resolved" location="top">
                    <template #activator="{ props }">
                      <v-checkbox
                        v-bind="props"
                        :model-value="item.resolved"
                        @update:model-value="val => item.resolved = val"
                        hide-details
                        density="compact"
                        color="success"
                      />
                    </template>
                  </v-tooltip>
                </td>
                <td>{{ item.name }}</td>
                <td>{{ item.address }}</td>
                <td>{{ item.city }}</td>
                <td>{{ item.regionCode }}</td>
                <td>{{ item.postalCode }}</td>
                <td>{{ item.country }}</td>
                <td>
                  <v-btn
                    aria-label="View Details"
                    density="compact"
                    prepend-icon="mdi-open-in-app"
                    size="small"
                    variant="text"
                    @click="showDetails(item)"
                  >
                    <template v-slot:prepend>
                      <v-icon color="primary"></v-icon>
                    </template>
                    View Details
                  </v-btn>
                </td>
              </tr>
            </template>
          </template>
        </v-data-table>

        <!-- Details Modal -->
        <v-dialog v-model="detailsDialog" max-width="600">
          <v-card>
            <v-card-title class="modal-title">
              <v-icon class="mr-2">mdi-information</v-icon>
              AdHoc Request Details
            </v-card-title>
            <v-card-text v-if="selectedDetails" class="modal-content">
              <div class="modal-section">
                <div class="modal-info-row">
                  <span class="modal-label">Request ID:</span>
                  <span class="modal-value">{{ selectedDetails.requestId }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Created:</span>
                  <span class="modal-value">{{ selectedDetails.creationDate }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Created By:</span>
                  <span class="modal-value">{{ selectedDetails.createdBy }}</span>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="modal-section">
                <div class="modal-section-title">Shipping Address</div>
                <div class="modal-info-row">
                  <span class="modal-label">Name:</span>
                  <span class="modal-value">{{ selectedDetails.name }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Address:</span>
                  <span class="modal-value">{{ selectedDetails.address }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">City:</span>
                  <span class="modal-value">{{ selectedDetails.city }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Region:</span>
                  <span class="modal-value">{{ selectedDetails.regionCode }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Postal Code:</span>
                  <span class="modal-value">{{ selectedDetails.postalCode }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Country:</span>
                  <span class="modal-value">{{ selectedDetails.country }}</span>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="modal-section">
                <div class="modal-section-title">Additional Information</div>
                <div class="modal-info-row">
                  <span class="modal-label">Item Number:</span>
                  <span class="modal-value">{{ selectedDetails.itemNumber }}</span>
                </div>
                <div class="modal-info-row">
                  <span class="modal-label">Tracking Number:</span>
                  <span class="modal-value">{{ selectedDetails.trackingNumber }}</span>
                </div>
                <div class="modal-label" style="margin-top: 12px; margin-bottom: 6px;">Comments:</div>
                <div class="modal-comments">{{ selectedDetails.comments || 'No comments' }}</div>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" text @click="detailsDialog = false">Close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-card-text>
    </v-card>

    <!-- Empty state when no data -->
    <v-card v-else-if="!isLoading && !isFetchingRefunds" class="mt-6 empty-state-card">
      <v-card-text class="text-center py-12">
        <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-inbox-outline</v-icon>
        <div class="text-h6 text-grey-darken-1 mb-2">No Ad Hoc Requests Found</div>
        <div class="text-body-2 text-grey">All Ad Hoc requests have been resolved.</div>
      </v-card-text>
    </v-card>

    <!-- Submit Button -->
    <div class="text-center mt-8">
      <v-btn
        color="success"
        prepend-icon="mdi-check-all"
        size="large"
        elevation="2"
        @click="submitResolvedRequests"
        :disabled="isLoading || selectedCount === 0"
      >
        Mark Selected Requests as Resolved
      </v-btn>
    </div>
  </div>
</template>

<style scoped>
/* Summary cards styling */
.summary-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 12px !important;
}
.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
}
.summary-card-selected {
  border-left: 4px solid #4caf50 !important;
  background: linear-gradient(135deg, #ffffff 0%, #f1f8f4 100%) !important;
}
.summary-card-total {
  border-left: 4px solid #1976d2 !important;
  background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%) !important;
}

/* Table header styling - matching other components */
.adhoc-table-header td {
  background: #1976d2 !important;
  color: #fff !important;
  font-weight: bold !important;
  font-size: 1.05em !important;
  letter-spacing: 0.5px !important;
  border-bottom: 2.5px solid #1565c0 !important;
  padding: 10px 8px !important;
}

/* Table row hover effects */
.adhoc-row {
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}
.adhoc-row:hover {
  background-color: #f5f5f5 !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.resolved-row {
  background-color: #e8f5e9 !important;
}
.resolved-row:hover {
  background-color: #c8e6c9 !important;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
}

/* Empty state styling */
.empty-state-cell {
  text-align: center;
  padding: 48px 0 !important;
  color: #888;
  font-size: 1.15em;
  background: #f5f5f5;
  font-style: italic;
}

.empty-state-card {
  background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
  border: 2px dashed #e0e0e0;
}

/* Modal styling */
.modal-title {
  font-size: 1.3em;
  font-weight: 600;
  color: #1976d2;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
  display: flex;
  align-items: center;
}

.modal-content {
  background: #f8fafc;
  border-radius: 8px;
  padding: 18px 16px;
}

.modal-section {
  margin-bottom: 12px;
}

.modal-section-title {
  font-weight: 700;
  color: #1976d2;
  font-size: 1.1em;
  margin-bottom: 8px;
}

.modal-info-row {
  display: flex;
  align-items: baseline;
  margin-bottom: 6px;
  gap: 8px;
}

.modal-label {
  font-weight: 600;
  color: #1976d2;
  min-width: 130px;
}

.modal-value {
  color: #333;
  font-weight: 500;
  flex: 1;
}

.modal-comments {
  background: #fff;
  border-radius: 6px;
  padding: 10px 14px;
  color: #333;
  font-size: 1em;
  white-space: pre-line;
  box-shadow: 0 1px 4px rgba(25, 118, 210, 0.08);
  border-left: 3px solid #1976d2;
}

/* Data table styling */
.adhoc-data-table {
  border-radius: 8px;
}
</style>
