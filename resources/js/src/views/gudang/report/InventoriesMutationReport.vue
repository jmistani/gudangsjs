
<template>
  <div class="container">
    <b-row class="mb-2">
      <b-col cols="12">
        <b-button-toolbar class="d-flex justify-content-center">
          <b-button-group>
            <b-button
              size="sm"
              variant="primary"
              :disabled="!pdfReady || pdfLoading"
              @click="printPDF"
            >
              Print
            </b-button>
            <b-button
              size="sm"
              variant="outline-primary"
              :disabled="!pdfReady"
              @click="openPDF"
            >
              Buka PDF
            </b-button>
            <b-button
              size="sm"
              variant="outline-primary"
              :disabled="!pdfReady"
              @click="downloadPDF"
            >
              Download
            </b-button>
            <b-button
              size="sm"
              variant="outline-secondary"
              @click="$router.go(-1)"
            >
              Kembali
            </b-button>
          </b-button-group>
        </b-button-toolbar>
      </b-col>
    </b-row>

    <div v-if="pdfLoading" class="text-center py-3">
      <b-spinner variant="primary" />
      <p class="mt-2">Memuat PDF...</p>
    </div>

    <b-alert :show="!!pdfError" variant="danger">
      {{ pdfError }}
    </b-alert>

    <iframe
      ref="pdfFrame"
      title="Bukti Penerimaan Barang"
      :src="pdfURL || 'about:blank'"
      width="100%"
      height="650"
      frameborder="0"
      @load="onPDFLoaded"
    />
  </div>
</template>

<script>
import axios from '@axios'

import {
  BRow,
  BCol,
  BButton,
  BButtonGroup,
  BButtonToolbar,
  BSpinner,
  BAlert,
} from 'bootstrap-vue'

export default {
  name: 'InventoryReceivePDF',

  components: {
    BRow,
    BCol,
    BButton,
    BButtonGroup,
    BButtonToolbar,
    BSpinner,
    BAlert,
  },

  props: {
    receiveId: {
      type: [String, Number],
      default: null,
    },
  },

  data() {
    return {
      pdfURL: '',
      pdfReady: false,
      pdfLoading: false,
      pdfError: '',
      pendingRevokeURL: '',
      requestId: 0,
    }
  },

  computed: {
    documentId() {
      return this.receiveId || this.$route.params.id
    },
  },

  watch: {
    documentId: {
      immediate: true,
      handler(id) {
        if (
          id !== null &&
          id !== undefined &&
          String(id).trim() !== ''
        ) {
          this.loadPDF(id)
        } else {
          this.pdfError =
            'ID penerimaan barang tidak ditemukan.'
        }
      },
    },
  },
  mounted() {
    console.log('InventoryReceivePDF component mounted')
  },
  beforeDestroy() {
    this.requestId += 1

    if (this.pdfURL) {
      URL.revokeObjectURL(this.pdfURL)
    }

    if (this.pendingRevokeURL) {
      URL.revokeObjectURL(this.pendingRevokeURL)
    }
  },

  methods: {
    async loadPDF(id) {
      console.log('InventoryReceivePDF loadPDF called:', id)

      const currentRequest = ++this.requestId

      this.pdfLoading = true
      this.pdfReady = false
      this.pdfError = ''

      try {
        // Read JWT token from Vuexy localStorage
        const token = localStorage.getItem('accessToken')

        if (
          !token ||
          token === 'undefined' ||
          token === 'null'
        ) {
          throw new Error(
            'JWT token tidak ditemukan. Silakan login kembali.'
          )
        }

        // Request PDF through authenticated Axios
        const response = await axios.get(
          `/inventory-receives/showPDF/${encodeURIComponent(id)}`,
          {
            responseType: 'blob',
            headers: {
              Accept: 'application/pdf, application/json',
              Authorization: `Bearer ${token}`,
            },
          }
        )

        if (currentRequest !== this.requestId) {
          return
        }

        // Detect unexpected login redirect
        const finalURL =
          response.request &&
          response.request.responseURL
            ? response.request.responseURL
            : ''

        console.log('PDF status:', response.status)
        console.log('PDF final URL:', finalURL)
        console.log(
          'PDF Content-Type:',
          response.headers['content-type']
        )

        if (
          /\/login(?:[/?#]|$)|\/auth-login(?:[/?#]|$)/i.test(finalURL)
        ) {
          throw new Error(
            'Server mengalihkan permintaan PDF ke halaman login.'
          )
        }

        // Verify that Laravel returned a real PDF
        const contentType = String(
          response.headers['content-type'] || ''
        ).toLowerCase()

        const signature = await response.data
          .slice(0, 5)
          .text()

        if (currentRequest !== this.requestId) {
          return
        }

        if (
          !contentType.includes('application/pdf') ||
          signature !== '%PDF-'
        ) {
          throw new Error(
            `Respons bukan PDF. Content-Type: ${
              contentType || 'tidak ada'
            }`
          )
        }

        // Create a browser-local PDF URL
        const nextURL = URL.createObjectURL(
          new Blob(
            [response.data],
            { type: 'application/pdf' }
          )
        )

        if (this.pendingRevokeURL) {
          URL.revokeObjectURL(this.pendingRevokeURL)
        }

        this.pendingRevokeURL = this.pdfURL
        this.pdfURL = nextURL

      } catch (error) {
        if (currentRequest !== this.requestId) {
          return
        }

        const status =
          error.response && error.response.status

        if (status === 401) {
          this.pdfError =
            'JWT tidak valid atau sudah kedaluwarsa. Silakan login kembali.'
        } else if (status === 403) {
          this.pdfError =
            'Anda tidak memiliki izin untuk melihat PDF ini.'
        } else {
          this.pdfError =
            error.message || 'Gagal memuat PDF.'
        }

        console.error(
          'Inventory Receive PDF error:',
          error
        )

      } finally {
        if (currentRequest === this.requestId) {
          this.pdfLoading = false
        }
      }
    },

    onPDFLoaded() {
      if (!this.pdfURL) return

      this.pdfReady = true

      if (this.pendingRevokeURL) {
        URL.revokeObjectURL(this.pendingRevokeURL)
        this.pendingRevokeURL = ''
      }
    },

    openPDF() {
      if (!this.pdfReady) return

      window.open(
        this.pdfURL,
        '_blank',
        'noopener,noreferrer'
      )
    },

    downloadPDF() {
      if (!this.pdfReady) return

      const link = document.createElement('a')

      link.href = this.pdfURL
      link.download =
        `inventory-receive-${this.documentId}.pdf`

      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },

    printPDF() {
      if (!this.pdfReady) return

      try {
        const frame = this.$refs.pdfFrame

        frame.contentWindow.focus()
        frame.contentWindow.print()

      } catch (error) {
        this.pdfError =
          'Tidak bisa print dari iframe. Gunakan Buka PDF lalu print dari browser.'
      }
    },
  },
}
</script>

<style scoped>
iframe {
  display: block;
  border: 0;
}
</style>
