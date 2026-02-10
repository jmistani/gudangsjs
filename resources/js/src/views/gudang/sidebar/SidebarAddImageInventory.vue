<template>
  <b-sidebar
    id="sidebar-add-image-inventory"
    :visible="isAddImageInventorySidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-image-inventory-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Tambah Gambar
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
          @submit.prevent="handleSubmit(handleImages)"
          @reset.prevent="resetForm"
        >

          <validation-provider
            name="Kode Barang"
            rules="mimes:image/*" 
            v-slot="{ errors, validate }"
          >
              <UploadImages @change="validate"/>
              <span>{{ errors[0] }}</span>
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
import UploadImages from "vue-upload-drop-images"
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
    BSidebar, BForm, BFormGroup, BFormInput, BFormTextarea, BButton, vSelect,  UploadImages,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
      },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddImageInventorySidebarActive',
    event: 'update:is-add-image-inventory-sidebar-active',
  },
  props: {
    editid: {
      required: true,
    },
    isAddImageInventorySidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
  methods:{
      handleImages(files){
          /*
            [
              {
                  "name": "Screenshot from 2021-02-23 12-36-33.png",
                  "size": 319775,
                  "type": "image/png",
                  "lastModified": 1614080193596
                  ...
              },
              ...
              ]
            */
      }
  },
  setup(props, { emit }) {
    const blankImages= ref([])
    const images = ref(JSON.parse(JSON.stringify(blankImages)))
    const resetImages = () => {
      images.value = JSON.parse(JSON.stringify(blankImages))
    }
    const onSubmit = () => {
      store.dispatch('app-inventory/addImage', { id: props.editid, images: images.value })
        .then(() => {
          emit('refetch-data')
          emit('update:is-add-image-inventory-sidebar-active', false)
        })
    }
    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetImages)

    return {
      images,
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
