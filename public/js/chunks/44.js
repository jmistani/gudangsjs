(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[44],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap-vue */ "./node_modules/bootstrap-vue/esm/index.js");
/* harmony import */ var vue_html2pdf__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-html2pdf */ "./node_modules/vue-html2pdf/dist/vue-html2pdf.esm.js");
/* harmony import */ var _axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @axios */ "./resources/js/src/libs/axios.js");
/* harmony import */ var vue_iframe_print__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vue-iframe-print */ "./node_modules/vue-iframe-print/lib/tag-textarea.umd.min.js");
/* harmony import */ var vue_iframe_print__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(vue_iframe_print__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _router__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/router */ "./resources/js/src/router/index.js");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





/* harmony default export */ __webpack_exports__["default"] = ({
  data: function data() {
    return {
      invTicketID: null,
      reportURL: '',
      printObj: {
        id: "printDiv",
        popTitle: 'vue-iframe-print',
        extraCss: '',
        extraHead: '<meta http-equiv="Content-Language"content="zh-cn"/>'
      },
      reportContent: ''
    };
  },
  // props: [htmlurl],
  directives: {
    'print': vue_iframe_print__WEBPACK_IMPORTED_MODULE_3___default.a
  },
  mounted: function mounted() {
    this.ticketID = _router__WEBPACK_IMPORTED_MODULE_4__["default"].currentRoute.params.id;
    this.reportURL = "http://localhost/api/tickets/showPDF/".concat(this.ticketID);
  },
  methods: {
    /*
        Generate Report using refs and calling the
        refs function generatePdf()
    */
    generateReport: function generateReport() {
      this.$refs.html2Pdf.generatePdf();
    },
    print: function print() {
      var prtContent = document.getElementById("reportContent");
      var iFrameBody = this.$refs.reportContent.contentWindow.document; //            doc.fromHTML(iFrameBody, 15, vOffset)

      var WinPrint = window.open(this.reportURL, '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0'); //            WinPrint.document.close();

      WinPrint.focus();
      setTimeout(function () {
        mywindow.print();
      }, 10);
      WinPrint.close();
    },
    printFrame: function printFrame(id) {
      var frm = document.getElementById(id).contentWindow;
      frm.focus(); // focus on contentWindow is needed on some ie versions

      frm.print();
      return false;
    }
  },
  components: {
    BTable: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BTable"],
    BCard: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BCard"],
    BCardBody: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BCardBody"],
    BButtonGroup: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BButtonGroup"],
    BAvatar: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BAvatar"],
    BRow: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BRow"],
    BCol: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BCol"],
    BButton: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BButton"],
    VueHtml2pdf: vue_html2pdf__WEBPACK_IMPORTED_MODULE_1__["default"],
    BButtonToolbar: bootstrap_vue__WEBPACK_IMPORTED_MODULE_0__["BButtonToolbar"]
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7& ***!
  \*****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "container" },
    [
      _c(
        "b-row",
        { staticClass: "mx-0" },
        [
          _c("b-col", { attrs: { md: "12" } }, [
            _c(
              "div",
              { staticClass: "mb-2" },
              [
                _c(
                  "b-button-toolbar",
                  {
                    staticClass: "d-flex justify-content-center",
                    attrs: { "aria-label": "Inventory Ticket" },
                  },
                  [
                    _c(
                      "b-button-group",
                      { staticClass: "mx-0" },
                      [
                        _c(
                          "b-button",
                          {
                            staticClass: "mb-0",
                            attrs: { size: "sm", pill: "", variant: "primary" },
                            on: {
                              click: function ($event) {
                                return _vm.printFrame("printDiv")
                              },
                            },
                          },
                          [_vm._v("Print")]
                        ),
                        _vm._v(" "),
                        _c(
                          "b-button",
                          {
                            staticClass: "mb-0",
                            attrs: {
                              size: "sm",
                              pill: "",
                              variant: "outline-primary",
                            },
                            on: {
                              click: function ($event) {
                                return _vm.$router.go(-1)
                              },
                            },
                          },
                          [_vm._v("Kembali")]
                        ),
                      ],
                      1
                    ),
                  ],
                  1
                ),
              ],
              1
            ),
          ]),
        ],
        1
      ),
      _vm._v(" "),
      _c("iframe", {
        ref: "reportContent",
        attrs: {
          id: "printDiv",
          src: this.reportURL,
          width: "100%",
          height: "500",
          frameborder: "0",
        },
      }),
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/src/views/gudang/barang/DisplayTicket.vue":
/*!****************************************************************!*\
  !*** ./resources/js/src/views/gudang/barang/DisplayTicket.vue ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DisplayTicket.vue?vue&type=template&id=62892bd7& */ "./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7&");
/* harmony import */ var _DisplayTicket_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DisplayTicket.vue?vue&type=script&lang=js& */ "./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _DisplayTicket_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__["render"],
  _DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/src/views/gudang/barang/DisplayTicket.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************!*\
  !*** ./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DisplayTicket_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib??ref--4-0!../../../../../../node_modules/vue-loader/lib??vue-loader-options!./DisplayTicket.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_DisplayTicket_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7&":
/*!***********************************************************************************************!*\
  !*** ./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7& ***!
  \***********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../../node_modules/vue-loader/lib??vue-loader-options!./DisplayTicket.vue?vue&type=template&id=62892bd7& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/src/views/gudang/barang/DisplayTicket.vue?vue&type=template&id=62892bd7&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_DisplayTicket_vue_vue_type_template_id_62892bd7___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);