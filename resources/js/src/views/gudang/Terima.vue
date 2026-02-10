<template>
  <b-card>
    <!-- form -->
    <b-form>
      <b-row>
        <!-- tanggal -->
        <b-col cols="12">
          <b-form-group
            label="Tanggal"
            label-for="tanggal"
          >
            <flat-pickr
              id="tanggal"
              class="form-control"
              name="tanggal"
              v-model="localOptions.tanggal"
              placeholder="tanggal"
            />
          </b-form-group>
        </b-col>
        <!--/ tanggal -->

        <!-- supplier -->
        <b-col md="6">
          <b-form-group
            label-for="supplier"
            label="Supplier"
          >
            <v-select
              id="Supplier"
              v-model="localOptions.supplier"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              label="name"
              :options="supplierOption"
            />
          </b-form-group>
        </b-col>
        <!--/ supplier -->

        <!-- supplier -->
        <b-col md="6">
          <b-form-group
            label-for="nobon"
            label="No Bon"
          >
            <b-form-input
              id="nobon"
              v-model="localOptions.nobon"
              placeholder="No Bon"
            />
          </b-form-group>
        </b-col>
        <!--/ supplier -->

      </b-row>
      <b-row>
        <b-col md="12">
          <repeat-items/>
          </b-col>
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BFormTextarea,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import Ripple from 'vue-ripple-directive'
import Cleave from 'vue-cleave-component'
import RepeatItems from './RepeatItems.vue'

// eslint-disable-next-line import/no-extraneous-dependencies
import 'cleave.js/dist/addons/cleave-phone.us'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BCard,
    BFormTextarea,
    vSelect,
    flatPickr,
    Cleave,
    RepeatItems
  },
  directives: {
    Ripple,
  },
  props: {
    informationData: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      countryOption: ['USA', 'India', 'Canada'],
      localOptions: {
        tanggal: '',
        supplier: '',
        nobon: ''
      },
      clevePhone: {
        phone: true,
        phoneRegionCode: 'US',
      },
    }
  },
  methods: {
    resetForm() {
      this.localOptions = JSON.parse(JSON.stringify(this.informationData))
    },
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
</style>
