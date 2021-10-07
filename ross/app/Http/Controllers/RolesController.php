<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use App\Models\Menu;


/*
Resource roles
+-----------+-------------------+---------------+------------------------------------------------+
| Method    | URI               | Name          | Action                                         |
+-----------+-------------------+---------------+------------------------------------------------+
| POST      | roles             | roles.store   | App\Http\Controllers\RolesController@store     |
| GET|HEAD  | roles             | roles.index   | App\Http\Controllers\RolesController@index     |
| GET|HEAD  | roles/create      | roles.create  | App\Http\Controllers\RolesController@create    |
| GET|HEAD  | roles/{role}      | roles.show    | App\Http\Controllers\RolesController@show      |
| DELETE    | roles/{role}      | roles.destroy | App\Http\Controllers\RolesController@destroy   |
| PUT|PATCH | roles/{role}      | roles.update  | App\Http\Controllers\RolesController@update    |
| GET|HEAD  | roles/{role}/edit | roles.edit    | App\Http\Controllers\RolesController@edit      |
+-----------+-------------------+---------------+------------------------------------------------+
*/

/**
 * Esta clase sirve para administrar los roles y permisos del sistema "ACL".
 * @Autor <achavezb@directvla.com.co>
 */
class RolesController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $errors = array();
    protected $result = array();

    # -----------------------------------------------------------------------------
    public function __construct() {
        #$this->middleware('auth');
        if(!\App::runningInConsole()){
            $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
            $parts = explode('@', $controller);
            $this->controller = substr($parts[0], 0, -10);
            $this->method = $parts[1];
            if ( file_exists('videos/'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/'.$this->controller.'_'.$this->method.'.mp4');
            }
        }
    }

    # -----------------------------------------------------------------------------
    // Mostrar una lista del recurso.
    public function index(){
        if(view()->exists('roles.index')) {
            return view('roles.index', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000097'),
                    'video' => $this->video,
                ],
                'roles' => Role::getAllRoles()
            ]);   
        }else{
            return view('errors.404', []);
        }       
    }

    # -----------------------------------------------------------------------------
    // Almacene un recurso recién creado en el almacenamiento.
    public function store(Request $request){
        $method = $request->method();
        $rowdata = array();
        if( $request->isMethod('post') ) {
            \DB::beginTransaction();
            try{
                $inputs = $request->All();
                $messages = [
                    'name.required' => trans('messages.000143'),
                    'name.min'  => trans('messages.000122'),
                    'description.required' => trans('messages.000144'),
                    'description.min'  => trans('messages.000123'),
                    'status.required' => trans('messages.000124'),
                ];
                #$validator = Validator::make($request->all(), Role::$rules, Role::$messages);
                $validator = Validator::make($request->all(), Role::$rules, $messages);
                if ($validator->fails()) {
                    #return redirect()->back()->withErrors($validator)->withInput();
                    $errors = $validator->errors();
                    foreach ($errors->all() as $message) {
                        array_push($this->errors, trans($message) );
                    }
                }
                $name = Role::where('name', $inputs['name'])->first();
                if ( !empty($name) ) {
                    array_push($this->errors, trans('messages.000146') );
                }
                if (empty($this->errors)) {
                    $id = Role::insertGetId([
                        'name'          => trim($inputs['name']),
                        'description'   => trim($inputs['description']),
                        'status'        => (int)$inputs['status'],
                        'user_id'       => Auth::id(),
                        'created_at'    => date('Y-m-d H:i:s')
                    ]);
                    if( $id>0 ) {
                        \DB::commit();
                        array_push($this->result, trans('messages.000125') );
                        $rowdata = $this->rowViewHtml($id);
                    }
                }
            }catch(\Exception $e) {
                \DB::rollback();
                #$message = $e->getLine()." - ".$e->getFile()." - ".$e->getMessage();
                $message = $e->getLine()." - ".$e->getMessage();
                array_push($this->errors, $message);
            }
        }else{
            // Mensaje 000116: Esta vista no esta disponible.
            array_push($this->errors, trans('messages.000116'));
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors,
            'row' => $rowdata
        ]);
    }

    # -----------------------------------------------------------------------------
    // Mostrar el formulario para editar el recurso especificado.
    public function edit( $secretid ){
        $id = isset($secretid) ? base64_decode($secretid) : 0;
        $role = Role::where('id', $id)->first();
        return response()->json($role);
    }

    # -----------------------------------------------------------------------------
    // Actualiza el recurso especificado en el almacenamiento.
    public function update(Request $request, $secretid){
        $method = $request->method();
        $rowdata = array();
        if( $request->isMethod('put') and $request->filled('id') ) {
            $id = base64_decode( $request->input('id') );

            $inputs = $request->All();
            
            $messages = [
                'name.required' => trans('messages.000143'),
                'name.min'  => trans('messages.000122'),
                'description.required' => trans('messages.000144'),
                'description.min'  => trans('messages.000123'),
                'status.required' => trans('messages.000124'),
            ];
            #$validator = Validator::make($request->all(), Role::$rules, Role::$messages);
            $validator = Validator::make($request->all(), Role::$rules, $messages);

            if ($validator->fails()) {
                #return redirect()->back()->withErrors($validator)->withInput();
                $errors = $validator->errors();
                foreach ($errors->all() as $message) {
                    array_push($this->errors, trans($message) );
                }
            }
           
            \DB::beginTransaction();
            try{
                $result=Role::where('id', $id)->update([
                    'name'          => trim($inputs['name']),
                    'description'   => trim($inputs['description']),
                    'status'        => (int)$inputs['status'],
                    'user_id'       => Auth::id(),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
                if ( $result ) {
                    \DB::commit();
                    array_push($this->result, trans('messages.000126') );
                    $rowdata = $this->rowViewHtml($id);
                }
            }catch(\Exception $e) {
                \DB::rollback();
                #$message = $e->getLine()." - ".$e->getFile()." - ".$e->getMessage();
                $message = $e->getLine()." - ".$e->getMessage();
                array_push($this->errors, $message);
            }
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors,
            'row' => $rowdata
        ]);
    }

    # -----------------------------------------------------------------------------
    // Eliminar el recurso especificado del almacenamiento.
    public function destroy( $secretid ){
        \DB::beginTransaction();
        try{
            $id = isset($secretid) ? base64_decode($secretid) : 0;
            $count = \DB::table('users')->where('role_id', $id)->count();
            if ($count==0) {
                $data = \DB::table('roles')->where('id', $id)->select('name', 'description')->first();
                \DB::table('menus_roles')->where('role_id', '=', $id)->where('protected', '=', 'N')->delete();
                $delete = Role::where('id', $id)->where('protected', '=', 'N')->delete();
                if ($delete) {
                    array_push($this->result, trans('messages.000127').' '.$data->name.' - '.$data->description );
                }else{
                    array_push($this->errors, trans('messages.000128') );
                }
            }else{
                array_push($this->errors, trans('messages.000129') );
            }
            \DB::commit();
        }catch(\Exception $e) {
            \DB::rollback();
            $message = $e->getLine()." - ".$e->getMessage();
            array_push($this->errors, $message);
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    # -----------------------------------------------------------------------------
    public function rowViewHtml( $id=''){
        $data = Role::where('id', $id)->first()->toArray();
        if ($data['status']==1) {
            $data['color'] = "green";
            $data['textstatus'] = trans('messages.000111');
        }else{
            $data['color'] = "red";
            $data['textstatus'] = trans('messages.000112');
        }
        $data['permision'] = url('roles/permisions/'.base64_encode($data['id']));
        $data['encriptid'] = base64_encode($data['id']);
        return $data;
    }

    # -----------------------------------------------------------------------------
    public function permisions( $secretid ){
        $role_id = isset($secretid) ? base64_decode($secretid) : 0;
        if( view()->exists('roles.permisions') and $role_id>0 ) {
            $menus = array();
            $parents = Menu::where('visible', 'S')->where('parent_id', 0)->where('status', 1)->get()->toArray();
            foreach ($parents as $key => $val) {
                $items = Menu::where('visible', 'S')->where('parent_id', '=', $val['id'])->where('status', 1)->get()->toArray();
                if (!empty($items)) {
                    array_push($menus,  $val);
                    $menus[$key]['items']=$items;
                }
            }
            $permisions = \DB::table('menus_roles')->where('role_id', '=', $role_id)->get();
            return view('roles.permisions', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000147'),
                    'video' => $this->video,
                ],
                'role' => Role::where('id', $role_id)->first(),
                'menus' => $menus,
                'permisions' => $permisions
            ]);   
        }else{
            return view('errors.404', []);
        } 
    }
  
    # -----------------------------------------------------------------------------
    public function permisionsStore(Request $request){
        $method = $request->method();
        if( $request->isMethod('post') ) {
            $inputs = $request->All();
            \DB::beginTransaction();
            try{
                \DB::table('menus_roles')->where('role_id', '=', $inputs['role_id'])->where('protected', '=', 'N')->delete();
                $arrayData=array();
                $array = \DB::table('menus_roles')->where('role_id', '=', $inputs['role_id'])->get()->toArray();
                if ( !empty($array) ) {
                    foreach ($array as $key => $val) {
                        array_push($arrayData, $val->menu_id );
                    }                    
                }
                foreach ($inputs['menus'] as $key => $menu_id) {
                    if ( !in_array($menu_id, $arrayData)) {
                        \DB::table('menus_roles')->insert([
                            'role_id' => $inputs['role_id'], 
                            'menu_id' => $menu_id,
                            'protected' => 'N'
                        ]);
                    }
                }
                \DB::commit();
                array_push($this->result, trans('messages.000126') );
            }catch(\Exception $e) {
                \DB::rollback();
                $message = $e->getLine()." - ".$e->getMessage();
                array_push($this->errors, $message);
            }
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }


}