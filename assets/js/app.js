'use strict';

require('../scss/global.scss');

global.$ = global.jQuery = require('jquery');

require('bootstrap');
require("datatables.net-bs4");
require("datatables.net-buttons-bs4");
require("datatables.net-select-bs4");

const Swal = require('sweetalert2');
global.Swal = Swal;