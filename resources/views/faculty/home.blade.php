@extends('layouts.sis')

@section('breadcrumb')
  Faculty Module: Home
@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}!

  @if ($facultyMember->chair)
  <br/><br/>
  As chair, blah blah blah
  @endif

@endsection

@section('pagedata')

<div class="panel-header"
    style="padding:8px; border-bottom:1px solid #dadada; border-top:1px solid #dadada;">
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

@if ($facultyMember->chair)
<div class="panel-header"
    style="padding:8px; border-top:2px solid #dadada;">
  <h4>Department Course Management (Chair-only)</h4>
</div>
<table class="table table-striped" style="border-top:1px solid #dadada;">
  <tr>
    <th>Course ID</th>
    <th>Course Code</th>
    <th>Course Name</th>
    <th>Credits</th>
    <th>Capacity</th>
    <th>&nbsp;</th>
  </tr>
  <form action="/faculty/course" method="POST" class="form-inline">
    {{ csrf_field() }}
    <tr>
      <td>
        Add new course:
      </td>
      <td>
        <input type="text" class="form-control"
          size="4" maxlength="4" name="c_name" placeholder="101"/>
      </td>
      <td>
        <input type="text" class="form-control"
          name="c_code" placeholder="A New Course"/>
      </td>
      <td>
        <select name="credits" class="form-control" style="width:auto; height:auto;">
          <option>1.5</option>
          <option selected>3.0</option>
          <option>4.0</option>
        </select>
      </td>
      <td>
        <select name="capacity" class="form-control" style="width:auto; height:auto;">
          @for ($cap=4; $cap<=15; $cap++)
            <option>{{ $cap }}</option>
          @endfor
        </select>
      </td>
      <td>
        <input type="submit" class="form-control" value="Submit"/>
      </td>
    </tr>
  </form>
  @foreach ($courses as $c)
  <form action="/faculty/course" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="_method" value="PUT"/>
    <input type="hidden" name="course_id" value="{{ $c->id }}"/>
    <tr>
      <td>{{ $c->id }}</td>
      <td>{{ $c->course_code }}</td>
      <td>{{ $c->course_name }}</td>
      <td>
        <select name="credits" class="form-control"
          style="width:auto; height:auto; font-size:.9">
          @foreach($creditOptions as $opt)
            @if ($c->credits == $opt)
              <option selected>
            @else
              <option>
            @endif
              {{ number_format($opt,1) }}</option>
          @endforeach
        </select>
      </td>
      <td>
        <select name="capacity" class="form-control" style="width:auto; height:auto;">
          @for ($cap=4; $cap<=15; $cap++)
            @if ($c->capacity == $cap )
              <option selected>
            @else
              <option>
            @endif
              {{ $cap }}</option>
          @endfor
        </select>
      </td>
      <td>
        <input type="submit" class="form-control" value="Update"/>
      </td>
    </tr>
  </form>
  @endforeach
</table>

@endif

@endsection
