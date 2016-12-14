@extends('layouts.sis')

@section('breadcrumb')

  Faculty Module: <a href="/faculty">Home</a> ||
    {{ $offering->dept_code}} {{ $offering->course_code}}:
    {{ $offering->course_name}} -
    Offering {{ $offering->instance_number }}
@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}!

@endsection

@section('pagedata')

  @if (count($enrollments) > 0)
    <table class="table table-striped">
      <tr>
        <th>Student</th>
        <th>Email Address</th>
        <th>Grade</th>
        <th>Enter/Revise Grade</th>
      </tr>
      @foreach ($enrollments as $enrl)
        <form method="POST"
            action="/faculty/offering/{{ $offering->id }}/enrollment"
            class="form-inline">
          {{ csrf_field() }}
          <input type="hidden" name="_method" value="PUT"/>
          <input type="hidden" name="enrollmentId"
            value="{{ $enrl->enrollment_id}}"/>
          @if ( $enrl->department_id == $offering->department_id )
          <tr style="font-weight:bold;">
          @else
          <tr>
          @endif
            <td>{{ $enrl->name }}</td>
            <td>{{ $enrl->email }}</td>
            <td>{{ $enrl->grade }}</td>
            <td>
              <select name="gradeId" class="form-control" style="width:auto;"
                  onchange="this.form.submit();">
                  <option value="-1">&nbsp;</option>
                @foreach ($grades as $g)
                  @if ($g->id == $enrl->grade_id)
                    <option select value="{{ $g->id}}">{{ $g->grade }}</option>
                  @else
                    <option value="{{ $g->id }}">{{ $g->grade }}</option>
                  @endif
                @endforeach
              </select>
            </td>
          </tr>
        </form>
      @endforeach
    </table>
  @else
    <div class="panel-header" style="padding:8px;">
      There are no students enrolled in this course offering.
    </div>
  @endif


@endsection
