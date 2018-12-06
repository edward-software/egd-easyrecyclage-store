'use strict';

require('../scss/global.scss');

global.$ = global.jQuery = require('jquery');

require("bootstrap");
require("datatables.net");
require("datatables.net-bs4");
require('datatables.net-buttons');
require("datatables.net-buttons-bs4");
require('datatables.net-select');
require("datatables.net-select-bs4");
require("datatables.net-rowreorder");
require("jquery-sortable");
require('jquery-ui');
require('jquery-ui/ui/widgets/sortable');
require('jquery-ui/ui/disable-selection');


const Swal = require('sweetalert2');
global.Swal = Swal;