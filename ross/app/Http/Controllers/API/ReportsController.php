<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Support\Facades\App;
use App\Libraries\AppCodes;


class ReportsController extends Controller
{
    protected $sqlbogServername;
	protected $sqlbogDatabase;
	protected $sqlbogUsername;
	protected $sqlbogPassword;
    protected $result = array();
    protected $resultPC = array();
    protected $errors = array();
    protected $formatErrors = array();


    # -----------------------------------------------------------------------------
	public function __construct() {
		$this->sqlbogServername = config('appross.sqlbog_servername', '');
		$this->sqlbogDatabase = config('appross.sqlbog_database', '');
		$this->sqlbogUsername = config('appross.sqlbog_username', '');
		$this->sqlbogPassword = config('appross.sqlbog_password', '');
	}

    //Obtener datos grafica
	public function graphics(Request $request ){
		
        try{
            $inputs = $request->All();
            extract( $request->All() );

            $php_extensions = get_loaded_extensions();
            if( !in_array( 'sqlsrv', $php_extensions) ){
                // Error al procesar la solicitud Sin conexión del servidor SQL.
                throw new \Exception(trans('messages.000178'), 10);
            }


            // Establishes the connection
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            if ($conn === false) {
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 1");
            }

            switch ($date_query) {
                case 'hoy':
                    $dateIni = date("Y-m-d", strtotime(date("Y-m-d")));
                break;
                case 'ayer':
                    $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 2 days'));
                break;
                case 'ultima_semana':
                    $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 8 days'));
                break;
                case 'ultimos_quince':
                    $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 15 days'));
                break;
                case 'ultimo_mes':
                    $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days'));
                break;
            }
            $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' - 1 days'));
            if($date_query == "hoy"){
                $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' + 1 days'));
            }

            //Operacion 2
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$dateIni."', @fechafin = '".$dateFin."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $chart_pie=array();
            $rowdata=array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    //$fecha = (array)$row['FechaEvento'];
                    //$fecha = substr($fecha['date'], 0, 10);
                    $fecha = "dato";

                    if( isset($chart_pie[$row['Perfil']]) ){
                        $chart_pie[$row['Perfil']]++;
                    }else{
                        $chart_pie[$row['Perfil']]=1;
                    }

                    if( isset($rowdata[$fecha][$row['Perfil']]) ){
                        $rowdata[$fecha][$row['Perfil']]++;
                    }else{
                        $rowdata[$fecha][$row['Perfil']]=1;
                    }
                }
            }
            

            //Date Consulta
            $today = date("Y-m-d", strtotime(date("Y-m-d"). ' - 1 days'));
            //Operacion 0
            $query ="exec stpQryStats @tpOperacion= 0, @dteIBS = '".$today."';";
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $totales=array();
            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            array_push($totales, $row['cntPre']);
            array_push($totales, $row['cntPos']);
            array_push($totales, $row['Total']);



            $perfiles=array();
            $query = "select DISTINCT b.Perfil from tbHistorico as a left join tbHistoricoProvisioning as b on a.CodHistorico=b.CodHistoricoProvisioning where (FechaEvento BETWEEN '".$dateIni."' AND '".$dateFin."')";
            $result = sqlsrv_query($conn, $query);
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                array_push($perfiles, $row['Perfil']);
            }

            
            
            sqlsrv_free_stmt($result);
            sqlsrv_close($conn);


            ksort($rowdata);
            ksort($perfiles);

            $dataline=array();
            $dataline['eje-x']="['x', ";
            foreach ($perfiles as $key => $val) {
                $dataline[$val]="['".$val."', ";
            }

            foreach ($rowdata as $dia => $data) {
                $dataline['eje-x'].="'".$dia."', ";
                foreach ($perfiles as $key => $val) {
                    if (isset($data[$val])) {
                        $dataline[$val].=$data[$val].", ";
                    }else{
                        $dataline[$val].="0, ";
                    }
                }
            }
            $dataline['eje-x'] = substr($dataline['eje-x'], 0, -2)."],";
            foreach ($perfiles as $key => $val) {
                $dataline[$val] = substr($dataline[$val], 0, -2)."],";
            }

            $string='';
            foreach ($dataline as $key => $val) {
                $string.=$val;
            }
            $string="[".substr($string, 0, -1)."]";

            $this->result['chartpie'] = $chart_pie;
            $this->result['chartline'] = $string;
            $this->result['perfiles'] = $perfiles;
            $this->result['rowdata'] = $rowdata;
            $this->result['resultado'] = $totales;
            // $this->result['fecha'] = $dateIni;
        }catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );
        }
		
		return response()->json([
			'result' => $this->result,
            'errors' => $this->errors
		]);
		
    }
    
    //Obtener planes Colombia
    public function planesPieChart(Request $request){
        try{
            $inputs = $request->All();
            extract( $request->All() );

            $php_extensions = get_loaded_extensions();
            if( !in_array( 'sqlsrv', $php_extensions) ){
                // Error al procesar la solicitud Sin conexión del servidor SQL.
                throw new \Exception(trans('messages.000178'), 10);
            }

            // Establishes the connection
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            if ($conn === false) {
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 1");
            }

            $dateIni = date("Y-m-d", strtotime(date("Y-m-d")));
            $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' + 1 days'));

            //Operacion 2
            //$query ="exec stpQryStats @tpOperacion= 11 , @fechaini = '2019-04-12', @fechafin = '2020-04-09', @codEvento = 1013 , @codEstadoProcesado = 1 ";//Activaciones
            $query = "EXEC stpQryStats @tpOperacion = 20,@codEvento = 1013 ";
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $resultados= array();
            $e = 0;
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {   
                $rowdata[$row["Perfil"]] = $row["Activaciones"];
            }

        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );
        }
		
        return response()->json([
            'result' => $rowdata
		]);
    }
    
    //Obtener activaciones x mes Colombia
    public function getActivationsxMonth(Request $request){
        try{
            $inputs = $request->All();
            extract( $request->All() );

            $php_extensions = get_loaded_extensions();
            if( !in_array( 'sqlsrv', $php_extensions) ){
                // Error al procesar la solicitud Sin conexión del servidor SQL.
                throw new \Exception(trans('messages.000178'), 10);
            }

            // Establishes the connection
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            if ($conn === false) {
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 1");
            }
            $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' first day of'));
            $dateIni = date("Y-m-d", strtotime($dateIni. '+ 1month'));
            $fecha = strtotime($dateIni);
            $mes= date("F", $fecha);


            //Get fechas primer mes
            $fechaInicio1 = date("Y-m-d", strtotime(date("Y-m-d"). ' first day of'));
            $fechaFinal1 = date("Y-m-d", strtotime($fechaInicio1. '+ 1month'));
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio1."', @fechafin = '".$fechaFinal1."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month1= array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month1[$row['Perfil']]) ){
                        $month1[$row['Perfil']]++;
                    }else{
                        $month1[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes1 = 0;
            foreach ($month1 as $key => $val) {
                $datos_mes1 = $datos_mes1 + $val;
            }
            
            //Get fechas segundo mes
            $fechaInicio2 = date("Y-m-d", strtotime($fechaInicio1. '- 1month'));
            $fechaFinal2 = $fechaInicio1;
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio2."', @fechafin = '".$fechaFinal2."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month2= array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month2[$row['Perfil']]) ){
                        $month2[$row['Perfil']]++;
                    }else{
                        $month2[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes2 = 0;
            foreach ($month2 as $key => $val) {
                $datos_mes2 = $datos_mes2 + $val;
            }

            //Get fechas tercer mes
            $fechaInicio3 = date("Y-m-d", strtotime($fechaInicio2. '- 1month'));
            $fechaFinal3 = $fechaInicio2;
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio3."', @fechafin = '".$fechaFinal3."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month3= array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month3[$row['Perfil']]) ){
                        $month3[$row['Perfil']]++;
                    }else{
                        $month3[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes3 = 0;
            foreach ($month3 as $key => $val) {
                $datos_mes3 = $datos_mes3 + $val;
            }

            //Get fechas cuarto mes
            $fechaInicio4 = date("Y-m-d", strtotime($fechaInicio3. '- 1month'));
            $fechaFinal4 = $fechaInicio3;
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio4."', @fechafin = '".$fechaFinal4."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month4=array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month4[$row['Perfil']]) ){
                        $month4[$row['Perfil']]++;
                    }else{
                        $month4[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes4 = 0;
            foreach ($month4 as $key => $val) {
                $datos_mes4 = $datos_mes4 + $val;
            }

            //Get fechas quinto mes
            $fechaInicio5 = date("Y-m-d", strtotime($fechaInicio4. '- 1month'));
            $fechaFinal5 = $fechaInicio4;
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio5."', @fechafin = '".$fechaFinal5."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month5=array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month5[$row['Perfil']]) ){
                        $month5[$row['Perfil']]++;
                    }else{
                        $month5[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes5 = 0;
            foreach ($month5 as $key => $val) {
                $datos_mes5 = $datos_mes5 + $val;
            }

            //Get fechas sexto mes
            $fechaInicio6 = date("Y-m-d", strtotime($fechaInicio5. '- 1month'));
            $fechaFinal6 = $fechaInicio5;
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$fechaInicio6."', @fechafin = '".$fechaFinal6."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $month6=array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);

                    if( isset($month6[$row['Perfil']]) ){
                        $month6[$row['Perfil']]++;
                    }else{
                        $month6[$row['Perfil']]=1;
                    }
                }
            }

            $datos_mes6 = 0;
            foreach ($month6 as $key => $val) {
                $datos_mes6 = $datos_mes6 + $val;
            }

            //Mes actual
            $query1 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio1."', @fechafin = N'".$fechaFinal1."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result2 = sqlsrv_query($conn, $query1);
            if ($result2 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador = 0;
            while ($row = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)) {
                $contador = intval($row["count(a.imsi)"]) + $contador;
            }
            $this->resultPC["mes1"] = $contador;

            // 1 mes antes
            $query2 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio2."', @fechafin = N'".$fechaFinal2."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result3 = sqlsrv_query($conn, $query2);
            if ($result3 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador2 = 0;
            while ($row = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)) {
                $contador2 = intval($row["count(a.imsi)"]) + $contador2;
            }
            $this->resultPC["mes2"] = $contador2;

            // 2 meses antes
            $query3 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio3."', @fechafin = N'".$fechaFinal3."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result4 = sqlsrv_query($conn, $query3);
            if ($result4 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador3 = 0;
            while ($row = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC)) {
                $contador3 = intval($row["count(a.imsi)"]) + $contador3;
            }
            $this->resultPC["mes3"] = $contador3;

            // 3 meses antes
            $query4 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio4."', @fechafin = N'".$fechaFinal4."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result5 = sqlsrv_query($conn, $query4);
            if ($result5 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador4 = 0;
            while ($row = sqlsrv_fetch_array($result5, SQLSRV_FETCH_ASSOC)) {
                $contador4 = intval($row["count(a.imsi)"]) + $contador4;
            }
            $this->resultPC["mes4"] = $contador4;

            // 4 meses antes
            $query5 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio5."', @fechafin = N'".$fechaFinal5."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result6 = sqlsrv_query($conn, $query5);
            if ($result6 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador5 = 0;
            while ($row = sqlsrv_fetch_array($result6, SQLSRV_FETCH_ASSOC)) {
                $contador5 = intval($row["count(a.imsi)"]) + $contador5;
            }
            $this->resultPC["mes5"] = $contador5;

            // 5 meses antes
            $query6 ="exec stpQryStats @tpOperacion= 13 , @fechaini = N'".$fechaInicio6."', @fechafin = N'".$fechaFinal6."'" ;//Portal Cautivo counters (search);//Activaciones PC

            $result7 = sqlsrv_query($conn, $query6);
            if ($result6 === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            }

            $contador6 = 0;
            while ($row = sqlsrv_fetch_array($result7, SQLSRV_FETCH_ASSOC)) {
                $contador6 = intval($row["count(a.imsi)"]) + $contador6;
            }
            $this->resultPC["mes6"] = $contador6;

            

            sqlsrv_free_stmt($result);
            sqlsrv_close($conn);
            
            //Geth array months
            $meses = array();
            $dateMes = $dateIni;
            
            for($i = 0; $i<=5; $i++){
                $dateMes = date("Y-m-d", strtotime($dateMes. '- 1month'));
                $fecha = strtotime($dateMes);
                $mes= date("F", $fecha);
                $meses[$i] = $mes;
            }

            
            // $this->result['month'] = $meses;
            
            $this->result['mes1'] = $datos_mes1;
            $this->result['mes2'] = $datos_mes2;
            $this->result['mes3'] = $datos_mes3;
            $this->result['mes4'] = $datos_mes4;
            $this->result['mes5'] = $datos_mes5;
            $this->result['mes6'] = $datos_mes6;
            $this->result['month'] = $meses;

        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );
        }
		
        return response()->json([
            'result' => $this->result,
            'resultPC' => $this->resultPC 
		]);
    }

    //Obtener activaciones ultima semana Colombia
    public function getActivationsLastWeek(Request $request){
        try{
            $inputs = $request->All();
            extract( $request->All() );

            $php_extensions = get_loaded_extensions();
            if( !in_array( 'sqlsrv', $php_extensions) ){
                // Error al procesar la solicitud Sin conexión del servidor SQL.
                throw new \Exception(trans('messages.000178'), 10);
            }

            // Establishes the connection
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            if ($conn === false) {
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 1");
            }

            $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 7 days'));
            $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' + 1 days'));

            //Operacion 2
            $query ="exec stpQryStats @tpOperacion= 2, @fechaini = '".$dateIni."', @fechafin = '".$dateFin."', @codEvento =".$type_query;//Activaciones
            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            } 

            $rowdata=array();
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                if ($row['CodRespuestaWS']==0) {
                    $dato = "datos";
                    $fecha = (array)$row['FechaEvento'];
                    $fecha = substr($fecha['date'], 0, 10);
                    // $fecha = "dato";

                    if( isset($rowdata[$fecha][$row['Perfil']]) ){
                        $rowdata[$fecha][$row['Perfil']]++;
                    }else{
                        $rowdata[$fecha][$row['Perfil']]=1;
                    }
                }
            }

            $this->result['rowdata'] = $rowdata;
        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );
        }
		
        return response()->json([
            'result' => $this->result,
		]);
    }

    //Obtener subscriptores de BroadBand Colombia
    public function getSubscriberBroadBand(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query ="EXEC [dbo].[stpQryStats] @tpOperacion = 16";//Activaciones

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
            
        }
        
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }

    //Obtener total activaciones portal cautivo Colombia
    public function getTotalActivationsPC(Request $request){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query ="EXEC [dbo].[stpQryStats] @tpOperacion = 12";//Activaciones

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
            
        }
        
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }

       
    }
    
    //Obtener Qty para los Supends Prepago Colombia
    public function getQTYPrepay(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query = "EXEC	[dbo].[stpQryStats] @tpOperacion = 21";

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }

    //Obtener activaciones portal cautivo ultma semana Argentina
    public function getActivationsPCLastWeekAr(){
        try{

            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $dateIni = date("Y-m-d", strtotime(date("Y-m-d"). ' - 7 days'));
            $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' + 1 days'));

            $query = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$dateIni."' , @fechaFin = N'".$dateFin."' , @codEvento = 1";

            $result = sqlsrv_query($conn, $query);            

            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $rowdata=array();
            $e8 = 0;
            $e1 = 0;
            $e2 = 0;
            $e3 = 0;
            $e4 = 0;
            $e5 = 0;
            $e6 = 0;
            $e7 = 0;
            $date0 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 7 days'));
            $date1 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 6 days'));
            $date2 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 5 days'));
            $date3 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 4 days'));
            $date4 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 3 days'));
            $date5 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 2 days'));
            $date6 = date("Y-m-d", strtotime(date("Y-m-d"). ' - 1 days'));
            $date7 = date("Y-m-d", strtotime(date("Y-m-d")));
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {    
                str_replace("-", "/" ,$date7) == $row['Fecha Activacion'] ? $e1 = $row['Cantidad'] + $e1 : '';
                str_replace("-", "/" ,$date6) == $row['Fecha Activacion'] ? $e2 = $row['Cantidad'] + $e2 : '';
                str_replace("-", "/" ,$date5) == $row['Fecha Activacion'] ? $e3 = $row['Cantidad'] + $e3 : '';
                str_replace("-", "/" ,$date4) == $row['Fecha Activacion'] ? $e4 = $row['Cantidad'] + $e4 : '';
                str_replace("-", "/" ,$date3) == $row['Fecha Activacion'] ? $e5 = $row['Cantidad'] + $e5 : '';
                str_replace("-", "/" ,$date2) == $row['Fecha Activacion'] ? $e6 = $row['Cantidad'] + $e6 : '';
                str_replace("-", "/" ,$date1) == $row['Fecha Activacion'] ? $e7 = $row['Cantidad'] + $e7 : '';
                str_replace("-", "/" ,$date0) == $row['Fecha Activacion'] ? $e8 = $row['Cantidad'] + $e8 : '';
            }

            $rowdata[0]["Cantidad"] = $e1;
            $rowdata[0]["Fecha"] = $date7;
            $rowdata[1]["Cantidad"] = $e2;
            $rowdata[1]["Fecha"] = $date6;
            $rowdata[2]["Cantidad"] = $e3;
            $rowdata[2]["Fecha"] =$date5;
            $rowdata[3]["Cantidad"] = $e4;
            $rowdata[3]["Fecha"] = $date4;
            $rowdata[4]["Cantidad"] = $e5;
            $rowdata[4]["Fecha"] = $date3;
            $rowdata[5]["Cantidad"] = $e6;
            $rowdata[5]["Fecha"] =$date2;
            $rowdata[6]["Cantidad"] = $e7;
            $rowdata[6]["Fecha"] = $date1;
            $rowdata[7]["Cantidad"] = $e8;
            $rowdata[7]["Fecha"] = $date0;
  

            $this->result = $rowdata;
            return response()->json([
                'result' => $rowdata
            ]);
        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }

    }

    //Activaciones del dia provisioning Argentina
    public function GetActivationsAr(Request $request){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query = "EXEC	[dbo].[stpQryStats] @tpOperacion = 14";

            $result = sqlsrv_query($conn, $query);            

            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
        }catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
  
    }

    //Activaciones del mes Provisioning Argentina
    public function getActivationsMonthAr(){
        try{

            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            //Mes 1
            $fechaInicio1 = date("Y-m-d", strtotime(date("Y-m-d"). ' first day of'));
            $fechaFinal1 = date("Y-m-d", strtotime($fechaInicio1. '+ 1month'));

            $query = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio1."' , @fechaFin = N'".$fechaFinal1."' , @codEvento = 1";

            $result = sqlsrv_query($conn, $query);            

            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $rowdata=array();
            $e = 0;
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e = $row["Cantidad"] + $e;
            }
            
            $fecha = strtotime($fechaInicio1);
            $mes1= date("F", $fecha);

            $rowdata[0]["FechaInicial"] = $fechaInicio1;
            $rowdata[0]["FechaFinal"] = $fechaFinal1;
            $rowdata[0]["Total"] = $e;
            $rowdata[0]["Mes"] = $mes1;

            //Mes 2
            $fechaInicio2 = date("Y-m-d", strtotime($fechaInicio1. '- 1month'));
            $fechaFinal2 = $fechaInicio1;
            $query2 = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio2."' , @fechaFin = N'".$fechaFinal2."' , @codEvento = 1";

            $result2 = sqlsrv_query($conn, $query2); 

            if ($result2 === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $e2 = 0;
            while ($row2 = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e2 = $row2["Cantidad"] + $e2;
            }
            
            $fecha2 = strtotime($fechaInicio2);
            $mes2= date("F", $fecha2);

            $rowdata[1]["FechaInicial"] = $fechaInicio2;
            $rowdata[1]["FechaFinal"] = $fechaFinal2;
            $rowdata[1]["Total"] = $e2;
            $rowdata[1]["Mes"] = $mes2;

            //Mes 3
            $fechaInicio3 = date("Y-m-d", strtotime($fechaInicio2. '- 1month'));
            $fechaFinal3 = $fechaInicio2;
            $query3 = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio3."' , @fechaFin = N'".$fechaFinal3."' , @codEvento = 1";

            $result3 = sqlsrv_query($conn, $query3); 

            if ($result3 === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $e3 = 0;
            while ($row3 = sqlsrv_fetch_array($result3, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e3 = $row3["Cantidad"] + $e3;
            }
            
            $fecha3 = strtotime($fechaInicio3);
            $mes3 = date("F", $fecha3);

            $rowdata[2]["FechaInicial"] = $fechaInicio3;
            $rowdata[2]["FechaFinal"] = $fechaFinal3;
            $rowdata[2]["Total"] = $e3;
            $rowdata[2]["Mes"] = $mes3;

            //Mes 4
            $fechaInicio4 = date("Y-m-d", strtotime($fechaInicio3. '- 1month'));
            $fechaFinal4 = $fechaInicio3;
            $query4 = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio4."' , @fechaFin = N'".$fechaFinal4."' , @codEvento = 1";

            $result4 = sqlsrv_query($conn, $query4); 

            if ($result4 === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $e4 = 0;
            while ($row4 = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e4 = $row4["Cantidad"] + $e4;
            }
            
            $fecha4 = strtotime($fechaInicio4);
            $mes4 = date("F", $fecha4);

            $rowdata[3]["FechaInicial"] = $fechaInicio4;
            $rowdata[3]["FechaFinal"] = $fechaFinal4;
            $rowdata[3]["Total"] = $e4;
            $rowdata[3]["Mes"] = $mes4;

            //Mes 5
            $fechaInicio5 = date("Y-m-d", strtotime($fechaInicio4. '- 1month'));
            $fechaFinal5 = $fechaInicio4;
            $query5 = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio5."' , @fechaFin = N'".$fechaFinal5."' , @codEvento = 1";

            $result5 = sqlsrv_query($conn, $query5); 

            if ($result5 === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $e5 = 0;
            while ($row5 = sqlsrv_fetch_array($result5, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e5 = $row5["Cantidad"] + $e5;
            }
            
            $fecha5 = strtotime($fechaInicio5);
            $mes5 = date("F", $fecha5);

            $rowdata[4]["FechaInicial"] = $fechaInicio5;
            $rowdata[4]["FechaFinal"] = $fechaFinal5;
            $rowdata[4]["Total"] = $e5;
            $rowdata[4]["Mes"] = $mes5;

            //Mes 6
            $fechaInicio6 = date("Y-m-d", strtotime($fechaInicio5. '- 1month'));
            $fechaFinal6 = $fechaInicio5;
            $query6 = "EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaIni = N'".$fechaInicio6."' , @fechaFin = N'".$fechaFinal6."' , @codEvento = 1";

            $result6 = sqlsrv_query($conn, $query6); 

            if ($result6 === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $e6 = 0;
            while ($row6 = sqlsrv_fetch_array($result6, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                $e6 = $row6["Cantidad"] + $e6;
            }
            
            $fecha6 = strtotime($fechaInicio6);
            $mes6 = date("F", $fecha6);

            $rowdata[5]["FechaInicial"] = $fechaInicio6;
            $rowdata[5]["FechaFinal"] = $fechaFinal6;
            $rowdata[5]["Total"] = $e6;
            $rowdata[5]["Mes"] = $mes6;



            $this->result = $rowdata;
            return response()->json([
                'result' => $rowdata
            ]);
        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }

    //Subcsriptores IBS  Argentina
    public function getSubscriberIBS(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query ="EXEC [dbo].[stpQryStats] @tpOperacion = 17";

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
            
        }
        
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }

    //Suspenciones Argentina
    public function getSuspendsAr(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query ="EXEC	[dbo].[stpQryStats] @tpOperacion = 18";

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
            
        }
        
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }

    //Obtener porcentaje planes Argentina
    public function getPlansArgentinaDaily(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $dateIni = date("Y-m-d", strtotime(date("Y-m-d")));
            $dateFin = date("Y-m-d", strtotime(date("Y-m-d"). ' + 1 days'));


            $query ="EXEC [dbo].[stpQryStats] @tpOperacion = 15, @fechaini = N'".$dateIni."', @fechafin = N'".$dateFin."', @codEvento = 1";

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }
            
            $rowdata=array();
            $plan2MB = 0;
            $plan3MB = 0;
            $plan6MB = 0;
            $plan9MB = 0;
            $plan10MB = 0;
            $plan12MB = 0;
            $plan15MB = 0;
            $plan20MB = 0;
            $plan25MB = 0;
            $plan30MB = 0;
            $plan100MB = 0;

            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {   
                //$rowdata[$e++] = $row;
                if($row["Plan"] == "2MB"){
                    $plan2MB = $row["Cantidad"] + $plan2MB;
                }
                if($row["Plan"] == "3MB"){
                    $plan3MB = $row["Cantidad"] + $plan3MB;
                }
                if($row["Plan"] == "6MB"){
                    $plan6MB = $row["Cantidad"] + $plan6MB;
                }
                if($row["Plan"] == "9MB"){
                    $plan9MB = $row["Cantidad"] + $plan9MB;
                }
                if($row["Plan"] == "10MB"){
                    $plan10MB = $row["Cantidad"] + $plan10MB;
                }
                if($row["Plan"] == "12MB"){
                    $plan12MB = $row["Cantidad"] + $plan12MB;
                }
                if($row["Plan"] == "15MB"){
                    $plan15MB = $row["Cantidad"] + $plan15MB;
                }
                if($row["Plan"] == "20MB"){
                    $plan20MB = $row["Cantidad"] + $plan20MB;
                }
                if($row["Plan"] == "25MB"){
                    $plan25MB = $row["Cantidad"] + $plan25MB;
                }
                if($row["Plan"] == "30MB"){
                    $plan30MB = $row["Cantidad"] + $plan30MB;
                }
                if($row["Plan"] == "100MB"){
                    $plan100MB = $row["Cantidad"] + $plan100MB;
                }


            }

            $rowdata["Plan2MB"] = $plan2MB;
            $rowdata["Plan3MB"] = $plan3MB;
            $rowdata["Plan6MB"] = $plan6MB;
            $rowdata["Plan9MB"] = $plan9MB;
            $rowdata["Plan10MB"] = $plan10MB;
            $rowdata["Plan12MB"] = $plan12MB;
            $rowdata["Plan15MB"] = $plan15MB;
            $rowdata["Plan20MB"] = $plan20MB;
            $rowdata["Plan25MB"] = $plan25MB;
            $rowdata["Plan30MB"] = $plan30MB;
            $rowdata["Plan100MB"] = $plan100MB;

            return response()->json([
                'result' => $rowdata,
                'fechaInicio' => $dateIni,
                'fechaFin' => $dateFin
            ]);
            
        }catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }

    }

    public function getSubscriberBroadBandAr(){
        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query ="EXEC [dbo].[stpQryStats] @tpOperacion = 19";

            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
            
        }
        
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
    }    

    //GET PROFILE FOR PROVISIONINGNAPI
    public function getProfileProvisioningAPI(Request $request){

        try{
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            
            if ($conn === false) {
                $this->errors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query = "EXEC stpQryNET @tpOperacion = 11, @IMSI = '". $request->input("imsi") ."' ";


            $result = sqlsrv_query($conn, $query);
            
            if ($result === false) {// Error handling
                $this->errors( sqlsrv_errors() );
                #die("Error 2");
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            return response()->json([
                'result' => $row
            ]);
        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );

            return response()->json([
                'result' => $this->errors
            ]);
        }
        
    }

    public function netcheckgraphics(Request $request){
        try{
            //$inputs = $request->All();
            extract( $request->All() );

            $php_extensions = get_loaded_extensions();
            if( !in_array( 'sqlsrv', $php_extensions) ){
                // Error al procesar la solicitud Sin conexión del servidor SQL.
                throw new \Exception(trans('messages.000178'), 10);
            }

            // Establishes the connection
            $connectionOptions = array("database"=>$this->sqlbogDatabase, "uid"=>$this->sqlbogUsername, "pwd"=>$this->sqlbogPassword );
            $conn = sqlsrv_connect($this->sqlbogServername, $connectionOptions);
            if ($conn === false) {
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 1");
            }

            $query = "EXEC [dbo].[stpQryStats] @tpOperacion = 22, @fechaini = N'".$request->input("date-ini")."', @fechafin = N'".$request->input("date-end")."'";

            $result = sqlsrv_query($conn, $query);
            if ($result === false) {// Error handling
                $this->formatErrors( sqlsrv_errors() );
                #die("Error 2");
            } 

            $rowdata=array();
            $contador = 0;
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $rowdata[$contador++] =$row;
            }
              
            return response()->json([
            'result' => $rowdata,
		]);

        }
        catch(\Exception $e){
            array_push($this->errors, $e->getMessage() );
        }
		
        return response()->json([
            'result' => $this->result,
		]);
    }
}

?>