<?php

use Illuminate\Database\Seeder;
use App\Department;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
          'ENG' => 'English',
          'GER' => 'German',
          'MATH' => 'Mathematics',
          'MUS' => 'Music',
          'BIO' => 'Biology',
          'CHM' => 'Chemistry',
          'THTR' => 'Theatre',
          'CSCI' => 'Computer Science',
        ];

        foreach ($data as $code => $name) {
          Department::create([
            'dept_code'=>$code,
            'dept_desc'=>$name
          ]);
        }
    }
}
