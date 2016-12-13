@extends('registrar.app')

@section('breadcrumb')
Registrar: <a href="/registrar">Departments</a> ::
  <a href="/registrar/dept/{{ $dept->id }}"
    >{{ $dept->dept_desc }}</a> ::
  <a href="/registrar/course/{{ $dept->id }}/{{ $course->id }}"
    >{{ $course->course_name }} ({{ $dept->dept_code }} {{ $course->course_code }})</a> ::
  Offering {{ $offering->instance_number }} ({{$faculty_member->person->name}}, Instructor)
@endsection

@section('summary')
  Summary section
@endsection

@section('pagedata')

<div class="panel-heading">
@if ( count($enrollments) < $course->capacity)
   <form action="/registrar/enroll/{{$offering->id}}" method="GET">
      Enroll Students: Search <input type="text" name="name"/>
    <input type="submit" value="Submit"/>
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
    <form action="/registrar/enroll/{{ $offering->id }}" method="post">
      {{ csrf_field() }}
      <input type="hidden" name="studentId" value="{{ $st->student_id }}"/>
      <tr>
        <td>
          <input type="submit" value="Enroll"/>
        </td>
        <td>
          {{$st->name}}
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
    <th>Student Name</th>
    <th>Year</th>
    <th>&nbsp;</th>
  </tr>
  @foreach ($enrollments as $student)
    <form action="/registrar/enroll/{{$offering->id}}" method="POST">
      {{ csrf_field() }}
      <input type="hidden" name="_method" value="DELETE"/>
      <input type="hidden" name="studentId" value="{{ $student->id }}"/>
      <tr>
        <td>{{ $student->name }}</td>
        <td>{{ $student->year }}</td>
        <td><input type="submit" value="Unenroll"/></td>
      </tr>
    </form>
  @endforeach
<table>
@endif
@endsection
