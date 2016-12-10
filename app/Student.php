<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
  public function user() {
    return $this->hasOne('App\User');
  }

  public function enrollments() {
    return $this->hasMany('App\Enrollment');
  }
}
