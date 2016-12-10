<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Department;
use App\FacultyMember;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
          'dolores@fac.nwr.edu'=>'ENG',
          'teddy@fac.nwr.edu'=>'CHM',
          'maeve@fac.nwr.edu'=>'MUS',
          'stewie@fac.nwr.edu'=>'BIO',
          'alank@fac.nwr.edu'=>'THTR',
          'amaso@fac.nwr.edu'=>'MATH',
          'benito@fac.nwr.edu'=>'CSCI',
          'abullock@fac.nwr.edu'=>'MUS'
        ];

        foreach ($data as $email => $code) {
          $fac = User::where('email','=',$email)->pluck('id')->first();
          $dept = Department::where('dept_code','=',$code)->pluck('id')->first();
          FacultyMember::create([
            'user_id' => $fac,
            'department_id' => $dept
          ]);
        }
    }
}
