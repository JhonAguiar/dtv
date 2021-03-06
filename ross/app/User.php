<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    
    use Notifiable;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'role_id', 
        'group_id', 
        'country_code', 
        'username', 
        'name', 
        'lastname', 
        'fullname', 
        'email', 
        'avatar',
        'country',
        'language',
        'last_session'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function getAllUsers()
    {
        $dataUsers = \DB::table('users as a')
            ->leftjoin('roles as b', 'b.id', '=', 'a.role_id')
            ->select('a.id as user_id', 'a.role_id','b.name as rol','a.username','a.fullname','a.email','a.avatar','a.country','a.language','a.last_session')
            ->orderBy('a.fullname', 'asc')
            ->paginate(50);
            #->get();
            #->toSql(); 
            #dd($dataUsers);
        return $dataUsers;      
    }
}
