<template>
  <b-card no-body>
    <b-alert :show="!!error" variant="danger" class="m-2">{{ error }}</b-alert>
    <div v-if="loadingHeader" class="p-3 text-center"><b-spinner /> Memuat barang...</div>
    <template v-if="inventory">
      <div class="m-2">
        <h3>{{ inventory.name }}</h3>
        <div class="mb-2">
          <b-button size="sm" variant="outline-primary" class="mr-1"
            :to="{ name: 'inventory-archived-mutations', query: { inventory_id: inventory.id } }">
            <feather-icon icon="ArchiveIcon" class="mr-50" /> Arsip Mutasi
          </b-button>
          <b-button size="sm" variant="outline-danger" :disabled="recalculating" @click="recalculate">
            {{ recalculating ? 'Menghitung...' : 'Rekalkulasi Stok' }}
          </b-button>
        </div>
        <b-row>
          <b-col cols="12" md="4"><strong>Stok:</strong> {{ inventory.in_stock | formatStock }} {{ inventory.unit }}</b-col>
          <b-col cols="12" md="4"><strong>Nilai stok:</strong> {{ moneyAmount(inventory.stock_value) | formatCurrencyWithPrefix }}</b-col>
          <b-col cols="12" md="4"><strong>Harga rata-rata:</strong> {{ moneyAmount(inventory.avg_value) | formatCurrencyWithPrefix }}</b-col>
        </b-row>
      </div>
      <div class="mx-2 d-flex align-items-center justify-content-between mb-1">
        <h5 class="mb-0">Mutasi Stok Aktif</h5>
        <div class="d-flex align-items-center">
          <span class="mr-1">Entries</span>
          <b-form-select v-model="perPage" :options="[10,25,50,100]" size="sm" style="width:90px" />
        </div>
      </div>
      <b-table :items="mutations" :fields="fields" :busy="loadingMutations" responsive striped show-empty
        empty-text="Belum ada mutasi aktif" class="mb-0">
        <template #table-busy><div class="text-center p-2"><b-spinner small /> Memuat mutasi...</div></template>
        <template #cell(masuk)="data"><span class="text-success">{{ Math.max(0, Number(data.item.amount)) | formatStock }}</span></template>
        <template #cell(keluar)="data"><span class="text-danger">{{ Math.max(0, -Number(data.item.amount)) | formatStock }}</span></template>
        <template #cell(post_date)="data">{{ data.item.post_date || '-' }}</template>
        <template #cell(keterangan)="data">
          <b-link v-if="isType(data.item, 'ConsumeJournal') && data.item.second_reference_id"
            :to="{name:'display-inventory-consume',params:{id:data.item.second_reference_id}}">Pemakaian</b-link>
          <b-link v-else-if="isType(data.item, 'ReceiveJournal') && data.item.second_reference_id"
            :to="{name:'display-inventory-receive',params:{id:data.item.second_reference_id}}">Penerimaan</b-link>
          <span v-else>{{ data.item.description || '-' }}</span>
        </template>
        <template #cell(giver)="data">{{ data.item.giver || '-' }}</template>
        <template #cell(receiver)="data">{{ data.item.receiver || '-' }}</template>
      </b-table>
      <div class="mx-2 mb-2 mt-2 d-flex align-items-center justify-content-between flex-wrap">
        <span class="text-muted">Showing {{ total ? (page-1)*perPage+1 : 0 }} to {{ Math.min(page*perPage,total) }} of {{ total }} entries</span>
        <b-pagination v-model="page" :total-rows="total" :per-page="perPage" />
      </div>
    </template>
  </b-card>
</template>

<script>
import { BCard, BAlert, BSpinner, BButton, BRow, BCol, BTable, BPagination, BFormSelect, BLink } from 'bootstrap-vue'
import axios from '@axios'
export default {
  name: 'InventoryView',
  components: { BCard, BAlert, BSpinner, BButton, BRow, BCol, BTable, BPagination, BFormSelect, BLink },
  data() {
    return {
      inventory: null, mutations: [], error: '', loadingHeader: false,
      loadingMutations: false, recalculating: false, page: 1, perPage: 25, total: 0,
      fields: [
        { key: 'post_date', label: 'Tanggal' },
        { key: 'masuk', label: 'Masuk' },
        { key: 'keluar', label: 'Keluar' },
        { key: 'keterangan', label: 'Keterangan' },
        { key: 'giver', label: 'Dari' },
        { key: 'receiver', label: 'Diterima' },
      ],
      requestVersion: 0,
    }
  },
  watch: {
    '$route.params.id': 'reload',
    page() { this.loadMutations() },
    perPage() { if (this.page !== 1) this.page = 1; else this.loadMutations() },
  },
  mounted() { this.reload() },
  methods: {
    moneyAmount(value) { return value && typeof value === 'object' ? value.amount : (value || 0) },
    isType(row, type) { return row.second_reference_type === `App\\Models\\${type}` },
    async reload() {
      const version = ++this.requestVersion
      const id = this.$route.params.id
      this.inventory = null; this.mutations = []; this.error = ''; this.loadingHeader = true
      try {
        const response = await axios.get(`inventories-fast/${id}`)
        if (version !== this.requestVersion) return
        this.inventory = response.data.payload
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
      const id = this.inventory.id
      this.loadingMutations = true
      try {
        const response = await axios.get(`inventories-fast/${id}/mutations`, { params: { page: this.page, per_page: this.perPage } })
        if (version !== this.requestVersion || String(id) !== String(this.$route.params.id)) return
        this.mutations = response.data.data || []
        this.total = response.data.total || 0
      } catch (e) {
        if (version === this.requestVersion) this.error = (e.response && e.response.data && e.response.data.message) || 'Gagal memuat mutasi.'
      } finally {
        if (version === this.requestVersion) this.loadingMutations = false
      }
    },
    async recalculate() {
      if (!window.confirm('Rekalkulasi stok barang ini?')) return
      this.recalculating = true
      try {
        await axios.get(`inventories/recalculateStock/${this.$route.params.id}`)
        await this.reload()
      } catch (e) {
        this.error = (e.response && e.response.data && e.response.data.message) || 'Rekalkulasi gagal.'
      } finally { this.recalculating = false }
    },
  },
}
</script>
