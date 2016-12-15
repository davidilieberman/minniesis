@extends('layouts.sis')

@section('breadcrumb')

Faculty Module: <a href="/faculty">Home</a> :: Student Majors

@endsection

@section('summary')

  This page provides a summary view of all students matriculated at
  Nowhere University as majors in your department, including the Grade Point
  Average of any student who has completed one or more courses at NWU.
  Click on a student's name for more detailed information about his or her
  academic experience.

@endsection

@section('pagedata')

<table class="table table-striped">
  <tr>
    <td colspan="5">
      <h4>Students</h4>
    </td>
  </tr>
  <tr style="font-size:.9em;">
    <th>Student ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Total Enrollments</th>
    <th>GPA</th>
  </tr>
  @foreach ($students as $s)
    @if ($s->department_id == $faculty->department_id)
    <tr style="font-size:.9em;">
      <td>{{ $s->id }}</td>
      <td><a href="/faculty/students/{{ $s->id }}">{{ $s->name }}</a></td>
      <td>{{ $s->email }}</td>
      <td>{{ $s->enrollments }}</td>
      <td>
        @if ( $s->gpa )
          {{ number_format($s->gpa, 2) }}
        @endif
      </td>
    </tr>
    @endif
  @endforeach
</table>

@endsection
