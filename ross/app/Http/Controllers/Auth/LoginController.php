<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Support\Facades\App;
use App\Libraries\AppCodes;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('guest')->except('logout');
    }

    public function username(){
        return 'username';
    }

    protected function validateLogin(Request $request){
        #print_r($request->All());die;
        $this->validate($request, [
            $this->username() => 'required|string|regex:/^\w+$/',
            'password' => 'required|string',
        ]);
    }

    protected function attemptLogin(Request $request){
        $credentials = $request->only($this->username(), 'password');
        $username = trim($credentials[$this->username()]);
        $usernamedn = sprintf(env('LDAP_USER_FORMAT', 'cn=%s,'.env('LDAP_BASE_DN')), $username);
        $password = trim($credentials['password']);
        
        try {

            // if(Adldap::auth()->attempt($usernamedn, $password, $bindAsUser=false)) {
                
                $user = \App\User::where($this->username(), $username)->first();
                if (!$user) { 
                    // var_dump($user);die; 
                    // Crear un nuevo usuario.
                    $user = new \App\User();
                    $sync_attrs = $this->retrieveSyncAttributes($username);
                    // foreach ($sync_attrs as $field => $value) {
                    //     if ($field=='created_at' or $field=='updated_at') {
                    //         $value= substr($value, 0, 8);
                    //         $str = substr($value, 0, 4)."-".substr($value, 4, -2)."-".substr($value, 6);
                    //         $date=date_create($str);
                    //         $value = date_format($date,"Y-m-d");
                    //     }
                    //     $user->$field = $value!==null ? $value : '';
                    // }
                    $user->group_id = 1;
                    $user->username = $username;
                    $user->password = encrypt($password);
                    $user->language = 'es';
                    $user->country_code = 1;
                    $user->name = "Jhon Henrry";
                    $user->lastName = "Aguiar Moreno";
                    $user->fullName = "Jhon Henrry Aguiar Moreno";
                    $user->email = "jaguiarm@directvla.com.co";
                }  
                $this->guard()->login($user, true);
                
                # Guardar el inicio de sesión.
                \DB::table('users')->where('username','=',$user->username)->update(['last_session'=>date("Y-m-d H:i:s")]);
                session()->put('country', $user->country); 
                session()->put('locale', $user->language);
                \App::setLocale( $user->language );
                return true;
                    
            // }
            
        }catch(\Exception $e) {
            // unauthenticated user
            if (Auth::guest()) {
                $msj = ($e->getMessage()=="Can't contact LDAP server") ? trans('messages.000164') : $e->getMessage();
                return redirect()->guest('login')->with('message', $msj );
            }
        }
        return false;
    }
    
    protected function retrieveSyncAttributes($username){
        $username = $username . env('LDAP_ACCOUNT_SUFFIX', '');
        $ldapuser = Adldap::search()->where(env('LDAP_USER_ATTRIBUTE'), '=', $username)->first();

        if (!$ldapuser) {
            return false;
        }

        $ldapuser_attrs = null;        
        $attrs = [];
        foreach (config('ldap_auth.sync_attributes') as $local_attr => $ldap_attr) {
            if ( $local_attr == 'username' ) {
                continue;
            }
     
            $method = 'get'.$ldap_attr;
            if (method_exists($ldapuser, $method)) {
                $attrs[$local_attr] = $ldapuser->$method();
                continue;
            }
            
            if ($ldapuser_attrs === null) {
                $ldapuser_attrs = self::accessProtected($ldapuser, 'attributes');
            }
            
            if (!isset($ldapuser_attrs[$ldap_attr])) {
                // an exception could be thrown
                $attrs[$local_attr] = null;
                continue;
            }
            
            if (!is_array($ldapuser_attrs[$ldap_attr])) {
                $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr];
            }
            
            if (count($ldapuser_attrs[$ldap_attr]) == 0) {
                // an exception could be thrown
                $attrs[$local_attr] = null;
                continue;
            }

            $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr][0];
            //$attrs[$local_attr] = implode(',', $ldapuser_attrs[$ldap_attr]);
        }
        return $attrs;
    }
    
    protected static function accessProtected($obj, $prop){
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }


}
