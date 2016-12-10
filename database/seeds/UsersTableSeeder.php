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

        $students = [
          ['rentwhistle', 'Robert Entwhistle'],
          ['upinelli', 'Umberto Pinelli'],
          ['mwagner', 'Miriam Wagner'],
          ['kmiller', 'Kate Miller'],
          ['glake', 'Greg Lake'],
          ['jglenn', 'John Glenn'],
          ['fthresher', 'Fred Thresher'],
          ['khirokami', 'Kayo Hirokami'],
          ['hli', 'Hoi-tzi Li'],
          ['roccam', 'Ralph Occam'],
          ['sdenton', 'Sarah Denton'],
          ['mtripplehorn', 'Malcolm Tripplehorne'],
          ['ibayles', 'Ingrid Bayled'],
          ['mberman', 'Micah Berman'],
          ['ctayler', 'Charles Taylor'],
          ['bcox', 'Bob Cox'],
          ['fkeyes', 'Florence Keyes'],
          ['hnorth', 'Hank North'],
          ['vallston', 'Val Allston'],
          ['bgallion', 'Benjamin Gallion'],
          ['efiarman', 'Elizabeth Fiarman'],
          ['jmyra', 'John Myra'],
          ['amorrocco', 'Andy Morrocco'],
          ['jbleer', 'Jonathan Bleer'],
          ['pglenndenning', 'Pierre Glenndenning'],
          ['ofleur', 'Olivia Fleur'],
          ['ablank', 'Amelia Blank'],
          ['hstout', 'Hal Stout'],
          ['jmbier', 'Jacob Meierbier'],
          ['qfoyle', 'Quentin Foyle'],
          ['skeating', 'Stanley Keating'],
          ['achandresekharan', 'Arjun Chandrasekharan']
        ];

        $users = [
          ['jill@harvard.edu','jill', 'helloworld', $stuId],
          ['jamal@harvard.edu', 'jamal', 'helloworld', $regId],
          ['gquag@staff.nwr.edu', 'Glenn Quagmire', 'helloworld', $regId],
          ['maggie@nwr.edu', 'Maggie Simpson', 'helloworld', $regId],
          ['bscriv@staff.nwr.edu', 'Bartleby T. Scrivener', 'helloworld', $regId],
        ];

        $existingUsers = User::all()->keyBy('email')->toArray();

        foreach ($faculty as $f) {
          $this->loadUser($f, '@fac.nwr.edu', $facId, $existingUsers);
        }

        foreach($students as $s) {
          $this->loadUser($s, '@nwr.edu', $stuId, $existingUsers);
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

    private function loadUser($user, $domain, $role, $existingUsers) {
        $em = $user[0].$domain;
        if (!array_key_exists($em, $existingUsers)) {
          User::create([
            'email' => $em,
            'name' => $user[1],
            'password' => Hash::make('helloworld'),
            'sis_role_id' => $role
          ]);
        }
    }
}
