<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    public function faculty_member() {
      return $this->belongsTo('App\FacultyMember');
    }

    public function course() {
      return $this->belongsTo('App\Course');
    }

    public function enrollments() {
      return $this->hasMany('App\Enrollment');
    }
}
