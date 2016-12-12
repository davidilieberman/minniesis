@extends('registrar.app')

@section('breadcrumb')
Registrar: <a href="/registrar">Departments</a> ::
  <a href="/registrar/dept/{{ $dept->id }}"
    >{{ $dept->dept_desc }}</a> ::
  {{ $course->course_name }}
@endsection

@section('summary')
  Summary section
@endsection

@section('pagedata')

<div class="panel-heading">
  @if ( $available > 0 )
  <form action="/registrar/offering" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="deptId" value="{{ $dept->id }}"/>
    <input type="hidden" name="courseId" value="{{ $course->id }}"/>
    Schedule a new offering -- Available Faculty:
    <select name="facId">
      @foreach ( $faculty as $f )
        @if ( $f->assgn_ct < 3 )
          <option value="{{ $f->id }}">{{ $f->name }}</option>
        @endif
      @endforeach
    </select>
    <input type="submit" value="Save"/>
  </form>
  @else
    Cannot schedule offerings of this course; all department faculty
      are fully committed.
  @endif
</div>

@if ( count($course->course_offerings) == 0 )
<div class="panel-heading">
  No offerings scheduled for this course.
</div>
@else
<table class="table table-striped">
  <tr>
    <th>Offering Number</th>
    <th>Faculty Member</th>
    <th>Status</th>
    <th>Enrollments</th>
    <th>&nbsp;</th>
  </tr>
  @foreach ( $course->course_offerings as $offering )
    <tr>
      <td>{{ $offering->instance_number }}</td>

      <td>
        {{ $faculty_names[$offering->faculty_member_id] }}
      </td>
      <td>
        @if ( $offering->active )
          active
        @else
          inactive
        @endif
      </td>
      <td>
        {{ $enrollment_counts[$offering->id] }}
      </td>
      <td>
        <form action="/registrar/offering" method="post">
          {{ csrf_field() }}
          <input type="hidden" name="_method" value="put"/>
          <input type="hidden" name="deptId" value="{{ $dept->id }}"/>
          <input type="hidden" name="courseId" value="{{ $course->id }}"/>
          <input type="hidden" name="offeringId" value="{{ $offering->id }}"/>
          @if ( $offering->active )
            <input type="submit" value="Deactivate"/>
          @else
            <input type="submit" value="Reactivate"/>
          @endif
        </form>
      </td>
    </tr>
  @endforeach
<table>
@endif
@endsection
