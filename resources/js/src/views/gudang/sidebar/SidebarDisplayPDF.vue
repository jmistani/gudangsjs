<template>
  <b-sidebar
    id="sidebar-display-pdf"
    :visible="isDisplayPdfSidebarActive"
    sidebar-class="sidebar-lg"
    bg-variant="white"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-display-Pdf-sidebar-active', val)"
  >
	<div>
		<pdf
			:src="pdfFile"
			@num-pages="pageCount = $event"
			@page-loaded="currentPage = $event"
		></pdf>
	</div>
  </b-sidebar>
</template>

<script>

import pdf from 'vue-pdf'
import {
  BSidebar,
} from 'bootstrap-vue'
import router from '@/router'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import gudangStoreModule from '../gudangStoreModule'

export default {
  components: {
    BSidebar
  },
  props: {
    isDisplayPdfSidebarActive: {
      type: Boolean,
      required: true,
    },    
  },
	data() {
		return {
			currentPage: 0,
			pageCount: 0,
		}
	},
  setup(props, { emit }) {
    const INVENTORY_APP_STORE_MODULE_NAME = 'app-inventory'


    const inventoryData = ref(null)
    const isFetching = ref(true)
    const mutationData = ref(null)

    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)
    })

    const pdfFile = ref(null)
    store.dispatch('app-inventory/inventoryReceivePDF', { id: router.currentRoute.params.id })
      .then((response) => {
        pdfFile.value = response.data.payload
      })
    return {
      pdfFile
    }
  }

}

</script>