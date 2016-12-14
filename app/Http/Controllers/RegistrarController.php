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
use App\SISQueries;

class RegistrarController extends Controller
{

    function studentsIndex() {
      return view('registrar.students')
        ->with('students', SISQueries::getGPAs());
    }

    function showStudent(Request $request) {
      $studentUserId = $request->route('studentUserId');
      $u = User::find($studentUserId);
      if (!$u) {
        Session:flash('error', 'No such student!');
        return $this->studentsIndex();
      }
      $enrollments = SISQueries::getStudentEnrollments($studentUserId);
      return view('registrar.student')
        ->with('student', $u)
        ->with('enrollments',$enrollments);
    }

    function deptsIndex() {
      return view('registrar.depts')
        ->with('depts', SISQueries::getDepartments());
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
      $fp = User::find($f->id);
      $f['person'] = $fp;
      $d = Department::find($c->department_id);

      $e = SISQueries::getOfferingEnrollments($offeringId);

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
        $r = SISQueries::countAssignmentsForOfferingInstructor($offeringId);
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
      // Search excludex students enrolled in any other offering of the same course
      $r = SISQueries::searchByStudentNameForEnrollment($name, $o);
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

      // Verify that the offering exists and that the student exists and isn't
      // already enrolled in the course
      $r = SISQueries::validateEnrollmentRequest($o, $studentId);

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
      $credits = SISQueries::getStudentEnrollmentCredits($studentId);
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

      //$students = SISQueries::getDeptStudents($deptId);
      //dump($students);

      return view('registrar.dept')
        ->with('faculty', SISQueries::getDeptFaculty($deptId))
        ->with('students', SISQueries::getDeptStudents($deptId))
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

      $faculty = SISQueries::getDeptFaculty($deptId);

      $fNames = array();
      $available = 0;
      foreach ($faculty as $f) {
        $fNames[$f->id] = $f->name;
        if ($f->assgn_ct < 3) $available += 1;
      }

      $enrollments = SISQueries::getCourseEnrollmentsCount($courseId);
      $enroll_counts = array();
      foreach ($enrollments as $e) {
        $enroll_counts[$e->id] = $e->enrl_ct;
      }

      $go = SISQueries::getGradedEnrollmentCounts($courseId);
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
      $assignments = SISQueries::countFacultyAssignments($facId);
      $f = $validation['facultyMember'];
      if ($assignments[0]->assign_ct > 2) {
        Session::flash('error', 'Faculty member '.$f->name
          .' has exceeded the limit for teaching assignments');
        return $this->coursePage($deptId, $courseId);
      }

      // Get the instance number for the new offering;
      $instanceNum = SISQueries::getCourseOfferingInstanceNumber($courseId);

      $o = new CourseOffering();
      $o->course_id = $courseId;
      $o->faculty_member_id = $facId;
      $o->instance_number = $instanceNum;
      $o->save();

      return $this->coursePage($deptId, $courseId);
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
            $join->on('faculty_members.id','=','users.id');
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
