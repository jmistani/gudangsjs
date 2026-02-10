<template>
   <div>
     <user-role-users></user-role-users>
   </div>
</template>

<script>
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BCard, BRow, BCol, BLink, BFormGroup, BFormInput, BInputGroupAppend, BInputGroup, BFormCheckbox, BCardText, BCardTitle, BImg, BForm, BButton,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store/index'
import roleStoreModule from './roleStoreModule'
import UserRoleUsers from './UserRoleUsers.vue'

export default {
  components: {
    UserRoleUsers,
    BForm,
    BCard,
    BRow,
    BCol,
    BLink,
    BCardTitle,
    BImg,
    BFormInput,
    BFormGroup,
    BInputGroup,
    BInputGroupAppend,
    BFormCheckbox,
    VuexyLogo,
    ValidationProvider,
    ValidationObserver,
    ToastificationContent,
    BButton,
    BCardText,
  },
   setup(prop, { emit }) {
  }
}
</script>


<style lang="scss">
   @import '~@core/scss/vue/libs/vue-select.scss';
</style>