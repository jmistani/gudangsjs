<template>
  <b-sidebar
    id="sidebar-add-new-journal"
    :visible="isAddNewAccountSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-new-journal-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Account
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
            name="Kode Akun / Tiket"
            rules="required"
          >
          <b-form-group
            label="Kode Akun / Tiket"
            label-for="code-journal"
          >
            <b-form-input
              id="code-journal"
              v-model="journal.code"
              trim
              placeholder="Kode"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>
          <validation-provider
            #default="validationContext"
            name="Nama Akun / Tiket"
            rules="required"
          >
          <b-form-group
            label="Nama Akun / Tiket"
            label-for="name-journal"
          >
            <b-form-input
              id="name-journal"
              v-model="journal.name"
              trim
              placeholder="Nama"
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
    prop: 'isAddNewAccountSidebarActive',
    event: 'update:is-add-new-journal-sidebar-active',
  },
  props: {
    isAddNewAccountSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  setup(props, { emit }) {
    const blankAccount = ref({
      name: '',
    })
    
    const journal = ref(JSON.parse(JSON.stringify(blankAccount)))
    const resetjournal = () => {
      journal.value = JSON.parse(JSON.stringify(blankAccount))
    }
    const onSubmit = () => {
      journal.value.type = 'expense'
      store.dispatch('app-inventory/addAccount', journal.value)
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-new-journal-sidebar-active', false)
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetjournal)

    return {
      journal,
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
