@extends('layouts.sis')

@section('breadcrumb')

  <a href="/registrar">Registrar</a> ::
  <a href="/registrar/students">Students</a> ::
  {{ $student->name }} ({{ $student->email }})

@endsection

@section('summary')

  This page provides some details about this student's NWU academic
  experience, including the student's GPA, major and course enrollments.
  Courses that satisfy requirements in the student's major are highlighted
  in bold.

@endsection

@section('pagedata')

@include('includes.gpa')

<div class="panel-header" style="padding:8px; border-bottom:1px solid #dadada;">
  Major: {{ $student->dept_desc }}
</div>

@include('includes.enrollments')

@endsection
