<?php
/**
 * Pagination partial
 * Variables: $pagination (array from Database::paginate)
 *            $baseUrl (string) - base URL for page links
 */
if (!isset($pagination) || $pagination['last_page'] <= 1) return;

$currentPage = $pagination['current_page'];
$lastPage = $pagination['last_page'];
$baseUrl = $baseUrl ?? '?';
$separator = strpos($baseUrl, '?') !== false ? '&' : '?';
?>
<nav aria-label="Page navigation">
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing <?= $pagination['from'] ?> to <?= $pagination['to'] ?> of <?= $pagination['total'] ?> entries
        </div>
        <ul class="pagination pagination-sm mb-0">
            <!-- Previous -->
            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $currentPage - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);

            if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= $baseUrl . $separator ?>page=1">1</a></li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif;
            endif;

            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;

            if ($end < $lastPage): ?>
                <?php if ($end < $lastPage - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $lastPage ?>"><?= $lastPage ?></a></li>
            <?php endif; ?>

            <!-- Next -->
            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $baseUrl . $separator ?>page=<?= $currentPage + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
