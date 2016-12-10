<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public function student() {
      return $this->belongsTo('App\Student');
    }

    public function course_offering() {
      return $this->belongsTo('App\CourseOffering');
    }
}
