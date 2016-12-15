# David Lieberman, CSCI E5

## Fall 2016

### Project 4:

#### Published URL: http://p4.davidisadorelieberman.com/

#### Video: https://www.youtube.com/watch?v=lK49rdYWN0w (This went a bit long ... )

#### GitHub: https://github.com/davidilieberman/minniesis

## MiniSIS

This project mimics a subset of the behaviors of the Student Information System deployed at prestigious Nowhere University. NWU is well known for its exceptionally small class sizes and commitment to manageable faculty teaching loads. The in-house SIS enforces these features of academic life at NWU with tools that enable authorized users to create courses, create offerings of those courses, enroll students in offerings, grade enrolled students, and change student majors. These activities are distributed among system actors as follows.

### Registrar

#### Sample user: jamal@harvard.edu

Holders of the Registrar role have visibility into the entire
system. They may view the courses and faculty members of
each of NWU's academic departments and are empowered to create offerings of a department's available courses, deactivate and reactivate existing offerings, enroll students in offerings and withdraw them from offerings. Registrar staff may also review a list of all students matriculated at NWU, and review each student's enrollments, grades and GPA.

These capabilities are constrained by the following business rules:
- Every offering must have a faculty members assigned to teach it
- Faculty members may only teach offerings of courses offered by their own departments
- Observing NWU's commitment to manageable teaching loads, a faculty member may only be assigned to a total of three active course offerings
- A course offering may only accept as many student enrollments as is defined by its course capacity.
- A student may not be enrolled in multiple offerings of the same course.
- A student may carry a maximum of 9 credits in course enrollments.
- Deactivating a course offering releases its faculty member for another teaching assignment and forcefully removes all of its enrolled students.

### Faculty

#### Sample user: stewie@fac.nwr.edu

Holders of the Faculty role have visibility only into those resources personally associated with them. All faculty members have access to a list of the course offerings they are assigned to teach, and may assign grades to students enrolled in those course offerings.

Faculty members designated as the chairs of their departments have additional capabilities. They may add new courses to their departments' course lists, change the enrollment capacity of existing courses, and mark existing courses canceled. Faculty department chairs may also review the academic experience of students majoring in their field of study.

These capabilities are constrained by the following business rules.
- A course code must in the format of a three digit number.
- A new course  may not have the same course code or name as an existing, active course.
- A new course must offer credits in one of the three supported configurations: 1.5 credits, 3.0 credits or 4.0 credits.
- A new course must define a capacity within the range set by NWU's commitment to small class sizes: a minimum of four students and a maximum of fifteen.

### Students

#### Sample user: jill@harvard.edu

Holders of the student role have visibility only into those resources personally associated with them. They may see the own course offering enrollments, enrollment grades and GPAs. Student may additionally choose to change their major to some other field of study on offer at NWU.

#### Out of scope

The following concerns are determined to be out of scope for the NWU SIS implementation.

- The system makes no attempt at user provisioning of faculty, staff or students. Those processes are assumed to be 'upstream' of the current business problem.
- The system makes no attempt to manage scheduling of classroom physical space or to detect scheduling collisions beyond the risk of assigning a student into more than one offering of the same course.

#### Other gaps

These features may be considered for future releases of the NWU SIS.

- Although some departments offer courses clearly designed to be taught in sequence, this version of the SIS has no capability of enforcing prerequisites. It also does not prevent students from being enrolled in more than one course in the same sequence at a time.
- The enrollment system makes not attempt to prioritize department majors for courses offered by their own departments over other students. Similarly, no attempt is made in this version of the SIS to give enrollment priority to upper classmen.
- The set of sample student is sufficiently small that it can be reviewed by a Registrar comfortably on a single page. At student body sizes of fifty or more, however, it would be useful to support pagination of the data in the user interface.
- The implementation of calendar awareness would enable the NWU SIS to produce student transcripts as well as manage the fulfillment of prerequisites to support the offering enrollment process.
