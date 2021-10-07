<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\User;
    use Illuminate\Support\Facades\Auth;
    use App\Models\Role;

    class UsersController extends Controller
    {

        protected $controller = '';
        protected $method = '';
        protected $video = '';
        protected $urlWsdl = '';
        protected $result = array();
        protected $errors = array();
        

        public function getUsers(){
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
            return response()->json([
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

        public function deleteUser(Request $request){
            $users = \DB::table('users')->where('username','=',$request->input("username"))->delete();

            return response()->json([
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => "Usuario eliminado correctamente",
                    'video' => $this->video,
                ]
            ]); 
        }

        public function changeRole( Request $request ){
            $method = $request->method();
            try{
                 if( $request->isMethod('post') ) {
                    $inputs = $request->All();
                    if ( $inputs['username_auth']!=$inputs['userid']) {
                        if($inputs['role_id'] == 1 ){
                            $master = User::find($inputs['user_id_auth']);
                            if($master->role_id != 1){
                                array_push($this->errors, trans('Usuario no autorizado para esta accion') );
                                return response()->json([
                                    'entradas' => $inputs,
                                    'result' => $this->result,
                                    'errors' => $this->errors
                                ]);
                            }
                        }
                        
                        $registro = User::find($inputs['userid']);
                        $registro->role_id = intval($inputs['role_id']);
                        $registro->updated_at = date("Y-m-d H:i:s");
                        $registro->updated_user_id = $inputs['user_id_auth'];
                        $result = $registro->save();

                        if ($result) {
                            array_push($this->result, trans('messages.000117') );
                        }else{
                            array_push($this->errors, trans('messages.000059') );
                        }
                    }else{
                        array_push($this->errors, trans('messages.000157') );
                    }
                 }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }

            return response()->json([
                'entradas' => $inputs,
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        
    }

?>