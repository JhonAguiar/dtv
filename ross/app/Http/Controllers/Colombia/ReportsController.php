<?php

namespace App\Http\Controllers\Colombia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{

	protected $sqlbogServername;
	protected $sqlbogDatabase;
	protected $sqlbogUsername;
	protected $sqlbogPassword;
    protected $result = array();
    protected $errors = array();


    # -----------------------------------------------------------------------------
	public function __construct() {
		$this->middleware('auth');
		$this->sqlbogServername = config('appross.sqlbog_servername', '');
		$this->sqlbogDatabase = config('appross.sqlbog_database', '');
		$this->sqlbogUsername = config('appross.sqlbog_username', '');
		$this->sqlbogPassword = config('appross.sqlbog_password', '');
	}


	# -----------------------------------------------------------------------------
	public function graphics( Request $request ){
		set_time_limit(180);
		if( $request->isMethod('post') ) {
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
						$fecha = (array)$row['FechaEvento'];
					 	$fecha = substr($fecha['date'], 0, 10);
						
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
		
				$perfiles=array();
				$query ="select DISTINCT b.Perfil from tbHistorico as a left join tbHistoricoProvisioning as b on a.CodHistorico=b.CodHistoricoProvisioning where (FechaEvento BETWEEN '".$dateIni."' AND '".$dateFin."')";
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
			}catch(\Exception $e){
				array_push($this->errors, $e->getMessage() );
			}
		}else{
			array_push($this->errors, trans('messages.000042'));
		}
		return response()->json([
			'result' => $this->result,
			'errors' => $this->errors
		]);


	}


	public function formatErrors($errors){
	    foreach ($errors as $error) {
	        $error = "SQLSTATE: ". $error['SQLSTATE'] ." Code: ". $error['code']." Message: ".$error['message'] . "<br/>";
	        array_push($this->errors, $error );
	    }
	}
}
