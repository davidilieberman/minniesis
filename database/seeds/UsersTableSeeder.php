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
        $regId = SisRole::where('role_code','=','RGR')->pluck('id')->first();
        $stuId = SisRole::where('role_code','=','STU')->pluck('id')->first();

        $faculty = [
          ['dolores','Dolores Abernathy'],
          ['teddy', 'Teddy Carlisle'],
          ['maeve', 'Maeve Billings'],
          ['stewie', 'Stewie Griffin'],
          ['alank', 'Alan Kingsman'],
          ['amaso', 'Amaso Delano'],
          ['benito', 'Benito Cereno'],
          ['abullock', 'Avery Bullock'],
          ['jpicard', 'Jack Picard'],
          ['chshaver', 'Charlie Shaver'],
          ['sswanson', 'Sue Swanson'],
          ['jlncstr', 'John G. Lancaster'],
          ['hgrey', 'Henry Grey'],
          ['lwinter', 'Liz Winter'],
          ['kpowell', 'Katerina Powell'],
          ['rsinger', 'Rachel Singer'],
          ['cammie', 'Cammie Lynne'],
          ['babs', 'Babette Collier'],
          ['aporter', 'Angela Porter'],
          ['gwaterhouse', 'Georgina Waterhouse']

        ];

        $users = [
          ['jill@harvard.edu','jill', 'helloworld', $stuId],
          ['jamal@harvard.edu', 'jamal', 'helloworld', $stuId],
          ['gquag@staff.nwr.edu', 'Glenn Quagmire', 'helloworld', $regId],
          ['maggie@nwr.edu', 'Maggie Simpson', 'helloworld', $stuId],
          ['bscriv@staff.nwr.edu', 'Bartleby T. Scrivener', 'helloworld', $regId],
        ];

        $existingUsers = User::all()->keyBy('email')->toArray();

        foreach ($faculty as $f) {
          $em = $f[0].'@fac.nwr.edu';
          if (!array_key_exists($em, $existingUsers)) {
            User::create([
              'email' => $em,
              'name' => $f[1],
              'password' => Hash::make('helloworld'),
              'sis_role_id' => $facId
            ]);
          }
        }

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
