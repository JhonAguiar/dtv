<?php
    
    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use App\Models;

    /**
     * Esta clase  
     * en el API de Provisioning.
     * @Autor <jaguiarmb@directvla.com.co>
     */
    class MongoController extends Controller
    {
        

        protected $host = '';
        protected $username = '';
        protected $password = '';
        protected $db = '';
        protected $collection = '';

        public function __construct(){
            $this->host = '10.165.1.9';
            $this->username = 'ross_user';
            $this->password = 'ross_123';
            $this->db = 'db';
            $this->collection = 'collection';
        }

        public function getAlertTasks(Request $request){
            try{

                $inputs = $request->All();

                $total = \DB::table('ejecuciones')->where('username', $inputs["username"])->where('status' ,  1)->count();
                $resultados = \DB::table('ejecuciones')->where('username',  $inputs["username"])->where('status' ,  1)->get();
                
                return response()->json([
                    'total' => $total,
                    'resultados' => $resultados
                ]); 

            }catch(\Exception $e){
                $msj = $e->getMessage();;
                return response()->json([
                    'response' => false,
                    'error' => $msj
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }

        public function getAlertTasksAll(Request $request){
            try{
                $inputs = $request->All();

                $total = \DB::table('ejecuciones')->where('username', $inputs["username"])->where('status' ,  1)->count();
                $resultados = \DB::table('ejecuciones')->where('username', $inputs["username"])->get();
                
                return response()->json([
                    'total' => $total,
                    'resultados' => $resultados
                ]); 

            }catch(\Exception $e){
                $msj = $e->getMessage();;
                return response()->json([
                    'response' => false,
                    'error' => $msj
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }

        public function changeStatusTask(Request $request){

            try{
                $inputs = $request->All();

                $result = \DB::table('ejecuciones')->where('username', $inputs["username"])->where('id' ,  $inputs["id"])->update([
                    'status'     => 2,
                ]);

                return response()->json([
                    'resultados' => $result
                ]); 

            }catch(\Exception $e){
                $msj = $e->getMessage();;
                return response()->json([
                    'response' => false,
                    'error' => $msj
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }
    }

?>
