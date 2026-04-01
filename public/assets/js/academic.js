$(document).ready(function() {

    // Helper: Reset validation states
    function resetValidation(form) {
        form.removeClass('was-validated');
        form.find('.server-invalid').removeClass('is-invalid server-invalid');
        form.find('.server-feedback').remove();
    }

    // 1. Subject Save Logic
    $('#frmAddSubject').on('submit', function(e) {
        e.preventDefault(); 
        let form = $(this);
        resetValidation(form);

        if (form[0].checkValidity() === false) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        let btn = $('#btnSaveSubject');
        let spinner = btn.find('.spinner-border');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert('Subject saved successfully!');
                    window.location.href = '/academic/subjects'; 
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                if (xhr.status === 422 && res.errors) {
                    $.each(res.errors, function(fieldName, errorMessage) {
                        let inputField = $('[name="' + fieldName + '"]');
                        inputField.addClass('is-invalid server-invalid');
                        inputField.after('<div class="invalid-feedback server-feedback d-block">' + errorMessage + '</div>');
                    });
                } else {
                    alert('An unexpected error occurred. Please try again.');
                }
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // 2. Subject Delete Logic
    $('#subjectsTable').on('click', '.btn-delete-subject', function() {
        let subjectId = $(this).data('id');
        let button = $(this);
        let row = button.closest('tr');

        if (confirm("Are you sure you want to delete this subject?")) {
            let originalIcon = button.html();
            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            button.prop('disabled', true);

            $.ajax({
                url: '/academic/subjects/delete/' + subjectId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        row.fadeOut(400, function() { $(this).remove(); });
                    } else {
                        alert('Error: ' + response.message);
                        button.html(originalIcon).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('A server error occurred while trying to delete.');
                    button.html(originalIcon).prop('disabled', false);
                }
            });
        }
    });

    // 3. Classroom Save Logic
    $('#frmClassroom').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        resetValidation(form);
        
        if (form[0].checkValidity() === false) {
            e.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        let btn = $('#btnSaveClassroom');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    alert('Classroom saved!'); 
                    window.location.reload(); 
                }
            },
            error: function(xhr) {
                if(xhr.status === 422 && xhr.responseJSON.errors) {
                    let res = xhr.responseJSON;
                    $.each(res.errors, function(fieldName, errorMessage) {
                        let inputField = form.find('[name="' + fieldName + '"]');
                        inputField.addClass('is-invalid server-invalid');
                        inputField.after('<div class="invalid-feedback server-feedback d-block">' + errorMessage + '</div>');
                    });
                } else {
                    alert('An error occurred.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).text('Save');
            }
        });
    });

});
