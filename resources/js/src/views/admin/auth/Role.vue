<template>
  <div class="checkout-items">
   <b-card-group deck>
      <b-card
         title="Roles"
      >
         <b-form-group
            label="Role"
            label-for="v-role"
         >
            <v-select
               id="v-role"
               label="name"
               v-model="selectedRole"
               :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
               :options="refRoles"
               @input="fillPermissions"
               :clearable="false"
            />
         </b-form-group>
         <ul class="">
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
            <b-button class="mt-1" href="#" variant="primary" @click="fillPermissions">Save</b-button>
            <b-button class="mt-1" href="#" variant="primary" @click="cancelForm">Cancel</b-button>
         </ul>
      </b-card>
   </b-card-group>
  </div>

</template>

<script>
import {
  BFormGroup, BCard, BCardBody, BLink, BImg, BButton, BBadge, BFormSpinbutton, BCardGroup, BCardText, 
  BFormCheckboxGroup, BFormCheckbox,
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
    vSelect, BFormCheckbox,
  },
  data() {
     return {
        selectedRole: null,
        selectedRolePermissions: [],
        permissionOptions: [],
        selectedPermissions: [],
     }
  },
  methods: {
     assignPermission() {
        var names
        var optionIndex = -1
        if(this.selectedRoles) {
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
               });
           });
        }
     },
     removePermission(role) {
        var names
        var optionIndex = -1
         this.refRolesPermissions[role].forEach((permission) => {
            names = this.refPermissionOptions.map(el => el.name)
            optionIndex = names.indexOf(permission)
            if(optionIndex > -1)
            {
               this.refPermissionOptions[optionIndex].disabled = false
               this.selectedPermissions.splice(this.selectedPermissions.indexOf(permission),1)
            }
         });
     },
     fillPermissions() {
        if(this.selectedRole) {
           this.selectedPermissions = []
           store.dispatch('app-roles/permissionsByRole',this.selectedRole.name)
            .then((response) => {
               response.data.payload.permissions.forEach((permission) => {
                  this.selectedPermissions.push(permission)
               })
            })
         }
        else
        {
           this.selectedPermissions = []
        }
     },
     savePermissions() {
         let data = []
         data['role'] = this.selectedRole
         data['permissions'] = this.selectedPermissions
        store.dispatch('app-role/save-role', data)
         .then((response) => {
            if(response.data.payload.status == "200") {
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
     selectedPermissions(oldvalues,newvalues) {
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
      refRoles,
      refPermissions,
      refRolesPermissions,
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