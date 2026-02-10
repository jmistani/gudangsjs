<template>
  <b-card

    no-body
    class="card-company-table"
  >
    <b-row class="same-height">
      <b-table
        ref="refTableList"
        :items="tableData"
        responsive
        :fields="tableColumns"
        class="mb-0"
      > 
        <!-- code -->
        <template #cell(code)="data">
          <div class="d-flex flex-column">
            <span class="font-weight-bolder mb-25">{{ data.item.code }}</span>
          </div>
        </template>

        <!-- code -->
        <template #cell(created_at)="data">
          <div class="d-flex flex-column">
            <span class="font-weight-bolder mb-25">{{ data.item.created_at }}</span>
          </div>
        </template>

        <!-- status -->
        <template #cell(status)="data">
          <div class="d-flex align-items-center">
            <b-avatar
              class="mr-1" size="18"
              :variant="data.item.status == 'open' ? 'success' : 'danger'"
            >
              <feather-icon
                size="15"
                :icon="data.item.status == 'open' ? 'CheckIcon' : 'XIcon'"
              />
            </b-avatar>
            <span>{{ data.item.status }}</span>
          </div>
        </template>

        <template #cell(action)="data">
          <b-link
            :to="{ name: 'display-inventory-receive' }"
            class="font-weight-bold"
          >
            <span class="text-truncate overflow-hidden">
              View
            </span>
          </b-link>
        </template>
      </b-table>
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
  }
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
