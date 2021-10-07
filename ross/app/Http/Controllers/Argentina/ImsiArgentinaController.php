<?php

namespace App\Http\Controllers\Argentina;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Esta clase sirve como herramienta para el tratamiento de las IMSI.
 * @Autor <achavezb@directvla.com.co>
 */
class ImsiArgentinaController extends Controller
{


    # -----------------------------------------------------------------------------
    protected static function technologiesArgentina(){
        return [
            ['id' => 1, 'technology' => 'FTTH', 'status' => 'active', 'selected' => ''],
            ['id' => 2, 'technology' => 'LTE/WIMAX', 'status' => 'active', 'selected' => 'selected'],
            ['id' => 3, 'technology' => 'SATELITAL', 'status' => 'active', 'selected' => ''],            
            ['id' => 4, 'technology' => '5G', 'status' => 'active', 'selected' => ''], 
        ];
    }


    # -----------------------------------------------------------------------------
    protected static function profilesArgentina(){
        return [
            ['id' => 1, 'technology' => 'FTTH', 'profiles' => ['6MB', '12MB', '30MB', '100MB']],
            ['id' => 2, 'technology' => 'LTE/WIMAX', 'profiles' => ['3MB', '6MB', '10MB', '15MB', '20MB']],
            ['id' => 3, 'technology' => 'SATELITAL', 'profiles' => ['1GB', '5GB', '10GB', '20GB', '30GB', '50GB']],
            ['id' => 4, 'technology' => '5G', 'profiles' => ['6MB', '10MB', '12MB', '20MB', '30MB', '50MB']],
        ];
    }


    # -----------------------------------------------------------------------------
    public static function getTechnologiesActive(){
        $collection = self::technologiesArgentina();
        return collect( $collection )->where('status', 'active');
    } 

    
    # -----------------------------------------------------------------------------
    public static function getProfiles(){
        $collection = self::profilesArgentina();
        return collect( $collection );
    }    


    # -----------------------------------------------------------------------------
    public function getProfilesByTechnology( Request $request ){
        extract( $request->All() );
        $collection = self::profilesArgentina();
        $data = collect( $collection )->where('technology', $technology);
        foreach ($data as $value) {
            $array[] = $value['profiles'];
        }
        return response()->json($array[0]);
    }


    # ----------------------------------------------------------------------
    # Retorna imsis unicas, extraidas de un archivo en formato CSV.
    public function getArrayImsis( $data='' ){
        ini_set('memory_limit', '256M');
        extract( $data->All() );
        $arrayImsis = array();
        $processErrors = array();
        $objResources = new \App\Http\Controllers\ResourcesController;

        if ( isset($load_type) and !empty($load_type) ){
            if ( $load_type=='multiple' and isset($subscriber_identity_file) ) {
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
                                if (($gestor=fopen( $validate['tmp_name'], "r") ) !== FALSE) {
                                    while ( ($filedata = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                                        $number=count($filedata);
                                        for ($c=0; $c<$number; $c++) {
                                            $strClean = $objResources->cleanStrings( $filedata[$c] );
                                            $parts = explode(' ', $strClean);
                                            foreach ($parts as $val) {
                                                $imsi = $this->imsiArgentina( $val );
                                                if (!empty($imsi)) {
                                                    array_push( $arrayImsis, $imsi );
                                                }
                                            }
                                        }
                                        $line++;
                                    }
                                    fclose($gestor);
                                }else{
                                    array_push( $processErrors, trans('messages.000040'));
                                }
                            }else{
                                array_push( $processErrors, trans('messages.000038'));
                            }
                        }else{
                            array_push( $processErrors, trans('messages.000032'));
                        }
                    }else{
                        array_push( $processErrors, trans('messages.000055'));
                    }
                }else{
                    array_push( $processErrors, trans('messages.000053'));
                }
            }else if( isset($subscriber_identity) and !empty($subscriber_identity) ){
                $imsi = self::imsiArgentina( $subscriber_identity );
                if (!empty($imsi)) {
                    array_push($arrayImsis, $imsi);
                }
            }else{
                array_push( $processErrors, trans('messages.000044'));
            }
        }else{
            array_push( $processErrors, trans('messages.000057'));
        }

        return [
            'imsis' => array_unique($arrayImsis),
            'errors' => $processErrors
        ];
    }

    # ----------------------------------------------------------------------
    # Retorna imsis unicas, extraidas de una cadena de texto.
    public static function extractImsis( $data='' ){
        $arrayImsis = array();
        $processErrors = array();
        $objResources = new \App\Http\Controllers\ResourcesController;
        $string = $objResources->cleanStrings( $data );
        $arrayParts = explode(",", $string);

        if ( is_array($arrayParts) or count($arrayParts)>0) {
            foreach ($arrayParts as $key => $value) {
                $parts = explode(' ', $value);
                foreach ($parts as $val) {
                    $imsi = self::imsiArgentina( $val );
                    if (!empty($imsi)) {
                        array_push($arrayImsis, $imsi);
                    }
                }
            }
        }                    
        return [
            'imsis' => array_unique($arrayImsis),
            'errors' => $processErrors
        ];
    }

    # ----------------------------------------------------------------------
    # Retorna imsis validas o vacio en caso contrario.
    public static function imsiArgentina( $string='' ){
        $string = !empty($string) ? trim( $string ) : '';// Limpiar espacios en los extremos.
        $string = strip_tags( $string );// Retirar Html.
        $clean = array("!", "¡", "¿", "?", "&nbsp;", "(", ")");
        $string = str_replace($clean, '', $string); // Remover especiales.
        #$string = preg_replace( "/[^0-9]/", "", $string );// Alfa númericos permitidos.
        $string = preg_replace ( '/\s\s+/', ' ', $string );// Elemina espacios prolongados.
        if ( strlen($string)>=12 and strlen($string)<=15) {
        #if (preg_match('/7{5}\d{10}/', $string)){
            return $string;
        }
        return '';
    }

}