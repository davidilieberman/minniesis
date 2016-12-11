@extends('registrar.app')

@section('breadcrumb')
Registrar: <a href="/registrar">Departments</a> ::
  {{ $dept->dept_desc }}
@endsection

@section('summary')
  Some text here.
@endsection

@section('pagedata')
                <table class="table table-striped">
                      <tr>
                        <th>Course Name</th>
                        <th>Number</th>
                        <th>Capacity</th>
                        <th>Credits</th>
                        <th># Scheduled Offerings</th>
                      </tr>
                      @foreach( $courses as $course )
                      <tr>
                        <td>
                            <a href="/registrar/course/{{$dept->id}}/{{$course['id']}}"
                              >{{ $course['course_name'] }}</a>
                        </td>
                        <td>{{ $course['course_code'] }}</td>
                        <td>{{ $course['capacity'] }}</td>
                        <td>{{ $course['credits'] }}</td>
                        <td>{{ count($course['course_offerings']) }}</td>
                      </tr>
                      @endforeach
                </table>
@endsection
