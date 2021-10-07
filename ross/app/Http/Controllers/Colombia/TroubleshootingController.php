<?php

namespace App\Http\Controllers\Colombia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
#use App\Http\Controllers\AppfpdfController;

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
    protected $arrayImsis = array();    
    protected $result = array();
    protected $errors = array();

    # -----------------------------------------------------------------------------
    public function __construct(){
        $this->middleware('auth');
        if(!\App::runningInConsole()){
            $controller = class_basename( \Route::getCurrentRoute()->getActionName() );
            $parts = explode('@', $controller);
            $this->controller = substr($parts[0], 0, -10);
            $this->method = $parts[1];
            if ( file_exists('videos/'.$this->controller.'_'.$this->method.'.mp4') ) {
                $this->video = url('videos/'.$this->controller.'_'.$this->method.'.mp4');
            }
            $this->urlWsdl = config('appross.provisioning_wsdl_col', '');
        }        
    }

    # -----------------------------------------------------------------------------
    public function getServices($value=''){
        if(view()->exists('colombia.troubleshooting.get_services')) {
            return view('colombia.troubleshooting.get_services', [
                'headers' => [
                    'controller' => $this->controller,
                    'method' => $this->method,
                    'title' => trans('messages.000029'),// Consultas troubleshooting
                    'video' => $this->video,
                    'country' => $this->country,                    
                ]
            ]);   
        } 
    }

    # -----------------------------------------------------------------------------
    public function getTroubleshooting( Request $request ){
        set_time_limit(180);
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

            if ( count($this->arrayImsis)>50) {
                throw new \Exception( trans('messages.000067').' ('.count($this->arrayImsis).')', 9999);
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
                            $result[$key] = @utf8_encode($value);
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

    # -----------------------------------------------------------------------------
    public function getServicesRender(Request $request){

        $renderHtml='';
        try{
            
            if( !$request->isMethod('post') ) {
                throw new \Exception(trans('messages.000042'), 9999);
            }

            $inputs = $request->All();
            $result = $this->getTroubleshooting($request);
            if ( !$result ) {
                throw new \Exception(trans('messages.000166'), 9999);
            }

            if ( $result and $inputs['render']=='pdf' ){
                $this->exportPdf( $this->result );
            }
            if ( $result and $inputs['render']==='html' ){
                if(view()->exists('colombia.troubleshooting.servicesrender')) {
                    return view('colombia.troubleshooting.servicesrender', [
                        'count' => count($this->arrayImsis),
                        'datahtml' => $this->result
                    ]);   
                }
            }
            throw new \Exception(trans('messages.000019'), 9999);

        }catch(\Exception $e){
            $renderHtml=('
            <div class="alert alert-info alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <b>Info: </b>'.$e->getMessage().'
            </div>');
        }
        return $renderHtml;
    }

    # -----------------------------------------------------------------------------
    public function exportPdf( $datapdf=array() ){
        header('Content-type: application/pdf');
        
        ob_start();
        #$pdf = new AppfpdfController();// De la forma usando inclusión de clase en la cabecera.
        $pdf = new \App\Http\Controllers\AppfpdfController;
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
        $pdf->Output($name_doc, 'D', true);
        #$pdf->Output();
        ob_end_flush();
        exit(0);


        return response()->json([
            'pdf' => $pdf,
        ]);
    }

}
