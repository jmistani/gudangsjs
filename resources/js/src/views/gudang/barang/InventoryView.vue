<template>

  <!-- Table Container Card -->
  <b-card
    no-body
  >
    <b-alert
      variant="danger"
      :show="inventoryData === undefined"
    >
      <h4 class="alert-heading">
        Error fetching inventory data 
      </h4>
      <div class="alert-body">
        No inventory found with this id. Check
        <b-link
          class="alert-link"
          :to="{ name: 'inventory-list'}"
        >
          Inventory List
        </b-link>
        for other inventory.
      </div>
    </b-alert>
    <div class="m-2">
      <b-row class="card-inventory-view-title mb-0 pb-0">
        <b-col v-if="!isFetching" cols="12" class="card-inventory-view-title mb-0 mt-0 pb-0 pt-0">
            <h3>{{inventoryData.name}}</h3>
              <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="outline-danger"
                pill
                class="mb-1"
                @click="recalculate()"
              >
                Rekalkulasi Stok
              </b-button>
            <b-card-text class="font-small-3">
              Nilai per {{inventoryData.unit}}: {{ inventoryData.avg_value.amount | formatCurrencyWithPrefix }}
            </b-card-text>
            <b-card-text class="font-small-3 text-success mb-1">
              <b-badge v-for="(tag,index) in JSON.parse(inventoryData.tags)" :key="index" variant="light-secondary">
                {{ tag }}
              </b-badge>
            </b-card-text>
            </b-col>
      </b-row>
      <b-row class="d-flex card-inventory-view-title">
        <b-col
          cols="3"
          class="d-flex justify-content-start card-inventory-view-title"
          order="1"
        >
          <b-spinner
            v-if="isFetching"
             label="Loading..."></b-spinner>
          <b-media no-body v-if="!isFetching">
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="success"
              >
                <feather-icon
                  size="24"
                  icon="BoxIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class="font-weight-bolder mb-0">
                {{ inventoryData.in_stock | formatStock }} {{ inventoryData.unit }}
              </h4>
              <b-card-text class="font-small-3 mb-0">
                Jumlah stok
              </b-card-text>
            </b-media-body>
          </b-media>
        </b-col>
        <b-col
          cols="3"
          class="d-flex justify-content-start card-inventory-view-title"
          order="2"
        >
          <b-media no-body v-if="!isFetching">
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="danger"
              >
                <feather-icon
                  size="24"
                  icon="DollarSignIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class="font-weight-bolder mb-0">
                {{ inventoryData.stock_value.amount | formatCurrencyWithPrefix }}
              </h4>
              <b-card-text class="font-small-3 mb-0">
                Nilai Stok
              </b-card-text>
            </b-media-body>
         </b-media>
        </b-col>
        <!-- Per Page -->
        <b-col
          cols="8"
          class="d-flex align-self-end justify-content-end card-inventory-view-title"
          order="3"
        >
          <label>Entries : </label>
          <v-select
            v-model="perPage"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="perPageOptions"
            :clearable="false"
            class="per-page-selector d-inline-block ml-0 mr-1 "
          />
        </b-col>


      </b-row>

    </div>

    <b-table
      v-if="!isFetching"
      ref="refMutationListTable"
      :items="inventoryData.stock_mutations"
      responsive
      :fields="tableColumns"
      primary-key="id"
      :sort-by.sync="sortBy"
      show-empty
      empty-text="No matching records found"
      :sort-desc.sync="isSortDirDesc"
      class="position-relative mt-0"
    >
      <template v-slot:table-busy>
          <spinner message="Loading ..."></spinner>
      </template>

      <template #head(inventoryStatus)>
        <feather-icon
          icon="TrendingUpIcon"
          class="mx-auto"
        />
      </template>

      <template #cell(tanggal)="data">
          {{data.item.journalhead.post_date}}
      </template>

      <template #cell(masuk)="data">
          <span :class="`text-${resolveMutationVariant(data.item.amount)}`">{{ resolveMutationIn(data.item.amount) | formatStock }}</span>
      </template>

      <template #cell(keluar)="data">
          <span :class="`text-${resolveMutationVariant(data.item.amount)}`">{{ resolveMutationOut(data.item.amount) | formatStock }}</span>
      </template>

      <template #cell(second_reference_type)="data">
        <div v-if="data.item.second_reference_type == 'App\\Models\\ConsumeJournal'">
          <b-link
            :to="{ name: 'display-inventory-consume', params: { id: data.item.second_reference.id }  }"
            class="font-weight-bold"
          >
            <span class="text-danger">Pemakaian</span>
          </b-link>
        </div>
        <div v-if="data.item.second_reference_type == 'App\\Models\\ReceiveJournal'">
          <b-link
            :to="{ name: 'display-inventory-receive', params: { id: data.item.second_reference.id }  }"
            class="font-weight-bold"
          >
            <span class="text-primary">Penerimaan</span>
          </b-link>
        </div>
      </template>

      <template #cell(giver)="data">
          {{ data.item.second_reference.giver }}
      </template>

      <template #cell(receiver)="data">
          {{ data.item.second_reference.receiver }}
      </template>

    </b-table>
    <div class="mx-2 mb-2">
      <b-row>

        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">Showing {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} entries</span>
        </b-col>
        <!-- Pagination -->
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPage"
            :total-rows="totalInventories"
            :per-page="perPage"
            first-number
            last-number
            class="mb-0 mt-1 mt-sm-0"
            prev-class="prev-item"
            next-class="next-item"
          >
            <template #prev-text>
              <feather-icon
                icon="ChevronLeftIcon"
                size="18"
              />
            </template>
            <template #next-text>
              <feather-icon
                icon="ChevronRightIcon"
                size="18"
              />
            </template>
          </b-pagination>

        </b-col>

      </b-row>
    </div>
  </b-card>
</template>

<script>
import {
  BCardText,
  BCardHeader, BCardTitle, BCardBody, BAlert,
  BForm, BFormGroup, BCard, BRow, BCol, BFormInput, BButton, BTable, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BTooltip,VBToggle,BPopover,
  BFormRadio, BMedia, BMediaBody, BMediaAside, BAvatar, BSpinner
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import router from '@/router'
import vSelect from 'vue-select'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import gudangStoreModule from '../gudangStoreModule'
import useInventoryView from './useInventoryView'
import StatisticCardVertical from '@core/components/statistics-cards/StatisticCardVertical.vue'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    StatisticCardVertical,
    BSpinner,
    BCardText,
    BCardHeader,
    BCardTitle,
    BCardBody,
    BMediaBody,
    BMediaAside,
    BAlert,
    BForm,
    BFormGroup,
    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BMedia,
    BAvatar,
    BLink,
    BBadge,
    BDropdown,
    BDropdownItem,
    BPagination,
    BTooltip,
    BFormRadio,
    BPopover,
    BAvatar,

    vSelect,
  },
  directives: {

    'b-toggle': VBToggle,
    Ripple,
  },
  methods: {
    recalculate() {
      this.isFetching = true

      store.dispatch('app-inventory/recalculateStock', { id: router.currentRoute.params.id })
        .then(response => { 
          this.inventoryData = response.data.payload 
          this.isFetching = false
        })
      }
  },
  mounted() {
  },
  setup() {

    const INVENTORY_APP_STORE_MODULE_NAME = 'app-inventory'

    const inventoryData = ref(null)
    const isFetching = ref(true)
    const mutationData = ref(null)

    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)
    })


    store.dispatch('app-inventory/fetchInventory', { id: router.currentRoute.params.id })
      .then(response => { 
        inventoryData.value = response.data.payload 
        isFetching.value = false
      })
      .catch(error => {
        if (error.response.code === 404) {
          inventoryData.value = undefined
        }
      })


    const {
      inventoryID,
      tableColumns,
      perPage,
      currentPage,
      totalInventories,
      dataMeta,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      refMutationListTable,

      refetchData,

      resolveMutationIn,
      resolveMutationOut,
      resolveMutationVariant,
    } = useInventoryView()

    return {
      tableColumns,
      perPage,
      currentPage,
      totalInventories,
      dataMeta,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      refMutationListTable,


      refetchData,

      avatarText,
      resolveMutationIn,
      resolveMutationOut,
      resolveMutationVariant,

      inventoryData,
      mutationData,
      isFetching
    }
  },
}
</script>

<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}

.inventory-filter-select {
  min-width: 190px;

  ::v-deep .vs__selected-options {
    flex-wrap: nowrap;
  }

  ::v-deep .vs__selected {
    width: 100px;
  }
}
.radio-inventory-category-filter {
  margin-right: 10px;
  margin-left: 10px;
}
.card-inventory-view-title  {
  margin-top: 0 !important;
  margin-bottom: 0 !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}
.bd-highlight {
    background-color: rgba(86,61,124,0.15);
    border: 1px solid rgba(86,61,124,0.15);
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
