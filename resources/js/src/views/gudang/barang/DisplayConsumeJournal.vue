<template>
  <div class="container">
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
         :src="this.reportURL"
         width="100%"
         height="500"
         frameborder="0">
      </iframe>
   </div>
</template>
<script>

import {BCard, BCardBody, BButtonGroup, BButton, BRow, BCol, BAvatar, BTable, BButtonToolbar} from 'bootstrap-vue'
import VueHtml2pdf from 'vue-html2pdf'
import axios from '@axios'
import Print from 'vue-iframe-print'
import router from '@/router'

export default {
   data() {
      return {
            invConsumeID: null,
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
      this.invConsumeID = router.currentRoute.params.id
      this.reportURL = `https://gudangsjs.serasijayasejati.com/api/inventory-consumes/showPDF/${this.invConsumeID}`
   },
   methods: {
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
         }
    },
 
    components: {
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
    }
}
</script>

<style lang="scss">
@import '@core/scss/inventory-consume.scss';