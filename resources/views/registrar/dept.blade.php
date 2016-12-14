@extends('layouts.sis')

@section('breadcrumb')
  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/depts">Departments</a> ::
  {{ $dept->dept_desc }}
@endsection

@section('summary')
  Some text here.
@endsection

@section('pagedata')
  <div class="panel-heading">
    <h4>Courses</h4>
  </div>
  <table class="table table-striped">
        <tr>
          <th>Course Name</th>
          <th>Number</th>
          <th>Capacity</th>
          <th>Credits</th>
          <th># Scheduled Offerings</th>
        </tr>
        @foreach( $courses as $course )
        <tr>
          <td>
              <a href="/registrar/courses/{{$dept->id}}/{{$course['id']}}"
                >{{ $course['course_name'] }}</a>
          </td>
          <td>{{ $course['course_code'] }}</td>
          <td>{{ $course['capacity'] }}</td>
          <td>{{ $course['credits'] }}</td>
          <td>{{ count($course['active_offerings']) }} active;
            {{ count($course['course_offerings']) - count($course['active_offerings']) }} inactive</td>
        </tr>
        @endforeach
  </table>

  @if (count($faculty) > 0)
  <div class="panel-heading" style="border-top:1px solid #ddd;">
    <h4>Faculty</h4>
  </div>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Email Address</th>
      <th>Teaching Assignments</th>
    </tr>
    @foreach ($faculty as $f)
      <tr>
        <td>{{$f->name}}</td>
        <td>{{$f->email}}</td>
        <td>{{$f->assgn_ct}}</td>
      </tr>
    @endforeach
  </table>

  @endif

@endsection
