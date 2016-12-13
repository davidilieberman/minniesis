<?php

namespace App\Http\Controllers;

define("MAX_CREDITS",     9.0);
define("MAX_ASSIGNMENTS", 3);

use Illuminate\Http\Request;
use Session;

use App\Department;
use App\Course;
use App\CourseOffering;
use App\Enrollment;
use App\FacultyMember;
use App\User;

use Illuminate\Support\Facades\DB;

class RegistrarController extends Controller
{
    function deptsIndex() {
      return view('registrar.depts')
        ->with('depts', $this->getDepartments());
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

    function storeOffering(Request $request) {
      $deptId = $request->input('deptId');
      $courseId = $request->input('courseId');
      $facId = $request->input('facId');

      return $this->saveOffering($deptId, $courseId, $facId);
    }

    function showOffering(Request $request) {

      $offeringId = $request->route('offeringId');

      // If we received a bad course offering id, error up to the departments index
      $o = CourseOffering::where([
          ['id', $offeringId],
          ['active', true]
        ])->first();
      if (!$o) {
        Session::flash('error', 'No matching active course offering!');
        return $this->deptsIndex();
      }

      // Climb the tree to load the offering's components
      $c = Course::find($o->course_id);
      $f = FacultyMember::find($o->faculty_member_id);
      $fp = User::find($f->user_id);
      $f['person'] = $fp;
      $d = Department::find($c->department_id);

      $e = $this->getOfferingEnrollments($offeringId);

      // If there are student search results, push them into the view
      $sr = false;
      $term = false;
      if (Session::has('student_search_results')) {
        $sr = Session::pull('student_search_results');
        $term = Session::pull('search_term');
      }

      return view('registrar.offering')
        ->with('search_term', $term)
        ->with('search_results', $sr)
        ->with('dept', $d)
        ->with('course', $c)
        ->with('faculty_member', $f)
        ->with('offering', $o)
        ->with('enrollments', $e);
    }

    /**
    * The only we change we support to a defined CourseOffering is to flip its status from active to inactive
    * or vice-versa.
    */
    function updateOffering(Request $request) {
      $deptId = $request->input('deptId');
      $courseId = $request->input('courseId');
      $offeringId = $request->input('offeringId');

      $v = $this->validation(array(
        'deptId'=>$deptId,
        'courseId'=>$courseId,
        'offeringId'=>$offeringId
      ));
      if (!$v['valid']) {
        Session::flash('error', $v['msg']);
        return $this->coursePage($deptId, $courseId);
      }
      $o = $v['offering'];

      $c = 0;
      if (!$o->active) {
        // Verify that activating this offering won't put the
        // faculty member over the assignment limit
        $q = 'select count(o.id) as assign_ct from course_offerings o '
              .'where o.active = true '
              .'and o.faculty_member_id = '
              .'(select faculty_member_id from course_offerings where id = :courseOfferingId)';
        $r = DB::select(DB::raw($q), array('courseOfferingId'=>$offeringId));
        if ($r[0]->assign_ct > 2) {
          Session::flash('error', 'Reactiving this offering would cause the faculty member '
            .'to exceed the limit for teaching assignments');
          return $this->coursePage($deptId, $courseId);
        }
      } else {
        $c = Enrollment::where('course_offering_id', $offeringId)->delete();
      }

      if ($c > 0) {
        // We are about to deactive the course; unenroll its students
        Session::flash('success','Course offering deactivated; '.$c.' students unenrolled.');
      }
      $o->active = !$o->active;
      $o->save();

      return $this->coursePage($deptId, $courseId);

    }

    function searchStudents(Request $request) {

      $offeringId = $request->route('offeringId');
      $o = CourseOffering::find($offeringId);

      $name = $request->input('name');
      if (!$name) {
        Session::flash('error', 'Please supply a name to search!');
        return $this->showOffering($request);
      }
      // Exclude students enrolled in any other offering of the same course
      $q = 'select s.id as student_id, s.year, u.name, u.email '
            .'from students s, users u where s.user_id = u.id '
            .'and lower(u.name) like :name '
            .'and s.id not in '
                .'(select student_id from enrollments where course_offering_id in '
                  .'(select id from course_offerings where course_id = :courseId)'
                .') '
            .'order by u.name';
      $r = DB::select(DB::raw($q), array(
        'name' => '%'.strtolower($name).'%',
        'courseId' => $o['course_id']
      ));
      Session::flash('student_search_results', $r);
      Session::flash('search_term', $name);
      return $this->showOffering($request);

    }

    function enrollStudent(Request $request) {

      $offeringId = $request->route('offeringId');
      $o = CourseOffering::find($offeringId);
      $this->validate($request, [
        'studentId' => 'required|numeric|min:1'
      ]);

      $studentId = $request->input('studentId');

      // Verify that the offering exists and that the student exists and isn't already enrolled in the course
      $q = 'select '
              .'(select count(id) from course_offerings where id=:offeringId) as offering_exists, '
              .'(select count(id) from students where id=:studentId) as student_exists, '
              .'(select count(id) from enrollments where student_id=:studentId_2 and course_offering_id in ('
                .'select id from course_offerings where course_id = :courseId'
              .')) as enrolled '
              .'from dual';
      $r = DB::select(DB::raw($q), array(
        'offeringId'=> $offeringId,
        'studentId' => $studentId,
        'studentId_2' => $studentId,
        'courseId' => $o['course_id']
      ));

      if (!$r[0]->offering_exists) {
        Session::flash('error', 'Received request to enroll a student in a non-existing course offering!');
        return $this->deptsIndex();
      }

      if (!$r[0]->student_exists) {
        Session::flash('error', 'Received request to enroll a non-existing student in this course offering!');
        return $this->showOffering($request);
      }

      if ($r[0]->enrolled) {
        Session::flash('error', 'The specified student is already in enrolled in this course offering!');
        return $this->showOffering($request);
      }

      // Verify that enrollment would not push student beyond
      // enrollment limit of 9 credits
      $credits = $this->getEnrollmentCredits($studentId);
      $courseCredit = Course::where('id',$o['course_id'])->pluck('credits')->first();
      if ($credits + $courseCredit > MAX_CREDITS) {
        Session::flash('error', 'Enrollment would place student over the maximum allowable credit limit.');
      } else {
        $e = new Enrollment();
        $e->student_id = $studentId;
        $o->enrollments()->save($e);
        Session::flash('success', 'Student enrolled in course offering');
      }
      return $this->showOffering($request);
    }

    function unenrollStudent(Request $request) {
      $offeringId = $request->route('offeringId');
      $this->validate($request, [
        'studentId' => 'required|numeric|min:1'
      ]);
      $studentId = $request->input('studentId');
      $enr = Enrollment::where([
          ['student_id', $studentId],
          ['course_offering_id', $offeringId]
        ])->first();
      if ($enr['grade']) {
        Session::flash('error', 'Student has been graded for this course offering and cannot be withdrawn');
      } else {
        $d = Enrollment::where([
             ['student_id', $studentId],
             ['course_offering_id', $offeringId]
        ])->delete();
        Session::flash('success', 'Student withdrawn from course offering');
      }
      return $this->showOffering($request);
    }


    private function deptPage($deptId) {

      $validation = $this->validation ( array(
        'deptId' => $deptId
      ));

      if (!$validation['valid']) {
        Session::flash('error', $validation['msg']);
        return $this->deptsIndex();
      }

      $department = $validation['dept'];
      $department->load('courses.course_offerings');
      $courses = $department->courses->toArray();
      // Sort department courses on course code.
      uasort($courses, function($a, $b) {
        $a['course_code'] < $b['course_code'] ? -1 : 1;
      });

      $c = array();
      foreach($courses as $course) {
        $offerings = array_filter($course['course_offerings'], function($co) {
          return $co['active'];
        });
        $course['active_offerings'] = $offerings;
        array_push($c, $course);
      }

      return view('registrar.dept')
        ->with('faculty', $this->getDeptFaculty($deptId))
        ->with('dept', $department)
        ->with('courses', $c);
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

      $faculty = $this->getDeptFaculty($deptId);

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

      $go = $this->getGradedEnrollmentCounts($courseId);
      return view('registrar.course')
        ->with('available', $available)
        ->with('dept', $department)
        ->with('faculty', $faculty)
        ->with('course', $course)
        ->with('enrollment_counts', $enroll_counts)
        ->with('graded_offerings', $go)
        ->with('faculty_names', $fNames);
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
           .'where faculty_member_id=:facId '
           .'and active=true';
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


    private function getOfferingEnrollments($offeringId) {
      $q = 'select u.name, s.id as student_id, s.year, u.email, u.id, g.grade '
          .'from users u join students s on s.user_id = u.id '
          .'join enrollments e on e.student_id = s.id '
          .'left join grades g on e.grade_id = g.id '
          .'where e.course_offering_id = :offeringId '
          .'order by u.name';
      $e = DB::select(DB::raw($q), array(
        'offeringId'=>$offeringId
      ));
      return $e;
    }

    private function getGradedEnrollmentCounts($courseId) {
        $q = 'SELECT o.id as course_offering_id, COUNT(e.id) as graded '
        .'FROM course_offerings o LEFT JOIN enrollments e '
        .'ON e.course_offering_id = o.id AND e.grade_id IS NOT NULL '
        .'WHERE o.course_id = :courseId GROUP BY o.id';
        $r = DB::select(DB::raw($q), array('courseId' => $courseId));
        $arr = array();
        foreach ($r as $result) {
          $arr[$result->course_offering_id] = $result->graded;
        }
        return $arr;
    }

    private function getEnrollmentCredits($studentId) {
      $q = 'SELECT ifnull(sum(c.credits),0) as total FROM courses c, course_offerings o, enrollments e '
        .'WHERE c.id = o.course_id AND o.id = e.course_offering_id AND e.student_id = :studentId';
      $r = DB::select(DB::raw($q), array('studentId' => $studentId));
      return $r[0]->total;
    }

    private function getDepartments() {
      $q = 'SELECT d.id, d.dept_code, d.dept_desc, '
            . 'count(c.id) as course_count, '
            . 'count(co.id) as offering_count '
            . 'FROM departments d '
            . 'join courses c on c.department_id = d.id '
            . 'left join course_offerings co on co.course_id = c.id '
            . 'group by d.id, d.dept_code, d.dept_desc '
            . 'order by d.dept_desc';

      return DB::select(DB::raw($q));
    }

    private function getDeptFaculty($deptId) {
      $q = 'select f.id, u.name, u.email, count(o.id) as assgn_ct '
            .'from faculty_members f '
            .'join users u on f.user_id = u.id '
            .'left join course_offerings o on o.faculty_member_id = f.id '
            .'and o.active = true '
            .'where f.department_id = :deptId '
            .'group by f.id, u.name '
            .'order by u.name';
      $faculty = DB::select(DB::raw($q), array('deptId' => $deptId));
      return $faculty;
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

    /**
    * Custom validation to reconcile the input components associated with
    * with Registrar activities.
    */
    private function validation($params) {
      $result = array( 'valid' => true );

      // We were passed a department ID. If this is not valid, we need to
      // return to the department selection page.
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
