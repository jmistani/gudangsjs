<template>
  <b-card title="AI Command Center — Gudang SJS">
    <b-alert show variant="info">Bahasa Indonesia adalah bahasa utama. English and mixed-language commands are also supported.</b-alert>
    <b-form-group label="Perintah / Command">
      <b-form-textarea v-model="prompt" rows="3" placeholder="Keluarkan 5 soda api oleh Hendra untuk cuci kain ekstraktor" />
    </b-form-group>
    <b-button variant="primary" :disabled="busy || !prompt.trim()" @click="interpret">Analisis Perintah</b-button>
    <b-button variant="outline-secondary" class="ml-1" @click="reset">Batal / Reset</b-button>
    <b-alert v-if="error" show variant="danger" class="mt-2">{{ error }}</b-alert>
    <b-card v-if="preview" class="mt-3" title="Pratinjau Transaksi / Transaction Preview">
      <b-table-simple small bordered>
        <b-tbody>
          <b-tr><b-th>Operasi</b-th><b-td>{{ preview.operation === 'stock_out' ? 'Barang Keluar' : 'Cek Stok' }}</b-td></b-tr>
          <b-tr><b-th>Barang</b-th><b-td>{{ preview.item }} ({{ preview.code }})</b-td></b-tr>
          <b-tr><b-th>Stok tersedia</b-th><b-td>{{ preview.available }} {{ preview.unit }}</b-td></b-tr>
          <template v-if="preview.operation === 'stock_out'">
            <b-tr><b-th>Jumlah keluar</b-th><b-td>{{ preview.quantity }} {{ preview.unit }}</b-td></b-tr>
            <b-tr><b-th>Penerima</b-th><b-td>{{ preview.receiver }}</b-td></b-tr>
            <b-tr><b-th>Tujuan</b-th><b-td>{{ preview.purpose }}</b-td></b-tr>
            <b-tr><b-th>Sisa stok (perkiraan)</b-th><b-td>{{ preview.available - preview.quantity }} {{ preview.unit }}</b-td></b-tr>
          </template>
        </b-tbody>
      </b-table-simple>
      <template v-if="preview.operation === 'stock_out'">
        <b-form-group label="Akun biaya / Expense account (wajib)">
          <b-form-select v-model="accountCode" :options="accountOptions" />
        </b-form-group>
        <b-alert show variant="warning">Periksa barang, jumlah, satuan, penerima, tujuan, dan akun biaya. Stok serta jurnal akuntansi hanya berubah setelah konfirmasi.</b-alert>
        <b-button variant="success" :disabled="busy || !accountCode" @click="confirm">Konfirmasi &amp; Proses</b-button>
        <b-button variant="outline-secondary" class="ml-1" @click="reset">Batal</b-button>
      </template>
    </b-card>
    <b-alert v-if="success" show variant="success" class="mt-3">{{ success }}</b-alert>
  </b-card>
</template>

<script>
import axios from '@axios'

export default {
  name: 'AiCommandCenter',
  data() {
    return {
      prompt: '',
      busy: false,
      preview: null,
      previewToken: null,
      accountCode: null,
      accounts: [],
      error: '',
      success: '',
    }
  },
  computed: {
    accountOptions() {
      return [{ value: null, text: 'Pilih akun biaya...', disabled: true },
        ...this.accounts.map(a => ({ value: a.code, text: `${a.code} — ${a.name}` }))]
    },
  },
  methods: {
    reset() {
      this.preview = null
      this.previewToken = null
      this.accountCode = null
      this.error = ''
      this.success = ''
    },
    message(error) {
      const response = error.response && error.response.data
      if (response && response.errors) return Object.values(response.errors).flat().join(' ')
      return (response && response.message) || 'Permintaan gagal. Silakan coba lagi.'
    },
    async interpret() {
      this.reset()
      this.busy = true
      try {
        const { data } = await axios.post('/ai-commands/preview', { prompt: this.prompt })
        this.preview = data.preview
        this.previewToken = data.preview_token || null
        if (this.preview.operation === 'stock_out') {
          const accounts = await axios.get('/ai-commands/accounts')
          this.accounts = accounts.data.accounts
        }
      } catch (error) {
        this.error = this.message(error)
      } finally {
        this.busy = false
      }
    },
    async confirm() {
      if (!this.previewToken || !this.accountCode || this.busy) return
      this.busy = true
      this.error = ''
      try {
        const { data } = await axios.post('/ai-commands/execute', {
          preview_token: this.previewToken,
          account_code: this.accountCode,
        })
        this.reset()
        this.success = `${data.message} Kode: ${data.result.code}`
      } catch (error) {
        this.error = this.message(error)
      } finally {
        this.busy = false
      }
    },
  },
}
</script>
