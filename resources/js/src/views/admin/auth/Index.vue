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
        <b-form-radio
              v-model="bagianFilter"
              name="bagian-barang"
              value="semua"
              selected
              plain
              class="radio-inventory-category-filter"
            >
              Semua 
            </b-form-radio>
            <b-form-radio
              v-model="bagianFilter"
              name="grup-perusahaan"
              value="perusahaan"
              plain
              class="radio-inventory-category-filter"
            >
              Perusahaan
            </b-form-radio>
            <b-form-radio
              v-model="bagianFilter"
              name="grup-relasi"
              value="relasi"
              plain
              class="radio-inventory-category-filter"
            >
              Relasi
            </b-form-radio>
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
              v-b-toggle.sidebar-add-new-roles
              variant="primary"
            >
              Tambah Rekening
            </b-button>
          </div>
        </b-col>
      </b-row>

    </div>

    <b-table
      ref="refUserListTable"
      :items="fetchAllUsers"
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

      <!-- <template #cell(code)="data">
        <b-link
          :to="{ name: 'roless-view', params: { id: data.item.id }}"
          class="font-weight-bold"
        >
          <span class="text-truncate overflow-hidden">
            {{ data.item.code | capital }}
          </span>
        </b-link>
      </template> -->

      <template #cell(alias)="data">
        <b-link
          :to="{ name: 'bank-view', params: { id: data.item.id }}"
          class="font-weight-bold"
        >
          {{ data.item.alias | capital }} 
        </b-link>
      </template>

      <template #cell(name)="data">
          {{ data.item.name | titlecase }} 
      </template>

      <template #cell(category)="data">
          <feather-icon
            :icon="resolveUserCategoryAndIcon(data.item.category).icon"
          />
      </template>

      <template #cell(bank)="data">
          {{ data.item.bank_name.name | capital }} 
      </template>

      <template #cell(number)="data">
          {{ data.item.number | capital }} 
      </template>

      <template #cell(actions)="data">
        <feather-icon
          size="16"
          icon="EditIcon"
          class="cursor-pointer"
          v-b-toggle.sidebar-edit-roles
          @click="handleEdit(data.item.id)"
        />
        <feather-icon
          size="16"
          icon="XIcon"
          class="cursor-pointer"
          @click="removeUser(data.item.id)"
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
            :total-rows="totalUsers"
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
import bankStoreModule from './bankStoreModule'
import useBankList from './useBankList'
export default {
  components: {
    SidebarAddNewUser,
    SidebarEditUser,
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
        this.$refs['bankaccountid'] = identifier
        this.bankaccountid = identifier
    },
    clearEditID() {
       this.$refs['bankaccountid'] = null
    } 
  },
  setup(prop, { emit }) {
    const BANK_APP_STORE_MODULE_NAME  = 'app-roles'

    // Register module
    if (!store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.registerModule(BANK_APP_STORE_MODULE_NAME, bankStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.unregisterModule(BANK_APP_STORE_MODULE_NAME)
    })

    const bankaccountid = ref()
    const banknames = ref([])
    store.dispatch('app-roles/listFormData')
      .then((response) => {
        banknames.value = response.data.payload.banknames
      })
    const isAddNewUserSidebarActive = ref(false)
    const isEditUserSidebarActive = ref(false)

    const {
      refUserListTable,
      tableColumns,
      newTagData,
      perPage,
      currentPage,
      totalUsers,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      bagianFilter,
      tagFilter,
      dataMeta,

      refetchData,
      fetchAllUsers,
      removeUser,
      resolveUserCategoryAndIcon
    } = useBankList()

    return {
      // Sidebar
      bankaccountid,
      banknames,
      isAddNewUserSidebarActive,
      isEditUserSidebarActive,
      tableColumns,
      newTagData,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,

      tagFilter,
      bagianFilter,

      refetchData,
      removeUser,
      fetchAllUsers,

      resolveUserCategoryAndIcon,
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
