<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 80)->unique();
            $table->string('description', 180);
            $table->char('protected', 1)->comment('S/N')->default('N')->comment('N=Editable');
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('name', 80);
            $table->string('description', 180);
            $table->string('icon', 80)->default('fa fa-check');
            $table->string('url_access', 180)->nullable();
            $table->string('controller', 180)->nullable();
            $table->char('visible', 1)->comment('S/N')->default('S')->comment('S=Visible');
            $table->char('protected', 1)->comment('S/N')->default('N')->comment('N=Editable');
            $table->integer('status')->default(1);
            $table->string('key_language', 80)->nullable();
            $table->char('country', 3)->nullable()->default('All');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->timestamps();
        });

        Schema::create('menus_roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->char('protected', 1)->comment('S/N')->default('N')->comment('N=Editable');
            $table->unique(array('role_id', 'menu_id'));
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->default(3);
            $table->integer('group_id');
            $table->integer('country_code');
            $table->string('username')->unique();
            $table->string('name');
            $table->string('lastname');
            $table->string('fullname');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->char('country', 3)->nullable();
            $table->char('language', 2)->default('es');
            $table->dateTime('last_session')->nullable();
            $table->string('password');          
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('updated_user_id')->default(0);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade')->onUpdate('cascade');
        });

        $faker = Faker\Factory::create();
        \DB::table('roles')->insert(['name'=> 'Webmaster', 'description'=> 'Ingenieros de sistemas encargados de la codificación de la aplicación.', 'protected'=>'S', 'status'=>1, 'created_at'=> date('Y-m-d H:i:s')]);
        \DB::table('roles')->insert(['name'=> 'Administrador', 'description'=> 'Director de proyecto.', 'protected'=>'S', 'status'=>1, 'created_at'=> date('Y-m-d H:i:s')]);
        \DB::table('roles')->insert(['name'=> 'Invitado', 'description'=> 'Asignación de rol para las personas autenticadas por primera vez.', 'protected'=>'S', 'status'=>1, 'created_at'=> date('Y-m-d H:i:s')]);
        \DB::table('roles')->insert(['name'=> 'Auxiliar Argentina', 'description'=> 'Perfil creado para solo gestión en Argentina.', 'protected'=>'N', 'status'=>1, 'created_at'=> date('Y-m-d H:i:s')]);
        \DB::table('roles')->insert(['name'=> 'Auxiliar Colombia', 'description'=> 'Perfil creado para solo gestión en Colombia.', 'protected'=>'N', 'status'=>1, 'created_at'=> date('Y-m-d H:i:s')]);

        /*
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Administración', 'description'=>'Se administran los recursos del sistema', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.001', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Menús de acceso', 'description'=>'Administración de los menús del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus', 'controller'=>'MenusController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.014', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form crear menu', 'description'=>'Guarda menú del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus', 'controller'=>'MenusController@store', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form obtener menu', 'description'=>'Consulta menu id', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus/{role}/edit', 'controller'=>'MenusController@edit', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form actualizar menu', 'description'=>'Actualiza el menú', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus/{role}', 'controller'=>'MenusController@update', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form eliminar menu', 'description'=>'Elimina el menú', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus/{role}', 'controller'=>'MenusController@destroy', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form menus padre', 'description'=>'Obtiene menús padre', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus/parent', 'controller'=>'MenusController@menusParents', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Roles y permisos', 'description'=>'Administrar los roles de usuarios del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles', 'controller'=>'RolesController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.015', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form crear rol', 'description'=>'Crea el rol', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles', 'controller'=>'RolesController@store', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form obtener rol', 'description'=>'Consulta el rol id', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles/{role}/edit', 'controller'=>'RolesController@edit', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form actualizar rol', 'description'=>'Actualiza el rol', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles/{role}', 'controller'=>'RolesController@update', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form eliminar rol', 'description'=>'Elimina el rol', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles/{role}', 'controller'=>'RolesController@destroy', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form redirección', 'description'=>'Redirecciona a permisos', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles/permisions/{role}', 'controller'=>'RolesController@permisions', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Usuarios', 'description'=>'Administrar los usuarios del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'users', 'controller'=>'UsersController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.009', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form subir foto', 'description'=>'Captura foto', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'users/photo', 'controller'=>'UsersController@photo', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Form crear foto', 'description'=>'Guarda la foto', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'users/photo_upload', 'controller'=>'UsersController@photoSave', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Aprovisionamiento', 'description'=>'Servicios WSDL de provisioning', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.002', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Crear perfil', 'description'=>'Vista de perfil de navegación', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/create', 'controller'=>'Colombia\ProvisioningController@create', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.004', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form crear perfil', 'description'=>'Guarda perfil de navegación', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning', 'controller'=>'Colombia\ProvisioningController@store', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Activar - desactivar', 'description'=>'Vista de activar desactivar', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/suspend-unsuspend', 'controller'=>'Colombia\ProvisioningController@suspendUnsuspend', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.005', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form activar - desactivar', 'description'=>'suspen unsuspend', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/suspend-unsuspend', 'controller'=>'Colombia\ProvisioningController@suspendUnsuspendProcess', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Cambio de perfil', 'description'=>'Vista para cambio de perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/edit', 'controller'=>'Colombia\ProvisioningController@edit', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.006', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form cambiar perfil', 'description'=>'Actualiza perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/update', 'controller'=>'Colombia\ProvisioningController@update', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/delete', 'controller'=>'Colombia\ProvisioningController@delete', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.007', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/destroy', 'controller'=>'Colombia\ProvisioningController@destroy', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Crear perfil', 'description'=>'Vista de perfil de navegación', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/create', 'controller'=>'Argentina\ProvisioningController@create', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.004', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form crear perfil', 'description'=>'Guarda perfil de navegación', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning', 'controller'=>'Argentina\ProvisioningController@store', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Cambio de perfil', 'description'=>'Vista para cambio de perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/edit', 'controller'=>'Argentina\ProvisioningController@edit', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.006', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form cambiar perfil', 'description'=>'Actualiza perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/update', 'controller'=>'Argentina\ProvisioningController@update', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/delete', 'controller'=>'Argentina\ProvisioningController@delete', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.007', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>17, 'name'=>'Form eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/destroy', 'controller'=>'Argentina\ProvisioningController@destroy', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Solucion - problemas', 'description'=>'Servicios WSDL de trobleshooting', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.003', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>32, 'name'=>'Consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/troubleshooting/services', 'controller'=>'Colombia\TroubleshootingController@getServices', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.008', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>32, 'name'=>'Form consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/troubleshooting/services', 'controller'=>'Colombia\TroubleshootingController@getServicesInfo', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>32, 'name'=>'Consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/troubleshooting/services', 'controller'=>'Argentina\TroubleshootingController@getServices', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.008', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>32, 'name'=>'Form consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/troubleshooting/services', 'controller'=>'Argentina\TroubleshootingController@getServicesInfo', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>32, 'name'=>'Form obtener perfil por tecnología', 'description'=>'Consultas básicas', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'imsi/technology', 'controller'=>'Argentina\ImsiArgentinaController@getProfilesByTechnology', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Herramientas', 'description'=>'Herramientas', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.011', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>38, 'name'=>'Documentos ROSS', 'description'=>'Documentos', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'documentlist', 'controller'=>'DocumentsController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.013', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>38, 'name'=>'Form documentos ROSS', 'description'=>'Documentos', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'document/{file}', 'controller'=>'DocumentsController@openDocument', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>38, 'name'=>'Comprobar sistemas', 'description'=>'ver sistemas en funcionamiento', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'systems', 'controller'=>'SystemsController@index ', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.012', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>38, 'name'=>'Form comprobar sistemas', 'description'=>'ver sistemas en funcionamiento', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'systems/check', 'controller'=>'SystemsController@checkSystems ', 'visible'=>'N', 'status'=>1, 'key_language'=>'N/A', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        */

        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Administración', 'description'=>'Se administran los recursos del sistema', 'protected'=>'S', 'icon'=>'fa fa-cogs', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.001', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Menús de acceso', 'description'=>'Administración de los menús del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'menus', 'controller'=>'MenusController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.014', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Roles y permisos', 'description'=>'Administrar los roles de usuarios del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'roles', 'controller'=>'RolesController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.015', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>1, 'name'=>'Usuarios', 'description'=>'Administrar los usuarios del sistema', 'protected'=>'S', 'icon'=>'fa fa-check', 'url_access'=>'users', 'controller'=>'UsersController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.009', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Aprovisionamiento', 'description'=>'Servicios WSDL de provisioning', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.002', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Crear perfil', 'description'=>'Vista de perfil de navegación', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/create', 'controller'=>'Colombia\ProvisioningController@create', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.004', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Activar - desactivar', 'description'=>'Vista de activar desactivar', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/suspend-unsuspend', 'controller'=>'Colombia\ProvisioningController@suspendUnsuspend', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.005', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Cambio de perfil', 'description'=>'Vista para cambio de perfil', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/edit', 'controller'=>'Colombia\ProvisioningController@edit', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.006', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/provisioning/delete', 'controller'=>'Colombia\ProvisioningController@delete', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.007', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Crear perfil', 'description'=>'Vista de perfil de navegación', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/create', 'controller'=>'Argentina\ProvisioningController@create', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.004', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Cambio de perfil', 'description'=>'Vista para cambio de perfil', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/edit', 'controller'=>'Argentina\ProvisioningController@edit', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.006', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>5, 'name'=>'Eliminar perfil', 'description'=>'Elimina perfil', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/provisioning/delete', 'controller'=>'Argentina\ProvisioningController@delete', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.007', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Solucion - problemas', 'description'=>'Servicios WSDL de trobleshooting', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.003', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>13, 'name'=>'Consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Colombia/troubleshooting/services', 'controller'=>'Colombia\TroubleshootingController@getServices', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.008', 'country'=>'Col', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>13, 'name'=>'Consulta de IMSIs', 'description'=>'Consultas básicas', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'Argentina/troubleshooting/services', 'controller'=>'Argentina\TroubleshootingController@getServices', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.008', 'country'=>'Arg', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>0, 'name'=>'Herramientas', 'description'=>'Herramientas', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.011', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>16, 'name'=>'Documentos ROSS', 'description'=>'Documentos', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'documentlist', 'controller'=>'DocumentsController@index', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.013', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        \DB::table('menus')->insert(['parent_id'=>16, 'name'=>'Comprobar sistemas', 'description'=>'ver sistemas en funcionamiento', 'protected'=>'N', 'icon'=>'fa fa-check', 'url_access'=>'systems', 'controller'=>'SystemsController@index ', 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.012', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);
        //\DB::table('menus')->insert(['parent_id'=>0, 'name'=>'ITNET Tools', 'description'=>'Herramientas para ITNET', 'protected'=>'S', 'icon'=>'fa fa-bars', 'url_access'=>NULL, 'controller'=>NULL, 'visible'=>'S', 'status'=>1, 'key_language'=>'menu.011', 'country'=>'All', 'user_id'=>1, 'created_at'=>date('Y-m-d H:i:s')]);

        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>2, 'protected'=>'S']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>3, 'protected'=>'S']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>4, 'protected'=>'S']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>6, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>7, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>8, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>9, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>10, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>11, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>12, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>14, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>15, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>17, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>1, 'menu_id'=>18, 'protected'=>'N']);

        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>3, 'protected'=>'S']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>4, 'protected'=>'S']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>6, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>7, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>8, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>9, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>10, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>11, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>12, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>14, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>15, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>17, 'protected'=>'N']);
        \DB::table('menus_roles')->insert(['role_id'=>2, 'menu_id'=>18, 'protected'=>'N']); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('menus_roles');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('roles');
    }
}
