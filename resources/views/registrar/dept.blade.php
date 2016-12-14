@extends('layouts.sis')

@section('breadcrumb')
  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/depts">Departments</a> ::
  {{ $dept->dept_desc }}
@endsection

@section('summary')
  This page lists the department's courses, its faculty and its student majors.
  Select a course to manage its offerings and enroll students.
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


  <div class="panel-heading" style="border-top:1px solid #ddd;">
    <h4>Faculty</h4>
  </div>

  @if (count($faculty) == 0)

  <div class="panel-heading" style="border-top:1px solid #ddd;">
    Department has no Faculty on staff
  </div>

  @else
  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Email Address</th>
      <th>Teaching Assignments</th>
    </tr>
    @foreach ($faculty as $f)
      <tr>
        <td>{{$f->name}}
            @if ($f->chair) (Chair) @endif
        </td>
        <td>{{$f->email}}</td>
        <td>{{$f->assgn_ct}}</td>
      </tr>
    @endforeach
  </table>

  @endif

  <div class="panel-heading" style="border-top:1px solid #ddd;">
    <h4>Student Majors</h4>
  </div>

  @if (count($students) == 0)

  <div class="panel-heading" style="border-top:1px solid #ddd;">
    Department has no student majors
  </div>

  @else
  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Email Address</th>
      <th>GPA</th>
    </tr>
    @foreach ($students as $s)
      <tr>
        <td>{{$s->name}} </td>
        <td>{{$s->email}}</td>
        <td>
          @if ($s->gpa > 0)
            {{number_format($s->gpa, 2)}}
          @endif
        </td>
      </tr>
    @endforeach
  </table>
  @endif
@endsection
