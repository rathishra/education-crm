<?php $pageTitle = 'Receipt #' . ($receipt['receipt_number'] ?? ''); ?>

<div class="print-page" style="padding: 30px 40px;">

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between border-bottom pb-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size:1.4rem;color:#1a56db">
                <i class="fas fa-graduation-cap me-2"></i><?= e(config('app.name', 'Edu Matrix')) ?>
            </h2>
            <div class="small text-muted"><?= e($receipt['institution_name'] ?? '') ?></div>
            <?php if (!empty($receipt['institution_address'])): ?>
            <div class="small text-muted"><?= e($receipt['institution_address']) ?></div>
            <?php endif; ?>
        </div>
        <div class="text-end">
            <div class="fw-bold fs-5 text-uppercase" style="letter-spacing:1px;color:#1a56db">Fee Receipt</div>
            <div class="mt-1">
                <span class="badge bg-primary fs-6 px-3 py-2"><?= e($receipt['receipt_number']) ?></span>
            </div>
            <?php if ($receipt['status'] === 'cancelled'): ?>
            <div class="mt-1"><span class="badge bg-danger fs-6">CANCELLED</span></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Receipt Meta -->
    <div class="row mb-3">
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0" style="font-size:.85rem">
                <tr>
                    <td class="text-muted fw-semibold ps-0" style="width:130px">Receipt Date</td>
                    <td><?= date('d M Y', strtotime($receipt['receipt_date'])) ?></td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold ps-0">Payment Mode</td>
                    <td>
                        <?php
                        $modeIcons = ['cash'=>'money-bill-wave','upi'=>'mobile-alt','card'=>'credit-card','netbanking'=>'university','cheque'=>'money-check','dd'=>'file-alt','online'=>'globe'];
                        $mode = $receipt['payment_mode'];
                        $icon = $modeIcons[$mode] ?? 'wallet';
                        ?>
                        <i class="fas fa-<?= $icon ?> me-1 text-primary"></i><?= strtoupper($mode) ?>
                        <?php if (!empty($receipt['reference_number'])): ?>
                        <span class="text-muted"> / Ref: <?= e($receipt['reference_number']) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($receipt['cheque_number'])): ?>
                <tr>
                    <td class="text-muted fw-semibold ps-0">Cheque No.</td>
                    <td><?= e($receipt['cheque_number']) ?> (<?= e($receipt['bank_name'] ?? '') ?>)</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted fw-semibold ps-0">Collected By</td>
                    <td><?= e($receipt['collector_name'] ?? 'Staff') ?></td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <div class="bg-light rounded p-3" style="font-size:.85rem">
                <div class="fw-bold mb-2" style="font-size:.9rem">Student Details</div>
                <div class="fw-semibold fs-6"><?= e($receipt['student_name'] ?? '') ?></div>
                <?php if (!empty($receipt['student_roll'])): ?>
                <div class="text-muted">Roll: <?= e($receipt['student_roll']) ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['enrollment_no'])): ?>
                <div class="text-muted">Enroll: <?= e($receipt['enrollment_no']) ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['course_name'])): ?>
                <div class="text-primary small"><?= e($receipt['course_name']) ?><?= !empty($receipt['batch_name']) ? ' — '.e($receipt['batch_name']) : '' ?></div>
                <?php endif; ?>
                <?php if (!empty($receipt['academic_year'])): ?>
                <div class="text-muted small">AY: <?= e($receipt['academic_year']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Fee Items Table -->
    <table class="table table-bordered" style="font-size:.85rem">
        <thead style="background:#1a56db;color:#fff">
            <tr>
                <th class="py-2">#</th>
                <th class="py-2">Fee Head</th>
                <th class="py-2">Category</th>
                <th class="py-2 text-end">Gross Amount</th>
                <th class="py-2 text-end">Concession</th>
                <th class="py-2 text-end">Fine</th>
                <th class="py-2 text-end">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $totalGross = 0; $totalConcession = 0; $totalFine = 0; $totalPaid = 0;
        foreach ($receipt['items'] as $i => $item):
            $gross = $item['gross_amount'] ?? $item['amount'];
            $con   = $item['concession_amount'] ?? 0;
            $fine  = $item['fine_amount'] ?? 0;
            $paid  = $item['amount'];
            $totalGross += $gross; $totalConcession += $con;
            $totalFine += $fine; $totalPaid += $paid;
        ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td class="fw-semibold"><?= e($item['head_name'] ?? '') ?></td>
            <td><span class="badge bg-secondary-subtle text-secondary border" style="font-size:.7rem"><?= ucfirst($item['category'] ?? '') ?></span></td>
            <td class="text-end">₹<?= number_format($gross,2) ?></td>
            <td class="text-end <?= $con>0?'text-success':'' ?>">
                <?= $con>0 ? '(₹'.number_format($con,2).')' : '—' ?>
            </td>
            <td class="text-end <?= $fine>0?'text-danger':'' ?>">
                <?= $fine>0 ? '+₹'.number_format($fine,2) : '—' ?>
            </td>
            <td class="text-end fw-semibold">₹<?= number_format($paid,2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background:#f8f9fa">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end">₹<?= number_format($totalGross,2) ?></td>
                <td class="text-end text-success"><?= $totalConcession>0?'(₹'.number_format($totalConcession,2).')':'—' ?></td>
                <td class="text-end text-danger"><?= $totalFine>0?'+₹'.number_format($totalFine,2):'—' ?></td>
                <td class="text-end fs-6" style="color:#1a56db">₹<?= number_format($totalPaid,2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Amount in Words + Balance -->
    <div class="row mt-2">
        <div class="col-7">
            <div class="border rounded p-2 bg-light" style="font-size:.82rem">
                <span class="text-muted fw-semibold">Amount in words:</span><br>
                <span class="fw-semibold"><?= e(numberToWords($totalPaid)) ?> Only</span>
            </div>
            <?php if (!empty($receipt['remarks'])): ?>
            <div class="mt-2 text-muted small"><strong>Remarks:</strong> <?= e($receipt['remarks']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <table class="table table-sm table-borderless mb-0 text-end" style="font-size:.85rem">
                <tr><td class="text-muted">Total Paid</td><td class="fw-bold text-success fs-6">₹<?= number_format($totalPaid,2) ?></td></tr>
                <?php if (!empty($receipt['balance_after'])): ?>
                <tr><td class="text-muted">Balance Due</td><td class="fw-semibold <?= $receipt['balance_after']>0?'text-danger':'text-success' ?>">
                    ₹<?= number_format($receipt['balance_after'],2) ?>
                </td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="d-flex align-items-end justify-content-between border-top mt-4 pt-3">
        <div class="small text-muted">
            <div>Generated: <?= date('d M Y h:i A') ?></div>
            <div>This is a computer generated receipt.</div>
            <?php if ($receipt['status'] === 'cancelled'): ?>
            <div class="text-danger fw-bold mt-1">*** CANCELLED RECEIPT — NOT VALID ***</div>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <div style="border-top:1px solid #333;width:140px;padding-top:4px;font-size:.8rem">Authorized Signatory</div>
        </div>
    </div>

    <!-- Dashed separator for thermal duplicate -->
    <div class="no-print mt-4 pt-2 border-top border-dashed text-center text-muted small">Student Copy</div>

</div>

<?php
function numberToWords(float $num): string {
    $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
             'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
             'Seventeen','Eighteen','Nineteen'];
    $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    $intPart = (int) floor($num);
    $decPart = round(($num - $intPart) * 100);
    function convertHundreds(int $n, array $ones, array $tens): string {
        $res = '';
        if ($n >= 100) { $res .= $ones[(int)($n/100)] . ' Hundred '; $n %= 100; }
        if ($n >= 20)  { $res .= $tens[(int)($n/10)] . ' '; $n %= 10; }
        if ($n > 0)    { $res .= $ones[$n] . ' '; }
        return $res;
    }
    if ($intPart === 0) return 'Zero';
    $words = '';
    if ($intPart >= 10000000) { $words .= convertHundreds((int)($intPart/10000000), $ones, $tens).'Crore '; $intPart %= 10000000; }
    if ($intPart >= 100000)   { $words .= convertHundreds((int)($intPart/100000), $ones, $tens).'Lakh '; $intPart %= 100000; }
    if ($intPart >= 1000)     { $words .= convertHundreds((int)($intPart/1000), $ones, $tens).'Thousand '; $intPart %= 1000; }
    $words .= convertHundreds($intPart, $ones, $tens);
    if ($decPart > 0) $words .= 'and '.convertHundreds($decPart, $ones, $tens).'Paise ';
    return trim($words);
}
?>
