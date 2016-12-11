@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                  Registrar: <a href="/registrar">Departments</a> ::
                    {{ $dept->dept_desc }}
                </div>
                <div class="panel-heading">
                  <p style="font-size:0.8em;">
                    Some text here.
                  </p>
                </div>
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
                @php
                  //dump($dept->toArray());
                @endphp
            </div>
        </div>
    </div>
</div>
@endsection
