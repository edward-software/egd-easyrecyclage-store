'use strict';

import '../scss/public.scss';

global.$ = global.jQuery = require('jquery');

const moment =  require('moment');
global.moment = moment;

import 'bootstrap';
import 'eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min';

const Swal = require('sweetalert2');
global.Swal = Swal;