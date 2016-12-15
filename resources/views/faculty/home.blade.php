@extends('layouts.sis')

@section('breadcrumb')
  Faculty Module: <a href="/faculty">Home</a>
@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}!  Select a course you are teaching from the
  "My Teaching Assignments" panel below to grade your students.

  @if ($facultyMember->chair)
  <br/><br/>
    As chair, you may also use the "Department Course
    Management" module to add, modify or cancel courses in your department, and
    the "Student Majors" module to review the academic experience of students
    majoring in your department's field of study.
  @endif

@endsection

@section('pagedata')

@if ($facultyMember->chair)

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada;">
  <h5><a href="/faculty/chair">Department Course Management</a></h5>
</div>

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada;">
  <h5><a href="/faculty/students">Student Majors</a></h5>
</div>

@endif

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada;">
  <h5>My Teaching Assignments</h5>
</div>

@if (count($offerings) > 0)
  <table class="table table-striped">
    <tr>
      <th>Course Offering</th>
      <th>Enrollment Count</th>
    </tr>
    @foreach ($offerings as $o)
      <tr>
        <td>
          <a href="/faculty/offering/{{$o->id}}"
            >{{ $o->dept_code}} {{ $o->course_code}}: {{ $o->course_name}} -
            Offering {{ $o->instance_number }}</a>
        </td>
        <td>{{ $o->enrl_cnt }}</td>
      </tr>
    @endforeach
  </table>
@else

<div class="panel-header" style="padding:8px; border-bottom:1px solid #dadada;">
  You are not currently assigned to teach any course offerings.
</div>

@endif


@endsection
