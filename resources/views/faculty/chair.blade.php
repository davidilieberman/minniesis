@extends('layouts.sis')

@section('breadcrumb')
  Faculty Module: <a href="/faculty">Home</a> :: Department Course Management
@endsection

@section('summary')

  As chair of your department, you may also use the "Department Course Management"
  panel below to add new courses to your department, change the enrollment
  capacity of existing courses, or cancel currently active courses. Once a
  course is canceled, the Registrar's office will no longer be able to add
  new offerings of it.

@endsection

@section('pagedata')

<table class="table table-striped"
    style="border-top:1px solid #dadada; font-size:.8em;">
  <tr>
    <th>Course ID</th>
    <th>Code</th>
    <th>Name</th>
    <th>Credits</th>
    <th>Capacity</th>
    <th colspan="2">&nbsp;</th>
  </tr>
  <form action="/faculty/course" method="POST" class="form-inline">
    {{ csrf_field() }}
    <tr>
      <td>
        Add new course:
      </td>
      <td>
        <input type="text" class="form-control"
          style="width:auto; height:auto; font-size:.88em;"
          size="3" maxlength="3" name="code" placeholder="101"/>
      </td>
      <td>
        <input type="text" class="form-control"
          style="width:auto; height:auto; font-size:.88em;"
          size="12"
          name="name" placeholder="A New Course"/>
      </td>
      <td>
        <select name="credits" class="form-control"
          style="width:auto; height:auto; font-size:.88em;">
          <option>1.5</option>
          <option selected>3.0</option>
          <option>4.0</option>
        </select>
      </td>
      <td>
        <select name="capacity" class="form-control"
          style="width:auto; height:auto; font-size:.88em;">
          @for ($cap=4; $cap<=15; $cap++)
            <option>{{ $cap }}</option>
          @endfor
        </select>
      </td>
      <td colspan="2">
        <input type="submit" class="form-control" value="Create Course"
          style="width:auto; height:auto; font-size:.8em; text-align:right;"/>
      </td>
    </tr>
  </form>
  @foreach ($courses as $c)
    <tr @if (!$c->available)
        style="background-color:#ddd;"
      @endif>
      <td>{{ $c->id }}</td>
      <td>{{ $c->course_code }}</td>
      <td>{{ $c->course_name }}</td>
      <td style="text-align:center;">
        {{ number_format($c->credits, 1) }}
      </td>
      <form action="/faculty/course" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PUT"/>
        <input type="hidden" name="course_id" value="{{ $c->id }}"/>
        <td style="text-align:center;">
          @if ( !$c->available )
            {{ $c->capacity }}
          @else
            <select name="capacity" class="form-control"
              style="width:auto; height:auto; font-size:.88em;">
              @for ($cap=4; $cap<=15; $cap++)
                @if ($c->capacity == $cap )
                  <option selected>
                @else
                  <option>
                @endif
                  {{ $cap }}</option>
              @endfor
            </select>
          @endif
        </td>
        <td>
          @if ( !$c->available )
            Canceled
          @else
          <input type="submit" class="form-control"
            style="font-size:.8em; height:auto;"
            value="Update"/>
          @endif
      </td>
    </form>
      @if ( !$c->available)
        <td>&nbsp;</td>
      @else
        <form action="/faculty/course" method="POST">
          {{ csrf_field() }}
          <input type="hidden" name="_method" value="DELETE"/>
          <input type="hidden" name="courseId" value="{{ $c->id }}"/>
          <td>
            <input type="submit" class="form-control"
              style="font-size:.8em; height:auto;"
              value="Cancel"/>
          </td>
        </form>
    @endif
    </tr>
  @endforeach
</table>
@endsection
