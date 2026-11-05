<template>
  <section class="manage-receive">
    <b-card no-body>
      <div class="header">
        <div><h4 class="mb-25">Kelola Terima Barang</h4><p class="text-muted mb-0">Daftar penerimaan stok dan non-stok.</p></div>
        <b-button variant="outline-primary" :to="{ name: 'inventory-receive-add' }">+ Terima Barang</b-button>
      </div>
      <div class="toolbar">
        <b-form-input v-model.trim="query" placeholder="Cari kode, vendor, atau penerima..." @keyup.enter="fetchRecords(1)" />
        <b-button variant="primary" @click="fetchRecords(1)">Cari</b-button>
        <b-button variant="outline-secondary" @click="fetchRecords(page)">Refresh</b-button>
      </div>
      <b-alert v-if="error" show variant="danger" class="mx-1">{{ error }}</b-alert>
      <div v-if="loading" class="text-center p-3"><b-spinner variant="primary" /></div>
      <b-table v-else :items="records" :fields="fields" responsive hover show-empty empty-text="Belum ada penerimaan." class="mb-0">
        <template #cell(code)="row"><b-button variant="link" size="sm" @click="openDetail(row.item.id)">{{ row.item.code || '-' }}</b-button></template>
        <template #cell(total)="row">{{ rupiah(row.item.total) }}</template>
        <template #cell(actions)="row">
          <b-button size="sm" variant="outline-primary" @click="openDetail(row.item.id)">Detail / Edit</b-button>
        </template>
      </b-table>
      <div class="footer"><span>{{ total }} transaksi</span><b-pagination v-model="page" :per-page="perPage" :total-rows="total" @change="fetchRecords" /></div>
    </b-card>

  </section>
</template>

<script>
import axios from '@axios'
import { BAlert, BBadge, BButton, BCard, BCol, BFormGroup, BFormInput, BFormTextarea, BPagination, BRow, BSpinner, BTable } from 'bootstrap-vue'
export default {
  name: 'ManageReceive',
  components: { BAlert, BBadge, BButton, BCard, BCol, BFormGroup, BFormInput, BFormTextarea, BPagination, BRow, BSpinner, BTable },
  data() { return { query: '', page: 1, perPage: 20, total: 0, records: [], loading: false, error: '', detail: null, detailLoading: false, detailError: '', saving: false,
    fields: [{ key: 'code', label: 'Kode' }, { key: 'post_date', label: 'Tanggal' }, { key: 'giver', label: 'Vendor' }, { key: 'receiver', label: 'Penerima' }, { key: 'item_count', label: 'Item' }, { key: 'total', label: 'Total' }, { key: 'actions', label: 'Aksi' }] } },
  created() { this.fetchRecords(1) },
  methods: {
    rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') },
    message(e) { return (e.response && e.response.data && e.response.data.message) || 'Terjadi kesalahan.' },
    async fetchRecords(page = 1) {
      this.loading = true; this.error = ''
      try {
        const r = await axios.get('admin/inventory-receives', { params: { page, perPage: this.perPage, q: this.query } })
        const p = r.data.payload
        this.records = p.data || []; this.total = p.total || 0; this.page = p.current_page || page
      } catch (e) { this.error = this.message(e) } finally { this.loading = false }
    },
    openDetail(id) {
      this.$router.push({ name: 'inventory-receive-manage-detail', params: { id } })
    },
  },
}
</script>

<style scoped>
.manage-receive { max-width: 1600px; margin: 0 auto; }
.header,.toolbar,.footer,.actions { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: 1rem 1.25rem; flex-wrap: wrap; }
.header { border-bottom: 1px solid rgba(128,128,128,.2); }
.toolbar .form-control { flex: 1 1 260px; min-width: 0; }
.footer .pagination { margin: 0; }
.item-card { border: 1px solid rgba(128,128,128,.25); border-radius: .5rem; padding: 1rem; margin-bottom: .75rem; }
.actions { padding: 1rem 0 0; }
@media(max-width: 767px) { .header,.footer,.actions { align-items: stretch; flex-direction: column; } .header .btn,.actions .btn { width: 100%; } }
</style>
