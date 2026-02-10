<template>
  <b-sidebar
    id="sidebar-edit-inventory"
    :visible="isEditInventorySidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @shown="fetchInventoryData"
    @change="(val) => $emit('update:is-edit-inventory-sidebar-active', val)"
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
            name="Kode Barang"
            rules="required|min:3"
          >
            <b-form-group
              label="Kode Barang"
              label-for="edit-kode-barang"
            >
              <b-form-input
                id="edit-kode-barang"
                v-model="inventory.code"
                trim
                placeholder="GRUP.JENIS.MODEL.SERI"
                :state="getValidationState(validationContext)"
              />
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
            label-for="edit-inventory-name"
          >
            <b-form-input
              id="edit-inventory-name"
              v-model="inventory.name"
              trim
              placeholder="Nama Barang"
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <!-- Address -->
          <validation-provider
            #default="validationContext"
            name="Full Name"
            rules=""
          >
          <b-form-group
            label="Grup"
            label-for="edit-grup-barang"
          >
            <b-form-input
              id="edit-grup-barang"
              v-model="inventory.category"
              placeholder="BAN/BAUT/OLI BBM/LAHAR/PARUTAN/DLL"
              trim
              :state="getValidationState(validationContext)"
            />
          </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Satuan"
            rules="required"
          >

          <!-- Contact -->
          <b-form-group
            label="Satuan"
            label-for="edit-satuan-barang"
          >
            <v-select
              v-model="inventory.unit"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="satuanOptions"
              input-id="edit-satuan-barang"
              class="satuan-selector"
              :clearable="false"
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
    prop: 'isAEditInventorySidebarActive',
    event: 'update:is-edit-inventory-sidebar-active',
  },
  props: {
    editid: {
      required: true,
    },
    isEditInventorySidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  methods: {
  },
  setup(props, { emit }) {
    const fetchInventoryData = () => {
      store.dispatch('app-inventory/fetchInventoryEdit', { id: props.editid })
        .then((response) => {
          inventory.value = response.data.payload
        })
    }
    const blankInventory = ref({
      code: '',
      name: '',
      category: '',
      unit: '',
    })
    const satuanOptions = ref([])
    store.dispatch('app-inventory/listFormData')
      .then((response) => {
        satuanOptions.value = response.data.payload.satuan
      })
    
    const inventory = ref(JSON.parse(JSON.stringify(blankInventory)))
    const resetinventory = () => {
      inventory.value = JSON.parse(JSON.stringify(blankInventory))
    }
    const onSubmit = () => {
      store.dispatch('app-inventory/updateInventory', { id: props.editid, inventory: inventory.value})
        .then(() => {
          emit('refetch-data')
          emit('update:is-edit-inventory-sidebar-active', false)
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
      onSubmit,
      required,
      alphaNum,
      email,
      min,

      fetchInventoryData,
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
