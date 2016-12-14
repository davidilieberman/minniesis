<?php

namespace App;

use Illuminate\Support\Facades\DB;

class SISQueries  {

  public static function getStudentEnrollments($studentUserId) {
    $q = 'select d.dept_code, c.course_code, c.course_name, c.credits, g.grade, f.name as instructor '
        .'from users u join students s on s.user_id = u.id '
        .'join enrollments e on e.student_id = s.id '
        .'join course_offerings o on e.course_offering_id = o.id '
        .'join courses c on c.id = o.course_id '
        .'join departments d on d.id = c.department_id '
        .'join faculty_members fm on fm.id = o.faculty_member_id '
        .'join users f on fm.user_id = f.id '
        .'left join grades g on g.id = e.grade_id '
        .'where u.id = :studentUserId order by d.dept_code, c.course_code';
    return DB::select(DB::raw($q), array('studentUserId' => $studentUserId));
  }
}
