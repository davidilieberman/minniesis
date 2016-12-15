<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

use App\Course;
use App\CourseOffering;
use App\Department;
use App\Enrollment;
use App\FacultyMember;
use App\Grade;
use App\SISQueries;

define ('CREDIT_OPTIONS', [1.5, 3.0, 4.0]);
define ('CAPACITY_MIN', 4);
define ('CAPACITY_MAX', 15);


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

    /**
    * The faculty chair may change a course's enrollment capacity.
    */
    function updateCourse(Request $request) {
      // Validation:
      // course exists
      $c = Course::find($request->input('course_id'));
      if (!$c) {
        Session::flash('error', 'No such course!');
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
      $c->capacity = $capacity;
      $c->save();

      Session::flash('success', 'Course successfully updated.');
      return $this->index();

    }

    function cancelCourse(Request $request) {
      // Validation:
      // course exists
      $c = Course::find($request->input('courseId'));
      if (!$c) {
        Session::flash('error', 'No such course!');
        return $this->index();
      }

      // faculty member is department chair
      $f = FacultyMember::find(Auth::user()->id);
      if (!$f or !$f->chair or !$f->department_id == $c->department_id) {
        Session::flash('error', 'You are not authorized to cancel this course.');
        return $this->index();
      }

      $c->available = false;
      $c->save();

      Session::flash('success', 'Course successfully canceled.');
      return $this->index();



    }

    function storeCourse(Request $request) {
      //Validation:

      // faculty is dept chair
      $f = FacultyMember::find(Auth::user()->id);
      if (!$f->chair) {
        Session::flash('error', 'You are not authorized to create new courses!');
        return $this->index();
      }

      //credits is one of 1.5, 3.0, 4.0
      $credits = $request->input('credits');
      if (!$credits or !in_array($credits, CREDIT_OPTIONS)) {
        Session::flash('error', 'Received invalid input for course credits.');
        return $this->index();
      }

      // code is 3 chars and is unique for dept, or is only
      // assigned to canceled courses
      $code = $request->input('code');
      if (!$code or !preg_match('/^\d{3}$/', $code)) {
        Session::flash('error', 'Course code must be a string of three numbers.');
        return $this->index();
      }

      //$collisions = Course::where(['course_code', $code], ['available',true])->get();
      $collisions = SISQueries::countCodeMatches($code, $f->department_id);
      if ($collisions > 0) {
        Session::flash('error', 'Course code "'.$code
          .'" is already assigned to an available course; the existing course must '
          .' be canceled before its code can be re-used.');
        return $this->index();
      }

      // name is not null and is unique for dept available course_offerings
      $name = $request->input('name');
      if (!$name or strlen($name) == 0) {
        Session::flash('error', 'Course name is required');
        return $this->index();
      }

      //$collisions = Course::where(['course_name', $name], ['available', true])->get();
      $collisions = SISQueries::countCourseNameMatches($name, $f->department_id);
      if ($collisions > 0) {
        Session::flash('error', 'Course name "'.$name.'" is aleady assigned to '
          .'an available course; the existing course must be canceled before '
          .'its name can be re-used.');
        return $this->index();
      }

      // capacity is in in range 4-15
      $capacity = $request->input('capacity');
      if (!is_numeric($capacity)
        or $capacity<CAPACITY_MIN or $capacity>CAPACITY_MAX) {
          Session::flash('error', 'Course capacity must be in the range '
            .CAPACITY_MIN. ' - ' .CAPACITY_MAX .'.');
          return $this->index();
        }


      $d = Department::find($f->department_id);
      $course = new Course();
      $course->course_code = $code;
      $course->course_name = $name;
      $course->credits = $credits;
      $course->capacity = $capacity;
      $d->courses()->save($course);

      Session::flash('success', 'Course created!');
      return $this->index();
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
