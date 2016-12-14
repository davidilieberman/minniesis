<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Session;

use App\Department;
use App\Student;
use App\SISQueries;

class StudentController extends Controller
{
    function index(Request $request) {
      $id = $request->user()->id;
      $u = SISQueries::getStudentWithGPA($id);
      if (!$u) {
        Session:flash('error', 'No such student!');
        return $this->studentsIndex();
      }
      $enrollments = SISQueries::getStudentEnrollments($id);
      $depts = Department::where('id','>','0')->orderBy('dept_desc')->get();
      return view('student.home')
        ->with('student', $u[0])
        ->with('depts', $depts)
        ->with('enrollments',$enrollments);
    }

    function changeMajor(Request $request) {
      $deptId = $request->input("deptId");
      $d = Department::find($deptId);
      if (!$d) {
        Session::flash('error', 'No such department!');
        return $this->index($request);
      }
      $s = Student::find($request->user()->id);
      $s->department_id = $d->id;
      $s->save();
      Session::flash('success','Major changed!');
      return $this->index($request);
    }
}
