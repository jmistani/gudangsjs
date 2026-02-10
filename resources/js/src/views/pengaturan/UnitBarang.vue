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
                    ref="unit-add-name"
                    name="unit-add-name"
                    :rules="{ requiredUnitBarang : true }"
                  >
                  <b-form-group
                    label="Nama"
                    label-for="unit-add-input-name"
                    id="unit-add-name"
                    :state="getValidationState(validationContext)"
                  >
                    <b-form-input
                      id="unit-add-input-name"
                      v-model="unitData.name"
                    />
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
          <b-card-body class="pb-2">
            <b-row>
              <b-col
                cols="12"
                md="4"
                class="d-flex align-items-center justify-content-center justify-content-sm-start"
              >                    
                <b-table
                  ref="refUnitList"
                  :items="fetchAllUnits"
                  striped
                  responsive
                  primary-key="id"
                  :fields="tableColumns"
                  show-empty
                  empty-text="No matching records found"
                >
                  <template #cell(name)="data">
                      <span>{{ data.item.name | titlecase }}</span>
                      <feather-icon
                        class="text-danger cursor-pointer"
                        icon="XIcon"
                        @click="removeUnit(data.item.id)"
                      />
                  </template>
                </b-table>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import Ripple from 'vue-ripple-directive'
import store from '@/store'
import {
  BRow, BCol, BCard, BCardBody, BButton, BLink, 
  BCardText, BForm, BFormGroup, BFormInput,  BTable, BPagination,
  BFormTextarea, BFormCheckbox, BPopover, VBToggle, BFormInvalidFeedback
} from 'bootstrap-vue'
import pengaturanStoreModule from './pengaturanStoreModule'
import { ValidationProvider, ValidationObserver, extend } from 'vee-validate'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import { ref, onUnmounted, computed, watch } from '@vue/composition-api'
import { requiredUnitBarang } from '@validationsInventory/setting-unit-barang.js'
import formValidation from '@core/comp-functions/forms/form-validation'
import { useToast } from 'vue-toastification/composition'

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
    BFormTextarea,
    BFormCheckbox,
    BPopover,
    BFormInvalidFeedback,
    BTable,
    BLink,
    ValidationProvider,
    ValidationObserver,
    ToastificationContent,
    requiredUnitBarang,
    BPagination,
  },
  directives: {
    Ripple,
    'b-toggle': VBToggle,

  },
  data() {
    return {
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
    const toast = useToast()

    const PENGATURAN_APP_STORE_MODULE_NAME = 'app-pengaturan'

    // Register module
    if (!store.hasModule(PENGATURAN_APP_STORE_MODULE_NAME)) {
      store.registerModule(PENGATURAN_APP_STORE_MODULE_NAME, pengaturanStoreModule)
    }
    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(PENGATURAN_APP_STORE_MODULE_NAME)) store.unregisterModule(PENGATURAN_APP_STORE_MODULE_NAME)

    })

    const refUnitList = ref(null)



    const blankUnit = ref({
        name: '',
    })
  
    const unitData = ref(JSON.parse(JSON.stringify(blankUnit)))

    const resetUnit = () => {
      unitData.value = JSON.parse(JSON.stringify(blankUnit))
    }

    const onSubmit = () => {
      store.dispatch('app-pengaturan/addUnit', unitData.value)
        .then(() => {
          refetchData()
          resetForm()
        })
    }

    const removeUnit = (id) => {
      if (confirm("Yakin akan menghapus grup?")) {
        store.dispatch('app-pengaturan/removeUnit', {id})
          .then(() => {
            refetchData()
          })
      }
    }

    const refetchData = () => {
        refUnitList.value.refresh()
    }
    
    const fetchAllUnits = (ctx, callback) => {
      store
        .dispatch('app-pengaturan/fetchAllUnits', {
        })
        .then((response) => {
            callback(response.data.payload)
        })
        .catch(() => {
            toast({
              component: ToastificationContent,
              props: {
                  title: "Error pengambilan data",
                  icon: 'AlertTriangleIcon',
                  variant: 'danger',
              },
          })
      })
    }
    const tableColumns = [
        { key: 'name', label: 'Unit', sortable: false },
    ]
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetUnit)
    
    return {
      unitData,
      onSubmit,
      removeUnit,
      fetchAllUnits,
      tableColumns,

      refUnitList,
      refFormObserver,
      getValidationState,
      resetForm,
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
