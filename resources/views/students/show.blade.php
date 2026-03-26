@extends('layouts.app')

@section('title', 'Student 360 View')
@section('page-title', 'Student 360 View')

@section('page-actions')
    <a href="{{ route('students.edit', $student) }}" class="btn btn-outline-secondary">
        <i class="fas fa-pen"></i> Edit
    </a>
@endsection

@section('content')
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex gap-4 align-items-center">
            <div>
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:80px; height:80px;">
                    <span class="text-white fs-4">{{ strtoupper(substr($student->first_name,0,1)) }}</span>
                </div>
            </div>
            <div>
                <h3 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h3>
                <p class="mb-1 text-muted">Admission #: {{ $student->admission_number }} • Roll #: {{ $student->roll_number ?? '-' }}</p>
                <div>
                    @foreach($student->tags as $tag)
                        <span class="badge bg-info text-dark">{{ $tag->tag }}</span>
                    @endforeach
                    <span class="badge bg-{{ $student->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                @foreach(['profile'=>'Profile','academic'=>'Academic','parents'=>'Parents','documents'=>'Documents','timeline'=>'Timeline'] as $id => $label)
                    <li class="nav-item">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ $id }}" type="button">{{ $label }}</button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-profile">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>Personal Details</strong>
                            <p class="mb-1">Gender: {{ ucfirst($student->gender ?? 'N/A') }}</p>
                            <p class="mb-1">DOB: {{ $student->date_of_birth?->format('d M, Y') ?? '-' }}</p>
                            <p class="mb-1">Blood Group: {{ $student->blood_group ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Contact</strong>
                            <p class="mb-1">Mobile: {{ $student->mobile_number ?? '-' }}</p>
                            <p class="mb-1">Email: {{ $student->email ?? '-' }}</p>
                            <p class="mb-1">Address: {{ $student->address_line1 ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Academic Status</strong>
                            <p class="mb-1">Category: {{ $student->category ?? '-' }}</p>
                            <p class="mb-1">Academic Status: {{ $student->academic_status ?? '-' }}</p>
                            <p class="mb-1">Admission Type: {{ ucfirst($student->admission_type) }}</p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-academic">
                    <div class="row">
                        <div class="col-md-6">
                            <p>Course: {{ $student->course?->name }}</p>
                            <p>Batch: {{ $student->batch?->name }}</p>
                            <p>Semester: {{ $student->semester?->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>Department: {{ $student->department?->name }}</p>
                            <p>Section: {{ $student->section?->name }}</p>
                            <p>Quota: {{ ucfirst($student->quota) }}</p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-parents">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Father</h6>
                            <p class="mb-1">{{ $student->parents->father_name ?? '-' }}</p>
                            <p class="mb-1">{{ $student->parents->father_mobile ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Mother</h6>
                            <p class="mb-1">{{ $student->parents->mother_name ?? '-' }}</p>
                            <p class="mb-1">{{ $student->parents->mother_mobile ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6>Guardian</h6>
                            <p class="mb-1">{{ $student->parents->guardian_name ?? '-' }}</p>
                            <p class="mb-1">{{ $student->parents->guardian_mobile ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-documents">
                    <div class="row">
                        @foreach($student->documents as $document)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ str_replace('_',' ', ucfirst($document->document_type)) }}</strong>
                                        <span class="badge bg-{{ $document->verification_status == 'verified' ? 'success' : 'warning' }}">
                                            {{ ucfirst($document->verification_status) }}
                                        </span>
                                    </div>
                                    <p class="mb-1 small">{{ $document->original_name }}</p>
                                    <p class="mb-1 small text-muted">Uploaded {{ $document->created_at->diffForHumans() }}</p>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('students.documents.download', $document) }}">Download</a>
                                        <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ Storage::url($document->file_path) }}">Preview</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-timeline">
                    @forelse($student->timeline as $event)
                        <div class="timeline-entry">
                            <div class="d-flex justify-content-between">
                                <strong>{{ ucfirst($event->event_type) }}</strong>
                                <small>{{ $event->happened_at->format('d M, Y H:i') }}</small>
                            </div>
                            <p class="mb-1">{{ $event->event_title }}</p>
                            <small class="text-muted">{{ $event->event_details }}</small>
                        </div>
                    @empty
                        <p class="text-muted">No timeline events yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
