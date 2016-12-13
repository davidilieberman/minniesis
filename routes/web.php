<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return redirect('/home');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('/registrar/depts', 'RegistrarController@deptsIndex')
  ->name('depts.index')
  ->middleware('authz:RGR');

Route::get('/registrar/depts/{deptId}', 'RegistrarController@showDept')
  ->name('depts.show')
  ->middleware('authz:RGR');

Route::get('/registrar/courses/{deptId}/{courseId}', 'RegistrarController@showCourse')
  ->name('courses.show')
  ->middleware('authz:RGR');

Route::get('/registrar/offering/{offeringId}', 'RegistrarController@showOffering')
  ->name('offerings.show')
  ->middleware('authz:RGR');

Route::post('/registrar/offering', 'RegistrarController@storeOffering')
  ->name('offerings.store')
  ->middleware('authz:RGR');

Route::put('/registrar/offering', 'RegistrarController@updateOffering')
  ->name('offerings.update')
  ->middleware('authz:RGR');

Route::get('/registrar/enroll/{offeringId}', 'RegistrarController@searchStudents')
  ->name('enrollments.students.search')
  ->middleware('authz:RGR');

Route::post('/registrar/enroll/{offeringId}', 'RegistrarController@enrollStudent')
  ->name('enrollments.store')
  ->middleware('authz:RGR');

Route::delete('/registrar/enroll/{offeringId}', 'RegistrarController@unenrollStudent')
  ->name('enrollemnts.destoy')
  ->middleware('authz:RGR');

Route::get('/faculty', function() {
  echo "Faculty page <br/>";
  dump(Auth::user()->toArray());
})->middleware('authz:FAC');;

Route::get('/student', function() {
  echo "Student page <br/>";
})->middleware('authz:STU');


Route::get('/logout', function() {
  Auth::logout();
  return redirect('/login');
});
