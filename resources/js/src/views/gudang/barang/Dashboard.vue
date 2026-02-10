<template>
  <b-card>
    <b-tabs>
      <b-tab>
        <template #title>
          <feather-icon icon="DownloadIcon" />
          <span>Penerimaan Barang</span>
        </template>
        <b-card>
          <b-row class="match-height">
            <b-col
              xl="6"
              md="6"
            >
              <h4>Daftar Penerimaan Barang</h4>
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
              <!-- <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="outline-primary"
                pill
              >
                Secondary
              </b-button> -->
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
              <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="primary"
                pill
                :to="{name: 'inventory-receive-add'}"
              >
                Tambah
              </b-button>
            </b-col>
          </b-row>
          <b-row class="match-height mt-2">
            <b-col
              xl="10"
              md="10"
            >
                <b-table
                small
                ref="refReceiveTable"
                :items="inventoryReceiveData"
                :fields="inventoryReceiveFields"
                class="mb-0"
                responsive
                striped
                show-empty
                empty-text="No matching records found"
              > 
                <template v-slot:table-busy>
                    <spinner message="Loading ..."></spinner>
                </template>
                <template #cell(name)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.stockable.name }}</span>
                  </div>
                </template>

                <template #cell(giver)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.second_reference.giver }}</span>
                  </div>
                </template>

                <template #cell(post_date)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.reference.post_date | formatDate }}</span>
                  </div>
                </template>

                <template #cell(amount)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.amount }}</span>
                  </div>
                </template>

                <template #cell(unit)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.stockable.unit }}</span>
                  </div>
                </template>

                <template #cell(price)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.price.amount | formatCurrencyWithPrefix }}</span>
                  </div>
                </template>

                <template #cell(action)="data">
                  <b-link
                    :to="{ name: 'display-inventory-receive', params: { id: data.item.second_reference.id }  }"
                    class="font-weight-bold"
                  >
                    <span class="text-truncate overflow-hidden">
                      View
                    </span>
                  </b-link>
                  <!--<feather-icon
                    :id="`form-dashboard-receive-icon-${data.item.id}`"
                    size="16"
                    icon="FileIcon"
                    class="cursor-pointer"
                  />
                   <b-popover
                    :ref="`form-receive-popover-${data.item.id}`"
                    :target="`form-dashboard-receive-icon-${data.item.id}`"
                    triggers="click"
                    placement="lefttop"
                  >
                      <b-row>

                        <b-col cols="12">
                          <b-form-group
                            label="Tiket"
                            :label-for="`dashboard-receive-${data.item.id}-tiket`"
                          >
                            <v-select
                              :id="`dashboard-receive-${data.item.id}-tiket`"
                              v-model="data.item.second_reference_id"
                              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                              :options="refTicketOptions"
                              label="name"
                              :clearable="false"
                              class="mb-0 item-selector-title"
                              placeholder="Select Item"
                              :reduce="refTicketOptions => refTicketOptions.id"
                              @input="assignTicketReceive(data.item.id,data.item.second_reference_id)"
                              style="width: 200px !important;"
                            >
                              <template #option="{ name }">
                                {{ name | titlecase }}
                              </template>
                            </v-select>
                          </b-form-group>
                        </b-col>


                        <b-col
                          cols="12"
                          class="d-flex justify-content-between mt-1"
                        >
                          <div v-if="data.item.second_reference_id">
                            <b-button
                              variant="outline-danger"
                              @click="removeTicketReceive(data.item.id)"
                            >
                              Hapus 
                            </b-button>
                          </div>
                        </b-col>
                      </b-row>
                  </b-popover> -->
                </template>
              </b-table>
            </b-col>
          </b-row>
        </b-card>
      </b-tab>
      <b-tab>
        <template #title>
          <feather-icon icon="ListIcon" />
          <span>Pemakaian Barang</span>
        </template>
        <b-card>
          <b-row class="match-height">
            <b-col
              xl="6"
              md="6"
            >
              <h4>Daftar Pemakaian Barang</h4>
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
              <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="primary"
                pill
                :to="{ name:'inventory-consume-add' }"
              >
                Tambah
              </b-button>
            </b-col>
          </b-row>
          <b-row class="match-height mt-2">
            <b-col
              xl="10"
              md="10"
            >
                <b-table
                small
                ref="refConsumeTable"
                :items="inventoryConsumeData"
                :fields="inventoryConsumeFields"
                class="mb-0"
                responsive
                striped
                show-empty
                empty-text="No matching records found"
              > 
                <template #cell(name)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.stockable.name }}</span>
                  </div>
                </template>

                <template #cell(giver)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.second_reference.receiver }}</span>
                  </div>
                </template>

                <template #cell(post_date)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.reference.post_date | formatDate }}</span>
                  </div>
                </template>

                <template #cell(amount)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.amount }}</span>
                  </div>
                </template>

                <template #cell(unit)="data">
                  <div class="d-flex flex-column">
                    <span class="font-weight-bolder mb-25">{{ data.item.stockable.unit }}</span>
                  </div>
                </template>

                <template #cell(action)="data">
                  <b-link
                    :to="{ name: 'display-inventory-consume', params: { id: data.item.second_reference.id }  }"
                    class="font-weight-bold"
                  >
                    <span class="text-truncate overflow-hidden">
                      View
                    </span>
                  </b-link>
                  <feather-icon
                    :id="`form-dashboard-consume-icon-${data.item.id}`"
                    size="16"
                    icon="FileIcon"
                    class="cursor-pointer"
                  />
                  <b-popover
                    :ref="`form-consume-popover-${data.item.id}`"
                    :target="`form-dashboard-consume-icon-${data.item.id}`"
                    triggers="click"
                    placement="lefttop"
                  >
                      <b-row>

                        <!-- Field: Discount -->
                        <b-col cols="12">
                          <b-form-group
                            label="Tiket"
                            :label-for="`dashboard-consume-${data.item.id}-tiket`"
                          >
                            <v-select
                              :id="`dashboard-consume-${data.item.id}-tiket`"
                              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                              v-model="data.item.second_reference_id"
                              :options="refTicketOptions"
                              label="name"
                              :clearable="false"
                              class="mb-0 item-selector-title"
                              :reduce="refTicketOptions => refTicketOptions.id"
                              @input="assignTicketConsume(data.item.id,data.item.second_reference_id)"
                              style="width: 200px !important;"
                            >
                              <template #option="{ name }">
                                {{ name | titlecase }}
                              </template>
                            </v-select>
                          </b-form-group>
                        </b-col>


                        <b-col
                          cols="12"
                          class="d-flex justify-content-between mt-1"
                        >
                          <div v-if="data.item.second_reference_id">
                            <b-button
                              variant="outline-danger"
                              @click="removeTicketConsume(data.item.id)"
                            >
                              Hapus 
                            </b-button>
                          </div>
                        </b-col>
                      </b-row>
                  </b-popover>
                </template>
              </b-table>
            </b-col>
          </b-row>
        </b-card>
      </b-tab>
      <b-tab>
        <template #title>
          <feather-icon icon="LayersIcon" />
          <span>Tiket</span>
        </template>
        <b-card>
          <b-row class="match-height">
            <b-col
              xl="2"
              md="2"
            >
              <h4>Daftar Tiket</h4>
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
            </b-col>
            <b-col
              xl="2"
              md="2"
            >
              <b-button
                size="sm"
                v-ripple.400="'rgba(113, 102, 240, 0.15)'"
                variant="primary"
                pill
                :to="{ name: 'inventory-ticket-add' }"
              >
                Tambah
              </b-button>
            </b-col>
          </b-row>
          <b-row class="match-height mt-2">
            <b-col
              xl="6"
              md="6"
            >
            <b-table
              small
              ref="refTicketTable"
              :items="ticketData"
              :fields="ticketFields"
              class="mb-0 mt-10"
              responsive
              striped
              show-empty
              empty-text="No matching records found"
            > 
              <!-- code -->
              <template #cell(code)="data">
                <div class="d-flex flex-column">
                  <span class="font-weight-bolder mb-25">{{ data.item.code }}</span>
                </div>
              </template>

              <!-- code -->
              <template #cell(post_date)="data">
                <div class="d-flex flex-column">
                  <span class="font-weight-bolder mb-25">{{ data.item.post_date | formatDateWithTime }}</span>
                </div>
              </template>

              <!-- status -->
              <template #cell(status)="data">
                <div class="d-flex align-items-center">
                  <b-avatar
                    class="mr-1" size="18"
                    :variant="data.item.status == 'open' ? 'success' : 'danger'"
                  >
                    <feather-icon
                      size="15"
                      :icon="data.item.status == 'open' ? 'CheckIcon' : 'XIcon'"
                    />
                  </b-avatar>
                  <span>{{ data.item.status }}</span>
                </div>
              </template>

              <template #cell(action)="data">
                <b-link
                  :to="{ name: 'display-ticket', params: { id: data.item.id } }" 
                  class="font-weight-bold"
                >
                  <span class="text-truncate overflow-hidden">
                    View
                  </span>
                </b-link>
              </template>
            </b-table>
            </b-col>
          </b-row>
          <b-row class="match-height">
            <b-col
              xl="6"
              md="6"
            >
              <!-- <dashboard-table-receive :tableData="inventoryReceiveData" :tableColumns="inventoryReceiveFields" /> -->
            </b-col>
          </b-row>

        </b-card>
      </b-tab>
    </b-tabs>
  </b-card>
</template>

<script>
import {
  BCard, BRow, BCol, BFormInput, BButton, BTable, BMedia, BAvatar, BLink, BPopover,
  BBadge, BDropdown, BDropdownItem, BPagination, BTooltip,VBToggle, BTabs, BTab, BFormGroup,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import vSelect from 'vue-select'
import { ref, onUnmounted, computed, watch } from '@vue/composition-api'
import store from '@/store'
import gudangStoreModule from '../gudangStoreModule'
import Ripple from 'vue-ripple-directive'
export default {
  components: {
    BCard,
    BPopover,
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
    BTabs,
    BTab,
    BFormGroup,

    vSelect,
  },
  directives: {

    'b-toggle': VBToggle,
    Ripple,
  },
  methods: {
    assignTicketReceive(smID,val) {
      if(val) {
        store.dispatch('app-inventory/assignTicketToStockMutation', { 
          stockMutationID:  smID,
          ticketID: val
        }).then(response => {
          this.$refs[`form-receive-popover-${smID}`].$emit('close')
        }).catch(error => {
        })
      }
    },
    assignTicketConsume(smID,val) {
      if(val) {
        store.dispatch('app-inventory/assignTicketToStockMutation', { 
          stockMutationID:  smID,
          ticketID: val
        }).then(response => {
          this.$refs[`form-consume-popover-${smID}`].$emit('close')
        }).catch(error => {
        })
      }
    },
    removeTicketReceive(smID) {
        store.dispatch('app-inventory/removeTicketFromStockMutation', { 
          stockMutationID:  smID
        }).then(response => {
          this.refetchDataReceive()
          this.refetchDataConsume()
          this.$refs[`form-receive-popover-${smID}`].$emit('close')
        }).catch(error => {
        })
    },
    removeTicketConsume(smID) {
        store.dispatch('app-inventory/removeTicketFromStockMutation', { 
          stockMutationID:  smID
        }).then(response => {
          this.refetchDataReceive()
          this.refetchDataConsume()
          this.$refs[`form-consume-popover-${smID}`].$emit('close')
        }).catch(error => {
        })
    },
  },
  mounted() {
    this.fetchAllTickets()
  },
  setup() {
    const INVENTORY_APP_STORE_MODULE_NAME = 'app-inventory'

    // Register module
    if (!store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.registerModule(INVENTORY_APP_STORE_MODULE_NAME, gudangStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(INVENTORY_APP_STORE_MODULE_NAME)) store.unregisterModule(INVENTORY_APP_STORE_MODULE_NAME)
    })

    const ticketData = (ctx, callback) => {
      store.dispatch('app-inventory/fetchAllTickets')
        .then((response) => {
            callback(response.data.payload)
        })
    }

    const ticketFields = ref([
      {label: 'Kode', key: 'code'},
      {label: 'Nama', key: 'name'},
      // {label: 'Tgl post', key: 'post_date'},
      {label: 'Status', key: 'status'},
      {label: 'Aksi', key: 'action'}
    ])

    const inventoryReceiveData = (ctx, callback) => {
      store.dispatch('app-inventory/fetchReceivesDB')
        .then((response) => {
            callback(response.data.payload)
        })
    }

    const inventoryReceiveFields = ref([
      {label: 'Nama Barang', key: 'name'},
      {label: 'Tanggal', key: 'post_date'},
      {label: 'Diterima dari', key: 'giver'},
      {label: 'Jumlah', key: 'amount'},
      {label: 'Satuan', key: 'unit'},
      {label: 'Harga', key: 'price'},
      {label: 'Aksi', key: 'action'}
    ])


    const inventoryConsumeData = (ctx, callback) => {
      store.dispatch('app-inventory/fetchConsumesDB')
        .then((response) => {
            callback(response.data.payload)
        })
    }
    
    const inventoryConsumeFields = ref([
      {label: 'Nama Barang', key: 'name'},
      {label: 'Tanggal', key: 'post_date'},
      {label: 'Penerima', key: 'giver'},
      {label: 'Jumlah', key: 'amount'},
      {label: 'Satuan', key: 'unit'},
      {label: 'Aksi', key: 'action'}
    ])

    const refTicketTable = ref(null)
    const refReceiveTable = ref(null)
    const refConsumeTable = ref(null)
    const perPageT = ref(20)
    const totalTickets = ref(0)
    const currentPageT = ref(1)
    const perPageOptionsT = [10, 25, 50, 100]
    const sortByT = ref('id')
    const isSortDirDescT = ref(true)
    const codeFilterT = ref(null)
    const dateFilterT = ref(null)
    const searchQueryT = ref('')

    const refTicketOptions = ref(null)
    const tiket = ref({

    })
    
    const dataMeta = computed(() => {
        const localItemsCountT = refTicketTable.value ? refTicketTable.value.localItems.length : 0
        return {
            fromt: perPageT.value * (currentPageT.value - 1) + (localItemsCountT ? 1 : 0),
            tot: perPageT.value * (currentPageT.value - 1) + localItemsCountT,
            oft: totalTickets.value,
        }
    })

    const refetchDataReceive = () => {
        refReceiveTable.value.refresh()
    }
    const refetchDataConsume = () => {
        refConsumeTable.value.refresh()
    }
    const refetchDataT = () => {
        refTicketTable.value.refresh()
    }
    watch([currentPageT, perPageT, searchQueryT, codeFilterT, dateFilterT], () => {
        refetchData()
    })

    const onDeleteT = (params) => {
      store.dispatch('app-inventory/removeTicket', params)
        .then(() => {
          emit('refetch-data-ticket')
        })
    }
    function fetchAllTickets()  {
      store
        .dispatch('app-inventory/fetchTicketOptions')
        .then((response) => {
            refTicketOptions.value = response.data.payload
        })
        .catch(() => {
            toast({
                component: ToastificationContent,
                props: {
                    title: "Error fetching tickets",
                    icon: 'AlertTriangleIcon',
                    variant: 'danger',
                },
            })
        })
    }
    return {
      perPageT,
      currentPageT,
      totalTickets,
      dataMeta,
      perPageOptionsT,
      searchQueryT,
      sortByT,
      isSortDirDescT,
      refTicketTable,
      refConsumeTable,
      refReceiveTable,
      refTicketOptions,

      codeFilterT,
      dateFilterT,

      refetchDataT,
      refetchDataReceive,
      refetchDataConsume,

      inventoryReceiveData,
      inventoryReceiveFields,

      inventoryConsumeData,
      inventoryConsumeFields,

      ticketData,
      ticketFields,

      fetchAllTickets,
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
</style>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>
