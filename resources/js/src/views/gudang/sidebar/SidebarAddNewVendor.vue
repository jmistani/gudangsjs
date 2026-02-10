<template>
  <b-sidebar
    id="sidebar-add-new-vendor"
    :visible="isAddNewVendorSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-vendor-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Vendor
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
            name="Nama Vendor"
            rules="required"
          >
          <b-form-group
            label="Nama Vendor"
            label-for="name-vendor"
          >
            <b-form-input
              id="name-vendor"
              v-model="vendor.name"
              trim
              placeholder="Nama"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Alamat"
            rules="required"
          >
          <b-form-group
            label="Alamat"
            label-for="address-vendor"
          >
            <b-form-textarea
              id="address-vendor"
              v-model="vendor.address"
              placeholder="Alamat"
              trim
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Kontak"
            rules=""
          >

          <b-form-group
            label="Kontak"
            label-for="contact-vendor"
          >
            <b-form-input
              id="contact-vendor"
              v-model="vendor.contact"
              trim
              placeholder="Kontak"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Email"
            rules=""
          >

          <b-form-group
            label="Email"
            label-for="email-vendor"
          >
            <b-form-input
              id="email-vendor"
              v-model="vendor.email"
              trim
              placeholder="Email"
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton,
} from 'bootstrap-vue'
import { required, alphaNum, email, min } from '@validations'
import { ref } from '@vue/composition-api'
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
    prop: 'isAddNewVendorSidebarActive',
    event: 'update:is-add-new-vendor-sidebar-active',
  },
  props: {
    isAddNewVendorSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  setup(props, { emit }) {
    const blankVendor = ref({
      name: '',
      address: '',
      contact: ''
    })
    
    const vendor = ref(JSON.parse(JSON.stringify(blankVendor)))
    const resetvendor = () => {
      vendor.value = JSON.parse(JSON.stringify(blankVendor))
    }
    const onSubmit = () => {
      store.dispatch('app-inventory/addVendor', vendor.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-vendor-sidebar-active', false)
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetvendor)

    return {
      vendor,
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
