@extends('layouts.sis')

@section('breadcrumb')

    <a href="/registrar">Registrar</a> ::
    Departments

@endsection

@section('summary')

  Some text here.

@endsection

@section('pagedata')
<table class="table table-striped">
  <tr>
    <th>Department</th>
    <th>Code</th>
    <th>Courses Listed</th>
    <th>Offerings Scheduled</th>
  </tr>
  @foreach($depts as $dept)
    <tr>
      <td>
        <a href="/registrar/depts/{{ $dept->id }}"
          >{{ $dept->dept_desc }}</a>
      </td>
      <td>{{ $dept->dept_code }}</td>
      <td>{{ $dept->course_count }}</td>
      <td>{{ $dept->offering_count }}</td>
    </tr>
  @endforeach
</table>
@endsection
