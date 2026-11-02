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
  BFormGroup, BRow, BSpinner,
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
    BFormDatepicker, BFormGroup, BRow, BSpinner,
  },
  data() {
    const now = new Date()
    return {
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
    async loadReport() {
      if (!this.dateStart || !this.dateEnd || this.dateStart > this.dateEnd) {
        this.errorMessage = 'Rentang tanggal tidak valid.'
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

        this.reportHTML = html
        this.reportURL = URL.createObjectURL(
          new Blob([html], { type: 'text/html;charset=utf-8' })
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
