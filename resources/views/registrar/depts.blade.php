@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                  Registrar: Departments
                </div>
                <div class="panel-heading">
                  <p style="font-size:0.8em;">
                    Some text here.
                  </p>
                </div>
                <table class="table table-striped">
                  <tr>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Courses Listed</th>
                    <th>Offerings Scheduled</th>
                  </tr>
                  @foreach($depts as $dept)
                    <tr>
                      <td>
                        <a href="/registrar/dept/{{ $dept->id }}"
                          >{{ $dept->dept_desc }}</a>
                      </td>
                      <td>{{ $dept->dept_code }}</td>
                      <td>{{ $dept->course_count }}</td>
                      <td>{{ $dept->offering_count }}</td>
                    </tr>
                  @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
