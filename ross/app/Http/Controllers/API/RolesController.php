<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;

class RolesController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $errors = array();
    protected $result = array();
    
    public function getRoles(){
        return response()->json([
            'headers' => [
                'controller' => $this->controller,
                'method' => $this->method,
                'title' => trans('messages.000097'),
                'video' => $this->video,
            ],
            'roles' => Role::getAllRoles()
        ]);             
    }

    # -----------------------------------------------------------------------------
    public function permisions( int $secretid ){
        $role_id = $secretid;
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
        return response()->json([
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

    }

      
    public function permisionsStore(Request $request){

        if( $request->isMethod('post') ) {
            $inputs = $request->All();
            if($inputs["value"] == "true"){
                \DB::beginTransaction();
                //accion create
                \DB::table('menus_roles')->insert([
                    'role_id' => $inputs['role_id'], 
                    'menu_id' => $inputs['menu_id'],
                    'protected' => 'N'
                ]);
                
                \DB::commit();
                array_push($this->result, trans('Permiso agregado con exito') );
            }else{
                \DB::beginTransaction();
                \DB::table('menus_roles')->where('menu_id', '=', $inputs['menu_id'])->where('role_id', '=', $inputs['role_id'])->where('protected', '=', 'N')->delete();
                //accion delete
                \DB::commit();
                array_push($this->result, trans('Permiso removido con exito') );
                
            }
            
            
        } 
        
        return response()->json([
            'entradas' => $inputs["value"] ,
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    # -----------------------------------------------------------------------------
    public function permisionsStore2(Request $request){
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
                        'user_id'       => 1,
                        'created_at'    => date('Y-m-d H:i:s')
                    ]);
                    if( $id>0 ) {
                        \DB::commit();
                        array_push($this->result, trans('messages.000125') );
                        $rowdata = $id;
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
    // Eliminar el recurso especificado del almacenamiento.
    public function destroy( $secretid ){
        \DB::beginTransaction();
        try{
            $id = isset($secretid) ? $secretid : 0;
            $count = \DB::table('users')->where('role_id', $id)->count();
            if ($count==0) {
                $data = \DB::table('roles')->where('id', $id)->select('name', 'description')->first();
                \DB::table('menus_roles')->where('role_id', '=', $id)->where('protected', '=', 'N')->delete();
                $delete = Role::where('id', $id)->where('protected', '=', 'N')->delete();
                if ($delete) {
                    //El registro fue eliminado satisfactoriamente
                    array_push($this->result, trans('messages.000127').' '.$data->name.' - '.$data->description );
                }else{
                    //El registro NO fue eliminado
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
            'id' => $secretid,
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    //Obtener informacion de un  id Especifico
    public function edit( $secretid ){
        $id = isset($secretid) ? $secretid : 0;
        $role = Role::where('id', $id)->first();
        return response()->json([
            'role' => $role
        ]);
    }

    # -----------------------------------------------------------------------------
    // Actualiza el recurso especificado en el almacenamiento.
    public function update(Request $request){
        $method = $request->method();
        $rowdata = array();
        if( $request->isMethod('put') and $request->filled('id') ) {
            $id = $request->input('id');

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
                    'user_id'       => (int)$inputs['user_id'],
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
                if ( $result ) {
                    \DB::commit();
                    array_push($this->result, trans('messages.000126') );
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

    
}

?>