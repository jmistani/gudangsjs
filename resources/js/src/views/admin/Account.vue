<template>

  <!-- Table Container Card -->
  <b-card
    no-body
  >

    <div class="m-2">

      <!-- Table Top -->
      <b-row>

        <!-- Per Page -->
        <b-col
          cols="12"
          md="6"
          class="d-flex align-items-center justify-content-start mb-1 mb-md-0"
        >
          <label>Entries</label>
          <v-select
            v-model="perPage"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="perPageOptions"
            :clearable="false"
            class="per-page-selector d-inline-block ml-50 mr-1"
          />
        </b-col>

        <!-- Search -->
        <b-col
          cols="12"
          md="6"
        >
          <div class="d-flex align-items-center justify-content-end">
            <b-form-input
              v-model="searchQuery"
              class="d-inline-block mr-1"
              placeholder="Search..."
            />
            <b-button
              v-b-toggle.sidebar-add-new-account
              class="d-inline-block mr-1"
              variant="primary"
            >
              Tambah
            </b-button>
          </div>
        </b-col>
      </b-row>

    </div>

    <b-table
      ref="refAccountListTable"
      :items="fetchAllAccounts"
      striped
      responsive
      :fields="tableColumns"
      primary-key="id"
      :sort-by.sync="sortBy"
      show-empty
      empty-text="No matching records found"
      :sort-desc.sync="isSortDirDesc"
      class="position-relative"
    >
      <template v-slot:table-busy>
          <spinner message="Loading ..."></spinner>
      </template>

      <template #cell(code)="data">
        <b-link
          class="font-weight-bold"
        >
          <span v-if="data.item.code" class="text-truncate overflow-hidden">
            {{ data.item.code | capital }}
          </span>
          <span v-else class="text-danger text-truncate overflow-hidden">
            *kosong*
          </span>
        </b-link>
      </template>

      <template #cell(name)="data">
          <div v-if="data.item.name">{{ data.item.name | titlecase}} </div>
          <div v-else>*kosong*</div>
      </template>

      <template #cell(balance)="data">
          Rp. {{ data.item.balance.amount | formatCurrency }} 
      </template>

      <template #cell(actions)="data">
        <feather-icon
          size="16"
          icon="EditIcon"
          class="cursor-pointer"
          v-b-toggle.sidebar-edit-accounts
          @click="handleEdit(data.item.id)"
        />
        <feather-icon
          size="16"
          icon="XIcon"
          class="cursor-pointer"
          @click="removeAccount(data.item.id)"
        />
      </template>

    </b-table>
    <div class="mx-2 mb-2">
      <b-row>

        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-start"
        >
          <span class="text-muted">Showing {{ dataMeta.from }} to {{ dataMeta.to }} of {{ dataMeta.of }} entries</span>
        </b-col>
        <!-- Pagination -->
        <b-col
          cols="12"
          sm="6"
          class="d-flex align-items-center justify-content-center justify-content-sm-end"
        >

          <b-pagination
            v-model="currentPage"
            :total-rows="totalAccounts"
            :per-page="perPage"
            first-number
            last-number
            class="mb-0 mt-1 mt-sm-0"
            prev-class="prev-item"
            next-class="next-item"
          >
            <template #prev-text>
              <feather-icon
                icon="ChevronLeftIcon"
                size="18"
              />
            </template>
            <template #next-text>
              <feather-icon
                icon="ChevronRightIcon"
                size="18"
              />
            </template>
          </b-pagination>

        </b-col>

      </b-row>
    </div>
    <sidebar-add-new-account
      :is-add-new-account-sidebar-active.sync="isAddNewAccountSidebarActive"
      @refetch-data="refetchData"
    />

    <!-- <sidebar-edit-accounts 
      :is-edit-accounts-sidebar-active.sync="isEditAccountSidebarActive"
      @refetch-data="refetchData"
      :editid="accountid"
    /> -->
  </b-card>
</template>

<script>
import {
  BForm, BFormGroup, BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink,
  BBadge, BDropdown, BDropdownItem, BPagination, BTooltip,VBToggle,BPopover,
  BFormRadio,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import SidebarAddNewAccount from './sidebar/SidebarAddNewAccount.vue'
// import SidebarEditAccount from './sidebar/SidebarEditAccount.vue'
import accountStoreModule from './accountStoreModule'
import useAccountList from './useAccountList'
export default {
  components: {
    SidebarAddNewAccount,
    BForm,
    BFormGroup,
    BCard,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BTable,
    BMedia,
    BAvatar,
    BLink,
    BBadge,
    BDropdown,
    BDropdownItem,
    BPagination,
    BTooltip,
    BFormRadio,
    BPopover,

    vSelect,
  },
  directives: {
    'b-toggle': VBToggle,
  },
  methods: {
    closeTag (identifier) {
        this.$refs["popover-tag-"+identifier].$emit('close')
    },
    handleEdit(identifier) {
        this.$refs['accountid'] = identifier
        this.accountid = identifier
    },
    clearEditID() {
       this.$refs['accountid'] = null
    }, 
    printAccountList() {
      
    }
  },
  setup(prop, { emit }) {
    const BANK_APP_STORE_MODULE_NAME  = 'app-accounts'

    // Register module
    if (!store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.registerModule(BANK_APP_STORE_MODULE_NAME, accountStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.unregisterModule(BANK_APP_STORE_MODULE_NAME)
    })
 
    const isAddNewAccountSidebarActive = ref(false)

    const {
      refAccountListTable,
      tableColumns,
      perPage,
      currentPage,
      totalAccounts,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      dataMeta,

      refetchData,
      fetchAllAccounts,
      removeAccount,
    } = useAccountList()

    return {
      isAddNewAccountSidebarActive,
      tableColumns,
      perPage,
      currentPage,
      totalAccounts,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refAccountListTable,

      refetchData,
      removeAccount,
      fetchAllAccounts,
    }
  },
}
</script>


<style lang="scss" scoped>
.per-page-selector {
  width: 90px;
}

.inventory-filter-select {
  min-width: 190px;

  ::v-deep .vs__selected-options {
    flex-wrap: nowrap;
  }

  ::v-deep .vs__selected {
    width: 100px;
  }
}
.radio-inventory-category-filter {
  margin-right: 10px;
  margin-left: 10px;
}
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
