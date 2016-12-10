<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacultyMember extends Model
{
    public function user() {
      return $this->hasOne('App\User');
    }

    public function department() {
      return $this->belongsTo('App\Department');
    }

    public function course_offerings() {
      return $this->hasMany('App\CourseOffering');
    }
}
