<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // Retrieve the IDs of all uses having the student role
      $users = User::whereHas('sis_role', function($q) {
        $q->where('role_code','STU');
      })->pluck('id');

      // Create a student record for each returned ID, arbitrarily
      // giving the student a 'year' property in the range 1 - 4.
      foreach($users as $userId) {
        $y = rand(1,4);
        Student::create([
          'user_id' => $userId,
          'year' => $y
        ]);
      }
    }
}
