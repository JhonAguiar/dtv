<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customs\Collections as objCollections;
use Illuminate\Support\Facades\Auth;

/**
 * Esta clase es utilitaria a las necesidades genericas en los demas controladores, 
 * se debe tener especial cuidado si se editan sus métodos por los llamados en los
 * diferentes lugares de la aplicación.
 * @Autor <achavezb@directvla.com.co>
 */
class ResourcesController extends Controller
{

    # ----------------------------------------------------------------------
    # Hacer ping a los servers. 
    #este método funciona cuando se tiene visibilidad entre servidores ROSS - Ip destino.
    public function getPingRemote($url=''){
        if (!empty($url)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            #print_r($url.' '.$httpcode);die;
            if($httpcode>=200 && $httpcode<300){
                $result = 'worked';
            } else {
                $result = "didn't work";
            }
            return $result;
        }
        return NULL;
    }


    # ----------------------------------------------------------------------
    # Cambiar la región de trabajo.    
    public function changeRegion( $region='' ){
        if ( !empty($region) ) {
            $region_selected = objCollections::getCollectionRegionsById($region);
            \DB::table('users')->where('username','=',Auth::user()->username)->update(['country'=>$region]);                        
            session()->put('country', $region); 
	    }
        return redirect('/home');
    }

    # ----------------------------------------------------------------------
    # Cambiar el lenguaje en laravel.    
    public function changeLanguage( $locale='' ){
        if ( !empty($locale) ) {
            \DB::table('users')->where('username','=',Auth::user()->username )->update(['language'=>$locale]);            
            session()->put('locale', $locale);
            #\App::setLocale( $locale );
        }
        return redirect()->back();
    }    

    # ----------------------------------------------------------------------
    # Cerrar la sesión por inactividad.
    public function closeSession( Request $request ){
        if( $request->isMethod('get') and Auth::check() ){ 
            Auth::logout();
            return response()->json("caducado");
        }
        return response()->json("No requiere estar autenticado.");
        /*if( Auth::check() ){ 
            # -------------------------------------------
            # Aquí el usuario esta logueado al sistema, ahora validar el tiempo de inactividad.            
            $tiempoSesion = \Session::get('tiempoSesion');
            if( !empty($tiempoSesion) ) {
                $inactivo = 1200;//20 minutos.1200
                $vida_session = (time() - $tiempoSesion); # Calculamos tiempo de vida inactivo.
                if($vida_session > $inactivo){
                    $request->session()->forget( 'tiempoSesion');
                    Auth::logout();
                    return response()->json("caducado");
                    #return redirect('login')->with('mensaje', 'Ha caducado la sesión por inactividad.');
                }
            }
            # Se resetea tiempoSesion asignandole el tiempo actual.
            \Session::put('tiempoSesion', time() );
        }
        # El usuario no estaba logueado.
        return response()->json("No requiere autenticar.");*/
    }

    # ----------------------------------------------------------------------
    # Redireccionar al formulario de login.
    public function sessionRedirect(){
        // Redireccionar a autenticar.
        return redirect('login')->with('message', trans('messages.000137') );
    }

    # ----------------------------------------------------------------------
    # Retorna el navegador que conecta el cliente.
    public function getBrowser() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		if(strpos($user_agent, 'MSIE') !== FALSE){
			return 'Internet explorer';
		}elseif(strpos($user_agent, 'Edge') !== FALSE){ //Microsoft Edge
			return 'Microsoft Edge';
		}elseif(strpos($user_agent, 'Trident') !== FALSE){ //IE 11
			return 'Internet explorer';
		}elseif(strpos($user_agent, 'Opera Mini') !== FALSE){
			return "Opera Mini";
		}elseif(strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR') !== FALSE){
			return "Opera";
		}elseif(strpos($user_agent, 'Firefox') !== FALSE){
			return 'Mozilla Firefox';
		}elseif(strpos($user_agent, 'Chrome') !== FALSE){
			return 'Google Chrome';
		}elseif(strpos($user_agent, 'Safari') !== FALSE){
			return "Safari";
		}else{
			return 'No hemos podido detectar su navegador';
		}
    }

    # ----------------------------------------------------------------------
    # Retorna el dispositivo que conecta el cliente.
    public function getDevice() {
        $tablet_browser = 0;
        $mobile_browser = 0;
        $body_class = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
            $body_class = "tablet";
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
            $body_class = "mobile";
        }
        if ( (isset($_SERVER['HTTP_ACCEPT']) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0)) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
            $body_class = "mobile";
        }
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda ','xda-'
		);
        if (in_array($mobile_ua,$mobile_agents)) {
            $mobile_browser++;
        }
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
              $tablet_browser++;
            }
        }
        if ($tablet_browser > 0) {
            return 'Tablet';
        }else if ($mobile_browser > 0) {
            return 'Mobil';
        }else {
            return 'Ordenador';
        } 
    }

    # ----------------------------------------------------------------------
    # Retorna la IP donde conecta el cliente.
    public function getAdressIP() {
        if ( !empty($_SERVER['HTTP_CLIENT_IP']) ){// verificamos si la IP es una conexión compartida.
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ){// verificamos si la IP pasa por un proxy.
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ( isset($_SERVER["HTTP_X_FORWARDED"]) ){
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif ( isset($_SERVER["HTTP_FORWARDED_FOR"]) ){
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif ( isset($_SERVER["HTTP_FORWARDED"]) ){
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];// Dirección IP desde la cual está viendo la página actual el usuario.
        }
    }

    # ----------------------------------------------------------------------
    # Retorna la fecha en formato texto.
    public function getStringDate(){
    	$meses = array('01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre');
    	#$dias = array('1' => 'lunes', '2' => 'martes', '3' => 'miercoles', '4' => 'jueves', '5' => 'viernes', '6' => 'sábado', '7' => 'domingo');
    	# echo date('N').' dias';
        return $meses[date('m')].' '.date('d').' de '.date('Y');
    }

    # ----------------------------------------------------------------------
    # Retorna cadena limpia sin etiquetas HTML y espacios en blanco prolongados.
    public function cleanStrings( $string='' ){
        $remove = array("!", "¡", "¿", "?", "&nbsp;", "(", ")");
        $string = !empty($string) ? @trim( $string ) : '';// Limpiar espacios en los extremos.
        $string = strip_tags( $string );// Retirar Html.
        $string = str_replace($remove, '', $string); // Remover especiales.
        $string = str_replace(',', ' ', $string); // Remover especiales.
        $string = preg_replace( "/[^A-Za-z0-9 ]/", "", $string );// Alfa númericos permitidos.
        $string = preg_replace ( '/\s\s+/', ' ', $string );// Elemina espacios prolongados.
        return $string;
    }

    # ----------------------------------------------------------------------
    # Retorna el MimeType válidos del nombre "nombre.ext" de un archivo pasado como parametro.
    # Retorna FALSE Si NO es válido retorna.
    public function contentMimeType( $filename ){
        # http://www.marcelopedra.com.ar/blog/2011/05/12/listado-de-tipos-mime/
        # https://developer.mozilla.org/es/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Lista_completa_de_tipos_MIME
        $mime_types = array(
            'xls'   => 'application/vnd.ms-excel',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf'   => 'application/pdf',
            'png'   => 'image/png',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'svg'   => 'image/svg+xml',
            'svgz'  => 'image/svg+xml',
            'rtf'   => 'application/rtf'
        );
        $info = explode( ".", $filename );
        $ext = mb_strtolower( trim( array_pop( $info ) ) ) ;
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }else {
            return FALSE;
        }
    }

    # ----------------------------------------------------------------------
    # Retorna un arreglo vacio o arreglo con urls válidas según la cadena que recibe.
    public function validateUrl( $string ){
        $arrayUrls=array();
        # ----------------------------------------------------------------------
        $cadena_filtrada    = preg_replace("/((http|https|www)[^\s]+)/", '<a href="$1">$0</a>', $string);
        $cadenas_http       = preg_replace("/href=\"www/", 'href="http://www', $cadena_filtrada);
        $cadena_twitter     = preg_replace("/(@[^\s]+)/", '<a target=\"_blank\"  href="http://twitter.com/intent/user?screen_name=$1">$0</a>', $cadenas_http);
        $cadena_final      = preg_replace("/(#[^\s]+)/", '<a target=\"_blank\"  href="http://twitter.com/search?q=$1">$0</a>', $cadena_twitter);
        #echo $cadena_final;
        # ----------------------------------------------------------------------
        # Poner en un arreglo las urls encontradas.
        preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $cadena_final, $result);
        if ( count($result[2])>0 ) {
            foreach ($result[2] as $key => $url) {
                //abrimos el archivo en lectura
                $id = @fopen($url,"r");
                //hacemos las comprobaciones
                if ($id) {
                    $arrayUrls[]=$url;
                    fclose($id);
                }
            }
        }
        # ----------------------------------------------------------------------
        return $arrayUrls;
    }

    # ----------------------------------------------------------------------
    # Elimina duplicados del arreglo mulridimensional por su clave.
    public function uniqueMultidimArray($array, $key){ 
        $temp_array = array(); 
        $i = 0; 
        $key_array = array();
        foreach($array as $val) { 
            if (!in_array($val[$key], $key_array)) { 
                $key_array[$i] = $val[$key]; 
                $temp_array[$i] = $val; 
            } 
            $i++; 
        } 
        return $temp_array; 
    }

    # ----------------------------------------------------------------------
    # Retorna la descarga del archivo solicitado.
    # $Obj->downloadFile("originalComprimido.ext", "copiaComprimido.rar"); 
    public function downloadFile( $path_file, $downloadfile=null ){
        if (file_exists($path_file)) {
            $downloadfile = $downloadfile !== null ? $downloadfile : basename($path_file);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$downloadfile);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: '.filesize($path_file));
            ob_clean();
            flush();
            readfile($path_file);
            return true;   
        }
        return false;      
    }

    # ----------------------------------------------------------------------
    # Retorna borrado de directorio.
    public function unlinkDir($dir){
        $files = array_diff(scandir($dir), array('.','..')); 
        foreach ($files as $file) { 
            (is_dir("$dir/$file") && !is_link($dir)) ? delTree("$dir/$file") : unlink("$dir/$file"); 
        } 
        return rmdir($dir);
    }

    # ----------------------------------------------------------------------
    # Retorna hexadecimal.
    public function generateHexadecimal() {
        $hexa = '';
        $Chars = "0123456789ABCDEF";
        for($i=0; $i<6; $i++) {
            $desordenada = str_shuffle($Chars);//Desordena la cadena.
            $rand = rand(0, 15);
            $hexa .= substr($desordenada, $rand, 1);
        }
        return $hexa;
    }

    # ----------------------------------------------------------------------
    # Retorna el dato alfanúmerico, si $acentos=true: retorna con acentos.
    public function alphanumericData( $string='', $acentos=false ){
        $remove = array("!", "¡", "¿", "?", "&nbsp;");
        $string = !empty($string) ? trim( $string ) : '';
        $string = str_replace($remove, "", $string);
        $string = strip_tags( $string );
        if ($acentos) {
            $string = preg_replace("/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ]/", "", $string);
        }else{
            $string = preg_replace("/[^A-Za-z0-9]/", "", $string);
        }
        return $string;
    }

    # ----------------------------------------------------------------------
    # Genera una operación matemática con texto y numeros.
    public function generateOperation(){
        $textos=array('cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve');
        $operadores=array('+', '*');
        $rand1 = rand(0, 9);
        $rand2 = rand(0, 9);
        $rand3 = rand(0, 1);
        $r1 = rand(0, 1);
        $r2 = rand(0, 1);
        $operador = $operadores[$rand3];
        switch ( $operador ) {
            case '+' : $result = (int)$rand1 + $rand2; break;
            case '*' : $result = (int)$rand1 * $rand2; break;
        }
        if ($r1==0 and $r2==0) {
            $pregunta = $rand1." ".$operador." ".$rand2." = ";
        }elseif($r1==1 and $r2==0){
            $pregunta = $textos[$rand1]." ".$operador." ".$rand2." = ";
        }elseif($r1==1 and $r2==1){
            $pregunta = $textos[$rand1]." ".$operador." ".$textos[$rand2]." = ";
        }else{
            $pregunta = $rand1." ".$operador." ".$textos[$rand2]." = ";
        }
        return array($pregunta, $result);
    }

    # ----------------------------------------------------------------------
    public function utf8StringsArrays(&$array){
        $func = function(&$value,&$key){
            if(is_string($value)){
                $value = utf8_encode($value);
            } 
            if(is_string($key)){
                $key = utf8_encode($key);
            }
            if(is_array($value)){
                utf8_string_array_encode($value);
            }
        };
        array_walk($array,$func);
        return $array;
    }

}
