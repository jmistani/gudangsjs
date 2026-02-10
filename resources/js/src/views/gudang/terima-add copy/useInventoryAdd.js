import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useInventoryAdd() {
    // Use toast
    const toast = useToast()

    const refInventoriesReceiving = ref(null)

    const dataMeta = computed(() => {
        return {
            subtotal: 0,
            discount: 0,
            tax: 0,
            total: 0,
        }
    })

    return {
        dataMeta,
        refInventoriesReceiving,
    }
}