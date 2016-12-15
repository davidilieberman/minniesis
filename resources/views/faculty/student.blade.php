@extends('layouts.sis')

@section('breadcrumb')

Faculty Module: <a href="/faculty">Home</a> ::
  <a href="/faculty/students">Student Majors</a> ::
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

@include('includes.enrollments')

@endsection
