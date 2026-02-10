<template>
  <b-sidebar
    id="sidebar-add-new-inventory"
    :visible="isAddNewInventorySidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-inventory-sidebar-active', val)"
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
            name="Kode Barang"
            :rules="{ uniqueKodeBarang: true }"
          >
            <b-form-group
              label="Kode Barang"
              label-for="kode-barang"
            >
              <b-form-input
                id="kode-barang"
                v-model="refInventory.code"
                trim
                placeholder="GRUP.JENIS.MODEL.SERI"
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
            name="Nama Barang"
            rules="required"
          >
          <b-form-group
            label="Nama Barang"
            label-for="inventory-name"
          >
            <b-form-input
              id="inventory-name"
              v-model="refInventory.name"
              trim
              placeholder="Nama Barang"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <!-- Address -->
          <validation-provider
            #default="validationContext"
            name="grup barang"
            rules="required"
          >
          <b-form-group
            label="Grup"
            label-for="grup-barang"
          >
              <b-form-input
                id="grup-barang"
                v-model="refInventory.category"
                placeholder="BAN/BAUT/OLI BBM/LAHAR/PARUTAN/DLL"
                trim
                :state="getValidationState(validationContext)"
                style="display:none;"
              />
              <v-select
                v-model="refInventory.category"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="grupOptions"
                input-id="grup-barang"
                class="satuan-selector"
                :clearable="false"
                :state="getValidationState(validationContext)"
              />
              <b-form-invalid-feedback class="text-custom-error" :state="validationContext.errors.length > 0 ? false:null">
                {{ validationContext.errors[0]}}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Satuan"
            :rules="{requiredSatuan: true}"
          >

          <!-- Contact -->
          <b-form-group
            label="Satuan"
            label-for="satuan-barang"
          >
              <b-form-input
                id="satuan-barang"
                v-model="refInventory.unit"
                trim
                :state="getValidationState(validationContext)"
                style="display:none;"
              />
            <v-select
              v-model="refInventory.unit"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="satuanOptions"
              input-id="satuan-barang"
              class="satuan-selector"
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
import { uniqueKodeBarang } from '@validationsInventory/sidebarnewinventory.js'
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
    prop: 'isAddNewInventorySidebarActive',
    event: 'update:is-add-new-inventory-sidebar-active',
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
    const refProcessing = ref(false);
    const toast = useToast()
    const satuanOptions = ref([])
    const grupOptions = ref([])
    store.dispatch('app-inventory/listFormData')
      .then((response) => {
        satuanOptions.value = response.data.payload.satuan
        grupOptions.value = response.data.payload.grups
      })
    
    const refInventory = ref(JSON.parse(JSON.stringify(blankInventory.value)))
    const resetInventory = () => {
      refInventory.value = JSON.parse(JSON.stringify(blankInventory.value))
    }
    const onSubmit = () => {
      refProcessing.value = true
      store.dispatch('app-inventory/addInventory', refInventory.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-inventory-sidebar-active', false)
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
    } = formValidation(resetInventory)

    return {
      refProcessing,
      refInventory,
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
  &.satuan-selector {
    background-color: #fff;

    .dark-layout & {
      background-color: unset;
    }
  }
}


</style>
