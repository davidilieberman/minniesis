<?php

use Illuminate\Database\Seeder;

use App\Grade;

class GradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            'A'   => 4.0,
            'A-'  => 3.7,
            'B+'  => 3.3,
            'B'   => 3.0,
            'B-'  => 2.7,
            'C+'  => 2.3,
            'C'   => 2.0,
            'C-'  => 1.7,
            'D'   => 1.0,
            'F'   => 0.5
        );

        foreach($data as $grade => $score) {
          Grade::create([
            'grade'=>$grade,
            'score'=>$score
          ]);
        }
    }
}
