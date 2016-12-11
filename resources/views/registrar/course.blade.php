@extends('layouts.app')

@section('content')



<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
              @if (Session::has('error'))
                <div class="panel-heading"
                  style="background-color:#ffd8d8; color:#880000">
                    {{ Session::pull('error') }}
                </div>
              @endif
                <div class="panel-heading">
                  Registrar: <a href="/registrar">Departments</a> ::
                    <a href="/registrar/dept/{{ $dept->id }}"
                      >{{ $dept->dept_desc }}</a> ::
                    {{ $course->course_name }}
                </div>
                <div class="panel-heading">
                  <p style="font-size:0.8em;">
                    Some text here.
                  </p>
                </div>
                @php
                  dump($faculty->toArray());
                  dump($course->toArray());
                @endphp
            </div>
        </div>
    </div>
</div>
@endsection
