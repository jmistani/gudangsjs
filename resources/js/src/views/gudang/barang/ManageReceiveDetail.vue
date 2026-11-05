<template>
  <section class="receive-detail-page">
    <div class="detail-top">
      <div>
        <b-button variant="outline-secondary" size="sm" :to="{ name: 'inventory-receive-manage' }" class="mb-1">
          <feather-icon icon="ArrowLeftIcon" class="mr-25" /> Kembali ke Daftar
        </b-button>
        <h3 class="mb-25">Detail Terima Barang</h3>
        <p class="text-muted mb-0">Periksa seluruh item dan perbarui informasi yang diperbolehkan.</p>
      </div>
      <b-badge v-if="detail" variant="light-primary" class="code-badge">{{ detail.code }}</b-badge>
    </div>
    <b-alert v-if="error" show variant="danger">{{ error }}</b-alert>
    <div v-if="loading" class="text-center py-5"><b-spinner variant="primary" /><p class="mt-1">Memuat detail penerimaan...</p></div>
    <template v-else-if="detail">
      <b-card class="mb-2">
        <div class="section-heading"><h5>Informasi Penerimaan</h5><small class="text-muted">Kode dan tanggal tidak dapat diubah</small></div>
        <b-row>
          <b-col cols="12" sm="6" lg="3"><b-form-group label="Kode Transaksi"><b-form-input :value="detail.code" readonly /></b-form-group></b-col>
          <b-col cols="12" sm="6" lg="3"><b-form-group label="Tanggal"><b-form-input :value="detail.post_date" readonly /></b-form-group></b-col>
          <b-col cols="12" sm="6" lg="3"><b-form-group label="Vendor"><b-form-input v-model.trim="detail.giver" maxlength="191" /></b-form-group></b-col>
          <b-col cols="12" sm="6" lg="3"><b-form-group label="Diterima Oleh"><b-form-input v-model.trim="detail.receiver" maxlength="191" /></b-form-group></b-col>
        </b-row>
      </b-card>

      <b-card class="mb-2" no-body>
        <div class="items-heading">
          <div><h5 class="mb-25">Daftar Barang Diterima</h5><small class="text-muted">{{ detail.items.length }} item • Ubah detail non-stok dan keterangan. Jumlah/harga hanya dapat diubah setelah pembalikan jurnal tersedia</small></div>
          <b-form-input v-model.trim="itemSearch" placeholder="Cari nama atau kode barang..." class="item-search" />
        </div>
        <div class="item-list">
          <div v-for="entry in filteredItems" :key="entry.item.type + '-' + entry.item.id" class="receipt-item">
            <div class="item-heading">
              <div class="item-title"><span class="item-number">{{ entry.index + 1 }}</span><div><strong>{{ entry.item.name || 'Barang tanpa nama' }}</strong><div class="text-muted small">{{ entry.item.code && entry.item.code !== '-' ? entry.item.code : 'Non-stok' }}</div></div></div>
              <div class="item-controls"><b-badge :variant="entry.item.type === 'stok' ? 'light-success' : 'light-info'">{{ entry.item.type }}</b-badge><b-button size="sm" variant="outline-danger" :disabled="saving" @click="deleteItem(entry.item)">Hapus Baris</b-button></div>
            </div>
            <b-row v-if="entry.item.type === 'non-stok'" class="mb-1"><b-col cols="12" md="8"><b-form-group label="Nama Barang"><b-form-input v-model.trim="entry.item.name" maxlength="191" /></b-form-group></b-col><b-col cols="12" md="4"><b-form-group label="Satuan"><b-form-input v-model.trim="entry.item.unit" maxlength="191" /></b-form-group></b-col></b-row>
            <div class="item-values">
              <div><span class="value-label">Jumlah</span><b-form-input v-model.number="entry.item.qty" type="number" min="0.01" step="any" /></div>
              <div><span class="value-label">Harga Satuan (Rp)</span><b-form-input v-model.number="entry.item.price" type="number" min="0" step="1" /></div>
              <div><span class="value-label">Subtotal</span><strong>{{ rupiah(Number(entry.item.qty || 0) * Number(entry.item.price || 0)) }}</strong></div>
            </div>
            <b-form-group label="Keterangan" class="mb-0">
              <b-form-textarea v-model="entry.item.memo" rows="2" max-rows="4" maxlength="2000" placeholder="Keterangan barang (opsional)" />
            </b-form-group>
          </div>
          <div v-if="!filteredItems.length" class="text-center text-muted py-3">Tidak ada barang yang cocok.</div>
        </div>
      </b-card>

      <div class="save-bar">
        <div><small class="text-muted d-block">Total Penerimaan</small><strong class="total-amount">{{ rupiah(totalAmount) }}</strong></div>
        <div class="save-actions">
          <b-button variant="outline-secondary" :to="{ name: 'inventory-receive-manage' }">Batal</b-button>
          <b-button variant="primary" :disabled="saving || !detail.giver || !detail.receiver" @click="save">
            <b-spinner v-if="saving" small class="mr-50" /> {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </b-button>
        </div>
      </div>
    </template>
  </section>
</template>

<script>
import axios from '@axios'
import { BAlert, BBadge, BButton, BCard, BCol, BFormGroup, BFormInput, BFormTextarea, BRow, BSpinner } from 'bootstrap-vue'

export default {
  name: 'ManageReceiveDetail',
  components: { BAlert, BBadge, BButton, BCard, BCol, BFormGroup, BFormInput, BFormTextarea, BRow, BSpinner },
  data() { return { detail: null, loading: false, saving: false, error: '', itemSearch: '' } },
  computed: {
    filteredItems() {
      const q = this.itemSearch.toLowerCase()
      return (this.detail ? this.detail.items : []).map((item, index) => ({ item, index }))
        .filter(({ item }) => !q || (String(item.name || '') + ' ' + String(item.code || '')).toLowerCase().includes(q))
    },
    totalAmount() { return this.detail ? this.detail.items.reduce((sum, x) => sum + Number(x.qty || 0) * Number(x.price || 0), 0) : 0 },
  },
  created() { this.load() },
  methods: {
    rupiah(value) { return 'Rp ' + Number(value || 0).toLocaleString('id-ID') },
    formatQty(value) { return Number(value || 0).toLocaleString('id-ID') },
    message(e) { const data = e.response && e.response.data; return (data && data.message) || 'Terjadi kesalahan.' },
    async load() {
      this.loading = true; this.error = ''
      try { const response = await axios.get('admin/inventory-receives/' + this.$route.params.id); this.detail = response.data.payload }
      catch (e) { this.error = this.message(e) }
      finally { this.loading = false }
    },
    async deleteItem(item) {
      const yes = await this.$bvModal.msgBoxConfirm(
        'Hapus baris ' + (item.name || item.id) + '? Penghapusan hanya akan berhasil jika pembalikan stok dan jurnal aman.',
        { title: 'Konfirmasi Hapus Baris', okTitle: 'Hapus', cancelTitle: 'Batal', okVariant: 'danger', centered: true },
      )
      if (!yes) return
      this.error = ''
      try {
        await axios.delete('admin/inventory-receives/' + this.detail.id + '/items/' + item.type + '/' + item.id)
        await this.load()
      } catch (e) { this.error = this.message(e) }
    },
    async save() {
      if (this.saving || !this.detail) return
      this.saving = true; this.error = ''
      try {
        await axios.put('admin/inventory-receives/' + this.detail.id, {
          giver: this.detail.giver,
          receiver: this.detail.receiver,
          items: this.detail.items.map(x => ({ type: x.type, id: x.id, name: x.name, unit: x.unit, qty: Number(x.qty), price: Number(x.price), memo: x.memo || '' })),
        })
        this.$router.push({ name: 'inventory-receive-manage' })
      } catch (e) { this.error = this.message(e); window.scrollTo(0, 0) }
      finally { this.saving = false }
    },
  },
}
</script>

<style scoped>
.receive-detail-page { max-width: 1560px; margin: 0 auto; padding-bottom: 2rem; }
.detail-top,.section-heading,.items-heading,.item-heading,.item-title,.save-bar,.save-actions { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.detail-top { margin-bottom: 1.5rem; flex-wrap: wrap; }
.code-badge { font-size: .9rem; padding: .7rem; }
.section-heading { margin-bottom: 1rem; flex-wrap: wrap; }
.section-heading h5 { margin: 0; }
.items-heading { padding: 1.25rem; flex-wrap: wrap; border-bottom: 1px solid rgba(128,128,128,.2); }
.item-search { max-width: 360px; }
.item-list { padding: 1rem; display: grid; gap: 1rem; }
.receipt-item { border: 1px solid rgba(128,128,128,.23); border-radius: .6rem; padding: 1.1rem; min-width: 0; }
.item-heading { align-items: flex-start; margin-bottom: 1rem; } .item-controls { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.item-title { justify-content: flex-start; min-width: 0; }
.item-title strong { overflow-wrap: anywhere; }
.item-number { display: inline-flex; justify-content: center; align-items: center; flex-shrink: 0; width: 32px; height: 32px; border-radius: .5rem; background: rgba(115,103,240,.13); color: #7367f0; font-weight: 700; }
.item-values { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .75rem; margin-bottom: 1rem; }
.item-values > div { border: 1px solid rgba(128,128,128,.16); border-radius: .4rem; padding: .8rem; min-width: 0; }
.item-values strong { display: block; overflow-wrap: anywhere; }
.value-label { display: block; font-size: .8rem; opacity: .7; margin-bottom: .25rem; }
.save-bar { padding: 1.25rem; border-radius: .5rem; border: 1px solid rgba(128,128,128,.2); background: var(--card-bg, transparent); flex-wrap: wrap; }
.total-amount { font-size: 1.4rem; }
@media (max-width: 767px) {
  .item-values { grid-template-columns: 1fr; }
  .items-heading,.item-search { width: 100%; max-width: none; }
  .save-bar,.save-actions { align-items: stretch; flex-direction: column; width: 100%; }
  .save-actions .btn { width: 100%; }
  .item-list { padding: .75rem; }
  .receipt-item { padding: .85rem; }
}
</style>
