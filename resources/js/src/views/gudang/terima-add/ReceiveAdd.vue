<template>
  <section class="receive-add-wrapper">
      <form-wizard
        ref="form"
        color="#7367F0"
        :title="null"
        :subtitle="null"
        shape="square"
        finish-button-text="Submit"
        back-button-text="Previous"
        class="mb-3"
        @on-complete="receiveInventory"
      >
        <tab-content
          title="Detail Penerimaan"
          :before-change="validationFormDetail"
        >
          <ValidationObserver 
            ref="DetailRules"
          >
            <b-row class="receive-add">
              <b-col
                cols="12"
                xl="12"
              >
                <b-card
                  no-body
                >

                  <b-card-body class="pb-0">

                    <b-row>
                      <b-col cols="12">
                        <b-button
                          id="popover-button-default"
                          variant="flat-success"
                          class="btn-icon"
                        >
                          <feather-icon icon="HelpCircleIcon" />
                        </b-button>
                        
                        <b-popover
                          target="popover-button-default"
                          variant="success"
                          triggers="focus"
                          placement="top"
                        >
                          <template #title>
                            <span>Bantuan</span>
                          </template>
                          <span class="helptext">{{refHelpText}}</span>
                        </b-popover>
                      </b-col>
                    </b-row>
                    <b-row class="receive-spacing">

                      <!-- Col: Invoice To -->
                      <b-col
                        cols="12"
                        xl="6"
                        class="mb-lg-1"
                      >
                        <b-form-input style="display: none;" v-model="receiveData.type"/>
                          <ValidationProvider
                            #default="validationContext"
                            ref="receive-add-vendor"
                            name="receive-add-vendor"
                            :rules="{ requiredVendor: true }"
                          >
                            <b-form-group
                              label="Vendor"
                              label-for="receive-data-vendor-id"
                              id="receive-add-vendor"
                              :state="getValidationState(validationContext)"
                            >
                              <v-select
                                v-model="receiveData.vendor"
                                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                                :options="refVendorOptions"
                                label="name"
                                input-id="receive-data-vendor-id"
                                :clearable="false"
                                class="select-option-capitalize"
                                @change="SyncVendorInput($event.target.selectedValue)"
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
                              <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                                {{ validationContext.errors[0] }}
                              </b-form-invalid-feedback>
                          </b-form-group>
                        </ValidationProvider>
                      </b-col>
                      <b-col  cols="12"
                        xl="6">
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
                    </b-row>
                    <b-row class="receive-spacing">
                      <!-- Header: Right Content -->
                      <b-col
                        cols="12"
                        xl="4"
                        class="mt-xl-0 mt-1 justify-content d-xl-flex d-block"
                      >
                        <div class="receive-number-date mt-md-0 mt-1">
                          <div class="d-flex align-items-center mb-0">
                          <ValidationProvider
                            #default="validationContext"
                            ref="receive-add-code"
                            name="receive-add-code"
                            :rules="{ requiredCode: true }"
                          >
                            <b-form-group 
                              label="Kode" 
                              label-for="receive-add-code-input" 
                              :state="getValidationState(validationContext)">
                              <b-input-group id="receive-add-code-input-group" class="input-group-merge receive-edit-input-group">
                                <b-input-group-prepend is-text>
                                  <feather-icon icon="HashIcon" />
                                </b-input-group-prepend>
                                <b-form-input
                                  id="receive-add-code-input"
                                  v-model="receiveData.receive_code"
                                />
                              </b-input-group>
                              </b-form-group>
                            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </ValidationProvider>
                          </div>
                        </div>
                      </b-col>
                      <b-col
                        cols="12"
                        xl="4"
                        class="mt-xl-0 mt-1 justify-content d-xl-flex d-block"
                      >
                        <div class="receive-number-date mt-md-0 mt-1">
                        <div class="d-flex align-items-center mt-0 mb-0">
                          <ValidationProvider
                            #default="validationContext"
                            ref="receive-add-date"
                            name="receive-add-date"
                            :rules="{ requiredDate: true } "
                          >
                            <b-form-group 
                              label="Tanggal"
                              label-for="receive-add-date-input"
                              id="receive-add-date" 
                              :state="getValidationState(validationContext)">
                            <flat-pickr
                              id="receive-add-date-input"
                              v-model="receiveData.post_date"
                              :config="flatpickrConfig"
                              class="form-control receive-edit-input"
                            />
                            </b-form-group>
                            <b-form-invalid-feedback 
                              class="text-custom-error" 
                              :state="validationContext.errors.length > 0 ? false:null">
                              {{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                          </ValidationProvider>
                        </div>
                        </div>
                      </b-col>
                      <b-col
                        cols="12"
                        xl="4"
                        class="mt-xl-0 mt-1 d-xl-flex d-block"
                      >
                        <div class="receive-number-date mt-md-0 mt-1">
                          <div class="d-flex align-items-center mt-0 mb-0">
                            <ValidationProvider
                              id="receive-add-input-staff-gudang"
                              ref="receive-add-input-staff-gudang"
                              :rules=" { requiredStaff: true } "
                              #default="validationContext"
                            >
                              <b-form-group 
                                label="Diterima"
                                label-for="receive-add-staff-gudang-input"
                                id="receive-add-input-staff-gudang" 
                                :state="getValidationState(validationContext)">
                                <b-form-input
                                  id="receive-add-input-staff-gudang-input"
                                  v-model="receiveData.receiver"
                                  placeholder="Nama Staff Gudang"
                                  class="form-control receive-edit-input"
                                />
                              </b-form-group> 
                              <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                                {{ validationContext.errors[0] }}
                              </b-form-invalid-feedback>
                            </ValidationProvider>
                          </div>
                        </div>
                      </b-col>
                    </b-row>
                  </b-card-body>
                  <hr class="receive-spacing mx-0 my-0 px-0 py-0">

                  <b-card-body class="pb-0 form-add-stock">
                    <b-row class="pb-0">
                      <b-col cols="12">
                        <div class="flex-grow-1">
                          <b-row class="flex-grow-1 px-0 receive-items-header">
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
                              Jumlah
                            </b-col>
                            <b-col
                              cols="12"
                              lg="1"
                            >
                              Satuan
                            </b-col>
                            <b-col
                              cols="12"
                              lg="2"
                            >
                              Harga
                            </b-col>
                            <b-col
                              cols="12"
                              lg="3"
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
                                <b-form-input style="display: none;" v-model="dataMeta.numOfValidItems" type="number" class="form-control"></b-form-input>
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
                  <b-card-body class="receive-padding pb-0 form-add-stock receive-items-section">
                    <div
                      ref="form"
                      class="repeater-form form-add-stock"
                    >
                      <b-row
                        v-for="(item, index) in receiveData.items"
                        :key="index"
                        ref="row"
                        class="pb-1"
                      >
                        <b-col cols="12">
                          <div class="d-flex border rounded py-0 px-0 receive-item-card" border="1">
                            <b-row class="flex-grow-1 px-0">
                              <!-- Single Item Form Headers -->
                              <b-col
                                cols="12"
                                lg="4"
                              >
                                <ValidationProvider
                                  #default="validationContext"
                                  :ref="`inventory-receive-inventory-input-${index}`"
                                  :name="`inventory-receive-inventory-input-${index}`"

                                  :rules="{ requiredInventory: true, myRule:[receiveData.items,'inventory','code'] }"
                                >
                                  <b-form-checkbox
                                    :id="`inventory-receive-stock-${index}`"
                                    :ref="`inventory-receive-stock-${index}`"
                                    v-model="item.stock"
                                    :name="`inventory-receive-stock-checkbox-${index}`"
                                    value="true"
                                    unchecked-value="false"
                                    @change="changeVal(index)"
                                  >
                                    Barang di stok
                                  </b-form-checkbox>
                                  <b-form-group
                                    :id="`inventory-receive-inventory-input-${index}`"
                                    :state="getValidationState(validationContext)"
                                  >
                                    <b-form-input 
                                      v-if="item.stock === 'false'"
                                      v-model="item.inventory"
                                      :id="`inventory-receive-inventory-input-${index}`"
                                      :ref="`inventory-receive-inventory-input-${index}`"
                                      placeholder="Nama Barang"
                                    ></b-form-input>
                                    <v-select
                                      v-if="item.stock === 'true'"
                                      :id="`inventory-receive-inventory-select-${index}`"
                                      :ref="`inventory-receive-inventory-select-${index}`"
                                      v-model="item.inventory"
                                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                                      :options="refInventoryOptions"
                                      label="name"
                                      :clearable="false"
                                      class="mb-0 item-selector-title"
                                      placeholder="Select Item"
                                      @input="updateItemForm(index)"
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
                                <ValidationProvider
                                  :ref="`inventory-receive-qty-input-${index}`"
                                  :name="`inventory-receive-qty-input-${index}`"

                                  :rules="{ hargaTerimaBarang:[receiveData.items,'inventory','code',index] }"
                                  #default="validationContext"
                                >
                                <b-form-group
                                  :id="`inventory-receive-qty-input-${index}`"
                                  :state="getValidationState(validationContext)"
                                >                         
                                <vue-numeric
                                  v-model="item.qty"
                                  :precision="2"
                                  :minus="false"
                                  size="sm"
                                  class="mx-0 my-0 px-0 py-0 form-control"
                                  @change="updatesubtotal(index)"
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
                                <span v-if="item.unit && item.stock == 'true'">
                                  {{item.unit}}
                                </span>
                                <span v-if="item.stock == 'false'">
                                  <ValidationProvider
                                    :ref="`inventory-receive-unit-input-${index}`"
                                    :name="`inventory-receive-unit-input-${index}`"

                                    :rules="{ requiredUnit: true }"
                                    #default="validationContext"
                                  >
                                    <b-form-group
                                      :id="`inventory-receive-unit-input-${index}`"
                                      :state="getValidationState(validationContext)"
                                    >
                                      <b-form-input 
                                      v-model="item.unit"
                                      />
                                    </b-form-group>
                                    <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                                      {{ validationContext.errors[0] }}
                                    </b-form-invalid-feedback>
                                </ValidationProvider>
                                </span>
                              </b-col>
                              <b-col
                                cols="12"
                                lg="2"
                              >
                                <ValidationProvider
                                  :ref="`inventory-receive-price-input-${index}`"
                                  :name="`inventory-receive-price-input-${index}`"

                                  :rules="{ hargaTerimaBarang:[receiveData.items,'inventory','code',index] }"
                                  #default="validationContext"
                                >
                                <b-form-group
                                  :id="`inventory-receive-price-input-${index}`"
                                  :state="getValidationState(validationContext)"
                                > 
                                <vue-numeric
                                  v-model="item.price"
                                  :precision="2"
                                  :minus="false"
                                  currency="Rp."
                                  size="sm"
                                  class="mx-0 my-0 px-0 py-0 form-control"
                                  @change="updatesubtotal(index)"
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
                                <p class="mb-0">
                                  <vue-numeric
                                    v-model="item.subtotal"
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
                            <div class="d-flex flex-column justify-content-between border-left py-1 px-1 receive-item-actions">
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

                
                  <b-card-body class="receive-padding pb-0">
                    <b-row>

                      <b-col
                        cols="12"
                        md="6"
                        class="mt-md-0 mt-3 d-flex align-items-center"
                        order="2"
                        order-md="1"
                      >
                      </b-col>

                      <!-- Col: Total -->
                      <b-col
                        cols="12"
                        md="12"
                        class="mt-4 mx-0 d-flex justify-content-end"
                      >
                        <div class="receive-total-wrapper">

                          <hr class="my-50">
                          <div class="receive-total-item">
                            <p class="receive-total-title">
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
                  <hr class="receive-spacing">

                  <b-card-body class="receive-padding pt-0">
                    <span class="font-weight-bold">Keterangan: </span>
                    <b-form-textarea v-model="receiveData.note" />
                  </b-card-body>
                </b-card>
              </b-col>
            </b-row>
          </ValidationObserver>
        </tab-content>
        <tab-content
          title="Pembiayaan Non Stok"
          :before-change="validationFormBiaya"
        >
          <ValidationObserver
            ref="BiayaRules"
          >
            <b-card>
              <b-card-body>
                  <b-row>
                    <b-col cols="4" class="text-center">
                      <h5>Barang</h5>                         
                    </b-col>
                    <b-col cols="8" class="text-center">
                      <h5>Jurnal</h5>
                    </b-col>
                  </b-row>
                  <b-row
                    v-for="(item, index) in receiveData.items"
                    :key="index"
                    ref="row"
                    class="pb-1"
                   >
                    <b-col cols="4" v-if="item.stock != 'true'" class="align-bottom">
                      {{item.inventory}}                         
                    </b-col>
                    <b-col cols="8">
                      <ValidationProvider
                            #default="validationContext"
                            ref="receive-add-nonstock-account"
                            name="receive-add-nonstock-account"
                            :rules="{ requiredAccount: true }"
                      >
                        <v-select
                          v-if="item.stock != 'true'"
                          :id="`inventory-receive-inventory-nonstock-account-${index}`"
                          :ref="`inventory-receive-inventory-nonstock-account-${index}`"
                          v-model="item.account"
                          :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                          :options="refAccountOptions"
                          :reduce="refAccountOptions => refAccountOptions.name"
                          label="name"
                          :clearable="false"
                          class="mb-0 item-selector-title"
                          placeholder="Select Item"
                        >
                        </v-select>
                        <b-form-input
                          rules="required"
                          :id="`inventory-receive-inventory-nonstock-account-input-${index}`"
                          :ref="`inventory-receive-inventory-nonstock-account-input-${index}`"
                          v-model="item.account"
                          style="display:none;"
                          :state="getValidationState(validationContext)"
                        />
                        <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                          {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                      </ValidationProvider> 
                    </b-col>
                  </b-row>
              </b-card-body>

            </b-card>
          </ValidationObserver>
        </tab-content>
    </form-wizard>
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
import { ref, onUnmounted, computed } from '@vue/composition-api'
import { heightTransition } from '@core/mixins/ui/transition'
import Ripple from 'vue-ripple-directive'
import { FormWizard, TabContent } from 'vue-form-wizard'
import store from '@/store'
import {
  BRow, BCol, BCard, BCardBody, BButton, BCardText, BForm, BFormGroup, BFormInput, BInputGroup,
  BInputGroupPrepend, BFormTextarea, BFormCheckbox, VBToggle, BFormInvalidFeedback,
  VBPopover, BPopover
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import gudangStoreModule from '../gudangStoreModule'
import SidebarAddNewVendor from '../sidebar/SidebarAddNewVendor.vue'
import SidebarAddNewInventory from '../sidebar/SidebarAddNewInventory.vue'
import useInventoryAdd from './useInventoryAdd'
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate'
import { required, email, url, myRule, myRule2, numOfItemTerimaBarang, requiredVendor, requiredUnit,
       requiredInventory, requiredInv, requiredStaff, requiredCode, requiredDate } from '@validationsInventory/receive-add.js'
import formValidation from '@core/comp-functions/forms/form-validation'
import MoneyFormat from 'vue-money-format'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
export default {
  components: {
    FormWizard,
    TabContent,
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
    flatPickr,
    vSelect,
    Logo,
    SidebarAddNewVendor,
    SidebarAddNewInventory,
    ValidationProvider,
    ValidationObserver,
    MoneyFormat,
    ToastificationContent,
    VBPopover,
    BPopover,
  },
  directives: {
    Ripple,
    'b-popover': VBPopover,
    'b-toggle': VBToggle,

  },
  data() {
    return {
      
      flatpickrConfig: {
        dateFormat: "d.m.Y",
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
    validationFormDetail() {
      return new Promise((resolve, reject) => {
        this.$refs.DetailRules.validate().then(success => {
          if (success) {
            if(this.receiveData.items.filter(item => item.stock === "false").length > 0) {
              this.refNonStockExists = true
            }
            else {
              this.refNonStockExists = false
            }
            resolve(true)
          } else {
            reject()
          }
        })
      })
    },
    validationFormBiaya() {
      return new Promise((resolve, reject) => {
        if(this.refNonStockExists) {
            this.$refs.BiayaRules.validate().then(success => {
              if (success) {
                resolve(true)
              } else {
                reject()
              }
            })
        }
        else
        {
          resolve(true)
        }
      })
    },
    updatesubtotal(index) {
      this.receiveData.items[index].subtotal = this.receiveData.items[index].qty * this.receiveData.items[index].price
    },
    changeVal(index) {
      this.receiveData.items[index].stock = this.$refs[`inventory-receive-stock-${index}`][0].checked
      this.receiveData.items[index].inventory = null
      this.receiveData.items[index].qty = 0
      this.receiveData.items[index].unit = ''