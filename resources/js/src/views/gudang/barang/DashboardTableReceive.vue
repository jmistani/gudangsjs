<template>
  <b-card
    no-body
    class="card-company-table"
  >
    <b-row class="same-height">
      
    </b-row>
  </b-card>
</template>

<script>
import {
  BCard, BTable, BAvatar, BImg, BLink, BRow, BCol,
} from 'bootstrap-vue'

export default {
  components: {
    BRow,
    BCol,
    BLink,
    BCard,
    BTable,
    BAvatar,
    BImg,
  },
  props: [
    tableData,
    tableColumns
  ],

  data() {
    return {
    }
  },
}
</script>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';
@import '~@core/scss/base/components/variables-dark';

.card-company-table ::v-deep td .b-avatar.badge-light-company {
  .dark-layout & {
    background: $theme-dark-body-bg !important;
  }
}
</style>
