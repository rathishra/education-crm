<?php
namespace App\Services;

/**
 * TimetableEngine
 *
 * Constraint-based timetable generation engine.
 * Algorithm: Greedy assignment with backtracking + soft-constraint scoring.
 *
 * Steps:
 *  1. Build a flat list of "slots to fill" sorted by most-constrained first.
 *  2. For each requirement (section × subject × N periods/week):
 *       — Find candidate (day, period) slots that satisfy all hard constraints.
 *       — Score each candidate against soft constraints.
 *       — Assign the highest-scoring valid slot.
 *       — Mark faculty, room, and section as busy for that slot.
 *  3. Return assignments + unresolved conflicts.
 */
class TimetableEngine
{
    // ── Inputs ───────────────────────────────────────────────────────────────

    /** @var array  [section_id => ['id','name','batch_id',...]] */
    private array $sections = [];

    /** @var array  [['section_id','subject_id','faculty_id','periods_per_week','entry_type','priority','preferred_room_id',...]] */
    private array $requirements = [];

    /** @var array  [period_id => ['id','period_number','period_name','start_time','end_time','is_break']] */
    private array $periods = [];

    /** @var string[]  working days e.g. ['monday','tuesday',...] */
    private array $workingDays = [];

    /** @var array  [room_id => ['id','room_number','capacity','room_type']] */
    private array $rooms = [];

    /** @var array  [faculty_id][day][period_id] = true  (pre-blocked unavailability) */
    private array $facultyBlocked = [];

    /** @var array  Generator config options */
    private array $config = [];

    // ── State during generation ──────────────────────────────────────────────

    /** [section_id][day][period_id] => assignment array | null */
    private array $sectionGrid = [];

    /** [faculty_id][day][period_id] => true */
    private array $facultyBusy = [];

    /** [room_id][day][period_id] => true */
    private array $roomBusy = [];

    /** [section_id][subject_id] => list of (day, period_id) assigned */
    private array $subjectAssigned = [];

    /** @var array  Unresolved requirements */
    private array $conflicts = [];

    /** @var string[]  Log messages */
    private array $log = [];

    // ── Public API ───────────────────────────────────────────────────────────

    public function setData(array $data): self
    {
        $this->sections      = $data['sections']      ?? [];
        $this->requirements  = $data['requirements']  ?? [];
        $this->periods       = $data['periods']        ?? [];
        $this->workingDays   = $data['working_days']   ?? ['monday','tuesday','wednesday','thursday','friday'];
        $this->rooms         = $data['rooms']          ?? [];
        $this->facultyBlocked = $data['faculty_blocked'] ?? [];
        $this->config        = array_merge($this->defaultConfig(), $data['config'] ?? []);
        return $this;
    }

    public function generate(): array
    {
        $startMs = (int)(microtime(true) * 1000);
        $this->log = [];
        $this->conflicts = [];
        $this->initGrids();

        // Sort requirements: most constrained first (fewest available slots)
        $sorted = $this->sortByConstraintLevel($this->requirements);

        $this->logMsg('Starting generation: ' . count($sorted) . ' requirements across '
            . count($this->sections) . ' sections, '
            . count($this->workingDays) . ' days, '
            . count($this->periods) . ' periods.');

        $totalSlots  = 0;
        $totalFilled = 0;

        foreach ($sorted as $req) {
            $needed = (int)($req['periods_per_week'] ?? 1);
            $totalSlots += $needed;

            for ($occurrence = 0; $occurrence < $needed; $occurrence++) {
                if ($this->assignOneSlot($req, $occurrence, $needed)) {
                    $totalFilled++;
                } else {
                    $this->conflicts[] = array_merge($req, ['occurrence' => $occurrence + 1]);
                    $this->logMsg("CONFLICT: section={$req['section_id']} subject={$req['subject_id']}"
                        . " occurrence=" . ($occurrence + 1));
                }
            }
        }

        $score = $totalSlots > 0
            ? round(($totalFilled / $totalSlots) * 100, 2)
            : 0.0;

        $durationMs = (int)(microtime(true) * 1000) - $startMs;

        $this->logMsg("Done. Assigned {$totalFilled}/{$totalSlots}. Score={$score}. Time={$durationMs}ms.");

        return [
            'assignments'      => $this->flattenAssignments(),
            'conflicts'        => $this->conflicts,
            'total_requirements' => $totalSlots,
            'assigned_count'   => $totalFilled,
            'conflict_count'   => count($this->conflicts),
            'score'            => $score,
            'duration_ms'      => $durationMs,
            'log'              => implode("\n", $this->log),
        ];
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    private function defaultConfig(): array
    {
        return [
            'max_consecutive_same'  => 2,
            'avoid_first_last_same' => true,
            'distribute_evenly'     => true,
            'lab_block_size'        => 2,
            'balance_faculty_load'  => true,
        ];
    }

    private function initGrids(): void
    {
        $this->sectionGrid    = [];
        $this->facultyBusy    = [];
        $this->roomBusy       = [];
        $this->subjectAssigned = [];

        // Pre-load faculty unavailability into busy grid
        foreach ($this->facultyBlocked as $facultyId => $days) {
            foreach ($days as $day => $periodIds) {
                foreach ($periodIds as $periodId) {
                    $this->facultyBusy[$facultyId][$day][$periodId] = true;
                }
            }
        }
    }

    /**
     * Sort requirements by most-constrained first:
     *  - Fewer available faculty slots → harder to place → first
     *  - Higher priority number → lower priority → last
     *  - Labs (need consecutive pairs) → first
     */
    private function sortByConstraintLevel(array $reqs): array
    {
        usort($reqs, function ($a, $b) {
            // Labs first (need 2 consecutive slots)
            $aIsLab = ($a['entry_type'] ?? 'lecture') === 'lab' ? 0 : 1;
            $bIsLab = ($b['entry_type'] ?? 'lecture') === 'lab' ? 0 : 1;
            if ($aIsLab !== $bIsLab) return $aIsLab - $bIsLab;

            // Higher periods_per_week → harder
            $diff = ($b['periods_per_week'] ?? 1) - ($a['periods_per_week'] ?? 1);
            if ($diff !== 0) return $diff;

            // Lower priority value = higher importance
            return ($a['priority'] ?? 5) - ($b['priority'] ?? 5);
        });
        return $reqs;
    }

    private function assignOneSlot(array $req, int $occurrence, int $totalOccurrences): bool
    {
        $sectionId  = (int)$req['section_id'];
        $subjectId  = (int)$req['subject_id'];
        $facultyId  = (int)($req['faculty_id'] ?? 0);
        $entryType  = $req['entry_type'] ?? 'lecture';
        $isLab      = $entryType === 'lab';
        $labBlock   = (int)($this->config['lab_block_size'] ?? 2);

        $candidates = $this->buildCandidates($sectionId, $facultyId, $isLab, $labBlock);

        if (empty($candidates)) return false;

        // Score each candidate
        $scored = [];
        foreach ($candidates as $cand) {
            $score = $this->scoreCandidateSoft($cand, $req, $occurrence, $totalOccurrences);
            $scored[] = array_merge($cand, ['score' => $score]);
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        $best = $scored[0];
        $this->markAssigned($best, $req, $isLab, $labBlock);
        return true;
    }

    /**
     * Build list of (day, period_id[, period_id2]) candidates satisfying hard constraints.
     */
    private function buildCandidates(int $sectionId, int $facultyId, bool $isLab, int $labBlock): array
    {
        $candidates = [];

        foreach ($this->workingDays as $day) {
            $periodList = array_values(array_filter(
                $this->periods,
                fn($p) => !($p['is_break'] ?? false)
            ));

            if ($isLab) {
                // Need labBlock consecutive non-break periods
                for ($i = 0; $i <= count($periodList) - $labBlock; $i++) {
                    $block = array_slice($periodList, $i, $labBlock);
                    // Check all slots in block are free
                    $valid = true;
                    foreach ($block as $p) {
                        if (!$this->isSlotFree($sectionId, $facultyId, $day, $p['id'])) {
                            $valid = false;
                            break;
                        }
                    }
                    if ($valid) {
                        $candidates[] = [
                            'day'        => $day,
                            'period_id'  => $block[0]['id'],
                            'period_ids' => array_column($block, 'id'),
                            'is_lab'     => true,
                        ];
                    }
                }
            } else {
                foreach ($periodList as $period) {
                    if ($this->isSlotFree($sectionId, $facultyId, $day, $period['id'])) {
                        $candidates[] = [
                            'day'        => $day,
                            'period_id'  => $period['id'],
                            'period_ids' => [$period['id']],
                            'is_lab'     => false,
                        ];
                    }
                }
            }
        }

        return $candidates;
    }

    private function isSlotFree(int $sectionId, int $facultyId, string $day, int $periodId): bool
    {
        // Section grid — slot already taken?
        if (isset($this->sectionGrid[$sectionId][$day][$periodId])) return false;

        // Faculty — already teaching somewhere else?
        if ($facultyId && isset($this->facultyBusy[$facultyId][$day][$periodId])) return false;

        return true;
    }

    /**
     * Score a candidate slot against soft constraints.
     * Higher = better. Starts at 100, deductions applied.
     */
    private function scoreCandidateSoft(array $cand, array $req, int $occurrence, int $total): float
    {
        $score = 100.0;
        $sectionId = (int)$req['section_id'];
        $subjectId = (int)$req['subject_id'];
        $day       = $cand['day'];
        $periodId  = $cand['period_id'];

        $already = $this->subjectAssigned[$sectionId][$subjectId] ?? [];

        // ── Soft: distribute evenly across days ──────────────────
        if ($this->config['distribute_evenly'] ?? true) {
            $daysUsed = array_unique(array_column($already, 'day'));
            if (in_array($day, $daysUsed, true)) {
                $score -= 20; // prefer a day not yet used for this subject
            }
        }

        // ── Soft: avoid more than N consecutive same subject ─────
        $maxConsec = (int)($this->config['max_consecutive_same'] ?? 2);
        $consecOnDay = count(array_filter($already, fn($a) => $a['day'] === $day));
        if ($consecOnDay >= $maxConsec) {
            $score -= 35;
        }

        // ── Soft: avoid first + last period for same subject ─────
        if ($this->config['avoid_first_last_same'] ?? true) {
            $nonBreaks   = array_values(array_filter($this->periods, fn($p) => !($p['is_break'] ?? false)));
            $firstPeriod = $nonBreaks[0]['id']   ?? null;
            $lastPeriod  = end($nonBreaks)['id'] ?? null;

            if ($periodId === $firstPeriod || $periodId === $lastPeriod) {
                $alreadyOnSameDay = array_filter($already, fn($a) => $a['day'] === $day);
                if (!empty($alreadyOnSameDay)) $score -= 15;
            }
        }

        // ── Soft: balance faculty load (prefer days with fewer assignments) ──
        if ($this->config['balance_faculty_load'] ?? true) {
            $facultyId = (int)($req['faculty_id'] ?? 0);
            if ($facultyId) {
                $dayLoad = count($this->facultyBusy[$facultyId][$day] ?? []);
                $score -= $dayLoad * 3; // slight penalty per existing period on that day
            }
        }

        // ── Soft: prefer earlier periods for lectures ─────────────
        $periodObj = $this->periods[$periodId] ?? null;
        if ($periodObj) {
            $pos = (int)($periodObj['period_number'] ?? 5);
            $score += max(0, 5 - $pos); // bonus for earlier periods
        }

        return max(0, $score);
    }

    private function markAssigned(array $slot, array $req, bool $isLab, int $labBlock): void
    {
        $sectionId = (int)$req['section_id'];
        $subjectId = (int)$req['subject_id'];
        $facultyId = (int)($req['faculty_id'] ?? 0);
        $day       = $slot['day'];
        $periodIds = $slot['period_ids'];

        foreach ($periodIds as $periodId) {
            $assignment = [
                'section_id'  => $sectionId,
                'subject_id'  => $subjectId,
                'faculty_id'  => $facultyId,
                'day'         => $day,
                'period_id'   => $periodId,
                'entry_type'  => $req['entry_type'] ?? 'lecture',
                'room_id'     => $req['preferred_room_id'] ?? null,
                'batch_id'    => $req['batch_id'] ?? null,
            ];

            $this->sectionGrid[$sectionId][$day][$periodId] = $assignment;

            if ($facultyId) {
                $this->facultyBusy[$facultyId][$day][$periodId] = true;
            }

            $this->subjectAssigned[$sectionId][$subjectId][] = [
                'day'       => $day,
                'period_id' => $periodId,
            ];
        }
    }

    private function flattenAssignments(): array
    {
        $flat = [];
        foreach ($this->sectionGrid as $sectionId => $days) {
            foreach ($days as $day => $periods) {
                foreach ($periods as $periodId => $assignment) {
                    $flat[] = $assignment;
                }
            }
        }
        return $flat;
    }

    private function logMsg(string $msg): void
    {
        $this->log[] = '[' . date('H:i:s') . '] ' . $msg;
    }
}
