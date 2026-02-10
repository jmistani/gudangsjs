<template>

  <div class="checkout-items">
   <b-card-group deck>
      <b-card
         title="Users"
      >
         <b-card-text></b-card-text>
            <b-form-group
               label="User"
               label-for="v-user"
            >
               <v-select
                  id="v-user"
                  label="username"
                  v-model="selectedUser"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="refUsers"
                  @input="clearPermissions"
                  :clearable="false"
               />
            </b-form-group>
         <b-button href="#" variant="primary" @click="fillPermissions">Go somewhere</b-button>
      </b-card>
      <b-card
         title="Permissions"
      >
         <vue-perfect-scrollbar
            :settings="perfectScrollbarSettings"
            class="sidebar-menu-list scroll-area"
         >
          <li
            v-for="permission in refPermissions"
            :key="permission.id"
            class="todo-item"
            @click="handlePermissionClick(task)"
          >
            <feather-icon
              icon="MoreVerticalIcon"
              class="draggable-task-handle d-inline"
            />
            <div class="todo-title-wrapper">
              <div class="todo-title-area">
                <div class="title-wrapper">
                  <b-form-checkbox
                    :checked="task.isCompleted"
                    @click.native.stop
                    @change="updatePermissionIsCompleted(task)"
                  />
                  <span class="todo-title">{{ permission.name }}</span>
                </div>
              </div>
              <div class="todo-item-action">
                <div class="badge-wrapper mr-1">
                  <b-badge
                    v-for="tag in task.tags"
                    :key="tag"
                    pill
                    :variant="`light-${resolveTagVariant(tag)}`"
                    class="text-capitalize"
                  >
                    {{ tag }}
                  </b-badge>
                </div>
                <small class="text-nowrap text-muted mr-1">{{ formatDate(task.dueDate, { month: 'short', day: 'numeric'}) }}</small>
                <b-avatar
                  v-if="task.assignee"
                  size="32"
                  :src="task.assignee.avatar"
                  :variant="`light-${resolveAvatarVariant(task.tags)}`"
                  :text="avatarText(task.assignee.fullName)"
                />
                <b-avatar
                  v-else
                  size="32"
                  variant="light-secondary"
                >
                  <feather-icon
                    icon="UserIcon"
                    size="16"
                  />
                </b-avatar>
              </div>
            </div>

          </li>
         </vue-perfect-scrollbar>
         <b-form-checkbox-group
            v-model="selectedUserPermissions"
            :options="permissionOptions"
            inline
         />
         <b-button href="#" variant="primary" @click="savePermissions">Go somewhere</b-button>
      </b-card>
   </b-card-group>
  </div>

</template>

<script>
import {
  BFormGroup, BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText, BFormCheckboxGroup
} from 'bootstrap-vue'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { formatDate } from '@core/utils/filter'
import vSelect from 'vue-select'
import roleStoreModule from './roleStoreModule'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'

export default {
  components: {
    BFormGroup, BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText, BFormCheckboxGroup,
    vSelect
  },
  data() {
     return {
        selectedUser: null,
        selectedUserPermissions: [],
        permissionOptions: []
     }
  },
  methods: {
     clearPermissions() {
        permissionOptions = null
     },
     fillPermissions() {
        if(this.selectedUser) {
           permissionOptions = refUserPermissions[this.selectedUser]['permission']
        }
     },
     savePermissions() {

     }
  },
  setup() {
    const ROLES_APP_STORE_MODULE_NAME = 'app-roles'

    // Register module
    if (!store.hasModule(ROLES_APP_STORE_MODULE_NAME)) store.registerModule(ROLES_APP_STORE_MODULE_NAME, roleStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(ROLES_APP_STORE_MODULE_NAME)) store.unregisterModule(ROLES_APP_STORE_MODULE_NAME)
    })
    
    const perfectScrollbarSettings = {
      maxScrollbarLength: 60,
    }
    
    const refUsers = ref([])
    const refRoles = ref([])
    const refRolesPermissions = ref([])
    const refPermissions = ref([])
    const refUserPermissions = ref([])

    store.dispatch('app-roles/pageData')
      .then((response) => {
        refUsers.value = response.data.payload.users
        refRoles.value = response.data.payload.roles
        refRolesPermissions.value = response.data.payload.rolesPermissions
        refPermissions.value = response.data.payload.permissions
        refUserPermissions.value = response.data.payload.userspermissions
    })
   return {
      refUsers,
      refRoles,
      refRolesPermissions,
      refPermissions,
      refUserPermissions,
      maxScrollbarLength,
   }
  }  
}
</script>

<style lang="scss" scoped>
   @import '~@core/scss/vue/libs/vue-select.scss';
.display-full-width {
   display: inline-block !important;
}
</style>