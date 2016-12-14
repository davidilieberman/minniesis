@extends('layouts.sis')

@section('breadcrumb')

  <a href="/registrar">Registrar</a> ::
  Students

@endsection

@section('summary')

  Some text here.

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
    <th>Major</th>
    <th>Total Enrollments</th>
    <th>GPA</th>
  </tr>
  @foreach ($students as $s)
    <tr style="font-size:.9em;">
      <td>{{ $s->id }}</td>
      <td><a href="/registrar/students/{{ $s->id }}">{{ $s->name }}</a></td>
      <td>{{ $s->email }}</td>
      <td>{{ $s->dept_desc }}</td>
      <td>{{ $s->enrollments }}</td>
      <td>
        @if ( $s->gpa )
          {{ number_format($s->gpa, 2) }}
        @endif
      </td>
    </tr>
  @endforeach
</table>

@endsection
