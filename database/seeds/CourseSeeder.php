<?php

use Illuminate\Database\Seeder;
use App\Department;
use App\Course;
use App\CourseOffering;
use App\Enrollment;
use App\Grade;
use App\FacultyMember;
use App\Student;
use App\User;


class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
          'CSCI' => [
            ['101', 'Programming in Java', 3.0, 8]
          ],
          'THTR' => [
            ['101', 'Introduction to Acting', 1.5, 10],
            ['201', 'Theatre in the Ancient World', 3.0, 8],
            ['211', 'Musical Theatre', 3.0, 8]
          ],
          'CHM' => [
            ['101', 'Introductory Chemistry I', 4.0, 8],
            ['102', 'Introductory Chemistry II', 4.0, 8],
            ['201', 'Organic Chemistry I', 4.0, 6],
            ['202', 'Organic Chemistry II', 4.0, 6]
          ],
          'MUS' => [
            ['101', 'Music Appreciation', 1.5, 12],
            ['105', 'Music Theory I', 3.0, 4],
            ['106', 'Music Theory II', 3.0, 4],
            ['201', 'Music History Survey I', 3.0, 6],
            ['202', 'Music History Survey II', 3.0, 6],
          ],
          'BIO' => [
            ['101', 'Cellular Biology I', 4.0, 8],
            ['102', 'Cellular Biology II', 4.0, 8],
            ['201', 'Biochemistry', 4.0, 8],
            ['202', 'Genetics', 4.0, 8]
          ],
          'MATH' => [
            ['101', 'College Algebra', 3.0, 10],
            ['105', 'Precalculus', 3.0, 10],
            ['201', 'Calculus I', 3.0, 8],
            ['202', 'Calculus II', 3.0, 8],
            ['301', 'Multivariable Calculus', 3.0, 6],
            ['302', 'Linear Algebra', 3.0, 6]
          ],
          'GER' => [
            ['101', 'Introductory German I', 3.0, 8],
            ['102', 'Introductory German II', 3.0, 8],
            ['201', 'Intermediate German I', 3.0, 8],
            ['202', 'Intermediate German II', 3.0, 8],
            ['305', 'Works of Goethe', 3.0, 6],
            ['307', 'Novels of Thomas Mann', 3.0, 6]
          ],
          'ENG' => [
            ['101', 'English Composition', 3.0, 12],
            ['100', 'English as a Second Langague', 1.5, 12],
            ['201', 'Advanced Writing Techniques', 3.0, 8],
            ['317', 'Shakespeare I', 3.0, 6],
            ['318', 'Shakespeare II', 3.0, 6],
            ['425', 'Postmodern Thought and the American Novel', 3.0, 6]
          ]
        ];

        foreach ($data as $deptCode => $courseList) {
          $dept = Department::where('dept_code','=',$deptCode)->first();
          foreach ($courseList as $course) {
            $c = new Course();
            $c->course_code = $course[0];
            $c->course_name = $course[1];
            $c->credits = $course[2];
            $c->capacity = $course[3];
            $dept->courses()->save($c);
          }
        }

        $courses = [
          ['BIO', '101', 'stewie'],
          ['CHM', '101', 'babs'],
          ['ENG', '318', 'dolores']
        ];

        $students = array(
          'rentwhistle@nwr.edu' => ['A', 'A-', 'B'],
          'upinelli@nwr.edu' => ['B+', 'B', 'A-'],
          'mwagner@nwr.edu' => ['B','B','B-'],
          'kmiller@nwr.edu' => ['B-', 'C','B-'],
          'jill@harvard.edu' => ['A','A','A']
        );

        $ind = 0;
        foreach ($courses as $course) {
          $u = User::where('email', $course[2].'@fac.nwr.edu')->first();
          $d = Department::where('dept_code', $course[0])->first();
          $c = Course::where([
            ['department_id', $d->id],
            ['course_code', $course[1]]
          ])->first();

          CourseOffering::create([
            'instance_number' => 1,
            'course_id' => $c->id,
            'faculty_member_id' => $u->id
          ]);

          $co = CourseOffering::where([
            ['course_id', $c->id],
            ['instance_number', 1]
          ])->first();


          foreach ($students as $s => $g) {
            $su = User::where('email', $s)->first();
            $grade = Grade::where('grade', $g[$ind])->first();
            Enrollment::create([
              'student_id' => $su->id,
              'grade_id' => $grade->id,
              'course_offering_id' => $co->id
            ]);
          }
          $ind++;
        }

        // One canceled course
        $c = new Course();
        $c->course_code = '101';
        $c->course_name = 'General Biology I';
        $c->credits = 3.0;
        $c->capacity = 8;
        $c->available = false;
        $d = Department::where('dept_code', 'BIO')->first();
        $d->courses()->save($c);

    }
}
