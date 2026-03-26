<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Organizations
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_code')->unique();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->string('subscription_plan')->nullable();
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->integer('max_institutions')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Institutions
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('institution_name');
            $table->string('institution_code')->unique();
            $table->string('institution_type')->nullable(); // Engineering, Medical, Arts, etc.
            $table->string('affiliation')->nullable();
            $table->string('university')->nullable();
            $table->year('establishment_year')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_contact')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Campuses
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('campus_name');
            $table->string('campus_code')->unique();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('department_name');
            $table->string('department_code')->unique();
            $table->string('hod_name')->nullable();
            $table->string('hod_contact')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Courses
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('course_name');
            $table->string('course_code')->unique();
            $table->string('course_type')->nullable(); // UG, PG, Diploma
            $table->string('degree_type')->nullable();
            $table->integer('duration_years')->default(1);
            $table->integer('total_semesters')->default(2);
            $table->integer('credits')->default(0);
            $table->integer('intake_capacity')->default(60);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Batches
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('batch_name'); // e.g., 2024-2028
            $table->string('academic_year')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('intake_capacity')->default(60);
            $table->integer('current_strength')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Sections
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('section_name'); // e.g., A, B, C
            $table->integer('capacity')->default(60);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Audit Logs (Activity History)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('module');
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('campuses');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('organizations');
    }
};
