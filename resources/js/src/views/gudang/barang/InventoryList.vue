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
              name="bagian-barang"
              value="pabrik"
              plain
              class="radio-inventory-category-filter"
            >
              Pabrik
            </b-form-radio>
            <b-form-radio
              v-model="bagianFilter"
              name="bagian-barang"
              value="pengangkutan"
              plain
              class="radio-inventory-category-filter"
            >
              Pengangkutan
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
            <b-form-input
              v-model="tagFilter"
              class="d-inline-block mr-1"
              placeholder="Tag Filter..."
            />
            <b-button
              v-b-toggle.sidebar-add-new-inventory
              variant="primary"
            >
              Tambah Barang
            </b-button>
          </div>
        </b-col>
      </b-row>

    </div>

    <b-table
      ref="refInventoryListTable"
      :items="fetchAllInventory"
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
          :to="{ name: 'inventory-view', params: { id: data.item.id }}"
          class="font-weight-bold"
        >
          <span class="text-truncate overflow-hidden">
            {{ data.item.code | capital }}
          </span>
        </b-link>
      </template>

      <template #cell(name)="data">
          {{ data.item.name | titlecase }} 
      </template>

      <template #cell(category)="data">
          <feather-icon
            :icon="resolveInventoryCategoryAndIcon(data.item.category).icon"
          />
            <span class="align-text-top text-capitalize">{{ data.item.category | titlecase }}</span>
      </template>

      <template #cell(unit)="data">
          {{ data.item.unit | capital }} 
      </template>

      <template #cell(tags)="data">
          <ul>
            <li v-for="tag in JSON.parse(data.item.tags)">{{tag | lowercase }} <feather-icon :ref="`remove-tag-${data.item.id}-${tag}`" size="16" icon="XIcon" class="cursor-pointer" @click="removeTag(data.item.id,tag)"/></li>
          </ul>
      </template>

      <template #cell(actions)="data">

          <feather-icon
            :id="`form-inventory-list-tag-icon-${data.item.id}`"
            size="16"
            icon="FileIcon"
            class="cursor-pointer"
          />

          <b-popover
            :ref="`popover-tag-${data.item.id}`"
            :id="`popover-tag-${data.item.id}`"
            :target="`form-inventory-list-tag-icon-${data.item.id}`"
            triggers="click"
            @refetch-data="refetchData"
          >
            <b-form 
              :id="`form-inv-list-tag-${data.item.id}`" 
              :name="`form-inv-list-tag-${data.item.id}`" 
              @submit.prevent="addTag(data.item.id, newTagData[`${data.item.id}`])"
            >
              <b-row>

                <b-col cols="12">
                  <b-form-group
                    label="Tag"
                    :label-for="`new-tag-data-${data.item.id}`"
                  >
                    <b-form-input
                      v-model="newTagData[`${data.item.id}`]"
                      :id="`new-tag-data-${data.item.id}`"
                      :value="null"
                      type="text"
                    />
                  </b-form-group>
                </b-col>

                <b-col
                  cols="12"
                  class="d-flex justify-content-between mt-1"
                >
                  <b-button
                    variant="outline-primary"
                    type="submit"

                  >
                    Apply 
                  </b-button>
                  <b-button
                    variant="outline-secondary"
                    @click="closeTag(data.item.id)"
                  >
                    Cancel
                  </b-button>
                </b-col>
              </b-row>
            </b-form>
          </b-popover>

          <feather-icon
            size="16"
            icon="ImageIcon"
            class="cursor-pointer"
            v-b-toggle.sidebar-add-image-inventory 
          />
          <feather-icon
            size="16"
            icon="XIcon"
            class="cursor-pointer"
            @click="removeInventory(data.item.id)"
          />
          <!-- Dropdown -->
          <b-dropdown
            variant="link"
            toggle-class="p-0"
            no-caret
            :right="$store.state.appConfig.isRTL"
          >

            <template #button-content>
              <feather-icon
                icon="MoreVerticalIcon"
                size="16"
                class="align-middle text-body"
              />
            </template>
            <b-dropdown-item v-b-toggle.sidebar-edit-inventory @click="handleEdit(data.item.id)">
              <feather-icon icon="EditIcon" />
              <span class="align-middle ml-50">Edit</span>
            </b-dropdown-item>
          </b-dropdown>
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
            :total-rows="totalInventories"
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
    <sidebar-add-new-inventory 
      :is-add-new-inventory-sidebar-active.sync="isAddNewInventorySidebarActive"
      @sidebar-response="sidebarResponse"
      @refetch-data="refetchData"
    />
    <sidebar-edit-inventory 
      :is-edit-inventory-sidebar-active.sync="isEditInventorySidebarActive"
      @refetch-data="refetchData"
      :editid="editid"
    />
    <sidebar-add-image-inventory
      :is-add-image-inventory-sidebar-active.sync="isAddImageInventorySidebarActive"
      @refetch-data="refetchData"
      :editid="editid"
    />  </b-card>
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
import SidebarAddNewInventory from '../sidebar/SidebarAddNewInventory.vue'
import SidebarEditInventory from '../sidebar/SidebarEditInventory.vue'
import SidebarAddImageInventory from '../sidebar/SidebarAddImageInventory.vue'
import gudangStoreModule from '../gudangStoreModule'
import useInventoryList from './useInventoryList'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    ToastificationContent,
    SidebarAddImageInventory,
    SidebarAddNewInventory,
    SidebarEditInventory,
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
    addTag (identifier, tagData) {
      store.dispatch('app-inventory/addTagInventory', {id: identifier, tag: tagData })
        .then(response => {
          if(response.data.payload == "OK") {
              this.$refs["popover-tag-"+identifier].$emit('close')
              this.$refs["popover-tag-"+identifier].$emit('refetch-data')
          }            
        })
    },
    removeTag (identifier, tagData) {
      if (confirm("Yakin akan menghapus data ?")) {
        store.dispatch('app-inventory/removeTagInventory', {id: identifier, tag: tagData })
          .then(response => {
            if(response.data.payload == "OK") {
              this.refetchData()
            }            
         })
      }
    },
    closeTag (identifier) {
        this.$refs["popover-tag-"+identifier].$emit('close')
    },
    handleEdit(identifier) {
        this.$refs['editid'] = identifier
    },
    clearEditID() {
       this.$refs['editid'] = null
    } 
  },
  setup(prop, { emit }) {
    const INVENTORY_APP_STORE_MODULE_NAME = 'app-inventory'

    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)
    })

    const editid = ref()
    const tagOptions = ref([])
    store.dispatch('app-inventory/listFormData')
      .then((response) => {
        tagOptions.value = response.data.payload.tags
      })
    const isAddNewInventorySidebarActive = ref(false)
    const isEditInventorySidebarActive = ref(false)
    const isAddImageInventorySidebarActive = ref(false)

    const {
      refInventoryListTable,
      tableColumns,
      newTagData,
      perPage,
      currentPage,
      totalInventories,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      bagianFilter,
      tagFilter,
      dataMeta,

      refetchData,
      sidebarResponse,
      fetchAllInventory,
      removeInventory,
      resolveInventoryCategoryAndIcon,
      resolveInventoryTags
    } = useInventoryList()

    return {
      // Sidebar
      editid,
      isAddNewInventorySidebarActive,
      isEditInventorySidebarActive,
      isAddImageInventorySidebarActive,
      tableColumns,
      newTagData,
      perPage,
      currentPage,
      totalInventories,
      dataMeta,
      perPageOptions,
      searchQuery,
      sortBy,
      isSortDirDesc,
      refInventoryListTable,

      tagFilter,
      bagianFilter,
      tagOptions,

      refetchData,
      sidebarResponse,
      removeInventory,
      fetchAllInventory,

      resolveInventoryCategoryAndIcon,
      resolveInventoryTags
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
