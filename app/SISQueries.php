<?php

namespace App;

use Illuminate\Support\Facades\DB;

/**
* Working with Eloquent reminded me of what it is I've always disliked about
* ORM solutions: the attempt to "simplify" away the complexities of SQL
* often results in an equally impenetrable, highly idiosyncratic DSL.
*  More often not not, the hoops required to get the DSL to perform joins
* or perform aggregation functions when I have solid command of the SQL
* necessary to perform the task at hand strongly dispose me to go back to
* the language designed and refined by data engineers over a period of decades
* as the best tool for the job. So here we are.
*/
class SISQueries  {

  public static function getStudentWithGPA($studentId) {
    $q = SISQueries::studentQueryBase().
      "where u.id = :studentId
        group by u.id, u.email, u.name, d.id, d.dept_desc, d.dept_code";
    return DB::select(DB::raw($q), array('studentId' => $studentId));
  }

  public static function getDeptStudents($deptId) {
    $q = SISQueries::studentQueryBase().
            "where s.department_id = :deptId
            group by u.id, u.name, u.email, u.name, d.id, d.dept_desc, d.dept_code
            order by u.name";
    return DB::select(DB::raw($q), array('deptId' => $deptId));
  }

  private static function studentQueryBase() {
    return "SELECT u.id, u.email, u.name, d.id as department_id,
              d.dept_desc, d.dept_code,
            (sum(g.score * c.credits) / sum(c.credits)) gpa
            from users u
            join students s on u.id = s.id
            join departments d on d.id = s.department_id
            left join enrollments e on e.student_id = s.id
            left join grades g on e.grade_id = g.id
            left join course_offerings o on e.course_offering_id = o.id
            left join courses c on o.course_id = c.id ";
  }

  private static function offeringQueryBase() {
    return "SELECT
            o.id,
            o.course_id,
            o.instance_number,
            o.faculty_member_id,
            c.course_name,
            c.course_code,
            c.available,
            d.dept_code,
            d.id as department_id,
            count(distinct e.id) as enrl_cnt
        FROM
            course_offerings o,
            courses c,
            departments d,
            enrollments e,
            students s
        WHERE
            d.id = c.department_id
        AND c.id = o.course_id
        AND o.active=true
        AND e.course_offering_id = o.id ";
  }

  public static function getStudentEnrollmentCredits($studentId) {
    $q = "SELECT ifnull(sum(c.credits),0) as total
            FROM courses c, course_offerings o, enrollments e
            WHERE c.id = o.course_id
            AND o.id = e.course_offering_id
            AND e.student_id = :studentId";
    $r = DB::select(DB::raw($q), array('studentId' => $studentId));
    return $r[0]->total;
  }

  public static function getStudentEnrollments($studentUserId) {
    $q = "SELECT d.dept_code, c.course_code, c.course_name,
              d.id as dept_id,
              c.credits, g.grade, f.name as instructor
            from users u join students s on s.id = u.id
            join enrollments e on e.student_id = s.id
            join course_offerings o on e.course_offering_id = o.id
            join courses c on c.id = o.course_id
            join departments d on d.id = c.department_id
            join faculty_members fm on fm.id = o.faculty_member_id
            join users f on fm.id = f.id
            left join grades g on g.id = e.grade_id
            where u.id = :studentUserId order by d.dept_code, c.course_code";
    return DB::select(DB::raw($q), array('studentUserId' => $studentUserId));
  }

  public static function getOfferingEnrollments($offeringId) {
    $q = "SELECT
            u.name,
            s.id as student_id,
            e.id as enrollment_id,
            s.year,
            u.email,
            u.id,
            g.grade,
            g.id as grade_id,
            s.department_id
          from users u join students s on s.id = u.id
          join enrollments e on e.student_id = s.id
          left join grades g on e.grade_id = g.id
          where e.course_offering_id = :offeringId
          order by u.name";
    $e = DB::select(DB::raw($q), array(
      'offeringId'=>$offeringId
    ));
    return $e;
  }

  public static function validateEnrollmentRequest($offering, $studentId) {
    $q = "SELECT
            (select count(id) from course_offerings where id=:offeringId)
                as offering_exists,
            (select count(id) from students where id=:studentId)
                as student_exists,
            (select count(id) from enrollments where student_id=:studentId_2
                  and course_offering_id in
              (select id from course_offerings where course_id = :courseId)
              ) as enrolled
            from dual";
    $r = DB::select(DB::raw($q), array(
        'offeringId'=> $offering['id'],
        'studentId' => $studentId,
        'studentId_2' => $studentId,
        'courseId' => $offering['course_id']
      ));

    return $r;
  }

  public static function searchByStudentNameForEnrollment($name, $offering) {
    $q = "SELECT s.id as student_id, s.year, u.name, u.email
            from students s, users u where s.id = u.id
            and lower(u.name) like :name
            and s.id not in
                (select student_id from enrollments where course_offering_id in
                  (select id from course_offerings where course_id = :courseId)
                )
            order by u.name";
    $r = DB::select(DB::raw($q), array(
      'name' => '%'.strtolower($name).'%',
      'courseId' => $offering['course_id']
    ));
    return $r;
  }

  public static function getGPAs() {
    $q = "SELECT u.id, u.email, u.name, d.dept_desc, d.id as department_id,
              count(e.id) enrollments,
          SUM(g.score * c.credits) / SUM(c.credits) gpa
          FROM users u JOIN students s ON s.id = u.id
          JOIN departments d ON d.id = s.department_id
          LEFT JOIN enrollments e ON e.student_id = s.id
          LEFT JOIN grades g ON g.id = e.grade_id
          LEFT JOIN course_offerings o ON o.id = e.course_offering_id
          LEFT JOIN courses c ON c.id = o.course_id
          GROUP BY u.id, u.email, u.name, d.dept_desc, d.id ORDER BY u.name" ;
    return DB::select(DB::raw($q));
  }

  public static function getDepartments() {
    $q = "SELECT d.id, d.dept_code, d.dept_desc,
            count(c.id) as course_count,
            count(co.id) as offering_count
          FROM departments d
          join courses c on c.department_id = d.id
          left join course_offerings co on co.course_id = c.id
          group by d.id, d.dept_code, d.dept_desc
          order by d.dept_desc";

    return DB::select(DB::raw($q));
  }

  public static function countCodeMatches($code, $deptId) {
    $q = "SELECT count(c.id) AS match_count
          FROM courses c
          WHERE c.course_code = :code
          AND c.department_id = :deptId";
    $r = DB::select(DB::raw($q),
      array('code'=>(string)$code, 'deptId'=>$deptId));
    return $r[0]->match_count;
  }

  public static function countCourseNameMatches($name, $deptId) {
      $q = "SELECT count(c.id) AS match_count
            FROM courses c
            WHERE c.course_name = :name
            AND c.department_id = :deptId";
      $r = DB::select(DB::raw($q),
        array('name'=>$name, 'deptId'=>$deptId));
      return $r[0]->match_count;
  }

  public static function getCourseEnrollmentsCount($courseId) {
    $q = "SELECT o.id, count(e.id) as enrl_ct
          from course_offerings o
          left join enrollments e on e.course_offering_id = o.id
          where o.course_id=:courseId
          group by o.id";

    return DB::select(DB::raw($q), array('courseId' => $courseId));
  }

  public static function getCourseOfferingInstanceNumber($courseId) {
    $q = "SELECT (ifnull(max(instance_number),0) + 1)
          as num from course_offerings where course_id=:courseId";

    $r = DB::select(DB::raw($q), array('courseId' => $courseId));
    return $r[0]->num;
  }

  public static function getGradedEnrollmentCounts($courseId) {
      $q = "SELECT o.id as course_offering_id, COUNT(e.id) as graded
            FROM course_offerings o LEFT JOIN enrollments e
            ON e.course_offering_id = o.id AND e.grade_id IS NOT NULL
            WHERE o.course_id = :courseId GROUP BY o.id";
      $r = DB::select(DB::raw($q), array('courseId' => $courseId));
      $arr = array();
      foreach ($r as $result) {
        $arr[$result->course_offering_id] = $result->graded;
      }
      return $arr;
  }

  public static function getDeptFaculty($deptId) {
    $q = "SELECT f.id, u.name, u.email, f.chair, count(o.id) as assgn_ct
          from faculty_members f
          join users u on f.id = u.id
          left join course_offerings o on o.faculty_member_id = f.id
          and o.active = true
          where f.department_id = :deptId
          group by f.id, u.name, u.email, f.chair
          order by chair desc, u.name";
    $faculty = DB::select(DB::raw($q), array('deptId' => $deptId));
    return $faculty;
  }

  public static function getFacultyTeachingAssignments($facId) {
      $q = SISQueries::offeringQueryBase()
          ."AND o.faculty_member_id = :facId
          group by o.id, o.course_id, o.instance_number, o.faculty_member_id,
            c.course_name, c.course_code, c.available, d.dept_code, c.id
          order by c.course_code, o.instance_number";
      return DB::select(DB::raw($q), array('facId' => $facId));
  }

  public static function getOffering($offeringId) {
      $q = SISQueries::offeringQueryBase()
      ." AND o.id = :offeringId group by o.id";
      return DB::select(DB::raw($q), array('offeringId' => $offeringId));
  }

  public static function countAssignmentsForOfferingInstructor($offeringId) {
    $q = "SELECT count(o.id) as assign_ct from course_offerings o
          where o.active = true
          and o.faculty_member_id =
          (select faculty_member_id
              from course_offerings where id = :courseOfferingId)";
    $r = DB::select(DB::raw($q), array('courseOfferingId'=>$offeringId));
    return $r;
  }

  public static function countFacultyAssignments($facId) {
    $q = "SELECT count(*) as assign_ct from course_offerings
         where faculty_member_id=:facId
         and active=true";
    return DB::select(DB::raw($q), array('facId' => $facId));
  }

}
