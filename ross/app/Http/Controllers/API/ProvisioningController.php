<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\Provisioning;
    use Illuminate\Support\Facades\Storage;
    use SoapClient;
    use App\Classes\DirecTVSoapClient;
    #use App\Customs\Collections as objCollections;


    

    /**
     * Esta clase consume los servicios wsdl: adición, suspensión, activación y upgrades 
     * en el API de Provisioning.
     * @Autor <achavezb@directvla.com.co>
     */
    class ProvisioningController extends Controller
    {

        protected $controller = '';
        protected $method = '';
        protected $country = 'Colombia';
        protected $urlWsdl = '';
        protected $arrayImsis = array();
        protected $result = array();
        protected $errors = array();
        protected $urlWsdlReadAr = '';
        
        /**
         * Provisioning Colombia
         */
        protected static function profilesColombia(){

            $result = \DB::connection('db_provisioning')->select('
    		select description, active 
    		from provisioningnapi.speed_profile 
            where active=1');
            
            return response()->json([
                'response' => $result,
                'error' => false
            ])->header("Access-Control-Allow-Origin",  "*");
        }
        
        // Get Profiles - Colombia
        public static function getProfilesActive(){
            $collection = self::profilesColombia();

            return response()->json([
                'response' => $collection,
                'error' => false
            ])->header("Access-Control-Allow-Origin",  "*");
        }    

        // Suspend - Unsuspend - Colombia
        public function suspendUnsuspendProcess( Request $request ){
            $imsi_show = "";
            $load_type = $request->input('load_type');
            $tiempo_inicio = $this->microtime_float();
            
            try{
                $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
                $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                $parts = explode('@', $controller);
                $this->controller = substr($parts[0], 0, -10);
                $this->method = $parts[1];

                $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
                if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                    extract( $request->All() );
                    if ( isset($method) and !empty($method) ) {
                        $if_conection = @get_headers($this->urlWsdl);
                        if ( is_array($if_conection) ){
                            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                            $clientSoapWsdl->soap_defencoding = 'UTF-8';
                            $clientSoapWsdl->decode_utf8 = FALSE;
                            $error = $clientSoapWsdl->getError();
                            if (!$error) {
                                foreach ($this->arrayImsis as $key => $imsi) {
                                    $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                                    if ( $method=='suspend' ) {
                                        $response = $clientSoapWsdl->call('Provisioning.suspend', $params);
                                    }else{
                                        $response = $clientSoapWsdl->call('Provisioning.unsuspend', $params);
                                    }
                                    if ( $response==1 and !isset($response['faultcode']) ) {
                                        $info = 'Provisioning.'.$method.' ok.';
                                        $code = 0;
                                    }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                        $info = 'Provisioning.'.$method.' error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                        $code = $response['faultcode'];
                                    }else{
                                        $info = 'Provisioning.'.$method.' error: -1 '.trans('messages.000019');
                                        $code = -1;
                                    }
                                    $this->result[$key]['imsi'] = $imsi;
                                    $imsi_show = $imsi;
                                    $this->result[$key]['info'] = $info;
                                    $this->result[$key]['code'] = $code;
                                }
                            }else{
                                array_push($this->errors, 'Provisioning error: '.$error );
                            }
                        }else{
                            //No hay conexion con el servidor
                            array_push($this->errors, trans('messages.000043')." Provisioning." );
                        }
                    }else{
                        //Se requiere el perfil de navegación
                        array_push($this->errors, trans('messages.000051'));
                    }
                }else{
                    //Esta acción no está permitida en el sistema.
                    array_push($this->errors, trans('messages.000042'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }

            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => $request->input('method'),
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => '',
                'searchcountry' => 'CO',
                'profile' => '' ,
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);


            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        // Create - Colombia
        public function store( Request $request ){
            $imsi_show = "";
            $load_type = $request->input('load_type');
            $tiempo_inicio = $this->microtime_float();

            if(empty($request->input('profile'))){

                array_push($this->errors, 'Debe seleleccionar un perfil' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            if(count($this->errors) > 0){
                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors,
                    'tiempo' => $tiempo_fin
                ]);
            }

            if( $request->isMethod('post') ) {
                try{
                    $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];
                    
                    $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        //$inputs = $request->All();
                        extract( $request->All() );
                        if ( isset($profile) and !empty($profile) ) {
                            $if_conection = @get_headers($this->urlWsdl);
                            if ( is_array($if_conection) ){
                                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                                $clientSoapWsdl->decode_utf8 = FALSE;
                                $error = $clientSoapWsdl->getError();
                                if (!$error) {
                                    foreach ($this->arrayImsis as $key => $imsi) {
                                        $params = array(
                                            "subscriber_identity"   => $imsi, 
                                            "profile"               => mb_strtoupper($profile),
                                            "force_provisioning"    => "?",
                                            "technology"            => "?",
                                            "category"              => "?",
                                            "reset"                 => "?"
                                        );
                                        $response = $clientSoapWsdl->call('Provisioning.create', $params);
                                        if ( $response==1 and !isset($response['faultcode']) ) {
                                            $info = 'Provisioning.create ok, Profile: '.$profile;
                                            $code = 0;
                                        }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                            $info = 'Provisioning.create error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                            $code = $response['faultcode'];
                                        }else{
                                            $info = 'Provisioning.create error: -1 '.trans('messages.000019');
                                            $code = -1;
                                        }
                                        $this->result[$key]['imsi'] = $imsi;
                                        $imsi_show = $this->result[$key]['imsi'];
                                        $this->result[$key]['info'] = $info;
                                        $this->result[$key]['code'] = $code;
                                    }
                                }else{
                                    array_push($this->errors, 'Provisioning error: '.$error );
                                }
                            }else{
                                array_push($this->errors, trans('messages.000043')." Provisioning." );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000051'));
                        }
                    }else{
                        //No existen IMSIs para procesar.
                        array_push($this->errors, trans('messages.000045'));
                    }
                }catch(Exception $e){
                    array_push($this->errors, "Error: ".$e->getMessage() );
                }
            }else{
                array_push($this->errors, trans('messages.000042'));
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }

            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => 'create',
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => '',
                'searchcountry' => 'CO',
                'profile' => $request->input('profile'),
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);

            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors,
                'tiempo' => $tiempo_fin
            ]);
        }

        // Update - Colombia
        public function update( Request $request ){
            $tiempo_inicio = $this->microtime_float();
            $load_type = $request->input('load_type');
            $imsi_show = "";

            if(empty($request->input('profile'))){

                array_push($this->errors, 'Debe seleleccionar un perfil' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            if( $request->isMethod('post') ) {
                try{
                    $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];

                    $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        extract( $request->All() );
                        if ( isset($profile) and !empty($profile) ) {
                            $if_conection = @get_headers($this->urlWsdl);
                            if ( is_array($if_conection) ){
                                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                                $clientSoapWsdl->decode_utf8 = FALSE;
                                $error = $clientSoapWsdl->getError();
                                if (!$error) {
                                    foreach ($this->arrayImsis as $key => $imsi) {
                                        $params = array(
                                            "subscriber_identity" => $imsi, 
                                            "profile" => mb_strtoupper($profile),
                                            "force_provisioning" => "?",
                                            "technology" => "?",
                                            "category" => "?"
                                        );
                                        $response = $clientSoapWsdl->call('Provisioning.update', $params);
                                        if ( $response==1 and !isset($response['faultcode']) ) {
                                            $info = 'Provisioning.update ok, Profile: '.$profile;
                                            $code = 0;
                                        }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                            $info = 'Provisioning.update error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                            $code = $response['faultcode'];
                                        }else{
                                            $info = 'Provisioning.update error: -1 '.trans('messages.00055');
                                            $code = -1;
                                        }
                                        $this->result[$key]['imsi'] = $imsi;
                                        $imsi_show = $imsi;
                                        $this->result[$key]['info'] = $info;
                                        $this->result[$key]['code'] = $code;
                                    }
                                }else{
                                    array_push($this->errors, 'Provisioning error: '.$error );
                                }
                            }else{
                                array_push($this->errors, trans('messages.000043')." Provisioning." );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000051'));
                        }
                    }else{
                        array_push($this->errors, trans('messages.000042'));
                    }
                }catch(Exception $e){
                    array_push($this->errors, "Error: ".$e->getMessage() );
                }
            }else{
                array_push($this->errors, trans('messages.000042'));
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }


            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => 'update',
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => '',
                'searchcountry' => 'CO',
                'profile' => $request->input('profile'),
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);


            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        //delete - Colombia
        public function destroy( Request $request ){
            $imsi_show = "";
            $load_type = $request->input('load_type');
            $tiempo_inicio = $this->microtime_float();
            if( $request->isMethod('post') ) {
                try{
                    $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];

                    $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        $if_conection = @get_headers($this->urlWsdl);
                        if ( is_array($if_conection) ){
                            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                            $clientSoapWsdl->soap_defencoding = 'UTF-8';
                            $clientSoapWsdl->decode_utf8 = FALSE;
                            $error = $clientSoapWsdl->getError();
                            if (!$error) {
                                foreach ($this->arrayImsis as $key => $imsi) {
                                    $params = array( "subscriber_identity" => $imsi, "technology" => "?" );
                                    $response = $clientSoapWsdl->call('Provisioning.delete', $params);
                                    if ( $response==1 and !isset($response['faultcode']) ) {
                                        $info = 'Provisioning.delete ok. ';
                                        $code = 0;
                                    }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                        $info = 'Provisioning.delete error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                        $code = $response['faultcode'];
                                    }else{
                                        $info = 'Provisioning.delete error: -1 '.trans('messages.00055');
                                        $code = -1;
                                    }
                                    $this->result[$key]['imsi'] = $imsi;
                                    $imsi_show = $imsi;
                                    $this->result[$key]['info'] = $info;
                                    $this->result[$key]['code'] = $code;
                                }
                            }else{
                                array_push($this->errors, 'Provisioning error: '.$error );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000043')." Provisioning." );
                        }
                    }else{
                        array_push($this->errors, 'Formulario para eliminar perfil de navegación');
                    }
                }catch(Exception $e){
                    array_push($this->errors, "Error: ".$e->getMessage() );
                }
            }else{
                array_push($this->errors, trans('messages.000042'));
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }
            

            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => 'delete',
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => '',
                'searchcountry' => 'CO',
                'profile' => '',
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);

            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        public function microtime_float(){
            list($useg, $seg) = explode(" ", microtime());
            return ((float)$useg + (float)$seg);
        }

        public function showBrand(){
            $resultado2 = \DB::connection('db_provisioning_test')->select("
            select * from cpe_brand");

            return response()->json([
                'response' => $resultado2

            ])->header("Access-Control-Allow-Origin",  "*");   
        }

        public function showModel(Request $request){

            $brand = $request->input("brand");

            $resultado1 = \DB::connection('db_provisioning_test')->select("
                    select * from cpe_model where cpe_brand_id = ".$brand."");

            return response()->json([
                'response' => $resultado1
            ])->header("Access-Control-Allow-Origin",  "*");     
        }

        public function modelRange(Request $request){
                 
            $model = $request->input("model");
            $array = $this->getArrayModelRange($request);
            $respuesta = $this->getModelandRange($array["imsis"], $model);
           
            return response()->json([
                'response' => $respuesta,
                'error' => false
            ])->header("Access-Control-Allow-Origin",  "*");
        }

        public function getModelandRange($array , $model){
            
            $array2 = array();
            $array3 = array();
            $array4 = array();
            $resultado;
            $error = array();

            $resultado = \DB::connection('db_provisioning_test')->select("
                    select * from model_range where cpe_model_id =".$model." order by upper_limit DESC");

            $tamImsi = count($array);
            
            foreach ($array as $clave => $valor) {

                $valor["imsi"] = (int)$valor["imsi"];

                //Esta consulta me dice si la imsi se encuentra ya asignada a un rango
                $result = \DB::connection('db_provisioning_test')->select("
                select 	m.installation_type, b.description, m.snmp_community_read, 
                			m.snmp_community_write, b.cpe_brand_id, m.cpe_model_id , r.lower_limit, r.upper_limit , r.model_range_id
                	from 	model_range r 
                			inner join cpe_model m on m.cpe_model_id = r.cpe_model_id 
                			inner join cpe_brand b on b.cpe_brand_id = m.cpe_brand_id 
                	where 	'".$valor["imsi"]."' between r.lower_limit and r.upper_limit 
                    limit	1"); 

                if(count($result) > 0){
                    $result = json_decode(json_encode($result), true);
                    if($result[0]["cpe_model_id"] == $model){
                        array_push($array2, $result[0]["model_range_id"]." - El numero de registro ".$valor["imsi"]." ya ha sido asignado a el rango ".$result[0]["lower_limit"]." y ".$result[0]["upper_limit"]." del mismo modelo");
                        
                    }else{
                        array_push($array2, "Error: El numero de registro ".$valor["imsi"]." ya ha sido asignado a un rango de un modelo diferente ".$result[0]["cpe_model_id"]." , se crea nuevo registro");
                        $resultado1 = \DB::connection('db_provisioning_test')->select("update model_range set lower_limit = '".$result[0]["lower_limit"]."' ,upper_limit = '".($valor["imsi"]-1)."' where model_range_id = ".$result[0]["model_range_id"]);
                        $resultado2 = \DB::connection('db_provisioning_test')->insert("insert into model_range (lower_limit , upper_limit, cpe_model_id)  values  ('".($valor["imsi"]+1)."' ,'".$result[0]["upper_limit"]."', ".$result[0]["cpe_model_id"]." ) ");
                        $resultado3 = \DB::connection('db_provisioning_test')->insert("insert into model_range (lower_limit, upper_limit, cpe_model_id) values ('".$valor["imsi"]."' , '".$valor["imsi"]."' , ".$result[0]["cpe_model_id"].")");
                        
                    }
                }else{
                    //array_push($array2, "El numero de registro ".$valor["imsi"]." no ha sido asignado a un rango");
                    $resultado = \DB::connection('db_provisioning_test')->select("
                    select * from model_range where cpe_model_id not in (6,7) order by upper_limit DESC");

                    $resultado = json_decode(json_encode($resultado), true);
                    $limit = count($resultado);

                    for($i= 0; $i<$limit; $i++){
                        $upperLimit = $resultado[$i]["upper_limit"];
                        $lowerLimit = $resultado[$i]["lower_limit"];

                        $diferencia = $valor["imsi"] - $upperLimit;
                        $array3[$i]["dif"] = $diferencia;
                        $array3[$i]["upp"] = $upperLimit;
                        $array3[$i]["act"] = $valor["imsi"];
                        $array3[$i]["low"] = $lowerLimit;

                        //Si la posición es 0 y la diferencia es positiva asignar rango
                        if($i == 0 && ($diferencia > 0)){
                            if($diferencia < 1000){
                                $rango_mayor = $array3[$i]["upp"] + 1000;
                                $rango_menor = $array3[$i]["upp"] + 1;
                                array_push($array2, "El numero de registro ".$valor["imsi"]." se puede asignar dentro de los rangos".$rango_menor." y ".$rango_mayor." ingreso 1");
                                $cont = 0;
                                for($j = $array[$clave]; $j<$tamImsi; $j++){
                                    $resta2 =  $rango_menor - $array[$j]["imsi"];
                                    if($resta2 < 50){
                                        $cont = $resta;
                                    }else{
                                        break;
                                    }
                                }
                                $resultados = \DB::connection('db_provisioning_test')->select("insert into model_range (lower_limit, upper_limit, cpe_model_id) values ('".$cont."' , '".$rango_menor."', '".$model."')");
                                
                            }else{
                                $rango_mayor = $valor["imsi"] + 50;
                                $rango_menor = $valor["imsi"];
                                array_push($array2, "El numero de registro ".$valor["imsi"]." se puede asignar dentro de los rangos".$rango_menor." y ".$rango_mayor." ingreso 2");
                                $resultados = \DB::connection('db_provisioning_test')->select("insert into model_range (lower_limit, upper_limit, cpe_model_id) values ('".$rango_menor."' , '".$rango_mayor."', '".$model."')");
                            }
                            break;
                        }

                        if(($resultado[$i]["upper_limit"] > $valor["imsi"]) && ($resultado[$i+1]["lower_limit"] < $valor["imsi"]) ){
                            $resta = $resultado[$i]["upper_limit"] - $resultado[$i+1]["lower_limit"];
                            
                            if($resta < 50){
                                $resultados = \DB::connection('db_provisioning_test')->insert("insert into model_range (lower_limit, upper_limit, cpe_model_id) values ('".$resultado[$i+1]["lower_limit"]."' , '".$valor["imsi"]."', '".$model."')");
                                array_push($array2, "El numero de registro ".$valor["imsi"]." se encuentra dentro de los rangos ".$resultado[$i+1]["lower_limit"]." y ".$resultado[$i]["upper_limit"]." el espacio de imsis disponible es de ".$resta." ingreso 3");
                            }else{
                                $mayor = $valor["imsi"] + 50;
                                $resultados = \DB::connection('db_provisioning_test')->insert("insert into model_range (lower_limit, upper_limit, cpe_model_id) values ('".$valor["imsi"]."' , '".$mayor."', '".$model."')");
                                array_push($array2, "El numero de registro ".$valor["imsi"]." se encuentra dentro de los rangos ".$resultado[$i+1]["lower_limit"]." y ".$resultado[$i]["upper_limit"]." el espacio de imsis disponible es de ".$resta." ingreso 4");
                            }
                            
                            break;
                        }
                    }



                }                
            }
 
            return response()->json([
                 'result' => $array2,
                // 'result2' => $array3,
                // 'defi ' => $array4,
                //'resultado' => $resultado,
                // 'error' => $error
            ])->header("Access-Control-Allow-Origin",  "*");
        }
 
        public function getArrayModelRange($data='' ){

            ini_set('memory_limit', '256M');
            #echo "<pre>"; print_r($data ); die("dos");
            extract( $data->All() );
            $arrayImsis = array();
            $arrayImsis2 = array();
            $processErrors = array();
            $objResources = new \App\Http\Controllers\ResourcesController;


            if ( isset($load_type) and !empty($load_type) ){

                if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
                    if ($data->hasFile('subscriber_identity_file') and $data->file('subscriber_identity_file')->isValid()) {
                        if ($data->hasFile('subscriber_identity_file') and $data->file('subscriber_identity_file')->isValid()) {
                            $validate = array();
                            $validate['name'] = $data->file('subscriber_identity_file')->getClientOriginalName();
                            $validate['tmp_name'] = $data->file('subscriber_identity_file')->getPathName();
                            $validate['error'] = $data->file('subscriber_identity_file')->getError();
                            $validate['size'] = $data->file('subscriber_identity_file')->getClientSize();
                            $validate['ext'] = $data->file('subscriber_identity_file')->getClientOriginalExtension();
                            if ( $validate['error']==0 ) {
                                $max = 10240000; //Bytes = 1024 Kilobytes
                                if ($validate['size'] < $max) {
                                    $info = explode( ".", $validate['name'] );
                                    $ext = mb_strtolower( trim( array_pop( $info ) ) ) ;
                                    if ( $ext=='txt' or $ext=='csv' ) {
                                        $line=1;
                                        $f =0;
                                        
                                        if (($gestor=fopen( $validate['tmp_name'], "r") ) !== FALSE) {
                                            while ( ($filedata = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                                                $num = count($filedata);
                                                $f++;
                                                if($f == 1){
                                                }else{
                                                    //Recorremos las columnas de esa linea
                                                    for ($columna = 0; $columna < $num; $columna++) 
                                                    {
                                                        $parts = explode(';', $filedata[$columna]);
                                                        $arrayImsis[$line]["IMSI"] = $parts[0];
                                                        $line++;
                                                    }
                                                }
                                                
                                            }
                                            // Ordenar array de forma ascendente
                                            asort($arrayImsis);
                                            $var = 0;
                                            foreach($arrayImsis as $key => $val){
                                                $arrayImsis2[$var]["key"] = $key;
                                                $arrayImsis2[$var]["imsi"] = $val["IMSI"];
                                                $var++;
                                            }

                                            fclose($gestor);
                                        }else{
                                            //No se puede abrir el archivo
                                            array_push( $processErrors, "No se puede abrir el archivo");
                                        }
                                    }else{
                                        //Tipo de archivo no permitido
                                        array_push( $processErrors, "Tipo de archivo no permitido");
                                    }
                                
                                }else{
                                    //El archivo es muy grande.
                                    array_push( $processErrors, "El tamaño del archivo es muy grande.");
                                }
                            }else{
                                //El archivo no cumple con las especificaciones.
                                array_push( $processErrors, "El archivo no cumple con las especificaciones.");
                            }
                        }else{
                            array_push( $processErrors, "El archivo no cumple con las especificaciones.");
                        }
                    }
                }else if( isset($subscriber_identity) and !empty($subscriber_identity) ){
                    $imsi = self::imsiColombia( $subscriber_identity );
                    if (!empty($imsi)) {
                        array_push($arrayImsis, $imsi);
                    }
                }else{
                    array_push( $processErrors, "Campo de datos no especificado");
                }
            }else{
                array_push( $processErrors, "Se requiere seleccionar el tipo de imsi que desea cargar");
            }

            return [
                'imsis' => $arrayImsis2,
                'errors' => $processErrors
            ];
        }

        public function loadTopology(Request $request){

            $array = $this->getArrayTopology($request);
            
            $respuesta = $this->getSqlTopology($array);

            return response()->json([
                'result' => $respuesta,
            ]);
        }

        public function getSqlTopology($data = ''){
            $string = '';
            $myfile = fopen("cell_id.sql", "w") or die("Unable to open file!");
            $arr = $data;
            foreach ($arr as $key => $value) {
                $string = "insert into cell (site_id, cell_id, ocupancy,max_speed_profile_priority) values ((select site_id from site where enodebid = ". $value["EnodebId"]."), ".$value["SECTOR_NUMBER"]." , ".$value["NIVEL Afectación por AO RADIO"].", ".$value["speedProfile"].");\n";
                fwrite($myfile, $string);
            }
            fclose($myfile);

            //Storage::disk('ftp')->put("cell_id.sql", fopen("cell_id.sql" , 'r+'));
            $c = curl_init();
            $file = "cell_id.sql";
            $fp = fopen($file, "r");

            curl_setopt($c, CURLOPT_URL, 'sftp://10.165.1.4/Data/'.$file);
            curl_setopt($c, CURLOPT_USERPWD, 'vrio\ftp_script:Password*006');
            curl_setopt($c, CURLOPT_UPLOAD, 1);
            curl_setopt($c, CURLOPT_INFILE, $fp);
            curl_setopt($c, CURLOPT_INFILESIZE, filesize($file));
            $error = '';
            $ok = '';
            if(curl_exec($c) === false)
            {
                $error = 'Curl error: ' . curl_error($c);
            }
            else
            {
                $ok = 'Operación completada sin errores';
            }


            curl_close($c);

            //Detenemos la funcion con un mensajes
            return response()->json([
                'error' => $error,
                'response' => $ok,
            ]);


        }

        public function getArrayTopology($data = ''){
            
            ini_set('memory_limit', '256M');
            #echo "<pre>"; print_r($data ); die("dos");
            extract( $data->All() );
            $arrayImsis = array();
            $arrayImsis2 = array();
            $processErrors = array();
            $objResources = new \App\Http\Controllers\ResourcesController;


                if ( isset($file) ) {
                    if ($data->hasFile('file') and $data->file('file')->isValid()) {
                        if ($data->hasFile('file') and $data->file('file')->isValid()) {
                            $validate = array();
                            $validate['name'] = $data->file('file')->getClientOriginalName();
                            $validate['tmp_name'] = $data->file('file')->getPathName();
                            $validate['error'] = $data->file('file')->getError();
                            $validate['size'] = $data->file('file')->getClientSize();
                            $validate['ext'] = $data->file('file')->getClientOriginalExtension();
                            if ( $validate['error']==0 ) {
                                $max = 10240000; //Bytes = 1024 Kilobytes
                                if ($validate['size'] < $max) {
                                    $info = explode( ".", $validate['name'] );
                                    $ext = mb_strtolower( trim( array_pop( $info ) ) ) ;
                                    if ( $ext=='csv' ) {
                                        $line=1;
                                        $f =0;
                                        
                                        if (($gestor=fopen( $validate['tmp_name'], "r") ) !== FALSE) {
                                            while ( ($filedata = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                                                $num = count($filedata);
                                                $f++;
                                                if($f == 1){
                                                }else{
                                                    //Recorremos las columnas de esa linea
                                                    for ($columna = 0; $columna < $num; $columna++) 
                                                    {
                                                        $parts = explode(';', $filedata[$columna]);
                                                        $arrayImsis[$line]["Codigo sitio"] = $parts[0];
                                                        $arrayImsis[$line]["Nombre Sitio"] = $parts[1];
                                                        $arrayImsis[$line]["SECTOR"] = $parts[2];
                                                        $arrayImsis[$line]["SECTOR_NUMBER"] = substr($parts[2], -1);
                                                        $arrayImsis[$line]["CUANTAS PORTADORAS TIENE ACTUALMENTE"] = $parts[3];
                                                        $arrayImsis[$line]["Ventas detenidas"] = $parts[4];
                                                        $arrayImsis[$line]["Posibilidad de upgrades ( MÁXIMO PLAN Recomendado PARA OFRECER UPGRADE)"] = $parts[5];
                                                        $arrayImsis[$line]["NIVEL Afectación por AO RADIO"] = $parts[6];
                                                        $arrayImsis[$line]["EnodebId"] = $parts[7];
                                                        $arrayImsis[$line]["speedProfile"] = 30;
                                                        $line++;
                                                    }
                                                }
                                                
                                            }
                                            // Ordenar array de forma ascendente
                                            asort($arrayImsis);
                                            $var = 0;

                                            fclose($gestor);
                                        }else{
                                            //No se puede abrir el archivo
                                            array_push( $processErrors, "No se puede abrir el archivo");
                                        }
                                    }else{
                                        //Tipo de archivo no permitido
                                        array_push( $processErrors, "Tipo de archivo no permitido");
                                    }
                                
                                }else{
                                    //El archivo es muy grande.
                                    array_push( $processErrors, "El tamaño del archivo es muy grande.");
                                }
                            }else{
                                //El archivo no cumple con las especificaciones.
                                array_push( $processErrors, "El archivo no cumple con las especificaciones.");
                            }
                        }else{
                            array_push( $processErrors, "El archivo no cumple con las especificaciones.");
                        }
                    }
                }

            return $arrayImsis;
        }

        /**
         * Provisioning Argentina
         */
        protected static function profilesArgentina(){
            $elemento = [
                ['id' => 1, 'technology' => 'FTTH', 'profiles' => ['2MB', '3MB','6MB', '12MB', '30MB', '100MB']],
                ['id' => 2, 'technology' => 'LTE/WIMAX', 'profiles' => ['2MB','3MB', '6MB', '10MB', '12MB' , '15MB', '20MB', '25MB' , 'INTERNET PREPAGO']],
                ['id' => 3, 'technology' => 'SATELITAL', 'profiles' => ['1GB', '5GB', '10GB', '20GB', '30GB', '50GB']],
                ['id' => 4, 'technology' => '5G', 'profiles' => ['6MB', '10MB', '12MB', '20MB', '30MB', '50MB']],
            ];
            return $elemento;
        }

        // Get Profiles - Argentina
        public static function getProfilesActiveAr(){
            $collection = self::profilesArgentina();
            return response()->json([
                'response' => $collection,
                'error' => false
            ])->header("Access-Control-Allow-Origin",  "*");
        } 

        //List Technologies
        protected static function technologiesArgentina(){
            return [
                ['id' => 1, 'technology' => 'FTTH', 'status' => 'active', 'selected' => ''],
                ['id' => 2, 'technology' => 'LTE/WIMAX', 'status' => 'active', 'selected' => 'selected'],
                ['id' => 3, 'technology' => 'SATELITAL', 'status' => 'active', 'selected' => ''],            
                ['id' => 4, 'technology' => '5G', 'status' => 'active', 'selected' => ''], 
            ];
        }

        // Get Technologies Argentina
        public static function getTechnologiesActiveAr(){
            $collection = self::technologiesArgentina();
            $dataCollection = collect( $collection )->where('status', 'active');
            return response()->json([
                'response' => $dataCollection,
                'error' => false
            ])->header("Access-Control-Allow-Origin",  "*");
        } 

        # Suspend - Unsuspend Argentina
        public function suspendUnsuspendProcessAr( Request $request ){
            $this->urlWsdlReadAr = config('appross.provisioning_wsdl_arg_read', '');
            $imsi_show = '';
            $tiempo_inicio = $this->microtime_float();
            $load_type = '';

            $technology = $request->input('technology');
            if(empty($technology)){

                array_push($this->errors, 'Debe seleleccionar una tecnología' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            try{
                $this->urlWsdl = config('appross.provisioning_wsdl_arg', '');
                $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                $parts = explode('@', $controller);
                $this->controller = substr($parts[0], 0, -10);
                $this->method = $parts[1];
                $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
                $load_type = $request->input('load_type');
                if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                    extract( $request->All() );
                    if ( isset($method) and !empty($method) ) {
                        $if_conection = @get_headers($this->urlWsdl);
                        if ( is_array($if_conection) ){


                            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                            $clientSoapWsdl->soap_defencoding = 'UTF-8';
                            $clientSoapWsdl->decode_utf8 = FALSE;
                            $error = $clientSoapWsdl->getError();

                            if($technology == '5G'){
                                $clientSoapWsdl5G = new \nusoap_client( $this->urlWsdlReadAr, true );
                                $clientSoapWsdl5G->soap_defencoding = 'UTF-8';
                                $clientSoapWsdl5G->decode_utf8 = FALSE;
                                $error = $clientSoapWsdl5G->getError();
                            }

                            if (!$error) {
                                foreach ($this->arrayImsis as $key => $imsi) {
                                    if($technology == '5G'){
                                        $params = array(
                                            "subscriber_identity" => mb_strtoupper($imsi), 
                                            "technology" => mb_strtoupper($technology)
                                        );
                                        
                                        if ( $method=='suspend' ) {
                                            $response = $clientSoapWsdl5G->call('Provisioning.suspend', $params);
                                        }else{
                                            $response = $clientSoapWsdl5G->call('Provisioning.unsuspend', $params);
                                        }
                                         
                                        if ( $response==1 and !isset($response['faultcode']) ) {
                                            $info = 'Provisioning.'.$method.' ok.';
                                            $code = 0;
                                        }elseif( isset($response['faultcode']) and (!empty($response['faultcode'] or $response['faultcode'] == 0)  ) ){
                                            $info = 'Provisioning.'.$method.' error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                            $code = $response['faultcode'];
                                        }else{
                                            $info = 'Provisioning.'.$method.' error: -1 '.trans('messages.000019');
                                            $code = -1;
                                        }
                                        $this->result[$key]['imsi'] = $imsi;
                                        $imsi_show = $imsi;
                                        $this->result[$key]['info'] = $info;
                                        $this->result[$key]['code'] = $code;
                                    }
                                    else{
                                        $metodo = '';
                                        $m = '';
                                        if ( $method=='suspend' ) {
                                            $metodo = 'SuspendRqElement'; 
                                            $m = 'Suspend';
                                        }else{
                                            $metodo = 'UnsuspendRqElement'; 
                                            $m = 'Unsuspend';
                                        }          
                                        
                                        $url='http://172.20.4.55/API_ING_Provisioning/index.php?wsdl';
                                            $context = stream_context_create(array(
                                            'http' => array(
                                                'header' => "User-Agent: PHP-SOAP\r\n"
                                                )
                                            ));
                                            
                                            $imsi_show = $request->input('subscriber_identity');
        
                                                $requestXml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bro="BroadbandProvisioningServices">
                                                <soapenv:Header/>
                                                <soapenv:Body>
                                                    <bro:'.$metodo.'>
                                                        <subscriberIdentity>'.mb_strtoupper($imsi).'</subscriberIdentity>
                                                        <customerID></customerID>
                                                        <deviceSerialNumber></deviceSerialNumber>
                                                        <servicePlan></servicePlan>
                                                        <invoiceProfile></invoiceProfile>
                                                        <oldSerialNumber></oldSerialNumber>
                                                        <capacity></capacity>
                                                        <technology>'.mb_strtoupper($technology).'</technology>
                                                    </bro:'.$metodo.'>
                                                </soapenv:Body>
                                                </soapenv:Envelope>
                                                ';
                
                                            $action = $m . 'Action';
                                            $url = str_replace('?wsdl', '', $url);
                                            
                                            $curlResponse = $this->curlCallOnError4($url, $action, $requestXml, mb_strtoupper($imsi), $method);
        
                                            array_push($this->result ,$curlResponse);


                                    }
                                }
                            }else{
                                array_push($this->errors, 'Provisioning error: '.$error );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000043')." Provisioning." );
                        }
                    }else{
                        array_push($this->errors, trans('messages.000051'));
                    }
                }else{
                    array_push($this->errors, trans('messages.000045'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }

            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => $request->input('method'),
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => $request->input('technology'),
                'searchcountry' => 'AR',
                'profile' => '',
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);
                //$request->input('subscriber_identity_file')

            return response()->json([
                'response' => $request->input('subscriber_identity_file'),
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        // Create - Argentina
        public function storeAr( Request $request ){
            $this->urlWsdlReadAr = config('appross.provisioning_wsdl_arg_read', '');
            $tiempo_inicio = $this->microtime_float();
            $imsi_show = '';
            $load_type = '';
            $technology = $request->input('technology');
            $respuesta;
            if(empty($technology)){

                array_push($this->errors, 'Debe seleleccionar una tecnología' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }
            if(empty($request->input('profile'))){

                array_push($this->errors, 'Debe seleleccionar un perfil' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }
            if(empty($request->input('subscriber_identity')) && empty($request->input('subscriber_identity_file'))){
                array_push($this->errors, 'Debe seleccionar una imsi o un archivo' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            if( $request->isMethod('post') ) {
                try{
                    $this->urlWsdl = config('appross.provisioning_wsdl_arg', '');
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];
                    $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        //$inputs = $request->All();
                        extract( $request->All() );
                        if ( isset($profile) and !empty($profile) ) {
                            $if_conection = @get_headers($this->urlWsdl);
                            if ( is_array($if_conection) ){

                                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                                $clientSoapWsdl->decode_utf8 = FALSE;
                                $error = $clientSoapWsdl->getError();

                                if($technology == '5G'){
                                    $clientSoapWsdl5G = new \nusoap_client( $this->urlWsdlReadAr, true );
                                    $clientSoapWsdl5G->soap_defencoding = 'UTF-8';
                                    $clientSoapWsdl5G->decode_utf8 = FALSE;
                                    $error = $clientSoapWsdl5G->getError();
                                }

                                if (!$error) {
                                    foreach ($this->arrayImsis as $key => $imsi) {
                                        $params = array(
                                            "subscriber_identity"   => mb_strtoupper($imsi), 
                                            "profile"               => mb_strtoupper($profile),
                                            "force_provisioning"    => "false",
                                            "technology"            => mb_strtoupper($technology)  
                                        );
                                        if($technology == '5G'){
                                            $response = $clientSoapWsdl5G->call('Provisioning.create', $params);
                                       
                                            if ( $response==1 and !isset($response['faultcode']) ) {
                                                $info = 'Provisioning.create ok, Profile: '.$profile;
                                                $code = 0;
                                            }elseif( isset($response['faultcode']) and (!empty($response['faultcode'] or $response['faultcode'] == 0) ) ){
                                                $info = 'Provisioning.create error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                                $code = $response['faultcode'];
                                            }else{
                                                $info = 'Provisioning.create error: -1 '.trans('messages.000019');
                                                $code = -1;
                                            }
                                            $this->result[$key]['imsi'] = $imsi;
                                            $imsi_show = $imsi;
                                            $this->result[$key]['info'] = $info;
                                            $this->result[$key]['code'] = $code;
                                            $load_type = $request->input('load_type');
                                        }else{
                                            $url='http://172.20.4.55/API_ING_Provisioning/index.php?wsdl';
                                            $context = stream_context_create(array(
                                            'http' => array(
                                                'header' => "User-Agent: PHP-SOAP\r\n"
                                                )
                                            ));
                                            
                                            $imsi_show = $request->input('subscriber_identity');
        
                                                $requestXml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bro="BroadbandProvisioningServices">
                                                <soapenv:Header/>
                                                <soapenv:Body>
                                                    <bro:ActivateRqElement>
                                                        <subscriberIdentity>'.mb_strtoupper($imsi).'</subscriberIdentity>
                                                        <customerID></customerID>
                                                        <deviceSerialNumber></deviceSerialNumber>
                                                        <servicePlan>'.mb_strtoupper($profile).'</servicePlan>
                                                        <invoiceProfile></invoiceProfile>
                                                        <oldSerialNumber></oldSerialNumber>
                                                        <capacity></capacity>
                                                        <technology>'.mb_strtoupper($technology).'</technology>
                                                    </bro:ActivateRqElement>
                                                </soapenv:Body>
                                                </soapenv:Envelope>
                                                ';
                
                                            $action = 'Activate' . 'Action';
                                            $url = str_replace('?wsdl', '', $url);
                                            
                                            $curlResponse = $this->curlCallOnError3($url, $action, $requestXml, mb_strtoupper($imsi));
        
                                            array_push($this->result ,$curlResponse);
                                        }
                                    }
                                }else{
                                    array_push($this->errors, 'Provisioning error: '.$error );
                                }
                            }else{
                                array_push($this->errors, trans('messages.000043')." Provisioning." );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000051'));
                        }
                    }else{
                        array_push($this->errors, trans('messages.000045'));
                    }
                    
                }catch(Exception $e){
                    array_push($this->errors, "Error: ".$e->getMessage() );
                }
            }else{
                array_push($this->errors, trans('messages.000042'));
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }

            
            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => "create",
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => $technology,
                'searchcountry' => 'AR',
                'profile' => $request->input('profile'),
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);

            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        // Update - Argentina
        public function updateAr( Request $request ){
            $this->urlWsdlReadAr = config('appross.provisioning_wsdl_arg_read', '');
            $imsi_show = "";
            $load_type = $request->input('load_type');
            $respuesta = array();
            $technology = $request->input('technology');
            if(empty($technology)){

                array_push($this->errors, 'Debe seleleccionar una tecnología' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            if(empty($request->input('profile'))){

                array_push($this->errors, 'Debe seleleccionar un perfil' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }
            
            sleep(8);
            set_time_limit(180);
            $tiempo_inicio = $this->microtime_float();
            if( $request->isMethod('post') ) {
                try{
                    $this->urlWsdl = config('appross.provisioning_wsdl_arg', '');
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];
                    $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        extract( $request->All() );
                        if ( isset($profile) and !empty($profile) ) {
                            $if_conection = @get_headers($this->urlWsdl);
                            if ( is_array($if_conection) ){

                                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                                $clientSoapWsdl->decode_utf8 = FALSE;
                                $error = $clientSoapWsdl->getError();

                                if($technology == '5G'){
                                    $clientSoapWsdl5G = new \nusoap_client( $this->urlWsdlReadAr, true );
                                    $clientSoapWsdl5G->soap_defencoding = 'UTF-8';
                                    $clientSoapWsdl5G->decode_utf8 = FALSE;
                                    $error = $clientSoapWsdl5G->getError();
                                }
                                    if (!$error) {
                                        foreach ($this->arrayImsis as $key => $imsi) {
                                            if($technology == '5G'){
                                                $params = array(
                                                    "subscriber_identity" => mb_strtoupper($imsi), 
                                                    "profile" => mb_strtoupper($profile),
                                                    "technology" => mb_strtoupper($technology)
                                                );
                                                
                                                $response = $clientSoapWsdl5G->call('Provisioning.update', $params);
                                        
                                                if ( $response==1 and !isset($response['faultcode']) ) {
                                                    $info = 'Provisioning.update ok, Profile: '.$profile;
                                                    $code = 0;
                                                }elseif( isset($response['faultcode']) and (!empty($response['faultcode'] or $response['faultcode'] == 0)) ){
                                                    $info = 'Provisioning.update error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                                    $code = $response['faultcode'];
                                                }else{
                                                    $info = 'Provisioning.update error: -1 '.trans('messages.000019');
                                                    $code = -1;
                                                }
                                                $this->result[$key]['imsi'] = $imsi;
                                                $imsi_show = $imsi;
                                                $this->result[$key]['info'] = $info;
                                                $this->result[$key]['code'] = $code;
                                            }else{
                                                $url='http://172.20.4.55/API_ING_Provisioning/index.php?wsdl';
                                                $context = stream_context_create(array(
                                                'http' => array(
                                                    'header' => "User-Agent: PHP-SOAP\r\n"
                                                    )
                                                ));
                                                
                                                $imsi_show = $request->input('subscriber_identity');
            
                                                    $requestXml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bro="BroadbandProvisioningServices">
                                                    <soapenv:Header/>
                                                    <soapenv:Body>
                                                        <bro:UpdateRqElement>
                                                            <subscriberIdentity>'.mb_strtoupper($imsi).'</subscriberIdentity>
                                                            <customerID></customerID>
                                                            <deviceSerialNumber></deviceSerialNumber>
                                                            <servicePlan>'.mb_strtoupper($profile).'</servicePlan>
                                                            <invoiceProfile></invoiceProfile>
                                                            <oldSerialNumber></oldSerialNumber>
                                                            <capacity></capacity>
                                                            <technology>'.mb_strtoupper($technology).'</technology>
                                                        </bro:UpdateRqElement>
                                                    </soapenv:Body>
                                                    </soapenv:Envelope>
                                                    ';
                    
                                                $action = 'Update' . 'Action';
                                                $url = str_replace('?wsdl', '', $url);
                                                
                                                $curlResponse = $this->curlCallOnError2($url, $action, $requestXml, mb_strtoupper($imsi));
            
                                                array_push($this->result ,$curlResponse);
                                            }
                                        }
                                    }else{
                                        array_push($this->errors, 'Provisioning error: '.$error );
                                    }
                               

                            }else{
                                array_push($this->errors, trans('messages.000043')." Provisioning." );
                            }
                        }else{
                            array_push($this->errors, trans('messages.000051'));
                        }
                    }else{
                        array_push($this->errors, trans('messages.000045'));
                    }
                }catch(Exception $e){
                    array_push($this->errors, "Error: ".$e->getMessage() );
                }
            }else{
                array_push($this->errors, trans('messages.000042'));
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }
            
            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => "update",
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => $request->input('technology'),
                'searchcountry' => 'AR',
                'profile' => $request->input('profile'),
                'searchresponse' => strval(json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            return response()->json([
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }
        // Destroy - Argentina
        public function destroyAr( Request $request ){
            $this->urlWsdlReadAr = config('appross.provisioning_wsdl_arg_read', '');
            $imsi_show = "";
            $load_type = $request->input('load_type');
            $tiempo_inicio = $this->microtime_float();
            $respuesta = array();

            $technology = $request->input('technology');
            if(empty($technology)){

                array_push($this->errors, 'Debe seleleccionar una tecnología' );

                return response()->json([
                    'result' => $this->result,
                    'errors' => $this->errors
                ]);
            }

            if( $request->isMethod('post') ) {              
                    //$this->urlWsdl = config('appross.provisioning_wsdl_arg', '');
                    $this->urlWsdl = "http://172.20.4.55/API_ING_Provisioning/index.php?wsdl";
                    $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                    $parts = explode('@', $controller);
                    $this->controller = substr($parts[0], 0, -10);
                    $this->method = $parts[1];
                    $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
                    $arrayData = $objImsis->getArrayImsis( $request );
                    $this->arrayImsis = $arrayData['imsis'];
                    $this->errors = array_merge($arrayData['errors']);
                    if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                        extract( $request->All() );
                        $if_conection = @get_headers($this->urlWsdl);
                        if ( is_array($if_conection) ){

                            foreach ($this->arrayImsis as $key => $imsi) {                           
                                
                                if($technology == "5G"){
                                    $clientSoapWsdl5G = new \nusoap_client( $this->urlWsdlReadAr, true );
                                    $clientSoapWsdl5G->soap_defencoding = 'UTF-8';
                                    $clientSoapWsdl5G->decode_utf8 = FALSE;
                                    $error = $clientSoapWsdl5G->getError();
                                    
                                    if (!$error) {
                                        foreach ($this->arrayImsis as $key => $imsi) {
                                            $params = array(
                                                "subscriber_identity" => mb_strtoupper($imsi), 
                                                "technology" => mb_strtoupper($technology)
                                            );
                                            
                                            $response = $clientSoapWsdl5G->call('Provisioning.delete', $params);
                                            
                                            if ( $response==1 and !isset($response['faultcode']) ) {
                                                $info = 'Provisioning.delete ok. ';
                                                $code = 0;
                                            }elseif( isset($response['faultcode']) and (!empty($response['faultcode'] or $response['faultcode'] == 0)) ){
                                                $info = 'Provisioning.delete error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                                $code = $response['faultcode'];
                                            }else{
                                                $info = 'Provisioning.delete error: -1 '.trans('messages.000019');
                                                $code = -1;
                                            }
                                            $this->result[$key]['imsi'] = $imsi;
                                            $imsi_show = $imsi;
                                            $this->result[$key]['info'] = $info;
                                            $this->result[$key]['code'] = $code;
                                        }
                                    }else{
                                        array_push($this->errors, 'Provisioning error: '.$error );
                                    }
                                }else{
                                    $url='http://172.20.4.55/API_ING_Provisioning/index.php?wsdl';
                                    $context = stream_context_create(array(
                                    'http' => array(
                                        'header' => "User-Agent: PHP-SOAP\r\n"
                                        )
                                    ));

                                
                                        $requestXml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bro="BroadbandProvisioningServices">
                                        <soapenv:Header/>
                                        <soapenv:Body>
                                            <bro:DeactivateRqElement>
                                                <subscriberIdentity>'.mb_strtoupper($imsi).'</subscriberIdentity>
                                                <customerID></customerID>
                                                <deviceSerialNumber></deviceSerialNumber>
                                                <servicePlan></servicePlan>
                                                <invoiceProfile></invoiceProfile>
                                                <oldSerialNumber></oldSerialNumber>
                                                <capacity></capacity>
                                                <technology>'.mb_strtoupper($technology).'</technology>
                                            </bro:DeactivateRqElement>
                                        </soapenv:Body>
                                        </soapenv:Envelope>
                                        ';
        
                                    $action = 'Deactivate' . 'Action';
                                    $url = str_replace('?wsdl', '', $url);

                                    $curlResponse = $this->curlCallOnError($url, $action, $requestXml, mb_strtoupper($imsi));

                                    array_push($respuesta, $curlResponse);
                                }

                            }
                        }else{
                            array_push($this->errors, trans('messages.000043')." Provisioning." );
                        }
                    }else{
                        array_push($this->errors, trans('messages.000042'));
                    }
                    
               
                
            }else{
                array_push($this->errors, trans('messages.000042').'revise el formato de carga');
            }

            $tiempo_fin = $this->microtime_float();
            $tiempo = $tiempo_fin - $tiempo_inicio;
            $base64 = "";
            if($load_type != "individual"){
                $path = $request->file('subscriber_identity_file');
                $archivo = file_get_contents($path);
                $base64 = base64_encode($archivo);
            }else{
                $imsi_show = $request->input('subscriber_identity');
            }
            
            \DB::table('log_provisionings')->insert([
                'searchdate' => date('Y-m-d H:i:s'), 
                'secondssearch' => $tiempo,
                'searchmethod' => "delete",
                'searchuser' => $request->input('username'),
                'searchimsi' => $imsi_show,
                'technology' => $request->input('technology'),
                'searchcountry' => 'AR',
                'profile' => '',
                'searchresponse' => strval(json_encode($respuesta).' '.json_encode($this->result).' '.json_encode($this->errors)),
                'searchtype' => $load_type,
                'searchfile' => $load_type ==  "individual" ? "" : $base64,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
                ]);

            return response()->json([
                'response' => $respuesta,
                'result' => $this->result,
                'errors' => $this->errors
            ]);
        }

        public function curlCallOnError4($url, $action, $xmlData , $imsi, $method) {
            $headers = array(
                    'Content-Type: text/xml; charset=utf-8',
                    'Content-Length: ' . strlen($xmlData),
                    'SOAPAction: ' . $action
            );            
            try{
                $metodo = $method == 'suspend' ? "SuspendRsElement" : "UnsuspendRsElement";

                $xmlData = str_replace("SOAP-ENV", "soapenv", $xmlData);
                $xmlData = str_replace("ns1", "bro", $xmlData);

                
                // Build the cURL session
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                //var_dump(htmlspecialchars($xmlData));
    
                $result = curl_exec($ch);
                //var_dump($result);
                if ($result === false) {
                        curl_close($ch);
                        return false;
                }
    
                $response = curl_multi_getcontent($ch);
        
                if (!empty($response)) {
                        $clean_xml = str_ireplace(['soapenv:', 'bro:'], '', $response);
                        $xmlObj = simplexml_load_string($clean_xml);
    
                        if (!empty($xmlObj) && is_object($xmlObj)) {
                                //error_log(print_r($xmlObj, true));
                            //print_r($xmlObj);
                            if (property_exists($xmlObj, 'Body')) {
                                $body = $xmlObj->Body;

                                if (property_exists($body, 'Fault')) {
                                    $faultCode = $body->Fault->faultcode;
                                    $faultString = $body->Fault->faultstring;
                                    $detailCode = $detailMessage = null;

                                    if (property_exists($body->Fault, 'detail')) {
                                            $detail = $body->Fault->detail;
                                            $detailCode = $body->Fault->detail->CommonExceptionElement->code;
                                            $detailMessage = $body->Fault->detail->CommonExceptionElement->message;
                                    }

                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;
                                    $faultCode = (array)$faultCode;
                                    $faultString = (array)$faultString;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'fault_code'    => $faultCode[0],
                                        'fault_string'  => $faultString[0],
                                        'imsi' => $imsi
                                    );

                                    
                                }else if(property_exists($body, $metodo)){
                                    //var_dump($method);
                                    if($method == 'suspend'){
                                        $detailCode = $body->SuspendRsElement->status;
                                        $detailMessage = $body->SuspendRsElement->message;
                                    }else{
                                        $detailCode = $body->UnsuspendRsElement->status;
                                        $detailMessage = $body->UnsuspendRsElement->message;
                                    }
                                
                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'imsi' => $imsi
                                    );
                                }
                                return $result;
                            }
                    }
                }
        
                curl_close($ch);

            }catch (Exception $e) {
                var_dump($e);
                //      error_log("curlCallOnError Exception. Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            }
        }

        public function curlCallOnError3($url, $action, $xmlData , $imsi) {
            $headers = array(
                    'Content-Type: text/xml; charset=utf-8',
                    'Content-Length: ' . strlen($xmlData),
                    'SOAPAction: ' . $action
            );            
            try{
        
                $xmlData = str_replace("SOAP-ENV", "soapenv", $xmlData);
                $xmlData = str_replace("ns1", "bro", $xmlData);

                
                // Build the cURL session
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                //var_dump(htmlspecialchars($xmlData));
    
                $result = curl_exec($ch);
                //var_dump($result);
                if ($result === false) {
                        curl_close($ch);
                        return false;
                }
    
                $response = curl_multi_getcontent($ch);
        
                if (!empty($response)) {
                        $clean_xml = str_ireplace(['soapenv:', 'bro:'], '', $response);
                        $xmlObj = simplexml_load_string($clean_xml);
    
                        if (!empty($xmlObj) && is_object($xmlObj)) {
                                //error_log(print_r($xmlObj, true));
                            //print_r($xmlObj);
                            if (property_exists($xmlObj, 'Body')) {
                                $body = $xmlObj->Body;

                                if (property_exists($body, 'Fault')) {
                                    $faultCode = $body->Fault->faultcode;
                                    $faultString = $body->Fault->faultstring;
                                    $detailCode = $detailMessage = null;

                                    if (property_exists($body->Fault, 'detail')) {
                                            $detail = $body->Fault->detail;
                                            $detailCode = $body->Fault->detail->CommonExceptionElement->code;
                                            $detailMessage = $body->Fault->detail->CommonExceptionElement->message;
                                    }

                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;
                                    $faultCode = (array)$faultCode;
                                    $faultString = (array)$faultString;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'fault_code'    => $faultCode[0],
                                        'fault_string'  => $faultString[0],
                                        'imsi' => $imsi
                                    );

                                    
                                }else if(property_exists($body, 'ActivateRsElement')){

                                    $detailCode = $body->ActivateRsElement->status;
                                    $detailMessage = $body->ActivateRsElement->message;
                                    
                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'imsi' => $imsi
                                    );
                                }
                                return $result;
                            }
                    }
                }
        
                curl_close($ch);

            }catch (Exception $e) {
                var_dump($e);
                //      error_log("curlCallOnError Exception. Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            }
        }

        public function curlCallOnError2($url, $action, $xmlData , $imsi) {
            $headers = array(
                    'Content-Type: text/xml; charset=utf-8',
                    'Content-Length: ' . strlen($xmlData),
                    'SOAPAction: ' . $action
            );            
            try{
        
                $xmlData = str_replace("SOAP-ENV", "soapenv", $xmlData);
                $xmlData = str_replace("ns1", "bro", $xmlData);

                
                // Build the cURL session
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                //var_dump(htmlspecialchars($xmlData));
    
                $result = curl_exec($ch);
                //var_dump($result);
                if ($result === false) {
                        curl_close($ch);
                        return false;
                }
    
                $response = curl_multi_getcontent($ch);
        
                if (!empty($response)) {
                        $clean_xml = str_ireplace(['soapenv:', 'bro:'], '', $response);
                        $xmlObj = simplexml_load_string($clean_xml);
    
                        if (!empty($xmlObj) && is_object($xmlObj)) {
                                //error_log(print_r($xmlObj, true));
                            //print_r($xmlObj);
                            if (property_exists($xmlObj, 'Body')) {
                                $body = $xmlObj->Body;

                                if (property_exists($body, 'Fault')) {
                                    $faultCode = $body->Fault->faultcode;
                                    $faultString = $body->Fault->faultstring;
                                    $detailCode = $detailMessage = null;

                                    if (property_exists($body->Fault, 'detail')) {
                                            $detail = $body->Fault->detail;
                                            $detailCode = $body->Fault->detail->CommonExceptionElement->code;
                                            $detailMessage = $body->Fault->detail->CommonExceptionElement->message;
                                    }

                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;
                                    $faultCode = (array)$faultCode;
                                    $faultString = (array)$faultString;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'fault_code'    => $faultCode[0],
                                        'fault_string'  => $faultString[0],
                                        'imsi' => $imsi
                                    );

                                    
                                }else if(property_exists($body, 'UpdateRsElement')){

                                    $detailCode = $body->UpdateRsElement->status;
                                    $detailMessage = $body->UpdateRsElement->message;
                                    
                                    $detailCode = (array)$detailCode;
                                    $detailMessage = (array)$detailMessage;

                                    $result = array(
                                        'code'          => $detailCode[0],
                                        'message'       => $detailMessage[0],
                                        'imsi' => $imsi
                                    );
                                }
                                return $result;
                            }
                    }
                }
        
                curl_close($ch);

            }catch (Exception $e) {
                var_dump($e);
                //      error_log("curlCallOnError Exception. Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            }
        }

        public function curlCallOnError($url, $action, $xmlData , $imsi) {
            // The HTTP headers for the request (based on image above)
            $headers = array(
                    'Content-Type: text/xml; charset=utf-8',
                    'Content-Length: ' . strlen($xmlData),
                    'SOAPAction: ' . $action
            );
        
            try{
        
                $xmlData = str_replace("SOAP-ENV", "soapenv", $xmlData);
    
                $xmlData = str_replace("ns1", "bro", $xmlData);
    
                // Build the cURL session
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                //var_dump(htmlspecialchars($xmlData));
    
                $result = curl_exec($ch);
    
                if ($result === false) {
                        curl_close($ch);
                        return false;
                }
    
                $response = curl_multi_getcontent($ch);
        
                if (!empty($response)) {
                        $clean_xml = str_ireplace(['soapenv:', 'bro:'], '', $response);
                        $xmlObj = simplexml_load_string($clean_xml);
    
                        if (!empty($xmlObj) && is_object($xmlObj)) {
                                //error_log(print_r($xmlObj, true));
    
                            if (property_exists($xmlObj, 'Body')) {
                                $body = $xmlObj->Body;

                                if (property_exists($body, 'Fault')) {
                                    $faultCode = $body->Fault->faultcode;
                                    $faultString = $body->Fault->faultstring;
                                    $detailCode = $detailMessage = null;

                                    if (property_exists($body->Fault, 'detail')) {
                                            $detail = $body->Fault->detail;
                                            $detailCode = $body->Fault->detail->CommonExceptionElement->code;
                                            $detailMessage = $body->Fault->detail->CommonExceptionElement->message;
                                    }

                                    $result = array(
                                        'code'          => $detailCode,
                                        'message'       => $detailMessage,
                                        'fault_code'    => $faultCode,
                                        'fault_string'  => $faultString,
                                        'imsi' => $imsi
                                    );

                                    
                                }else if(property_exists($body, 'DeactivateRsElement')){

                                    $detailCode = $body->DeactivateRsElement->status;
                                    $detailMessage = $body->DeactivateRsElement->message;

                                    $result = array(
                                        'code'          => $detailCode,
                                        'message'       => $detailMessage,
                                        'imsi' => $imsi
                                    );
                                }

                                return $result;
                            }
                    }
                }
        
                curl_close($ch);
        
            } catch (Exception $e) {
            var_dump($e);
            //      error_log("curlCallOnError Exception. Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            }
        }

        # ----------------------------------------------------------------------
        # Retorna imsis validas o vacio en caso contrario.
        public static function imsiColombia( $string='' ){
            $string = !empty($string) ? trim( $string ) : '';       // Limpiar espacios en los extremos.
            $string = strip_tags( $string );                        // Retirar Html.
            $string = preg_replace( "/[^0-9]/", "", $string );      // Númericos permitidos.
            $string = preg_replace ( '/\s\s+/', ' ', $string );     // Elemina espacios prolongados.
            $findme = '732176000';
            $pos = strpos($string, $findme);
            if ( is_numeric($string) and strlen($string)==15 and $pos!==false) {
                return $string;
            }
            return '';
        }
    }


?>
