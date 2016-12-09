<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public function faculty() {
      return $this->belongsToMany('App\User')->withTimestamps();
    }
}
