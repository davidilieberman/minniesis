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

      return $this->saveOffering($deptId, $courseId, $facId);
    }

    function storeOffering(Request $request) {
      $deptId = $request->input('deptId');
      $courseId = $request->input('courseId');
      $facId = $request->input('facId');

      return $this->saveOffering($deptId, $courseId, $facId);
    }

    private function saveOffering($deptId, $courseId, $facId) {
      $validation = $this->validation(array(
        'deptId'=>$deptId,
        'courseId'=>$courseId,
        'facId'=>$facId
      ));

      if (!$validation['valid']) {
        Session::flash('error', $validation['msg']);
        return $this->coursePage($deptId, $courseId);
      }


      // Verify that this assignment won't exceed faculty assignment capacity
      // A faculty may not have more than three teaching assignments
      $q = 'select count(*) as assign_ct from course_offerings '
           .'where faculty_member_id=:facId';
      $r = DB::select(DB::raw($q), array('facId' => $facId));
      $f = $validation['facultyMember'];
      if ($r[0]->assign_ct > 2) {
        Session::flash('error', 'Faculty member '.$f->name
          .' has exceeded the limit for teaching assignments');
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

    function updateOffering(Request $request) {
      $deptId = $request->input('deptId');
      $courseId = $request->input('courseId');
      $offeringId = $request->input('offeringId');

      $v = $this->validation(array(
        'deptId'=>$deptId,
        'courseId'=>$courseId,
        'offeringId'=>$offeringId
      ));

      $o = $v['offering'];
      $o->active = !$o->active;
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

      $department = $v['dept'];
      $course = $v['course'];
      $course->load('course_offerings');

      $q = 'select f.id, u.name, count(o.id) as assgn_ct '
            .'from faculty_members f '
            .'join users u on f.user_id = u.id '
            .'left join course_offerings o on o.faculty_member_id = f.id '
            .'and o.active = true '
            .'where f.department_id = :deptId '
            .'group by f.id, u.name '
            .'order by u.name';
      $faculty = DB::select(DB::raw($q), array('deptId' => $deptId));

      $fNames = array();
      $available = 0;
      foreach ($faculty as $f) {
        $fNames[$f->id] = $f->name;
        if ($f->assgn_ct < 3) $available += 1;
      }

      $q = 'select o.id, count(e.id) as enrl_ct '
            .'from course_offerings o '
            .'left join enrollments e on e.course_offering_id = o.id '
            .'where o.course_id=:courseId '
            .'group by o.id';
      $enrollments = DB::select(DB::raw($q), array('courseId' => $courseId));
      $enroll_counts = array();
      foreach ($enrollments as $e) {
        $enroll_counts[$e->id] = $e->enrl_ct;
      }

      return view('registrar.course')
        ->with('available', $available)
        ->with('dept', $department)
        ->with('faculty', $faculty)
        ->with('course', $course)
        ->with('enrollment_counts', $enroll_counts)
        ->with('faculty_names', $fNames);
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
        $fac = FacultyMember::where('faculty_members.id', $params['facId'])
          ->join('users', function($join) {
            $join->on('faculty_members.user_id','=','users.id');
          })->first();
        $result = $this->check($result, 'facultyMember', 'No such faculty member!', $fac);
        if (!$result['valid']) {
          return $result;
        }
      }

      if (array_key_exists('offeringId', $params)) {
        $result = $this->num_check($result, $params['offeringId'], 'Course Offering ID');
        if (!$result['valid']) {
          return $result;
        }
        $o = CourseOffering::find($params['offeringId']);
        $result = $this->check($result, 'offering', 'No such course offering!', $o);
        if (!$result['valid']) {
          return $result;
        }
      }

      // Ensure agreement across components
      $d = array_key_exists('dept', $result) ? $result['dept'] : false;
      $c = array_key_exists('course', $result) ? $result['course'] : false;
      $f = array_key_exists('facultyMember', $result) ? $result['facultyMember'] : false;
      $o = array_key_exists('offering', $result) ? $result['offering'] : false;
      if ($d) {
        if ($c && $d->id != $c->department_id) {
          $result['valid'] = false;
          $result['msg'] = 'Course '.$c->course_name.' is not offered by the '
            .$d->dept_desc.' department.';
          return $result;
        }
        if ($f && $d->id != $f->department_id) {
          $result['valid'] = false;
          $result['msg'] = 'Faculty Member '.$f->name.' does not teach in the '
            .$d->dept_desc.' department.';
          return $result;
        }
        if ($f && $c && $f->department_id != $c->department_id) {
          $result['valid'] = false;
          $result['msg'] = 'Faculty Member '.$f->name.' cannot be assigned to teach '
            .$c->course_name;
          return $result;
        }
      }

      // If course and faculty are both specified, they must belong to the
      // same department.
      if (array_key_exists('facultyMember', $result)
        && array_key_exists('course', $result)) {
          $f = $result['facultyMember'];
          $c = $result['course'];
          $d = $result['dept'];
          if ($f->department_id != $c->department_id) {
          $result['valid'] = false;
          $result['msg'] = 'Faculty Member '.$f->name.' may not be assigned to '
            .'courses in the '.$d->dept_desc.' department.';
        }
      }

      return $result;
    }

}
