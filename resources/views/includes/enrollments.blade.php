<table class="table table-striped">
  <tr>
    <td colspan="5">
      <h4>Enrollments</h4>
    </td>
  </tr>

  @if ( count($enrollments) == 0)

    <tr>
      <td colspan="5">
        Student is not currently enrolled in any courses.
      </td>
    </tr>

  @else

    <tr>
      <th>Course Code</th>
      <th>Name</th>
      <th>Instructor</th>
      <th>Credits</th>
      <th>Grade</th>
    </tr>


    @foreach ($enrollments as $enrl)
     @if ($enrl->dept_id == $student->department_id)
        <tr style="font-weight:bold;">
     @else
        <tr>
     @endif
        <td>{{ $enrl->dept_code }} {{ $enrl->course_code}}</td>
        <td>{{ $enrl->course_name}}</td>
        <td>{{ $enrl->instructor }}</td>
        <td>{{ $enrl->credits }}</td>
        <td>{{ $enrl->grade }}</td>
      </tr>
    @endforeach

  @endif

</table>
