<template>
  <b-card no-body>
    <div class="p-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
        <div>
          <h3 class="mb-25">Arsip Mutasi Stok</h3>
          <small class="text-muted">Tanggal arsip berasal dari waktu proses pengarsipan (archived_at), terpisah dari tanggal transaksi.</small>
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
        <b-col cols="12" md="3">
          <b-form-group label="Tanggal transaksi dari (DD MM YYYY)">
            <b-form-input v-model.trim="filters.transaction_date_start" placeholder="01 01 2026" maxlength="10" @keyup.enter="applyFilters" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Tanggal transaksi sampai (DD MM YYYY)">
            <b-form-input v-model.trim="filters.transaction_date_end" placeholder="31 12 2026" maxlength="10" @keyup.enter="applyFilters" />
          </b-form-group>
        </b-col>
        <b-col cols="12" md="3">
          <b-form-group label="Cari barang / deskripsi / kode">
            <b-form-input v-model.trim="filters.search" placeholder="Cari..." @keyup.enter="applyFilters" />
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
      <small class="text-muted">Klik judul kolom untuk mengurutkan hasil filter. Urutan berlaku untuk seluruh halaman.</small>
      <b-alert variant="info" :show="unassignedCount > 0 && !loading">
        {{ unassignedCount }} mutasi arsip tidak memiliki archive_batch_id. Tanggal arsip untuk data tersebut mungkin belum tersedia.
      </b-alert>
    </div>

    <b-table
      :items="rows"
      :fields="fields"
      :busy="loading"
      :sort-by.sync="sortBy"
      :sort-desc.sync="sortDesc"
      :no-local-sorting="true"
      @sort-changed="onSortChanged"
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
  transaction_date_start: '', transaction_date_end: '',
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
      sortBy: 'archive_date',
      sortDesc: true,
      total: 0,
      from: 0,
      to: 0,
      unassignedCount: 0,
      fields: [
        { key: 'archive_date', sortable: true, label: 'Tanggal Arsip' },
        { key: 'archive_batch_id', sortable: true, label: 'Batch ID' },
        { key: 'batch_period', sortable: true, label: 'Periode Batch' },
        { key: 'inventory', sortable: true, label: 'Barang' },
        { key: 'post_date', sortable: true, label: 'Tanggal Transaksi' },
        { key: 'masuk', sortable: true, label: 'Masuk', class: 'text-right' },
        { key: 'keluar', sortable: true, label: 'Keluar', class: 'text-right' },
        { key: 'price', sortable: true, label: 'Harga', class: 'text-right' },
        { key: 'total', sortable: true, label: 'Total', class: 'text-right' },
        { key: 'reference', sortable: true, label: 'Referensi' },
        { key: 'description', sortable: true, label: 'Deskripsi' },
      ],
    }
  },
  computed: {
    batchOptions() {
      return [{ value: '', text: 'Semua batch' }].concat(this.batches.map(batch => ({
        value: String(batch.id),
        text: `#${batch.id} — ${batch.created_at ? this.formatDate(batch.created_at) : 'tanggal belum tersedia'}`,
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
      // Only extract date characters; do not convert timestamps between timezones.
      const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/)
      return match ? `${match[3]} ${match[2]} ${match[1]}` : String(value)
    },
    parseTransactionDate(input, label) {
      if (!input) return ''
      const match = String(input).trim().match(/^(\d{2})[ \/-](\d{2})[ \/-](\d{4})$/)
      if (!match) throw new Error(`${label} harus berformat DD MM YYYY, contoh 27 10 2026.`)
      const day = Number(match[1])
      const month = Number(match[2])
      const year = Number(match[3])
      const date = new Date(Date.UTC(year, month - 1, day))
      if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) {
        throw new Error(`${label} tidak valid.`)
      }
      return `${match[3]}-${match[2]}-${match[1]}`
    },
    shortType(type) { return String(type || '').split('\\').pop() },
    applyFilters() {
      this.currentPage = 1
      this.loadData()
    },
    resetFilters() {
      this.filters = emptyFilters()
      this.currentPage = 1
      // The URL filter should also be cleared so a future route change is predictable.
      if (this.$route.query.inventory_id) {
        const query = { ...this.$route.query }
        delete query.inventory_id
        this.$router.replace({ query }).catch(() => {})
      }
      this.loadData()
    },
    onSortChanged(ctx) {
      // Sorting is performed by Laravel across all archive rows (not just this page).
      this.sortBy = ctx.sortBy || 'archive_date'
      this.sortDesc = !!ctx.sortDesc
      this.currentPage = 1
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
        const params = { page: this.currentPage, per_page: this.perPage, sort_by: this.sortBy, sort_desc: this.sortDesc ? 1 : 0 }
        Object.keys(this.filters).forEach(key => {
          if (['transaction_date_start', 'transaction_date_end'].includes(key)) return
          if (this.filters[key] !== '' && this.filters[key] !== null) params[key] = this.filters[key]
        })
        const first = this.parseTransactionDate(this.filters.transaction_date_start, 'Tanggal transaksi dari')
        const last = this.parseTransactionDate(this.filters.transaction_date_end, 'Tanggal transaksi sampai')
        if (first && last && first > last) throw new Error('Tanggal transaksi sampai tidak boleh lebih awal dari tanggal mulai.')
        if (first) params.transaction_date_start = first
        if (last) params.transaction_date_end = last
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
          || error.message || 'Gagal mengambil arsip mutasi stok. Periksa koneksi dan endpoint API.'
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
