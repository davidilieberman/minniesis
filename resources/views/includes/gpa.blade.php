@if ($student->gpa)
  <table class="table" style="border-bottom:1px solid #dadada;">
    <tr>
      <td>GPA: {{ number_format($student->gpa, 2) }}</td>
    </tr>
  </table>
@endif
