<?php
/**
 * Shared helper functions used across the public and admin pages.
 * Every database query in this file uses a prepared statement.
 */

// ------------------------------------------------------------------
// Output / formatting helpers
// ------------------------------------------------------------------

/** Escape a string for safe HTML output (XSS protection). */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a number as Philippine peso currency, e.g. 240 -> "₱240.00". */
function formatCurrency($amount) {
    return '₱' . number_format((float) $amount, 2);
}

/** Format a MySQL date (Y-m-d) as m/d/Y, or an em dash if empty/null. */
function formatDate($date) {
    if (empty($date)) {
        return '—';
    }
    return date('m/d/Y', strtotime($date));
}

/** Inline SVG icon markup for a strand, keyed by the icon name stored in the DB. */
function strandIcon($icon) {
    $icons = [
        'calculator' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><circle cx="8" cy="10.5" r="0.6" fill="currentColor"/><circle cx="12" cy="10.5" r="0.6" fill="currentColor"/><circle cx="16" cy="10.5" r="0.6" fill="currentColor"/><circle cx="8" cy="14.5" r="0.6" fill="currentColor"/><circle cx="12" cy="14.5" r="0.6" fill="currentColor"/><circle cx="16" cy="14.5" r="0.6" fill="currentColor"/><circle cx="8" cy="18.5" r="0.6" fill="currentColor"/><circle cx="12" cy="18.5" r="0.6" fill="currentColor"/><circle cx="16" cy="18.5" r="0.6" fill="currentColor"/></svg>',
        'flask' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6.4a2 2 0 0 1-.3 1L4.2 18a1.8 1.8 0 0 0 1.5 2.8h12.6a1.8 1.8 0 0 0 1.5-2.8l-4.5-8.6a2 2 0 0 1-.3-1V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/><line x1="6.5" y1="15" x2="17.5" y2="15"/></svg>',
        'book' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    ];
    return $icons[$icon] ?? $icons['book'];
}

// ------------------------------------------------------------------
// CSRF protection -- every admin form includes a hidden csrf_token
// field; every POST handler checks it before touching the database.
// ------------------------------------------------------------------

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

// ------------------------------------------------------------------
// Strands
// ------------------------------------------------------------------

function getAllStrands($pdo) {
    return $pdo->query('SELECT * FROM strands ORDER BY id ASC')->fetchAll();
}

function getStrandById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM strands WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ------------------------------------------------------------------
// Sections
// ------------------------------------------------------------------

function getSectionsByStrand($pdo, $strandId) {
    $stmt = $pdo->prepare('SELECT * FROM sections WHERE strand_id = ? ORDER BY name ASC');
    $stmt->execute([$strandId]);
    return $stmt->fetchAll();
}

function getSectionById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM sections WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** All sections grouped by strand_id, e.g. [1 => [...], 2 => [...]]. Used to feed the strand/section cascading dropdown in JS. */
function getSectionsGroupedByStrand($pdo) {
    $sections = $pdo->query('SELECT * FROM sections ORDER BY name ASC')->fetchAll();
    $grouped = [];
    foreach ($sections as $s) {
        $grouped[$s['strand_id']][] = ['id' => $s['id'], 'name' => $s['name']];
    }
    return $grouped;
}

function countStudentsInSection($pdo, $sectionId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE section_id = ?');
    $stmt->execute([$sectionId]);
    return (int) $stmt->fetchColumn();
}

// ------------------------------------------------------------------
// Students
// ------------------------------------------------------------------

function getStudentById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Students in one section, with optional name/ID search. */
function getStudentsBySection($pdo, $sectionId, $search = '') {
    $sql = 'SELECT * FROM students WHERE section_id = ?';
    $params = [$sectionId];
    if ($search !== '') {
        $sql .= ' AND (full_name LIKE ? OR student_id LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    $sql .= ' ORDER BY full_name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** All students (optionally filtered by strand/section/search), joined with strand code and section name for display. */
function getAllStudents($pdo, $search = '', $strandId = null, $sectionId = null) {
    $sql = 'SELECT s.*, st.code AS strand_code, sec.name AS section_name
            FROM students s
            JOIN strands st ON s.strand_id = st.id
            JOIN sections sec ON s.section_id = sec.id
            WHERE 1=1';
    $params = [];
    if ($search !== '') {
        $sql .= ' AND (s.full_name LIKE ? OR s.student_id LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    if (!empty($strandId)) {
        $sql .= ' AND s.strand_id = ?';
        $params[] = $strandId;
    }
    if (!empty($sectionId)) {
        $sql .= ' AND s.section_id = ?';
        $params[] = $sectionId;
    }
    $sql .= ' ORDER BY st.code ASC, sec.name ASC, s.full_name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Filters an already-fetched student list down to fully-paid or with-balance only. */
function filterStudentsByBalance($pdo, $students, $filter) {
    if ($filter !== 'fully_paid' && $filter !== 'with_balance') {
        return $students;
    }
    return array_values(array_filter($students, function ($s) use ($pdo, $filter) {
        $totals = calculateStudentTotals(getStudentPayments($pdo, $s['id']));
        return $filter === 'fully_paid' ? $totals['balance'] <= 0 : $totals['balance'] > 0;
    }));
}

// ------------------------------------------------------------------
// Payment requirements & student payments
// ------------------------------------------------------------------

function getPaymentRequirements($pdo) {
    return $pdo->query('SELECT * FROM payment_requirements ORDER BY sort_order ASC, id ASC')->fetchAll();
}

/**
 * Every payment requirement for one student, LEFT JOINed against any
 * existing student_payments row. A requirement with no row yet shows
 * up automatically as "unpaid" with no date -- this is what lets new
 * payment requirements apply to every student without a migration.
 */
function getStudentPayments($pdo, $studentId) {
    $stmt = $pdo->prepare(
        'SELECT pr.id AS requirement_id, pr.name, pr.amount,
                COALESCE(sp.status, "unpaid") AS status, sp.date_paid
         FROM payment_requirements pr
         LEFT JOIN student_payments sp
                ON sp.requirement_id = pr.id AND sp.student_id = ?
         ORDER BY pr.sort_order ASC, pr.id ASC'
    );
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

/**
 * Create or update the payment status and date for one student and requirement.
 * Uses INSERT ... ON DUPLICATE KEY UPDATE so it works whether or not
 * a student_payments row already exists for this pair.
 */
function updateStudentPayment($pdo, $studentId, $requirementId, $status, $datePaid) {
    $status = ($status === 'paid') ? 'paid' : 'unpaid';
    $datePaid = ($status === 'paid' && !empty($datePaid)) ? $datePaid : null;

    $stmt = $pdo->prepare(
        'INSERT INTO student_payments (student_id, requirement_id, status, date_paid)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status), date_paid = VALUES(date_paid)'
    );
    $stmt->execute([$studentId, $requirementId, $status, $datePaid]);
}

/** Given the array from getStudentPayments(), compute required/paid/balance totals. */
function calculateStudentTotals($payments) {
    $required = 0.0;
    $paid = 0.0;
    foreach ($payments as $p) {
        $required += (float) $p['amount'];
        if ($p['status'] === 'paid') {
            $paid += (float) $p['amount'];
        }
    }
    return [
        'required' => $required,
        'paid'     => $paid,
        'balance'  => $required - $paid,
    ];
}
