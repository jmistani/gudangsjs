<template>
  <b-card no-body>
    <div class="p-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
        <div>
          <h3 class="mb-25">Arsip Mutasi Stok</h3>
          <small class="text-muted">Tanggal arsip berasal dari tanggal dibuatnya batch arsip, bukan tanggal transaksi.</small>
        </div>
        <b-button variant="outline-primary" :disabled="loading" @click="loadData">
          <feather-icon icon="RefreshCwIcon" class="mr-50" />Muat Ulang
        </b-button>
      </div>

      <b-row>
        <b-col cols="12" md="3">
          <b-form-group label="ID Barang">
            <b-form-input v-model.trim="filters.inventory_id" type="number" min="1" placeholder="Contoh: 341" @keyup.enter="applyFilters" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Diarsipkan mulai">
            <b-form-input v-model="filters.date_start" type="date" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Diarsipkan sampai">
            <b-form-input v-model="filters.date_end" type="date" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Batch arsip">
            <b-form-select v-model="filters.archive_batch_id" :options="batchOptions" />
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
        <b-col cols="12" md="5">
          <b-form-group label="Cari nama, kode, deskripsi, atau ID">
            <b-form-input v-model.trim="filters.search" placeholder="Nama, kode, ID barang, ID mutasi..." @keyup.enter="applyFilters" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Baris per halaman">
            <b-form-select v-model="perPage" :options="[10, 25, 50, 100]" @change="applyFilters" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="4" class="d-flex align-items-center mb-1">
          <b-button variant="primary" class="mr-1" :disabled="loading" @click="applyFilters">Terapkan</b-button>
          <b-button variant="outline-secondary" :disabled="loading" @click="resetFilters">Reset</b-button>
        </b-col>
      </b-row>
      <b-alert variant="danger" :show="!!errorMessage">{{ errorMessage }}</b-alert>
      <b-alert variant="warning" :show="!loading && !errorMessage && total === 0">
        Tidak ada mutasi arsip yang cocok. Semua filter yang terisi digabungkan.
        Klik Reset untuk mencari di seluruh inventaris.
      </b-alert>
      <b-alert variant="info" :show="unassignedCount > 0 && !loading">
        {{ unassignedCount }} mutasi arsip tidak memiliki archive_batch_id. Tanggal arsip untuk data tersebut tidak diketahui.
      </b-alert>
    </div>

    <b-table
      :items="rows"
      :fields="fields"
      :busy="loading"
      responsive
      striped
      show-empty
      empty-text="Tidak ada mutasi arsip sesuai filter."
      class="mb-0"
    >
      <template #table-busy>
        <div class="text-center p-2"><b-spinner small class="mr-1" /> Memuat arsip...</div>
      </template>
      <template #cell(archive_date)="data">
        <span v-if="data.item.archive_date">{{ formatDate(data.item.archive_date) }}</span>
        <span v-else class="text-muted">Tidak diketahui</span>
      </template>
      <template #cell(batch_period)="data">
        <span v-if="data.item.range_start && data.item.range_end">
          {{ data.item.range_start }} s.d. {{ data.item.range_end }}
        </span>
        <span v-else>-</span>
      </template>
      <template #cell(inventory)="data">
        <b-link v-if="data.item.inventory_id" :to="{ name: 'inventory-view', params: { id: data.item.inventory_id } }">
          {{ data.item.inventory_code || ('#' + data.item.inventory_id) }}
        </b-link>
        <span v-else>-</span>
        <div class="small text-muted">{{ data.item.inventory_name || 'Barang tidak ditemukan' }}</div>
      </template>
      <template #cell(post_date)="data">{{ formatDate(data.item.post_date) }}</template>
      <template #cell(masuk)="data"><span class="text-success">{{ data.item.amount > 0 ? formatNumber(data.item.amount) : '-' }}</span></template>
      <template #cell(keluar)="data"><span class="text-danger">{{ data.item.amount < 0 ? formatNumber(-data.item.amount) : '-' }}</span></template>
      <template #cell(price)="data">{{ formatNumber(data.item.price) }}</template>
      <template #cell(total)="data">{{ formatNumber(data.item.total) }}</template>
      <template #cell(reference)="data">
        <span v-if="data.item.second_reference_type">{{ shortType(data.item.second_reference_type) }} #{{ data.item.second_reference_id || '-' }}</span>
        <span v-else>-</span>
      </template>
      <template #cell(description)="data">{{ data.item.description || '-' }}</template>
    </b-table>

    <div class="mx-2 my-2 d-flex justify-content-between align-items-center flex-wrap">
      <span class="text-muted">Menampilkan {{ from }}–{{ to }} dari {{ total }} mutasi arsip</span>
      <b-pagination v-model="currentPage" :total-rows="total" :per-page="perPage" :disabled="loading" @change="onPageChange" />
    </div>
  </b-card>
</template>

<script>
import {
  BCard, BRow, BCol, BFormGroup, BFormInput, BFormSelect,
  BButton, BAlert, BTable, BPagination, BSpinner, BLink,
} from 'bootstrap-vue'
import axios from '@axios'

const emptyFilters = () => ({
  inventory_id: '', date_start: '', date_end: '', archive_batch_id: '', search: '',
})

export default {
  name: 'ArchivedStockMutations',
  components: {
    BCard, BRow, BCol, BFormGroup, BFormInput, BFormSelect,
    BButton, BAlert, BTable, BPagination, BSpinner, BLink,
  },
  data() {
    return {
      loading: false,
      errorMessage: '',
      rows: [],
      batches: [],
      filters: { ...emptyFilters(), inventory_id: this.$route.query.inventory_id || '' },
      currentPage: 1,
      perPage: 25,
      total: 0,
      from: 0,
      to: 0,
      unassignedCount: 0,
      fields: [
        { key: 'archive_date', label: 'Tanggal Arsip' },
        { key: 'archive_batch_id', label: 'Batch ID' },
        { key: 'batch_period', label: 'Periode Batch' },
        { key: 'inventory', label: 'Barang' },
        { key: 'post_date', label: 'Tanggal Transaksi' },
        { key: 'masuk', label: 'Masuk', class: 'text-right' },
        { key: 'keluar', label: 'Keluar', class: 'text-right' },
        { key: 'price', label: 'Harga', class: 'text-right' },
        { key: 'total', label: 'Total', class: 'text-right' },
        { key: 'reference', label: 'Referensi' },
        { key: 'description', label: 'Deskripsi' },
      ],
    }
  },
  computed: {
    batchOptions() {
      return [{ value: '', text: 'Semua batch' }].concat(this.batches.map(batch => ({
        value: String(batch.id),
        text: `#${batch.id} — ${batch.range_start} s.d. ${batch.range_end}`,
      })))
    },
  },
  watch: {
    '$route.query.inventory_id'(id) {
      this.filters.inventory_id = id || ''
      this.currentPage = 1
      this.loadData()
    },
  },
  mounted() {
    this.loadData()
  },
  methods: {
    formatNumber(value) {
      const number = Number(value)
      return Number.isFinite(number) ? new Intl.NumberFormat('id-ID').format(number) : '-'
    },
    formatDate(value) {
      if (!value) return '-'
      // Keep the date as returned by Laravel; avoid timezone conversion for display.
      return String(value).replace('T', ' ').replace(/\.\d+Z?$/, '').replace(/Z$/, '')
    },
    shortType(type) { return String(type || '').split('\\').pop() },
    applyFilters() {
      this.currentPage = 1
      this.loadData()
    },
    resetFilters() {
      this.filters = emptyFilters()
      this.currentPage = 1
      if (this.$route.query.inventory_id) {
        const query = { ...this.$route.query }
        delete query.inventory_id
        this.$router.replace({ query }).catch(() => {})
      }
      this.loadData()
    },
    onPageChange(page) {
      this.currentPage = page
      this.loadData()
    },
    async loadData() {
      this.loading = true
      this.errorMessage = ''
      try {
        const params = { page: this.currentPage, per_page: this.perPage }
        Object.keys(this.filters).forEach(key => {
          if (this.filters[key] !== '' && this.filters[key] !== null) params[key] = this.filters[key]
        })
        const { data } = await axios.get('stock-mutations/archive', { params })
        this.rows = Array.isArray(data.data) ? data.data : []
        this.total = Number(data.total) || 0
        this.from = Number(data.from) || 0
        this.to = Number(data.to) || 0
        this.batches = Array.isArray(data.batches) ? data.batches : []
        this.unassignedCount = Number(data.unassigned_count) || 0
      } catch (error) {
        this.rows = []
        this.total = 0
        this.from = 0
        this.to = 0
        this.errorMessage = (error.response && error.response.data && error.response.data.message)
          || 'Gagal mengambil arsip mutasi stok. Periksa koneksi dan endpoint API.'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
.table { white-space: nowrap; }
</style>
