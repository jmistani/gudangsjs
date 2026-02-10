<template>
  <div class="container">
     <b-row>
        <b-col md="6">
           <b-button variant="info" v-print="printObj">Print</b-button>
           <b-button variant="info" @click="printFrame('printDiv')">Print</b-button>
        </b-col>
     </b-row>
      <iframe
         id="printDiv" 
         ref="reportContent"
         src="http://localhost/api/inventory-receives/showPDF/1"
         width="100%"
         height="500"
         frameborder="0">
      </iframe>
   </div>
</template>
<script>

import {BButton, BRow, BCol, BAvatar, BTable} from 'bootstrap-vue'
import VueHtml2pdf from 'vue-html2pdf'
import axios from '@axios'
import Print from 'vue-iframe-print'

export default {
   data() {
      return {
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
      this.loadReport()
   },
   methods: {
        /*
            Generate Report using refs and calling the
            refs function generatePdf()
        */
        generateReport () {
            this.$refs.html2Pdf.generatePdf()
        },
        loadReport() {
           axios
            .get('inventory-receives/showPDF/1').then((response) => {
               this.reportContent = response.data
            })
        },
         print() {
            var prtContent = document.getElementById("reportContent");
            var iFrameBody = this.$refs.reportContent.contentWindow.document
//            doc.fromHTML(iFrameBody, 15, vOffset)
            
            var WinPrint = window.open('http://localhost/api/inventory-receives/showPDF/1', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
            WinPrint.document.close();
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
      BAvatar,
      BRow,
      BCol,
      BButton,
      VueHtml2pdf,
    }
}
</script>

<style lang="scss">
@import '@core/scss/inventory-receive.scss';