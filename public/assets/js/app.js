/**
 * Education CRM - Main JavaScript
 */

(function($) {
    'use strict';

    // ============================================================
    // Global AJAX Setup
    // ============================================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // ============================================================
    // Toastr Configuration
    // ============================================================
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000,
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };

    // ============================================================
    // Sidebar Toggle
    // ============================================================
    $('#sidebarToggle').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('sb-sidenav-toggled');
        localStorage.setItem('sb|sidebar-toggle', $('body').hasClass('sb-sidenav-toggled'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        $('body').addClass('sb-sidenav-toggled');
    }

    // ============================================================
    // Initialize Select2
    // ============================================================
    function initSelect2() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            placeholder: function() {
                return $(this).data('placeholder') || 'Select...';
            }
        });
    }
    initSelect2();

    // ============================================================
    // Initialize DataTables
    // ============================================================
    window.initDataTable = function(selector, options) {
        var defaults = {
            responsive: true,
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            language: {
                search: '',
                searchPlaceholder: 'Search...',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        };

        return $(selector).DataTable($.extend(true, defaults, options || {}));
    };

    // Auto-init DataTables
    if ($('.data-table').length) {
        initDataTable('.data-table');
    }

    // ============================================================
    // Delete Confirmation
    // ============================================================
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var name = $(this).data('name') || 'this record';

        if (confirm('Are you sure you want to delete ' + name + '? This action cannot be undone.')) {
            form.submit();
        }
    });

    // ============================================================
    // AJAX Form Submit
    // ============================================================
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('[type="submit"]');
        var btnText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');

        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Operation successful');

                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 500);
                    }

                    if (response.reload) {
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    }

                    // Close modal if in one
                    var modal = form.closest('.modal');
                    if (modal.length) {
                        bootstrap.Modal.getInstance(modal[0])?.hide();
                    }

                    // Trigger custom event
                    form.trigger('ajax:success', [response]);
                } else {
                    toastr.error(response.message || 'Something went wrong');

                    // Display field errors
                    if (response.errors) {
                        $.each(response.errors, function(field, message) {
                            var input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + message + '</div>');
                        });
                    }
                }
            },
            error: function(xhr) {
                var msg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            },
            complete: function() {
                btn.prop('disabled', false).html(btnText);
            }
        });
    });

    // ============================================================
    // Dynamic Dropdown Loading
    // ============================================================
    window.loadDependentDropdown = function(url, targetSelector, placeholder) {
        var target = $(targetSelector);
        target.empty().append('<option value="">' + (placeholder || 'Loading...') + '</option>');

        $.getJSON(url, function(response) {
            target.empty().append('<option value="">' + (placeholder || 'Select...') + '</option>');
            if (response.data) {
                $.each(response.data, function(i, item) {
                    target.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
            }
            target.trigger('change');
        }).fail(function() {
            target.empty().append('<option value="">Failed to load</option>');
        });
    };

    // Load departments when institution changes
    $(document).on('change', '.institution-select', function() {
        var instId = $(this).val();
        if (instId) {
            loadDependentDropdown(
                APP_URL + '/api/lookup/departments/' + instId,
                '.department-select',
                'Select Department'
            );
            loadDependentDropdown(
                APP_URL + '/api/lookup/courses/' + instId,
                '.course-select',
                'Select Course'
            );
        }
    });

    // Load batches when course changes
    $(document).on('change', '.course-select', function() {
        var courseId = $(this).val();
        if (courseId) {
            loadDependentDropdown(
                APP_URL + '/api/lookup/batches/' + courseId,
                '.batch-select',
                'Select Batch'
            );
        }
    });

    // ============================================================
    // Notification Polling
    // ============================================================
    function loadNotifications() {
        $.getJSON(APP_URL + '/notifications/unread-count', function(response) {
            var count = response.count || 0;
            var badge = $('#notifCount');
            if (count > 0) {
                badge.show().find('.count').text(count > 99 ? '99+' : count);
            } else {
                badge.hide();
            }
        });
    }

    // Poll every 60 seconds
    if (typeof APP_URL !== 'undefined') {
        loadNotifications();
        setInterval(loadNotifications, 60000);
    }

    // ============================================================
    // Utility Functions
    // ============================================================
    window.CRM = {
        // Format currency
        formatCurrency: function(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        // Format date
        formatDate: function(dateStr) {
            if (!dateStr) return '-';
            var d = new Date(dateStr);
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        // Confirm action
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        // Show loading
        showLoading: function() {
            $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },

        // Hide loading
        hideLoading: function() {
            $('.loading-overlay').remove();
        },

        // AJAX helper
        ajax: function(url, method, data, callback) {
            $.ajax({
                url: url,
                method: method,
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (callback) callback(response);
                },
                error: function(xhr) {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        }
    };

    // ============================================================
    // Auto-hide alerts after 5 seconds
    // ============================================================
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

    // ============================================================
    // Tooltip initialization
    // ============================================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });

})(jQuery);

// Global APP_URL variable
var APP_URL = document.querySelector('meta[name="csrf-token"]')?.closest('html')?.querySelector('base')?.href || window.location.origin;
