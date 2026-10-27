<template>
  <b-card no-body>
    <b-alert :show="!!errorMessage" variant="danger" class="m-2">
      {{ errorMessage }}
      <b-link :to="{ name: 'inventory-list' }" class="alert-link">Kembali ke Daftar Barang</b-link>
    </b-alert>

    <div v-if="isFetching" class="text-center p-3">
      <b-spinner label="Loading..." />
      <div class="mt-1">Memuat data barang...</div>
    </div>

    <template v-if="inventoryData">
      <div class="m-2">
        <b-row class="card-inventory-view-title mb-1">
          <b-col cols="12">
            <h3>{{ inventoryData.name || '-' }}</h3>
            <b-button
              size="sm"
              variant="outline-danger"
              pill
              class="mb-1"
              :disabled="isFetching"
              @click="recalculate"
            >
              Rekalkulasi Stok
            </b-button>
            <b-card-text class="font-small-3">
              Nilai per {{ inventoryData.unit || '-' }}:
              {{ moneyAmount(inventoryData.avg_value) | formatCurrencyWithPrefix }}
            </b-card-text>
            <b-card-text class="font-small-3 text-success mb-1">
              <b-badge
                v-for="(tag, index) in parsedTags"
                :key="`${tag}-${index}`"
                variant="light-secondary"
                class="mr-25"
              >
                {{ tag }}
              </b-badge>
            </b-card-text>
          </b-col>
        </b-row>

        <b-row class="align-items-center">
          <b-col cols="12" md="3" class="mb-1">
            <b-media no-body>
              <b-media-aside class="mr-2">
                <b-avatar size="48" variant="success">
                  <feather-icon size="24" icon="BoxIcon" />
                </b-avatar>
              </b-media-aside>
              <b-media-body class="my-auto">
                <h4 class="font-weight-bolder mb-0">
                  {{ inventoryData.in_stock | formatStock }} {{ inventoryData.unit || '' }}
                </h4>
                <b-card-text class="font-small-3 mb-0">Jumlah stok</b-card-text>
              </b-media-body>
            </b-media>
          </b-col>
          <b-col cols="12" md="3" class="mb-1">
            <b-media no-body>
              <b-media-aside class="mr-2">
                <b-avatar size="48" variant="danger">
                  <feather-icon size="24" icon="DollarSignIcon" />
                </b-avatar>
              </b-media-aside>
              <b-media-body class="my-auto">
                <h4 class="font-weight-bolder mb-0">
                  {{ moneyAmount(inventoryData.stock_value) | formatCurrencyWithPrefix }}
                </h4>
                <b-card-text class="font-small-3 mb-0">Nilai Stok</b-card-text>
              </b-media-body>
            </b-media>
          </b-col>
          <b-col cols="12" md="6" class="d-flex justify-content-md-end align-items-center mb-1">
            <label class="mb-0 mr-1">Entries:</label>
            <v-select
              v-model="perPage"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="perPageOptions"
              :clearable="false"
              class="per-page-selector"
            />
          </b-col>
        </b-row>
      </div>

      <b-table
        ref="refMutationListTable"
        :items="mutations"
        :fields="tableColumns"
        :per-page="Number(perPage)"
        :current-page="currentPage"
        :sort-by.sync="sortBy"
        :sort-desc.sync="isSortDirDesc"
        primary-key="id"
        responsive
        show-empty
        empty-text="Belum ada mutasi stok"
        class="position-relative mt-0"
      >
        <template #head(inventoryStatus)>
          <feather-icon icon="TrendingUpIcon" class="mx-auto" />
        </template>

        <template #cell(tanggal)="data">
          {{ data.item.journalhead && data.item.journalhead.post_date
            ? data.item.journalhead.post_date : '-' }}
        </template>

        <template #cell(masuk)="data">
          <span :class="`text-${resolveMutationVariant(data.item.amount)}`">
            {{ resolveMutationIn(data.item.amount) | formatStock }}
          </span>
        </template>

        <template #cell(keluar)="data">
          <span :class="`text-${resolveMutationVariant(data.item.amount)}`">
            {{ resolveMutationOut(data.item.amount) | formatStock }}
          </span>
        </template>

        <template #cell(second_reference_type)="data">
          <b-link
            v-if="data.item.second_reference_type === 'App\\Models\\ConsumeJournal' && data.item.second_reference && data.item.second_reference.id"
            :to="{ name: 'display-inventory-consume', params: { id: data.item.second_reference.id } }"
            class="font-weight-bold text-danger"
          >
            Pemakaian
          </b-link>
          <b-link
            v-else-if="data.item.second_reference_type === 'App\\Models\\ReceiveJournal' && data.item.second_reference && data.item.second_reference.id"
            :to="{ name: 'display-inventory-receive', params: { id: data.item.second_reference.id } }"
            class="font-weight-bold text-primary"
          >
            Penerimaan
          </b-link>
          <span v-else>-</span>
        </template>

        <template #cell(giver)="data">
          {{ data.item.second_reference ? (data.item.second_reference.giver || '-') : '-' }}
        </template>

        <template #cell(receiver)="data">
          {{ data.item.second_reference ? (data.item.second_reference.receiver || '-') : '-' }}
        </template>
      </b-table>

      <div class="mx-2 mb-2">
        <b-row>
          <b-col cols="12" sm="6" class="d-flex align-items-center justify-content-center justify-content-sm-start">
            <span class="text-muted">
              Showing {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} entries
            </span>
          </b-col>
          <b-col cols="12" sm="6" class="d-flex align-items-center justify-content-center justify-content-sm-end">
            <b-pagination
              v-model="currentPage"
              :total-rows="mutations.length"
              :per-page="Number(perPage)"
              first-number
              last-number
              class="mb-0 mt-1 mt-sm-0"
            >
              <template #prev-text><feather-icon icon="ChevronLeftIcon" size="18" /></template>
              <template #next-text><feather-icon icon="ChevronRightIcon" size="18" /></template>
            </b-pagination>
          </b-col>
        </b-row>
      </div>
    </template>
  </b-card>
</template>

<script>
import {
  BCard, BAlert, BLink, BRow, BCol, BButton, BCardText, BBadge,
  BMedia, BMediaAside, BMediaBody, BAvatar, BSpinner, BTable, BPagination,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import gudangStoreModule from '../gudangStoreModule'
import useInventoryView from './useInventoryView'

export default {
  name: 'InventoryView',
  components: {
    BCard, BAlert, BLink, BRow, BCol, BButton, BCardText, BBadge,
    BMedia, BMediaAside, BMediaBody, BAvatar, BSpinner, BTable, BPagination,
    vSelect,
  },
  setup() {
    const moduleName = 'app-inventory'
    if (!store.hasModule(moduleName)) {
      store.registerModule(moduleName, gudangStoreModule)
    }
    onUnmounted(() => {
      // Preserve this project's existing dynamic Vuex module lifecycle.
      if (store.hasModule(moduleName)) store.unregisterModule(moduleName)
    })

    const inventoryData = ref(null)
    const isFetching = ref(false)
    const errorMessage = ref('')

    const {
      tableColumns, perPage, currentPage, perPageOptions, sortBy,
      isSortDirDesc, refMutationListTable, resolveMutationIn,
      resolveMutationOut, resolveMutationVariant,
    } = useInventoryView()

    return {
      inventoryData, isFetching, errorMessage,
      tableColumns, perPage, currentPage, perPageOptions, sortBy,
      isSortDirDesc, refMutationListTable, resolveMutationIn,
      resolveMutationOut, resolveMutationVariant,
    }
  },
  computed: {
    mutations() {
      return this.inventoryData && Array.isArray(this.inventoryData.stock_mutations)
        ? this.inventoryData.stock_mutations : []
    },
    parsedTags() {
      const tags = this.inventoryData && this.inventoryData.tags
      if (Array.isArray(tags)) return tags
      if (!tags || typeof tags !== 'string') return []
      try {
        const parsed = JSON.parse(tags)
        return Array.isArray(parsed) ? parsed : []
      } catch (error) {
        return []
      }
    },
    dataMeta() {
      const total = this.mutations.length
      const pageSize = Math.max(1, Number(this.perPage) || 1)
      const page = Math.max(1, Number(this.currentPage) || 1)
      const from = total ? ((page - 1) * pageSize) + 1 : 0
      return { from, to: Math.min(page * pageSize, total), of: total }
    },
  },
  watch: {
    '$route.params.id': function onInventoryIdChanged(id) {
      this.loadInventory(id)
    },
    perPage() {
      this.currentPage = 1
    },
  },
  mounted() {
    this.loadInventory(this.$route.params.id)
  },
  methods: {
    moneyAmount(value) {
      if (value && typeof value === 'object') return value.amount == null ? 0 : value.amount
      return value == null ? 0 : value
    },
    async loadInventory(id) {
      if (id === null || id === undefined || String(id).trim() === '') {
        this.inventoryData = null
        this.errorMessage = 'ID barang tidak ditemukan.'
        return
      }
      this.isFetching = true
      this.errorMessage = ''
      this.inventoryData = null
      try {
        const response = await store.dispatch('app-inventory/fetchInventory', { id })
        // Ignore response from a previous route if user navigated quickly.
        if (String(this.$route.params.id) !== String(id)) return
        this.inventoryData = response.data && response.data.payload
        if (!this.inventoryData) this.errorMessage = 'Data barang tidak ditemukan.'
        this.currentPage = 1
      } catch (error) {
        if (String(this.$route.params.id) !== String(id)) return
        const status = error.response && error.response.status
        this.errorMessage = status === 404
          ? 'Data barang tidak ditemukan (404).'
          : (error.response && error.response.data && error.response.data.message) || 'Gagal memuat data barang.'
        console.error('Inventory view fetch failed:', error)
      } finally {
        if (String(this.$route.params.id) === String(id)) this.isFetching = false
      }
    },
    async recalculate() {
      const id = this.$route.params.id
      this.isFetching = true
      this.errorMessage = ''
      try {
        await store.dispatch('app-inventory/recalculateStock', { id })
        // Refetch the full inventory record; recalculation endpoints may
        // return only a status or summary instead of the full relationship tree.
        await this.loadInventory(id)
      } catch (error) {
        this.errorMessage = (error.response && error.response.data && error.response.data.message)
          || 'Rekalkulasi stok gagal.'
        console.error('Recalculate stock failed:', error)
      } finally {
        this.isFetching = false
      }
    },
  },
}
</script>

<style scoped>
.per-page-selector { width: 90px; }
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
