@extends('layouts.sis')

@section('breadcrumb')
  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/depts">Departments</a> ::
  <a href="/registrar/depts/{{ $dept->id }}">{{ $dept->dept_desc }}</a> ::
  {{ $course->course_name }} ({{ $dept->dept_code }} {{ $course->course_code }})
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
    @if ( !$offering->active )
    <tr style="background-color:#dadada">
    @else
    <tr>
    @endif
      <td>
        @if ( $offering->active )
        <a href="/registrar/offering/{{ $offering->id }}"
          >{{ $dept->dept_code }} {{ $course->course_code }} -
            {{ $offering->instance_number }}</a>
        @else
          {{ $dept->dept_code }} {{ $course->course_code }} -
          {{ $offering->instance_number }}
        @endif
      </td>

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
          @if ( $offering->active and !$graded_offerings[$offering->id] )
            <input type="submit" value="Deactivate"/>
          @elseif ( $offering->active and $graded_offerings[$offering->id])
              Cannot deactivate course offering after grades are entered.
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
