<template>

  <!-- Table Container Card -->
  <b-card
    no-body
  >
    <b-alert
      variant="danger"
      :show="refSupplierData === undefined"
    >
      <h4 class="alert-heading">
        Error fetching supplier data 
      </h4>
      <div class="alert-body">
        No supplier found with this id. Check
        <b-link
          class="alert-link"
          :to="{ name: 'supplier-list'}"
        >
          Supplier List
        </b-link>
        for other ticket.
      </div>
    </b-alert>
    <div class="m-2">
      <b-row class="card-supplier-view-title mb-0 pb-0" style="background: url('/images/dimension.png')');">
        <b-col v-if="!refIsFetching" cols="12" class="card-supplier-view-title mb-0 mt-0 pb-0 pt-0">

            <h3 class="text-primary mb-2">{{refSupplierData.name}}</h3>
              <!-- <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="outline-danger"
                pill
                class="mb-1"
                @click="recalculate()"
              >
                Rekalkulasi Total Tiket
              </b-button> -->
            <b-card-text v-if="refSupplierData.type == 'kendaraan'" class="font-small-3">
              No Polisi:
            </b-card-text>
            <b-media no-body v-if="!refIsFetching">
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
                <h4 v-if="dataMeta.total" class="font-weight-bolder mb-0">
                  {{ dataMeta.total | formatCurrencyWithPrefix }}
                </h4>
                <h4 v-else class="text-danger"> data kosong </h4>
                <b-card-text v-if="dataMeta.total" class="font-small-3 mb-0">
                  Total
                </b-card-text>
              </b-media-body>
              <b-img
                class="ticket-picture mr-5"
                :src="require('@/assets/images/binders.svg')"
              />
            </b-media>
          </b-col>
      </b-row>      
    </div>

    <div class="m-1">
      <b-row class="card-supplier-view-title mb-0 pb-0">
        <b-col v-if="!refIsFetching" cols="12" class="card-supplier-view-title mb-0 mt-0 pb-0 pt-0">
      <b-table
        v-if="!refIsFetching"
        ref="refMutationListTable"
        :items="refSupplierData.stock_mutations"
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

        <template #head(supplierStatus)>
          <feather-icon
            icon="TrendingUpIcon"
            class="mx-auto"
          />
        </template>

        <template #cell(created_at)="data">
            {{ data.item.created_at | formatDateWithTime }}
        </template>

        <template #cell(barang)="data">
            {{ data.item.stockable.name }}
        </template>

        <template #cell(harga)="data">
            {{ data.item.price.amount | formatCurrencyWithPrefix }}
        </template>

        <template #cell(jumlah)="data">
            <span :class="`text-${resolveMutationVariant(data.item.amount)}`">{{ resolveMutationOut(data.item.amount) | formatStock }}</span>
        </template>

        <template #cell(subtotal)="data">
            {{ data.item.price.amount * data.item.amount * -1| formatCurrencyWithPrefix }}
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
              :total-rows="totalMutations"
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
    </b-col>
    </b-row>
    </div>
  </b-card>
</template>

<script>
import {
  BCardText,
  BCardHeader, BCardTitle, BCardBody, BAlert, BImg,
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
import useSupplierView from './useSupplierView'
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
    BImg,
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
      this.refIsFetching = true

      store.dispatch('app-supplier/recalculateSupplier', { id: router.currentRoute.params.id })
        .then(response => { 
          this.refSupplierData = response.data.payload 
          this.refIsFetching = false
        })
      }
  },
  mounted() {
  },
  setup() {

    const INVENTORY_APP_STORE_MODULE_NAME = 'app-supplier'

    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)
    })



    const {
      tableColumns,
      perPage,
      currentPage,
      totalMutations,
      dataMeta,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      refMutationListTable,
      refSupplierData,
      refetchData,
      refIsFetching,

      resolveMutationIn,
      resolveMutationOut,
      resolveMutationVariant
    } = useSupplierView(router.currentRoute.params.id)

    return {
      tableColumns,
      perPage,
      currentPage,
      totalMutations,
      dataMeta,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      refMutationListTable,
      refSupplierData,


      refetchData,

      avatarText,
      resolveMutationIn,
      resolveMutationOut,
      resolveMutationVariant,
      refIsFetching
    }
  },
}
</script>

<style lang="scss" scoped>
.ticket-picture {
  width:80px;
}
.per-page-selector {
  width: 90px;
}

.supplier-filter-select {
  min-width: 190px;

  ::v-deep .vs__selected-options {
    flex-wrap: nowrap;
  }

  ::v-deep .vs__selected {
    width: 100px;
  }
}
.radio-supplier-category-filter {
  margin-right: 10px;
  margin-left: 10px;
}
.card-supplier-view-title  {
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

.ticket-view-card {
  border-top: 1px solid  #5D6975;
  border-bottom: 1px solid  #5D6975;
  color: #5D6975;
  font-size: 2.4em;
  line-height: 1.4em;
  font-weight: normal;
  text-align: center;
  margin: 0 0 20px 0;
}
.crop {
    width: 200px;
    height: 150px;
    overflow: hidden;
}

.crop img {
    width: 400px;
    height: 300px;
    margin: -75px 0 0 -100px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
