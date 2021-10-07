<?php

namespace App\Http\Controllers\Colombia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Esta clase sirve como herramienta para el tratamiento de las IMSI.
 * @Autor <achavezb@directvla.com.co>
 */
class ImsiColombiaController extends Controller
{

    # -----------------------------------------------------------------------------
    protected static function profilesColombia(){
        return [
            ['id' => 1, 'profile' => 'DIRECTV NET 1M', 'status' => 'inactive'],
            ['id' => 2, 'profile' => 'DIRECTV NET 2M', 'status' => 'active'],
            ['id' => 3, 'profile' => 'DIRECTV NET 4M', 'status' => 'active'],
            ['id' => 4, 'profile' => 'DIRECTV NET 6M', 'status' => 'active'],
            ['id' => 5, 'profile' => 'DIRECTV NET 10M', 'status' => 'active'],
            ['id' => 6, 'profile' => 'DIRECTV NET 15M', 'status' => 'active'],
            ['id' => 7, 'profile' => 'DIRECTV NET 20M', 'status' => 'active']
        ];
    }

    # -----------------------------------------------------------------------------
    public static function getProfilesActive(){
        $collection = self::profilesColombia();
        return collect( $collection )->where('status', 'active');
    }    
    
    # ----------------------------------------------------------------------
    # Retorna imsis unicas, extraidas de un archivo en formato CSV.
    public function getArrayImsis( $data='' ){
        ini_set('memory_limit', '256M');
        #echo "<pre>"; print_r($data ); die("dos");
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
                                                $imsi = $this->imsiColombia( $val );
                                                if (!empty($imsi)) {
                                                    array_push( $arrayImsis, $imsi );
                                                }
                                            }
                                        }
                                        $line++;
                                    }
                                    fclose($gestor);
                                }else{
                                    //No se puede abrir el archivo
                                    array_push( $processErrors, trans('messages.000040'));
                                }
                            }else{
                                //Tipo de archivo no permitido
                                array_push( $processErrors, trans('messages.000038'));
                            }
                        }else{
                            //El archivo es muy grande.
                            array_push( $processErrors, trans('messages.000032'));
                        }
                    }else{
                        //El archivo no cumple con las especificaciones.
                        array_push( $processErrors, trans('messages.000055'));
                    }
                }else{
                    //El elemento cargado no es un archivo válido
                    array_push( $processErrors, trans('messages.000053'));
                }
            }else if( isset($subscriber_identity) and !empty($subscriber_identity) ){
                $imsi = self::imsiColombia( $subscriber_identity );
                if (!empty($imsi)) {
                    array_push($arrayImsis, $imsi);
                }
            }else{
                //Campo de datos no especificado
                array_push( $processErrors, trans('messages.000044'));
            }
        }else{
            //Se requiere seleccionar el tipo de carga de IMSIs
            array_push( $processErrors, trans('messages.000057'));
        }

        return [
            'imsis' => array_unique($arrayImsis),
            'errors' => $processErrors
        ];

        // return [
        //     'imsis' => array_unique($arrayImsis),
        //     'errors' => $processErrors
        // ];
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
                    $imsi = self::imsiColombia( $val );
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
