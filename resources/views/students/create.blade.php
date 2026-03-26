@extends('layouts.app')

@section('title', 'Add Student')
@section('page-title', 'Add Student')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile">Profile</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-academic">Academic</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parents">Parents</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">Documents</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timeline">Timeline</button></li>
                </ul>
                <div class="tab-content mt-4">
                    <div class="tab-pane fade show active" id="tab-profile">
                        @include('students.partials.form')
                    </div>
                    <div class="tab-pane fade" id="tab-academic">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select">
                                    <option value="">Select course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Batch</label>
                                <select name="batch_id" class="form-select">
                                    <option value="">Select batch</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester</label>
                                <select name="semester_id" class="form-select">
                                    <option value="">Select semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Section</label>
                                <select name="section_id" class="form-select">
                                    <option value="">Select section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Admission Date</label>
                                <input type="date" name="admission_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-parents">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Father Name</label>
                                <input type="text" name="father_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Father Mobile</label>
                                <input type="text" name="father_mobile" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mother Mobile</label>
                                <input type="text" name="mother_mobile" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Guardian Name</label>
                                <input type="text" name="guardian_name" class="form-control">
                                <small class="text-muted">Leave blank if parents are primary contacts.</small>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-documents">
                        <div class="mb-3">
                            <label class="form-label">Upload Documents</label>
                            <input type="file" name="documents[]" class="form-control" multiple>
                            <small class="text-muted">Accepts PDF/JPG/PNG.</small>
                        </div>
                        <div>
                            <h6 class="mb-3">Document types included</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(['marksheets','transfer_certificate','conduct_certificate','id_proof','community_certificate','income_certificate','passport_photo','medical_record'] as $type)
                                    <span class="badge bg-outline-secondary text-dark text-capitalize">{{ str_replace('_',' ',$type) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-timeline">
                        <div class="mb-3">
                            <label class="form-label">Notes / Timeline entry</label>
                            <textarea name="timeline_note" class="form-control" rows="3" placeholder="Record an admission note, special requirements, or tags."></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button class="btn btn-primary px-5">Save Student</button>
                </div>
            </form>
        </div>
    </div>
@endsection
