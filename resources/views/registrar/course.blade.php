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
                @php
                  dump($faculty->toArray());
                @endphp
            </div>
        </div>
    </div>
</div>
@endsection
