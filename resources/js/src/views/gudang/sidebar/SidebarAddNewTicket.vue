<template>
  <b-sidebar
    id="sidebar-add-new-ticket"
    :visible="isAddNewTicketSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-ticket-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Tiket
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
            name="Kode Tiket"
            rules="required"
          >
            <b-form-group
              label="Kode Tiket"
              label-for="code-ticket"
            >
              <b-form-input
                id="code-ticket"
                v-model="ticket.code"
                trim
                placeholder="Kode"
                :state="getValidationState(validationContext)"
                readonly
              />
            </b-form-group>
            <b-form-invalid-feedback :state="validationContext.errors.length > 1" class="text-danger">
                {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
          </validation-provider>
          <validation-provider
            #default="validationContext"
            name="Nama Akun / Tiket"
            rules="required"
          >
            <b-form-group
              label="Nama Akun / Tiket"
              label-for="name-ticket"
            >
              <b-form-input
                id="name-ticket"
                v-model="ticket.name"
                trim
                placeholder="Nama"
                :state="getValidationState(validationContext)"
              />
            </b-form-group>
            <b-form-invalid-feedback :state="validationContext.errors.length > 1" class="text-danger">
                {{ validationContext.errors[0] }}
            </b-form-invalid-feedback>
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
  BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, BFormRadioGroup, BFormInvalidFeedback
} from 'bootstrap-vue'
import { required, alphaNum, email, min } from '@validations'
import { ref } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'
import store from '@/store'

export default {
  components: {
    BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, vSelect, BFormRadioGroup, BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
      },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddNewTicketSidebarActive',
    event: 'update:is-add-new-ticket-sidebar-active',
  },
  props: {
    isAddNewTicketSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  data() {
    return {
    }
  },
  setup(props, { emit }) {
    const prefilledTicket = ref({
        type: 'ticket',
        code: 'TK',
        name: ''
    })
    
    const blankTicket = ref({
        type: '',
        code: '',
        name: ''
    })
    
    const ticket = ref(JSON.parse(JSON.stringify(prefilledTicket.value)))
    const resetTicketForm = () => {
      ticket.value = JSON.parse(JSON.stringify(prefilledTicket.value))
    }
    const clearTicketForm = () => {
      ticket.value = JSON.parse(JSON.stringify(blankTicket.value))
    }
    const onSubmit = () => {
      store.dispatch('app-inventory/addAccount', ticket.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-ticket-sidebar-active', false)
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetTicketForm,clearTicketForm)

    const refTypeOptions =  [
        { item: 'ticket', name: 'Tiket' },
        { item: 'expense', name: 'Akun', notEnabled: true },
      ]

    return {
      ticket,
      onSubmit,
      required,
      alphaNum,
      email,
      min,

      refTypeOptions,
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
