<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

use App\Course;
use App\CourseOffering;
use App\Enrollment;
use App\FacultyMember;
use App\Grade;
use App\SISQueries;

define ('CREDIT_OPTIONS', [1.5, 3.0, 4.0]);


class FacultyController extends Controller
{
    function index() {
      $u = Auth::user();
      $f = FacultyMember::find($u->id);
      //$opts = array('1.5', '3.0', '4.0');
      return view('faculty.home')
        ->with('offerings',
          SISQueries::getFacultyTeachingAssignments($u->id))
        ->with('facultyMember', $f)
        ->with('creditOptions', CREDIT_OPTIONS)
        ->with('courses', Course::where('department_id', $f->department_id)
          ->orderBy('course_code')->get());
    }

    function updateCourse(Request $request) {
      // Validation:
      // course exists
      $c = Course::find($request->input('course_id'));
      if (!$c) {
        Session::flash('error', 'No such course!');
        return $this->index();
      }
      // credit is one of 1.5, 3.0, 4.0
      $credits = $request->input('credits');
      if (!in_array($credits, CREDIT_OPTIONS)) {
        Session::flash('error', 'Received in invalid number of credits for course.');
        return $this->index();
      }

      // capacity is int in range 4-15
      $capacity = $request->input('capacity');
      if (!is_numeric($capacity) or $capacity < 4 or $capacity > 15) {
        Session::flash('error',
          'Received invalid input "'.$capacity.'" for course enrollment capacity.');
        return $this->index();
      }

      $f = FacultyMember::find(Auth::user()->id);
      if ($c->department_id != $f->department_id or !$f->chair) {
        Session::flash('error', 'You are not authorized to manage the requested course.');
        return $this->index();
      }

      // faculty is chair of course's department

      $c->credits = $credits;
      $c->capacity = $capacity;
      $c->save();

      Session::flash('success', 'Course successfully updated.');
      return $this->index();

    }

    function storeCourse(Request $request) {
      //Validation:
      //credits is one of 1.5, 3.0, 4.0

      // code is unique for dept

      //capcity is int in range 4-15

      //faculty is dept chair
    }

    function showOffering(Request $request) {
      // Validation: received a valid course offering id
      $offeringId = $request->route('offeringId');
      $o = SISQueries::getOffering($offeringId)[0];
      if (!$o) {
        Session::flash('error', 'No such offering!');
        return $this->index();
      }

      // Validation: faculty members is session is the offering instructor
      if ($o->faculty_member_id != Auth::user()->id) {
        Session::flash('error', 'You are not the instructor of the requested offering!');
        return $this->index();
      }

      // Get the enrollment of the offering and the table of grades
      $grades = Grade::where('score','>',0.0)->orderBy('score', 'desc')->get();
      return view('faculty.offering')
        ->with('offering', $o)
        ->with('enrollments', SISQueries::getOfferingEnrollments($offeringId))
        ->with('grades', $grades);
    }

    function updateEnrollmentGrade(Request $request) {
      // Validation: enrollment must exist, and faculty member must be
      // the assigned offering instructor
      $enrlId = $request->input('enrollmentId');
      $offeringId = $request->route('offeringId');

      $e = Enrollment::find($enrlId);
      if (!$e or $e->course_offering_id != $offeringId) {
        Session::flash('error', 'No matching enrollment!');
        return $this->showOffering($request);
      }

      $g = Grade::find($request->input('gradeId'));
      if (!$g) {
        Session::flash('error', 'Received invalid grade');
        return $this->showOffering($request);
      }

      $e->grade_id = $g->id;
      $e->save();
      Session::flash('success', 'Student grade entered');
      return $this->showOffering($request);
    }
}
