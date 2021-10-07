<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Http\Controllers\AppfpdfController;

    /**
     * Esta clase obtiene los servicios básicos de consulta en el API de Troubleshooting.
     * @Autor <achavezb@directvla.com.co>
     */
    class TroubleshootingController extends Controller
    {
        protected $controller = '';
        protected $method = '';
        protected $video = '';
        protected $country = 'Colombia';
        protected $urlWsdl = '';
        protected $urlWsdlReadAr = '';
        protected $arrayImsis = array();    
        protected $result = array();
        protected $errors = array();

        public function __construct(){
            if(!\App::runningInConsole()){
                $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
                $parts = explode('@', $controller);
                $this->controller = substr($parts[0], 0, -10);
                $this->method = $parts[1];
                if ( file_exists('videos/'.$this->controller.'_'.$this->method.'.mp4') ) {
                    $this->video = url('videos/'.$this->controller.'_'.$this->method.'.mp4');
                }
                $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
                $this->urlWsdlReadAr = config('appross.provisioning_wsdl_arg_read', '');
            }        
        }
        
        public function factory_reset(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();
            $subscriber_identity = $request->input("imsi");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.factory_reset', $params);

            return response()->json([
                'data' => $result 
            ]); 
        }

        public function software_reboot(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();
            $subscriber_identity = $request->input("imsi");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.software_reboot', $params);

            return response()->json([
                'data' => $result 
            ]); 

        }

        public function setSSID24(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();

            $subscriber_identity = $request->input("imsi");
            $ssid = $request->input("texto");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "ssid" => $ssid ,"technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.set_ssid2_4', $params);

            return response()->json([
                'data' => $result 
            ]); 
        }

        public function setSSID5(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();

            $subscriber_identity = $request->input("imsi");
            $ssid = $request->input("texto");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "ssid" => $ssid ,"technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.set_ssid5', $params);

            return response()->json([
                'data' => $result 
            ]); 
        }

        public function setpassword24(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();

            $subscriber_identity = $request->input("imsi");
            $ssid = $request->input("texto");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "ssid" => $ssid ,"technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.set_password2_4', $params);

            return response()->json([
                'data' => $result 
            ]); 
        }

        public function setpassword5(Request $request){
            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();

            $subscriber_identity = $request->input("subscriber_identity");
            $ssid = $request->input("texto");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "ssid" => $ssid ,"technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.set_password5', $params);

            return response()->json([
                'data' => $result 
            ]); 
        }


        //SSID
        public function getSSID(Request $request){

            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";

            $inputs = $request->All();

            $subscriber_identity = $request->input("subscriber_identity");

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $params = array("subscriber_identity"=>$subscriber_identity, "technology"=>"?");
            $result = $clientSoapWsdl->call('Troubleshooting.get_ssid', $params);
            $ssid_errors = 0;
            foreach ($result as $key => $value) {
                                
                if ($key == "ssid_2.4ghz"){
                    $key = "ssid_24ghz";
                }
                $resultado_final["ssid_".$key] = $value=='' ? '' :@utf8_encode($value);
                
               
                if ($key=='faultcode'){
                    $ssid_errors++;
                }

                $resultado_final["ssid_errors"] = $ssid_errors;
            }

            $response['total'] = $resultado_final;

            return response()->json([
                'data' => $response
            ]); 

        }

        //Fota Col
        public function getServicesRenderFota(Request $request){
            try{
                
                if( !$request->isMethod('post') ) {
                    throw new \Exception(trans('messages.000042'), 9999);
                }
    
                $inputs = $request->All();
               
    
                if (  $inputs['render']=="pdf" ){
                    $result = $this->fotaColombia($request);
                    if($result){
                        if ( !$result ) {
                            throw new \Exception(trans('messages.000166'), 9999);
                        }
                    
                        $this->exportPdf( $this->result );
                       
                    }
                }
                if ( $inputs['render']==='html' ){
                    $result = $this->fotaColombia($request);
                    if($result){
                        if ( !$result ) {
                            throw new \Exception(trans('messages.000166'), 9999);
                        }
                        return response()->json([
                            'count' => count($this->arrayImsis),
                            'datahtml' => $this->result
                        ]);  
                    } 
                }

                    
            }catch(\Exception $e){
                $msj= $e->getMessage();

                return response()->json([
                    'response' => false,
                    'error' => $msj,
                    'render' =>$this->result 
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }

        //Fota Colombia
        public function fotaColombia(Request $request){

            $this->urlWsdl = "http://10.165.1.6/BroadbandDev/Colombia/BOG/LTE/?wsdl";
            $inputs = $request->All();
            extract($inputs);

            $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
            if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }else if( $load_type=='individual' and !empty($subscriber_identity) ){
                $arrayData = $objImsis->extractImsis( $subscriber_identity );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }

            $resultado_final = array();

            if ( empty($this->arrayImsis) ) {
                throw new \Exception( trans('messages.000045'), 9999); 
            }

            // if ( count($this->arrayImsis)>50) {
            //     throw new \Exception( trans('messages.000067').' ('.count($this->arrayImsis).')', 9999);
            // }

            $if_conection = @get_headers($this->urlWsdl);
            if ( !is_array($if_conection) ){
                throw new \Exception( trans('messages.000043').' Troubleshooting', 9999);
            }

            $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
            $clientSoapWsdl->soap_defencoding = 'UTF-8';
            $clientSoapWsdl->decode_utf8 = FALSE;
            $error = $clientSoapWsdl->getError();

            $contador = 0;
            foreach ($this->arrayImsis as $key => $imsi) {
                $errores = 0;
                // -----------------------------------------------------------
                
                $resultado_final["fota_fota_status"] = "";
                $resultado_final["fota_fota_timer_trigger"] = "";
                $resultado_final["fota_url_fota"] = "";
                $resultado_final["fota_errors"] = "";
                $resultado_final["fota_faultcode"] = "";
                $resultado_final["fota_faultstring"] = "";

                $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                $result = $clientSoapWsdl->call('Troubleshooting.get_fota', $params);

                if (!empty($result)) {
                    $error_fota = 0;
                    foreach ($result as $key => $value) {
                                

                        $resultado_final["fota_".$key] = $value=='' ? '' :@utf8_encode($value);
                        
                        
                        if ($key=='faultcode' or ($key=='internet_access' and $value!=1) ) {
                            $errores++;
                        }
                        if ($key=='faultcode'){
                            $error_fota++;
                        }
                        $resultado_final["imsi"] = $imsi;
                        $resultado_final["fota_errors"] = $error_fota;
                    }
                }

                $response[$contador]['total'] = $resultado_final;

                $contador++;

            }

            $this->result = $response;
            return true;

        }

        //Render Colombia
        public function getServicesRender(Request $request){
            try{
                
                if( !$request->isMethod('post') ) {
                    throw new \Exception(trans('messages.000042'), 9999);
                }
    
                $inputs = $request->All();
               
    
                if (  $inputs['render']=="pdf" ){
                    $result = $this->getTroubleshootingCol($request);
                    if($result){
                        if ( !$result ) {
                            throw new \Exception(trans('messages.000166'), 9999);
                        }
                    
                        $this->exportPdf( $this->result );
                       
                    }
                }
                if ( $inputs['render']==='html' ){
                    $result = $this->getTroubleshooting($request);
                    if($result){
                        if ( !$result ) {
                            throw new \Exception(trans('messages.000166'), 9999);
                        }
                        return response()->json([
                            'count' => count($this->arrayImsis),
                            'datahtml' => $this->result
                        ]);  
                    } 
                }

                    
            }catch(\Exception $e){
                $msj= $e->getMessage();

                return response()->json([
                    'response' => false,
                    'error' => $msj,
                    'render' =>$this->result 
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }

        //Troubleshooting Colombia
        public function getTroubleshooting( Request $request ){
            $inputs = $request->All();

            extract($inputs);

            if ( $read == "false" && $get_params == "false" && $massive_fail == "false" && $get_location == "false" ) {
                throw new \Exception( trans('messages.000165'), 9999);
            }
            if ( !isset($load_type) or empty($load_type) ){
                throw new \Exception( trans('messages.000057'), 9999);
            } 

    
            $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
            if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }else if( $load_type=='individual' and !empty($subscriber_identity) ){
                $arrayData = $objImsis->extractImsis( $subscriber_identity );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }
            
            $resultado_final = array();

            if ( isset($read) or isset($get_params) or isset($massive_fail) or isset($get_location) ) {
                
                if ( empty($this->arrayImsis) ) {
                    throw new \Exception( trans('messages.000045'), 9999); 
                }
    
                // if ( count($this->arrayImsis)>50) {
                //     throw new \Exception( trans('messages.000067').' ('.count($this->arrayImsis).')', 9999);
                // }
    
                $if_conection = @get_headers($this->urlWsdl);
                if ( !is_array($if_conection) ){
                    throw new \Exception( trans('messages.000043').' Troubleshooting', 9999);
                }
    
                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                $clientSoapWsdl->decode_utf8 = FALSE;
                $error = $clientSoapWsdl->getError();
                if ($error) {
                    throw new \Exception( 'Troubleshooting error: '.$error, 9999);
                }
                $contador = 0;
                foreach ($this->arrayImsis as $key => $imsi) {
                    $errores = 0;
                    // -----------------------------------------------------------
                    if ( $read == "true" ) {
                        $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                        $result = $clientSoapWsdl->call('Provisioning.read', $params);
                        
                        $resultado_final["read_empty"] = false;
                        if (!empty($result)) {
                            $error_read = 0;
                            foreach ($result as $key => $value) {
                                
                                if ($key=='internet_access') {
                                    $resultado_final["read_".$key] = ($value==1) ? 'SI' : 'NO';
                                }else{
                                    $resultado_final["read_".$key] = $value=='' ? '' :@utf8_encode($value);
                                }
                                
                                if ($key=='faultcode' or ($key=='internet_access' and $value!=1) ) {
                                    $errores++;
                                    
                                }
                                if ($key=='faultcode'){
                                    $error_read++;
                                }
                                $resultado_final["read_errors"] = $error_read;
                            }
                        }else{
                            $errores++;
                            $resultado_final["read_empty"] = true;
                        }

                        // array_push($resultado_final, $resultado);
                        unset($result);
                    }
                    // -----------------------------------------------------------
                    if ( $get_params  == "true" ) {
                        $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                        $result = $clientSoapWsdl->call('Troubleshooting.get_params', $params);
                        $resultado_final["get_params_empty"] = false;
                        $resultado_final["get_params_bs_name"] = "";
                        $resultado_final["get_params_bsid"] = "";
                        $resultado_final["get_params_cinr"] = "";
                        $resultado_final["get_params_empty"] = "";
                        $resultado_final["get_params_errors"] = "";
                        $resultado_final["get_params_faultcode"] = "";
                        $resultado_final["get_params_faultstring"] = "";
                        $resultado_final["get_params_firmware_chipset_version"] = "";
                        $resultado_final["get_params_firmware_version"] = "";
                        $resultado_final["get_params_high_ocupancy"] = "";
                        $resultado_final["get_params_ip_address"] = "";
                        $resultado_final["get_params_ping_rtt"] = "";
                        $resultado_final["get_params_rsrp"] = "";
                        $resultado_final["get_params_rsrq"] = "";
                        $resultado_final["get_params_tx_power"] = "";
                        $resultado_final["get_params_model"] = "";
                        if (!empty($result)) {
                            $error_getp = 0;
                            foreach ($result as $key => $value) {
                                $resultado_final["get_params_".$key] = "";
                                $resultado_final["get_params_".$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                    $error_getp++;
                                }
                            }
                            $resultado_final["get_params_errors"] = $error_getp;
                        }else{
                            $errores++;
                            $resultado_final["get_params_empty"] = true;
                        }
                    }
                    // -----------------------------------------------------------
                    if ( $get_location == "true" ) {
                        $params = array("subscriber_identity"=>$imsi);
                        $result = $clientSoapWsdl->call('Troubleshooting.get_location', $params);
                        $resultadoGetLoc = array();
                        $resultado_final["get_location_empty"] = false;
                        if (!empty($result)) {
                            $error_getl = 0;
                            foreach ($result as $key => $value) {
                                $resultado_final["get_location_".$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                    $error_getl++;
                                }
                                if ($key=='findingProcess') {
                                    unset($result['findingProcess']);
                                }
                            }
                            $resultado_final["get_location_errors"] = $error_getl;
                        }else{
                            $errores++;
                            $resultado_final["get_location_empty"] = true;
                        }
                        unset($result);
                    } 
                    // -----------------------------------------------------------
                    if ( $massive_fail == "true" ) {
                        $params = array("imsi"=>$imsi, "canal"=>"?");
                        $result = $clientSoapWsdl->call('Troubleshooting.massiveFail', $params);
                        if (!empty($result)) {
                            $error_mass = 0;
                            foreach ($result as $key => $value) {
                                $resultado_final["massive_fail_".$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                    $error_mass++;
                                }
                            }
                            $resultado_final["massive_fail_errors"] = $error_mass;
                        }else{
                            $errores++;
                            $resultado_final["massive_fail_empty"] = $error_mass;
                        }

                        unset($result);
                    }

                    $response[$contador]['errors']=$errores;
                    $response[$contador]['imsi']= $imsi;
                    $response[$contador]['total'] = $resultado_final;

                    $contador++;
                }
                $this->result = $response;
                return true;
            }else{
                throw new \Exception( trans('messages.000047'), 9999);                    
            }
    
            return false;
        }

       
        public function getServicesRenderAr(Request $request){
            try{
                
                if( !$request->isMethod('post') ) {
                    throw new \Exception(trans('messages.000042'), 9999);
                }
    
                $inputs = $request->All();
                $result = $this->getTroubleshootingArgentina($request);
                if ( !$result ) {
                    throw new \Exception(trans('messages.000166'), 9999);
                }
    
                if ( $result and $inputs['render']=='pdf' ){
                    $this->exportPdf( $result );
                    // return response()->json([
                    //     'count' => count($this->arrayImsis),
                    //     'datahtml' => $this->result
                    // ]);  
                }
                if ( $result and $inputs['render']==='html' ){
                    return response()->json([
                        'count' => count($this->arrayImsis),
                        'datahtml' => $this->result
                    ]);  
                }
                throw new \Exception(trans('messages.000019'), 9999);
    
            }catch(\Exception $e){
                $msj= $e->getMessage();

                return response()->json([
                    'response' => false,
                    'error' => $msj,
                    'render' =>$inputs['render']
                ])->header("Access-Control-Allow-Origin",  "*");
            }

        }

        //Troubleshooting Argentina
        public function getTroubleshootingArgentina( Request $request ){
            $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
            $parts = explode('@', $controller);
            $this->controller = substr($parts[0], 0, -10);
            $this->method = $parts[1];
            $this->urlWsdl = config('appross.provisioning_wsdl_arg', '');

            $inputs = $request->All();
            extract($inputs);

            if ( $read == "false" && $get_params == "false") {
                throw new \Exception( trans('messages.000165'), 9999);
            }
            if ( !isset($load_type) or empty($load_type) ){
                throw new \Exception( trans('messages.000057'), 9999);
            } 

            if(empty($technology)){
                throw new \Exception( trans('Debe seleccionar una tecnología'), 9999);
            }

            $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
            if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }else if( $load_type=='individual' and !empty($subscriber_identity) ){
                $arrayData = $objImsis->extractImsis( $subscriber_identity );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }
            
            $resultado_final = array();

            if ( isset($read) or isset($get_params) ) {
                
                if ( empty($this->arrayImsis) ) {
                    throw new \Exception( trans('messages.000045'), 9999); 
                }
    
         
                $if_conection = @get_headers($this->urlWsdl);
                if ( !is_array($if_conection) ){
                    throw new \Exception( trans('messages.000043').' Troubleshooting', 9999);
                }
                
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

                    if ($error) {
                        throw new \Exception( 'Troubleshooting error: '.$error, 9999);
                    }
                    $contador = 0;
                    foreach ($this->arrayImsis as $key => $imsi) {
                        $errores = 0;
                        // -----------------------------------------------------------
                        if ( $read == "true" ) {

                            $resultado_final["read_brand"] = false;
                            $resultado_final["read_bsid"] = "";
                            $resultado_final["read_cinr"] = "";
                            $resultado_final["read_empty"] = "";
                            $resultado_final["read_errors"] = "";
                            $resultado_final["read_firmware_version"] = "";
                            $resultado_final["read_internet_access"] = "";
                            $resultado_final["read_ip_address"] = "";
                            $resultado_final["read_model"] = "";
                            $resultado_final["read_ping_rtt"] = "";
                            $resultado_final["read_profile"] = "";
                            $resultado_final["read_rsrp"] = "";

                            if ($technology == '5G'){
                                $params = array("subscriber_identity" => mb_strtoupper($imsi), "technology" => mb_strtoupper($technology) );
                                $result = $clientSoapWsdl5G->call('Provisioning.read', $params);
                            }else{
                                $params = array("subscriber_identity" => mb_strtoupper($imsi), "technology" => mb_strtoupper($technology) );
                                $result = $clientSoapWsdl->call('Provisioning.read', $params);
                            }
                            

                            $resultado_final["read_empty"] = false;

                            if (!empty($result)) {
                                $error_read = 0;
                                foreach ($result as $key => $value) {
                                    if ($key=='internet_access') {
                                        $resultado_final["read_".$key] = ($value==1) ? 'SI' : 'NO';
                                    }else{
                                        $resultado_final["read_".$key] = @utf8_encode($value);
                                    }
                                    
                                    if ($key=='faultcode' or ($key=='internet_access' and $value!=1) ) {
                                        $errores++;
                                        
                                    }
                                    if ($key=='faultcode'){
                                        $error_read++;
                                    }
                                    $resultado_final["read_errors"] = $error_read;
                                }
                            }else{
                                $errores++;
                                $resultado_final["read_empty"] = true;
                            }

                            unset($result);
                        }

                    // -----------------------------------------------------------
                    if ( $get_params == "true" ) {

                        if($technology == "LTE/WIMAX"){
                            $resultado_final["get_params_brand"] = false;
                            $resultado_final["get_params_bsid"] = "";
                            $resultado_final["get_params_cinr"] = "";
                            $resultado_final["get_params_empty"] = "";
                            $resultado_final["get_params_epc_location"] = "";
                            $resultado_final["get_params_errors"] = "";
                            $resultado_final["get_params_firmware_version"] = "";
                            $resultado_final["get_params_ip_address"] = "";
                            $resultado_final["get_params_model"] = "";
                            $resultado_final["get_params_ping_rtt"] = "";
                            $resultado_final["get_params_rsrp"] = "";
                            $resultado_final["get_params_rsrq"] = "";
                            $resultado_final["get_params_rssi"] = "";
                        }
                        if($technology == "FTTH"){
                            $resultado_final["get_params_config_state"] = "";
                            $resultado_final["get_params_distance"] = "";
                            $resultado_final["get_params_empty"] = "";
                            $resultado_final["get_params_errors"] = "";
                            
                            $resultado_final["get_params_olt_location"] = "";
                            $resultado_final["get_params_olt_port"] = "";
                            $resultado_final["get_params_olt_rx_power"] = "";
                            $resultado_final["get_params_olt_slot"] = "";
                            $resultado_final["get_params_ont_rx_power"] = "";
                            $resultado_final["get_params_onu_ID"] = "";
                            $resultado_final["get_params_onu_omci_state"] = "";
                            $resultado_final["get_params_oper_status"] = "";
                            $resultado_final["get_params_technology"] = "";
                        }

                        $resultado_final["get_params_faultcode"] = "";
                        $resultado_final["get_params_faultstring"] = "";

                        


                        $params = array( "subscriber_identity" => mb_strtoupper($imsi), "technology" => mb_strtoupper($technology) );
                        $result = $clientSoapWsdl->call('Troubleshooting.get_params', $params);
                        $resultado_final["get_params_empty"] = false;
                        if (!empty($result)) {
                            $error_getp = 0;
                            foreach ($result as $key => $value) {
                                $resultado_final["get_params_".$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                    $error_getp++;
                                }
                            }
                            $resultado_final["get_params_errors"] = $error_getp;
                        }else{
                            $errores++;
                            $resultado_final["get_params_empty"] = true;
                        }

                        unset($result);
                    }
    
                    // $response[$imsi]['errors']=0;
                    // if ($errores>0) {
                    //     $response[$imsi]['errors']=$errores;
                    // }
                    $response[$contador]['errors']=$errores;
                    $response[$contador]['imsi']= $imsi;
                    $response[$contador]['total'] = $resultado_final;

                    $contador++;

                }
                $this->result = $response;
                return true;
            }else{
                throw new \Exception( trans('messages.000047'), 9999);                    
            }
    
            return false;
        } 
        
         # -----------------------------------------------------------------------------
         public function exportPdf( $datapdf=array() ){
            try{
                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename="myPDF.pdf');

                // Send Headers: Prevent Caching of File
                header('Cache-Control: private');
                header('Pragma: private');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/force-download');

                $headers = array(
                    'Content-Type'=> 'application/pdf'
                  );

                http_response_code(200);
                
                ob_start();
                $pdf = new AppfpdfController();// De la forma usando inclusión de clase en la cabecera.
                // $pdf = new \App\Http\Controllers\AppfpdfController;
                $pdf->AddPage('P', 'Letter', 0);
                $pdf->AliasNbPages();
                $pdf->SetMargins(15, 20, 15);
                $pdf->SetAutoPageBreak(true, 20); 
                $pdf->SetTitle( "ROSS - Regional Operations Support System" , true);
                $pdf->SetAuthor('Ing Alfonso Chávez', true);
                $pdf->SetSubject('Troubleshooting', true);
                $pdf->SetCreator('CREADOR', true);

                $pdf->SetTextColor( 0, 16, 41 );
                $pdf->SetFillColor(206, 208, 214);

                $subtitulo="Informe Troubleshooting";
                $pdf->SetFont('Arial', 'I', 18);
                $pdf->MultiCell( 196, 8, $subtitulo, 0, 'L', false);
                $pdf->Ln();

                $obj = new \App\Http\Controllers\ResourcesController;
                $generate = $obj->getStringDate().' '.date("H:i:s");
                $texto = trans('messages.000169');
                $texto = str_replace("xxxx-xx-xx", $generate, $texto);
                $pdf->SetFont('Arial', '', 10);        
                $y = round( $pdf->GetY() );
                $pdf->MultiCell( 188, 5, utf8_decode($texto), 0, 'J', false);
                $pdf->Ln();

                foreach ($datapdf as $imsi => $data) {
                    $pdf->SetFont('Arial', 'B', 12);
                    if ($data['errors']>0) {
                        $pdf->SetTextColor( 189, 31, 52 );
                        $pdf->SetFillColor( 245, 183, 177 );// Rojo
                    }else{
                        $pdf->SetTextColor( 20, 90, 50 ); 
                        $pdf->SetFillColor( 125, 206, 160 );// Verde
                    }
                    $pdf->MultiCell( 188, 10, 'IMSI: '.utf8_decode($imsi), 0, 'L', true);

                    foreach ($data as $group => $info) {
                        if (!empty($info) and $group!='errors') {
                            $pdf->SetFont('Arial', 'B', 10);
                            $pdf->SetTextColor( 82, 86, 89 );
                            $pdf->SetFillColor( 228, 235, 243 );
                            $msj = trans('messages.000167').': Provisioning '.ucwords(str_replace("_", " ", $group));
                            $pdf->MultiCell( 188, 7, utf8_decode($msj), 0, 'L', true);

                            foreach ($info as $key => $value) {
                                $pdf->SetFont('Arial', '', 10);
                                $pdf->SetTextColor( 82, 86, 89 );
                                $pdf->SetFillColor( 228, 235, 243 );
                                if ($key=='faultstring') {
                                    $pdf->SetFont('Arial', 'B', 10);
                                    $pdf->SetTextColor( 189, 31, 52 );
                                }
                                $pdf->MultiCell( 188, 6, utf8_decode($key.':  '.$value), 0, 'L', true);
                            }
                        }
                    }
                    $pdf->Ln(10);
                }
            
                $name_doc = 'ROSS'.date("YmdHi").'.pdf';
                $pdf->Output($name_doc);                
                
            }catch(\Exception $e){
                $msj= $e->getMessage();

                return response()->json([
                    'response' => false,
                    'error' => $msj
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }

        # -----------------------------------------------------------------------------
        public function getTroubleshootingCol( Request $request ){

            $inputs = $request->All();
            if ( empty($inputs) ) {
                throw new \Exception( trans('messages.000165'), 9999);
            }

            extract($inputs);
            if ( !isset($load_type) or empty($load_type) ){
                throw new \Exception( trans('messages.000057'), 9999);
            } 

            $objImsis = new \App\Http\Controllers\Colombia\ImsiColombiaController;
            if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }else if( $load_type=='individual' and !empty($subscriber_identity) ){
                $arrayData = $objImsis->extractImsis( $subscriber_identity );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
            }

            if ( isset($read) or isset($get_params) or isset($massive_fail) or isset($get_location) ) {
                
                if ( empty($this->arrayImsis) ) {
                    throw new \Exception( trans('messages.000045'), 9999); 
                }

                

                $if_conection = @get_headers($this->urlWsdl);
                if ( !is_array($if_conection) ){
                    throw new \Exception( trans('messages.000043').' Troubleshooting', 9999);
                }

                $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                $clientSoapWsdl->soap_defencoding = 'UTF-8';
                $clientSoapWsdl->decode_utf8 = FALSE;
                $error = $clientSoapWsdl->getError();
                if ($error) {
                    throw new \Exception( 'Troubleshooting error: '.$error, 9999);
                }

                foreach ($this->arrayImsis as $key => $imsi) {
                    $errores = 0;
                    // -----------------------------------------------------------
                    if ( isset($read) ) {
                        $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                        $result = $clientSoapWsdl->call('Provisioning.read', $params);
                        if (!empty($result)) {
                            foreach ($result as $key => $value) {
                                $result[$key] = @utf8_encode($value);
                                if ($key=='internet_access') {
                                    $result[$key] = ($value==1) ? 'SI' : 'NO';
                                }
                                if ($key=='faultcode' or ($key=='internet_access' and $value!=1) ) {
                                    $errores++;
                                }
                            }
                        }else{
                            $errores++;
                        }
                        $response[$imsi]['read']=$result;
                        unset($result);
                    }
                    // -----------------------------------------------------------
                    if ( isset($get_params) ) {
                        $params = array("subscriber_identity"=>$imsi, "technology"=>"?");
                        $result = $clientSoapWsdl->call('Troubleshooting.get_params', $params);
                        if (!empty($result)) {
                            foreach ($result as $key => $value) {
                                $result[$key] = empty($value) ? '' :@utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                }
                            }
                        }else{
                            $errores++;
                        }
                        $response[$imsi]['get_params']=$result;
                        unset($result);
                    }
                    // -----------------------------------------------------------
                    if ( isset($get_location) ) {
                        $params = array("subscriber_identity"=>$imsi);
                        $result = $clientSoapWsdl->call('Troubleshooting.get_location', $params);
                        if (!empty($result)) {
                            foreach ($result as $key => $value) {
                                $result[$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                }
                                if ($key=='findingProcess') {
                                    unset($result['findingProcess']);
                                }
                            }
                        }else{
                            $errores++;
                        }
                        $response[$imsi]['get_location']=$result;
                        unset($result);
                    } 
                    // -----------------------------------------------------------
                    if ( isset($massive_fail) ) {
                        $params = array("imsi"=>$imsi, "canal"=>"?");
                        $result = $clientSoapWsdl->call('Troubleshooting.massiveFail', $params);
                        if (!empty($result)) {
                            foreach ($result as $key => $value) {
                                $result[$key] = @utf8_encode($value);
                                if ($key=='faultcode') {
                                    $errores++;
                                }
                            }
                        }else{
                            $errores++;
                        }
                        $response[$imsi]['massive_fail']=$result;
                        unset($result);
                    }
                    $response[$imsi]['errors']=0;
                    if ($errores>0) {
                        $response[$imsi]['errors']=$errores;
                    }
                }
                $this->result = $response;
                return true;
            }else{
                throw new \Exception( trans('messages.000047'), 9999);                    
            }

            return false;
        }

        }

    

?>