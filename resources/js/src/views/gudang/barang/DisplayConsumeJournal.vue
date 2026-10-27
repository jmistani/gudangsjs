<template>
  <div class="container">
    <b-row class="mx-0">
      <b-col md="12">
        <div class="mb-2">
          <b-button-toolbar class="d-flex justify-content-center" aria-label="Inventory Consume">
            <b-button-group class="mx-0">
              <b-button
                size="sm"
                class="mb-0"
                pill
                variant="primary"
                :disabled="!pdfReady || isLoading"
                @click="printFrame"
              >
                Print
              </b-button>
              <b-button
                size="sm"
                class="mb-0"
                pill
                variant="outline-primary"
                @click="$router.go(-1)"
              >
                Kembali
              </b-button>
            </b-button-group>
          </b-button-toolbar>
        </div>
      </b-col>
    </b-row>

    <b-alert :show="!!errorMessage" variant="danger">
      {{ errorMessage }}
    </b-alert>

    <div v-if="isLoading" class="text-center my-3">
      <b-spinner variant="primary" />
      <div>Memuat bukti pemakaian barang...</div>
    </div>

    <iframe
      id="printDiv"
      ref="reportContent"
      title="Bukti Pemakaian Barang"
      :src="reportURL || 'about:blank'"
      width="100%"
      height="500"
      frameborder="0"
      @load="onFrameLoad"
    />
  </div>
</template>

<script>
import axios from '@axios'
import {
  BButton,
  BRow,
  BCol,
  BButtonToolbar,
  BButtonGroup,
  BAlert,
  BSpinner,
} from 'bootstrap-vue'

export default {
  name: 'DisplayConsumeJournal',

  components: {
    BButton,
    BRow,
    BCol,
    BButtonToolbar,
    BButtonGroup,
    BAlert,
    BSpinner,
  },

  data() {
    return {
      reportURL: '',
      pdfReady: false,
      isLoading: false,
      errorMessage: '',
      requestVersion: 0,
      oldObjectURL: '',
    }
  },

  watch: {
    '$route.params.id': {
      immediate: true,
      handler(id) {
        if (id !== undefined && id !== null && String(id).trim() !== '') {
          this.loadReport(id)
        } else {
          this.errorMessage = 'ID pemakaian barang tidak ditemukan.'
        }
      },
    },
  },

  beforeDestroy() {
    this.requestVersion += 1
    if (this.reportURL) URL.revokeObjectURL(this.reportURL)
    if (this.oldObjectURL) URL.revokeObjectURL(this.oldObjectURL)
  },

  methods: {
    async loadReport(id) {
      const version = ++this.requestVersion
      this.pdfReady = false
      this.isLoading = true
      this.errorMessage = ''

      try {
        const token = localStorage.getItem('accessToken')
        if (!token || token === 'null' || token === 'undefined') {
          throw new Error('Token login tidak ditemukan. Silakan login kembali.')
        }

        const response = await axios.get(
          `/inventory-consumes/showPDF/${encodeURIComponent(id)}`,
          {
            responseType: 'blob',
            headers: {
              Accept: 'application/pdf, application/json',
              Authorization: `Bearer ${token}`,
            },
          },
        )
        if (version !== this.requestVersion) return

        const finalURL = (response.request && response.request.responseURL) || ''
        if (/\/(?:login|auth-login)(?:[/?#]|$)/i.test(finalURL)) {
          throw new Error('Server mengalihkan permintaan PDF ke halaman login.')
        }

        const contentType = String(response.headers['content-type'] || '').toLowerCase()
        const signature = await response.data.slice(0, 5).text()
        if (version !== this.requestVersion) return

        if (!contentType.includes('application/pdf') || signature !== '%PDF-') {
          throw new Error(`Server tidak mengirim PDF (Content-Type: ${contentType || 'kosong'}).`)
        }

        const nextURL = URL.createObjectURL(response.data)
        if (this.oldObjectURL) URL.revokeObjectURL(this.oldObjectURL)
        this.oldObjectURL = this.reportURL
        this.reportURL = nextURL
      } catch (error) {
        if (version !== this.requestVersion) return
        const status = error.response && error.response.status
        if (status === 401) {
          this.errorMessage = 'Token JWT tidak valid atau kedaluwarsa. Silakan login kembali.'
        } else if (status === 403) {
          this.errorMessage = 'Anda tidak memiliki izin untuk membuka laporan ini.'
        } else {
          this.errorMessage = error.message || 'Gagal memuat laporan pemakaian barang.'
        }
        console.error('Inventory Consume PDF failed:', error)
      } finally {
        if (version === this.requestVersion) this.isLoading = false
      }
    },

    onFrameLoad() {
      if (!this.reportURL) return
      this.pdfReady = true
      if (this.oldObjectURL) {
        URL.revokeObjectURL(this.oldObjectURL)
        this.oldObjectURL = ''
      }
    },

    printFrame() {
      if (!this.pdfReady) return
      try {
        const frame = this.$refs.reportContent
        frame.contentWindow.focus()
        frame.contentWindow.print()
      } catch (error) {
        this.errorMessage = 'Tidak dapat print dari iframe. Buka PDF di tab baru untuk mencetak.'
      }
    },
  },
}
</script>

