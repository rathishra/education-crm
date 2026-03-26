@php
    $statuses = ['active' => 'Active', 'inactive' => 'Inactive', 'alumni' => 'Alumni', 'dropout' => 'Dropout'];
    $genders = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
    $admissionTypes = ['regular' => 'Regular', 'lateral' => 'Lateral'];
    $quotas = ['management' => 'Management', 'government' => 'Government'];
    $studentTypes = ['hosteller' => 'Hosteller', 'day_scholar' => 'Day Scholar'];
@endphp

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Admission Number</label>
        <input type="text" name="admission_number" value="{{ old('admission_number', $student->admission_number ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" value="{{ old('last_name', $student->last_name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">Select</option>
            @foreach($genders as $value => $label)
                <option value="{{ $value }}" @if(old('gender', $student->gender ?? '') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($student)->date_of_birth?->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Mobile Number</label>
        <input type="text" name="mobile_number" value="{{ old('mobile_number', $student->mobile_number ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Address Line 1</label>
        <input type="text" name="address_line1" value="{{ old('address_line1', $student->address_line1 ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">City</label>
        <input type="text" name="city" value="{{ old('city', $student->city ?? '') }}" class="form-control">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">State</label>
        <input type="text" name="state" value="{{ old('state', $student->state ?? '') }}" class="form-control">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" value="{{ old('pincode', $student->pincode ?? '') }}" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Admission Type</label>
        <select name="admission_type" class="form-select">
            @foreach($admissionTypes as $value => $label)
                <option value="{{ $value }}" @if(old('admission_type', $student->admission_type ?? 'regular') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Quota</label>
        <select name="quota" class="form-select">
            @foreach($quotas as $value => $label)
                <option value="{{ $value }}" @if(old('quota', $student->quota ?? 'management') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Student Type</label>
        <select name="student_type" class="form-select">
            @foreach($studentTypes as $value => $label)
                <option value="{{ $value }}" @if(old('student_type', $student->student_type ?? 'day_scholar') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @if(old('status', $student->status ?? 'active') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
