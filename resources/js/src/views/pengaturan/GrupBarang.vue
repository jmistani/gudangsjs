<template>
  <section class="settings-page">
    <b-row>
      <b-col cols="12" lg="4">
        <b-card class="settings-card">
          <h4 class="mb-50">Tambah Grup Barang</h4>
          <p class="text-muted">Buat grup barang baru untuk pilihan pada data inventory.</p>
          <b-form @submit.prevent="add">
            <b-form-group label="Nama" label-for="setting-name">
              <b-form-input id="setting-name" v-model.trim="name" :state="name ? null : false" placeholder="Masukkan nama..." maxlength="191" required />
              <b-form-invalid-feedback>Nama wajib diisi.</b-form-invalid-feedback>
            </b-form-group>
            <b-button variant="primary" type="submit" block :disabled="saving || !name">
              <b-spinner v-if="saving" small class="mr-50" />Simpan
            </b-button>
          </b-form>
          <b-alert v-if="formError" show variant="danger" class="mt-1 mb-0">{{ formError }}</b-alert>
        </b-card>
      </b-col>
      <b-col cols="12" lg="8">
        <b-card class="settings-card">
          <div class="list-header">
            <div><h4 class="mb-25">Daftar Grup Barang</h4><small class="text-muted">{{ filtered.length }} dari {{ rows.length }} data</small></div>
            <b-button variant="outline-primary" size="sm" :disabled="loading" @click="load">Refresh</b-button>
          </div>
          <b-form-input v-model.trim="search" class="mb-1" placeholder="Cari nama..." />
          <b-alert v-if="listError" show variant="danger">{{ listError }}</b-alert>
          <div v-if="loading" class="text-center p-3"><b-spinner variant="primary" /></div>
          <b-table v-else responsive hover :items="paged" :fields="fields" show-empty empty-text="Tidak ada data yang cocok." class="mb-0">
            <template #cell(index)="row">{{ (page - 1) * perPage + row.index + 1 }}</template>
            <template #cell(name)="row"><span class="font-weight-bold">{{ row.item.name }}</span></template>
            <template #cell(actions)="row">
              <b-button size="sm" variant="outline-danger" :disabled="deleting === row.item.id" @click="remove(row.item)">
                <feather-icon icon="Trash2Icon" class="mr-25" /> Hapus
              </b-button>
            </template>
          </b-table>
          <div class="list-footer">
            <small class="text-muted">Menampilkan {{ paged.length }} data</small>
            <b-pagination v-model="page" :per-page="perPage" :total-rows="filtered.length" class="mb-0" />
          </div>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import axios from '@axios'
import { BAlert, BButton, BCard, BCol, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BPagination, BRow, BSpinner, BTable } from 'bootstrap-vue'

export default {
  name: 'GroupBarangSettings',
  components: { BAlert, BButton, BCard, BCol, BForm, BFormGroup, BFormInput, BFormInvalidFeedback, BPagination, BRow, BSpinner, BTable },
  data() {
    return {
      name: '', search: '', rows: [], page: 1, perPage: 10, saving: false, loading: false, deleting: null, formError: '', listError: '',
      fields: [{ key: 'index', label: '#', thStyle: { width: '55px' } }, { key: 'name', label: 'Nama' }, { key: 'actions', label: 'Aksi', thStyle: { width: '115px' } }],
    }
  },
  computed: {
    filtered() { return this.rows.filter(x => String(x.name || '').toLowerCase().includes(this.search.toLowerCase())) },
    paged() { return this.filtered.slice((this.page - 1) * this.perPage, this.page * this.perPage) },
  },
  watch: { search() { this.page = 1 } },
  created() { this.load() },
  methods: {
    message(e) { return (e.response && e.response.data && e.response.data.message) || 'Permintaan gagal. Coba lagi.' },
    async load() {
      this.loading = true; this.listError = ''
      try { const r = await axios.get('inventory-groups/all'); this.rows = Array.isArray(r.data.payload) ? r.data.payload : [] }
      catch (e) { this.listError = this.message(e) }
      finally { this.loading = false }
    },
    async add() {
      if (!this.name || this.saving) return
      this.saving = true; this.formError = ''
      try {
        await axios.post('inventory-groups', { name: this.name })
        this.name = ''; this.page = 1; await this.load()
      } catch (e) { this.formError = this.message(e) }
      finally { this.saving = false }
    },
    async remove(row) {
      const yes = await this.$bvModal.msgBoxConfirm('Hapus "' + row.name + '"? Pastikan tidak digunakan oleh barang lain.', {
        title: 'Konfirmasi Hapus', okTitle: 'Hapus', cancelTitle: 'Batal', okVariant: 'danger', centered: true,
      })
      if (!yes) return
      this.deleting = row.id; this.listError = ''
      try { await axios.delete('inventory-groups/' + row.id); await this.load(); if (this.page > 1 && !this.paged.length) this.page-- }
      catch (e) { this.listError = this.message(e) }
      finally { this.deleting = null }
    },
  },
}
</script>

<style scoped>
.settings-page { max-width: 1500px; margin: 0 auto; }
.settings-card { min-height: 200px; }
.list-header,.list-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.list-header { margin-bottom: 1rem; }
.list-footer { padding-top: 1rem; }
@media (max-width: 991px) { .settings-card { margin-bottom: 1rem; } }
@media (max-width: 575px) { .list-footer { flex-direction: column; align-items: stretch; } .list-footer .pagination { justify-content: center; } }
</style>
