<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public function faculty_members() {
      return $this->hasMany('App\Faculty');
    }

    public function courses() {
      return $this->hasMany('App\Course');
    }
}
