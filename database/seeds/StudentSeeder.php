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
      $users = User::whereHas('sis_role', function($q) {
        $q->where('role_code','STU');
      })->pluck('id');

      foreach($users as $userId) {
        $y = rand(1,4);
        Student::create([
          'user_id' => $userId,
          'year' => $y
        ]);
      }
    }
}
