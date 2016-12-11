<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\Course;
use App\CourseOffering;
use App\FacultyMember;

use Illuminate\Support\Facades\DB;

class RegistrarController extends Controller
{
    function listDepts() {

      $q = 'select d.id, d.dept_code, d.dept_desc, '
            . 'count(c.id) as course_count, '
            . 'count(co.id) as offering_count '
            . 'from departments d '
            . 'join courses c on c.department_id = d.id '
            . 'left join course_offerings co on co.course_id = c.id '
            . 'group by d.id, d.dept_code, d.dept_desc '
            . 'order by d.dept_desc';

      $depts = DB::select(DB::raw($q));

      return view('registrar.depts')
        ->with('depts', $depts);

    }

    function showDept(Request $request) {
      //dump($request);
      $deptId = $request->route('deptId');
      $department = Department::find($deptId);
      $department->load('courses.course_offerings');
      $courses = $department->courses->toArray();
      uasort($courses, function($a, $b) {
        $a['course_code'] < $b['course_code'] ? -1 : 1;
      });

      return view('registrar.dept')
        ->with('dept', $department)
        ->with('courses', $courses);

    }

    function showCourse(Request $request) {
      $deptId = $request->route('deptId');
      $courseId = $request->route('courseId');

      $department = Department::find($deptId);
      $faculty = FacultyMember::where('department_id','=',$deptId)
        ->join('users', function($join){
          $join->on('faculty_members.id','=','users.id');
        })->get();
      $course = Course::find($courseId);
      $course->load('course_offerings');

      return view('registrar.course')
        ->with('dept', $department)
        ->with('faculty', $faculty)
        ->with('course', $course);
    }
}
