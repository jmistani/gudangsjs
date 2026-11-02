<template>
  <section class="consume-page">
    <ValidationObserver ref="formObserver" v-slot="{ handleSubmit, invalid }">
      <b-form @submit.prevent="handleSubmit(submitConsume)">
        <div class="page-heading">
          <div>
            <h2 class="mb-25">Keluar Barang</h2>
            <p class="text-muted mb-0">Catat pengeluaran stok dan pilih barang melalui pencarian cepat.</p>
          </div>
          <b-button variant="outline-secondary" :to="{ name: 'inventory-list' }">Kembali</b-button>
        </div>

        <b-card class="section-card" no-body>
          <div class="section-heading"><h4 class="mb-0">Informasi transaksi</h4></div>
          <div class="section-body">
            <b-row>
              <b-col cols="12" lg="6">
                <ValidationProvider v-slot="{ errors }" name="Pencatatan biaya" :rules="{ requiredAccount: true }">
                  <b-form-group label="Pencatatan biaya" label-for="consume-account">
                    <v-select
                      v-model="consumeData.account"
                      input-id="consume-account"
                      :options="accountOptions"
                      label="name"
                      :clearable="false"
                      placeholder="Pilih akun biaya"
                    >
                      <template #option="{ name }">{{ name }}</template>
                    </v-select>
                    <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                  </b-form-group>
                </ValidationProvider>
              </b-col>
              <b-col cols="12" sm="6" lg="3">
                <ValidationProvider v-slot="{ errors }" name="Kode transaksi" :rules="{ requiredCode: true }">
                  <b-form-group label="Kode" label-for="consume-code">
                    <b-form-input id="consume-code" v-model.trim="consumeData.consume_code" placeholder="Kode transaksi" autocomplete="off" />
                    <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                  </b-form-group>
                </ValidationProvider>
              </b-col>
              <b-col cols="12" sm="6" lg="3">
                <ValidationProvider v-slot="{ errors }" name="Tanggal" :rules="{ requiredDate: true }">
                  <b-form-group label="Tanggal" label-for="consume-date">
                    <flat-pickr id="consume-date" v-model="consumeData.post_date" :config="flatpickrConfig" class="form-control" />
                    <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                  </b-form-group>
                </ValidationProvider>
              </b-col>
              <b-col cols="12" md="6">
                <ValidationProvider v-slot="{ errors }" name="Staff Gudang" :rules="{ requiredStaff: true }">
                  <b-form-group label="Staff Gudang" label-for="consume-giver">
                    <b-form-input id="consume-giver" v-model.trim="consumeData.giver" placeholder="Nama staff gudang" />
                    <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                  </b-form-group>
                </ValidationProvider>
              </b-col>
              <b-col cols="12" md="6">
                <ValidationProvider v-slot="{ errors }" name="Diterima oleh" :rules="{ requiredReceiver: true }">
                  <b-form-group label="Diterima oleh" label-for="consume-receiver">
                    <b-form-input id="consume-receiver" v-model.trim="consumeData.receiver" placeholder="Nama penerima" />
                    <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                  </b-form-group>
                </ValidationProvider>
              </b-col>
            </b-row>
          </div>
        </b-card>

        <b-card class="section-card" no-body>
          <div class="section-heading item-heading">
            <div>
              <h4 class="mb-25">Daftar Barang Keluar</h4>
              <small class="text-muted">{{ consumeData.items.length }} baris · {{ validItemCount }} baris lengkap</small>
            </div>
            <div class="heading-actions">
              <b-button
                v-if="$can('create', 'inventory')"
                type="button"
                size="sm"
                variant="outline-primary"
                @click="isAddNewInventorySidebarActive = true"
              >
                Buat Barang Baru
              </b-button>
              <b-button type="button" size="sm" variant="primary" @click="addItem">
                <feather-icon icon="PlusIcon" class="mr-25" /> Tambah Baris
              </b-button>
            </div>
          </div>
          <div class="section-body item-list">
            <div v-for="(item, index) in consumeData.items" :key="item._key" class="item-card">
              <div class="item-card-header">
                <span class="item-number">Barang {{ index + 1 }}</span>
                <b-button
                  type="button" variant="flat-danger" size="sm"
                  :disabled="consumeData.items.length === 1 || submitting"
                  @click="removeItem(index)"
                >
                  <feather-icon icon="Trash2Icon" class="mr-25" /> Hapus
                </b-button>
              </div>
              <b-row class="item-fields">
                <b-col cols="12" lg="5">
                  <ValidationProvider v-slot="{ errors }" :name="`Barang ${index + 1}`" :rules="{ requiredInventory: true }">
                    <b-form-group label="Nama / Kode Barang">
                      <v-select
                        v-model="item.inventory"
                        :options="searchState[item._key] ? searchState[item._key].options : []"
                        :filterable="false"
                        :loading="searchState[item._key] ? searchState[item._key].loading : false"
                        :clearable="true"
                        label="name"
                        placeholder="Ketik minimal 2 huruf nama atau kode..."
                        @search="query => searchInventory(query, item._key)"
                        @input="selected => selectInventory(item, selected)"
                      >
                        <template #selected-option="option">
                          <span class="selected-item">{{ option.code }} — {{ option.name }}</span>
                        </template>
                        <template #option="option">
                          <div class="search-result">
                            <strong class="search-code">{{ option.code }}</strong>
                            <span>{{ option.name }}</span>
                            <small class="text-muted">Stok: {{ option.in_stock }} {{ option.unit }}</small>
                          </div>
                        </template>
                        <template #no-options>Masukkan minimal 2 karakter; hasil maksimal 20 barang.</template>
                      </v-select>
                      <div v-if="item.inventory" class="stock-caption">
                        Stok tersedia: <strong>{{ item.in_stock }} {{ item.unit }}</strong>
                      </div>
                      <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                    </b-form-group>
                  </ValidationProvider>
                </b-col>
                <b-col cols="6" md="4" lg="2">
                  <b-form-group label="Harga rata-rata">
                    <div class="value-field">{{ rupiah(priceOf(item)) }}</div>
                  </b-form-group>
                </b-col>
                <b-col cols="6" md="4" lg="2">
                  <ValidationProvider v-slot="{ errors }" :name="`Jumlah barang ${index + 1}`" :rules="{ qtyConsumeAdd: [consumeData.items, 'inventory', 'id', index] }">
                    <b-form-group label="Jumlah">
                      <vue-numeric v-model="item.qty" :precision="2" :minus="false" class="form-control numeric-field" />
                      <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                    </b-form-group>
                  </ValidationProvider>
                </b-col>
                <b-col cols="12" md="4" lg="3">
                  <b-form-group label="Subtotal">
                    <div class="value-field subtotal">{{ rupiah(subtotalOf(item)) }}</div>
                  </b-form-group>
                </b-col>
                <b-col cols="12">
                  <ValidationProvider v-slot="{ errors }" :name="`Keterangan barang ${index + 1}`" :rules="{ requiredKeterangan: true }">
                    <b-form-group label="Keterangan">
                      <b-form-input v-model.trim="item.memo" placeholder="Keperluan / lokasi penggunaan barang" />
                      <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                    </b-form-group>
                  </ValidationProvider>
                </b-col>
              </b-row>
            </div>
            <b-button type="button" variant="outline-primary" block :disabled="submitting" @click="addItem">
              <feather-icon icon="PlusIcon" class="mr-25" /> Tambah Barang Lagi
            </b-button>
          </div>
        </b-card>

        <b-card class="section-card footer-card" no-body>
          <div class="footer-content">
            <div>
              <small class="text-muted d-block">Total pengeluaran</small>
              <h3 class="mb-0 total-text">{{ rupiah(total) }}</h3>
            </div>
            <div class="footer-actions">
              <b-button type="button" variant="outline-secondary" :to="{ name: 'inventory-list' }" :disabled="submitting">Batal</b-button>
              <b-button type="submit" variant="primary" :disabled="submitting || !validItemCount || invalid">
                <b-spinner v-if="submitting" small class="mr-50" />
                {{ submitting ? 'Menyimpan...' : 'Simpan Pengeluaran' }}
              </b-button>
            </div>
          </div>
        </b-card>
      </b-form>
    </ValidationObserver>
    <sidebar-add-new-inventory
      :is-add-new-inventory-sidebar-active.sync="isAddNewInventorySidebarActive"
    />
  </section>
</template>

<script>
import { ValidationObserver, ValidationProvider } from 'vee-validate'
import { BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BButton, BSpinner } from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import store from '@/store'
import gudangStoreModule from '../gudangStoreModule'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import SidebarAddNewInventory from '../sidebar/SidebarAddNewInventory.vue'
// Ensure project-specific custom validation rules are registered.
import '@validationsInventory/consume-add.js'

const MODULE = 'app-inventory'
let nextRowKey = 1

function blankItem() {
  return {
    _key: nextRowKey++,
    inventory: null,
    price: 0,
    qty: 0,
    unit: '',
    avg_value: { amount: 0, currency: 'IDR' },
    in_stock: 0,
    subtotal: 0,
    memo: '',
  }
}

export default {
  name: 'ConsumeAdd',
  components: {
    ValidationObserver, ValidationProvider,
    BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BButton, BSpinner,
    vSelect, flatPickr, ToastificationContent, SidebarAddNewInventory,
  },
  data() {
    return {
      flatpickrConfig: { dateFormat: 'd.m.Y' },
      accountOptions: [],
      searchState: {},
      searchTimers: {},
      searchVersions: {},
      submitting: false,
      isAddNewInventorySidebarActive: false,
      consumeData: {
        consume_code: '',
        post_date: '',
        giver: '',
        receiver: '',
        account: null,
        items: [blankItem()],
        note: '',
        total: 0,
      },
    }
  },
  computed: {
    total() {
      return this.consumeData.items.reduce((sum, item) => sum + this.subtotalOf(item), 0)
    },
    validItemCount() {
      return this.consumeData.items.filter(item =>
        item.inventory && Number(item.qty) > 0 && String(item.memo || '').trim()
      ).length
    },
  },
  created() {
    if (!store.hasModule(MODULE)) store.registerModule(MODULE, gudangStoreModule)
    this.loadFormOptions()
  },
  beforeDestroy() {
    Object.keys(this.searchTimers).forEach(key => clearTimeout(this.searchTimers[key]))
    // Outstanding responses are ignored when the component is disposed.
    Object.keys(this.searchVersions).forEach(key => { this.searchVersions[key] += 1 })
    if (store.hasModule(MODULE)) store.unregisterModule(MODULE)
  },
  methods: {
    async loadFormOptions() {
      const jobs = [
        [ 'fetchAccountExpenseOptions', 'accountOptions' ],
      ]
      await Promise.all(jobs.map(async ([action, key]) => {
        try {
          const response = await store.dispatch(`${MODULE}/${action}`)
          this[key] = (response.data && response.data.payload) || []
        } catch (error) {
          console.error(`${action} failed`, error)
        }
      }))
    },
    addItem() {
      this.consumeData.items.push(blankItem())
    },
    removeItem(index) {
      if (this.consumeData.items.length <= 1) return
      const item = this.consumeData.items[index]
      if (!item) return
      this.cleanupSearch(item._key)
      this.consumeData.items.splice(index, 1)
    },
    cleanupSearch(key) {
      clearTimeout(this.searchTimers[key])
      this.searchVersions[key] = (this.searchVersions[key] || 0) + 1
      this.$delete(this.searchState, key)
      this.$delete(this.searchTimers, key)
    },
    searchInventory(rawQuery, key) {
      const query = String(rawQuery || '').trim()
      clearTimeout(this.searchTimers[key])
      const version = (this.searchVersions[key] || 0) + 1
      this.$set(this.searchVersions, key, version)

      if (query.length < 2) {
        this.$set(this.searchState, key, { options: [], loading: false })
        return
      }

      this.$set(this.searchState, key, { options: [], loading: true })
      this.$set(this.searchTimers, key, setTimeout(async () => {
        try {
          const response = await store.dispatch(`${MODULE}/searchInventory`, query)
          if (this.searchVersions[key] !== version) return
          const options = response.data && response.data.payload
          this.$set(this.searchState, key, {
            options: Array.isArray(options) ? options : [], loading: false,
          })
        } catch (error) {
          if (this.searchVersions[key] !== version) return
          console.error('Inventory autocomplete error:', error)
          this.$set(this.searchState, key, { options: [], loading: false })
        }
      }, 300))
    },
    selectInventory(item, selected) {
      if (!selected) {
        item.avg_value = { amount: 0, currency: 'IDR' }
        item.in_stock = 0
        item.unit = ''
        item.qty = 0
        return
      }
      const rawValue = selected.avg_value
      item.avg_value = rawValue && typeof rawValue === 'object'
        ? { ...rawValue, amount: Number(rawValue.amount) || 0 }
        : { amount: Number(rawValue) || 0, currency: 'IDR' }
      item.in_stock = Number(selected.in_stock) || 0
      item.unit = selected.unit || ''
      item.qty = 0
    },
    priceOf(item) {
      const raw = item.avg_value
      return Number(raw && typeof raw === 'object' ? raw.amount : raw) || 0
    },
    subtotalOf(item) {
      return (Number(item.qty) || 0) * this.priceOf(item)
    },
    rupiah(value) {
      return `Rp ${Math.round(Number(value) || 0).toLocaleString('id-ID')}`
    },
    toast(variant, title, message) {
      this.$toast({
        component: ToastificationContent,
        props: { title, text: message, variant, icon: variant === 'success' ? 'CheckCircleIcon' : 'AlertTriangleIcon' },
      })
    },
    async submitConsume() {
      if (this.submitting) return
      // Validate again at submit time, including quantity and stock.
      const lines = this.consumeData.items
      if (!lines.length || lines.some(item =>
        !item.inventory || !(Number(item.qty) > 0) || !String(item.memo || '').trim()
      )) {
        this.toast('danger', 'Data belum lengkap', 'Pilih barang, isi jumlah, dan keterangan pada setiap baris.')
        return
      }
      if (lines.some(item => Number(item.qty) > Number(item.in_stock))) {
        this.toast('danger', 'Stok tidak cukup', 'Ada jumlah barang yang melebihi stok tersedia.')
        return
      }
      const ids = lines.map(item => item.inventory.id)
      if (new Set(ids).size !== ids.length) {
        this.toast('danger', 'Barang duplikat', 'Gabungkan barang yang sama dalam satu baris.')
        return
      }

      this.submitting = true
      try {
        const payload = {
          ...this.consumeData,
          total: this.total,
          items: lines.map(({ _key, ...item }) => ({
            ...item,
            subtotal: this.subtotalOf(item),
          })),
        }
        await store.dispatch(`${MODULE}/consumeInventory`, payload)
        this.toast('success', 'Berhasil', 'Data pengeluaran barang tersimpan.')
        this.$router.push({ name: 'inventory-list' })
      } catch (error) {
        console.error('Checkout save error:', error)
        this.toast('danger', 'Gagal menyimpan', 'Silakan periksa koneksi atau data lalu coba lagi.')
      } finally {
        this.submitting = false
      }
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
</style>

<style lang="scss" scoped>
.consume-page { max-width: 1500px; margin: 0 auto; padding-bottom: 2rem; }
.page-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
.section-card { overflow: visible; border: 1px solid rgba(110, 125, 145, .16); border-radius: .75rem; margin-bottom: 1.3rem; }
.section-heading { padding: 1.2rem 1.4rem; border-bottom: 1px solid rgba(110, 125, 145, .15); }
.section-body { padding: 1.4rem; }
.heading-actions { display: flex; flex-wrap: wrap; gap: .65rem; }
.item-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
.item-card { padding: 1.1rem 1.2rem .5rem; margin-bottom: 1rem; border-radius: .7rem; border: 1px solid rgba(110, 125, 145, .23); background: rgba(140, 155, 180, .035); }
.item-card-header { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .7rem; }
.item-number { font-weight: 600; }
.item-fields { align-items: flex-start; }
.value-field { min-height: 2.7rem; padding: .7rem .75rem; background: rgba(120, 130, 150, .07); border-radius: .3rem; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.subtotal { font-weight: 600; }
.stock-caption { padding-top: .35rem; font-size: .8rem; color: #168568; }
.search-result { display: flex; flex-direction: column; white-space: normal; overflow-wrap: anywhere; line-height: 1.35; gap: .2rem; padding: .25rem 0; }
.search-code { font-size: .82rem; }
.selected-item { white-space: normal; overflow-wrap: anywhere; }
.footer-card { margin-top: 1rem; }
.footer-content { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1.25rem 1.4rem; }
.footer-actions { display: flex; flex-wrap: wrap; gap: .75rem; }
.total-text { font-weight: 700; }
@media (max-width: 767.98px) {
  .section-heading, .section-body, .footer-content { padding: 1rem; }
  .item-card { padding: .9rem .9rem .3rem; }
  .footer-actions { width: 100%; }
  .footer-actions .btn { flex: 1 1 auto; }
  .value-field { white-space: normal; overflow-wrap: anywhere; }
}
</style>
