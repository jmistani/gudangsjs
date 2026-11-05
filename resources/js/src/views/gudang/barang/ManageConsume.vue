<template>
  <section class="consume-admin-page">
    <b-card no-body class="shadow-sm">
      <div class="page-header">
        <div>
          <h3 class="mb-25">Keluar Barang</h3>
          <p class="text-muted mb-0">Kelola transaksi pengeluaran barang yang sudah tercatat.</p>
        </div>
        <b-button variant="outline-secondary" :to="{ name: 'inventory-dashboard' }">
          Kembali
        </b-button>
      </div>

      <div class="toolbar">
        <b-input-group class="search-box">
          <b-form-input
            v-model.trim="search"
            placeholder="Cari kode, staff gudang, atau penerima..."
            @keyup.enter="loadRecords(1)"
          />
          <b-input-group-append>
            <b-button variant="primary" @click="loadRecords(1)">
              Cari
            </b-button>
          </b-input-group-append>
        </b-input-group>

        <div class="toolbar-actions">
          <b-button
            variant="danger"
            :disabled="selectedIds.length === 0 || loading"
            @click="deleteSelected"
          >
            <feather-icon icon="Trash2Icon" class="mr-25" />
            Hapus Terpilih
            <span v-if="selectedIds.length">({{ selectedIds.length }})</span>
          </b-button>

          <b-button variant="outline-primary" :disabled="loading" @click="loadRecords(page)">
            <feather-icon icon="RefreshCwIcon" class="mr-25" />
            Refresh
          </b-button>
        </div>
      </div>

      <div v-if="errorMessage" class="px-1 pb-1">
        <b-alert show variant="danger" class="mb-0">
          {{ errorMessage }}
        </b-alert>
      </div>

      <div v-if="loading" class="text-center py-4">
        <b-spinner variant="primary" />
        <div class="mt-1 text-muted">Memuat data Keluar Barang...</div>
      </div>

      <b-table
        v-else
        responsive
        hover
        :items="records"
        :fields="fields"
        class="mb-0"
        empty-text="Belum ada data Keluar Barang."
        show-empty
      >
        <template #head(select)>
          <b-form-checkbox
            :checked="allVisibleSelected"
            :indeterminate="someVisibleSelected"
            @change="toggleAllVisible"
          />
        </template>

        <template #cell(select)="data">
          <b-form-checkbox
            :checked="selectedIds.includes(data.item.id)"
            @change="toggleSelected(data.item.id)"
          />
        </template>

        <template #cell(code)="data">
          <button type="button" class="record-link" @click="openDetail(data.item.id)">
            {{ data.item.code || '-' }}
          </button>
        </template>

        <template #cell(post_date)="data">
          {{ formatDate(data.item.post_date) }}
        </template>

        <template #cell(item_count)="data">
          {{ data.item.item_count }}
        </template>

        <template #cell(total)="data">
          {{ rupiah(data.item.total) }}
        </template>

        <template #cell(actions)="data">
          <div class="d-flex justify-content-end">
            <b-button
              size="sm"
              variant="flat-primary"
              class="mr-25"
              @click="openDetail(data.item.id)"
            >
              <feather-icon icon="EyeIcon" class="mr-25" />
              Detail
            </b-button>
            <b-button
              size="sm"
              variant="flat-danger"
              @click="deleteRecord(data.item)"
            >
              <feather-icon icon="Trash2Icon" />
            </b-button>
          </div>
        </template>
      </b-table>

      <div v-if="!loading && records.length" class="table-footer">
        <small class="text-muted">
          {{ totalRecords }} transaksi
        </small>

        <b-pagination
          v-model="page"
          :total-rows="totalRecords"
          :per-page="perPage"
          align="right"
          class="mb-0"
          @change="loadRecords"
        />
      </div>
    </b-card>

    <b-modal
      v-model="detailVisible"
      size="xl"
      scrollable
      centered
      :title="detailTitle"
      hide-footer
      @hidden="closeDetail"
    >
      <div v-if="detailLoading" class="text-center py-5">
        <b-spinner variant="primary" />
      </div>

      <b-alert v-else-if="detailError" show variant="danger">
        {{ detailError }}
      </b-alert>

      <ValidationObserver v-else ref="detailObserver" v-slot="{ invalid }">
        <b-form @submit.prevent="saveDetail">
          <b-row>
            <b-col cols="12" md="6" lg="3">
              <b-form-group label="Kode">
                <b-form-input v-model.trim="detail.code" />
              </b-form-group>
            </b-col>

            <b-col cols="12" md="6" lg="3">
              <b-form-group label="Tanggal">
                <b-form-input v-model="detail.post_date" type="date" />
              </b-form-group>
            </b-col>

            <b-col cols="12" md="6" lg="3">
              <b-form-group label="Staff Gudang">
                <b-form-input v-model.trim="detail.giver" />
              </b-form-group>
            </b-col>

            <b-col cols="12" md="6" lg="3">
              <b-form-group label="Diterima oleh">
                <b-form-input v-model.trim="detail.receiver" />
              </b-form-group>
            </b-col>

            <b-col cols="12" md="6">
              <b-form-group label="Akun Biaya">
                <v-select
                  v-model="selectedAccount"
                  :options="accountOptions"
                  label="name"
                  :clearable="false"
                  placeholder="Pilih akun biaya"
                />
              </b-form-group>
            </b-col>
          </b-row>

          <div class="detail-section-title">
            <div>
              <h5 class="mb-25">Detail Barang</h5>
              <small class="text-muted">
                Nama/kode barang dikunci. Yang dapat diubah adalah jumlah dan keterangan.
              </small>
            </div>
          </div>

          <div
            v-for="(item, index) in detail.items"
            :key="item.mutation_id"
            class="detail-item"
          >
            <div class="detail-item-title">
              <strong>{{ index + 1 }}. {{ item.code }} — {{ item.name }}</strong>
              <span class="text-muted">{{ item.unit }}</span>
            </div>

            <b-row>
              <b-col cols="12" md="4">
                <b-form-group label="Jumlah">
                  <vue-numeric
                    v-model="item.qty"
                    :precision="2"
                    :minus="false"
                    class="form-control"
                  />
                </b-form-group>
              </b-col>

              <b-col cols="12" md="4">
                <b-form-group label="Harga rata-rata">
                  <b-form-input :value="rupiah(item.price)" readonly />
                </b-form-group>
              </b-col>

              <b-col cols="12" md="4">
                <b-form-group label="Subtotal">
                  <b-form-input :value="rupiah(subtotalOf(item))" readonly />
                </b-form-group>
              </b-col>

              <b-col cols="12">
                <b-form-group label="Keterangan">
                  <b-form-input
                    v-model.trim="item.memo"
                    placeholder="Keterangan / keperluan"
                  />
                </b-form-group>
              </b-col>
            </b-row>
          </div>

          <div class="detail-total">
            Total: <strong>{{ rupiah(detailTotal) }}</strong>
          </div>

          <div class="modal-actions">
            <b-button type="button" variant="outline-danger" @click="deleteDetail">
              <feather-icon icon="Trash2Icon" class="mr-25" />
              Hapus Transaksi
            </b-button>

            <div>
              <b-button
                type="button"
                variant="outline-secondary"
                class="mr-50"
                @click="detailVisible = false"
              >
                Batal
              </b-button>
              <b-button type="submit" variant="primary" :disabled="saving || invalid">
                <b-spinner v-if="saving" small class="mr-50" />
                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </b-button>
            </div>
          </div>
        </b-form>
      </ValidationObserver>
    </b-modal>
  </section>
</template>

<script>
import axios from '@axios'
import { ValidationObserver } from 'vee-validate'
import vSelect from 'vue-select'
import VueNumeric from 'vue-numeric'
import {
  BAlert,
  BButton,
  BCard,
  BCol,
  BForm,
  BFormGroup,
  BFormInput,
  BInputGroup,
  BInputGroupAppend,
  BModal,
  BPagination,
  BRow,
  BSpinner,
  BTable,
  BFormCheckbox,
} from 'bootstrap-vue'

export default {
  name: 'ManageConsume',

  components: {
    ValidationObserver,
    vSelect,
    VueNumeric,
    BAlert,
    BButton,
    BCard,
    BCol,
    BForm,
    BFormGroup,
    BFormInput,
    BInputGroup,
    BInputGroupAppend,
    BModal,
    BPagination,
    BRow,
    BSpinner,
    BTable,
    BFormCheckbox,
  },

  data() {
    return {
      records: [],
      selectedIds: [],
      accountOptions: [],
      search: '',
      page: 1,
      perPage: 25,
      totalRecords: 0,
      loading: false,
      saving: false,
      detailLoading: false,
      detailVisible: false,
      detailError: '',
      errorMessage: '',
      detail: {
        id: null,
        code: '',
        post_date: '',
        giver: '',
        receiver: '',
        account_code: '',
        account_name: '',
        items: [],
      },
    }
  },

  computed: {
    fields() {
      return [
        { key: 'select', label: '', class: 'select-column' },
        { key: 'code', label: 'Kode', sortable: false },
        { key: 'post_date', label: 'Tanggal', sortable: false },
        { key: 'giver', label: 'Staff Gudang', sortable: false },
        { key: 'receiver', label: 'Diterima Oleh', sortable: false },
        { key: 'item_count', label: 'Barang', sortable: false, class: 'text-center' },
        { key: 'total', label: 'Total', sortable: false, class: 'text-right' },
        { key: 'actions', label: 'Aksi', class: 'action-column' },
      ]
    },

    allVisibleSelected() {
      return this.records.length > 0
        && this.records.every(record => this.selectedIds.includes(record.id))
    },

    someVisibleSelected() {
      return this.records.some(record => this.selectedIds.includes(record.id))
        && !this.allVisibleSelected
    },

    selectedAccount: {
      get() {
        return this.accountOptions.find(option => option.code === this.detail.account_code)
          || (this.detail.account_code
            ? {
              code: this.detail.account_code,
              name: this.detail.account_name || this.detail.account_code,
            }
            : null)
      },
      set(value) {
        this.detail.account_code = value ? value.code : ''
        this.detail.account_name = value ? value.name : ''
      },
    },

    detailTitle() {
      return this.detail.code
        ? `Keluar Barang — ${this.detail.code}`
        : 'Detail Keluar Barang'
    },

    detailTotal() {
      return this.detail.items.reduce(
        (sum, item) => sum + this.subtotalOf(item),
        0,
      )
    },
  },

  created() {
    this.loadRecords(1)
    this.loadAccountOptions()
  },

  methods: {
    async loadRecords(page = 1) {
      this.loading = true
      this.errorMessage = ''

      try {
        const response = await axios.get('admin/inventory-consumes', {
          params: {
            page,
            perPage: this.perPage,
            q: this.search || undefined,
          },
        })

        const payload = response.data && response.data.payload
        this.records = payload && Array.isArray(payload.data) ? payload.data : []
        this.page = payload ? payload.current_page : page
        this.totalRecords = payload ? payload.total : this.records.length

        const visibleIds = new Set(this.records.map(record => record.id))
        this.selectedIds = this.selectedIds.filter(id => visibleIds.has(id))
      } catch (error) {
        this.errorMessage = this.errorMessageFrom(error, 'Gagal memuat data Keluar Barang.')
      } finally {
        this.loading = false
      }
    },

    async loadAccountOptions() {
      try {
        const response = await axios.get('journals/expenses')
        this.accountOptions = (response.data && response.data.payload) || []
      } catch (error) {
        console.error('Failed to load expense accounts:', error)
      }
    },

    async openDetail(id) {
      this.detailVisible = true
      this.detailLoading = true
      this.detailError = ''
      this.detail = {
        id: null,
        code: '',
        post_date: '',
        giver: '',
        receiver: '',
        account_code: '',
        account_name: '',
        items: [],
      }

      try {
        const response = await axios.get(`admin/inventory-consumes/${id}`)
        this.detail = response.data.payload
      } catch (error) {
        this.detailError = this.errorMessageFrom(error, 'Gagal memuat detail transaksi.')
      } finally {
        this.detailLoading = false
      }
    },

    closeDetail() {
      this.detailError = ''
      this.saving = false
    },

    toggleSelected(id) {
      if (this.selectedIds.includes(id)) {
        this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== id)
      } else {
        this.selectedIds = [...this.selectedIds, id]
      }
    },

    toggleAllVisible(checked) {
      if (checked) {
        const ids = this.records.map(record => record.id)
        this.selectedIds = Array.from(new Set([...this.selectedIds, ...ids]))
      } else {
        const visibleIds = new Set(this.records.map(record => record.id))
        this.selectedIds = this.selectedIds.filter(id => !visibleIds.has(id))
      }
    },

    async saveDetail() {
      if (this.saving || !this.detail.id) return

      const confirmed = await this.confirm(
        'Simpan perubahan?',
        'Jumlah/keterangan dan informasi transaksi akan diperbarui dan saldo stok akan dihitung ulang.',
        'Simpan',
      )

      if (!confirmed) return

      this.saving = true

      try {
        await axios.put(`admin/inventory-consumes/${this.detail.id}`, {
          code: this.detail.code,
          post_date: this.detail.post_date,
          giver: this.detail.giver,
          receiver: this.detail.receiver,
          account_code: this.detail.account_code,
          items: this.detail.items.map(item => ({
            mutation_id: item.mutation_id,
            qty: Number(item.qty),
            memo: item.memo || '',
          })),
        })

        this.$toast({
          variant: 'success',
          title: 'Berhasil',
          text: 'Data Keluar Barang berhasil diperbarui.',
        })

        this.detailVisible = false
        await this.loadRecords(this.page)
      } catch (error) {
        this.detailError = this.errorMessageFrom(error, 'Gagal menyimpan perubahan.')
      } finally {
        this.saving = false
      }
    },

    async deleteRecord(record) {
      const confirmed = await this.confirm(
        'Hapus transaksi?',
        `Transaksi ${record.code || record.id} akan dihapus dan saldo stok/accounting akan disesuaikan kembali.`,
        'Hapus',
      )

      if (!confirmed) return

      try {
        await axios.delete(`admin/inventory-consumes/${record.id}`)
        this.selectedIds = this.selectedIds.filter(id => id !== record.id)

        this.$toast({
          variant: 'success',
          title: 'Berhasil',
          text: 'Transaksi berhasil dihapus.',
        })

        await this.loadRecords(this.page)
      } catch (error) {
        this.errorMessage = this.errorMessageFrom(error, 'Gagal menghapus transaksi.')
      }
    },

    async deleteSelected() {
      if (!this.selectedIds.length) return

      const confirmed = await this.confirm(
        'Hapus transaksi terpilih?',
        `Sebanyak ${this.selectedIds.length} transaksi akan dihapus.`,
        'Hapus Semua',
      )

      if (!confirmed) return

      try {
        await axios.post('admin/inventory-consumes/bulk-delete', {
          ids: this.selectedIds,
        })

        this.$toast({
          variant: 'success',
          title: 'Berhasil',
          text: 'Transaksi terpilih berhasil dihapus.',
        })

        this.selectedIds = []
        await this.loadRecords(this.page)
      } catch (error) {
        this.errorMessage = this.errorMessageFrom(error, 'Gagal menghapus transaksi terpilih.')
      }
    },

    async deleteDetail() {
      if (!this.detail.id) return

      const confirmed = await this.confirm(
        'Hapus transaksi ini?',
        `Transaksi ${this.detail.code || this.detail.id} akan dihapus permanen dari daftar aktif.`,
        'Hapus',
      )

      if (!confirmed) return

      try {
        await axios.delete(`admin/inventory-consumes/${this.detail.id}`)
        this.detailVisible = false
        this.$toast({
          variant: 'success',
          title: 'Berhasil',
          text: 'Transaksi berhasil dihapus.',
        })
        await this.loadRecords(this.page)
      } catch (error) {
        this.detailError = this.errorMessageFrom(error, 'Gagal menghapus transaksi.')
      }
    },

    confirm(title, message, okTitle) {
      return this.$bvModal.msgBoxConfirm(message, {
        title,
        okTitle,
        cancelTitle: 'Batal',
        okVariant: 'danger',
        cancelVariant: 'outline-secondary',
        centered: true,
        hideHeaderClose: false,
      })
    },

    subtotalOf(item) {
      return (Number(item.qty) || 0) * (Number(item.price) || 0)
    },

    rupiah(value) {
      return `Rp ${Math.round(Number(value) || 0).toLocaleString('id-ID')}`
    },

    formatDate(value) {
      if (!value) return '-'
      const parts = String(value).split('-')
      if (parts.length !== 3) return value
      return `${parts[2]}-${parts[1]}-${parts[0]}`
    },

    errorMessageFrom(error, fallback) {
      const response = error && error.response
      if (response && response.status === 403) {
        return 'Akses ditolak. Halaman ini hanya dapat digunakan oleh Super Admin.'
      }
      if (response && response.data && response.data.message) {
        return response.data.message
      }
      return fallback
    },
  },
}
</script>

<style lang="scss" scoped>
.consume-admin-page {
  max-width: 1600px;
  margin: 0 auto;
  padding-bottom: 2rem;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(110, 125, 145, .15);
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  width: min(100%, 520px);
}

.toolbar-actions {
  display: flex;
  gap: .5rem;
  flex-wrap: wrap;
}

.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
}

.record-link {
  padding: 0;
  border: 0;
  background: transparent;
  color: #7367f0;
  font-weight: 600;
  cursor: pointer;
}

.record-link:hover {
  text-decoration: underline;
}

.select-column {
  width: 45px;
}

.action-column {
  width: 170px;
}

.detail-section-title {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-top: 1px solid rgba(110, 125, 145, .15);
}

.detail-item {
  padding: 1rem;
  margin-bottom: .85rem;
  border: 1px solid rgba(110, 125, 145, .2);
  border-radius: .65rem;
  background: rgba(120, 130, 150, .035);
}

.detail-item-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: .8rem;
}

.detail-total {
  text-align: right;
  font-size: 1.05rem;
  padding: 1rem 0;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(110, 125, 145, .15);
}

@media (max-width: 767.98px) {
  .page-header,
  .toolbar,
  .table-footer,
  .modal-actions {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .page-header,
  .toolbar,
  .table-footer,
  .modal-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    width: 100%;
  }

  .toolbar-actions {
    width: 100%;
  }

  .toolbar-actions .btn {
    flex: 1 1 auto;
  }

  .detail-item-title {
    align-items: flex-start;
    flex-direction: column;
  }

  .modal-actions > div {
    display: flex;
    width: 100%;
  }

  .modal-actions > div .btn {
    flex: 1 1 auto;
  }
}

.dark-layout {
  .detail-item {
    background: rgba(255, 255, 255, .025);
    border-color: rgba(255, 255, 255, .08);
  }

  .record-link {
    color: #8b7ff6;
  }
}
</style>
