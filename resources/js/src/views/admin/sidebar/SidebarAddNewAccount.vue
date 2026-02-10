<template>
  <b-sidebar
    id="sidebar-add-new-account"
    :visible="isAddNewAccountSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-account-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Barang
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
            name="Kode"
            :rules="{ uniqueKodeAccount: true }"
          >
            <b-form-group
              label="Kode"
              label-for="kode-account"
            >
              <b-form-input
                id="kode-account"
                v-model="refAccount.code"
                trim
                placeholder="KODE"
                :state="getValidationState(validationContext)"
              />
            <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                {{validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Nama Account"
            rules="required"
          >
          <b-form-group
            label="Nama"
            label-for="account-name"
          >
            <b-form-input
              id="account-name"
              v-model="refAccount.name"
              trim
              placeholder="Nama"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Tipe"
            :rules="{requiredType: true}"
          >

          <!-- Contact -->
          <b-form-group
            label="Tipe"
            label-for="tipe-account"
          >
              <b-form-input
                id="tipe-account"
                v-model="refAccount.type"
                trim
                :state="getValidationState(validationContext)"
                style="display:none;"
              />
            <v-select
              v-model="refAccount.type"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="typeOptions"
              input-id="type-account"
              class="type-selector"
              :clearable="false"
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
              :disabled="refProcessing"
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
import { uniqueKodeAccount, requiredType } from '@core/utils/validations/account/sidebarnewaccount.js'
import { ref } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import store from '@/store'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, vSelect, BFormInvalidFeedback,
    ToastificationContent,
    // Form Validation
    ValidationProvider,
    ValidationObserver,
      },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewAccountSidebarActive',
    event: 'update:is-add-new-account-sidebar-active',
  },
  props: {
    isAddNewAccountSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  setup(props, { emit }) {
    const blankAccount = ref({
      code: '',
      name: '',
      category: '',
      currency: 'IDR',
    })
    const refProcessing = ref(false);
    const toast = useToast()
    const typeOptions = ref([])
    const grupOptions = ref([])
    store.dispatch('app-accounts/listFormData')
      .then((response) => {
        typeOptions.value = response.data.payload.type
      })
    
    const refAccount = ref(JSON.parse(JSON.stringify(blankAccount.value)))
    const resetAccount = () => {
      refAccount.value = JSON.parse(JSON.stringify(blankAccount.value))
    }
    const onSubmit = () => {
      refProcessing.value = true
      store.dispatch('app-accounts/addAccount', refAccount.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-account-sidebar-active', false)
          emit('sidebar-response', true)
          refProcessing.value = false
        })
        .catch(error => {
          refProcessing.value = false
            toast({
              component: ToastificationContent,
              props: {
                title: 'Error',
                icon: 'BellIcon',
                text: 'error',
                variant: 'danger',
              },
            })
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetAccount)

    return {
      refProcessing,
      refAccount,
      typeOptions,
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
  &.type-selector {
    background-color: #fff;

    .dark-layout & {
      background-color: unset;
    }
  }
}


</style>
