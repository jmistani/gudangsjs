import { ref, watch, computed } from '@vue/composition-api'
import store from '@/store'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useConsumeAdd() {
    // Use toast
    const toast = useToast()

    return {
        toast,
    }
}