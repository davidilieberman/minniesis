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

Route::get('/registrar', 'RegistrarController@listDepts')
  ->middleware('authz:RGR');

Route::get('/registrar/dept/{deptId}', 'RegistrarController@showDept')
  ->middleware('authz:RGR');

Route::get('/registrar/course/{deptId}/{courseId}', 'RegistrarController@showCourse')
  ->middleware('authz:RGR');

Route::get('/registrar/offering/{offeringId}',
  'RegistrarController@showOffering')
  ->middleware('authz:RGR');

Route::post('/registrar/offering', 'RegistrarController@storeOffering')
  ->middleware('authz:RGR');

Route::put('/registrar/offering', 'RegistrarController@updateOffering')
  ->middleware('authz:RGR');

Route::get('/registrar/enroll/{offeringId}', 'RegistrarController@searchStudents')
  ->middleware('authz:RGR');

Route::post('/registrar/enroll/{offeringId}', 'RegistrarController@enrollStudent')
  ->middleware('authz:RGR');

Route::delete('/registrar/enroll/{offeringId}', 'RegistrarController@unenrollStudent')
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
