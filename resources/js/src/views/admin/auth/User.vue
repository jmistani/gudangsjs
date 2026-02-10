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
              v-model="roleFilter"
              name="bagian-barang"
              value="semua"
              selected
              plain
              class="radio-inventory-category-filter"
            >
              Semua 
            </b-form-radio>
            <b-form-radio
              v-model="roleFilter"
              name="staff-gudang"
              value="staff-gudang"
              plain
              class="radio-inventory-category-filter"
            >
              Staff Gudang
            </b-form-radio>
            <b-form-radio
              v-model="roleFilter"
              name="kasir"
              value="kasir"
              plain
              class="radio-inventory-category-filter"
            >
              Kasir
            </b-form-radio>
            <b-form-radio
              v-model="roleFilter"
              name="super-admin"
              value="super-admin"
              plain
              class="radio-inventory-category-filter"
            >
              Super Admin
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
              v-b-toggle.sidebar-add-new-users
              class="d-inline-block mr-1"
              variant="primary"
            >
              Tambah
            </b-button>
            <b-button
              variant="primary"
              class="d-inline-block mr-1"
              @click="printUserList"
            >
              Print
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
          :to="{ name: 'userss-view', params: { id: data.item.id }}"
          class="font-weight-bold"
        >
          <span class="text-truncate overflow-hidden">
            {{ data.item.code | capital }}
          </span>
        </b-link>
      </template> -->

      <template #cell(name)="data">
          <div v-if="data.item.name">{{ data.item.name | titlecase}} </div>
          <div v-else>*kosong*</div>
      </template>

      <template #cell(username)="data">
          {{ data.item.username | lowercase}} 
      </template>

      <template #cell(roles)="data">
          <ul>
            <li v-for="role in data.item.roles" :key="role.index">{{role.name | lowercase }} </li>
          </ul>
      </template> 

      <template #cell(actions)="data">
        <feather-icon
          size="16"
          icon="EditIcon"
          class="cursor-pointer"
          v-b-toggle.sidebar-edit-users
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
    <!-- <sidebar-add-new-users 
      :is-add-new-users-sidebar-active.sync="isAddNewUserSidebarActive"
      @refetch-data="refetchData"
    />

    <sidebar-edit-users 
      :is-edit-users-sidebar-active.sync="isEditUserSidebarActive"
      @refetch-data="refetchData"
      :editid="userid"
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
// import SidebarAddNewUser from './sidebar/SidebarAddNewUser.vue'
// import SidebarEditUser from './sidebar/SidebarEditUser.vue'
import userStoreModule from './userStoreModule'
import useUserList from './useUserList'
export default {
  components: {
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
        this.$refs['userid'] = identifier
        this.userid = identifier
    },
    clearEditID() {
       this.$refs['userid'] = null
    }, 
    printUserList() {
      
    }
  },
  setup(prop, { emit }) {
    const BANK_APP_STORE_MODULE_NAME  = 'app-users'

    // Register module
    if (!store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.registerModule(BANK_APP_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(BANK_APP_STORE_MODULE_NAME)) store.unregisterModule(BANK_APP_STORE_MODULE_NAME)
    })

    // store.dispatch('app-users/pageData')
    //   .then((response) => {
    //   })
 
    const {
      refUserListTable,
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      roleFilter,
      dataMeta,

      refetchData,
      fetchAllUsers,
      removeUser,
    } = useUserList()

    return {
      // Sidebar
      tableColumns,
      perPage,
      currentPage,
      totalUsers,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refUserListTable,

      roleFilter,

      refetchData,
      removeUser,
      fetchAllUsers,
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
