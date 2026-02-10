<template>
</template>
<script>
import {
  BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText,
} from 'bootstrap-vue'
import store from '@/store'
import { ref } from '@vue/composition-api'
import { formatDate } from '@core/utils/filter'
import vSelect from 'vue-select'

export default {
  components: {
    BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText,
    vSelect
  },
  data() {
  }
}
</script>
