@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

              @if (Session::has('error') )
                <div class="panel-heading"
                  style="background-color:#ffd8d8; color:#880000">
                    {{ Session::pull('error') }}
                </div>
              @endif

                <div class="panel-heading">
                  @yield('breadcrumb')
                </div>
                <div class="panel-heading">
                  <p style="font-size:0.8em;">
                    @yield('summary')
                  </p>
                </div>
                @yield('pagedata')
            </div>
        </div>
    </div>
</div>
@endsection
