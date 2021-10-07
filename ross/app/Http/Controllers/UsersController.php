<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Role;

/**
 * Esta clase se usa para el tratamiento de información de los usuarios del sistema.
 * @Autor <achavezb@directvla.com.co>
 */
class UsersController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $urlWsdl = '';
    protected $result = array();
    protected $errors = array();

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
            $this->urlWsdl = config('appross.provisioning_col_wsdl', '');
        }
    }

    public function index(){
        if(view()->exists('users.index')) {
            $users = User::leftjoin('roles as b', 'b.id', '=', 'role_id')->select('users.*', 'b.name as rol_name')->get();
            if (!empty($users)) {
                $path = 'img/avatar/';
                $dir = opendir('img/avatar/');                
                foreach ($users as $key => $info) {
                    $avatar = url('img/avatar/aaa-default.png');
                    if ( !empty($info->avatar) and file_exists('img/avatar/'.$info->avatar) ) {
                        $avatar = url('img/avatar/'.$info->avatar);
                    }else{
                        while ($element = readdir($dir)){
                            if( $element != "." && $element != ".."){
                                if( !is_dir($path.$element) ){
                                    $files = explode(".", $element);
                                    if ( reset($files) == $info->username ) {
                                        #echo "<br>".$element;
                                        \DB::table('users')->where('username','=',$info->username)->update(['avatar'=>$element]);
                                        $avatar = url('img/avatar/'.$element);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    $info->url_avatar=$avatar;
                    if ( empty($info->country) ) {
                        $info->country="No selected";
                    }
                }
            }
            #echo "<pre>";print_r($users);die;
            return view('users.index', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000091'),
                    'video' => $this->video,
                ],
                'users' => $users,
                'roles' => Role::getAllRoles()
            ]);   
        }
    }
    
    # -----------------------------------------------------------------------------
    public function photo(){
        if(view()->exists('users.photo')) {
            $resources = new \App\Http\Controllers\ResourcesController;
            $avatar = url('img/avatar/aaa-default.png');
            $user = \DB::table('users')
                ->select('id as user_id', 'role_id', 'fullname', 'email', 'avatar', 'country')
                ->where('username', '=', Auth::user()->username )
                ->first();

            if ( !empty($user->avatar) and file_exists('img/avatar/'.$user->avatar) ) {
                $avatar = url('img/avatar/'.$user->avatar);
            }
            return view('users.photo', [
                'headers' => [
                	'title' => trans('messages.000034'),
                    'controller' => $this->controller,
                    'method' => $this->method,
                ],
                'device' => $resources->getDevice(),
                'avatar' => $avatar,
            ]);   
        }
        return view('errors.404');
    }

    # -----------------------------------------------------------------------------
    public function photoSave( Request $request ){
        try{
            #echo "<pre>"; print_r($request->All()); print_r($_FILES);die;
            if( $request->isMethod('post') and Auth::user()->username ) {
    			extract( $request->All() );
    		 	if ( $request->hasFile('imagen_crop') ) {
                    $files=$request->file('imagen_crop');
    			 	$filename = $files->getClientOriginalName();
    			 	$info = explode( ".", $filename);
    			 	$ext = mb_strtolower( trim( array_pop( $info ) ) ) ;
                    $newImg = mb_strtolower( Auth::user()->username.'.'.$ext );
                    $urlImg = public_path('img/avatar/'.$newImg);
                    $img=\Intervention\Image\Facades\Image::make( $request->file('imagen_crop') );
                    $img->crop( round($data_width), round($data_height), round($data_x), round($data_y) );
                    $img->resize(200, 200);
                    if( $img->save( $urlImg ) ){
                        $result = \DB::table('users')->where( 'username', '=', Auth::user()->username )->update(['avatar' => $newImg]);
                        return redirect('home')->with('excelente', trans('messages.000177'));//La imagén fue cargada satisfactoriamente.
                    }else{
                        if ( !file_exists('img/avatar/'.$newImg) ) {
                        	$result = \DB::table('users')->where( 'username', '=', Auth::user()->username )->update(['avatar' => '']);
                        }
                    }
    			 }else{
                    throw new \Exception( trans('messages.000176'), 9999);
                 }
            }else{
                throw new \Exception( trans('messages.000176'), 9999);
            }
        }catch(\Exception $e){
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    # -----------------------------------------------------------------------------
    public function changeRole( Request $request ){
        $method = $request->method();
        if( $request->isMethod('post') ) {
            $inputs = $request->All();
            if ( Auth::user()->username!=$inputs['username']) {
                $result = \DB::table('users')
                    ->where('username','=', $inputs['username'])
                    ->update([
                        'role_id'=>$inputs['role_id'],
                        'updated_at'=>date("Y-m-d H:i:s"),
                        'updated_user_id'=>Auth::id()
                    ]);
                if ($result) {
                    array_push($this->result, trans('messages.000117') );
                }else{
                    array_push($this->errors, trans('messages.000059') );
                }
            }else{
                array_push($this->errors, trans('messages.000157') );
            }
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }


}
