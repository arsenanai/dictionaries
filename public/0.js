(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

/***/ "./resources/js/api/auth.js":
/*!**********************************!*\
  !*** ./resources/js/api/auth.js ***!
  \**********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_0__);
 //const axios = () => import('axios');

var client = axios__WEBPACK_IMPORTED_MODULE_0___default.a.create({
  baseURL: '/api'
});
/* harmony default export */ __webpack_exports__["default"] = ({
  login: function login(params) {
    return client.post('login', params);
  },
  register: function register(params) {
    return client.post("register", params);
  },
  logout: function logout(params) {
    return client.get("logout", params);
  },
  user: function user(params) {
    return client.get("user", params);
  }
});

/***/ })

}]);