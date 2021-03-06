@extends('layouts.sis')

@section('breadcrumb')
  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/depts">Departments</a> ::
  <a href="/registrar/depts/{{ $dept->id }}">{{ $dept->dept_desc }}</a> ::
  <a href="/registrar/courses/{{ $dept->id }}/{{ $course->id }}"
    >{{ $course->course_name }} ({{ $dept->dept_code }} {{ $course->course_code }})</a> ::
  Offering {{ $offering->instance_number }}
@endsection

@section('summary')
  Use this page to enroll students in this course offering. Note that
  the course offering may only accept the maximum number students defined
  for the course. An enrollment request will be rejected if it would cause
  the student to exceed a total of nine credits in enrolled courses.
@endsection

@section('pagedata')

<div class="panel-heading">
 <span style='font-weight:bold;'>{{$faculty_member->person->name}}, Instructor</span>
</div>

<div class="panel-heading">
@if ( count($enrollments) < $course->capacity)
   <form action="/registrar/enroll/{{$offering->id}}" method="GET"
        class="form-inline">
      Enroll Students: Search <input type="text" name="name" class="form-control"/>
    <input type="submit" value="Submit" class="form-control"/>
  </form>
@else
    Course Offering is full (maximum capacity: {{ $course->capacity }})
@endif
</div>
@if ($search_term)
<div class="panel-heading">
  Searched for students on partial match for '{{ $search_term }}'; retrieved {{ count($search_results)}}.
  @if (count($search_results) > 0)
    <table class="table table-striped">
  @foreach($search_results as $st)
    <form action="/registrar/enroll/{{ $offering->id }}" method="post"
        class="form-inline">
      {{ csrf_field() }}
      <input type="hidden" name="studentId" value="{{ $st->student_id }}"/>
      <tr>
        <td>
          <input type="submit" value="Enroll" class="form-control"/>
        </td>
        <td>
          {{$st->name}}
        </td>
        <td>
          {{$st->email}}
        </td>
      </tr>
    </form>
  @endforeach
  </table>
  @endif
</div>
@endif


@if ( count($enrollments) == 0 )
<div class="panel-heading">
  No students enrolled in this course (maximum capacity: {{ $course->capacity }})
</div>
@else
<table class="table table-striped">
  <tr>
    <th>Student ID</th>
    <th>Name</th>
    <th>Email Address</th>
    <th>Year</th>
    <th>Grade</th>
    <th>&nbsp;</th>
  </tr>
  @foreach ($enrollments as $student)
    <form action="/registrar/enroll/{{$offering->id}}" method="POST"
        class="form-inline">
      {{ csrf_field() }}
      <input type="hidden" name="_method" value="DELETE"/>
      <input type="hidden" name="studentId" value="{{ $student->student_id }}"/>
      <tr>
        <td>{{ $student->id }}</td>
        <td>{{ $student->name }}</td>
        <td>{{ $student->email }}</td>
        <td>{{ $student->year }}</td>
        <td>{{ $student->grade }}</td>
        <td>
          @if ( !$student->grade )
            <input type="submit" value="Withdraw" class="form-control"/>
          @endif
        </td>
      </tr>
    </form>
  @endforeach
<table>
@endif
@endsection
