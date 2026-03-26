<?php $pageTitle = 'Follow-up Calendar'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-calendar-alt me-2"></i>Follow-up Calendar</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item active">Calendar</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= url('followups') ?>" class="btn btn-outline-info me-1">
            <i class="fas fa-list me-1"></i>List View
        </a>
        <?php if (hasPermission('followups.create')): ?>
        <a href="<?= url('followups/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Follow-up
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Calendar Legend -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <small class="text-muted fw-semibold">Legend:</small>
            <span><span class="badge bg-success">&nbsp;</span> <small>Call / WhatsApp</small></span>
            <span><span class="badge bg-info">&nbsp;</span> <small>Email</small></span>
            <span><span class="badge bg-primary">&nbsp;</span> <small>SMS</small></span>
            <span><span class="badge bg-warning">&nbsp;</span> <small>Meeting</small></span>
            <span><span class="badge bg-secondary">&nbsp;</span> <small>Visit</small></span>
            <span><span class="badge bg-dark">&nbsp;</span> <small>Other</small></span>
        </div>
    </div>
</div>

<!-- Calendar -->
<div class="card">
    <div class="card-body">
        <div id="followupCalendar"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('followupCalendar');

    var typeColors = {
        'call':     '#198754',
        'email':    '#0dcaf0',
        'sms':      '#0d6efd',
        'whatsapp': '#198754',
        'meeting':  '#ffc107',
        'visit':    '#6c757d',
        'other':    '#212529'
    };

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        selectable: false,
        nowIndicator: true,
        dayMaxEvents: true,

        events: function(info, successCallback, failureCallback) {
            $.ajax({
                url: '<?= url('followups/events') ?>',
                method: 'GET',
                data: {
                    start: info.startStr,
                    end: info.endStr
                },
                success: function(data) {
                    var events = data.map(function(item) {
                        return {
                            id: item.id,
                            title: item.subject + ' - ' + (item.lead_name || ''),
                            start: item.scheduled_at,
                            backgroundColor: typeColors[item.type] || '#6c757d',
                            borderColor: typeColors[item.type] || '#6c757d',
                            textColor: (item.type === 'meeting') ? '#000' : '#fff',
                            extendedProps: {
                                lead_id: item.lead_id,
                                type: item.type,
                                status: item.status,
                                priority: item.priority,
                                assigned_name: item.assigned_name || ''
                            }
                        };
                    });
                    successCallback(events);
                },
                error: function() {
                    failureCallback();
                    toastr.error('Failed to load calendar events.');
                }
            });
        },

        eventClick: function(info) {
            var props = info.event.extendedProps;
            if (props.lead_id) {
                window.location.href = '<?= url('leads') ?>/' + props.lead_id;
            }
        },

        eventDidMount: function(info) {
            var props = info.event.extendedProps;
            var tooltip = 'Type: ' + (props.type ? props.type.charAt(0).toUpperCase() + props.type.slice(1) : '-');
            tooltip += '\nStatus: ' + (props.status ? props.status.charAt(0).toUpperCase() + props.status.slice(1) : '-');
            tooltip += '\nPriority: ' + (props.priority ? props.priority.charAt(0).toUpperCase() + props.priority.slice(1) : '-');
            if (props.assigned_name) {
                tooltip += '\nAssigned: ' + props.assigned_name;
            }
            info.el.setAttribute('title', tooltip);

            // Add strikethrough for completed
            if (props.status === 'completed') {
                info.el.style.opacity = '0.6';
                info.el.style.textDecoration = 'line-through';
            }
            // Add red border for overdue pending
            if (props.status === 'pending' && new Date(info.event.startStr) < new Date()) {
                info.el.style.borderLeft = '3px solid #dc3545';
            }
        },

        loading: function(isLoading) {
            if (isLoading) {
                calendarEl.style.opacity = '0.5';
            } else {
                calendarEl.style.opacity = '1';
            }
        }
    });

    calendar.render();
});
</script>
