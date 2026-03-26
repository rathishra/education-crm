<?php
/**
 * API Routes
 * All API routes are prefixed with /api
 */

use Core\App;

$router = App::getInstance()->router();

$router->group(['prefix' => 'api', 'middleware' => 'api'], function ($router) {

    // Dashboard Stats
    $router->get('/dashboard/stats', 'Api\DashboardApiController@stats');

    // Leads API
    $router->get('/leads', 'Api\LeadApiController@index');
    $router->post('/leads', 'Api\LeadApiController@store');
    $router->get('/leads/{id}', 'Api\LeadApiController@show');
    $router->post('/leads/{id}', 'Api\LeadApiController@update');
    $router->post('/leads/{id}/delete', 'Api\LeadApiController@destroy');
    $router->post('/leads/{id}/status', 'Api\LeadApiController@updateStatus');
    $router->post('/leads/{id}/assign', 'Api\LeadApiController@assign');
    $router->get('/leads/statuses', 'Api\LeadApiController@statuses');
    $router->get('/leads/sources', 'Api\LeadApiController@sources');

    // Students API
    $router->get('/students', 'Api\StudentApiController@index');
    $router->get('/students/{id}', 'Api\StudentApiController@show');

    // Courses API
    $router->get('/courses', 'Api\CourseApiController@index');
    $router->get('/courses/{id}/batches', 'Api\CourseApiController@batches');

    // Enquiries API
    $router->get('/enquiries', 'Api\EnquiryApiController@index');
    $router->post('/enquiries', 'Api\EnquiryApiController@store');

    // Fees API
    $router->get('/fees/student/{student_id}', 'Api\FeeApiController@studentFees');
    $router->get('/fees/due-list', 'Api\FeeApiController@dueList');

    // Reports API
    $router->get('/reports/lead-stats', 'Api\ReportApiController@leadStats');
    $router->get('/reports/revenue', 'Api\ReportApiController@revenue');

    // Lookup endpoints (for AJAX dropdowns)
    $router->get('/lookup/institutions', 'Api\LookupApiController@institutions');
    $router->get('/lookup/departments/{institution_id}', 'Api\LookupApiController@departments');
    $router->get('/lookup/courses/{institution_id}', 'Api\LookupApiController@courses');
    $router->get('/lookup/batches/{course_id}', 'Api\LookupApiController@batches');
    $router->get('/lookup/counselors/{institution_id}', 'Api\LookupApiController@counselors');
    $router->get('/lookup/lead-sources', 'Api\LookupApiController@leadSources');
    $router->get('/lookup/lead-statuses', 'Api\LookupApiController@leadStatuses');
});

// Public API - Enquiry submission (no auth needed)
$router->post('/api/public/enquiry', 'Api\EnquiryApiController@publicStore');
