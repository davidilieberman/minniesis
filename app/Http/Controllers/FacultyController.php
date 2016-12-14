<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

use App\CourseOffering;
use App\Enrollment;
use App\Grade;
use App\SISQueries;

class FacultyController extends Controller
{
    function index() {
      return view('faculty.home')
        ->with('offerings',
          SISQueries::getFacultyTeachingAssignments(Auth::user()->id));
    }

    function showOffering(Request $request) {
      // Validation: received a valid course offering id
      $offeringId = $request->route('offeringId');
      //$o = CourseOffering::find($offeringId);
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
