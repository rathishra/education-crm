<?php
namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\Admission;

class ApplyController extends BaseController
{
    private Admission $admission;

    public function __construct()
    {
        parent::__construct();
        $this->admission = new Admission();
    }

    /**
     * Show public admission form
     */
    public function index(): void
    {
        // Fetch options
        $this->db->query("SELECT id, name, code FROM institutions WHERE status = 'active' AND deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();

        $this->db->query("SELECT c.id, c.name, c.code, c.institution_id, i.name AS institution_name
                          FROM courses c 
                          JOIN institutions i ON i.id = c.institution_id
                          WHERE c.status = 'active' AND c.deleted_at IS NULL
                          ORDER BY i.name, c.name");
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT ay.id, ay.name, ay.institution_id 
                          FROM academic_years ay 
                          JOIN institutions i ON i.id = ay.institution_id
                          WHERE ay.deleted_at IS NULL
                          ORDER BY ay.start_date DESC");
        $academicYears = $this->db->fetchAll();

        $pageTitle = 'Apply for Admission';
        $this->view('front.apply', compact('institutions', 'courses', 'academicYears', 'pageTitle'), 'auth');
    }

    /**
     * Handle public admission submission
     */
    public function submit(): void
    {
        $data = $this->postData();

        $errors = $this->validate($data, [
            'first_name'      => 'required',
            'phone'           => 'required|phone',
            'institution_id'  => 'required|numeric',
            'course_id'       => 'required|numeric',
            'academic_year_id'=> 'numeric',
            'email'           => 'email',
        ]);

        if ($errors) {
            $this->backWithErrors($errors, $data);
            return;
        }

        // Ensure institution and course are valid and aligned
        $this->db->query("SELECT id, code FROM institutions WHERE id = ? AND status = 'active' AND deleted_at IS NULL", [$data['institution_id']]);
        $inst = $this->db->fetch();
        if (!$inst) {
            $this->backWithErrors(['Invalid institution selected.'], $data);
            return;
        }

        $this->db->query("SELECT id FROM courses WHERE id = ? AND institution_id = ? AND status = 'active' AND deleted_at IS NULL", [$data['course_id'], $data['institution_id']]);
        if (!$this->db->fetch()) {
            $this->backWithErrors(['Course is not available for the selected institution.'], $data);
            return;
        }

        // Academic year is optional but should belong to institution if provided
        if (!empty($data['academic_year_id'])) {
            $this->db->query("SELECT id FROM academic_years WHERE id = ? AND institution_id = ?", [$data['academic_year_id'], $data['institution_id']]);
            if (!$this->db->fetch()) {
                $this->backWithErrors(['Academic year does not belong to the selected institution.'], $data);
                return;
            }
        }

        $insertData = [
            'institution_id'         => (int)$data['institution_id'],
            'admission_number'       => $this->admission->generateAdmissionNumber((int)$data['institution_id']),
            'first_name'             => sanitize($data['first_name']),
            'last_name'              => sanitize($data['last_name'] ?? ''),
            'email'                  => sanitize($data['email'] ?? ''),
            'phone'                  => sanitize($data['phone']),
            'date_of_birth'          => $data['date_of_birth'] ?: null,
            'gender'                 => $data['gender'] ?? null,
            'address_line1'          => sanitize($data['address_line1'] ?? ''),
            'city'                   => sanitize($data['city'] ?? ''),
            'state'                  => sanitize($data['state'] ?? ''),
            'pincode'                => sanitize($data['pincode'] ?? ''),
            'nationality'            => sanitize($data['nationality'] ?? 'Indian'),
            'category'               => $data['category'] ?? null,
            'previous_qualification' => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'    => $data['previous_percentage'] ?: null,
            'previous_institution'   => sanitize($data['previous_institution'] ?? ''),
            'previous_year_of_passing'=> $data['previous_year_of_passing'] ?: null,
            'course_id'              => (int)$data['course_id'],
            'batch_id'               => null,
            'academic_year_id'       => $data['academic_year_id'] ?: null,
            'admission_type'         => $data['admission_type'] ?? 'regular',
            'application_date'       => date('Y-m-d'),
            'status'                 => 'applied',
            'lead_id'                => null,
            'remarks'                => sanitize($data['remarks'] ?? ''),
            'created_by'             => null,
        ];

        $this->admission->create($insertData);

        $this->redirectWith('apply/thank-you', 'success', 'Application submitted. Our admissions team will contact you soon.');
    }

    public function thankYou(): void
    {
        $pageTitle = 'Application Submitted';
        $this->view('front.thankyou', compact('pageTitle'), 'auth');
    }
}
