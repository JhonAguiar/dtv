<?php

namespace App\Http\Controllers\API;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;



class RoleController extends Controller
{
    public static function getMenuHtml(Request $request){
        try{
            $menuHtml = array();
            $modules = Menu::where('status', 1)
                ->select('id', 'name', 'icon', 'country', 'url_access', 'key_language')
                ->where('parent_id', 0)
                ->get()
                ->toArray();
                $aux=0;
                foreach ($modules as $key => $module) {
                    $menus = self::getMenu( $module['id'] , $request->role_id, $request->username )->toArray();
                    $itemsAll = collect( $menus )->where('country', 'All')->toArray();
                    $itemsCountry = collect( $menus )->where('country', $request->country)->toArray();
                    $itemsMerge = array_merge($itemsAll, $itemsCountry);
                    if ( !empty($itemsMerge) ) {
                        $menuHtml[$aux] = $module;
                        $menuHtml[$aux]['items'] = $itemsMerge;
                        unset($itemsMerge);
                        $aux++;
                    }
                }
            return response()->json([
                'response' => true,
                'data' => $menuHtml
            ])->header("Access-Control-Allow-Origin",  "*");
        }catch(\Exception $e){
            $msj = $e->getMessage();;
            return response()->json([
                'response' => false,
                'error' => $msj
            ])->header("Access-Control-Allow-Origin",  "*");
        }
    } 

    public static function getMenu( $parent_id=null , $role_id, $username ){
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
            print_r($info);die; 
        }
        return '';
    }
}
