<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function sis_role() {
      return $this->belongsTo('App\SisRole');
    }

    public function departments() {
      //if($this->role->role_code == 'FAC') {
        return $this->belongsToMany('App\Department')->withTimestamps();
      //}
      //return false;
    }
}
