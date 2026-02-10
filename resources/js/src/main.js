import Vue from 'vue'
import { ToastPlugin, ModalPlugin } from 'bootstrap-vue'
import VueCompositionAPI from '@vue/composition-api'
import VueSanctum from 'vue-sanctum';
import router from './router'
import store from './store'
import App from './App.vue'
import axios from '@axios'
import Spinner from 'vue-simple-spinner';
import VueNumeric from 'vue-numeric'
import * as VeeValidate from 'vee-validate';

// Global Components
import './global-components'

// 3rd party plugins
import '@/libs/portal-vue'
import '@/libs/toastification'
import moment from 'moment'
import '@/libs/sweet-alerts'
import '@/libs/acl'
// BSV Plugin Registration
Vue.use(ToastPlugin)
Vue.use(ModalPlugin)
Vue.use(VeeValidate, {
    mode: 'lazy'
});

// Composition API
Vue.use(VueCompositionAPI)
Vue.component('spinner', Spinner)
Vue.use(VueNumeric)
    //Vue Sanctum
Vue.use(VueSanctum, {
        axios: axios,
        store,
    })
    // import core styles
require('@core/scss/core.scss')

// import assets styles
require('@/assets/scss/style.scss')
require('@core/assets/fonts/feather/iconfont.css')

Vue.config.productionTip = false

Vue.directive('clear-zero', {
    bind: function(el, binding, vnode) {
        el.onfocus = function() {
            if (parseFloat(el.value) === 0) {
                el.value = null;
            }
        };
    }
});

Vue.filter("titlecase", function(value) {
    return value.toLowerCase().replace(/(?:^|\s|-)\S/g, x => x.toUpperCase());
});
Vue.filter("lowercase", function(value) {
    return value.toLowerCase();
});
Vue.filter("capital", function(value) {
    return value.toUpperCase();
});
Vue.filter('formatDateWithTime', function(value) {
    if (value) {
        return moment(String(value)).format('DD MMM YYYY H:mm')
    }
});
Vue.filter('formatDate', function(value) {
    if (value) {
        return moment(String(value)).format('DD MMM YYYY')
    }
});
Vue.filter('formatCurrency', function(value) {
    if (value) {
        let val = (value / 1).toFixed(2).replace('.', ',')
        console.log(val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."))
        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    }
});
Vue.filter('formatCurrencyWithPrefix', function(value) {
    if (value) {
        let val = (value / 1).toFixed(2).replace('.', ',')
        return 'Rp. ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    }
});
Vue.filter('formatStock', function(value) {
    if (value == 0) return "0";
    if (value) {
        var float = parseFloat(value);
        var int = parseInt(value);
        if (float != int) {
            let val = (value / 1).toFixed(1).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        } else {
            let val = (value / 1).toFixed(0)
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        }
    }
});
Vue.filter('formatNetto', function(value) {
    return value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
});

new Vue({
    router,
    store,
    render: h => h(App),
}).$mount('#app')