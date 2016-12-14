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
          'ENG' => [
            ['dolores',true],
            ['gwaterhouse', false],
            ['jlncstr', false]
          ],
          'CHM' => [
            ['teddy', true],
            ['aporter', false],
            ['babs', false]
          ],
          'MUS' => [
            ['maeve', true],
            ['cammie', false],
            ['rsinger', false]
          ],
          'BIO' => [
            ['stewie', true],
            ['kpowell', false],
            ['sswanson', false]
          ],
          'THTR' => [
            ['lwinter', true],
            ['hgrey', false],
            ['jpicard', false]
          ],
          'MATH' => [
            ['chshaver', true],
            ['amaso', false],
          ],
          'CSCI' => [
            ['benito', true],
            ['abullock', false],
            ['alank', false],
          ],
          'GER' => [
            ['mmazur', true],
            ['goakfoot', false],
            ['kguld', false]
          ]
        ];

        foreach ($data as $code => $names)
        {
            $dept = Department::where('dept_code','=',$code)->first();
            foreach ($names as $uname) {
              $userId = User::where('email','=',$uname[0].'@fac.nwr.edu')
                  ->pluck('id')->first();
              if ($userId)
              {
                $fac = new FacultyMember();
                //$fac->user_id = $userId;
                $fac->id = $userId;
                $fac->chair = $uname[1];
                $dept->faculty_members()->save($fac);
              }
            }
        }
    }
}
