<template>
  <b-sidebar
    id="sidebar-add-new-bank-account"
    :visible="isAddNewInventorySidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-bank-account-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Rek Bank
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />

      </div>

      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Body -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >

          <validation-provider
            #default="validationContext"
            name="Alias Rekening"
            :rules="{ uniqueKodeBarang: true }"
          >
            <b-form-group
              label="Alias Rekening"
              label-for="alias-rekening"
            >
              <b-form-input
                id="alias-rekening"
                v-model="bank.alias"
                trim
                placeholder="Alias Rekening"
                :state="getValidationState(validationContext)"
              />
            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                {{validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Supplier Name -->
          <validation-provider
            #default="validationContext"
            name="Nama Bank"
            rules="required"
          >
          <b-form-group
            label="Nama Bank"
            label-for="bank-name"
          >
            <v-select
              v-model="bank.bankname"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="bankNames"
              input-id="bank-name"
              class="bank-selector"
              :clearable="false"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Nama Rekening"
            rules="required"
          >
          <b-form-group
            label="Nama Rekening"
            label-for="nama-rekening"
          >
              <b-form-input
                id="nama-rekening"
                v-model="bank.accountname"
                placeholder="Nama Pemilik Rekening"
                trim
                :state="getValidationState(validationContext)"
              />
              <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                {{ validationContext.errors[0]}}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="No Rekening"
            :rules="{requiredSatuan: true}"
          >

          <!-- Contact -->
          <b-form-group
            label="No Rekening"
            label-for="no-rekening"
          >
              <b-form-input
                id="no-rekening"
                v-model="bank.accountnumber"
                trim
                :state="getValidationState(validationContext)"
              />
            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
              {{ validationContext.errors[0]}}
            </b-form-invalid-feedback>
          </b-form-group>
          </validation-provider>


          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Add
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              variant="outline-secondary"
              @click="hide"
            >
              Cancel
            </b-button>
          </div>
        </b-form>
      </validation-observer>
    </template>
  </b-sidebar>
</template>

<script>
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import formValidation from '@core/comp-functions/forms/form-validation'
import {
  BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, BFormInvalidFeedback
} from 'bootstrap-vue'
import { required, alphaNum, email, min } from '@validations'
import { uniqueKodeBarang } from '@validationsInventory/sidebarnewbank.js'
import { ref } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import store from '@/store'

export default {
  components: {
    BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, vSelect, BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
      },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewInventorySidebarActive',
    event: 'update:is-add-new-bank-account-sidebar-active',
  },
  props: {
    isAddNewInventorySidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  setup(props, { emit }) {
    const blankInventory = ref({
      code: '',
      name: '',
      category: '',
      unit: '',
    })
    const satuanOptions = ref([])
    const grupOptions = ref([])
    store.dispatch('app-bank-account/listFormData')
      .then((response) => {
        satuanOptions.value = response.data.payload.satuan
        grupOptions.value = response.data.payload.grups
      })
    
    const inventory = ref(JSON.parse(JSON.stringify(blankInventory)))
    const resetinventory = () => {
      bank.value = JSON.parse(JSON.stringify(blankInventory))
    }
    const onSubmit = () => {
      store.dispatch('app-bank-account/addInventory', bank.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-bank-account-sidebar-active', false)
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetinventory)

    return {
      inventory,
      satuanOptions,
      grupOptions,
      onSubmit,
      required,
      alphaNum,
      email,
      min,

      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';

.v-select {
  &.item-selector-title,
  &.bank-selector {
    background-color: #fff;

    .dark-layout & {
      background-color: unset;
    }
  }
}


</style>
