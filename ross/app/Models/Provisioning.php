<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provisioning extends Model
{
    
	protected $connection = 'db_provisioning';

    public static function getActiveProfiles(){
		return \DB::connection('db_provisioning')->select('
    		select description 
    		from provisioningnapi.speed_profile 
    		where active=1 and speed_profile_id<>:id', ['id'=>7]
    	);
    }

    
}
