@extends('layouts.sis')

@section('breadcrumb')
  Faculty Module: <a href="/faculty">Home</a>
@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}!  Select a course you are teaching from the
  "My Teaching Assignments" grade your students.

  @if ($facultyMember->chair)
  <br/><br/>
    As chair, you may also use the "Department Course
    Management" module to add, modify or cancel courses in your department.
  @endif

@endsection

@section('pagedata')

@if ($facultyMember->chair)

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada;">
  <h4><a href="/faculty/chair">Department Course Management (Chair-only)</a></h4>
</div>

@endif

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada;">
  <h4>My Teaching Assignments</h4>
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
