<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemsController extends Controller
{
    public function getServers(){
        return response()->json([
            'server' => [
            ['ip' => '10.165.1.24', 'name' => 'Provisioning front prd', 'status' => 'active'],
            ['ip' => '10.165.1.4', 'name' => 'Provisioning db dev', 'status' => 'active'],
            ['ip' => '10.165.1.6', 'name' => 'Provisioning front dev', 'status' => 'active'],
            ['ip' => '10.165.1.216', 'name' => 'Radius db', 'status' => 'active'],
            ['ip' => '172.31.152.17', 'name' => 'Otrs db', 'status' => 'active'],
            ['ip' => '172.20.163.84', 'name' => 'IBS', 'status' => 'active'],
            ['ip' => '10.165.1.25', 'name' => 'Portal cautivo', 'status' => 'active'],
            ]
        ]); 
    }

    public function checkSystems( Request $request ){
    	# https://alvinalexander.com/php/php-ping-scripts-examples-ping-command
    	set_time_limit(180);
    	ini_set('memory_limit', '256M');
        if( $request->isMethod('post') ) {
            try{
		        $collection = self::servers();
            	$inputs = $request->All();
            	if ( isset($inputs['servers']) and is_array($inputs['servers']) and !empty($inputs['servers']) ) {
			        foreach ($inputs['servers'] as $key => $ipAddress) {
						$timeA = microtime(true);
						$server = collect( $collection )->firstWhere('ip', $ipAddress);
						$this->servers[$key]['ip'] = 'IP: '.$ipAddress;
						$this->servers[$key]['name'] = mb_strtoupper($server['name']);
						$ch = curl_init( $ipAddress );
						curl_setopt($ch, CURLOPT_TIMEOUT, 5);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$data = curl_exec($ch);
						$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						curl_close($ch);
						if($httpcode>=200 && $httpcode<300){
							$this->servers[$key]['status'] = 'Worked';
						} else {
							$this->servers[$key]['status'] = "No work";
						}
						$timeB = microtime(true);
						$time = $timeB - $timeA;
						$this->servers[$key]['time'] = 'Time: '.round($time,3).' Sg.';
			        }
            	}else{
            		array_push($this->errors, trans('messages.000094'));
            	}
            }catch(Exception $e){
                array_push($this->errors, "Error: ".$e->getMessage() );
            }
        }else{
            array_push($this->errors, trans('messages.000042'));
        }
		#print_r($this->servers);
		#print_r($this->errors);
		#die;
        return response()->json([
            'result' => $this->servers,
            'errors' => $this->errors
        ]);
    }
}

?>