<template>
  <section class="invoice-add-wrapper">
    <b-row class="invoice-add">

      <!-- Col: Left (Invoice Container) -->
      <b-col
        cols="12"
        xl="12"
      >
        <b-form ref="form" @submit.prevent>
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
                  xl="4"
                  class="mb-lg-1"
                >
                  <h6 class="mb-2">
                    Vendor :
                  </h6>

                  <!-- Select Vendor -->
                  <v-select
                    v-model="receiveData.vendor"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                    :options="refVendorOptions"
                    label="name"
                    input-id="invoice-data-vendor"
                    :clearable="false"
                    class="select-option-capitalize"
                  >
                      <template #option="{ name, address }">
                             <!-- HTML that describe how select should render items when the select is open -->
                        {{ name | titlecase }} - {{ address | titlecase}}
                      </template>
                      <template #list-header>
                      <li
                        v-b-toggle.sidebar-add-new-vendor
                        class="add-new-vendor-header d-flex align-items-center my-50"
                      >
                        <feather-icon
                          icon="PlusIcon"
                          size="16"
                        />
                        <span class="align-middle ml-25">Add New Vendor</span>
                      </li>
                    </template>
                  </v-select>

                  <!-- Selected Vendor -->
                  <div
                    v-if="receiveData.vendor"
                    class="mt-1"
                  >
                    <h6 class="mb-25">
                      {{ receiveData.vendor.name }}
                    </h6>
                    <b-card-text class="mb-25">
                      {{ receiveData.vendor.address }}
                    </b-card-text>
                    <b-card-text class="mb-25">
                      {{ receiveData.vendor.contact }}
                    </b-card-text>
                    <b-card-text class="mb-0">
                      {{ receiveData.vendor.email }}
                    </b-card-text>
                  </div>
                </b-col>
                <!-- Header: Right Content -->
                <b-col
                  cols="12"
                  xl="6"
                  class="mt-xl-0 mt-2 justify-content-end d-xl-flex d-block"
                >
                <div class="invoice-number-date mt-md-0 mt-2">
                  <div class="d-flex align-items-center justify-content-md-fill mb-1">
                    <h4 class="invoice-title">
                      Invoice
                    </h4>
                    <b-input-group class="input-group-merge invoice-edit-input-group disabled">
                      <b-input-group-prepend is-text>
                        <feather-icon icon="HashIcon" />
                      </b-input-group-prepend>
                      <b-form-input
                        id="invoice-data-id"
                        v-model="receiveData.id"
                        disabled
                      />
                    </b-input-group>
                  </div>
                  <div class="d-flex align-items-center justify-content-md-end mb-1">
                    <span class="title">
                      Tanggal:
                    </span>
                    <flat-pickr
                      v-model="receiveData.date"
                      :config="flatpickrConfig"
                      class="form-control invoice-edit-input"
                    />
                  </div>
                </div>
                </b-col>
                <b-col
                  cols="12"
                  xl="2"
                  class="mt-xl-0 mt-2  align-items-end flex-column  d-xl-flex d-block"
                >
                    <!-- Button: Send Invoice -->
                    <b-button
                      v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                      variant="primary"
                      class="mb-75"
                      block
                      @click="receiveInventory"
                    >
                      Simpan
                    </b-button>

                    <!-- Button: DOwnload -->
                    <b-button
                      v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                      variant="outline-primary"
                      class="mb-75"
                      block
                      :to="{ name: 'inventory-list'}"
                    >
                      Cancel
                    </b-button>
                </b-col> 
              </b-row>
            </b-card-body>
          <hr class="invoice-spacing mx-0 my-0 px-0 py-0">

          <b-card-body class="invoice-padding pb-0">
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
                      lg="2"
                    >
                      Jumlah
                    </b-col>
                    <b-col
                      cols="12"
                      lg="4"
                    >
                      Satuan
                    </b-col>
                  </b-row>
                  <div class="form-item-action-col" />
                </div>                  
              </b-col>
            </b-row>
          </b-card-body>
          <b-card-body class="invoice-padding pb-0">
            <div
              ref="form"
              class="repeater-form"
            >
              <b-row
                v-for="(item, index) in receiveData.items"
                :key="index"
                ref="row"
                class="pb-0"
              >
                <b-col cols="12">
                  <div class="d-flex border rounded py-0 px-0" border="1">
                    <b-row class="flex-grow-1 px-0">
                      <!-- Single Item Form Headers -->
                      <b-col
                        cols="12"
                        lg="4"
                      >
                        <label class="d-inline d-lg-none">Item</label>
                        <v-select
                          v-model="item.inventory"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="refInventoryOptions"
                          label="name"
                          :clearable="false"
                          class="mb-0 item-selector-title"
                          placeholder="Select Item"
                          @input="val => updateItemForm(index, {price:0, qty:0, unit:'BH', memo: ''} )"
                        >
                          <template #option="{ name }">
                                <!-- HTML that describe how select should render items when the select is open -->
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
                      </b-col>
                      <b-col
                        cols="12"
                        lg="2"
                      >
                        <label class="d-inline d-lg-none">Cost</label>
                        <b-form-input
                          v-model="item.price"
                          type="number"
                          class="mb-0"
                        />
                      </b-col>
                      <b-col
                        cols="12"
                        lg="2"
                      >
                        <label class="d-inline d-lg-none">Qty</label>
                        <b-form-input
                          v-model="item.qty"
                          type="number"
                          class="mb-0"
                        />
                      </b-col>
                      <b-col
                        cols="12"
                        lg="2"
                      >
                        <label class="d-inline d-lg-none">Satuan</label>
                          <v-select
                          v-model="item.unit"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="satuanOptions"
                          :clearable="false"
                          class="mb-0 item-selector-title"
                          placeholder="Satuan"
                        />
                      </b-col>
                      <b-col
                        cols="12"
                        lg="1"
                      >
                        <label class="d-inline d-lg-none">Price</label>
                        <p class="mb-0">
                          ${{ item.price * item.qty }}
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
                        <b-form @submit.prevent>
                          <b-row>

                            <!-- Field: Discount -->
                            <b-col cols="12">
                              <b-form-group
                                label="Keterangan"
                                :label-for="`setting-item-${index}-keterangan`"
                              >
                                <b-form-input
                                  :id="`setting-item-${index}-keterangan`"
                                  :value="null"
                                  type="text"
                                  v-model="item.memo"
                                />
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
                        </b-form>
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
                Add Item
              </b-button>
          </b-card-body>

            <!-- Invoice Description: Total -->
            <b-card-body class="invoice-padding pb-0">
              <b-row>

                <!-- Col: Sales Persion -->
                <b-col
                  cols="12"
                  md="6"
                  class="mt-md-0 mt-3 d-flex align-items-center"
                  order="2"
                  order-md="1"
                >
                  <label
                    for="invoice-data-sales-person"
                    class="text-nowrap mr-50"
                  >Sales Person:</label>
                  <b-form-input
                    id="invoice-data-sales-person"
                    v-model="receiveData.salesPerson"
                    placeholder="Edward Crowley"
                  />
                </b-col>

                <!-- Col: Total -->
                <b-col
                  cols="12"
                  md="6"
                  class="mt-md-6 d-flex justify-content-end"
                  order="1"
                  order-md="2"
                >
                  <div class="invoice-total-wrapper">
                    <div class="invoice-total-item">
                      <p class="invoice-total-title">
                        Subtotal:
                      </p>
                      <p class="invoice-total-amount">
                        {{ dataMeta.subtotal }}
                      </p>
                    </div>
                    <div class="invoice-total-item">
                      <p class="invoice-total-title">
                        Discount:
                      </p>
                      <p class="invoice-total-amount">
                        {{ dataMeta.discount }}
                      </p>
                    </div>
                    <div class="invoice-total-item">
                      <p class="invoice-total-title">
                        Tax:
                      </p>
                      <p class="invoice-total-amount">
                        {{ dataMeta.tax }}
                      </p>
                    </div>
                    <hr class="my-50">
                    <div class="invoice-total-item">
                      <p class="invoice-total-title">
                        Total:
                      </p>
                      <p class="invoice-total-amount">
                        {{ dataMeta.total }}
                      </p>
                    </div>
                  </div>
                </b-col>
              </b-row>
            </b-card-body>

            <!-- Spacer -->
            <hr class="invoice-spacing">

            <!-- Note -->
            <b-card-body class="invoice-padding pt-0">
              <span class="font-weight-bold">Note: </span>
              <b-form-textarea v-model="receiveData.note" />
            </b-card-body>
          </b-card>
        </b-form>
      </b-col>

      </b-row>
    <sidebar-add-new-vendor
      :is-add-new-vendor-sidebar-active.sync="isAddNewVendorSidebarActive"
      @refetch-data="refetchVendorData"
     />
    <sidebar-add-new-inventory
      :is-add-new-inventory-sidebar-active.sync="isAddNewInventorySidebarActive"
      @refetch-data="refetchInventoryData"
     />
  </section>
</template>

<script>
import Logo from '@core/layouts/components/Logo.vue'
import { ref, onUnmounted } from '@vue/composition-api'
import { heightTransition } from '@core/mixins/ui/transition'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import {
  BRow, BCol, BCard, BCardBody, BButton, BCardText, BForm, BFormGroup, BFormInput, BInputGroup, BInputGroupPrepend, BFormTextarea, BFormCheckbox, BPopover, VBToggle,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import gudangStoreModule from '../gudangStoreModule'
import SidebarAddNewVendor from '../sidebar/SidebarAddNewVendor.vue'
import SidebarAddNewInventory from '../sidebar/SidebarAddNewInventory.vue'
import useInventoryAdd from './useInventoryAdd'

export default {
  components: {
    BRow,
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
    flatPickr,
    vSelect,
    Logo,
    SidebarAddNewVendor,
    SidebarAddNewInventory,
  },
  directives: {
    Ripple,
    'b-toggle': VBToggle,

  },
  data() {
    return {
      flatpickrConfig: {
         defaultDate: 'today'
      }
    }
  },
  mixins: [heightTransition],
  mounted() {
    this.fetchAllVendor()
    this.fetchAllInventory()
    this.initTrHeight()
  },
  created() {
    window.addEventListener('resize', this.initTrHeight)
  },
  destroyed() {
    window.removeEventListener('resize', this.initTrHeight)
  },
  methods: {
    addNewItemInItemForm() {
      this.$refs.form.style.overflow = 'hidden'
      this.receiveData.items.push(JSON.parse(JSON.stringify(this.itemFormBlankItem)))

      this.$nextTick(() => {
        this.trAddHeight(this.$refs.row[0].offsetHeight)
        setTimeout(() => {
          this.$refs.form.style.overflow = null
        }, 400)
      })
    },
    removeItem(index) {
      this.receiveData.items.splice(index, 1)
      this.trTrimHeight(this.$refs.row[0].offsetHeight)
    },
    initTrHeight() {
      this.trSetHeight(null)
      this.$nextTick(() => {
        this.trSetHeight(this.$refs.form.scrollHeight)
      })
    },
    receiveInventory() {
       store
        .dispatch('app-inventory/receiveInventory',this.receiveData)
        .then(response)
        .catch(() => {
          toast({
              component: ToastificationContent,
              props: {
                  title: "Error Menyimpan Data",
                  icon: 'AlertTriangleIcon',
                  variant: 'danger',
              },
          })
        })
    }
  },
  watch: {
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

    const refVendorOptions = ref([])

    const isAddNewVendorSidebarActive = ref(false)
    const isAddNewInventorySidebarActive = ref(false)

    const itemFormBlankItem = {
        code: '',
        price: 0,
        qty: 0,
        unit: 0,
        memo: ''
    }
    const receiveData = ref({
        id: '',
        vendor: null,

        // ? Set single Item in form for adding data
        items: [JSON.parse(JSON.stringify(itemFormBlankItem))],

        salesPerson: '',
        note: '',
        paymentMethod: null,
        payment: '',
    })

    const satuanOptions = [
        'BH', 'LSN', 'BTG', 'CM', 'M', 'M3', 'PSG', 'LTR', 'DRUM', 'BTL', 'KOTAK', 'TON'
    ]

    const statusPembayaranOptions = [
        "Bon / Hutang",
        "Cash / Lunas"
    ]

    const paymentMethods = [
        'Bank Account',
        'PayPal',
        'UPI Transfer',
    ]

    const updateItemForm = (index, val) => {
        const { price, qty, unit } = val
        receiveData.value.items[index].price = price
        receiveData.value.items[index].qty = qty
        receiveData.value.items[index].unit = unit
    }

    function fetchAllInventory() {
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

    function fetchAllVendor()  {
        store
            .dispatch('app-inventory/fetchVendorOptions')
            .then((response) => {
                refVendorOptions.value = response.data.payload
            })
            .catch(() => {
                toast({
                    component: ToastificationContent,
                    props: {
                        title: "Error fetching vendors",
                        icon: 'AlertTriangleIcon',
                        variant: 'danger',
                    },
                })
            })
    }

    const refetchInventoryData = () => {
        refInventoryOptions.value.refresh()
    }

    const refetchVendorData = () => {
        refVendorOptions.value.refresh()
    }

    const {
      dataMeta,
      refInventoriesReceiving,

    } = useInventoryAdd()

    return {
      dataMeta,
      refInventoriesReceiving,
      refInventoryOptions,
      refVendorOptions,
      itemFormBlankItem,
      receiveData,
      satuanOptions,
      statusPembayaranOptions,
      paymentMethods,

      isAddNewVendorSidebarActive,
      isAddNewInventorySidebarActive,


      updateItemForm,
      refetchInventoryData,
      refetchVendorData,
      fetchAllVendor,
      fetchAllInventory,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
.invoice-add-wrapper {
  .add-new-vendor-header {
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
@import "~@core/scss/base/pages/app-invoice.scss";
@import '~@core/scss/base/components/variables-dark';

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
</style>
