<template>
  <section class="invoice-add-wrapper">
    <ValidationObserver 
      ref="refFormObserver"
      v-slot="{ handleSubmit }"
    >
      <b-form ref="form" @submit.prevent="handleSubmit(consumeInventory)">
        <b-row class="invoice-add">
        <!-- Col: Left (Invoice Container) -->
          <b-col
            cols="12"
            xl="10"
          >
            <b-card
              no-body
              class="invoice-preview-card"
            >
              <!-- Header -->
              <b-card-body class="invoice-padding pb-0">

                <b-row class="invoice-spacing">

                  <!-- Col: Invoice To -->
                  <b-col
                    cols="12"
                    xl="9"
                    class="mb-lg-1"
                  >
                    <b-row>
                      <b-col
                        cols="9"
                        xl="6"
                      >
                        <ValidationProvider
                          #default="validationContext"
                          ref="consume-add-account"
                          name="consume-add-account"
                          :rules="{ requiredAccount: true }"
                        >
                        <b-form-group
                          label="Pencatatan biaya"
                          label-for="consume-add-account"
                          :state="getValidationState(validationContext)"
                        >
                          <v-select
                            id="consume-add-account"
                            v-model="consumeData.account"
                            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                            :options="refAccountOptions"
                            label="name"
                            input-id="consume-data-account-id"
                            :clearable="false"
                            class="select-option-capitalize"
                          >
                              <template #option="{ name }">
                                    <!-- HTML that describe how select should render items when the select is open -->
                                {{ name | titlecase }} 
                              </template>
                              <!-- <template #list-header>
                              <li
                                v-b-toggle.sidebar-add-new-ticket
                                class="add-new-account-header d-flex align-items-center my-50"
                              >
                                <feather-icon
                                  icon="PlusIcon"
                                  size="16"
                                />
                                <span class="align-middle ml-25">Tambah Akun/Tiket Reparasi/Servis/Proyek</span>
                              </li>
                            </template> -->
                          </v-select>
                          <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group>
                        </ValidationProvider>
                      </b-col>
                    </b-row>
                    <b-row>
                      <b-col
                        cols="12"
                        xl="3"
                      >
                      <ValidationProvider
                        #default="validationContext"
                        ref="consume-add-code"
                        name="consume-add-code"
                        :rules="{ requiredCode: true }"
                        class="invoice-title-input"
                      >
                        <b-form-group 
                          label="Kode"
                          label-for="consume-add-code-input-group"
                          :state="getValidationState(validationContext)"
                        >
                          <b-input-group id="consume-add-code-input-group" class="input-group-merge invoice-edit-input-group">
                            <b-input-group-prepend is-text>
                              <feather-icon icon="HashIcon" />
                            </b-input-group-prepend>
                            <b-form-input
                              id="consume-add-code-input"
                              v-model="consumeData.consume_code"
                            />
                          </b-input-group>
                        </b-form-group>
                        <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                          {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                      </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        xl="3"
                      >
                        <ValidationProvider
                          #default="validationContext"
                          ref="consume-add-date"
                          name="consume-add-date"
                          :rules="{ requiredDate: true } "
                          class="invoice-title-input"
                        >
                          <b-form-group label="Tanggal" label-for="consume-add-date-input" :state="getValidationState(validationContext)">
                          <flat-pickr
                            id="consume-add-date-input"
                            v-model="consumeData.post_date"
                            :config="flatpickrConfig"
                            class="form-control invoice-title-input"
                          />
                          </b-form-group>
                          <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        xl="3"
                      >
                        <ValidationProvider
                          #default="validationContext"
                          ref="consume-add-input-staff-gudang"
                          :rules=" { requiredStaff: true } "
                          class="invoice-title-input"
                        >
                          <b-form-group 
                            label="Staff Gudang" 
                            label-for="consume-add-input-staff-gudang-input" 
                            :state="getValidationState(validationContext)"
                          >
                            <b-form-input
                              id="consume-add-input-staff-gudang-input"
                              v-model="consumeData.giver"
                              placeholder="Nama Staff Gudang"
                            />
                            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </b-form-group> 
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        xl="3"
                      >
                        <ValidationProvider
                          id="consume-add-input-penerima"
                          ref="consume-add-input-penerima"
                          :rules=" { requiredReceiver: true } "
                          #default="validationContext"
                          class="invoice-title-input"
                        >
                          <b-form-group 
                            label="Diterima" 
                            label-for="consume-add-input-penerima-input" 
                            :state="getValidationState(validationContext)"
                          >
                            <b-form-input
                              id="consume-add-input-penerima-input"
                              v-model="consumeData.receiver"
                              placeholder="Nama Penerima"
                            />
                            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </b-form-group> 
                        </ValidationProvider>
                      </b-col>
                    </b-row>
                  </b-col>
                </b-row>
              </b-card-body>
            <hr class="invoice-spacing mx-0 my-0 px-0 py-0">

          <b-card-body class="invoice-padding pb-0 form-consume-row">
            <b-row class="pb-0">
              <b-col cols="12">
                <div class="flex-grow-1">
                  <b-row class="flex-grow-1 px-0">
                    <!-- Single Item Form Headers -->
                    <b-col
                      cols="12"
                      lg="4"
                    >                      
                      Nama
                    </b-col>
                    <b-col
                      cols="12"
                      lg="2"
                    >
                      Harga
                    </b-col>
                    <b-col
                      cols="12"
                      lg="1"
                    >
                      Jumlah
                    </b-col>
                    <b-col
                      cols="12"
                      lg="3"
                    >
                      Keterangan
                    </b-col>
                    <b-col
                      cols="12"
                      lg="2"
                    >
                      <ValidationProvider
                        ref="inventory-numofvaliditems"
                        name="inventory-numofvaliditems"
                        :rules="{numOfItemTerimaBarang: true }"
                        v-slot="{ errors }"
                      >
                      <b-form-group
                        id="inventory-numofvaliditems"
                      >
                      <b-form-input style="display:none;"  v-model="dataMeta.numOfValidItems" type="number" class="form-control"></b-form-input>
                        <span class="text-custom-error" :state="errors.length > 0 ? false:null" >{{errors[0]}}</span>
                      </b-form-group>
                      </ValidationProvider>
                    </b-col>
                  </b-row>
                  <div class="form-item-action-col" />
                </div>                  
              </b-col>
            </b-row>
          </b-card-body>
          <b-card-body class="invoice-padding pb-0 form-consume-row">
            <div
              ref="form"
              class="repeater-form form-consume-row"
            >
              <b-row
                v-for="(item, index) in consumeData.items"
                :key="index"
                ref="row"
                class="pb-1"
              >
                <b-col cols="12">
                  <div class="d-flex border rounded py-0 px-0 text-right" border="1">
                    <b-row class="flex-grow-1 px-0">
                      <!-- Single Item Form Headers -->
                      <b-col
                        cols="12"
                        lg="4"
                      >
                        <ValidationProvider
                          #default="validationContext"
                          :ref="`inventory-consume-inventory-input-${index}`"
                          :name="`inventory-consume-inventory-input-${index}`"

                          :rules="{ requiredInventory: true, myRule:[consumeData.items,'inventory','code'] }"
                        >
                          <b-form-group
                            :id="`inventory-consume-inventory-input-${index}`"
                            :state="getValidationState(validationContext)"
                          >
                            <v-select
                              :id="`inventory-consume-inventory-input-${index}`"
                              v-model="item.inventory"
                              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                              :options="refInventoryOptions"
                              label="name"
                              :clearable="false"
                              class="mb-0 item-selector-title"
                              placeholder="Select Item"
                              @input="val => updateItemForm(index, val)"
                              :state="getValidationState(validationContext)"
                            >
                              <template #option="{ name }">
                                {{ name | titlecase }}
                              </template>
                              <template #list-header>
                                <li
                                  v-b-toggle.sidebar-add-new-inventory
                                  class="add-new-inventory-header d-flex align-items-center my-50"
                                >
                                  <feather-icon
                                    icon="PlusIcon"
                                    size="16"
                                  />
                                  <span class="align-middle ml-25">Add New Inventory</span>
                                </li>
                              </template>
                            </v-select>
                            <span class="text-success stock-display-consume-add" v-if="item.inventory"> Stok: {{ item.inventory.in_stock }} {{ item.inventory.unit }}</span>                            
                            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </b-form-group>
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        lg="2"
                        align="text-right"
                      >
                        <ValidationProvider
                          :ref="`inventory-consume-price-input-${index}`"
                          :name="`inventory-consume-price-input-${index}`"

                          :rules="{ hargaTerimaBarang:[consumeData.items,'inventory','code',index] }"
                          #default="validationContext"
                        >
                        <b-form-group
                          :id="`inventory-consume-price-input-${index}`"
                          :state="getValidationState(validationContext)"
                        > 
                        <vue-numeric v-if="item.inventory"
                          v-model="item.avg_value.amount"
                          :precision="2"
                          :minus="false"
                          currency="Rp."
                          size="sm"
                          read-only
                          class="mx-0 my-0 px-0 py-0 form-control"
                        />
                        </b-form-group>
                        <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                          {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        lg="1"
                      >
                        <ValidationProvider
                          :ref="`inventory-consume-qty-input-${index}`"
                          :name="`inventory-consume-qty-input-${index}`"

                          :rules="{ qtyConsumeAdd:[consumeData.items,'inventory','id',index] }"
                          #default="validationContext"
                        >
                        <b-form-group
                          :state="getValidationState(validationContext)"
                        >        
                        <vue-numeric
                          :id="`inventory-consume-qty-input-${index}`"
                          v-model="item.qty"
                          :precision="2"
                          :minus="false"
                          size="sm"
                          class="mx-0 my-0 px-0 py-0 form-control"
                        />
                        </b-form-group>
                            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        lg="3"
                      >
                        <ValidationProvider
                          :ref="`inventory-consume-memo-input-${index}`"
                          :name="`inventory-consume-memo-input-${index}`"

                          :rules="{ requiredKeterangan: true }"
                          #default="validationContext"
                        >
                        <b-form-group
                          :state="getValidationState(validationContext)"
                        >
                          <b-form-input 
                            v-model="item.memo" 
                            rules="required" 
                            class="form-control"
                            :id="`inventory-consume-memo-input-${index}`"
                            :state="getValidationState(validationContext)"
                          />                 
                          <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group> 
                        </ValidationProvider>
                      </b-col>
                      <b-col
                        cols="12"
                        lg="2"
                      >
                        <p class="mb-0">
                          <vue-numeric
                            v-model="dataMeta.subtotalRow[index]"
                            :precision="2"
                            :minus="false"
                            read-only
                            currency="Rp."
                            class="mx-0 my-0 px-0 py-0 form-control"
                            align="right"
                          />

                        </p>
                      </b-col>
                    </b-row>
                    <div class="d-flex flex-column justify-content-between border-left py-1 px-1">
                      <feather-icon
                        size="16"
                        icon="XIcon"
                        class="cursor-pointer"
                        @click="removeItem(index)"
                      />
                      <feather-icon
                        :id="`form-item-file-icon-${index}`"
                        size="16"
                        icon="FileIcon"
                        class="cursor-pointer"
                      />

                      <!-- Setting Item Form -->
                      <b-popover
                        :ref="`form-item-settings-popover-${index}`"
                        :target="`form-item-file-icon-${index}`"
                        triggers="click"
                        placement="lefttop"
                      >
                          <b-row>

                            <!-- Field: Discount -->
                            <b-col cols="12">
                              <b-form-group
                                label="Tiket"
                                :label-for="`setting-item-${index}-tiket`"
                              >
                                <v-select
                                  :id="`setting-item-${index}-tiket`"
                                  v-model="item.tiket"
                                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                                  :options="refTicketOptions"
                                  label="name"
                                  :clearable="false"
                                  class="mb-0 item-selector-title"
                                  placeholder="Select Item"
                                >
                                  <template #option="{ name }">
                                    {{ name | titlecase }}
                                  </template>
                                </v-select>
                              </b-form-group>
                            </b-col>


                            <b-col
                              cols="12"
                              class="d-flex justify-content-between mt-1"
                            >
                              <b-button
                                variant="outline-primary"
                                @click="() => {$refs[`form-item-settings-popover-${index}`][0].$emit('close')}"
                              >
                                Tutup
                              </b-button>
                            </b-col>
                          </b-row>
                      </b-popover>
                    </div>
                  </div>
                </b-col>
              </b-row>
            </div>
              <b-button
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                size="sm"
                variant="primary"
                @click="addNewItemInItemForm"
              >
                Tambah Barang
              </b-button>
          </b-card-body>

            <b-card-body class="invoice-padding pb-0">
              <b-row>
                <!-- Col: Total -->
                <b-col
                  cols="12"
                  md="10"
                  class="mt-6 mx-0 d-flex justify-content-end"
                >
                  <div class="receive-total-wrapper">

                    <hr class="my-50">
                    <div class="consume-total-item">
                      <p class="consume-total-title">
                        Total: <vue-numeric
                            v-model="dataMeta.total"
                            :precision="2"
                            :minus="false"
                            read-only
                            currency="Rp."
                            class="form-control"
                            align="right"
                          />
                      </p>
                    </div>
                  </div>
                </b-col>
              </b-row>
            </b-card-body>

            <!-- Spacer -->
            <hr class="invoice-spacing">
            </b-card>
          </b-col>
          <b-col
            cols="12"
            xl="2"
          >
            <b-card
              no-body
            >
              <b-card-body class="pb-0">
                <!-- Button: Send Invoice -->
                <b-button
                  v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                  variant="primary"
                  class="mb-75"
                  block
                  pill
                  type="submit"
                >
                  Simpan
                </b-button>

                <!-- Button: DOwnload -->
                <b-button
                  v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                  variant="outline-primary"
                  class="mb-75"
                  block
                  pill
                  :to="{ name: 'inventory-list'}"
                >
                  Cancel
                </b-button>
              </b-card-body>
            </b-card>
          </b-col> 
        </b-row>
      </b-form>
    </ValidationObserver>
    <!-- <sidebar-add-new-account
      :is-add-new-account-sidebar-active.sync="isAddNewAccountSidebarActive"
      @refetch-data="refetchAccountData"
     /> -->
    <sidebar-add-new-inventory
      :is-add-new-inventory-sidebar-active.sync="isAddNewInventorySidebarActive"
      @refetch-data="refetchInventoryOptions"
     />
    <!-- <sidebar-add-new-ticket
      :is-add-new-ticket-sidebar-active.sync="isAddNewTicketSidebarActive"
      @refetch-data="refetchAccountData"
     /> -->
  </section>
</template>

<script>
import Logo from '@core/layouts/components/Logo.vue'
import { ref, onUnmounted, computed, watch } from '@vue/composition-api'
import { heightTransition } from '@core/mixins/ui/transition'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import {
  BRow, BCol, BCard, BCardBody, BButton, BCardText, BForm, BFormGroup, BFormInput, BInputGroup,
  BInputGroupPrepend, BFormTextarea, BFormCheckbox, BPopover, VBToggle, BFormInvalidFeedback,
  BFormValidFeedback, BFormRadio
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import gudangStoreModule from '../gudangStoreModule'
// import SidebarAddNewAccount from '../sidebar/SidebarAddNewAccount.vue'
// import SidebarAddNewTicket from '../sidebar/SidebarAddNewTicket.vue'
import SidebarAddNewInventory from '../sidebar/SidebarAddNewInventory.vue'
import useConsumeAdd from './useConsumeAdd'
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate'
import { required, email, url, myRule, myRule2, numOfItemTerimaBarang,
       requiredInventory, requiredInv, requiredDate, requiredReceiver, requiredStaff, requiredKeterangan,
       requiredAccount } from '@validationsInventory/consume-add.js'
import formValidation from '@core/comp-functions/forms/form-validation'
import MoneyFormat from 'vue-money-format'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BRow,
    BFormRadio,
    BCol,
    BCard,
    BCardBody,
    BButton,
    BCardText,
    BForm,
    BFormGroup,
    BFormInput,
    BInputGroup,
    BInputGroupPrepend,
    BFormTextarea,
    BFormCheckbox,
    BPopover,
    BFormInvalidFeedback,
    BFormValidFeedback,
    flatPickr,
    vSelect,
    Logo,
    // SidebarAddNewAccount,
    // SidebarAddNewTicket,
    SidebarAddNewInventory,
    ValidationProvider,
    ValidationObserver,
    MoneyFormat,
    ToastificationContent,
  },
  directives: {
    Ripple,
    'b-toggle': VBToggle,

  },
  data() {
    return {
      flatpickrConfig: {
        dateFormat: "d.m.Y",
      },
    }
  },
  mixins: [heightTransition],
  mounted() {
    this.fetchAllAccount()
    this.initTrHeight()
    this.fetchInventoryOptions()
    this.fetchAllTickets()

  },
  created() {
    window.addEventListener('resize', this.initTrHeight)
  },
  destroyed() {
    window.removeEventListener('resize', this.initTrHeight)
  },
  methods: {
    addNewItemInItemForm() {
//      this.$refs.form.style.overflow = 'hidden'
      this.consumeData.items.push(JSON.parse(JSON.stringify(this.itemFormBlankItem)))
      this.$nextTick(() => {
        this.trAddHeight(this.$refs.row[0].offsetHeight)
        setTimeout(() => {
          this.$refs.form.style.overflow = null
        }, 400)
      })
    },
    removeItem(index) {
      this.consumeData.items.splice(index, 1)
      this.trTrimHeight(this.$refs.row[0].offsetHeight)
    },
    initTrHeight() {
      this.trSetHeight(null)
      this.$nextTick(() => {
        this.trSetHeight(this.$refs.form.scrollHeight)
      })
    },
    consumeInventory() {
      this.consumeData.total = this.dataMeta.total;
       store
        .dispatch('app-inventory/consumeInventory',this.consumeData)
        .then((response) => {
          this.$router.push({ name: 'inventory-list'})
          this.showToast('success','CheckCircleIcon','Data tersimpan', 'Berhasil')
        })
        .catch(() => {
          this.showToast('danger','HeartIcon','Error menyimpan data', 'Error menyimpan data')
        })
    },
    updateItemForm(index, val) {
        const { avg_value, in_stock, unit } = val
        if(this.consumeData.items != null) {
          this.consumeData.items[index].avg_value= avg_value
          this.consumeData.items[index].in_stock = in_stock
          this.consumeData.items[index].qty = 0
          this.consumeData.items[index].unit = unit
          this.consumeData.items[index].memo = ''
        }
        return true
    },
    sycnValueWithHidden() {
        this.consumeData.items.inventoryHidden = this.consumeData.items.inventory
    },
    showToast(variant, icon, title, message) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: title,
          icon,
          text: message,
          variant,
        },
      })
    }
  },
  setup() {
    const INVENTORY_APP_STORE_MODULE_NAME = 'app-inventory'


    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) {
      store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)
    }
    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)

    })

    const refInventoryOptions = ref([])
    const refAccountOptions = ref([])
    const refTicketOptions = ref([])

    const isAddNewAccountSidebarActive = ref(false)
    const isAddNewTicketSidebarActive = ref(false)
    const isAddNewInventorySidebarActive = ref(false)

    const itemFormBlankItem = {
        inventory: null,
        price: 0,
        qty: 0,
        unit: '',
        avg_value: 0,
        in_stock: 0,
        subtotal: 0,
        memo: ''
    }
    const consumeData = ref({
        consume_code: '',
        post_date: '',
        giver: '',
        receiver: '',
        account: null,
        // ? Set single Item in form for adding data
        items: [JSON.parse(JSON.stringify(itemFormBlankItem))],
        note: '',
        total: 0,
    })

    const satuanOptions = ref([])
    store.dispatch('app-inventory/consumeFormData')
      .then((response) => {
        satuanOptions.value = response.data.payload.satuan
      })
    
    const statusPembayaranOptions = [
        "Bon / Hutang",
        "Cash / Lunas"
    ]

    const paymentMethods = [
        'Bank Account',
        'PayPal',
        'UPI Transfer',
    ]

    watch([], () => {
    })

    function fetchInventoryOptions() {
      store
        .dispatch('app-inventory/fetchInventoryOptions')
        .then((response) => {
          refInventoryOptions.value = response.data.payload
        })
        .catch(() => {
          toast({
            component: ToastificationContent,
            props: {
                title: "Error fetching inventories",
                icon: 'AlertTriangleIcon',
                variant: 'danger',
            },
          })
        })      
    }

    function fetchAllAccount()  {
        store
            .dispatch('app-inventory/fetchAccountExpenseOptions')
            .then((response) => {
                refAccountOptions.value = response.data.payload
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching accounts",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    function fetchAllTickets()  {
        store
            .dispatch('app-inventory/fetchTicketOptions')
            .then((response) => {
                refTicketOptions.value = response.data.payload
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching tickets",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    function refetchInventoryOptions() {
        fetchInventoryOptions()
    }

    const refetchAccountData = () => {
        fetchAllAccount()
    }

    const subtotalRow = ref([])
    const discount = ref(0)
    const tax = ref(0)

    const dataMeta = computed(() => {
        return {
            numOfValidItems: consumeData.value.items.reduce((numofvalidrow, i) => numofvalidrow + (i.qty > 0 && i.inventory != null && i.memo != '' ? 1:0), 0),
            subtotalRow:  
              consumeData.value.items.map((item) => {
                return Number(item.qty * item.avg_value.amount)
              })
            ,
            total: consumeData.value.items.reduce((runningsubTotalCount, i) => runningsubTotalCount + (i.qty * i.avg_value.amount), 0)
        }
    })

    const {
      refInventoriesReceiving,

    } = useConsumeAdd()

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation()
    
    return {
      dataMeta,
      refInventoriesReceiving,
      refInventoryOptions,
      refAccountOptions,
      refTicketOptions,
      itemFormBlankItem,
      consumeData,
      satuanOptions,
      statusPembayaranOptions,
      paymentMethods,

      subtotalRow,
      resetForm,
      refFormObserver,
      getValidationState,
      isAddNewAccountSidebarActive,
      isAddNewTicketSidebarActive,
      isAddNewInventorySidebarActive,
      
      fetchInventoryOptions,
      refetchInventoryOptions,
      refetchAccountData,
      fetchAllAccount,
      fetchAllTickets
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
.invoice-add-wrapper {
  .add-new-account-header {
    padding: $options-padding-y $options-padding-x;
      color: $success;

    &:hover {
      background-color: rgba($success, 0.12);
    }
  }
  .add-new-inventory-header {
    padding: $options-padding-y $options-padding-x;
      color: $primary;

    &:hover {
      background-color: rgba($success, 0.12);
    }
  }
}
</style>


<style lang="scss" scoped>
@import "~@core/scss/base/pages/app-consume-add.scss";
@import '~@core/scss/base/components/variables-dark';
@import '~@core/scss/base/custom/custom.scss';

.form-item-section {
background-color:$product-details-bg;
}

.form-item-action-col {
  width: 27px;
}

.repeater-form {
  // overflow: hidden;
  transition: .35s height;
}

.v-select {
  &.item-selector-title,
  &.payment-selector {
    background-color: #fff;

    .dark-layout & {
      background-color: unset;
    }
  }
}
.select-option-capitalize {
  text-transform: capitalize;
}
.dark-layout {
  .form-item-section {
    background-color: $theme-dark-body-bg;

    .row .border {
      background-color: $theme-dark-card-bg;
    }

  }
}
.bd-highlight {
    background-color: rgba(86,61,124,0.15);
    border: 1px solid rgba(86,61,124,0.15);
}
.stock-display-consume-add {
  font-size: 11px;
}
#style1.vue-numeric {
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
}
#style1.vue-numeric-input.updown .numeric-input {
  padding-right: 5px !important;
  padding-left: 5px !important;
}
#style1.vue-numeric-input.updown .btn {
  background: #6fbbff !important;
}
#style1.vue-numeric-input.updown .btn-increment {
  height: 1.5rem;
  width: 100%;
  right: 0 !important;
  left:0 !important;
  top: 0 !important;
  bottom: auto !important;
}

#style1.vue-numeric-input.updown .btn-decrement {
  height: 1.5rem;
  width: 100%;
  left: 0 !important;
  right: 0 !important;
  top: auto !important;
  bottom: 0 !important;
}
.form-consume-row {
  background: #FFF7F7 !important;
}
</style></style>
