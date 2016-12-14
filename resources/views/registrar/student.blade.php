@extends('layouts.sis')

@section('breadcrumb')

  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/students">Students</a> ::
  {{ $student->name }} ({{ $student->email }})

@endsection

@section('summary')

  Some text here.

@endsection

@section('pagedata')

<table class="table table-striped">
  <tr>
    <td colspan="5">
      <h4>Enrollments</h4>
    </td>
  </tr>

  @if ( count($enrollments) == 0)

    <tr>
      <td colspan="5">
        Student is not currently enrolled in any courses.
      </td>
    </tr>

  @else

    <tr>
      <th>Course Code</th>
      <th>Name</th>
      <th>Instructor</th>
      <th>Credits</th>
      <th>Grade</th>
    </tr>


    @foreach ($enrollments as $enrl)
      <tr>
        <td>{{ $enrl->dept_code }} {{ $enrl->course_code}}</td>
        <td>{{ $enrl->course_name}}</td>
        <td>{{ $enrl->instructor }}</td>
        <td>{{ $enrl->credits }}</td>
        <td>{{ $enrl->grade }}</td>
      </tr>
    @endforeach
    
  @endif

</table>

@endsection
