@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

              <div class="panel-heading">
                @yield('breadcrumb')
              </div>

              <div class="panel-heading">
                <p style="font-size:0.8em;">
                  @yield('summary')
                </p>
              </div>

              @if (Session::has('success'))
              <div class="panel-heading"
                style="background-color:#d8ffd8; color:#006f00">
                  {{ Session::pull('success') }}
              </div>
              @endif

              @if (Session::has('error') )
                <div class="panel-heading"
                  style="background-color:#ffd8d8; color:#880000">
                    {{ Session::pull('error') }}
                </div>
              @endif

                @yield('pagedata')
            </div>
        </div>
    </div>
</div>
@endsection
