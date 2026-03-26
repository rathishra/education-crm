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

    // Users
    $router->get('/users', 'Admin\UserController@index', 'users.index');
    $router->get('/users/create', 'Admin\UserController@create', 'users.create');
    $router->post('/users', 'Admin\UserController@store', 'users.store');
    $router->get('/users/{id}', 'Admin\UserController@show', 'users.show');
    $router->get('/users/{id}/edit', 'Admin\UserController@edit', 'users.edit');
    $router->post('/users/{id}', 'Admin\UserController@update', 'users.update');
    $router->post('/users/{id}/delete', 'Admin\UserController@destroy', 'users.destroy');
    $router->post('/users/{id}/toggle', 'Admin\UserController@toggleStatus', 'users.toggle');

    // Leads
    $router->get('/leads', 'Admin\LeadController@index', 'leads.index');
    $router->get('/leads/create', 'Admin\LeadController@create', 'leads.create');
    $router->post('/leads', 'Admin\LeadController@store', 'leads.store');
    $router->get('/leads/{id}', 'Admin\LeadController@show', 'leads.show');
    $router->get('/leads/{id}/edit', 'Admin\LeadController@edit', 'leads.edit');
    $router->post('/leads/{id}', 'Admin\LeadController@update', 'leads.update');
    $router->post('/leads/{id}/delete', 'Admin\LeadController@destroy', 'leads.destroy');
    $router->post('/leads/{id}/assign', 'Admin\LeadController@assign', 'leads.assign');
    $router->post('/leads/{id}/status', 'Admin\LeadController@updateStatus', 'leads.status');
    $router->post('/leads/{id}/activity', 'Admin\LeadController@addActivity', 'leads.activity');
    $router->post('/leads/{id}/convert', 'Admin\LeadController@convert', 'leads.convert');
    $router->get('/leads/import', 'Admin\LeadController@showImport', 'leads.import');
    $router->post('/leads/import', 'Admin\LeadController@import', 'leads.import.post');
    $router->get('/leads/export', 'Admin\LeadController@export', 'leads.export');

    // Enquiries
    $router->get('/enquiries', 'Admin\EnquiryController@index', 'enquiries.index');
    $router->get('/enquiries/create', 'Admin\EnquiryController@create', 'enquiries.create');
    $router->post('/enquiries', 'Admin\EnquiryController@store', 'enquiries.store');
    $router->get('/enquiries/{id}', 'Admin\EnquiryController@show', 'enquiries.show');
    $router->get('/enquiries/{id}/edit', 'Admin\EnquiryController@edit', 'enquiries.edit');
    $router->post('/enquiries/{id}', 'Admin\EnquiryController@update', 'enquiries.update');
    $router->post('/enquiries/{id}/delete', 'Admin\EnquiryController@destroy', 'enquiries.destroy');
    $router->post('/enquiries/{id}/convert', 'Admin\EnquiryController@convertToLead', 'enquiries.convert');

    // Follow-ups
    $router->get('/followups', 'Admin\FollowupController@index', 'followups.index');
    $router->get('/followups/calendar', 'Admin\FollowupController@calendar', 'followups.calendar');
    $router->get('/followups/create', 'Admin\FollowupController@create', 'followups.create');
    $router->post('/followups', 'Admin\FollowupController@store', 'followups.store');
    $router->get('/followups/{id}/edit', 'Admin\FollowupController@edit', 'followups.edit');
    $router->post('/followups/{id}', 'Admin\FollowupController@update', 'followups.update');
    $router->post('/followups/{id}/complete', 'Admin\FollowupController@complete', 'followups.complete');
    $router->post('/followups/{id}/delete', 'Admin\FollowupController@destroy', 'followups.destroy');
    $router->get('/followups/events', 'Admin\FollowupController@events', 'followups.events');

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
    $router->get('/batches/{id}/edit', 'Admin\BatchController@edit', 'batches.edit');
    $router->post('/batches/{id}', 'Admin\BatchController@update', 'batches.update');
    $router->post('/batches/{id}/delete', 'Admin\BatchController@destroy', 'batches.destroy');

    // Admissions
    $router->get('/admissions', 'Admin\AdmissionController@index', 'admissions.index');
    $router->get('/admissions/create', 'Admin\AdmissionController@create', 'admissions.create');
    $router->post('/admissions', 'Admin\AdmissionController@store', 'admissions.store');
    $router->get('/admissions/{id}', 'Admin\AdmissionController@show', 'admissions.show');
    $router->get('/admissions/{id}/edit', 'Admin\AdmissionController@edit', 'admissions.edit');
    $router->post('/admissions/{id}', 'Admin\AdmissionController@update', 'admissions.update');
    $router->post('/admissions/{id}/approve', 'Admin\AdmissionController@approve', 'admissions.approve');
    $router->post('/admissions/{id}/reject', 'Admin\AdmissionController@reject', 'admissions.reject');
    $router->post('/admissions/{id}/enroll', 'Admin\AdmissionController@enroll', 'admissions.enroll');

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
    $router->get('/students/export', 'Admin\StudentController@export', 'students.export');

    // Fee Structures
    $router->get('/fees', 'Admin\FeeController@index', 'fees.index');
    $router->get('/fees/create', 'Admin\FeeController@create', 'fees.create');
    $router->post('/fees', 'Admin\FeeController@store', 'fees.store');
    $router->get('/fees/{id}', 'Admin\FeeController@show', 'fees.show');
    $router->get('/fees/{id}/edit', 'Admin\FeeController@edit', 'fees.edit');
    $router->post('/fees/{id}', 'Admin\FeeController@update', 'fees.update');
    $router->post('/fees/{id}/delete', 'Admin\FeeController@destroy', 'fees.destroy');

    // Payments
    $router->get('/payments', 'Admin\PaymentController@index', 'payments.index');
    $router->get('/payments/collect/{student_id}', 'Admin\PaymentController@collect', 'payments.collect');
    $router->post('/payments/collect', 'Admin\PaymentController@store', 'payments.store');
    $router->get('/payments/{id}/receipt', 'Admin\PaymentController@receipt', 'payments.receipt');
    $router->get('/payments/due-list', 'Admin\PaymentController@dueList', 'payments.due');

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
});
