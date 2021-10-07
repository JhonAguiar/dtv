<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Auth and Permisions
Route::post('login', 'API\LoginController@attemptLogin');
Route::get('users', 'API\UsersController@getUsers');
Route::post('delete-user' , 'API\UsersController@deleteUser');
Route::post('menu', 'API\RoleController@getMenuHtml');
Route::get('logs_provisioning' , 'API\LogsProvisioningController@getLogsProvisioning');


// Roles and Permissions
Route::get('roles', 'API\RolesController@getRoles');
Route::post('changeRol', 'API\UsersController@changeRole');
Route::get('permisions/{id}', 'API\RolesController@permisions');
Route::post('permisionsStore', 'API\RolesController@permisionsStore');

//Menus
Route::get('menus_index' , 'API\MenusController@index');
Route::post('accesos_menu', 'API\MenusController@store');
Route::delete('accesos_menu/{id}' , 'API\MenusController@destroy');
Route::get('accesos_menu/{id}','API\MenusController@edit' );
Route::put('accesos_menu','API\MenusController@update');


Route::post('roles', 'API\RolesController@store');
Route::delete('roles/{id}' , 'API\RolesController@destroy');
Route::get('roles_edit/{id}','API\RolesController@edit' );
Route::put('roles','API\RolesController@update');

// Check System
Route::get('server' , 'API\SystemsController@getServers');
Route::post('checkSystems', 'API\SystemsController@checkSystems');

/**
 * SERVICIOS ------ COLOMBIA
 */

// Graphics dashboard - Colombia
Route::post('reporte', 'API\ReportsController@graphics');
Route::post('reporte-netcheck', 'API\ReportsController@netcheckgraphics');
Route::post('plansPercentege', 'API\ReportsController@planesPieChart');
Route::post('activationslastweek', 'API\ReportsController@getActivationsLastWeek');
Route::post('activationsxmonth', 'API\ReportsController@getActivationsxMonth');
Route::get('activationsPC' , 'API\ReportsController@getTotalActivationsPC');
Route::get('subscribersBroadband' , 'API\ReportsController@getSubscriberBroadBand');
Route::get('prepayco', 'API\ReportsController@getQTYPrepay');

// Graphics dashboard - Argentina
Route::get('getActivationsAr' , 'API\ReportsController@GetActivationsAr');
Route::get('activationsPClastweekAr' , 'API\ReportsController@getActivationsPCLastWeekAr');
Route::get('activationsMonthAr' , 'API\ReportsController@getActivationsMonthAr');
Route::get('getSubscriberIBS' , 'API\ReportsController@getSubscriberIBS');
Route::get('suspendsAr' , 'API\ReportsController@getSuspendsAr');
Route::get('plansar', 'API\ReportsController@getPlansArgentinaDaily');
Route::get('subscribersBroadbandar' , 'API\ReportsController@getSubscriberBroadBandAr');

//External services
Route::post('getProfileProvAPI' , 'API\ReportsController@getProfileProvisioningAPI');


//Troubleshooting services - Colombia
Route::post('troubleshootingServices', 'API\TroubleshootingController@getServicesRender');
Route::post('getfota', 'API\TroubleshootingController@getServicesRenderFota');
Route::post('getSSID', 'API\TroubleshootingController@getSSID');
Route::post('setssid-24', 'API\TroubleshootingController@setSSID24');
Route::post('setssid-5', 'API\TroubleshootingController@setSSID5');
Route::post('setpassword24', 'API\TroubleshootingController@setpassword24');
Route::post('setpassword5', 'API\TroubleshootingController@setpassword5');
Route::post('factoryReset', 'API\TroubleshootingController@factory_reset');
Route::post('softwareReboot', 'API\TroubleshootingController@software_reboot');

//Troubleshooting services - Argentina
Route::post('troubleshootingServicesAr', 'API\TroubleshootingController@getServicesRenderAr');

// Provisioning services - Colombia
Route::get('profilesActive', 'API\ProvisioningController@getProfilesActive' );
Route::post('suspend-unsuspend' , 'API\ProvisioningController@suspendUnsuspendProcess');
Route::post('create', 'API\ProvisioningController@store' );
Route::post('update', 'API\ProvisioningController@update');
Route::post('destroy', 'API\ProvisioningController@destroy');

// Model Range
Route::post('model_range', 'API\ProvisioningController@modelRange');
Route::get('get-brand', 'API\ProvisioningController@showBrand');
Route::post('get-model', 'API\ProvisioningController@showModel');

// Load Topology
Route::post('load-topology', 'API\ProvisioningController@loadTopology');

//CRON Task Programming
Route::post('sendTask', 'API\TaskProgController@insert');

/**
 * SERVICIOS ------ ARGENTINA
 */

// Provisioning services - Argentina
Route::get('profilesActiveAr', 'API\ProvisioningController@getProfilesActiveAr');
Route::get('technologiesActiveAr', 'API\ProvisioningController@getTechnologiesActiveAr');
Route::post('create-ar', 'API\ProvisioningController@storeAr' );
Route::post('update-ar', 'API\ProvisioningController@updateAr' );
Route::post('destroy-ar', 'API\ProvisioningController@destroyAr' );
Route::post('suspend-unsuspend-ar' , 'API\ProvisioningController@suspendUnsuspendProcessAr');

//Mongo services 
Route::post('taskings', 'API\MongoController@getAlertTasks');
Route::post('taskingsAll', 'API\MongoController@getAlertTasksAll');
Route::post('changeStatus', 'API\MongoController@changeStatusTask');

