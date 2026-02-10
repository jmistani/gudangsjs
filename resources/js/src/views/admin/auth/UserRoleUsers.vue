<template>
  <div class="checkout-items">
   <b-card-group deck>
      <b-card
         title="Users"
      >
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
               @input="fillPermissions"
               :clearable="false"
            />
         </b-form-group>
         <b-form-group
            label="Name"
            label-for="v-name"
         >
            <b-form-input
               id="v-name"
               v-model="fullname"
            >
            </b-form-input>
         </b-form-group>
         <ul class="">
            <li class="mb-1">Roles</li>
         <b-form-checkbox-group 
            label="Roles" 
            name="role" 
            :options="refRoleOptions"
            text-field="name"
            value-field="name"
            v-model="selectedRoles"
            stacked
            :disabled="isDisabledRoles"
         >
         </b-form-checkbox-group>
            <li class="mb-1 mt-1">Permission</li>
         <b-form-checkbox-group 
            label="Permissions" 
            name="permission" 
            :options="refPermissionOptions" 
            value-field="name"
            text-field="name"
            v-model="selectedPermissions"
            stacked
            :disabled="isDisabledPermissions"
         >
         </b-form-checkbox-group>
         <b-button class="mt-1" variant="primary" @click="savePermissions">Save</b-button>
         <b-button class="mt-1" href="#" variant="primary" @click="cancelForm">Cancel</b-button>
         </ul>
      </b-card>
   </b-card-group>
  </div>

</template>

<script>
import {
  BFormGroup, BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText, 
  BFormCheckboxGroup, BFormCheckbox, BFormInput
} from 'bootstrap-vue'
import store from '@/store'
import { ref, onUnmounted } from '@vue/composition-api'
import { formatDate } from '@core/utils/filter'
import vSelect from 'vue-select'
import roleStoreModule from './roleStoreModule'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BFormGroup, BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText, BFormCheckboxGroup,
    vSelect, BFormCheckbox, BFormInput
  },
  data() {
     return {
        userData: {
           fullname: '',
           username: '',
           selectedRoles: [],
           selectedPermissions: []
        },
        selectedUser: null,
        selectedUserPermissions: [],
        permissionOptions: [],
        selectedRoles: [],
        selectedPermissions: [],
        isDisabledRoles: true,
        isDisabledPermissions: true,
        fullname: ''
     }
  },
  methods: {
     disableAllRoleOptions(disable) {
        this.refRoleOptions.forEach((option) => {
           option.disabled = disable
        })
     },
     disableAllRoleExceptSuper(disable) {
        this.refRoleOptions.forEach((option) => {
           if(option.name != 'super-admin')
            option.disabled = disable
        })
     },
     disableAllPermissionOptions(disable) {
        this.refPermissionOptions.forEach((option) => {
           option.disabled = disable
        })
     },
     assignSuperAdminPermissions(tick) {
        this.refPermissionOptions.forEach((option) => {
           option.disabled = tick
        })
        if(tick) {
           this.refPermissionOptions.forEach((option) => {
              this.selectedPermissions.push(option.name)
           })
        }
        else {
           this.selectedPermissions = []
        }
     },
     assignRole(role) {
        var names
        var optionIndex = -1
        var BreakException = {}
         try
         {
            if(role == 'super-admin'){
               throw BreakException
            }
            this.selectedRoles.forEach((role) => {
               this.refRolesPermissions[role].forEach((permission) => {
                  names = this.refPermissionOptions.map(el => el.name)
                  optionIndex = names.indexOf(permission)
                  if(optionIndex >-1)
                  {
                     this.refPermissionOptions[optionIndex].disabled = true
                     if(this.selectedPermissions.indexOf(permission) < 0)
                        this.selectedPermissions.push(permission)
                  }
               })
            })
         }
         catch(e) {
            this.disableAllRoleExceptSuper(true)
            this.assignSuperAdminPermissions(true)
            this.selectedRoles = ['super-admin']
         }
     },
     removeRole(role) {
        var names
        var optionIndex = -1
         if(role == 'super-admin') {
            this.disableAllRoleExceptSuper(false)
            this.assignSuperAdminPermissions(false)
         }
         else {
            this.refRolesPermissions[role].forEach((permission) => {
               names = this.refPermissionOptions.map(el => el.name)
               optionIndex = names.indexOf(permission)
               if(optionIndex > -1)
               {
                  this.refPermissionOptions[optionIndex].disabled = false
                  this.selectedPermissions.splice(this.selectedPermissions.indexOf(permission),1)
               }
            })
         }
     },
     updateByPermission(permissionname) {
        if(this.selectedRoles) {
         this.selectedRoles.forEach((role) => {
            if(this.refRolesPermissions[role].indexOf(permissionname) >-1)
               {
                  return true
               }
            });
        }
      return false
    },
     clearPermissions() {
        this.permissionOptions = null
     },
     fillPermissions() {
        if(this.selectedUser) {
            this.isDisabledRoles = false
            this.isDisabledPermissions = false
           this.disableAllRoleOptions(false)
           this.disableAllPermissionOptions(false)
           this.selectedRoles = []
           this.selectedPermissions = []
           store.dispatch('app-roles/rolePermission',this.selectedUser.username)
            .then((response) => {
               this.fullname = response.data.payload.fullname
               response.data.payload.roles.forEach((role) => {
                  this.selectedRoles.push(role.name)
                  if(role.name == 'super-admin')
                     this.disableAllRoleExceptSuper(true)
               })
               // let intersection = this.selectedPermissions.filter(x => response.data.payload.permissions.indexOf(x) ==-1);
               // this.selectedPermissions.push(intersection)
            })
         }
        else
        {
           this.selectedRoles = []
           this.isDisabledRoles = true
           this.selectedPermissions = []
           this.isDisabledPermissions = true
        }
     },
     savePermissions() {
         this.userData.fullname = this.fullname
         this.userData.username = this.selectedUser.username
         this.userData.selectedRoles = this.selectedRoles
         this.userData.selectedPermissions = this.selectedPermissions
        store.dispatch('app-roles/saveUser', this.userData)
         .then((response) => {
            if(response.data.code == 200) {
               
               this.$toast({
                  component: ToastificationContent,
                  props: {
                  title: 'Form Submitted',
                  icon: 'EditIcon',
                  variant: 'success',
                  },
               })
           }
           else {
               this.$toast({
                  component: ToastificationContent,
                  props: {
                  title: 'Error Submitting',
                  icon: 'WarningIcon',
                  variant: 'warning',
                  },
               })
           }
         })
     },
     updatePermissionsOnRoleSelection(value) {
     },
     cancelForm() {
     }
  },
  watch: {
     selectedRoles(oldvalues,newvalues) {
        if(oldvalues.length >= newvalues.length) {
            let intersection = oldvalues.filter(x => newvalues.indexOf(x) ==-1);
            this.assignRole(intersection)

        } else {
            let intersection = newvalues.filter(x => oldvalues.indexOf(x) ==-1);
            this.removeRole(intersection[0])
        }
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
    const refPermissions = ref([])
    const refRolesPermissions = ref([])
    const refRoleOptions = ref([])
    const refPermissionOptions = ref([])

    store.dispatch('app-roles/pageData')
      .then((response) => {
        refUsers.value = response.data.payload.users
        refRoles.value = response.data.payload.roles
        refPermissions.value = response.data.payload.permissions
        refRolesPermissions.value = response.data.payload.rolepermissions
         response.data.payload.roles.forEach((role) => {
            refRoleOptions.value.push({ name: role.name, value: role.name, disabled: false}) 
         })
         response.data.payload.permissions.forEach((permission) => {
            refPermissionOptions.value.push({ name: permission.name, value: permission.name, disabled: false}
         ) 
      })

    })
    
   return {
      refUsers,
      refRoles,
      refPermissions,
      refRolesPermissions,
      refRoleOptions,
      refPermissionOptions,
      perfectScrollbarSettings,
   }
  }  
}
</script>

<style lang="scss" scoped>
   @import '~@core/scss/vue/libs/vue-select.scss';
.demo-inline-spacing { 
   display:-webkit-box;
   display:-ms-flexbox;
   display:flex;
   -ms-flex-wrap:wrap;
   flex-wrap:wrap;
   -webkit-box-pack:start;
   -ms-flex-pack:start;
   justify-content:flex-start;
   -webkit-box-align:center;
   -ms-flex-align:center;
   align-items:center;
}
</style>