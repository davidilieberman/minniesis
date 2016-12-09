<?php

use Illuminate\Database\Seeder;
use App\SisRole;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $facId = SisRole::where('role_code','=','FAC')->pluck('id')->first();
        $regId = SisRole::where('role_code','=','REG')->pluck('id')->first();
        $stuId = SisRole::where('role_code','=','STU')->pluck('id')->first();

        $users = [
          ['jill@harvard.edu','jill', 'helloworld', $stuId],
          ['jamal@harvard.edu', 'jamal', 'helloworld', $stuId]
        ];

        $existingUsers = User::all()->keyBy('email')->toArray();

        foreach ($users as $user) {
          User::create([
            'email' => $user[0],
            'name' => $user[1],
            'password' => Hash::make($user[2]),
            'sis_role_id' => $user[3]
          ]);
        }

    }
}
