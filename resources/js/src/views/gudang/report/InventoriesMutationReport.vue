<template>
  <div class="container">
      <validation-observer
         #default="{ handleSubmit }"
         ref="refFormObserver"
      >
      <!-- Body -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
         <b-form
            @submit.prevent="handleSubmit(onSubmit)"
            @reset.prevent="resetForm"
         >
            <b-row>
               <b-col cols="6">
                  <b-form-group
                     label="Tanggal Awal"
                     label-for="date-start"
                  >
                     <validation-provider
                        #default="validationContext"
                        name="TanggalAwal"
                        rules="required"
                     >
                        <b-form-datepicker
                           id="date-start"  
                           :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                           locale="id"
                           v-model="refReport.per_date_start"
                           :format="formatDate"
                        ></b-form-datepicker>
                        <b-form-invalid-feedback :state="validationContext.errors.length > 1" class="text-danger">
                           {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                     </validation-provider>
                  </b-form-group>
               </b-col>               
               <b-col cols="6">
                  <b-form-group
                     label="Tanggal Akhir"
                     label-for="date-end"
                  >
                     <validation-provider
                        #default="validationContext"
                        name="TanggalAkhir"
                        rules="required"
                     >
                        <b-form-datepicker
                           id="date-end"  
                           :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                           locale="id"
                           v-model="refReport.per_date_end"
                           :format="formatDate"
                        ></b-form-datepicker>
                        <b-form-invalid-feedback :state="validationContext.errors.length > 1" class="text-danger">
                           {{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                     </validation-provider>
                  </b-form-group>
               </b-col>

               <b-col md="12">
                  <b-form-group>
                     <b-button  type=submit class="primary">Tampilkan</b-button>
                  </b-form-group>
               </b-col>
            </b-row>
         </b-form>
      </div>
      </validation-observer>
     <b-row class="mx-0">
         <b-col md="12">
            <div class="mb-2">
               <b-button-toolbar class="d-flex justify-content-center" aria-label="Inventory Consume">
                  <!-- <b-button variant="info" v-print="printObj">Print</b-button> -->
                  <b-button-group class="mx-0">
                     <b-button  size="sm" class="mb-0" pill variant="primary" @click="printFrame('printDiv')">Print</b-button>
                        <b-button  size="sm" class="mb-0" pill variant="outline-primary" @click="$router.go(-1)">Kembali</b-button>
                  </b-button-group>
               </b-button-toolbar>
            </div>
        </b-col>
     </b-row>
      <iframe
         id="printDiv" 
         ref="reportContent"
         :src="refReportURL"
         width="100%"
         height="500"
         frameborder="0">
      </iframe>
   </div>
</template>
<script>

import { ValidationProvider, ValidationObserver } from 'vee-validate'
import formValidation from '@core/comp-functions/forms/form-validation'
import {BForm, BFormGroup, BFormCheckbox, BCard, BCardBody, BButtonGroup, BButton, BRow, BCol,
    BAvatar, BTable, BButtonToolbar, BFormDatepicker, BFormInvalidFeedback} from 'bootstrap-vue'
import { required } from '@validations'
import { ref } from '@vue/composition-api'
import VueHtml2pdf from 'vue-html2pdf'
import Print from 'vue-iframe-print'
import router from '@/router'
import axios from 'axios'

export default {
   components: {
      BForm,
      BTable,
      BCard,
      BCardBody,
      BButtonGroup,
      BAvatar,
      BRow,
      BCol,
      BButton,
      VueHtml2pdf,
      BButtonToolbar,
      BFormDatepicker,
      BFormCheckbox,
      BFormInvalidFeedback,
      ValidationProvider,
      ValidationObserver,
      required,
      BFormGroup,
   },
   data() {
      return {
            bygroup: true,
            reportURL: '',
            printObj: {
              id: "printDiv",
              popTitle: 'vue-iframe-print',
              extraCss: '',
              extraHead: '<meta http-equiv="Content-Language"content="zh-cn"/>'
            },
            tableData: [ 
            { nama:'john sen', email:'tebenk@gmail.com', telp:'08129298888' },
            { nama:'john sen', email:'tebenk@gmail.com', telp:'08129298888' },
            { nama:'john sen', email:'tebenk@gmail.com', telp:'08129298888' }
            ],
         tableColumns: [ 
            {key:'nama', label:'Nama'},
            {key:'email', label:'Email'},
            {key:'telp', label:'Telp'}
          ],
         reportContent: '',
      }
   },
   directives:{
      'print': Print,
   },
   mounted() {
      this.reportURL = ''
   },
   methods: {
      formatDate (date) {
         return moment(date).format('DD-MM-YYYY')
      },
        /*
            Generate Report using refs and calling the
            refs function generatePdf()
        */
        generateReport () {
            this.$refs.html2Pdf.generatePdf()
        },
         print() {
            var prtContent = document.getElementById("reportContent");
            var iFrameBody = this.$refs.reportContent.contentWindow.document
//            doc.fromHTML(iFrameBody, 15, vOffset)
            
            var WinPrint = window.open(this.reportURL, '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
//            WinPrint.document.close();
            WinPrint.focus();
            setTimeout(function(){mywindow.print();},10);
            WinPrint.close();
         },
         printFrame(id) {
            var frm = document.getElementById(id).contentWindow;
            frm.focus();// focus on contentWindow is needed on some ie versions
            frm.print();
            return false;
         },
    },
  setup(props, { emit }) {
     
   const prefilledForm = ref({
        per_date_start: null,
        per_date_end: null
    })
    
    const blankForm = ref({
        per_date_start: null,
        per_date_end: null
    })
    
    const refReport = ref(JSON.parse(JSON.stringify(prefilledForm.value)))
    const refReportURL = ref('')
    const resetReportForm = () => {
      refReport.value = JSON.parse(JSON.stringify(prefilledForm.value))
    }
    const clearReportForm = () => {
      refReport.value = JSON.parse(JSON.stringify(blankForm.value))
    }
    const onSubmit = () => {
       axios.get('https://gudangsjs.serasijayasejati.com/sanctum/csrf-cookie').then(response => {
       })
       //check for expired token
       refReportURL.value = `https://gudangsjs.serasijayasejati.com/api/inventory/report/inventory-mutation/${refReport.value.per_date_start}/${refReport.value.per_date_end}`
      //  if(refReportURL.value.status != 200)
      //         router.push({ name: 'login' })
    }

   const {
         refFormObserver,
         getValidationState,
         resetForm,
      } = formValidation(resetReportForm,clearReportForm)
    
    return {
      refReport,
      refReportURL,
      onSubmit,
      required,

      refFormObserver,
      getValidationState,
      resetForm,
    }
    
    }

}
</script>

<style lang="scss">
@import '@core/scss/inventory-consume.scss';