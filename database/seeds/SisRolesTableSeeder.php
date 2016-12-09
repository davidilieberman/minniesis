<?php

use Illuminate\Database\Seeder;
use App\SisRole;

class SisRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
          'STF' => 'Staff',
          'STU' => 'Student',
          'RGR' => 'Registrar',
          'FAC' => 'Faculty'
        ];

        foreach($data as $role=>$desc) {
          $sr = new SisRole();
          $sr->role_code = $role;
          $sr->role_desc = $desc;
          $sr->save();
        }
    }
}
