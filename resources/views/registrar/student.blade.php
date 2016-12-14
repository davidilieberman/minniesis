@extends('layouts.sis')

@section('breadcrumb')

  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/students">Students</a> ::
  {{ $student->name }} ({{ $student->email }})

@endsection

@section('summary')

  Some text here.

@endsection

@section('pagedata')

@include('includes.gpa')

@include('includes.enrollments')

@endsection
