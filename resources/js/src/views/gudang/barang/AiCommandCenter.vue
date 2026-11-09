<template>
  <div class="ai-panel"><h3>AI Command Center — Gudang SJS</h3>
    <div class="ai-notice">Bahasa Indonesia adalah bahasa utama. English and mixed-language commands are also supported.</div>
    <div class="ai-field"><label for="ai-prompt">Perintah / Command</label>
      <textarea id="ai-prompt" v-model="prompt" rows="3" class="ai-input" placeholder="Keluarkan 5 soda api oleh Hendra untuk cuci kain ekstraktor"></textarea>
    </div>
    <button type="button" class="ai-btn ai-primary" :disabled="busy || !prompt.trim()" @click="interpret">Analisis Perintah</button>
    <button type="button" class="ai-btn ai-secondary" class="ml-1" @click="reset">Batal / Reset</button>
    <div v-if="error" class="ai-error">{{ error }}</div>
    <section v-if="preview" class="ai-preview"><h4>Pratinjau Transaksi / Transaction Preview</h4>
      <table class="ai-table">
        <tbody>
          <tr><th>Operasi</th><td>{{ preview.operation === 'stock_out' ? 'Barang Keluar' : 'Cek Stok' }}</td></tr>
          <tr><th>Barang</th><td>{{ preview.item }} ({{ preview.code }})</td></tr>
          <tr><th>Stok tersedia</th><td>{{ preview.available }} {{ preview.unit }}</td></tr>
          <template v-if="preview.operation === 'stock_out'">
            <tr><th>Jumlah keluar</th><td>{{ preview.quantity }} {{ preview.unit }}</td></tr>
            <tr><th>Penerima</th><td>{{ preview.receiver }}</td></tr>
            <tr><th>Tujuan</th><td>{{ preview.purpose }}</td></tr>
            <tr><th>Sisa stok (perkiraan)</th><td>{{ preview.available - preview.quantity }} {{ preview.unit }}</td></tr>
          </template>
        </tbody>
      </table>
      <template v-if="preview.operation === 'stock_out'">
        <div class="ai-field"><label for="ai-account">Akun biaya / Expense account (wajib)</label>
          <select id="ai-account" v-model="accountCode" class="ai-input"><option v-for="option in accountOptions" :key="String(option.value)" :value="option.value" :disabled="option.disabled">{{ option.text }}</option></select>
        </div>
        <div class="ai-warning">Periksa barang, jumlah, satuan, penerima, tujuan, dan akun biaya. Stok serta jurnal akuntansi hanya berubah setelah konfirmasi.</div>
        <button type="button" class="ai-btn ai-success" :disabled="busy || !accountCode" @click="confirm">Konfirmasi &amp; Proses</button>
        <button type="button" class="ai-btn ai-secondary" class="ml-1" @click="reset">Batal</button>
      </template>
    </section>
    <div v-if="success" class="ai-success-note">{{ success }}</div>
  </div>
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

<style scoped>
.ai-panel { padding: 22px; background: #252d42; color: #e7eaf2; border-radius: 10px; max-width: 850px; }
.ai-panel h3 { margin: 0 0 16px; color: inherit; }
.ai-panel h4 { color: inherit; }
.ai-notice,.ai-warning,.ai-error,.ai-success-note { padding: 12px; border-radius: 6px; margin: 12px 0; }
.ai-notice { background: #24445b; }
.ai-warning { background: #5a482b; }
.ai-error { background: #62333a; }
.ai-success-note { background: #245b47; }
.ai-field { margin: 16px 0; }
.ai-field label { display: block; margin-bottom: 8px; }
.ai-input { width: 100%; padding: 10px 12px; border: 1px solid #64708a; border-radius: 6px; background: #151d30; color: #fff; }
.ai-btn { border: 0; padding: 9px 14px; border-radius: 6px; margin: 5px 8px 5px 0; cursor: pointer; }
.ai-btn:disabled { opacity: .5; cursor: not-allowed; }
.ai-primary { background: #7367f0; color: white; }
.ai-secondary { background: #47516a; color: white; }
.ai-success { background: #28a879; color: white; }
.ai-preview { margin-top: 20px; padding: 16px; border: 1px solid #59647c; border-radius: 8px; }
.ai-table { width: 100%; border-collapse: collapse; }
.ai-table th,.ai-table td { padding: 9px; border: 1px solid #59647c; text-align: left; }
</style>
