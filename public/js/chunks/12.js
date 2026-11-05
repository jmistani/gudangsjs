(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[12],{

/***/ "./resources/js/src/@core/auth/jwt/jwtDefaultConfig.js":
/*!*************************************************************!*\
  !*** ./resources/js/src/@core/auth/jwt/jwtDefaultConfig.js ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  // Endpoints
  loginEndpoint: '/jwt/login',
  registerEndpoint: '/jwt/register',
  refreshEndpoint: '/jwt/refresh-token',
  logoutEndpoint: '/jwt/logout',
  // This will be prefixed in authorization header with token
  // e.g. Authorization: Bearer <token>
  tokenType: 'Bearer',
  // Value of this property will be used as key to store JWT token in storage
  storageTokenKeyName: 'accessToken',
  storageRefreshTokenKeyName: 'refreshToken'
});

/***/ }),

/***/ "./resources/js/src/@core/auth/jwt/jwtService.js":
/*!*******************************************************!*\
  !*** ./resources/js/src/@core/auth/jwt/jwtService.js ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return JwtService; });
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/objectSpread2.js */ "./node_modules/@babel/runtime/helpers/esm/objectSpread2.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_classCallCheck_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_createClass_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/createClass.js */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/defineProperty.js */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.array.concat.js */ "./node_modules/core-js/modules/es.array.concat.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/es.array.filter.js */ "./node_modules/core-js/modules/es.array.filter.js");
/* harmony import */ var core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _jwtDefaultConfig__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./jwtDefaultConfig */ "./resources/js/src/@core/auth/jwt/jwtDefaultConfig.js");









var JwtService = /*#__PURE__*/function () {
  // Will be used by this service for making API calls
  // jwtConfig <= Will be used by this service
  // For Refreshing Token
  // For Refreshing Token
  function JwtService(axiosIns, jwtOverrideConfig) {
    var _this = this;

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_classCallCheck_js__WEBPACK_IMPORTED_MODULE_1__["default"])(this, JwtService);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "axiosIns", null);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "jwtConfig", Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])({}, _jwtDefaultConfig__WEBPACK_IMPORTED_MODULE_7__["default"]));

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "isAlreadyFetchingAccessToken", false);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "subscribers", []);

    this.axiosIns = axiosIns;
    this.jwtConfig = Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])(Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])({}, this.jwtConfig), jwtOverrideConfig); // Request Interceptor

    this.axiosIns.interceptors.request.use(function (config) {
      // Get token from localStorage
      var accessToken = _this.getToken(); // If token is present add it to request's Authorization Header


      if (accessToken) {
        // eslint-disable-next-line no-param-reassign
        config.headers.Authorization = "".concat(_this.jwtConfig.tokenType, " ").concat(accessToken);
      }

      return config;
    }, function (error) {
      return Promise.reject(error);
    }); // Add request/response interceptor

    this.axiosIns.interceptors.response.use(function (response) {
      return response;
    }, function (error) {
      // const { config, response: { status } } = error
      var config = error.config,
          response = error.response;
      var originalRequest = config; // if (status === 401) {

      if (response && response.status === 401) {
        if (!_this.isAlreadyFetchingAccessToken) {
          _this.isAlreadyFetchingAccessToken = true;

          _this.refreshToken().then(function (r) {
            _this.isAlreadyFetchingAccessToken = false; // Update accessToken in localStorage

            _this.setToken(r.data.accessToken);

            _this.setRefreshToken(r.data.refreshToken);

            _this.onAccessTokenFetched(r.data.accessToken);
          });
        }

        var retryOriginalRequest = new Promise(function (resolve) {
          _this.addSubscriber(function (accessToken) {
            // Make sure to assign accessToken according to your response.
            // Check: https://pixinvent.ticksy.com/ticket/2413870
            // Change Authorization header
            originalRequest.headers.Authorization = "".concat(_this.jwtConfig.tokenType, " ").concat(accessToken);
            resolve(_this.axiosIns(originalRequest));
          });
        });
        return retryOriginalRequest;
      }

      return Promise.reject(error);
    });
  }

  Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_createClass_js__WEBPACK_IMPORTED_MODULE_2__["default"])(JwtService, [{
    key: "onAccessTokenFetched",
    value: function onAccessTokenFetched(accessToken) {
      this.subscribers = this.subscribers.filter(function (callback) {
        return callback(accessToken);
      });
    }
  }, {
    key: "addSubscriber",
    value: function addSubscriber(callback) {
      this.subscribers.push(callback);
    }
  }, {
    key: "getToken",
    value: function getToken() {
      return localStorage.getItem(this.jwtConfig.storageTokenKeyName);
    }
  }, {
    key: "getRefreshToken",
    value: function getRefreshToken() {
      return localStorage.getItem(this.jwtConfig.storageRefreshTokenKeyName);
    }
  }, {
    key: "setToken",
    value: function setToken(value) {
      localStorage.setItem(this.jwtConfig.storageTokenKeyName, value);
    }
  }, {
    key: "setRefreshToken",
    value: function setRefreshToken(value) {
      localStorage.setItem(this.jwtConfig.storageRefreshTokenKeyName, value);
    }
  }, {
    key: "login",
    value: function login() {
      var _this$axiosIns;

      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      return (_this$axiosIns = this.axiosIns).post.apply(_this$axiosIns, [this.jwtConfig.loginEndpoint].concat(args));
    }
  }, {
    key: "register",
    value: function register() {
      var _this$axiosIns2;

      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args[_key2] = arguments[_key2];
      }

      return (_this$axiosIns2 = this.axiosIns).post.apply(_this$axiosIns2, [this.jwtConfig.registerEndpoint].concat(args));
    }
  }, {
    key: "refreshToken",
    value: function refreshToken() {
      return this.axiosIns.post(this.jwtConfig.refreshEndpoint, {
        refreshToken: this.getRefreshToken()
      });
    }
  }]);

  return JwtService;
}();



/***/ }),

/***/ "./resources/js/src/@core/auth/jwt/useJwt.js":
/*!***************************************************!*\
  !*** ./resources/js/src/@core/auth/jwt/useJwt.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useJwt; });
/* harmony import */ var _jwtService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./jwtService */ "./resources/js/src/@core/auth/jwt/jwtService.js");

function useJwt(axiosIns, jwtOverrideConfig) {
  var jwt = new _jwtService__WEBPACK_IMPORTED_MODULE_0__["default"](axiosIns, jwtOverrideConfig);
  return {
    jwt: jwt
  };
}

/***/ }),

/***/ "./resources/js/src/@core/auth/sanctum/sanctumDefaultConfig.js":
/*!*********************************************************************!*\
  !*** ./resources/js/src/@core/auth/sanctum/sanctumDefaultConfig.js ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  // Endpoints
  loginEndpoint: '/auth/login',
  registerEndpoint: '/auth/register',
  refreshEndpoint: '/auth/refresh',
  logoutEndpoint: '/auth/logout',
  // This will be prefixed in authorization header with token
  // e.g. Authorization: Bearer <token>
  tokenType: 'Bearer',
  // Value of this property will be used as key to store JWT token in storage
  storageTokenKeyName: 'accessToken'
});

/***/ }),

/***/ "./resources/js/src/@core/auth/sanctum/sanctumService.js":
/*!***************************************************************!*\
  !*** ./resources/js/src/@core/auth/sanctum/sanctumService.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return SanctumService; });
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/objectSpread2.js */ "./node_modules/@babel/runtime/helpers/esm/objectSpread2.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_classCallCheck_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_createClass_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/createClass.js */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/@babel/runtime/helpers/esm/defineProperty.js */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.array.concat.js */ "./node_modules/core-js/modules/es.array.concat.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! core-js/modules/es.array.filter.js */ "./node_modules/core-js/modules/es.array.filter.js");
/* harmony import */ var core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_filter_js__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _sanctumDefaultConfig__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./sanctumDefaultConfig */ "./resources/js/src/@core/auth/sanctum/sanctumDefaultConfig.js");











var SanctumService = /*#__PURE__*/function () {
  // Will be used by this service for making API calls
  // sanctumConfig <= Will be used by this service
  // For Refreshing Token
  // For Refreshing Token
  function SanctumService(axiosIns, sanctumOverrideConfig) {
    var _this = this;

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_classCallCheck_js__WEBPACK_IMPORTED_MODULE_1__["default"])(this, SanctumService);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "axiosIns", null);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "sanctumConfig", Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])({}, _sanctumDefaultConfig__WEBPACK_IMPORTED_MODULE_9__["default"]));

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "isAlreadyFetchingAccessToken", false);

    Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_defineProperty_js__WEBPACK_IMPORTED_MODULE_3__["default"])(this, "subscribers", []);

    this.axiosIns = axiosIns;
    this.sanctumConfig = Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])(Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_objectSpread2_js__WEBPACK_IMPORTED_MODULE_0__["default"])({}, this.sanctumConfig), sanctumOverrideConfig); // Request Interceptor

    this.axiosIns.interceptors.request.use(function (config) {
      // Get token from localStorage
      var accessToken = _this.getToken(); // If token is present add it to request's Authorization Header


      if (accessToken) {
        // eslint-disable-next-line no-param-reassign
        config.headers.Authorization = "".concat(_this.sanctumConfig.tokenType, " ").concat(accessToken);
      }

      return config;
    }, function (error) {
      return Promise.reject(error);
    }); // Add request/response interceptor

    this.axiosIns.interceptors.response.use(function (response) {
      return response;
    }, function (error) {
      // const { config, response: { status } } = error
      var config = error.config,
          response = error.response;
      var originalRequest = config; // if (status === 401) {

      if (response && response.status === 401) {
        //               window.location.replace("https://gudangsjsv2.serasijayasejati.com/login")
        window.location.replace("https://gudangsjs.local/login");
      }

      return Promise.reject(error);
    });
  }

  Object(D_wamp_www_gudangsjs_node_modules_babel_runtime_helpers_esm_createClass_js__WEBPACK_IMPORTED_MODULE_2__["default"])(SanctumService, [{
    key: "onAccessTokenFetched",
    value: function onAccessTokenFetched(accessToken) {
      this.subscribers = this.subscribers.filter(function (callback) {
        return callback(accessToken);
      });
    }
  }, {
    key: "addSubscriber",
    value: function addSubscriber(callback) {
      this.subscribers.push(callback);
    }
  }, {
    key: "getToken",
    value: function getToken() {
      return localStorage.getItem(this.sanctumConfig.storageTokenKeyName);
    }
  }, {
    key: "getRefreshToken",
    value: function getRefreshToken() {
      return localStorage.getItem(this.sanctumConfig.storageRefreshTokenKeyName);
    }
  }, {
    key: "setToken",
    value: function setToken(value) {
      localStorage.setItem(this.sanctumConfig.storageTokenKeyName, value);
    }
  }, {
    key: "setRefreshToken",
    value: function setRefreshToken(value) {
      localStorage.setItem(this.sanctumConfig.storageRefreshTokenKeyName, value);
    }
  }, {
    key: "login",
    value: function login() {
      var _this$axiosIns;

      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      return (_this$axiosIns = this.axiosIns).post.apply(_this$axiosIns, [this.sanctumConfig.loginEndpoint].concat(args));
    }
  }, {
    key: "register",
    value: function register() {
      var _this$axiosIns2;

      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args[_key2] = arguments[_key2];
      }

      return (_this$axiosIns2 = this.axiosIns).post.apply(_this$axiosIns2, [this.sanctumConfig.registerEndpoint].concat(args));
    }
  }, {
    key: "refreshToken",
    value: function refreshToken() {
      return this.axiosIns.get(this.sanctumConfig.refreshEndpoint); // return this.axiosIns.post(this.sanctumConfig.refreshEndpoint, {
      //     refreshToken: this.getRefreshToken(),
      // })
    }
  }]);

  return SanctumService;
}();



/***/ }),

/***/ "./resources/js/src/@core/auth/sanctum/useSanctum.js":
/*!***********************************************************!*\
  !*** ./resources/js/src/@core/auth/sanctum/useSanctum.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return useSanctum; });
/* harmony import */ var _sanctumService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./sanctumService */ "./resources/js/src/@core/auth/sanctum/sanctumService.js");

function useSanctum(axiosIns, sanctumOverrideConfig) {
  var sanctum = new _sanctumService__WEBPACK_IMPORTED_MODULE_0__["default"](axiosIns, sanctumOverrideConfig);
  return {
    sanctum: sanctum
  };
}

/***/ }),

/***/ "./resources/js/src/auth/jwt/useJwt.js":
/*!*********************************************!*\
  !*** ./resources/js/src/auth/jwt/useJwt.js ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _core_auth_jwt_useJwt__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @core/auth/jwt/useJwt */ "./resources/js/src/@core/auth/jwt/useJwt.js");
/* harmony import */ var _axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @axios */ "./resources/js/src/libs/axios.js");



var _useJwt = Object(_core_auth_jwt_useJwt__WEBPACK_IMPORTED_MODULE_0__["default"])(_axios__WEBPACK_IMPORTED_MODULE_1__["default"], {}),
    jwt = _useJwt.jwt;

/* harmony default export */ __webpack_exports__["default"] = (jwt);

/***/ }),

/***/ "./resources/js/src/auth/sanctum/useSanctum.js":
/*!*****************************************************!*\
  !*** ./resources/js/src/auth/sanctum/useSanctum.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _core_auth_sanctum_useSanctum__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @core/auth/sanctum/useSanctum */ "./resources/js/src/@core/auth/sanctum/useSanctum.js");
/* harmony import */ var _axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @axios */ "./resources/js/src/libs/axios.js");



var _useSanctum = Object(_core_auth_sanctum_useSanctum__WEBPACK_IMPORTED_MODULE_0__["default"])(_axios__WEBPACK_IMPORTED_MODULE_1__["default"], {}),
    sanctum = _useSanctum.sanctum;

/* harmony default export */ __webpack_exports__["default"] = (sanctum);

/***/ })

}]);