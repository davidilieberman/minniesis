@extends('layouts.sis')

@section('breadcrumb')

  Registrar

@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}! This application will allow you
  to manage Nowhere University course offerings and enrollments. Use
  the Departments module to identify courses, manage their offerings
  and enroll students in offerings. For your convenience, you can
  use the Students module to review information about individual students.
  Use the "breadcrumb" interface, above, to navigate the modules.

@endsection

@section('pagedata')

<table class="table">
  <tr>
    <td><h3>Registrar Home Page</h3></td>
  </tr>
  <tr>
    <td><h4><a href="/registrar/depts">Departments</a></h4></td>
  </tr>
  <tr>
    <td><h4><a href="/registrar/students">Students</a></h4></td>
  </tr>
</table>

@endsection
