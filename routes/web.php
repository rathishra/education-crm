<?php
/**
 * Web Routes
 */

use Core\App;

$router = App::getInstance()->router();

// ============================================================
// Guest Routes (Login, Register, Password Reset)
// ============================================================
$router->group(['middleware' => 'guest'], function ($router) {
    $router->get('/login', 'Auth\AuthController@showLogin', 'login');
    $router->post('/login', 'Auth\AuthController@login', 'login.post');
    $router->get('/forgot-password', 'Auth\AuthController@showForgotPassword', 'password.forgot');
    $router->post('/forgot-password', 'Auth\AuthController@forgotPassword', 'password.forgot.post');
    $router->get('/reset-password/{token}', 'Auth\AuthController@showResetPassword', 'password.reset');
    $router->post('/reset-password', 'Auth\AuthController@resetPassword', 'password.reset.post');

    // Public admission form
    $router->get('/apply', 'Front\ApplyController@index', 'apply.form');
    $router->post('/apply', 'Front\ApplyController@submit', 'apply.submit');
    $router->get('/apply/thank-you', 'Front\ApplyController@thankYou', 'apply.thankyou');
});

// ============================================================
// Authenticated Routes
// ============================================================
$router->group(['middleware' => 'auth'], function ($router) {

    // Logout
    $router->post('/logout', 'Auth\AuthController@logout', 'logout');

    // Institution Switch
    $router->post('/switch-institution', 'Auth\AuthController@switchInstitution', 'switch.institution');

    // Dashboard
    $router->get('/', 'Admin\DashboardController@index', 'home');
    $router->get('/dashboard', 'Admin\DashboardController@index', 'dashboard');
    $router->get('/dashboard/stats', 'Admin\DashboardController@stats', 'dashboard.stats');

    // Profile
    $router->get('/profile', 'Auth\AuthController@profile', 'profile');
    $router->post('/profile', 'Auth\AuthController@updateProfile', 'profile.update');
    $router->post('/change-password', 'Auth\AuthController@changePassword', 'password.change');

    // Organizations — static routes MUST come before {id} parameterized routes
    $router->get('/organizations', 'Admin\OrganizationController@index', 'organizations.index');
    $router->get('/organizations/create', 'Admin\OrganizationController@create', 'organizations.create');
    $router->get('/organizations/export', 'Admin\OrganizationController@export', 'organizations.export');
    $router->post('/organizations', 'Admin\OrganizationController@store', 'organizations.store');
    $router->post('/organizations/import', 'Admin\OrganizationController@import', 'organizations.import');
    $router->get('/organizations/{id}', 'Admin\OrganizationController@show', 'organizations.show');
    $router->get('/organizations/{id}/edit', 'Admin\OrganizationController@edit', 'organizations.edit');
    $router->post('/organizations/{id}', 'Admin\OrganizationController@update', 'organizations.update');
    $router->post('/organizations/{id}/delete', 'Admin\OrganizationController@destroy', 'organizations.destroy');
    $router->post('/organizations/{id}/toggle-status', 'Admin\OrganizationController@toggleStatus', 'organizations.toggle_status');

    // Institutions
    $router->get('/institutions', 'Admin\InstitutionController@index', 'institutions.index');
    $router->get('/institutions/create', 'Admin\InstitutionController@create', 'institutions.create');
    $router->post('/institutions', 'Admin\InstitutionController@store', 'institutions.store');
    $router->get('/institutions/{id}', 'Admin\InstitutionController@show', 'institutions.show');
    $router->get('/institutions/{id}/edit', 'Admin\InstitutionController@edit', 'institutions.edit');
    $router->post('/institutions/{id}', 'Admin\InstitutionController@update', 'institutions.update');
    $router->post('/institutions/{id}/delete', 'Admin\InstitutionController@destroy', 'institutions.destroy');

    // Departments
    $router->get('/departments', 'Admin\DepartmentController@index', 'departments.index');
    $router->get('/departments/create', 'Admin\DepartmentController@create', 'departments.create');
    $router->post('/departments', 'Admin\DepartmentController@store', 'departments.store');
    $router->get('/departments/{id}/edit', 'Admin\DepartmentController@edit', 'departments.edit');
    $router->post('/departments/{id}', 'Admin\DepartmentController@update', 'departments.update');
    $router->post('/departments/{id}/delete', 'Admin\DepartmentController@destroy', 'departments.destroy');

    // Academic Years
    $router->get('/academic-years', 'Admin\AcademicYearController@index', 'academic_years.index');
    $router->get('/academic-years/create', 'Admin\AcademicYearController@create', 'academic_years.create');
    $router->post('/academic-years', 'Admin\AcademicYearController@store', 'academic_years.store');
    $router->get('/academic-years/{id}/edit', 'Admin\AcademicYearController@edit', 'academic_years.edit');
    $router->post('/academic-years/{id}', 'Admin\AcademicYearController@update', 'academic_years.update');
    $router->post('/academic-years/{id}/delete', 'Admin\AcademicYearController@destroy', 'academic_years.destroy');

    // Roles — static routes BEFORE {id} parameterized routes
    $router->get('/roles', 'Admin\RoleController@index', 'roles.index');
    $router->get('/roles/create', 'Admin\RoleController@create', 'roles.create');
    $router->post('/roles', 'Admin\RoleController@store', 'roles.store');
    $router->get('/roles/{id}/edit', 'Admin\RoleController@edit', 'roles.edit');
    $router->post('/roles/{id}/update', 'Admin\RoleController@update', 'roles.update');
    $router->post('/roles/{id}/delete', 'Admin\RoleController@destroy', 'roles.destroy');
    $router->post('/roles/{id}/clone', 'Admin\RoleController@clone', 'roles.clone');
    $router->get('/roles/{id}/permissions', 'Admin\RoleController@getPermissions', 'roles.permissions');
    $router->post('/roles/{id}/permissions/save', 'Admin\RoleController@savePermissions', 'roles.permissions.save');

    // Users — static/deep routes BEFORE {id} parameterized routes
    $router->get('/users', 'Admin\UserController@index', 'users.index');
    $router->get('/users/create', 'Admin\UserController@create', 'users.create');
    $router->post('/users', 'Admin\UserController@store', 'users.store');
    $router->get('/users/{id}/permissions', 'Admin\UserController@permissions', 'users.permissions');
    $router->post('/users/{id}/permissions/save', 'Admin\UserController@savePermissions', 'users.permissions.save');
    $router->get('/users/{id}', 'Admin\UserController@show', 'users.show');
    $router->get('/users/{id}/edit', 'Admin\UserController@edit', 'users.edit');
    $router->post('/users/{id}', 'Admin\UserController@update', 'users.update');
    $router->post('/users/{id}/delete', 'Admin\UserController@destroy', 'users.destroy');
    $router->post('/users/{id}/toggle', 'Admin\UserController@toggleStatus', 'users.toggle');

    // Leads — static routes MUST come before {id} parameterised routes
    $router->get('/leads', 'Admin\LeadController@index', 'leads.index');
    $router->get('/leads/create', 'Admin\LeadController@create', 'leads.create');
    $router->get('/leads/import', 'Admin\LeadController@showImport', 'leads.import');
    $router->get('/leads/export', 'Admin\LeadController@export', 'leads.export');
    $router->get('/leads/check-duplicate', 'Admin\LeadController@checkDuplicate', 'leads.check_duplicate');
    $router->get('/leads/ajax/departments', 'Admin\LeadController@ajaxDepartments', 'leads.ajax_departments');
    $router->get('/leads/ajax/courses', 'Admin\LeadController@ajaxCourses', 'leads.ajax_courses');
    $router->post('/leads', 'Admin\LeadController@store', 'leads.store');
    $router->post('/leads/import', 'Admin\LeadController@import', 'leads.import.post');
    $router->get('/leads/{id}', 'Admin\LeadController@show', 'leads.show');
    $router->get('/leads/{id}/edit', 'Admin\LeadController@edit', 'leads.edit');
    $router->post('/leads/{id}', 'Admin\LeadController@update', 'leads.update');
    $router->post('/leads/{id}/delete', 'Admin\LeadController@destroy', 'leads.destroy');
    $router->post('/leads/{id}/assign', 'Admin\LeadController@assign', 'leads.assign');
    $router->post('/leads/{id}/status', 'Admin\LeadController@updateStatus', 'leads.status');
    $router->post('/leads/{id}/activity', 'Admin\LeadController@addActivity', 'leads.activity');
    $router->post('/leads/{id}/followup', 'Admin\LeadController@storeFollowup', 'leads.followup');
    $router->post('/leads/{id}/convert', 'Admin\LeadController@convert', 'leads.convert');

    // CRM Dashboard
    $router->get('/crm/dashboard', 'Admin\CrmDashboardController@index', 'crm.dashboard');

    // Enquiries — static routes before {id} to prevent collision
    $router->get('/enquiries', 'Admin\EnquiryController@index', 'enquiries.index');
    $router->get('/enquiries/create', 'Admin\EnquiryController@create', 'enquiries.create');
    $router->get('/enquiries/export', 'Admin\EnquiryController@export', 'enquiries.export');
    $router->post('/enquiries/bulk', 'Admin\EnquiryController@bulk', 'enquiries.bulk');
    $router->get('/enquiries/check-duplicate', 'Admin\EnquiryController@checkDuplicate', 'enquiries.check_duplicate');
    $router->get('/enquiries/ajax/departments', 'Admin\EnquiryController@ajaxDepartments', 'enquiries.ajax_departments');
    $router->get('/enquiries/ajax/courses', 'Admin\EnquiryController@ajaxCourses', 'enquiries.ajax_courses');
    $router->post('/enquiries', 'Admin\EnquiryController@store', 'enquiries.store');
    $router->get('/enquiries/{id}', 'Admin\EnquiryController@show', 'enquiries.show');
    $router->get('/enquiries/{id}/edit', 'Admin\EnquiryController@edit', 'enquiries.edit');
    $router->post('/enquiries/{id}', 'Admin\EnquiryController@update', 'enquiries.update');
    $router->post('/enquiries/{id}/delete', 'Admin\EnquiryController@destroy', 'enquiries.destroy');
    $router->post('/enquiries/{id}/convert', 'Admin\EnquiryController@convertToLead', 'enquiries.convert');
    $router->post('/enquiries/{id}/convert-to-admission', 'Admin\EnquiryController@convertToAdmission', 'enquiries.convert_admission');
    $router->post('/enquiries/{id}/quick-status', 'Admin\EnquiryController@quickStatus', 'enquiries.quick_status');

    // Follow-ups
    // Follow-ups — static routes before {id} to prevent collision
    $router->get('/followups', 'Admin\FollowupController@index', 'followups.index');
    $router->get('/followups/calendar', 'Admin\FollowupController@calendar', 'followups.calendar');
    $router->get('/followups/create', 'Admin\FollowupController@create', 'followups.create');
    $router->get('/followups/events', 'Admin\FollowupController@events', 'followups.events');
    $router->post('/followups', 'Admin\FollowupController@store', 'followups.store');
    $router->get('/followups/{id}', 'Admin\FollowupController@show', 'followups.show');
    $router->get('/followups/{id}/edit', 'Admin\FollowupController@edit', 'followups.edit');
    $router->post('/followups/{id}', 'Admin\FollowupController@update', 'followups.update');
    $router->post('/followups/{id}/complete', 'Admin\FollowupController@complete', 'followups.complete');
    $router->post('/followups/{id}/reschedule', 'Admin\FollowupController@reschedule', 'followups.reschedule');
    $router->post('/followups/{id}/delete', 'Admin\FollowupController@destroy', 'followups.destroy');

    // Tasks
    $router->get('/tasks', 'Admin\TaskController@index', 'tasks.index');
    $router->post('/tasks', 'Admin\TaskController@store', 'tasks.store');
    $router->post('/tasks/{id}', 'Admin\TaskController@update', 'tasks.update');
    $router->post('/tasks/{id}/status', 'Admin\TaskController@updateStatus', 'tasks.status');
    $router->post('/tasks/{id}/delete', 'Admin\TaskController@destroy', 'tasks.destroy');

    // Courses
    $router->get('/courses', 'Admin\CourseController@index', 'courses.index');
    $router->get('/courses/create', 'Admin\CourseController@create', 'courses.create');
    $router->post('/courses', 'Admin\CourseController@store', 'courses.store');
    $router->get('/courses/{id}/edit', 'Admin\CourseController@edit', 'courses.edit');
    $router->post('/courses/{id}', 'Admin\CourseController@update', 'courses.update');
    $router->post('/courses/{id}/delete', 'Admin\CourseController@destroy', 'courses.destroy');

    // Batches
    $router->get('/batches', 'Admin\BatchController@index', 'batches.index');
    $router->get('/batches/create', 'Admin\BatchController@create', 'batches.create');
    $router->post('/batches', 'Admin\BatchController@store', 'batches.store');
    $router->get('/batches/{id}/subjects', 'Admin\BatchController@subjects', 'batches.subjects');
    $router->post('/batches/{id}/subjects/assign', 'Admin\BatchController@assignSubject', 'batches.subjects.assign');
    $router->post('/batches/{id}/subjects/{subjectId}/remove', 'Admin\BatchController@removeSubject', 'batches.subjects.remove');
    $router->get('/batches/{id}/edit', 'Admin\BatchController@edit', 'batches.edit');
    $router->post('/batches/{id}', 'Admin\BatchController@update', 'batches.update');
    $router->post('/batches/{id}/delete', 'Admin\BatchController@destroy', 'batches.destroy');

    // Admissions — static routes BEFORE parameterized {id} routes
    $router->get('/admissions', 'Admin\AdmissionController@index', 'admissions.index');
    $router->get('/admissions/create', 'Admin\AdmissionController@create', 'admissions.create');
    $router->post('/admissions', 'Admin\AdmissionController@store', 'admissions.store');
    $router->get('/admissions/ajax/departments', 'Admin\AdmissionController@ajaxDepartments', 'admissions.ajax.departments');
    $router->get('/admissions/ajax/courses', 'Admin\AdmissionController@ajaxCourses', 'admissions.ajax.courses');
    $router->get('/admissions/ajax/batches', 'Admin\AdmissionController@ajaxBatches', 'admissions.ajax.batches');
    $router->get('/admissions/ajax/from-lead', 'Admin\AdmissionController@fromLead', 'admissions.ajax.from_lead');
    $router->get('/admissions/check-duplicate', 'Admin\AdmissionController@checkDuplicate', 'admissions.check_duplicate');
    $router->get('/admissions/export', 'Admin\AdmissionController@export', 'admissions.export');
    $router->post('/admissions/bulk', 'Admin\AdmissionController@bulkAction', 'admissions.bulk');
    $router->post('/admissions/{id}/quick-status', 'Admin\AdmissionController@quickStatus', 'admissions.quick_status');
    $router->get('/admissions/{id}', 'Admin\AdmissionController@show', 'admissions.show');
    $router->get('/admissions/{id}/edit', 'Admin\AdmissionController@edit', 'admissions.edit');
    $router->post('/admissions/{id}', 'Admin\AdmissionController@update', 'admissions.update');
    $router->post('/admissions/{id}/approve', 'Admin\AdmissionController@approve', 'admissions.approve');
    $router->post('/admissions/{id}/reject', 'Admin\AdmissionController@reject', 'admissions.reject');
    $router->post('/admissions/{id}/cancel', 'Admin\AdmissionController@cancel', 'admissions.cancel');
    $router->post('/admissions/{id}/reopen', 'Admin\AdmissionController@reopen', 'admissions.reopen');
    $router->post('/admissions/{id}/mark-document-pending', 'Admin\AdmissionController@markDocumentPending', 'admissions.mark_doc_pending');
    $router->post('/admissions/{id}/mark-payment-pending', 'Admin\AdmissionController@markPaymentPending', 'admissions.mark_pay_pending');
    $router->post('/admissions/{id}/enroll', 'Admin\AdmissionController@enroll', 'admissions.enroll');
    $router->post('/admissions/{id}/documents', 'Admin\AdmissionController@storeDocument', 'admissions.documents.store');
    $router->post('/admissions/{id}/documents/verify', 'Admin\AdmissionController@verifyDocument', 'admissions.documents.verify');
    $router->post('/admissions/{id}/payments', 'Admin\AdmissionController@storePayment', 'admissions.payments.store');
    $router->post('/admissions/{id}/notes', 'Admin\AdmissionController@addNote', 'admissions.notes.store');
    $router->get('/admissions/{id}/offer-letter', 'Admin\AdmissionController@offerLetter', 'admissions.offer_letter');
    $router->get('/admissions/{id}/admission-letter', 'Admin\AdmissionController@admissionLetter', 'admissions.admission_letter');
    $router->post('/admissions/{id}/interview', 'Admin\AdmissionController@scheduleInterview', 'admissions.interview.store');
    $router->post('/admissions/{id}/interview-result', 'Admin\AdmissionController@recordInterviewResult', 'admissions.interview.result');

    // Students
    $router->get('/students/dashboard', 'Admin\StudentDashboardController@index', 'students.dashboard');
    $router->get('/students', 'Admin\StudentController@index', 'students.index');
    $router->get('/students/create', 'Admin\StudentController@create', 'students.create');
    $router->post('/students', 'Admin\StudentController@store', 'students.store');
    $router->get('/students/{id}', 'Admin\StudentController@show', 'students.show');
    $router->get('/students/{id}/edit', 'Admin\StudentController@edit', 'students.edit');
    $router->post('/students/{id}', 'Admin\StudentController@update', 'students.update');
    $router->post('/students/{id}/delete', 'Admin\StudentController@destroy', 'students.destroy');
    $router->post('/students/{id}/note', 'Admin\StudentController@addNote', 'students.note');
    $router->post('/students/{id}/assign-section', 'Admin\StudentController@assignSection', 'students.assign_section');
    $router->get('/students/export', 'Admin\StudentController@export', 'students.export');

    // Fee Structures (legacy — replaced by Fees module below)
    // $router->get('/fees', 'Admin\FeeController@index', 'fees.index');
    // $router->get('/fees/create', 'Admin\FeeController@create', 'fees.create');
    // $router->post('/fees', 'Admin\FeeController@store', 'fees.store');
    // $router->get('/fees/{id}', 'Admin\FeeController@show', 'fees.show');
    // $router->get('/fees/{id}/edit', 'Admin\FeeController@edit', 'fees.edit');
    // $router->post('/fees/{id}', 'Admin\FeeController@update', 'fees.update');
    // $router->post('/fees/{id}/delete', 'Admin\FeeController@destroy', 'fees.destroy');

    // Payments (legacy — replaced by Fees/Collection module below)
    // $router->get('/payments', 'Admin\PaymentController@index', 'payments.index');
    // $router->get('/payments/collect/{student_id}', 'Admin\PaymentController@collect', 'payments.collect');
    // $router->post('/payments/collect', 'Admin\PaymentController@store', 'payments.store');
    // $router->get('/payments/{id}/receipt', 'Admin\PaymentController@receipt', 'payments.receipt');
    // $router->get('/payments/due-list', 'Admin\PaymentController@dueList', 'payments.due');

    // Communication
    $router->get('/communication/templates', 'Admin\CommunicationController@templates', 'communication.templates');
    $router->post('/communication/templates', 'Admin\CommunicationController@storeTemplate', 'communication.templates.store');
    $router->post('/communication/templates/{id}', 'Admin\CommunicationController@updateTemplate', 'communication.templates.update');
    $router->post('/communication/send', 'Admin\CommunicationController@send', 'communication.send');
    $router->get('/communication/bulk', 'Admin\CommunicationController@bulkForm', 'communication.bulk');
    $router->post('/communication/bulk', 'Admin\CommunicationController@sendBulk', 'communication.bulk.send');
    $router->get('/communication/log', 'Admin\CommunicationController@log', 'communication.log');

    // Attendance
    $router->get('/attendance', 'Admin\AttendanceController@index', 'attendance.view');
    $router->post('/attendance/store', 'Admin\AttendanceController@store', 'attendance.store');
    $router->get('/attendance/report', 'Admin\AttendanceController@report', 'attendance.reports');

    // Academics: Subjects
    $router->get('/subjects', 'Admin\SubjectController@index', 'subjects.index');
    $router->get('/subjects/create', 'Admin\SubjectController@create', 'subjects.create');
    $router->post('/subjects', 'Admin\SubjectController@store', 'subjects.store');
    $router->get('/subjects/{id}/edit', 'Admin\SubjectController@edit', 'subjects.edit');
    $router->post('/subjects/{id}', 'Admin\SubjectController@update', 'subjects.update');
    $router->post('/subjects/{id}/delete', 'Admin\SubjectController@destroy', 'subjects.destroy');

    // Academics: Timetable
    $router->get('/timetable', 'Admin\TimetableController@index', 'timetable.index');
    $router->get('/timetable/create', 'Admin\TimetableController@create', 'timetable.create');
    $router->post('/timetable', 'Admin\TimetableController@store', 'timetable.store');
    $router->get('/timetable/{id}/edit', 'Admin\TimetableController@edit', 'timetable.edit');
    $router->post('/timetable/{id}', 'Admin\TimetableController@update', 'timetable.update');
    $router->post('/timetable/{id}/delete', 'Admin\TimetableController@destroy', 'timetable.destroy');

    // Exams
    $router->get('/exams', 'Admin\ExamController@index', 'exams.index');
    $router->get('/exams/create', 'Admin\ExamController@create', 'exams.create');
    $router->post('/exams', 'Admin\ExamController@store', 'exams.store');
    $router->get('/exams/{id}', 'Admin\ExamController@show', 'exams.show');
    $router->post('/exams/{id}/schedule', 'Admin\ExamController@addSchedule', 'exams.schedule.add');
    $router->post('/exams/{id}/schedule/{scheduleId}/delete', 'Admin\ExamController@deleteSchedule', 'exams.schedule.delete');
    $router->get('/exams/{id}/schedule/{scheduleId}/marks', 'Admin\ExamController@marks', 'exams.marks');
    $router->post('/exams/{id}/schedule/{scheduleId}/marks', 'Admin\ExamController@storeMarks', 'exams.marks.store');

    // Hostel
    $router->get('/hostels', 'Admin\HostelController@index', 'hostels.index');
    $router->post('/hostels', 'Admin\HostelController@store', 'hostels.store');
    $router->get('/hostels/allocations', 'Admin\HostelController@allocations', 'hostels.allocations');
    $router->post('/hostels/allocations', 'Admin\HostelController@createAllocation', 'hostels.allocations.store');
    $router->get('/hostels/{id}/rooms', 'Admin\HostelController@rooms', 'hostels.rooms');
    $router->post('/hostels/{id}/rooms', 'Admin\HostelController@storeRoom', 'hostels.rooms.store');

    // Transport
    $router->get('/transport', 'Admin\TransportController@index', 'transport.index');
    $router->post('/transport', 'Admin\TransportController@store', 'transport.store');
    $router->get('/transport/allocations', 'Admin\TransportController@allocations', 'transport.allocations');
    $router->post('/transport/allocations', 'Admin\TransportController@createAllocation', 'transport.allocations.store');
    $router->get('/transport/{id}/stops', 'Admin\TransportController@stops', 'transport.stops');
    $router->post('/transport/{id}/stops', 'Admin\TransportController@storeStop', 'transport.stops.store');

    // Library
    $router->get('/library', 'Admin\LibraryController@index', 'library.index');
    $router->get('/library/create', 'Admin\LibraryController@create', 'library.create');
    $router->post('/library', 'Admin\LibraryController@store', 'library.store');
    $router->get('/library/issues', 'Admin\LibraryController@issues', 'library.issues');
    $router->post('/library/issues', 'Admin\LibraryController@storeIssue', 'library.issues.store');
    $router->post('/library/issues/{id}/return', 'Admin\LibraryController@processReturn', 'library.issues.return');

    // Placement
    $router->get('/placement/companies', 'Admin\PlacementController@companies', 'placement.companies');
    $router->post('/placement/companies', 'Admin\PlacementController@storeCompany', 'placement.companies.store');
    $router->get('/placement/drives', 'Admin\PlacementController@drives', 'placement.drives');
    $router->post('/placement/drives', 'Admin\PlacementController@storeDrive', 'placement.drives.store');
    $router->get('/placement/drives/{id}/applications', 'Admin\PlacementController@applications', 'placement.applications');
    $router->post('/placement/drives/{id}/applications', 'Admin\PlacementController@storeApplication', 'placement.applications.store');
    $router->post('/placement/drives/{id}/applications/{appId}', 'Admin\PlacementController@updateApplication', 'placement.applications.update');

    // HR & Payroll
    $router->get('/hr/staff', 'Admin\StaffController@index', 'hr.staff');
    $router->get('/hr/staff/{id}/edit', 'Admin\StaffController@edit', 'hr.staff.edit');
    $router->post('/hr/staff/{id}/edit', 'Admin\StaffController@update', 'hr.staff.update');

    $router->get('/hr/payroll', 'Admin\PayrollController@index', 'hr.payroll');
    $router->post('/hr/payroll/generate', 'Admin\PayrollController@generate', 'hr.payroll.generate');
    $router->post('/hr/payroll/{id}/process', 'Admin\PayrollController@process', 'hr.payroll.process');

    // ── Faculty Management ─────────────────────────────────────
    $router->get('/faculty',                            'Admin\FacultyController@index',          'faculty.index');
    $router->get('/faculty/create',                     'Admin\FacultyController@create',         'faculty.create');
    $router->post('/faculty',                           'Admin\FacultyController@store',          'faculty.store');
    $router->get('/faculty/export',                     'Admin\FacultyController@export',         'faculty.export');
    // Leave (must come BEFORE /faculty/{id} to avoid ID clash)
    $router->get('/faculty/leave',                      'Admin\FacultyController@leave',          'faculty.leave');
    $router->post('/faculty/leave/apply',               'Admin\FacultyController@leaveStore',     'faculty.leave.apply');
    $router->post('/faculty/leave/{id}/action',         'Admin\FacultyController@leaveAction',    'faculty.leave.action');
    // Performance
    $router->get('/faculty/performance',                'Admin\FacultyController@performance',    'faculty.performance');
    $router->post('/faculty/performance/save',          'Admin\FacultyController@performanceSave','faculty.performance.save');
    // Attendance
    $router->get('/faculty/attendance',                 'Admin\FacultyController@attendance',     'faculty.attendance');
    $router->post('/faculty/attendance/mark',           'Admin\FacultyController@attendanceMark', 'faculty.attendance.mark');
    $router->post('/faculty/attendance/bulk',           'Admin\FacultyController@attendanceBulk', 'faculty.attendance.bulk');
    // Profile (after static routes)
    $router->get('/faculty/{id}',                       'Admin\FacultyController@show',           'faculty.show');
    $router->get('/faculty/{id}/edit',                  'Admin\FacultyController@edit',           'faculty.edit');
    $router->post('/faculty/{id}/update',               'Admin\FacultyController@update',         'faculty.update');

    // Institutions — add toggle status route
    $router->post('/institutions/{id}/toggle-status', 'Admin\InstitutionController@toggleStatus', 'institutions.toggle_status');

    // Campuses
    $router->get('/campuses', 'Admin\CampusController@index', 'campuses.index');
    $router->get('/campuses/create', 'Admin\CampusController@create', 'campuses.create');
    $router->post('/campuses', 'Admin\CampusController@store', 'campuses.store');
    $router->get('/campuses/{id}', 'Admin\CampusController@edit', 'campuses.show');
    $router->get('/campuses/{id}/edit', 'Admin\CampusController@edit', 'campuses.edit');
    $router->post('/campuses/{id}', 'Admin\CampusController@update', 'campuses.update');
    $router->post('/campuses/{id}/edit', 'Admin\CampusController@update', 'campuses.update_alt');
    $router->post('/campuses/{id}/delete', 'Admin\CampusController@destroy', 'campuses.destroy');
    $router->post('/campuses/{id}/toggle-status', 'Admin\CampusController@toggleStatus', 'campuses.toggle_status');

    // Sections
    $router->get('/sections', 'Admin\SectionController@index', 'sections.index');
    $router->post('/sections', 'Admin\SectionController@store', 'sections.store');
    $router->post('/sections/auto-generate', 'Admin\SectionController@autoGenerate', 'sections.auto_generate');
    $router->post('/sections/{id}', 'Admin\SectionController@update', 'sections.update');
    $router->post('/sections/{id}/delete', 'Admin\SectionController@destroy', 'sections.destroy');

    // Reports
    $router->get('/reports', 'Admin\ReportController@index', 'reports.index');
    $router->get('/reports/leads', 'Admin\ReportController@leads', 'reports.leads');
    $router->get('/reports/admissions', 'Admin\ReportController@admissions', 'reports.admissions');
    $router->get('/reports/revenue', 'Admin\ReportController@revenue', 'reports.revenue');
    $router->get('/reports/counselor-performance', 'Admin\ReportController@counselorPerformance', 'reports.counselor');
    $router->get('/reports/institution-wise', 'Admin\ReportController@institutionWise', 'reports.institution');

    // Settings
    $router->get('/settings', 'Admin\SettingController@index', 'settings.index');
    $router->post('/settings', 'Admin\SettingController@update', 'settings.update');
    $router->get('/settings/communication', 'Admin\SettingController@communication', 'settings.communication');
    $router->post('/settings/communication', 'Admin\SettingController@updateCommunication', 'settings.communication.update');

    // Notifications
    $router->get('/notifications', 'Admin\NotificationController@index', 'notifications.index');
    $router->post('/notifications/{id}/read', 'Admin\NotificationController@markRead', 'notifications.read');
    $router->post('/notifications/read-all', 'Admin\NotificationController@markAllRead', 'notifications.readAll');
    $router->get('/notifications/unread-count', 'Admin\NotificationController@unreadCount', 'notifications.unread');

    // Documents
    $router->post('/documents/upload', 'Admin\DocumentController@upload', 'documents.upload');
    $router->post('/documents/{id}/verify', 'Admin\DocumentController@verify', 'documents.verify');
    $router->post('/documents/{id}/delete', 'Admin\DocumentController@destroy', 'documents.destroy');
    $router->get('/documents/{id}/download', 'Admin\DocumentController@download', 'documents.download');

    // Audit Logs
    $router->get('/audit-logs', 'Admin\AuditController@index', 'audit.index');
    // ============================================================
    // NEW ACADEMIC MODULE (Namespace: Academic)
    // ============================================================
    
    // Academic Subjects — static routes BEFORE {id} parameterized routes
    $router->get('/academic/subjects',                   'Academic\SubjectController@index',        'academic.subjects.index');
    $router->get('/academic/subjects/create',            'Academic\SubjectController@create',       'academic.subjects.create');
    $router->post('/academic/subjects/store',            'Academic\SubjectController@store',        'academic.subjects.store');
    $router->post('/academic/subjects/bulk',             'Academic\SubjectController@bulkAction',   'academic.subjects.bulk');
    $router->get('/academic/subjects/export',            'Academic\SubjectController@export',       'academic.subjects.export');
    $router->get('/academic/subjects/ajax/by-course',    'Academic\SubjectController@ajaxByCourse', 'academic.subjects.ajax.bycourse');
    $router->get('/academic/subjects/{id}',              'Academic\SubjectController@show',         'academic.subjects.show');
    $router->get('/academic/subjects/edit/{id}',         'Academic\SubjectController@edit',         'academic.subjects.edit');
    $router->post('/academic/subjects/update/{id}',      'Academic\SubjectController@update',       'academic.subjects.update');
    $router->post('/academic/subjects/delete/{id}',      'Academic\SubjectController@delete',       'academic.subjects.delete');
    $router->post('/academic/subjects/{id}/toggle-status','Academic\SubjectController@toggleStatus','academic.subjects.toggle');
    $router->post('/academic/subjects/{id}/duplicate',   'Academic\SubjectController@duplicate',    'academic.subjects.duplicate');

    // Academic Classrooms — full CRUD
    $router->get('/academic/classrooms',               'Academic\ClassroomController@index',   'academic.classrooms.index');
    $router->post('/academic/classrooms/store',        'Academic\ClassroomController@store',   'academic.classrooms.store');
    $router->get('/academic/classrooms/{id}/json',     'Academic\ClassroomController@getOne',  'academic.classrooms.get');
    $router->post('/academic/classrooms/{id}/update',  'Academic\ClassroomController@update',  'academic.classrooms.update');
    $router->post('/academic/classrooms/{id}/toggle',  'Academic\ClassroomController@toggle',  'academic.classrooms.toggle');
    $router->post('/academic/classrooms/{id}/delete',  'Academic\ClassroomController@destroy', 'academic.classrooms.delete');

    // Academic Periods — static routes BEFORE {id} parameterized routes
    $router->get('/academic/periods',                  'Academic\PeriodController@index',        'academic.periods.index');
    $router->post('/academic/periods/store',           'Academic\PeriodController@store',        'academic.periods.store');
    $router->post('/academic/periods/seed-defaults',   'Academic\PeriodController@seedDefaults', 'academic.periods.seed');
    $router->post('/academic/periods/clear',           'Academic\PeriodController@clearAll',     'academic.periods.clear');
    $router->get('/academic/periods/{id}/json',        'Academic\PeriodController@getOne',       'academic.periods.get');
    $router->post('/academic/periods/{id}/update',     'Academic\PeriodController@update',       'academic.periods.update');
    $router->post('/academic/periods/{id}/delete',     'Academic\PeriodController@destroy',      'academic.periods.delete');

    // Academic Batches — static routes BEFORE {id} parameterized routes
    $router->get('/academic/batches', 'Academic\BatchController@index', 'academic.batches.index');
    $router->get('/academic/batches/create', 'Academic\BatchController@create', 'academic.batches.create');
    $router->post('/academic/batches/store', 'Academic\BatchController@store', 'academic.batches.store');
    $router->get('/academic/batches/{id}', 'Academic\BatchController@show', 'academic.batches.show');
    $router->get('/academic/batches/edit/{id}', 'Academic\BatchController@edit', 'academic.batches.edit');
    $router->post('/academic/batches/update/{id}', 'Academic\BatchController@update', 'academic.batches.update');
    $router->post('/academic/batches/delete/{id}', 'Academic\BatchController@destroy', 'academic.batches.delete');

    // Academic Sections — static routes BEFORE {id} parameterized routes
    $router->get('/academic/sections', 'Academic\SectionController@index', 'academic.sections.index');
    $router->get('/academic/sections/create', 'Academic\SectionController@create', 'academic.sections.create');
    $router->post('/academic/sections/store', 'Academic\SectionController@store', 'academic.sections.store');
    $router->get('/academic/sections/{id}', 'Academic\SectionController@show', 'academic.sections.show');
    $router->get('/academic/sections/edit/{id}', 'Academic\SectionController@edit', 'academic.sections.edit');
    $router->post('/academic/sections/update/{id}', 'Academic\SectionController@update', 'academic.sections.update');
    $router->post('/academic/sections/delete/{id}', 'Academic\SectionController@destroy', 'academic.sections.delete');
    $router->post('/academic/sections/{id}/enroll', 'Academic\SectionController@enroll', 'academic.sections.enroll');
    $router->post('/academic/sections/unenroll/{enrollId}', 'Academic\SectionController@unenroll', 'academic.sections.unenroll');

    // Academic Timetable — static routes BEFORE {id} parameterized routes
    $router->get('/academic/timetable',                        'Academic\TimetableController@index',          'academic.timetable.index');
    $router->get('/academic/timetable/generator',              'Academic\TimetableController@generator',      'academic.timetable.generator');
    $router->post('/academic/timetable/store',                 'Academic\TimetableController@store',          'academic.timetable.store');
    $router->post('/academic/timetable/copy',                  'Academic\TimetableController@copyTimetable',  'academic.timetable.copy');
    $router->post('/academic/timetable/clear',                 'Academic\TimetableController@clearSection',   'academic.timetable.clear');
    $router->get('/academic/timetable/ajax/faculty',           'Academic\TimetableController@ajaxFaculty',    'academic.timetable.ajax.faculty');
    $router->get('/academic/timetable/ajax/sections',          'Academic\TimetableController@ajaxSections',   'academic.timetable.ajax.sections');
    $router->get('/academic/timetable/{id}/view',              'Academic\TimetableController@viewTimetable',  'academic.timetable.view');

    // Academic Attendance — static routes BEFORE {id} parameterized routes
    $router->get('/academic/attendance', 'Academic\AttendanceController@index', 'academic.attendance.index');
    $router->get('/academic/attendance/mark', 'Academic\AttendanceController@mark', 'academic.attendance.mark');
    $router->post('/academic/attendance/store', 'Academic\AttendanceController@store', 'academic.attendance.store');
    $router->get('/academic/attendance/history', 'Academic\AttendanceController@history', 'academic.attendance.history');
    $router->get('/academic/attendance/report', 'Academic\AttendanceController@report', 'academic.attendance.report');

    // Academic Assessments — static routes BEFORE {id}
    $router->get('/academic/assessments',               'Academic\AssessmentController@index',      'academic.assessments.index');
    $router->get('/academic/assessments/create',        'Academic\AssessmentController@create',     'academic.assessments.create');
    $router->post('/academic/assessments/store',        'Academic\AssessmentController@store',      'academic.assessments.store');
    $router->get('/academic/assessments/marks',         'Academic\AssessmentController@marks',      'academic.assessments.marks');
    $router->post('/academic/assessments/marks/store',  'Academic\AssessmentController@storeMarks', 'academic.assessments.marks.store');
    $router->get('/academic/assessments/{id}/show',     'Academic\AssessmentController@show',       'academic.assessments.show');
    $router->get('/academic/assessments/{id}/edit',     'Academic\AssessmentController@edit',       'academic.assessments.edit');
    $router->post('/academic/assessments/{id}/update',  'Academic\AssessmentController@update',     'academic.assessments.update');
    $router->post('/academic/assessments/{id}/publish', 'Academic\AssessmentController@publish',    'academic.assessments.publish');
    $router->post('/academic/assessments/{id}/delete',  'Academic\AssessmentController@destroy',    'academic.assessments.delete');

    // Grading Schemas — all static paths BEFORE {id} params
    $router->get('/academic/grading-schemas', 'Academic\GradingSchemaController@index', 'academic.grading.index');
    $router->post('/academic/grading-schemas/store', 'Academic\GradingSchemaController@store', 'academic.grading.store');
    // Category
    $router->post('/academic/grading-schemas/categories/store', 'Academic\GradingSchemaController@storeCategory', 'academic.grading.cat.store');
    $router->post('/academic/grading-schemas/categories/delete/{id}', 'Academic\GradingSchemaController@destroyCategory', 'academic.grading.cat.delete');
    // Component
    $router->post('/academic/grading-schemas/components/store', 'Academic\GradingSchemaController@storeComponent', 'academic.grading.comp.store');
    $router->post('/academic/grading-schemas/components/update/{id}', 'Academic\GradingSchemaController@updateComponent', 'academic.grading.comp.update');
    $router->post('/academic/grading-schemas/components/delete/{id}', 'Academic\GradingSchemaController@destroyComponent', 'academic.grading.comp.delete');
    // Sub-component
    $router->post('/academic/grading-schemas/sub-components/store', 'Academic\GradingSchemaController@storeSubComponent', 'academic.grading.sub.store');
    $router->post('/academic/grading-schemas/sub-components/delete/{id}', 'Academic\GradingSchemaController@destroySubComponent', 'academic.grading.sub.delete');
    // Grade rules
    $router->post('/academic/grading-schemas/rules/store', 'Academic\GradingSchemaController@storeRule', 'academic.grading.rule.store');
    $router->post('/academic/grading-schemas/rules/delete/{id}', 'Academic\GradingSchemaController@destroyRule', 'academic.grading.rule.delete');
    // Parameterised — LAST
    $router->get('/academic/grading-schemas/{id}/detail', 'Academic\GradingSchemaController@detail', 'academic.grading.detail');
    $router->post('/academic/grading-schemas/update/{id}', 'Academic\GradingSchemaController@update', 'academic.grading.update');
    $router->post('/academic/grading-schemas/delete/{id}', 'Academic\GradingSchemaController@destroy', 'academic.grading.delete');

    // Faculty Allocation — static routes BEFORE {id}
    $router->get('/academic/faculty-allocation',                    'Academic\FacultyAllocationController@index',        'academic.faculty.index');
    $router->get('/academic/faculty-allocation/create',             'Academic\FacultyAllocationController@create',       'academic.faculty.create');
    $router->post('/academic/faculty-allocation/store',             'Academic\FacultyAllocationController@store',        'academic.faculty.store');
    $router->get('/academic/faculty-allocation/ajax/sections',      'Academic\FacultyAllocationController@ajaxSections', 'academic.faculty.ajax.sections');
    $router->get('/academic/faculty-allocation/ajax/workload',      'Academic\FacultyAllocationController@workload',     'academic.faculty.ajax.workload');
    $router->get('/academic/faculty-allocation/{id}/edit',          'Academic\FacultyAllocationController@edit',         'academic.faculty.edit');
    $router->post('/academic/faculty-allocation/{id}/update',       'Academic\FacultyAllocationController@update',       'academic.faculty.update');
    $router->post('/academic/faculty-allocation/{id}/delete',       'Academic\FacultyAllocationController@destroy',      'academic.faculty.delete');

    // LMS — static routes BEFORE {id}
    $router->get('/academic/lms',               'Academic\LmsController@index',   'academic.lms.index');
    $router->get('/academic/lms/create',        'Academic\LmsController@create',  'academic.lms.create');
    $router->post('/academic/lms/store',        'Academic\LmsController@store',   'academic.lms.store');
    $router->get('/academic/lms/{id}/edit',     'Academic\LmsController@edit',    'academic.lms.edit');
    $router->post('/academic/lms/{id}/update',  'Academic\LmsController@update',  'academic.lms.update');
    $router->post('/academic/lms/{id}/toggle',  'Academic\LmsController@toggle',  'academic.lms.toggle');
    $router->get('/academic/lms/{id}/download', 'Academic\LmsController@download','academic.lms.download');
    $router->post('/academic/lms/{id}/delete',  'Academic\LmsController@destroy', 'academic.lms.delete');

    // Subject Allocation — batch ↔ subject management
    $router->get('/academic/subject-allocation',                   'Academic\SubjectAllocationController@index',     'academic.subjectalloc.index');
    $router->post('/academic/subject-allocation/assign',           'Academic\SubjectAllocationController@assign',    'academic.subjectalloc.assign');
    $router->post('/academic/subject-allocation/bulk-copy',        'Academic\SubjectAllocationController@bulkCopy',  'academic.subjectalloc.copy');
    $router->post('/academic/subject-allocation/{id}/remove',      'Academic\SubjectAllocationController@remove',    'academic.subjectalloc.remove');
    $router->post('/academic/subject-allocation/{id}/update',      'Academic\SubjectAllocationController@updateRow', 'academic.subjectalloc.update');

    // Subject AJAX
    $router->get('/academic/subjects/ajax/by-course', 'Academic\SubjectController@ajaxByCourse', 'academic.subjects.ajax.bycourse');

    // ============================================================
    // FEES MODULE (Namespace: Fees)
    // ============================================================

    // Fees landing — redirect to reports/hub
    $router->get('/fees', 'Fees\FeeReportController@index', 'fees.home');

    // Fee Heads Master
    $router->get('/fees/heads',                     'Fees\FeeHeadController@index',   'fees.heads.index');
    $router->post('/fees/heads/store',              'Fees\FeeHeadController@store',   'fees.heads.store');
    $router->get('/fees/heads/{id}/json',           'Fees\FeeHeadController@getOne',  'fees.heads.get');
    $router->post('/fees/heads/{id}/update',        'Fees\FeeHeadController@update',  'fees.heads.update');
    $router->post('/fees/heads/{id}/toggle',        'Fees\FeeHeadController@toggle',  'fees.heads.toggle');
    $router->post('/fees/heads/{id}/delete',        'Fees\FeeHeadController@destroy', 'fees.heads.delete');

    // Fee Structures — static routes BEFORE {id}
    $router->get('/fees/structures',                        'Fees\FeeStructureController@index',        'fees.structures.index');
    $router->get('/fees/structures/create',                 'Fees\FeeStructureController@create',       'fees.structures.create');
    $router->post('/fees/structures/store',                 'Fees\FeeStructureController@store',        'fees.structures.store');
    $router->get('/fees/structures/ajax/batches',           'Fees\FeeStructureController@ajaxBatches',  'fees.structures.ajax.batches');
    $router->get('/fees/structures/{id}/edit',              'Fees\FeeStructureController@edit',         'fees.structures.edit');
    $router->post('/fees/structures/{id}/update',           'Fees\FeeStructureController@update',       'fees.structures.update');
    $router->post('/fees/structures/{id}/toggle',           'Fees\FeeStructureController@toggleStatus', 'fees.structures.toggle');
    $router->post('/fees/structures/{id}/copy',             'Fees\FeeStructureController@copy',         'fees.structures.copy');
    $router->post('/fees/structures/{id}/delete',           'Fees\FeeStructureController@destroy',      'fees.structures.delete');

    // Fee Assignment — static routes BEFORE {id}
    $router->get('/fees/assignment',                        'Fees\FeeAssignmentController@index',       'fees.assignment.index');
    $router->get('/fees/assignment/export',                 'Fees\FeeAssignmentController@export',      'fees.assignment.export');
    $router->post('/fees/assignment/assign',                'Fees\FeeAssignmentController@assign',      'fees.assignment.assign');
    $router->post('/fees/assignment/bulk-assign',           'Fees\FeeAssignmentController@bulkAssign',  'fees.assignment.bulk');
    $router->get('/fees/assignment/ajax/search',            'Fees\FeeAssignmentController@ajaxSearch',  'fees.assignment.ajax.search');
    $router->get('/fees/assignment/ajax/structures',        'Fees\FeeAssignmentController@ajaxStructures','fees.assignment.ajax.structures');
    $router->get('/fees/assignment/ajax/student-assignments','Fees\FeeAssignmentController@ajaxStudentAssignments','fees.assignment.ajax.student');
    $router->post('/fees/assignment/{id}/waive',            'Fees\FeeAssignmentController@waive',       'fees.assignment.waive');

    // Fee Collection — cashier screen — static routes BEFORE {id}
    $router->get('/fees/collection',                        'Fees\FeeCollectionController@index',       'fees.collection.index');
    $router->get('/fees/collection/student-fees',           'Fees\FeeCollectionController@studentFees', 'fees.collection.student_fees');
    $router->get('/fees/collection/student-receipts',       'Fees\FeeCollectionController@studentReceipts','fees.collection.student_receipts');
    $router->post('/fees/collection/collect',               'Fees\FeeCollectionController@collect',     'fees.collection.collect');
    $router->get('/fees/receipts/{id}/view',                'Fees\FeeCollectionController@receipt',     'fees.receipts.view');
    $router->get('/fees/receipts/{id}/print',               'Fees\FeeCollectionController@receiptPrint','fees.receipts.print');
    $router->post('/fees/receipts/{id}/cancel',             'Fees\FeeCollectionController@cancel',      'fees.receipts.cancel');

    // Fee Concessions — static routes BEFORE {id}
    $router->get('/fees/concessions',                       'Fees\FeeConcessionController@index',       'fees.concessions.index');
    $router->post('/fees/concessions/store',                'Fees\FeeConcessionController@store',       'fees.concessions.store');
    $router->post('/fees/concessions/{id}/approve',         'Fees\FeeConcessionController@approve',     'fees.concessions.approve');
    $router->post('/fees/concessions/{id}/reject',          'Fees\FeeConcessionController@reject',      'fees.concessions.reject');
    $router->post('/fees/concessions/{id}/delete',          'Fees\FeeConcessionController@destroy',     'fees.concessions.delete');

    // Fee Refunds — static routes BEFORE {id}
    $router->get('/fees/refunds',                           'Fees\FeeRefundController@index',           'fees.refunds.index');
    $router->post('/fees/refunds/store',                    'Fees\FeeRefundController@store',           'fees.refunds.store');
    $router->post('/fees/refunds/{id}/approve',             'Fees\FeeRefundController@approve',         'fees.refunds.approve');
    $router->post('/fees/refunds/{id}/process',             'Fees\FeeRefundController@process',         'fees.refunds.process');
    $router->post('/fees/refunds/{id}/reject',              'Fees\FeeRefundController@reject',          'fees.refunds.reject');

    // Fee Reports — static routes BEFORE {id}
    $router->get('/fees/reports',                           'Fees\FeeReportController@index',           'fees.reports.index');
    $router->get('/fees/reports/collection',                'Fees\FeeReportController@collection',      'fees.reports.collection');
    $router->get('/fees/reports/collection/export',         'Fees\FeeReportController@exportCollection','fees.reports.collection.export');
    $router->get('/fees/reports/pending',                   'Fees\FeeReportController@pending',         'fees.reports.pending');
    $router->get('/fees/reports/pending/export',            'Fees\FeeReportController@exportPending',   'fees.reports.pending.export');
    $router->get('/fees/reports/ledger/{studentId}',        'Fees\FeeReportController@ledger',          'fees.reports.ledger');

    // Fee Dashboard (overview landing page replacing old /fees)
    $router->get('/fees',                                   'Fees\FeeReportController@dashboard',       'fees.dashboard');

});

// ============================================================
// Student Portal — Guest Routes (login, forgot/reset password)
// ============================================================
$router->group(['prefix' => 'portal/student', 'middleware' => 'portal_guest'], function ($router) {
    $router->get('/login',                      'Portal\Auth\PortalAuthController@showLogin',          'portal.login');
    $router->post('/login',                     'Portal\Auth\PortalAuthController@login',              'portal.login.post');
    $router->get('/forgot-password',            'Portal\Auth\PortalAuthController@showForgotPassword', 'portal.forgot');
    $router->post('/forgot-password',           'Portal\Auth\PortalAuthController@forgotPassword',     'portal.forgot.post');
    $router->get('/reset-password/{token}',     'Portal\Auth\PortalAuthController@showReset',          'portal.reset');
    $router->post('/reset-password',            'Portal\Auth\PortalAuthController@resetPassword',      'portal.reset.post');
});

// ============================================================
// Student Portal — Authenticated Routes
// ============================================================
$router->group(['prefix' => 'portal/student', 'middleware' => 'portal_auth'], function ($router) {
    $router->post('/logout',                    'Portal\Auth\PortalAuthController@logout',             'portal.logout');

    // Dashboard
    $router->get('/dashboard',                  'Portal\DashboardController@index',                    'portal.dashboard');

    // Profile
    $router->get('/profile',                    'Portal\ProfileController@index',                      'portal.profile');
    $router->post('/profile/change-password',   'Portal\ProfileController@changePassword',             'portal.profile.password');

    // Fees & Payments
    $router->get('/fees',                       'Portal\FeeController@index',                          'portal.fees');
    $router->get('/fees/receipt/{id}',          'Portal\FeeController@receipt',                        'portal.fees.receipt');
    $router->get('/fees/receipt/{id}/print',    'Portal\FeeController@printReceipt',                   'portal.fees.receipt.print');

    // Attendance
    $router->get('/attendance',                 'Portal\AttendanceController@index',                   'portal.attendance');

    // Timetable
    $router->get('/timetable',                  'Portal\TimetableController@index',                    'portal.timetable');

    // Exams & Results
    $router->get('/exams',                      'Portal\ExamController@index',                         'portal.exams');
    $router->get('/exams/results',              'Portal\ExamController@results',                       'portal.exams.results');

    // LMS / Course Materials
    $router->get('/lms',                        'Portal\LmsController@index',                          'portal.lms');
    $router->get('/lms/download/{id}',          'Portal\LmsController@download',                       'portal.lms.download');

    // Documents
    $router->get('/documents',                  'Portal\DocumentController@index',                     'portal.documents');

    // Notifications
    $router->get('/notifications',              'Portal\NotificationController@index',                 'portal.notifications');
    $router->post('/notifications/{id}/read',   'Portal\NotificationController@markRead',              'portal.notifications.read');
});

