<template>
  <section class="">
    <b-row class="">
      <b-col
        cols="12"
        xl="4"
      >
        <ValidationObserver 
          ref="refFormObserver"
          v-slot="{ handleSubmit }"
        >
        <b-form ref="form" @submit.prevent="handleSubmit(onSubmit)">
          <b-card
            no-body
            class=""
          >
            <!-- Header -->
            <b-card-body class="pb-0">
              <b-row class="">
                <b-col
                  cols="12"
                  md="12"
                  class=""
                >
                  <b-badge pill :variant="supplierData.mode == 'add' ? 'primary' : 'warning'" class="badge-glow">{{ supplierData.mode == 'add' ? 'Adding' : 'Editing' }}</b-badge>
                  <ValidationProvider
                    #default="validationContext"
                    ref="supplier-add-name"
                    name="supplier-add-name"
                    :rules="{ requiredNamaSupplier: [supplierData.mode] }"
                    mode="lazy"
                  >
                <b-form-group
                    label="Nama"
                    label-for="supplier-add-input-name"
                    id="supplier-add-name"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      :disabled="supplierData.mode == 'edit'"
                      id="supplier-add-input-name"
                      v-model="supplierData.name"
                    />
                    <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                  </ValidationProvider>
                </b-col>
              </b-row>
              <b-row class="">
                <b-col
                  cols="12"
                  md="12"
                  class=""
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="supplier-add-email"
                    name="supplier-add-email"
                    mode="lazy"
                  >
                  <b-form-group
                    label="Email"
                    label-for="supplier-add-input-email"
                    id="supplier-add-email"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      id="supplier-add-input-email"
                      v-model="supplierData.email"
                    />
                    <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                  </ValidationProvider>
                </b-col>
              </b-row>
              <b-row class="">
                <b-col
                  cols="12"
                  md="12"
                  class=""
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="supplier-add-address"
                    name="supplier-add-address"
                    mode="lazy"
                  >
                  <b-form-group
                    label="Alamat"
                    label-for="supplier-add-input-address"
                    id="supplier-add-address"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      id="supplier-add-input-address"
                      v-model="supplierData.address"
                    />
                    <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                  </ValidationProvider>
                </b-col>
              </b-row>
              <b-row class="">
                <b-col
                  cols="12"
                  md="12"
                  class=""
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="supplier-add-contact"
                    name="supplier-add-contact"
                    mode="lazy"
                  >
                  <b-form-group
                    label="Telp"
                    label-for="supplier-add-input-contact"
                    id="supplier-add-contact"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      id="supplier-add-input-contact"
                      v-model="supplierData.contact"
                    />
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
                  xl="6"
                  class="mt-xl-0 mt-3  align-items-end flex-column  d-xl-flex d-block"
                >
                </b-col>
                <b-col
                  cols="12"
                  xl="6"
                  class="mt-xl-0 mt-3  align-items-end flex-column  d-xl-flex d-block"
                >
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
                      :to="{ name: 'inventory-list'}"
                      pill
                    >
                      Cancel
                    </b-button>
                </b-col> 
              </b-row>
            </b-card-body>
            <hr class="">
          </b-card>
          </b-form>
        </ValidationObserver>
      </b-col>
      <b-col
        cols="12"
        xl="8"
      >
        <b-card
          no-body
          class=""
        >
          <!-- Header -->
          <b-card-body class="pb-0">
            <b-row>

              <!-- Per Page -->
              <b-col
                cols="12"
                md="6"
                class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
              >
                <label>Entries</label>
                <v-select
                  v-model="perPage"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="perPageOptions"
                  :clearable="false"
                  class="per-page-selector d-inline-block ml-50 mr-1"
                />
                <!-- <b-form-radio
                  v-model="typeFilter"
                  name="type-supplier"
                  value=""
                  selected
                  plain
                  class="radio-supplier-type-filter"
                >
                  Semua 
                </b-form-radio>
                <b-form-radio
                  v-model="typeFilter"
                  name="type-supplier"
                  value="pabrik"
                  plain
                  class="radio-supplier-type-filter"
                >
                  Pabrik
                </b-form-radio>
                <b-form-radio
                  v-model="typeFilter"
                  name="type-supplier"
                  value="kendaraan"
                  plain
                  class="radio-supplier-type-filter"
                >
                  Kendaraan
                </b-form-radio> -->
              </b-col>

              <!-- Search -->
              <b-col
                cols="12"
                md="6"
              >
                <div class="d-flex align-items-center justify-content-end">
                  <b-form-input
                    v-model="searchQuery"
                    class="d-inline-block mr-1"
                    placeholder="Search..."
                  />
                </div>
              </b-col>
            </b-row>
            <b-row>
              <b-col
                cols="12"
                md="12"
                class="d-flex align-items-center justify-content-center justify-content-sm-start"
              >
                <b-table
                  ref="refSupplierTable"
                  :items="fetchSuppliers"
                  striped
                  responsive
                  :fields="tableColumns"
                  primary-key="id"
                  :sort-by.sync="sortBy"
                  show-empty
                  empty-text="No matching records found"
                  :sort-desc.sync="isSortDirDesc"
                  class="position-relative mt-2"
                >
                  <template v-slot:table-busy>
                      <spinner message="Loading ..."></spinner>
                  </template>

                  <template #cell(name)="data">
                    <!-- <b-link
                      :to="{ name: 'supplier-view', params: { id: data.item.id }}"
                      class="font-weight-bold"
                    > -->
                      <span class="text-truncate overflow-hidden">
                        {{ data.item.name | titlecase }}
                      </span>
                    <!-- </b-link> -->
                  </template>

                  <template #cell(actions)="data">
                    <b-link
                      :to="{ name: 'inventory-supplier-view', params: { id: data.item.id }  }"
                    >
                      <feather-icon
                      icon="AlignJustifyIcon"
                      :to="{ name: 'inventory-supplier-view', params: { id: data.item.id }  }"
                      class="cursor-pointer"
                      size="18" />
                    </b-link>
                    <span 
                      class="text-secondary"
                    >
                      <b-link
                        :to="{ name: 'display-supplier', params: { id: data.item.id }  }"
                      >
                      <feather-icon
                      icon="PrinterIcon"
                      class="cursor-pointer"
                      size="18" 
                      :to="{ name: 'display-supplier', params: { id: data.item.id }  }"
                      />
                      </b-link>
                    </span>
                    <span 
                      class="text-warning"
                    >
                      <feather-icon
                        icon="EditIcon"
                        class="cursor-pointer"
                        size="18" 
                        @click="editSupplier(data.item.id)"
                      />
                    </span>
                  </template>
                </b-table>
              </b-col>
            </b-row>
            <b-row class="mt-2 pb-2">
              <b-col
                cols="12"
                md="6"
                class="d-flex align-items-center justify-content-center justify-content-sm-start"
              >

                <span class="text-muted">Showing {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} entries</span>
              </b-col>
              <!-- Pagination -->
              <b-col
                cols="12"
                md="6"
                class="d-flex align-items-center justify-content-center justify-content-sm-end"
              >

                <b-pagination
                  v-model="currentPage"
                  :total-rows="totalSuppliers"
                  :per-page="perPage"
                  first-number
                  last-number
                  class="mb-0 mt-1 mt-sm-0"
                  prev-class="prev-item"
                  next-class="next-item"
                >
                  <template #prev-text>
                    <feather-icon
                      icon="ChevronLeftIcon"
                      size="18"
                    />
                  </template>
                  <template #next-text>
                    <feather-icon
                      icon="ChevronRightIcon"
                      size="18"
                    />
                  </template>
                </b-pagination>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import Logo from '@core/layouts/components/Logo.vue'
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import {
  BRow, BCol, BFormRadioGroup, BFormRadio, BCard, BCardBody, BButton, BLink, BBadge,
  BCardText, BForm, BFormGroup, BFormInput, BInputGroup, BTable, BPagination,
  BInputGroupPrepend, BFormTextarea, BFormCheckbox, BPopover, VBToggle, BFormInvalidFeedback
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import supplierStoreModule from './supplierStoreModule'
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate'
import MoneyFormat from 'vue-money-format'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, onUnmounted, computed, watch } from '@vue/composition-api'
import { requiredNamaSupplier } from '@validationsSupplier/supplier-add.js'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BBadge,
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
    BTable,
    flatPickr,
    vSelect,
    Logo,
    BLink,
    ValidationProvider,
    ValidationObserver,
    MoneyFormat,
    ToastificationContent,
    BFormRadioGroup,
    BFormRadio,
    requiredNamaSupplier,
    BPagination,
  },
  directives: {
    Ripple,
    'b-toggle': VBToggle,

  },
  data() {
    return {
      
      flatpickrConfig: {
        dateFormat: "d.m.Y",
      }
    }
  },
  mixins: [],
  mounted() {
    this.supplierData.mode = 'add'
  },
  created() {
  },
  destroyed() {
  },
  methods: {
    editSupplier(ID) {
      store.dispatch('app-supplier/fetchSupplierEdit', {id: ID}) 
        .then((response) => {
          this.supplierData  = response.data.payload
          this.supplierData.mode = 'edit'
        })
    }
  },
  watch: {
  },
  setup() {
    const SUPPLIER_APP_STORE_MODULE_NAME = 'app-supplier'

    // Register module
    if (!store.hasModule(SUPPLIER_APP_STORE_MODULE_NAME)) {
      store.registerModule(SUPPLIER_APP_STORE_MODULE_NAME, supplierStoreModule)
    }
    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(SUPPLIER_APP_STORE_MODULE_NAME)) store.unregisterModule(SUPPLIER_APP_STORE_MODULE_NAME)

    })

    const refSupplierTable = ref(null)

    const tableColumns = ref([
        { key: 'name', label: 'Nama', sortable: true },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'address', label: 'Alamat', sortable: true },
        { key: 'contact', label: 'Telp', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false },
    ])

    const perPage = ref(20)
    const totalSuppliers = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const searchQuery = ref('')
    const typeFilter = ref('')

  const dataMeta = computed(() => {
      const localItemsCount = refSupplierTable.value ? refSupplierTable.value.localItems.length : 0
      return {
          from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
          to: perPage.value * (currentPage.value - 1) + localItemsCount,
          of: totalSuppliers.value,
      }
  })
  const blankSupplier = ref({
      name: '',
      email: '',
      address: '',
      contact: '',
      mode: 'add',
  })
  
  const supplierData = ref(JSON.parse(JSON.stringify(blankSupplier)))

  const resetSupplier = () => {
    supplierData.value = JSON.parse(JSON.stringify(blankSupplier))
  }

  const onSubmit = () => {
    if(supplierData.value.mode == 'add') {
      store.dispatch('app-supplier/addSupplier', supplierData.value)
        .then(() => {
          refetchData()
          resetForm()
          supplierData.value.mode='add'
        })
    } else {
      store.dispatch('app-supplier/updateSupplier', {id: supplierData.value.id, supplier: supplierData.value })
        .then(() => {
          refetchData()
          resetForm()
        })
    }
  }

  const closeSupplier = (supplierID) => {
    if (confirm("Yakin akan meyelesaikan tiket?")) {
      store.dispatch('app-supplier/closeSupplier', supplierID)
        .then(() => {
          refetchData()
        })
    }
  }

  const printSupplier = (supplierID) => {
    console.log(supplierID)
  }

  const refetchData = () => {
      refSupplierTable.value.refresh()
  }

  watch([currentPage, perPage, searchQuery], () => {
      refetchData()
  })

const fetchSuppliers = (ctx, callback) => {
    store
      .dispatch('app-supplier/fetchSuppliers', {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
      })
      .then((response) => {
          callback(response.data.payload.data)
          totalSuppliers.value = response.data.payload.total
      })
      .catch(() => {
          toast({
              component: ToastificationContent,
              props: {
                  title: "Error fetching Suppliers' list",
                  icon: 'AlertTriangleIcon',
                  variant: 'danger',
              },
          })
      })
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetSupplier)
    
    return {
      dataMeta,

      supplierData,
      onSubmit,
      closeSupplier,

      currentPage,
      fetchSuppliers,
      totalSuppliers,
      searchQuery,
      perPage,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      typeFilter,
      refSupplierTable,
      tableColumns,
      refFormObserver,
      getValidationState,
      resetForm,
      printSupplier,
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
@import "~@core/scss/base/pages/app-inventory.scss";
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
</style></style>
