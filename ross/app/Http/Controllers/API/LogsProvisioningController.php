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
class LogsProvisioningController extends Controller
{
    protected $controller = '';
    protected $method = '';
    protected $video = '';
    protected $urlWsdl = '';
    protected $result = array();
    protected $errors = array();

    public function getLogsProvisioning(){
        try{
            $response = \DB::table('log_provisionings')->get();
        }catch(Exception $e){
            array_push($this->errors, "Error: ".$e->getMessage() );
        }
        return response()->json([
            'response' => $response,
            'error' => ''
        ])->header("Access-Control-Allow-Origin",  "*");
    }
    
    

}
