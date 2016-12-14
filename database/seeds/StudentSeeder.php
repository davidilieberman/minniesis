<?php

use Illuminate\Database\Seeder;
use App\Department;
use App\Student;
use App\User;


class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $depts = [
        'ENG',
        'GER',
        'MATH',
        'MUS',
        'BIO',
        'CHM',
        'THTR',
        'CSCI',
      ];


      $students = [
        'rentwhistle',
        'upinelli',
        'mwagner',
        'kmiller',
        'glake',
        'jglenn',
        'fthresher',
        'khirokami',
        'hli',
        'roccam',
        'sdenton',
        'mtripplehorn',
        'ibayles',
        'mberman',
        'ctayler',
        'bcox',
        'fkeyes',
        'hnorth',
        'vallston',
        'bgallion',
        'efiarman',
        'jmyra',
        'amorrocco',
        'jbleer',
        'pglenndenning',
        'ofleur',
        'ablank',
        'hstout',
        'jmbier',
        'qfoyle',
        'skeating',
        'achandresekharan',
      ];

      // Retrieve the IDs of all uses having the student role
      // $users = User::whereHas('sis_role', function($q) {
      //   $q->where('role_code','STU');
      // })->pluck('id');

      $yearCnt = 1;
      $deptIdx = 0;
      $deptsSize = count($depts);
      // Create a student record for each returned ID, arbitrarily
      // giving the student a 'year' property in the range 1 - 4.
      foreach($students as $student) {
        $u = User::where('email', $student.'@nwr.edu')->first();
        $d = Department::where('dept_code', $depts[$deptIdx++ % $deptsSize])->first();
        $y = ($yearCnt++ % 4)+1;
        Student::create([
          'id' => $u->id,
          'year' => $y,
          'department_id' => $d->id
        ]);
      }
    }
}
