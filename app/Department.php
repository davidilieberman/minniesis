<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public function users() {
      return $this->belongsToMany('App\Faculty')->withTimestamps();
    }
}
