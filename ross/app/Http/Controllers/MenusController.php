<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Menu;



/**
 * Esta clase sirve para administrar los menus.
 * @Autor <achavezb@directvla.com.co>
 */
class MenusController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $errors = array();
    protected $result = array();

    # -----------------------------------------------------------------------------
    public function __construct() {
        $this->middleware('auth');
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
        if(view()->exists('menus.index')) {
            $rows_parents = Menu::orderBy('name','asc')->where('parent_id', 0)->get();
            $rows_menus = \DB::table('menus as shild')
                ->leftJoin('menus as parent', 'parent.id', '=', 'shild.parent_id')
                ->select('parent.id as parentid', 'parent.name as parent', 'shild.*')
                ->paginate(200); 
                #dd($rows_menus);
                return view('menus.index', [
                    'headers' => [
                        'controller' => $this->controller,
                        'method' => $this->method,
                        'title' => trans('messages.000130'),
                        'video' => $this->video,
                    ],
                    'parents' => $rows_parents,
                    'menus' => $rows_menus
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
            try{
                $inputs = (object)$request->All();
                #echo "<pre>"; print_r($inputs);die;
                if (strlen($inputs->name)<3) {
                    array_push($this->errors, trans('messages.000122') );
                }
                
                if (strlen($inputs->description)<10) {
                    array_push($this->errors, trans('messages.000123') );
                }
                
                if ($inputs->status=='') {
                    array_push($this->errors, trans('messages.000124') );
                }

                if (empty($this->errors)) {
                    $inputs->parent_id = !empty($inputs->parent_id) ? $inputs->parent_id : 0;
                    $inputs->icon = ($inputs->parent_id==0) ? 'fa fa-bars' : 'fa fa-list';
                    $id = Menu::insertGetId([
                        'parent_id'     => $inputs->parent_id,
                        'name'          => trim($inputs->name),
                        'description'   => trim($inputs->description),
                        'icon'          => $inputs->icon,
                        'url_access'    => $inputs->url_access,
                        'controller'    => $inputs->controller,
                        'visible'       => $inputs->visible,
                        'status'        => (int)$inputs->status,
                        'key_language'  => $inputs->key_language,
                        'country'       => !empty($inputs->country) ? ucfirst($inputs->country) : 'All',
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
        $data = Menu::where('id', $id)->first();
        return response()->json($data);
    }

    # -----------------------------------------------------------------------------
    // Actualiza el recurso especificado en el almacenamiento.
    public function update(Request $request, $secretid){
        $method = $request->method();
        $rowdata = array();
        if( $request->isMethod('put') and $request->filled('id') ) {
            $inputs = (object)$request->All();            
            $id = base64_decode( $request->input('id') );
            \DB::beginTransaction();
            try{
                $inputs->parent_id = !empty($inputs->parent_id) ? $inputs->parent_id : 0;
                $inputs->icon = ($inputs->parent_id==0) ? 'fa fa-bars' : 'fa fa-check';
                $result=Menu::where('id', $id)->update([
                    'parent_id'     => $inputs->parent_id,
                    'name'          => trim($inputs->name),
                    'description'   => trim($inputs->description),
                    'icon'          => $inputs->icon,
                    'url_access'    => $inputs->url_access,
                    'controller'    => $inputs->controller,
                    'visible'       => $inputs->visible,
                    'status'        => (int)$inputs->status,
                    'key_language'  => $inputs->key_language,
                    'country'       => !empty($inputs->country) ? ucfirst($inputs->country) : 'All',
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
        $id = isset($secretid) ? base64_decode($secretid) : 0;
        $count = \DB::table('menus as a')
            ->join('menus as b', 'a.id', '=', 'b.parent_id')
            ->where('a.id', '=', $id)
            ->count();

        if ($count==0) {
            $data = \DB::table('menus')->where('id', $id)->select('name', 'description')->first();
            $del_menu_roll = \DB::table('menus_roles')->where('menu_id', $id)->delete();
            $delete = Menu::where('id', $id)->delete();
            if ($delete) {
                array_push($this->result, trans('messages.000127') );
                array_push($this->result, $data->name.' - '.$data->description);
            }else{
                array_push($this->errors, trans('messages.000128') );
            }
        }else{
            array_push($this->errors, trans('messages.000129') );
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    # -----------------------------------------------------------------------------
    public function rowViewHtml( $id=''){
        $data = \DB::table('menus as shild')
            ->leftJoin('menus as parent', 'parent.id', '=', 'shild.parent_id')
            ->select('parent.id as parentid', 'parent.name as parent', 'shild.*')
            ->where('shild.id', $id)
            ->first();
        if ($data->status==1) {
            $data->color = "green";
            $data->textstatus = trans('messages.000111');
        }else{
            $data->color = "red";
            $data->textstatus = trans('messages.000112');
        }
        $data->encriptid = base64_encode($data->id);
        return $data;
    }

    # -----------------------------------------------------------------------------
    public function menusParents(){
        $data = Menu::where('parent_id', 0)->where('status', 1)->get()->toArray();
        return response()->json($data);
    }

}