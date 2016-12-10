<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function department() {
      return $this->belongsTo('App\Department');
    }

    public function course_offerings() {
      return $this->hasMany('App\CourseOffering');
    }
}
