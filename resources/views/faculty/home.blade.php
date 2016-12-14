@extends('layouts.sis')

@section('breadcrumb')
  Faculty Module: Home
@endsection

@section('summary')

  Greetings, {{ Auth::user()->name}}!

@endsection

@section('pagedata')


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

@endif

@endsection
