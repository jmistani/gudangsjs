<template>
    <div>
      <b-form
        ref="form"
        :style="{height: trHeight}"
        class="repeater-form"
        @submit.prevent="repeateAgain"
      >

        <!-- Row Loop -->
        <b-row
          v-for="(item, index) in items"
          :id="item.id"
          :key="item.id"
          ref="row"
        >

          <!-- Item Name -->
          <b-col md="3">
            <b-form-group
              label="Item Name"
              label-for="item-name"
            >
              <b-form-input
                id="item-name"
                type="text"
                placeholder="Nama Barang"
              />
            </b-form-group>
          </b-col>

          <!-- Cost -->
          <b-col md="1">
            <b-form-group
              label="Jumlah"
              label-for="jumlah"
            >
              <b-form-input
                id="jumlah"
                type="number"
                placeholder="0"
              />
            </b-form-group>
          </b-col>
          <!-- Jumlah -->
          <b-col md="2">
            <b-form-group
              label="Satuan"
              label-for="satuan"
            >
              <b-form-input
                id="satuan"
                type="text"
                placeholder="BH/LSN/KG/LITER/METER"
              />
            </b-form-group>
          </b-col>

          <!-- Harga -->
          <b-col md="1">
            <b-form-group
              label="Harga"
              label-for="harga"
            >
              <b-form-input
                id="harga"
                type="number"
                decimal="2"
                placeholder="0"
              />
            </b-form-group>
          </b-col>

          <!-- Total -->
          <b-col
            md="1"
          >
            <b-form-group
              label="Total"
              label-for="total"
            >
              <b-form-input
                id="total"
                value="0"
                plaintext
              />
            </b-form-group>
          </b-col>

          <!-- Remove Button -->
          <b-col
            md="2"
            class="mb-50"
          >
            <b-button
              v-ripple.400="'rgba(234, 84, 85, 0.15)'"
              variant="outline-danger"
              class="mt-0 mt-md-2"
              @click="removeItem(index)"
            >
              <feather-icon
                icon="XIcon"
                class="mr-25"
              />
              <span>Delete</span>
            </b-button>
          </b-col>
          <b-col cols="12">
            <hr>
          </b-col>
        </b-row>

      </b-form>
    <b-button
      v-ripple.400="'rgba(255, 255, 255, 0.15)'"
      variant="primary"
      @click="repeateAgain"
    >
      <feather-icon
        icon="PlusIcon"
        class="mr-25"
      />
      <span>Add New</span>
    </b-button>
  </div>
</template>

<script>
import BCardCode from '@core/components/b-card-code'
import {
  BForm, BFormGroup, BFormInput, BRow, BCol, BButton,
} from 'bootstrap-vue'
import { heightTransition } from '@core/mixins/ui/transition'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BCardCode,
    BForm,
    BRow,
    BCol,
    BButton,
    BFormGroup,
    BFormInput,
  },
  directives: {
    Ripple,
  },
  mixins: [heightTransition],
  data() {
    return {
      items: [],
      nextTodoId: 1,
    }
  },
  mounted() {
    this.initTrHeight()
  },
  created() {
    window.addEventListener('resize', this.initTrHeight)
  },
  destroyed() {
    window.removeEventListener('resize', this.initTrHeight)
  },
  methods: {
    repeateAgain() {
      this.items.push({
        id: this.nextTodoId += this.nextTodoId,
      })

      this.$nextTick(() => {
        this.trAddHeight(this.$refs.row[0].offsetHeight)
      })
    },
    removeItem(index) {
      this.items.splice(index, 1)
      this.trTrimHeight(this.$refs.row[0].offsetHeight)
    },
    initTrHeight() {
      this.trSetHeight(null)
      this.$nextTick(() => {
        this.trSetHeight(this.$refs.form.scrollHeight)
      })
    },
  },
}
</script>

<style lang="scss" scoped>
.repeater-form {
  overflow: hidden;
  transition: .35s height;
}
</style>
