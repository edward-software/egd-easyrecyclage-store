'use strict';

import '../scss/global.scss';

global.$ = global.jQuery = require('jquery');

import 'bootstrap';
import 'datatables.net-bs4';
import 'datatables.net-buttons-bs4';
import 'datatables.net-select-bs4';

import Swal from 'sweetalert2';
global.Swal = Swal;
