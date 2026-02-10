<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <b-link class="brand-logo">
        <vuexy-logo />
        <h2 class="brand-text text-primary ml-1">
          Vuexy
        </h2>
      </b-link>

      <!-- Left Text-->
      <b-col
        lg="8"
        class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
            fluid
            :src="imgUrl"
            alt="Login V2"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Add User-->
      <b-col
        lg="4"
        class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
          sm="8"
          md="6"
          lg="12"
          class="px-xl-2 mx-auto"
        >
          <b-card-title
            title-tag="h2"
            class="font-weight-bold mb-1"
          >
            SJS ERP Add user 👋
          </b-card-title>
          <b-card-text class="mb-2">
            Silahkan tambahkan user
          </b-card-text>

          <!-- form -->
          <validation-observer ref="adduserValidation">
            <b-form
              class="auth-adduser-form mt-2"
              @submit.prevent
            >
              <!-- email -->
              <b-form-group
                label="Username"
                label-for="adduser-username"
              >
                <validation-provider
                  #default="{ errors }"
                  name="Username"
                  :rules=" { usernameValidation: true }"
                >
                  <b-form-input
                    id="adduser-username"
                    v-model="userData.username"
                    :state="errors.length > 0 ? false:null"
                    name="adduser-username"
                    placeholder="username"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="adduser-password">Password</label>
                </div>
                <validation-provider
                  #default="{ errors }"
                  name="Password"
                  rules="required|min:6"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="adduser-password"
                      v-model="userData.password"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      :type="passwordFieldType"
                      name="adduser-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- confirm password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="adduser-confirm-password">Password</label>
                </div>
                <validation-provider
                  #default="{ errors }"
                  name="Confirm Password"
                  rules="required|confirmed:Password"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="adduser-confirm-password"
                      v-model="passwordConfirm"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      :type="passwordFieldType"
                      name="adduser-confirm-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- submit buttons -->
              <b-button
                type="submit"
                variant="primary"
                block
                @click="addUser"
              >
                Add user
              </b-button>
            </b-form>
          </validation-observer>

          <b-card-text class="text-center mt-2">
            <span>Already registered? </span>
            <b-link :to="{name:'page-auth-login-v2'}">
              <span>&nbsp;Login</span>
            </b-link>
          </b-card-text>

          <!-- divider -->
          <div class="divider my-2">
            <div class="divider-text">
              or
            </div>
          </div>
        </b-col>
      </b-col>
    <!-- /Add User-->
    </b-row>
  </div>
</template>

<script>
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BCard, BRow, BCol, BLink, BFormGroup, BFormInput, BInputGroupAppend, BInputGroup, BFormCheckbox, BCardText, BCardTitle, BImg, BForm, BButton,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import { required, email } from '@validations'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import store from '@/store/index'
import userStoreModule from './userStoreModule'
import { usernameValidation } from '@core/validations/add-user.js'
import formValidation from '@core/comp-functions/forms/form-validation'

export default {
  components: {
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
    usernameValidation
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      passwordConfirm: '',
      sideImg: require('@/assets/images/pages/login-v2.svg'),
      // validation rulesimport store from '@/store/index'
      required,
      email,
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    passwordConfirmToggleIcon() {
      return this.passwordConfirmFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/login-v2-dark.svg')
        return this.sideImg
      }
      return this.sideImg
    },
  },
  methods: {
    addUser() {
      this.$refs.adduserValidation.validate().then(success => {
        if (success) {
          store.dispatch('app-user/addUser', this.userData)
            .then(response => {
              if (response.statusText == 'OK') {
                toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Success',
                    icon: 'CheckIcon',
                    variant: 'success',
                    text: `Added User with username: ${userData.username}`,
                  },
                })
              }
            })
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Form Submitted',
              icon: 'EditIcon',
              variant: 'success',
            },
          })
        }
      })
    },
  },
  setup(props, { emit }) {

    const USER_STORE_MODULE_NAME = 'app-user'

    // Register module
    if (!store.hasModule(USER_STORE_MODULE_NAME)) store.registerModule(USER_STORE_MODULE_NAME, userStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(USER_STORE_MODULE_NAME)) store.unregisterModule(USER_STORE_MODULE_NAME)
    })
    const blankUserData = {
      username: '',
      password: '',
    }
    const userData = ref(JSON.parse(JSON.stringify(blankUserData)))
    const resetuserData = () => {
      userData.value = JSON.parse(JSON.stringify(blankUserData))
    }
    return {
      userData,
      resetuserData,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>
