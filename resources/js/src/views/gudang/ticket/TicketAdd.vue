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

                <!-- Col: Invoice To -->
                <b-col
                  cols="12"
                  md="12"
                  class=""
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="ticket-add-name"
                    name="ticket-add-name"
                    :rules="{ requiredNamaTiket: true }"
                    mode="lazy"
                  >
                  <b-form-group
                    label="Nama"
                    label-for="ticket-add-input-name"
                    id="ticket-add-name"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      id="ticket-add-input-name"
                      v-model="ticketData.name"
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
                  xl="12"
                  class=""
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="ticket-add-name"
                    name="ticket-add-name"
                    :rules="{ requiredTipe: true }"
                  >
                      <b-form-input type="text" 
                        id="ticket-add-name"
                        class="form-control disabled" 
                        name="expirationMonth" 
                        v-model="ticketData.name" 
                        rules="required" 
                        style="display:none;"
                        >
                      </b-form-input>                    
                      <b-form-group 
                      label="Jenis"
                      label-for="ticket-add-input-type"
                      id="ticket-add-input-type" 
                      :state="getValidationState(validationContext)"
                      >
                        <v-select
                          v-model="ticketData.type"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="typeOptions"
                          label="name"
                          input-id="ticket-add-type-id"
                          :clearable="false"
                          :reduce="typeOptions=>typeOptions.name"
                          class="select-option-capitalize"
                        >
                        <template #option="{ name }">
                              <!-- HTML that describe how select should render items when the select is open -->
                          {{ name | titlecase }} 
                        </template>
                        <!-- <template #search="{attributes, events}">
                          <input
                            class="vs__search"
                            :required="!ticketData.typeUI"
                            v-bind="attributes"
                            v-on="events"
                          />
                        </template> -->
                      </v-select>
                    </b-form-group>
                    <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                      {{ validationContext.errors[0] }}
                    </b-form-invalid-feedback>
                  </ValidationProvider>
                  

                </b-col>
              </b-row>
              <b-row v-if="ticketData.type ==='kendaraan'">
                <b-col
                  cols="12"
                  xl="12"
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="ticket-add-nopolisi"
                    name="ticket-add-nopolisi"
                    :rules="{ requiredNopolisi: true }"
                  >
                    <b-form-group
                      label="NoPolisi"
                      label-for="ticket-add-input-nopolisi"
                      id="ticket-add-nopolisi"
                      :state="getValidationState(validationContext)"
                    >
                      <v-select
                        id="ticket-add-nopolisi"
                        v-model="ticketData.nopolisi"
                        :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                        :options="nopolisiOptions"
                        label="name"
                        input-id="ticket-add-nopolisi-id"
                        :reduce="nopolisiOptions=>nopolisiOptions.name"
                        :clearable="false"
                        class="select-option-capitalize"
                      >
                          <template #option="{ name }">
                                <!-- HTML that describe how select should render items when the select is open -->
                            {{ name | capital }} 
                          </template>
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
                  xl="12"
                >
                  <ValidationProvider
                    #default="validationContext"
                    ref="ticket-add-keterangan"
                    name="ticket-add-keterangan"
                    :rules="{ requiredKeterangan: true }"
                  >
                    <b-form-group
                      label="Keterangan"
                      label-for="ticket-add-input-keterangan"
                      id="ticket-add-keterangan"
                      :state="getValidationState(validationContext)"
                    >
                      <b-form-textarea v-model="ticketData.keterangan"/>
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
                  name="type-ticket"
                  value=""
                  selected
                  plain
                  class="radio-ticket-type-filter"
                >
                  Semua 
                </b-form-radio>
                <b-form-radio
                  v-model="typeFilter"
                  name="type-ticket"
                  value="pabrik"
                  plain
                  class="radio-ticket-type-filter"
                >
                  Pabrik
                </b-form-radio>
                <b-form-radio
                  v-model="typeFilter"
                  name="type-ticket"
                  value="kendaraan"
                  plain
                  class="radio-ticket-type-filter"
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
                  ref="refTicketTable"
                  :items="fetchAllTickets"
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
                      :to="{ name: 'ticket-view', params: { id: data.item.id }}"
                      class="font-weight-bold"
                    > -->
                      <span v-if="data.item.name" class="text-truncate overflow-hidden">
                            {{ data.item.name }}
                      </span>
                      <span v-else class="warning">*kosong*</span>
                    <!-- </b-link> -->
                  </template>

                  <template #cell(type)="data">
                    <feather-icon
                      icon="TruckIcon"
                      size="18" v-if="data.item.type === 'kendaraan'"/>
                    <feather-icon
                      icon="TrelloIcon"
                      size="18" v-if="data.item.type === 'pabrik'"/>
                    <span class="align-text-top text-capitalize">{{ data.item.type }}</span>
                  </template>

                  <template #cell(nopolisi)="data">
                    <span v-if="data.item.nopolisi">{{ data.item.nopolisi }}</span>
                    <span v-else class="warning">-</span>
                  </template>

                  <template #cell(status)="data">
                    <span 
                      v-if="data.item.status == 'open'"
                      class="text-primary"
                    >
                      {{ "berlangsung" | lowercase }}
                    </span>
                    <span 
                      v-if="data.item.status == 'closed'"
                      class="text-success"
                    >
                      {{ "selesai" | lowercase }}
                      <feather-icon
                      icon="CheckCircleIcon"
                      :to="{ name: 'inventory-ticket-view', params: { id: data.item.id }  }"
                      class="cursor-pointer"
                      size="18" />
                    </span>
                  </template>

                  <template #cell(actions)="data">
                    <b-link
                      :to="{ name: 'inventory-ticket-view', params: { id: data.item.id }  }"
                    >
                      <feather-icon
                      icon="AlignJustifyIcon"
                      :to="{ name: 'inventory-ticket-view', params: { id: data.item.id }  }"
                      class="cursor-pointer"
                      size="18" />
                    </b-link>
                    <span 
                      v-if="data.item.status == 'open'"
                      class="text-success"
                    >
                      <feather-icon
                      icon="CheckSquareIcon"
                      @click="closeTicket(data.item.id)"
                      class="cursor-pointer"
                      size="18" />
                    </span>
                    <span 
                      class="text-secondary"
                    >
                      <b-link
                        :to="{ name: 'display-ticket', params: { id: data.item.id }  }"
                      >
                      <feather-icon
                      icon="PrinterIcon"
                      class="cursor-pointer"
                      size="18" 
                      :to="{ name: 'display-ticket', params: { id: data.item.id }  }"
                      />
                      </b-link>
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
                  :total-rows="totalTickets"
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
  BRow, BCol, BFormRadioGroup, BFormRadio, BCard, BCardBody, BButton, BLink, 
  BCardText, BForm, BFormGroup, BFormInput, BInputGroup, BTable, BPagination,
  BInputGroupPrepend, BFormTextarea, BFormCheckbox, BPopover, VBToggle, BFormInvalidFeedback
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import gudangStoreModule from '../gudangStoreModule'
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate'
import MoneyFormat from 'vue-money-format'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, onUnmounted, computed, watch } from '@vue/composition-api'
import {  requiredNamaTiket, requiredKeterangan, requiredNama, requiredTipe, requiredNopolisi } from '@validationsInventory/ticket-add.js'
import formValidation from '@core/comp-functions/forms/form-validation'

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
    requiredNamaTiket,
    requiredNama,
    requiredTipe,
    requiredNopolisi,
    requiredKeterangan,
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
  },
  created() {
  },
  destroyed() {
  },
  methods: {
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

    const refTicketTable = ref(null)

    const tableColumns = ref([
        { key: 'name', label: 'Nama', sortable: true },
        { key: 'type', label: 'Tipe', sortable: true },
        { key: 'nopolisi', label: 'No Polisi', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'actions', label: 'Actions', sortable: false },
    ])

    const typeOptions = ref([
      { name: 'pabrik', value: 'pabrik', disabled: false },
      { name: 'kendaraan', value: 'kendaraan', disabled: false },
    ])

    const nopolisiOptions = ref([
      { name: 'BK 9090 LL' },
      { name: 'BK 8501 BT' },
    ])

    const perPage = ref(20)
    const totalTickets = ref(0)
    const currentPage = ref(1)
    const perPageOptions = [10, 25, 50, 100]
    const sortBy = ref('id')
    const isSortDirDesc = ref(true)
    const searchQuery = ref('')
    const typeFilter = ref('')

  const dataMeta = computed(() => {
      const localItemsCount = refTicketTable.value ? refTicketTable.value.localItems.length : 0
      return {
          from: perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
          to: perPage.value * (currentPage.value - 1) + localItemsCount,
          of: totalTickets.value,
      }
  })
  const blankTicket = ref({
      name: '',
      type: '',
      nopolisi: '',
      status: 'berlangsung',
  })
  
  const ticketData = ref(JSON.parse(JSON.stringify(blankTicket)))

  const resetTicket = () => {
    ticketData.value = JSON.parse(JSON.stringify(blankTicket))
  }

  const onSubmit = () => {
    store.dispatch('app-inventory/addTicket', ticketData.value)
      .then(() => {
        refetchData()
        resetForm()
      })
  }

  const closeTicket = (ticketID) => {
    if (confirm("Yakin akan meyelesaikan tiket?")) {
      store.dispatch('app-inventory/closeTicket', ticketID)
        .then(() => {
          refetchData()
        })
    }
  }

  const printTicket = (ticketID) => {
    console.log(ticketID)
  }

  const refetchData = () => {
      refTicketTable.value.refresh()
  }

  watch([currentPage, perPage, searchQuery], () => {
      refetchData()
  })

const fetchAllTickets = (ctx, callback) => {
    store
      .dispatch('app-inventory/fetchAllTickets', {
          q: searchQuery.value,
          perPage: perPage.value,
          page: currentPage.value,
          sortBy: sortBy.value,
          sortDesc: isSortDirDesc.value,
      })
      .then((response) => {
          callback(response.data.payload)
          totalTickets.value = response.data.payload.total
      })
      .catch(() => {
          toast({
              component: ToastificationContent,
              props: {
                  title: "Error fetching Tickets' list",
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
    } = formValidation(resetTicket)
    
    return {
      dataMeta,

      ticketData,
      typeOptions,
      nopolisiOptions,
      onSubmit,
      closeTicket,

      currentPage,
      fetchAllTickets,
      totalTickets,
      searchQuery,
      perPage,
      perPageOptions,
      sortBy,
      isSortDirDesc,
      typeFilter,
      refTicketTable,
      tableColumns,
      refFormObserver,
      getValidationState,
      resetForm,
      printTicket,
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
