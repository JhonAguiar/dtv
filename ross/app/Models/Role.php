<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Menus;

class Role extends Model
{
    # ----------------------------------------------------------------------
    protected $table = 'roles';

    # ----------------------------------------------------------------------
    protected $fillable = ['id', 'name', 'description', 'protected', 'status'];

    # ----------------------------------------------------------------------
    public static $rules = [
        'name' => 'required|min:3',
        'description' => 'required|min:5',
        'status' => 'required',
    ];

    # ----------------------------------------------------------------------
    public static $messages = [
        'name.required' => 'messages.000143',
        'name.min'  => 'messages.000122',
        'description.required' => 'messages.000144',
        'description.min'  => 'messages.000123',
        'status.required' => 'messages.000124',
    ];

    # ----------------------------------------------------------------------
    protected static function getAllRoles( $inputs=array() ){
        $data = \DB::table('roles as a')
            ->select('a.*')
            ->orderBy('a.id', 'asc')
            ->paginate(20);
            #->get();
            #->toSql(); 
            #dd($datos);
        return $data;      
    }

    # ----------------------------------------------------------------------
    public static function getMenu( $parent_id=null ){
        $username = Auth::user()->username;
        $role_id = Auth::user()->role_id;        
        $conditions=array();
        if ( isset($username) and !empty($username)){
            $conditions[]=['b.id', '=', $role_id];
            $conditions[]=['b.status', '=', 1];
            $conditions[]=['c.status', '=', 1];
            $conditions[]=['c.parent_id', '=', $parent_id];
            $conditions[]=['c.visible', '=', 'S'];
            $info=\DB::table('menus_roles as a')
                ->join('roles as b', 'b.id', '=', 'a.role_id')
                ->join('menus as c', 'c.id', '=', 'a.menu_id')
                ->select('c.id', 'c.name', 'c.icon', 'c.country', 'c.url_access', 'c.key_language')
                ->where( $conditions )
                ->get();          
            return $info;
        }
        return '';
    }

    # --------------------------------------------------------------------
    public static function getMenuHtml(){
        $menuHtml = array();
        $modules = Menu::where('status', 1)
            ->select('id', 'name', 'icon', 'country', 'url_access', 'key_language')
            ->where('parent_id', 0)
            ->get()
            ->toArray();
        $aux=0;
        foreach ($modules as $key => $module) {
            $menus = self::getMenu( $module['id'] )->toArray();
            $itemsAll = collect( $menus )->where('country', 'All')->toArray();
            $itemsCountry = collect( $menus )->where('country', Auth::user()->country)->toArray();
            $itemsMerge = array_merge($itemsAll, $itemsCountry);
            if ( !empty($itemsMerge) ) {
                $menuHtml[$aux] = $module;
                $menuHtml[$aux]['items'] = $itemsMerge;
                unset($itemsMerge);
                $aux++;
            }
        }
        return $menuHtml;
    }  

    # ----------------------------------------------------------------------
    protected static function coleccionExcepcion(){
        return [
            ['controller' => 'HomeController@home'],
            ['controller' => ''],
        ];
    }

    # --------------------------------------------------------------------
    public static function authorizePermission(){
        $configRoute    = request()->route()->getAction();
        $getController  = class_basename($configRoute['controller']);
        $partes         = explode('@', $getController);
        if(Auth::user()->role_id>0) {
            # -------------------------------------------------------------
            # Consultar los permisos sobre la petición del usuario.
            $controller = trim(substr($configRoute['controller'], 21));
            $conditions[]=['a.role_id', '=', Auth::user()->role_id];
            $conditions[]=['b.id', '=', Auth::user()->role_id];
            $conditions[]=['c.controller', '=', $controller];
            $conditions[]=['b.status', '=', 1];
            $conditions[]=['c.status', '=', 1];            
            $validar=\DB::table('menus_roles as a')
                ->join('roles as b', 'b.id', '=', 'a.role_id')
                ->join('menus as c', 'c.id', '=', 'a.menu_id')
                ->select('b.name as role¨', 'c.controller', 'c.name as menu')
                ->where( $conditions )
                ->first();
            # -------------------------------------------------------------
            # Validar información frente al perfil.
                #echo "<pre>==";print_r($validar->controller);print_r($controller);die;
            if (!empty($validar) and trim($validar->controller)==trim($controller)) {
                return true;
            }else{
                # -------------------------------------------------------------
                # Permitir permisos por Excepción.
                $coleccion=Role::coleccionExcepcion();
                $info=collect( $coleccion )->where('controller', $controller);
                if( count($info)>0 ) {
                    return true;
                }
            }
        }
        return false;
    }

    # --------------------------------------------------------------------
}
