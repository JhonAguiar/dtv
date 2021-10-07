<?php
return [
    'broadband' 					=> env('ROSS_BROADBAND', ''),
    'sigla' 						=> env('ROSS_SIGLA', ''),
    'provisioning_wsdl_col' 		=> env('ENDPOINT_PROVISIONING_COL', ''),
    'provisioning_wsdl_arg' 		=> env('ENDPOINT_PROVISIONING_ARG', ''),
    'provisioning_wsdl_arg_read' 	=> env('ENDPOINT_PROVISIONING_ARG_READ', ''),
    'provisioning_wsdl_arg_upd' 	=> env('ENDPOINT_PROVISIONING_ARG_UPD', ''),
    'sqlbog_servername' 			=> env('SQLBOG_SERVER', ''),
    'sqlbog_database' 				=> env('SQLBOG_DATABASE', ''),
    'sqlbog_username' 				=> env('SQLBOG_USERNAME', ''),
    'sqlbog_password' 				=> env('SQLBOG_PASSWORD', ''),
    'sqlbog_poirt' 					=> env('SQLBOG_PORT', ''),
];
