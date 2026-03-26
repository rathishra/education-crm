@extends('layouts.app')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile">Profile</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-academic">Academic</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-parents">Parents</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents">Documents</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timeline">Timeline</button></li>
                </ul>
                <div class="tab-content mt-4">
                    <div class="tab-pane fade show active" id="tab-profile">
                        @include('students.partials.form', ['student' => $student])
                    </div>
                    <div class="tab-pane fade" id="tab-academic">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select">
                                    <option value="">Select course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @if($student->course_id == $course->id) selected @endif>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Batch</label>
                                <select name="batch_id" class="form-select">
                                    <option value="">Select batch</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" @if($student->batch_id == $batch->id) selected @endif>{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester</label>
                                <select name="semester_id" class="form-select">
                                    <option value="">Select semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" @if($student->semester_id == $semester->id) selected @endif>{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Section</label>
                                <select name="section_id" class="form-select">
                                    <option value="">Select section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" @if($student->section_id == $section->id) selected @endif>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Admission Date</label>
                                <input type="date" name="admission_date" value="{{ $student->admission_date?->format('Y-m-d') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-parents">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Father Name</label>
                                <input type="text" name="father_name" value="{{ $student->parents->father_name ?? '' }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" value="{{ $student->parents->mother_name ?? '' }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guardian Name</label>
                                <input type="text" name="guardian_name" value="{{ $student->parents->guardian_name ?? '' }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-documents">
                        <div class="mb-3">
                            <label class="form-label">Upload Additional Documents</label>
                            <input type="file" name="documents[]" class="form-control" multiple>
                        </div>
                        <div class="row">
                            @foreach($student->documents as $document)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ str_replace('_',' ', ucfirst($document->document_type)) }}</strong>
                                            <span class="badge bg-{{ $document->verification_status == 'verified' ? 'success' : 'warning' }}">{{ ucfirst($document->verification_status) }}</span>
                                        </div>
                                        <p class="mb-1 small">{{ $document->original_name }}</p>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('students.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-timeline">
                        <div class="mb-3">
                            <label class="form-label">Add Timeline Note</label>
                            <textarea name="timeline_note" class="form-control" rows="3">{{ old('timeline_note', '') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button class="btn btn-primary px-5">Update Student</button>
                </div>
            </form>
        </div>
    </div>
@endsection
