<?php
/** 
 *  @file web.php
 *  @brief Aplicación web para el Sistema de soporte a operaciones regionales.
 *  @comment .
 *  @author Ing. Alfonso Chávez <achavezb@directvla.com.co>
 */

Route::get('/', function () {
    return redirect('/home');
    #return view('pages.welcome');
});
Auth::routes();
Auth::routes(['reset' => false, 'verify' => false, 'register' => false, ]);

Route::get('home',              'HomeController@index')->name('home');
Route::get('country/{region}',  'ResourcesController@changeRegion')->where(['region' => '[A-Za-z]+']);
Route::get('language/{lang}',   'ResourcesController@changeLanguage')->where(['lang' => '[a-z]+']);
Route::get('sessiontime',       'ResourcesController@closeSession');
Route::get('sessionredirect',   'ResourcesController@sessionRedirect');
Route::get('denied', function () { return view('errors.403'); });


# ===================================================================================================
Route::middleware('ross_acl')->get('roles',                                     'RolesController@index')->name('roles.index');    
Route::middleware('auth')->post('roles',                                    	'RolesController@store')->name('roles.store');
Route::middleware('auth')->get('roles/{role}/edit',                         	'RolesController@edit')->name('roles.edit');
Route::middleware('auth')->put('roles/{role}',                              	'RolesController@update')->name('roles.update');
Route::middleware('auth')->delete('roles/{role}',                           	'RolesController@destroy')->name('roles.destroy');
Route::middleware('auth')->get('roles/permisions/{role}',                   	'RolesController@permisions')->name('roles.permisions');
Route::middleware('auth')->post('roles/permisions',                   			'RolesController@permisionsStore')->name('roles.permisionsstore');
#Route::resource('roles', 'RolesController');


# ===================================================================================================
Route::middleware('ross_acl')->get('menus',                           			'MenusController@index')->name('menus.index');    
Route::middleware('auth')->post('menus',                                    	'MenusController@store')->name('menus.store');
Route::middleware('auth')->get('menus/{role}/edit',                         	'MenusController@edit')->name('menus.edit');
Route::middleware('auth')->put('menus/{role}',                              	'MenusController@update')->name('menus.update');
Route::middleware('auth')->delete('menus/{role}',                           	'MenusController@destroy')->name('menus.destroy');
Route::middleware('auth')->get('menus/parent',                              	'MenusController@menusParents')->name('menus.parent');
#Route::resource('menus', 'MenusController');


# ===================================================================================================
Route::middleware('ross_acl')->get('Argentina/provisioning/create',          	'Argentina\ProvisioningController@create')->name('argprovisioning.create');
Route::middleware('auth')->post('Argentina/provisioning',                   	'Argentina\ProvisioningController@store')->name('argprovisioning.store');
Route::middleware('ross_acl')->get('Argentina/provisioning/edit',            	'Argentina\ProvisioningController@edit')->name('argprovisioning.edit');
Route::middleware('auth')->post('Argentina/provisioning/update',            	'Argentina\ProvisioningController@update')->name('argprovisioning.update');
Route::middleware('ross_acl')->get('Argentina/provisioning/delete',         	'Argentina\ProvisioningController@delete')->name('argprovisioning.delete');
Route::middleware('auth')->post('Argentina/provisioning/destroy',           	'Argentina\ProvisioningController@destroy')->name('argprovisioning.destroy');
Route::middleware('ross_acl')->get('Argentina/troubleshooting/services',    	'Argentina\TroubleshootingController@getServices')->name('argtroubleshooting.getservices');
Route::middleware('auth')->post('Argentina/troubleshooting/services',       	'Argentina\TroubleshootingController@getServicesRender')->name('argtroubleshooting.viewservices');
Route::middleware('auth')->post('imsi/technology',                          	'Argentina\ImsiArgentinaController@getProfilesByTechnology')->name('argprovisioning.technology');
Route::middleware('ross_acl')->get('Argentina/troubleshooting/reset',           'Argentina\TroubleshootingController@resetConnection')->name('argtroubleshooting.reset_view');
Route::middleware('auth')->post('Argentina/troubleshooting/reset',              'Argentina\TroubleshootingController@resetConnectionProccess')->name('argtroubleshooting.reset_connection');


# ===================================================================================================
Route::middleware('ross_acl')->get('Colombia/provisioning/create',              'Colombia\ProvisioningController@create')->name('colprovisioning.create');
Route::middleware('auth')->post('Colombia/provisioning',                    	'Colombia\ProvisioningController@store')->name('colprovisioning.store');
Route::middleware('ross_acl')->get('Colombia/provisioning/edit',                'Colombia\ProvisioningController@edit')->name('colprovisioning.edit');
Route::middleware('auth')->post('Colombia/provisioning/update',             	'Colombia\ProvisioningController@update')->name('colprovisioning.update');
Route::middleware('ross_acl')->get('Colombia/provisioning/suspend-unsuspend',	'Colombia\ProvisioningController@suspendUnsuspend')->name('colprovisioning.suspend-unsuspend');
Route::middleware('auth')->post('Colombia/provisioning/suspend-unsuspend',  	'Colombia\ProvisioningController@suspendUnsuspendProcess')->name('colprovisioning.viewsuspend-unsuspend');
Route::middleware('ross_acl')->get('Colombia/provisioning/delete',       		'Colombia\ProvisioningController@delete')->name('colprovisioning.delete');
Route::middleware('auth')->post('Colombia/provisioning/destroy',            	'Colombia\ProvisioningController@destroy')->name('colprovisioning.destroy');
Route::middleware('ross_acl')->get('Colombia/troubleshooting/services',    		'Colombia\TroubleshootingController@getServices')->name('coltroubleshooting.getservices');
Route::middleware('auth')->post('Colombia/troubleshooting/services',        	'Colombia\TroubleshootingController@getServicesRender')->name('coltroubleshooting.viewservices');
Route::middleware('auth')->post('Colombia/graphics',        					'Colombia\ReportsController@graphics')->name('colgraphics');


# ===================================================================================================
Route::middleware('ross_acl')->get('systems',                              		'SystemsController@index')->name('systems.index');
Route::middleware('auth')->post('systems/check',                            	'SystemsController@checkSystems')->name('systems.check');
Route::middleware('ross_acl')->get('documentlist',                         		'DocumentsController@index')->name('cocumentlist.index');
Route::middleware('auth')->get('document/{file}',                           	'DocumentsController@openDocument')->name('cocumentlist.view');
Route::middleware('ross_acl')->get('users',                                 	'UsersController@index')->name('users.index');
Route::middleware('auth')->get('users/photo',                               	'UsersController@photo')->name('users.photo');
Route::middleware('auth')->post('users/photo_upload',                       	'UsersController@photoSave')->name('users.photosave');
Route::middleware('auth')->post('users/role',                        			'UsersController@changeRole')->name('users.changerole');


# ===================================================================================================
// PDF: https://appdividend.com/2019/09/13/laravel-6-generate-pdf-from-view-example-tutorial-from-scratch/
// https://blog.coffeedevs.com/laravel-6-0-cambios-en-espanol/
// https://medium.com/@cvallejo/roles-usuarios-laravel-2e1c6123ad
/*
Route::get('sqlbog',        'ExampleController@sqlbog');
Route::get('soap',          'ExampleController@soap');
Route::get('ldap',          'ExampleController@ldap');
Route::get('slug/{str}',    'ExampleController@slug');
Route::get('verpdf',        'ExampleController@verpdf');
Route::get('/php', function () {
	$php_extensions = get_loaded_extensions();
	echo "<pre>";print_r($php_extensions);echo "<pre/>";die;
});
Route::get('/provisioning', function () {
    $blogs = DB::connection('db_provisioning')->table("speed_profile")->get();
    echo "<pre>"; print_r($blogs); echo "<pre/>"; die("<br>Stop");
});
Route::get('/fpdf', function () {
	header('Content-type: application/pdf');
    \Fpdf::AddPage('P', 'Letter', 0);
    \Fpdf::SetFont('Courier', 'B', 18);
    \Fpdf::Cell(50, 25, 'Hello World!');
    #\Fpdf::Output('Anuncio.pdf', 'D', true);
    \Fpdf::Output();
    exit(0);
});
*/