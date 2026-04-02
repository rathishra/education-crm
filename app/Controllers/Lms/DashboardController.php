<?php
namespace App\Controllers\Lms;

class DashboardController extends LmsBaseController
{
    public function index(): void
    {
        $pageTitle = 'LMS Dashboard';
        $this->view('lms/dashboard/index', compact('pageTitle'));
    }
}
