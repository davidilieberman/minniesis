@extends('layouts.sis')

@section('breadcrumb')
  <table style="width:100%"><tr><td>
      Student: <span style="font-weight:bold;">{{ $student->name }}</span>
  </td>
  <td style="text-align:right;">
    Major: {{ $student->dept_desc }}
  </td>
  </tr></table>
@endsection

@section('summary')

  Greetings, {{ $student->name}}! You can use this page to review your
  course enrollments, check your grades and GPA, and change your major
  if you choose.

@endsection

@section('pagedata')

@include('includes.gpa')

<div class="panel-header" style="padding:8px; border-bottom:1px solid #dadada;">
  <form action="/student/update" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="_method" value="PUT"/>
  Change Major:
    <select name="deptId"
        onchange="if (this.options[selectedIndex].value != {{$student->department_id}})
                    this.form.submit();">
      @foreach ($depts as $d)
        @if ( $d->id == $student->department_id )
          <option value="{{$d->id}}" selected>{{$d->dept_desc}}</option>
        @else
          <option value="{{$d->id}}">{{$d->dept_desc}}</option>
        @endif
      @endforeach
    </select>
  </form>
</div>

@include('includes.enrollments')

@endsection
