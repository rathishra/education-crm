@extends('layouts.app')

@section('title', 'Student List')
@section('page-title', 'Student List')

@section('page-actions')
    <a href="{{ route('students.dashboard') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i>Add Student
    </a>
@endsection

@section('content')
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('students.index') }}">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, roll, phone">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Any status</option>
                        <option value="active" @if(request('status')=='active') selected @endif>Active</option>
                        <option value="inactive" @if(request('status')=='inactive') selected @endif>Inactive</option>
                        <option value="alumni" @if(request('status')=='alumni') selected @endif>Alumni</option>
                        <option value="dropout" @if(request('status')=='dropout') selected @endif>Dropout</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select">
                        <option value="">All courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @if(request('course_id')==$course->id) selected @endif>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" class="form-select">
                        <option value="">All batches</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" @if(request('batch_id')==$batch->id) selected @endif>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select">
                        <option value="">Any semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" @if(request('semester_id')==$semester->id) selected @endif>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select">
                        <option value="">Any section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @if(request('section_id')==$section->id) selected @endif>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tag</label>
                    <select name="tag" class="form-select">
                        <option value="">Any tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}" @if(request('tag')==$tag) selected @endif>{{ $tag }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Admission #</th>
                            <th scope="col">Name</th>
                            <th scope="col">Course / Batch</th>
                            <th scope="col">Status</th>
                            <th scope="col">Mobile</th>
                            <th scope="col">Tags</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student->admission_number }}</td>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td>{{ $student->course?->name }} / {{ $student->batch?->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'alumni' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td>{{ $student->mobile_number }}</td>
                                <td>
                                    @foreach($student->tags as $tag)
                                        <span class="badge bg-light text-dark">{{ $tag->tag }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('students.documents.index', $student) ?? '#' }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-file-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $students->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
