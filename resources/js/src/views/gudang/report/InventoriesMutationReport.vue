<template>
  <section class="mutation-report">
    <b-card>
      <h4 class="mb-2">Laporan Mutasi Barang</h4>
      <b-form @submit.prevent="loadReport">
        <b-row>
          <b-col cols="12" md="6" lg="4">
            <b-form-group label="Tanggal Mulai">
              <b-form-datepicker v-model="dateStart" locale="id" :max="dateEnd || today" required />
            </b-form-group>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-form-group label="Tanggal Akhir">
              <b-form-datepicker v-model="dateEnd" locale="id" :min="dateStart" :max="today" required />
            </b-form-group>
          </b-col>
        </b-row>
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
          <h5 class="mb-1">Bagian Laporan</h5>
          <div class="button-row mb-1">
            <b-button size="sm" variant="outline-primary" @click="setPreset('all')">Semua</b-button>
            <b-button size="sm" variant="outline-primary" @click="setPreset('detail')">Detail Saja</b-button>
            <b-button size="sm" variant="outline-primary" @click="setPreset('summary')">Rangkuman Saja</b-button>
          </div>
        </div>
        <b-row>
          <b-col v-for="section in availableSections" :key="section.key" cols="12" md="6" xl="4" class="mb-1">
            <b-form-checkbox v-model="selectedSections" :value="section.key">
              {{ section.label }}
            </b-form-checkbox>
          </b-col>
        </b-row>
        <b-alert :show="selectedSections.length === 0" variant="warning">Pilih minimal satu bagian laporan.</b-alert>
        <b-button type="submit" variant="primary" :disabled="loading || !selectedSections.length">
          {{ loading ? 'Memuat...' : 'Tampilkan Laporan' }}
        </b-button>
      </b-form>
    </b-card>

    <b-card v-if="reportURL || loading || errorMessage" class="mt-2">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
        <h5>Hasil Mutasi Barang</h5>
        <div class="button-row mb-1">
          <b-button size="sm" variant="primary" :disabled="!ready" @click="printReport">Print</b-button>
          <b-button size="sm" variant="outline-primary" :disabled="!ready" @click="openReport">Buka Laporan</b-button>
          <b-button size="sm" variant="outline-primary" :disabled="!ready" @click="downloadReport">Download HTML</b-button>
          <b-button size="sm" variant="outline-secondary" @click="$router.go(-1)">Kembali</b-button>
        </div>
      </div>
      <b-alert :show="!!errorMessage" variant="danger">{{ errorMessage }}</b-alert>
      <div v-if="loading" class="text-center py-2">
        <b-spinner variant="primary" /><p>Memuat laporan...</p>
      </div>
      <iframe v-if="reportURL" ref="reportFrame" :src="reportURL" title="Laporan Mutasi Barang"
              class="report-frame" @load="onFrameLoad" />
    </b-card>
  </section>
</template>

<script>
import axios from '@axios'
import {
  BAlert, BButton, BCard, BCol, BForm, BFormCheckbox,
  BFormDatepicker, BFormGroup, BRow, BSpinner,
} from 'bootstrap-vue'

const AVAILABLE_SECTIONS = [
  { key: 'receive', label: 'Penerimaan Stok — Detail', kind: 'detail' },
  { key: 'receiveSum', label: 'Penerimaan Stok — Rangkuman', kind: 'summary' },
  { key: 'nsreceive', label: 'Penerimaan Non-Stok — Detail', kind: 'detail' },
  { key: 'nsreceiveSum', label: 'Penerimaan Non-Stok — Rangkuman', kind: 'summary' },
  { key: 'consume', label: 'Pemakaian Stok — Detail', kind: 'detail' },
  { key: 'consumeSum', label: 'Pemakaian Stok — Rangkuman', kind: 'summary' },
  { key: 'nsconsume', label: 'Pemakaian Non-Stok — Detail', kind: 'detail' },
  { key: 'nsconsumeSum', label: 'Pemakaian Non-Stok — Rangkuman', kind: 'summary' },
]
function localDate(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}
export default {
  name: 'InventoriesMutationReport',
  components: {
    BAlert, BButton, BCard, BCol, BForm, BFormCheckbox,
    BFormDatepicker, BFormGroup, BRow, BSpinner,
  },
  data() {
    const now = new Date()
    return {
      availableSections: AVAILABLE_SECTIONS,
      selectedSections: AVAILABLE_SECTIONS.map(section => section.key),
      dateStart: localDate(new Date(now.getFullYear(), now.getMonth(), 1)),
      dateEnd: localDate(now),
      today: localDate(now),
      reportURL: '',
      ready: false,
      loading: false,
      errorMessage: '',
      requestVersion: 0,
    }
  },
  beforeDestroy() {
    this.requestVersion += 1
    if (this.reportURL) URL.revokeObjectURL(this.reportURL)
  },
  methods: {
    setPreset(preset) {
      this.selectedSections = this.availableSections
        .filter(section => preset === 'all' || section.kind === preset)
        .map(section => section.key)
    },
    async loadReport() {
      if (!this.dateStart || !this.dateEnd || this.dateStart > this.dateEnd || !this.selectedSections.length) {
        this.errorMessage = 'Pilih rentang tanggal yang valid dan minimal satu bagian laporan.'
        return
      }
      const version = ++this.requestVersion
      this.loading = true
      this.ready = false
      this.errorMessage = ''
      if (this.reportURL) URL.revokeObjectURL(this.reportURL)
      this.reportURL = ''
      try {
        const endpoint = `inventory/report/inventory-mutation/${this.dateStart}/${this.dateEnd}`
        const response = await axios.get(endpoint, {
          params: { sections: this.selectedSections.join(',') },
          responseType: 'text',
          headers: { Accept: 'text/html' },
        })
        if (version !== this.requestVersion) return
        const html = typeof response.data === 'string' ? response.data : ''
        const contentType = String(response.headers['content-type'] || '').toLowerCase()
        if (!contentType.includes('text/html') || !/<!doctype html|<html/i.test(html)) {
          throw new Error('Respons server bukan HTML laporan.')
        }
        if (/name=["'](?:password|login-password)["']|auth-login-form/i.test(html)) {
          throw new Error('Sesi login berakhir. Silakan login kembali.')
        }
        this.reportURL = URL.createObjectURL(new Blob([html], { type: 'text/html;charset=utf-8' }))
      } catch (error) {
        if (version !== this.requestVersion) return
        const status = error.response && error.response.status
        this.errorMessage = status === 401 ? 'Silakan login kembali.'
          : status === 403 ? 'Anda tidak memiliki izin membuka laporan.'
            : error.message || 'Gagal memuat laporan.'
      } finally {
        if (version === this.requestVersion) this.loading = false
      }
    },
    onFrameLoad() { this.ready = Boolean(this.reportURL) },
    printReport() {
      if (!this.ready) return
      try { this.$refs.reportFrame.contentWindow.print() }
      catch (e) { this.errorMessage = 'Tidak dapat mencetak dari iframe. Gunakan Buka Laporan.' }
    },
    openReport() { if (this.ready) window.open(this.reportURL, '_blank', 'noopener,noreferrer') },
    downloadReport() {
      if (!this.ready) return
      const a = document.createElement('a')
      a.href = this.reportURL
      a.download = `mutasi-barang-${this.dateStart}-${this.dateEnd}.html`
      document.body.appendChild(a)
      a.click()
      a.remove()
    },
  },
}
</script>

<style scoped>
.button-row { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.report-frame { display: block; width: 100%; min-height: 75vh; border: 1px solid #d6dce5; border-radius: 5px; background: white; }
</style>
