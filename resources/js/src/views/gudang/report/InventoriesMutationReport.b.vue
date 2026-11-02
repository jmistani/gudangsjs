<template>
  <section class="mutation-report">
    <b-card>
      <h4 class="mb-2">Laporan Mutasi Barang</h4>

      <b-form @submit.prevent="loadReport">
        <b-row>
          <b-col cols="12" md="5" lg="4">
            <b-form-group label="Tanggal Mulai" label-for="mutation-start">
              <b-form-datepicker
                id="mutation-start"
                v-model="dateStart"
                locale="id"
                :max="dateEnd || today"
                required
              />
            </b-form-group>
          </b-col>

          <b-col cols="12" md="5" lg="4">
            <b-form-group label="Tanggal Akhir" label-for="mutation-end">
              <b-form-datepicker
                id="mutation-end"
                v-model="dateEnd"
                locale="id"
                :min="dateStart"
                :max="today"
                required
              />
            </b-form-group>
          </b-col>

          <b-col cols="12" md="2" lg="4" class="d-flex align-items-end mb-1">
            <b-button type="submit" variant="primary" block :disabled="loading">
              {{ loading ? 'Memuat...' : 'Tampilkan' }}
            </b-button>
          </b-col>
        </b-row>
        <div class="mt-1 mb-2">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
            <strong>Bagian laporan</strong>
            <div class="d-flex flex-wrap report-actions">
              <b-button size="sm" variant="outline-primary" @click="selectPreset('all')">Semua</b-button>
              <b-button size="sm" variant="outline-primary" @click="selectPreset('detail')">Detail Saja</b-button>
              <b-button size="sm" variant="outline-primary" @click="selectPreset('summary')">Rangkuman Saja</b-button>
            </div>
          </div>
          <b-row>
            <b-col v-for="section in sections" :key="section.key" cols="12" md="6" lg="4">
              <b-form-checkbox v-model="selectedSections" :value="section.key" class="mb-75">
                {{ section.label }}
              </b-form-checkbox>
            </b-col>
          </b-row>
          <small class="text-muted">Bagian non-stok berasal dari transaksi NonStockInventory pada laporan lama.</small>
        </div>
      </b-form>

      <b-alert show variant="light" class="mb-0">
        Pilih rentang tanggal lalu klik Tampilkan untuk melihat mutasi stok.
      </b-alert>
    </b-card>

    <b-card v-if="reportURL || loading || errorMessage" class="mt-2">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
        <h5 class="mb-1">Hasil Mutasi Barang</h5>
        <div class="d-flex flex-wrap report-actions mb-1">
          <b-button size="sm" variant="primary" :disabled="!ready" @click="printReport">
            Print
          </b-button>
          <b-button size="sm" variant="outline-primary" :disabled="!ready" @click="openReport">
            Buka Laporan
          </b-button>
          <b-button size="sm" variant="outline-primary" :disabled="!ready" @click="downloadReport">
            Download HTML
          </b-button>
          <b-button size="sm" variant="outline-secondary" @click="$router.go(-1)">
            Kembali
          </b-button>
        </div>
      </div>

      <b-alert :show="!!errorMessage" variant="danger">{{ errorMessage }}</b-alert>

      <div v-if="loading" class="text-center py-3">
        <b-spinner variant="primary" />
        <p class="mt-1">Memuat laporan mutasi barang...</p>
      </div>

      <iframe
        v-if="reportURL"
        ref="reportFrame"
        :src="reportURL"
        title="Laporan Mutasi Barang"
        class="report-frame"
        @load="onFrameLoad"
      />
    </b-card>
  </section>
</template>

<script>
import axios from '@axios'
import {
  BAlert, BButton, BCard, BCol, BForm, BFormDatepicker,
  BFormGroup, BFormCheckbox, BRow, BSpinner,
} from 'bootstrap-vue'

function localDate(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export default {
  name: 'InventoriesMutationReport',
  components: {
    BAlert, BButton, BCard, BCol, BForm,
    BFormDatepicker, BFormGroup, BFormCheckbox, BRow, BSpinner,
  },
  data() {
    const now = new Date()
    return {
      sections: [
        { key: 'receive', label: 'Penerimaan Stok — Detail', kind: 'detail' },
        { key: 'receiveSum', label: 'Penerimaan Stok — Rangkuman', kind: 'summary' },
        { key: 'nsreceive', label: 'Non-Stok — Detail', kind: 'detail' },
        { key: 'nsreceiveSum', label: 'Non-Stok — Rangkuman', kind: 'summary' },
        { key: 'consume', label: 'Pemakaian Stok — Detail', kind: 'detail' },
        { key: 'consumeSum', label: 'Pemakaian Stok — Rangkuman', kind: 'summary' },
      ],
      selectedSections: ['receive', 'receiveSum', 'nsreceive', 'nsreceiveSum', 'consume', 'consumeSum'],
      dateStart: localDate(new Date(now.getFullYear(), now.getMonth(), 1)),
      dateEnd: localDate(now),
      today: localDate(now),
      reportURL: '',
      reportHTML: '',
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
    selectPreset(mode) {
      this.selectedSections = this.sections
        .filter(section => mode === 'all' || section.kind === mode)
        .map(section => section.key)
    },
    filterReportSections(html) {
      // The current Blade template renders six tables in this fixed order.
      // Guard against future layout changes rather than silently omitting data.
      const documentObject = new DOMParser().parseFromString(html, 'text/html')
      const tables = Array.from(documentObject.querySelectorAll('main > table'))
      if (tables.length !== this.sections.length) {
        throw new Error(`Struktur laporan berubah: ditemukan ${tables.length} tabel, diharapkan ${this.sections.length}.`)
      }
      tables.forEach((table, index) => {
        if (!this.selectedSections.includes(this.sections[index].key)) table.remove()
      })
      // Original template has explicit page breaks before its third and fifth tables.
      // Remove them for custom selections to avoid empty printed pages.
      documentObject.querySelectorAll('main > div[style*="page-break-before"]').forEach(el => el.remove())
      return '<!DOCTYPE html>\n' + documentObject.documentElement.outerHTML
    },
    async loadReport() {
      if (!this.dateStart || !this.dateEnd || this.dateStart > this.dateEnd) {
        this.errorMessage = 'Rentang tanggal tidak valid.'
        return
      }

      if (!this.selectedSections.length) {
        this.errorMessage = 'Pilih minimal satu bagian laporan.'
        return
      }

      const version = ++this.requestVersion
      this.loading = true
      this.ready = false
      this.errorMessage = ''

      if (this.reportURL) {
        URL.revokeObjectURL(this.reportURL)
        this.reportURL = ''
      }

      try {
        const endpoint = `inventory/report/inventory-mutation/${this.dateStart}/${this.dateEnd}`
        const response = await axios.get(endpoint, {
          responseType: 'text',
          headers: { Accept: 'text/html' },
        })

        if (version !== this.requestVersion) return

        const contentType = String(response.headers['content-type'] || '').toLowerCase()
        const html = typeof response.data === 'string' ? response.data : ''

        if (!contentType.includes('text/html') || !/<html|<!doctype html/i.test(html)) {
          throw new Error('Server tidak mengembalikan halaman laporan HTML.')
        }

        // A login redirect can also return HTML: never show it as a report.
        if (/name=["'](?:password|login-password)["']|auth-login-form/i.test(html)) {
          throw new Error('Sesi login sudah berakhir. Silakan login kembali.')
        }

        const filteredHTML = this.filterReportSections(html)
        this.reportHTML = filteredHTML
        this.reportURL = URL.createObjectURL(
          new Blob([filteredHTML], { type: 'text/html;charset=utf-8' })
        )
      } catch (error) {
        if (version !== this.requestVersion) return
        const status = error.response && error.response.status
        this.errorMessage = status === 401
          ? 'Sesi login sudah berakhir. Silakan login kembali.'
          : status === 403
            ? 'Anda tidak memiliki izin untuk membuka laporan.'
            : (error.message || 'Gagal memuat laporan.')
        console.error('Mutation report error:', error)
      } finally {
        if (version === this.requestVersion) this.loading = false
      }
    },
    onFrameLoad() {
      this.ready = Boolean(this.reportURL)
    },
    printReport() {
      if (!this.ready || !this.$refs.reportFrame) return
      try {
        const frame = this.$refs.reportFrame.contentWindow
        frame.focus()
        frame.print()
      } catch (error) {
        this.errorMessage = 'Print langsung gagal. Gunakan Buka Laporan lalu cetak.'
      }
    },
    openReport() {
      if (this.ready) window.open(this.reportURL, '_blank', 'noopener,noreferrer')
    },
    downloadReport() {
      if (!this.ready) return
      const link = document.createElement('a')
      link.href = this.reportURL
      link.download = `mutasi-barang-${this.dateStart}-${this.dateEnd}.html`
      document.body.appendChild(link)
      link.click()
      link.remove()
    },
  },
}
</script>

<style scoped>
.report-frame {
  display: block;
  width: 100%;
  min-height: 75vh;
  border: 1px solid rgba(128, 128, 128, 0.25);
  border-radius: 0.4rem;
  background: white;
}
.report-actions {
  gap: 0.5rem;
}
</style>
