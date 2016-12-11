<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

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
      $deptId = $request->route('deptId');
      return $this->deptPage($deptId);
    }

    function showCourse(Request $request) {
      $deptId = $request->route('deptId');
      $courseId = $request->route('courseId');
      return $this->coursePage($deptId, $courseId);
    }

    function addOffering(Request $request) {

      $deptId = $request->route('deptId');
      $courseId = $request->route('courseId');
      $facId = $request->route('facId');

      // Verify that faculty member and course belong to specified department
      $f = FacultyMember::where('faculty_members.id', $facId)
        ->join('users', function($join) {
          $join->on('faculty_members.user_id','=','users.id');
        })->first();
      if (!$f) {
        Session::flash('error', 'No such faculty member!');
        return $this->coursePage($deptId, $courseId);
      }

      $c = Course::find($courseId);
      if (!$c) {
        Session::flash('error', 'No such course!');
        return $this->coursePage($deptId, $courseId);
      }

      if ($c->department_id != $deptId || $f->department_id != $deptId) {
        // Error condition
        Session::flash('error', 'Specified course and faculty members must belong to specified department');
        return $this->coursePage($deptId, $courseId);
      }

      // Verify that this assignment won't exceed faculty assignment capacity
      // A faculty may not have more than three teaching assignments
      $q = 'select count(*) as assign_ct from course_offerings '
           .'where faculty_member_id=:facId';
      $r = DB::select(DB::raw($q), array('facId' => $facId));
      //dump($r);
      if ($r[0]->assign_ct > 2) {
        // Error condition
        Session::flash('error', 'Faculty member '.$f->name.' has exceeded the limit for teaching assignments');
        return $this->coursePage($deptId, $courseId);
      }

      // Get the instance number for the new offering;
      $q = 'select (ifnull(max(instance_number),0) + 1) '
          .'as num from course_offerings where course_id=:courseId';
      $r = DB::select(DB::raw($q), array('courseId' => $courseId));
      $instanceNum = $r[0]->num;

      $o = new CourseOffering();
      $o->course_id = $courseId;
      $o->faculty_member_id = $facId;
      $o->instance_number = $instanceNum;
      $o->save();

      return $this->coursePage($deptId, $courseId);
    }

    private function deptPage($deptId) {

      $validation = $this->validation ( array(
        'deptId' => $deptId
      ));

      if (!$validation['valid']) {
        Session::flash('error', $validation['msg']);
        return $this->listDepts();
      }

      $department = $validation['dept'];
      $department->load('courses.course_offerings');
      $courses = $department->courses->toArray();
      // Sort department courses on course code.
      uasort($courses, function($a, $b) {
        $a['course_code'] < $b['course_code'] ? -1 : 1;
      });

      return view('registrar.dept')
        ->with('dept', $department)
        ->with('courses', $courses);
    }

    private function coursePage($deptId, $courseId) {

      $v = $this->validation(array(
        'deptId'=>$deptId,
        'courseId'=>$courseId
      ));

      if (!$v['valid']) {
        Session::flash('error', $v['msg']);
        return $this->deptPage($deptId);
      }

      //$department = Department::find($deptId);
      $department = $v['dept'];
      //$course = Course::find($courseId);
      $course = $v['course'];
      $course->load('course_offerings');

      $faculty = FacultyMember::where('department_id','=',$deptId)
        ->join('users', function($join) {
          $join->on('faculty_members.user_id','=','users.id');
        })->get();

      return view('registrar.course')
        ->with('dept', $department)
        ->with('faculty', $faculty)
        ->with('course', $course);
    }

    private function check($result, $key, $message, $obj) {
      if (!$obj) {
        $result['valid'] = false;
        $result['msg'] = $message;
      } else {
        $result[$key] = $obj;
      }
      return $result;
    }

    private function num_check($result, $val, $label) {
      if (!is_numeric($val) || $val < 1) {
        $result['valid'] = false;
        $result['msg'] = $label.' must be a positive integer.';
      }
      return $result;
    }

    private function validation($params) {
      $result = array( 'valid' => true );
      if (array_key_exists('deptId', $params)) {
        $result = $this->num_check($result, $params['deptId'], 'Department ID');
        if (!$result['valid']) {
          return $result;
        }
        $department = Department::find($params['deptId']);
        $result = $this->check($result, 'dept', 'No such department!', $department);
        if (!$result['valid']) {
          return $result;
        }
      }

      if (array_key_exists('courseId', $params)) {
        $result = $this->num_check($result, $params['courseId'], 'Course ID');
        if (!$result['valid']) {
          return $result;
        }
        $course = Course::find($params['courseId']);
        $result = $this->check($result, 'course', 'No such course!', $course);
        if (!$result['valid']) {
          return $result;
        }
      }

      if (array_key_exists('facId', $params)) {
        $result = $this->num_check($result, $params['facId'], 'Faculty Member ID');
        if (!$result['valid']) {
          return $result;
        }
        $fac = FacultyMember::where('faculty_members.id', $facId)
          ->join('users', function($join) {
            $join->on('faculty_members.user_id','=','users.id');
          })->first();
        $result = $this->check($result, 'faculty', 'No such faculty member!', $fac);
        if (!$result['valid']) {
          return $result;
        }
      }
      return $result;
    }

}
