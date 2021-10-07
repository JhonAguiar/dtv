<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogProvisioning extends Model
{
    protected $table = 'log_provisionings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'searchdate', 
        'secondssearch', 
        'searchmethod', 
        'searchuser', 
        'searchimsi', 
        'technology', 
        'profile',
        'searchcountry', 
        'searchresponse', 
        'searchtype',
        'searchfile'
    ];
}
