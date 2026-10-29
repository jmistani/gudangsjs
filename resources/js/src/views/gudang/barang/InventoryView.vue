<template>
  <b-card no-body>
    <b-alert :show="!!error" variant="danger" class="m-2">{{ error }}</b-alert>
    <div v-if="loadingHeader" class="p-3 text-center"><b-spinner small /> Memuat barang...</div>

    <template v-if="inventory">
      <div class="m-2">
        <h3>{{ inventory.name }} <small class="text-muted">({{ inventory.code }})</small></h3>
        <div class="mb-2">
          <b-button size="sm" variant="outline-primary" class="mr-1"
            :to="{name:'inventory-archived-mutations',query:{inventory_id:inventory.id}}">
            Arsip Mutasi
          </b-button>
          <b-button size="sm" variant="outline-secondary" :disabled="loadingHeader || loadingMutations" @click="reload">Refresh Data</b-button>
        </div>
        <b-row>
          <b-col cols="12" md="4"><b-card class="mb-1" bg-variant="light"><small>Stok tersimpan</small><h4>{{ number(inventory.in_stock) }} {{ inventory.unit }}</h4></b-card></b-col>
          <b-col cols="12" md="4"><b-card class="mb-1" bg-variant="light"><small>Nilai stok tersimpan</small><h4>{{ currency(moneyAmount(inventory.stock_value)) }}</h4></b-card></b-col>
          <b-col cols="12" md="4"><b-card class="mb-1" bg-variant="light"><small>Harga rata-rata terakhir</small><h4>{{ currency(moneyAmount(inventory.avg_value)) }}</h4></b-card></b-col>
        </b-row>

        <div v-if="summary" class="mt-1">
          <h5>Rekonsiliasi saldo mutasi aktif</h5>
          <b-row>
            <b-col cols="12" md="3"><strong>Total qty mutasi:</strong> {{ number(summary.mutation_qty) }}</b-col>
            <b-col cols="12" md="3"><strong>Total nilai mutasi:</strong> {{ currency(summary.mutation_value) }}</b-col>
            <b-col cols="12" md="3"><strong>Selisih qty:</strong>
              <b-badge :variant="qtyDelta === 0 ? 'success' : 'danger'">{{ number(qtyDelta) }}</b-badge>
            </b-col>
            <b-col cols="12" md="3"><strong>Selisih nilai:</strong>
              <b-badge :variant="valueDelta === 0 ? 'success' : 'danger'">{{ currency(valueDelta) }}</b-badge>
            </b-col>
          </b-row>
          <b-alert show :variant="qtyDelta === 0 && valueDelta === 0 ? 'success' : 'warning'" class="mt-1">
            {{ qtyDelta === 0 && valueDelta === 0 ? 'Saldo inventori cocok dengan mutasi aktif (termasuk archive summary).' : 'Saldo tersimpan berbeda dari total mutasi aktif. Periksa hasil rekonsiliasi sebelum melakukan pengarsipan.' }}
          </b-alert>
          <small class="text-muted">
            Penyesuaian kuantitas dan nilai ditampilkan sebagai mutasi terpisah. Mutasi nilai saja berjumlah 0, tetapi nilainya tetap dihitung.
            Archive summary juga tetap dihitung sebagai saldo aktif.
          </small>
        </div>
      </div>

      <div class="mx-2 mb-1 d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0">Mutasi Stok Aktif</h5>
        <div class="d-flex align-items-center flex-wrap">
          <b-form-checkbox v-model="adjustmentOnly" switch class="mr-2">Hanya penyesuaian</b-form-checkbox>
          <span class="mr-1">Entries</span>
          <b-form-select v-model="perPage" :options="[10,25,50,100]" size="sm" style="width:90px" />
        </div>
      </div>

      <b-table :items="mutations" :fields="fields" :busy="loadingMutations" responsive striped hover show-empty
        empty-text="Tidak ada mutasi pada filter ini" class="mb-0">
        <template #table-busy><div class="text-center p-2"><b-spinner small /> Memuat mutasi...</div></template>
        <template #cell(tanggal)="data">{{ displayDate(data.item.post_date || data.item.created_at) }}</template>
        <template #cell(jenis)="data">
          <b-badge :variant="badgeVariant(data.item)">{{ mutationType(data.item) }}</b-badge>
        </template>
        <template #cell(masuk)="data"><span class="text-success">{{ positive(data.item.amount) ? number(positive(data.item.amount)) : '-' }}</span></template>
        <template #cell(keluar)="data"><span class="text-danger">{{ negative(data.item.amount) ? number(negative(data.item.amount)) : '-' }}</span></template>
        <template #cell(harga)="data">{{ currency(data.item.price) }}</template>
        <template #cell(nilai)="data"><span :class="Number(data.item.total) < 0 ? 'text-danger' : 'text-success'">{{ currency(data.item.total) }}</span></template>
        <template #cell(keterangan)="data">
          <span v-if="isAdjustment(data.item)">{{ data.item.description }}</span>
          <span v-else-if="isSummary(data.item)">{{ data.item.description || 'Saldo Ringkasan Arsip' }}</span>
          <b-link v-else-if="isType(data.item,'ConsumeJournal') && data.item.second_reference_id"
            :to="{name:'display-inventory-consume',params:{id:data.item.second_reference_id}}">Pemakaian</b-link>
          <b-link v-else-if="isType(data.item,'ReceiveJournal') && data.item.second_reference_id"
            :to="{name:'display-inventory-receive',params:{id:data.item.second_reference_id}}">Penerimaan</b-link>
          <span v-else>{{ data.item.description || '-' }}</span>
        </template>
        <template #cell(jurnal)="data">{{ data.item.journal_code || '-' }}</template>
      </b-table>

      <div class="mx-2 mb-2 mt-2 d-flex align-items-center justify-content-between flex-wrap">
        <span class="text-muted">Menampilkan {{ total ? (page-1)*perPage+1 : 0 }}–{{ Math.min(page*perPage,total) }} dari {{ total }}</span>
        <b-pagination v-model="page" :total-rows="total" :per-page="perPage" />
      </div>
    </template>
  </b-card>
</template>

<script>
import {
  BCard, BAlert, BSpinner, BButton, BRow, BCol, BTable,
  BPagination, BFormSelect, BLink, BBadge, BFormCheckbox,
} from 'bootstrap-vue'
import axios from '@axios'

export default {
  name: 'InventoryView',
  components: {
    BCard, BAlert, BSpinner, BButton, BRow, BCol, BTable,
    BPagination, BFormSelect, BLink, BBadge, BFormCheckbox,
  },
  data() {
    return {
      inventory: null, summary: null, mutations: [], error: '',
      loadingHeader: false, loadingMutations: false,
      page: 1, perPage: 25, total: 0, adjustmentOnly: false,
      requestVersion: 0, mutationVersion: 0,
      fields: [
        { key: 'tanggal', label: 'Tanggal' },
        { key: 'jenis', label: 'Jenis' },
        { key: 'masuk', label: 'Masuk', tdClass: 'text-right' },
        { key: 'keluar', label: 'Keluar', tdClass: 'text-right' },
        { key: 'harga', label: 'Harga', tdClass: 'text-right' },
        { key: 'nilai', label: 'Total Nilai', tdClass: 'text-right' },
        { key: 'keterangan', label: 'Keterangan' },
        { key: 'jurnal', label: 'Kode Jurnal' },
      ],
    }
  },
  computed: {
    qtyDelta() { return this.summary && this.inventory ? Number(this.inventory.in_stock) - Number(this.summary.mutation_qty) : 0 },
    valueDelta() { return this.summary && this.inventory ? Number(this.moneyAmount(this.inventory.stock_value)) - Number(this.summary.mutation_value) : 0 },
  },
  watch: {
    '$route.params.id': 'reload',
    page() { this.loadMutations() },
    perPage() { if (this.page !== 1) this.page = 1; else this.loadMutations() },
    adjustmentOnly() { if (this.page !== 1) this.page = 1; else this.loadMutations() },
  },
  mounted() { this.reload() },
  methods: {
    moneyAmount(value) { return value && typeof value === 'object' ? value.amount : (value == null ? 0 : value) },
    number(value) { return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value) || 0) },
    currency(value) { return `Rp${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(this.moneyAmount(value)) || 0)}` },
    positive(value) { return Math.max(0, Number(value) || 0) },
    negative(value) { return Math.max(0, -(Number(value) || 0)) },
    isType(row, type) { return row.second_reference_type === `App\\Models\\${type}` },
    isAdjustment(row) {
      return String(row.description || '').toLowerCase().startsWith('penyesuaian stok')
        || String(row.journal_code || '').startsWith('ADJ.STOCK.')
    },
    isSummary(row) {
      return Number(row.is_archive_summary) === 1
        || String(row.description || '').toUpperCase().startsWith('ARCHIVE SUMMARY')
        || String(row.journal_code || '').startsWith('arc.mutasi.')
    },
    mutationType(row) {
      if (this.isSummary(row)) return 'Saldo Arsip'
      if (this.isAdjustment(row)) return Number(row.amount) === 0 ? 'Penyesuaian Nilai' : 'Penyesuaian Stok'
      if (Number(row.amount) > 0) return 'Masuk'
      if (Number(row.amount) < 0) return 'Keluar'
      return 'Koreksi Nilai'
    },
    badgeVariant(row) {
      if (this.isSummary(row)) return 'info'
      if (this.isAdjustment(row)) return 'warning'
      return Number(row.amount) > 0 ? 'success' : (Number(row.amount) < 0 ? 'danger' : 'secondary')
    },
    displayDate(value) {
      if (!value) return '-'
      const s = String(value)
      const match = s.match(/^(\d{4})-(\d{2})-(\d{2})/)
      return match ? `${match[3]}-${match[2]}-${match[1]}` : s
    },
    async reload() {
      const version = ++this.requestVersion
      ++this.mutationVersion
      const id = this.$route.params.id
      this.inventory = null; this.summary = null; this.mutations = []
      this.error = ''; this.loadingHeader = true
      try {
        const response = await axios.get(`inventories-fast/${id}`)
        if (version !== this.requestVersion) return
        this.inventory = response.data.payload
        this.summary = response.data.reconciliation || null
        if (this.page !== 1) this.page = 1
        else this.loadMutations()
      } catch (e) {
        if (version === this.requestVersion) this.error = (e.response && e.response.data && e.response.data.message) || 'Gagal memuat barang.'
      } finally {
        if (version === this.requestVersion) this.loadingHeader = false
      }
    },
    async loadMutations() {
      if (!this.inventory) return
      const version = this.requestVersion
      const mutationVersion = ++this.mutationVersion
      const id = this.inventory.id
      this.loadingMutations = true
      try {
        const response = await axios.get(`inventories-fast/${id}/mutations`, {
          params: { page: this.page, per_page: this.perPage, adjustment_only: this.adjustmentOnly ? 1 : 0 },
        })
        if (version !== this.requestVersion || mutationVersion !== this.mutationVersion) return
        this.mutations = response.data.data || []
        this.total = Number(response.data.total) || 0
      } catch (e) {
        if (version === this.requestVersion && mutationVersion === this.mutationVersion) {
          this.error = (e.response && e.response.data && e.response.data.message) || 'Gagal memuat mutasi.'
        }
      } finally {
        if (version === this.requestVersion && mutationVersion === this.mutationVersion) this.loadingMutations = false
      }
    },
  },
}
</script>
