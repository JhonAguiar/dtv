<?php

namespace App\Http\Controllers\Argentina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    protected $video = '';
    protected $country = 'Argentina';
    protected $urlWsdl = '';
    protected $arrayImsis = array();
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
            $this->urlWsdl = config('appross.provisioning_wsdl_arg', '');
        }
    }

    # -----------------------------------------------------------------------------
	public function create(){
        if(view()->exists('argentina.provisioning.create')) {
            $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
            if ( file_exists('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4');
            }
            return view('argentina.provisioning.create', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000033'),// Crear perfil de navegación
                    'video' => $this->video,
                    'country' => $this->country,
                ],
                'technologies' => $objImsis->getTechnologiesActive(),
            ]);   
        }else{
            return view('errors.404', []);
        }		
	}

    # -----------------------------------------------------------------------------
    public function suspendUnsuspend(){
        if(view()->exists('argentina.provisioning.suspend_unsuspend')) {
            if ( file_exists('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4');
            }
            return view('argentina.provisioning.suspend_unsuspend', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000028'),
                    'video' => $this->video,
                    'country' => $this->country,
                ],
            ]);   
        }else{
            return view('errors.404', []);
        }
    }

    # -----------------------------------------------------------------------------
    public function edit(){
        if(view()->exists('argentina.provisioning.edit')) {
            $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
            if ( file_exists('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4');
            }
            return view('argentina.provisioning.edit', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000036'),
                    'video' => $this->video,
                    'country' => $this->country,
                ],
                'technologies' => $objImsis->getTechnologiesActive(),
            ]);   
        }else{
            return view('errors.404', []);
        }
    }

    # -----------------------------------------------------------------------------
    public function delete(){
        if(view()->exists('argentina.provisioning.delete')) {
            $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
            if ( file_exists('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/Argentina_'.$this->controller.'_'.$this->method.'.mp4');
            }
            return view('argentina.provisioning.delete', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000030'),
                    'video' => $this->video,
                    'country' => $this->country,
                ],
                'technologies' => $objImsis->getTechnologiesActive(),
            ]);   
        }else{
            return view('errors.404', []);
        }
    }

    # -----------------------------------------------------------------------------
    public function store( Request $request ){
        set_time_limit(180);
        if( $request->isMethod('post') ) {
            try{
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
                            if (!$error) {
                                foreach ($this->arrayImsis as $key => $imsi) {
                                    $params = array(
                                        "subscriber_identity"   => mb_strtoupper($imsi), 
                                        "profile"               => mb_strtoupper($profile),
                                        "force_provisioning"    => "false",
                                        "technology"            => mb_strtoupper($technology)  
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
                    array_push($this->errors, trans('messages.000045'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
        }else{
            array_push($this->errors, trans('messages.000042'));
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    # -----------------------------------------------------------------------------
    public function suspendUnsuspendProcess( Request $request ){
        set_time_limit(180);
        if( $request->isMethod('post') ) {
            try{
                $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
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
                                    $params = array(
                                        "subscriber_identity" => mb_strtoupper($imsi), 
                                        "technology" => mb_strtoupper($technology)
                                    );
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
                    array_push($this->errors, trans('messages.000045'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
        }else{
            array_push($this->errors, trans('messages.000042'));
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    # -----------------------------------------------------------------------------
    public function update( Request $request ){
        sleep(8);
        set_time_limit(180);
        if( $request->isMethod('post') ) {
            try{
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
                            if (!$error) {
                                foreach ($this->arrayImsis as $key => $imsi) {
                                    $params = array(
                                        "subscriber_identity" => mb_strtoupper($imsi), 
                                        "profile" => mb_strtoupper($profile),
                                        "technology" => mb_strtoupper($technology)
                                    );
                                    $response = $clientSoapWsdl->call('Provisioning.update', $params);
                                    if ( $response==1 and !isset($response['faultcode']) ) {
                                        $info = 'Provisioning.update ok, Profile: '.$profile;
                                        $code = 0;
                                    }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                        $info = 'Provisioning.update error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                        $code = $response['faultcode'];
                                    }else{
                                        $info = 'Provisioning.update error: -1 '.trans('messages.000019');
                                        $code = -1;
                                    }
                                    $this->result[$key]['imsi'] = $imsi;
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
                    array_push($this->errors, trans('messages.000045'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
        }else{
            array_push($this->errors, trans('messages.000042'));
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }

    public function destroy( Request $request ){
        set_time_limit(180);
        if( $request->isMethod('post') ) {
            try{
                $objImsis = new \App\Http\Controllers\Argentina\ImsiArgentinaController;
                $arrayData = $objImsis->getArrayImsis( $request );
                $this->arrayImsis = $arrayData['imsis'];
                $this->errors = array_merge($arrayData['errors']);
                if ( !empty($this->arrayImsis) and empty($this->errors) ) {
                    extract( $request->All() );
                    $if_conection = @get_headers($this->urlWsdl);
                    if ( is_array($if_conection) ){
                        $clientSoapWsdl = new \nusoap_client( $this->urlWsdl, true );
                        $clientSoapWsdl->soap_defencoding = 'UTF-8';
                        $clientSoapWsdl->decode_utf8 = FALSE;
                        $error = $clientSoapWsdl->getError();                        
                        if (!$error) {
                            foreach ($this->arrayImsis as $key => $imsi) {
                                $params = array( 
                                    "subscriber_identity" => mb_strtoupper($imsi), 
                                    "technology" => mb_strtoupper($technology) 
                                );
                                $response = $clientSoapWsdl->call('Provisioning.delete', $params);
                                if ( $response==1 and !isset($response['faultcode']) ) {
                                    $info = 'Provisioning.delete ok. ';
                                    $code = 0;
                                }elseif( isset($response['faultcode']) and !empty($response['faultcode']) ){
                                    $info = 'Provisioning.delete error: '.$response['faultcode'].' '.@utf8_encode($response['faultstring']);
                                    $code = $response['faultcode'];
                                }else{
                                    $info = 'Provisioning.delete error: -1 '.trans('messages.000019');
                                    $code = -1;
                                }
                                $this->result[$key]['imsi'] = $imsi;
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
                    array_push($this->errors, trans('messages.000042'));
                }
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
        }else{
            array_push($this->errors, trans('messages.000042'));
        }
        return response()->json([
            'result' => $this->result,
            'errors' => $this->errors
        ]);
    }


}
