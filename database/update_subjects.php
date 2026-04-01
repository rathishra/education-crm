<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $mysqli = new mysqli("127.0.0.1", "root", "", "education_crm");
    
    // We add all the new columns required by the UI to the subjects table
    $alterQueries = [
        "ALTER TABLE subjects ADD COLUMN subject_description VARCHAR(500) NULL AFTER subject_name;",
        "ALTER TABLE subjects ADD COLUMN subject_display_code VARCHAR(100) NULL AFTER subject_description;",
        "ALTER TABLE subjects ADD COLUMN subject_classification VARCHAR(100) NULL AFTER subject_type;",
        "ALTER TABLE subjects ADD COLUMN subject_category VARCHAR(100) NULL AFTER subject_classification;",
        "ALTER TABLE subjects ADD COLUMN mark_definition VARCHAR(100) NULL AFTER subject_category;",
        "ALTER TABLE subjects ADD COLUMN integrated_subject_type VARCHAR(100) NULL AFTER mark_definition;",
        "ALTER TABLE subjects ADD COLUMN is_embedded TINYINT(1) DEFAULT 0 AFTER integrated_subject_type;",
        "ALTER TABLE subjects ADD COLUMN effective_from DATE NULL AFTER is_embedded;",
        "ALTER TABLE subjects ADD COLUMN effective_through DATE NULL AFTER effective_from;",
        "ALTER TABLE subjects ADD COLUMN has_syllabus TINYINT(1) DEFAULT 0 AFTER effective_through;",
        "ALTER TABLE subjects ADD COLUMN has_lesson_plan TINYINT(1) DEFAULT 0 AFTER has_syllabus;",
        "ALTER TABLE subjects ADD COLUMN has_topic_coverage TINYINT(1) DEFAULT 0 AFTER has_lesson_plan;",
        "ALTER TABLE subjects ADD COLUMN lesson_plan_approval_department VARCHAR(150) NULL AFTER has_topic_coverage;",
        "ALTER TABLE subjects ADD COLUMN subject_part VARCHAR(100) NULL AFTER lesson_plan_approval_department;",
        "ALTER TABLE subjects ADD COLUMN is_regional_language TINYINT(1) DEFAULT 0 AFTER subject_part;",
        "ALTER TABLE subjects ADD COLUMN is_dependant_language TINYINT(1) DEFAULT 0 AFTER is_regional_language;",
        "ALTER TABLE subjects ADD COLUMN abbreviation VARCHAR(50) NULL AFTER is_dependant_language;",
        "ALTER TABLE subjects ADD COLUMN board VARCHAR(100) NULL AFTER abbreviation;",
        "ALTER TABLE subjects ADD COLUMN include_cgpa TINYINT(1) DEFAULT 0 AFTER board;",
        "ALTER TABLE subjects ADD COLUMN coe_code VARCHAR(100) NULL AFTER include_cgpa;"
    ];
    
    foreach ($alterQueries as $q) {
        try {
            $mysqli->query($q);
            echo "Success: $q\n";
        } catch (Exception $e) {
            echo "Skipped/Error (already exists?): " . $e->getMessage() . "\n";
        }
    }
    echo "Done.\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
