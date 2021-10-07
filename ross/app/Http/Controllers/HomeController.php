<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
#use Adldap\Laravel\Facades\Adldap;

/**
 * Esta clase sirve como punto de entrada a los usuarios que se han autenticado en el sistema.
 * @Autor <achavezb@directvla.com.co>
 */
class HomeController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
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
        }
    }

    # -----------------------------------------------------------------------------
    public function index( Request $request ){
        if(!\App::runningInConsole()){
            if(view()->exists('home.index')) {
                $obj = new \App\Http\Controllers\ResourcesController;
                $userAuth = Auth::user();
                #$dataSesion = $request->session()->all();

                $user = User::where('username', $userAuth->username)
                    ->leftjoin('roles as b', 'b.id', '=', 'role_id')
                    ->select('users.*', 'b.name as rol')
                    ->first();
                #echo "<pre>";print_r($user);print_r($userAuth);die;
                $avatar = url('img/avatar/aaa-default.png');
                if ( !empty($userAuth->avatar) and file_exists('img/avatar/'.$userAuth->avatar) ) {
                    $avatar = url('img/avatar/'.$userAuth->avatar);
                }else{
                    $path = 'img/avatar/';
                    $dir = opendir('img/avatar/');
                    while ($element = readdir($dir)){
                        if( $element != "." && $element != ".."){
                            if( !is_dir($path.$element) ){
                                $files = explode(".", $element);
                                if ( reset($files) == $userAuth->username ) {
                                    #echo "<br>".$element;
                                    \DB::table('users')->where('username','=',$userAuth->username)->update(['avatar'=>$element]);
                                    $avatar = url('img/avatar/'.$element);
                                    break;
                                }
                            }
                        }
                    }
                }
                return view('home.index', [
                    'headers' => [
                        'controller' => $this->controller,
                        'method' => $this->method,
                        'title' => trans('messages.000007').' '.ucwords(@Auth::user()->name),
                        'video' => $this->video,
                    ],
                    'avatar' => $avatar,
                    'date' => $obj->getStringDate(),
                    'ip' => $obj->getAdressIP(),
                    'device' => $obj->getDevice(),
                    'navigator' => $obj->getBrowser(),
                    'user' => $user
                ]);   
            }
        }
    }


}
