<template>
  <v-app>
    <v-container class="custom-margin" fluid>
      <Banner/>

      <v-sheet elevation="3" rounded="lg">
        <v-tabs v-model="selectedTab" :items="navigationTabs">

          <template v-slot:tab="{item}">
            <v-tab
              :prepend-icon="item.icon"
              :text="item.text"
              :value="item.value"
              class="BissellBannerInverted">
            </v-tab>
          </template>

          <template v-slot:item="{item}">
            <v-tabs-window-item :value="item.value" class="pa-4">
              <div v-if="item.value == 'warehouse'">
                <WarehouseCreate/>
              </div>
              <div v-else-if="item.value == 'consumercare'">
                <ConsumerCareReview/>
              </div>
              <div v-else-if="item.value == 'accountsreceivable'">
                <ARReview/>
              </div>
                <div v-else-if="item.value == 'aropenerp'">
                    <AROpenERP/>
                </div>
                <div v-else-if="item.value == 'searchrefunds'">
                  <SearchRefunds/>
                </div>
                <div v-else-if="item.value == 'adhoc'">
                  <AdHoc/>
                </div>
            </v-tabs-window-item>
          </template>
        </v-tabs>
      </v-sheet>
    </v-container>
  </v-app>
</template>

<script setup>
  import {ref} from 'vue'
  import Banner from './components/Banner.vue'
  import WarehouseCreate from './components/Warehouse.vue'
  import ConsumerCareReview from './components/ConsumerCare.vue'
  import AdHoc from './components/AdHoc.vue'
  import ARReview from './components/AccountsReceivable.vue'
  import AROpenERP from './components/AROpenERP.vue'
  import SearchRefunds from './components/Search.vue'
  
  /***** UI STATES *****/
  const selectedTab = ref("warehouse");
  const navigationTabs = ref([]);
  // const navigationTabs = [
  //   {
  //     icon: "mdi-mail",
  //     text: "Warehouse",
  //     value: "warehouse"
  //   },
  //   {
  //     icon: "mdi-phone",
  //     text: "Consumer Care Review",
  //     value: "consumercare"
  //   },
  //   {
  //     icon: "mdi-calculator",
  //     text: "Accounts Receivable Review",
  //     value: "accountsreceivable"
  //   },
  //   {
  //     icon: "mdi-exclamation-thick",
  //     text: "A/R Open ERP Submissions",
  //     value: "aropenerp"
  //   },
  //   {
  //     icon: "mdi-magnify",
  //     text: "Search Refunds",
  //     value: "searchrefunds"
  //   }
  // ]

  let globalConfigPromise;
  ORACLE_SERVICE_CLOUD.extension_loader.load("Refunds Dashboard", "1.0").then(function(extensionLibrary) {
    extensionLibrary.getGlobalContext().then(function(globalContext) {
      globalConfigPromise = globalConfigPromise || globalContext.invokeAction("GetRefundsDashboardConfiguration");
      globalConfigPromise.then(function(globalConfig) {
        console.log('GetRefundsDashboardConfiguration result:', globalConfig);
        if (globalConfig && globalConfig.result && globalConfig.result[0]) {
          const cfg = globalConfig.result[0];
          console.log('ShowWarehouseTab:', cfg.ShowWarehouseTab);
          console.log('ShowConsumerCareTab:', cfg.ShowConsumerCareTab);
          console.log('ShowARTabs:', cfg.ShowARTabs);
          console.log('ShowSearchTab:', cfg.ShowSearchTab);
          console.log('ShowAllTabs:', cfg.ShowAllTabs);

          let firstTab = null;
          // If ShowAllTabs is enabled, show all tabs regardless of individual settings
          if(cfg.ShowAllTabs){
            navigationTabs.value.push({icon: "mdi-mail", text: "Warehouse", value: "warehouse"});
            navigationTabs.value.push({icon: "mdi-phone", text: "Consumer Care Review", value: "consumercare"});
            navigationTabs.value.push({icon: "mdi-help-rhombus", text: "Ad Hoc Requests", value: "adhoc"});
            navigationTabs.value.push({icon: "mdi-calculator", text: "Accounts Receivable Review", value: "accountsreceivable"});
            navigationTabs.value.push({icon: "mdi-exclamation-thick", text: "A/R Open ERP Submissions", value: "aropenerp"});
            navigationTabs.value.push({icon: "mdi-magnify", text: "Search Refunds", value: "searchrefunds"});
            firstTab = 'warehouse';
          } else {
            if(cfg.ShowWarehouseTab){
              navigationTabs.value.push({icon: "mdi-mail", text: "Warehouse", value: "warehouse"});
              if (!firstTab) firstTab = 'warehouse';
            }
            if(cfg.ShowConsumerCareTab){
              navigationTabs.value.push({icon: "mdi-phone", text: "Consumer Care Review", value: "consumercare"});
              navigationTabs.value.push({icon: "mdi-help-rhombus", text: "Ad Hoc Requests", value: "adhoc"});
              if (!firstTab) firstTab = 'consumercare';
            }
            if(cfg.ShowARTabs){
              navigationTabs.value.push({icon: "mdi-calculator", text: "Accounts Receivable Review", value: "accountsreceivable"});
              navigationTabs.value.push({icon: "mdi-exclamation-thick", text: "A/R Open ERP Submissions", value: "aropenerp"});
              if (!firstTab) firstTab = 'accountsreceivable';
            }
            if(cfg.ShowSearchTab){
              navigationTabs.value.push({icon: "mdi-magnify", text: "Search Refunds", value: "searchrefunds"});
              if (!firstTab) firstTab = 'searchrefunds';
            }
          }
          if (firstTab) {
            selectedTab.value = firstTab;
          }
        } else {
          console.warn('No valid config found in GetRefundsDashboardConfiguration result:', globalConfig);
        }
      });
    });
  });

</script>

<style>
  .BissellBanner {
    background-color: rgb(var(--v-theme-bissellBackground)) !important;
    color: white !important;
  }
  .BissellBannerInverted {
    background-color: white !important;
    color: rgb(var(--v-theme-bissellBackground)) !important; 
  }
  .BissellButton {
    background-color: rgb(var(--v-theme-bissellButton)) !important;
    color: white !important;
  }
  .BissellButtonCancel {
    background-color: white !important;
    color: rgb(var(--v-theme-bissellButtonRed)) !important;
  }
  .BissellCard {
    background-color: white !important;
  }
  .BissellSubTable {
    background-color: lightgrey !important;
  }
  .BUIExtensionRow {
    margin-bottom: 20px !important;
  }
  .custom-margin {
    margin-left: inherit !important;
    margin-right: inherit !important;
  }
  .PrintAllButton {
    float: right;
    margin-bottom: 20px !important;
  }
  .selected-row {
    background-color: #e3f2fd; /* Light blue */
  }
  .BissellBannerDetails {
    background-color: rgb(var(--v-theme-bissellBackground)) !important;
    color: white !important;
    text-align: center;
    font-weight: bold;
    padding-top:5px;
    padding-bottom:5px;
  }
  .row-approved {
    background-color: #e8f5e9; /* Light green */
  }

  .row-denied {
    background-color: #ffebee; /* Light red */
  }
  .overlay-center {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .refundSuccess {
    background-color:lightgreen;
    padding:10px;
    font-weight:bold;
    border-radius:10px;
  }
  .refundError {
    background-color: lightcoral;
    color:darkblue;
    font-weight:bold;
    border-radius:10px;
    padding:10px;
  }

</style>