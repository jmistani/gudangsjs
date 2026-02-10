<template>
  <b-sidebar
    id="sidebar-edit-inventory"
    :visible="isEditBankSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @shown="fetchBankData"
    @change="(val) => $emit('update:is-edit-bank-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Edit Barang
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
            rules="required|min:3"
          >
            <b-form-group
              label="Alias Rekening"
              label-for="alias-rekening"
            >
              <b-form-input
                id="alias-rekening"
                v-model="bankaccount.alias"
                trim
                placeholder="Alias Rekening"
                :state="getValidationState(validationContext)"
              />
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Nama Bank"
            rules="required"
          >
          <b-form-group
            label="Nama Bank"
            label-for="edit-bank-name"
          >
            <v-select
              v-model="bankaccount.bankname"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="bankNames"
              input-id="bank-name"
              class="bank-selector"
              :clearable="false"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <!-- Address -->
          <validation-provider
            #default="validationContext"
            name="Nama Rekening"
            rules=""
          >
          <b-form-group
            label="Nama Rekening"
            label-for="edit-nama-rekening"
          >
            <b-form-input
              id="edit-nama-rekening"
              v-model="bankaccount.accountname"
              placeholder="Nama Pemilik Rekening"
              trim
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="No Rekening"
            rules="required"
          >

          <!-- Contact -->
          <b-form-group
            label="No Rekening"
            label-for="edit-no-rekening"
          >
              <b-form-input
                id="no-rekening"
                v-model="bankaccount.accountnumber"
                trim
                :state="getValidationState(validationContext)"
              />
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
              Edit
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton,
} from 'bootstrap-vue'
import { required, alphaNum, email, min } from '@validations'
import { ref } from '@vue/composition-api'
import router from '@/router'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import store from '@/store'

export default {
  components: {
    BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
      },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAEditBankAccountSidebarActive',
    event: 'update:is-edit-bank-accountsidebar-active',
  },
  props: {
    editid: {
      required: true,
    },
    isEditBankAccountSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  methods: {
  },
  setup(props, { emit }) {
    const fetchBankData = () => {
      store.dispatch('app-inventory/fetchBankAccountEdit', { id: props.editid })
        .then((response) => {
          bankaccount.value = response.data.payload
        })
    }
    const blankBank = ref({
      alias: '',
      accountname: '',
      bankname: null,
      category: '',
      type: '',
      accountnumber: '',
    })
    const satuanOptions = ref([])
    store.dispatch('app-inventory/listFormData')
      .then((response) => {
        banknames.value = response.data.payload.satuan
      })
    
    const bankaccount = ref(JSON.parse(JSON.stringify(blankBank)))
    const resetbankaccount = () => {
      bankaccount.value = JSON.parse(JSON.stringify(blankBank))
    }
    const onSubmit = () => {
      store.dispatch('app-inventory/updateBank', { id: props.editid, inventory: bankaccount.value})
        .then(() => {
          emit('refetch-data')
          emit('update:is-edit-bank-account-sidebar-active', false)
        })
    }

const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetbankaccount)

    return {
      bankaccount,
      banknames,
      onSubmit,
      required,
      alphaNum,
      email,
      min,

      fetchBankData,
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
