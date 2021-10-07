<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models;

/**
 * Esta clase se usa para el tratamiento de información de los usuarios del sistema.
 * @Autor <achavezb@directvla.com.co>
 */
class TaskProgController extends Controller
{

    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $urlWsdl = '';
    protected $result = array();
    protected $errors = array();

    public function insert(Request $request)
    {
        $val = $this->validacionesTask($request);
        if (count($val) > 0) {
            return response()->json([
                'respuesta' => 'error',
                'error' => $val
            ])->header("Access-Control-Allow-Origin",  "*");
        } else {

            $execType = $request->input('executionType');
            $perType = $request->input('perType');
            $periodicity = $request->input('periodicity');
            $execDateStart = $request->input('execIniDate') . ' ' . $request->input('execIniTime');
            $execDateEnd = $request->input('execEndDate') . ' ' . $request->input('execEndTime');
            $country = $request->input('country');
            $username = $request->input('username');
            $technology = $request->input('technology');
            $read = $request->input('read');
            $getParams = $request->input('getParams');
            $getLocation = $request->input('getLocation');
            $massiveFail = $request->input('massiveFail');

            //obtenemos el campo file definido en el formulario
            $file = $request->file('file');
            //obtenemos el nombre del archivo
            $nombre = $file->getClientOriginalName();

            $s = explode(".", $nombre);
            $nombre = $s[0];
            //indicamos que queremos guardar un nuevo archivo en el disco local
            $archivo = $request->file('file')->store('storage');
            try {
                if ($execType == 1) {
                    \DB::table('ejecuciones')->insert([
                        'name_file' => $nombre,
                        'executation_date' => $execDateStart,
                        'executation_type' => $execType,
                        'file_ubication' => $archivo,
                        'periodicity_type' => $perType,
                        'periodicity' => $periodicity != '' ? $periodicity : 0,
                        'country' => $country,
                        'username' =>  $username,
                        'final_date' => $execDateEnd,
                        'technology' => $technology,
                        'read' => intval($read == "true"),
                        'getParams' => intval($getParams == "true"),
                        'getLocation' => intval($getLocation == "true"),
                        'massiveFail' => intval($massiveFail == "true"),
                        'status' => 0
                    ]);
                } else {
                    \DB::table('ejecuciones')->insert([
                        'name_file' => $nombre,
                        'executation_date' => $execDateStart,
                        'executation_type' => $execType,
                        'file_ubication' => $archivo,
                        'periodicity_type' => 0,
                        'periodicity' => $periodicity != '' ? $periodicity : 0,
                        'country' => $country,
                        'username' =>  $username,
                        'technology' => $technology,
                        'read' => intval($read == "true"),
                        'getParams' => intval($getParams == "true"),
                        'getLocation' => intval($getLocation == "true"),
                        'massiveFail' => intval($massiveFail == "true"),
                        'status' => 0
                    ]);
                }

                return response()->json([
                    'respuesta' => "ok"
                ])->header("Access-Control-Allow-Origin",  "*");
            } catch (Exception $e) {

                array_push($this->errors, "Error: " . $e->getMessage());
                return response()->json([
                    'error' => $this->errors
                ])->header("Access-Control-Allow-Origin",  "*");
            }
        }
    }

    public function validacionesTask(Request $request)
    {

        $array = [];

        //Verifica que no existan mas de dos procesos por usuario en la fecha actual actual
        $maxProcesosUsuario = \DB::table('ejecuciones')
        ->select("username")
        ->whereRaw("(NOW() between executation_date and final_date or (executation_type = 2 and date(executation_date) = curdate()))")
        ->where('username', $request->input('username'))
        ->where('status', 0)
        ->groupBy('username')
        ->havingRaw('count(*) >= ?', [2])
        ->first();

        if ($maxProcesosUsuario != NULL) {
            array_push($array, "Supera el maximo de procesos, 2 por usuario al dia");
        }

        $request->input('executionType') == '' ? array_push($array, 'Debe seleccionar un tipo de ejecución') : '';
        $request->input('execIniDate') == '' ? array_push($array, 'Debe seleccionar una fecha inicial') : '';
        $request->input('execIniTime') == '' ? array_push($array, 'Debe seleccionar una Hora inicial') : '';

        ($request->input('executionType') == 1 && $request->input('perType') == '') ? array_push($array, 'Debe seleccionar un tipo de periodicidad') : '';
        ($request->input('executionType') == 1 && $request->input('periodicity') == '') ? array_push($array, 'Debe seleccionar la frecuencia con la que se realizara la tarea') : '';
        ($request->input('executionType') == 1 && $request->input('execEndDate') == '') ? array_push($array, 'Debe seleccionar una fecha Final') : '';
        ($request->input('executionType') == 1 && $request->input('execEndTime') == '') ? array_push($array, 'Debe seleccionar una Hora Final') : '';

        ($request->input('country') == 'ARG' && $request->input('technology') == '') ? array_push($array, 'Debe seleccionar una Tecnología') : '';

        empty($request->hasFile('file')) ? array_push($array, 'Debe seleccionar un archivo para iniciar la tarea') : '';

        //Validación tipo archivo
        if (!empty($request->hasFile('file'))) {
            $file = $request->file('file');

            //obtenemos el nombre del archivo
            $nombre = $file->getClientOriginalName();
            $info = explode(".", $nombre);


            $ext = mb_strtolower(trim(array_pop($info)));

            if ($ext != 'csv') {
                array_push($array, 'El tipo de archivo no es valido, por favor seleccione un archivo .csv');
            }
        }

        return $array;
    }
}
