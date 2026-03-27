<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0 text-dark font-weight-bold">Classrooms & Labs</h4>
            <p class="text-muted mb-0">Manage classrooms across your institution</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassroomModal">
                <i class="bi bi-plus-circle"></i> Add Classroom
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0" id="classroomsTable">
                <thead class="bg-light">
                    <tr>
                        <th>Room Number</th>
                        <th>Room Name</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($classrooms)): foreach($classrooms as $room): ?>
                    <tr>
                        <td class="font-weight-bold"><?= htmlspecialchars($room['room_number']) ?></td>
                        <td><?= htmlspecialchars($room['room_name']) ?></td>
                        <td><?= $room['capacity'] ?></td>
                        <td>
                            <?php if($room['is_active']): ?>
                                <span class="badge bg-success rounded-pill">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-light text-primary me-1"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">No classrooms found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Classroom Modal -->
<div class="modal fade" id="addClassroomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="frmClassroom" action="/academic/classrooms/store" method="POST" novalidate>
          <div class="modal-header">
            <h5 class="modal-title">New Classroom</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Room Number *</label>
                  <input type="text" class="form-control" name="room_number" required>
                  <div class="invalid-feedback">Room Number is required.</div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Room Name (Optional)</label>
                  <input type="text" class="form-control" name="room_name" placeholder="e.g. Science Lab">
              </div>
              <div class="mb-3">
                  <label class="form-label">Capacity *</label>
                  <input type="number" class="form-control" name="capacity" value="60" min="1" required>
                  <div class="invalid-feedback">Capacity must be greater than 0.</div>
              </div>
              <div class="form-check form-switch mt-3">
                  <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" checked value="1">
                  <label class="form-check-label" for="isActiveCheck">Active</label>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="btnSaveClassroom">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>
