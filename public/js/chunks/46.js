(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[46],{

/***/ "./resources/js/src/views/gudang/gudangStoreModule.js":
/*!************************************************************!*\
  !*** ./resources/js/src/views/gudang/gudangStoreModule.js ***!
  \************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.array.concat.js */ "./node_modules/core-js/modules/es.array.concat.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @axios */ "./resources/js/src/libs/axios.js");



/* harmony default export */ __webpack_exports__["default"] = ({
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    searchInventory: function searchInventory(ctx, keyword) {
      return _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/search', {
        params: {
          q: keyword
        }
      });
    },
    fetchAllInventory: function fetchAllInventory(ctx, qParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories', {
          params: qParams
        }).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchTickets: function fetchTickets(ctx, qParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('tickets', {
          params: qParams
        }).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchInventoryOptions: function fetchInventoryOptions(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchInventory: function fetchInventory(ctx, _ref) {
      var id = _ref.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("inventories/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    recalculateStock: function recalculateStock(ctx, _ref2) {
      var id = _ref2.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("inventories/recalculateStock/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchInventoryEdit: function fetchInventoryEdit(ctx, _ref3) {
      var id = _ref3.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("inventoriesEdit/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addInventoryImage: function addInventoryImage(ctx, _ref4) {
      var id = _ref4.id,
          Image = _ref4.Image;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post("inventories/".concat(id, "/add-image/"), Image).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addInventory: function addInventory(ctx, inventoryData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventories', inventoryData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    updateInventory: function updateInventory(ctx, _ref5) {
      var id = _ref5.id,
          inventory = _ref5.inventory;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].put("inventories/".concat(id), inventory).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeInventory: function removeInventory(ctx, _ref6) {
      var id = _ref6.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"]["delete"]("inventories/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAllVendor: function fetchAllVendor(ctx, queryParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('vendors', {
          params: queryParams
        }).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchVendorOptions: function fetchVendorOptions(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('vendors/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAllTickets: function fetchAllTickets(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('tickets/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAccountOptions: function fetchAccountOptions(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('journals/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAccountExpenseOptions: function fetchAccountExpenseOptions(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('journals/expenses').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchTicketOptions: function fetchTicketOptions(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('tickets/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addAccount: function addAccount(ctx, ledgerData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('journals', ledgerData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeAccount: function removeAccount(ctx, _ref7) {
      var id = _ref7.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"]["delete"]("journals/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addVendor: function addVendor(ctx, vendorData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('vendors', vendorData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeVendor: function removeVendor(ctx, _ref8) {
      var id = _ref8.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"]["delete"]("vendors/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    receiveInventory: function receiveInventory(ctx, receiveData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventories/receive', receiveData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    consumeInventory: function consumeInventory(ctx, consumeData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventories/consume', consumeData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchInventoryWithMutation: function fetchInventoryWithMutation(ctx, queryParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/transaction', {
          params: queryParams
        }).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addTagInventory: function addTagInventory(ctx, tagData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post("inventories/tag/add", tagData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeTagInventory: function removeTagInventory(ctx, tagData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventories/tag/remove', tagData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    formReceiveData: function formReceiveData(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/formReceiveData').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    listFormData: function listFormData(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/listFormData').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    consumeFormData: function consumeFormData(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/consumeFormData').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    assignTicketToStockMutation: function assignTicketToStockMutation(ctx, aParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("inventories/assignTicket/".concat(aParams.stockMutationID, "/").concat(aParams.ticketID)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeTicketFromStockMutation: function removeTicketFromStockMutation(ctx, aParams) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("inventories/removeTicket/".concat(aParams.stockMutationID)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchReceivesDB: function fetchReceivesDB(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/receivesDB').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchConsumesDB: function fetchConsumesDB(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/consumesDB').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchReceives: function fetchReceives(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/receives').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchConsumes: function fetchConsumes(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventories/consumes').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    inventoryReceivePDF: function inventoryReceivePDF(ctx, _ref9) {
      var id = _ref9.id;
      return new Promise(function (resolve, reject) {
        Object(_axios__WEBPACK_IMPORTED_MODULE_2__["default"])({
          url: "inventory-receives/showPDF/".concat(id),
          method: 'GET' // { responseType: 'blob' }
          // responseType: "application/pdf"
          // responseType: 'blob'

        }).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchTicket: function fetchTicket(ctx, _ref10) {
      var id = _ref10.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("tickets/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addTicket: function addTicket(ctx, ticketData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('tickets', ticketData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    closeTicket: function closeTicket(ctx, ticketID) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("tickets/close/".concat(ticketID)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAllGroups: function fetchAllGroups(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventory-groups/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeGroup: function removeGroup(ctx, _ref11) {
      var id = _ref11.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"]["delete"]("inventory-groups/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addGroup: function addGroup(ctx, groupData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventory-groups', groupData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    fetchAllUnits: function fetchAllUnits(ctx) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('inventory-units/all').then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    removeUnit: function removeUnit(ctx, _ref12) {
      var id = _ref12.id;
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"]["delete"]("inventory-units/".concat(id)).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    },
    addUnit: function addUnit(ctx, unitData) {
      return new Promise(function (resolve, reject) {
        _axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('inventory-units', unitData).then(function (response) {
          return resolve(response);
        })["catch"](function (error) {
          return reject(error);
        });
      });
    }
  }
});

/***/ })

}]);