<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SisRolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(DepartmentsSeeder::class);
        $this->call(FacultySeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(GradesTableSeeder::class);
        $this->call(CourseSeeder::class);
    }
}
