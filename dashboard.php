
<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

require_login();

$u = $_SESSION['user'];
$pageTitle = 'Dashboard';
$officeName = '';

$counts = [
    'Pending' => 0,
    'Cleared' => 0,
    'Rejected' => 0
];

$officeCards = [];
$printRows = [];
$studentNoForPrint = '';

// Get office name for office users.
if ($u['role'] === 'office' && !empty($u['office_id'])) {
    $q = $pdo->prepare('SELECT name FROM offices WHERE id = ?');
    $q->execute([$u['office_id']]);
    $officeName = (string) $q->fetchColumn();
}

// Prepare role-specific dashboard and print report data.
if ($u['role'] === 'student') {
    $studentId = (int) $u['student_id'];

    // Count the student's requests by status.
    $q = $pdo->prepare(
        'SELECT status, COUNT(*) AS total
         FROM clearance_requests
         WHERE student_id = ?
         GROUP BY status'
    );
    $q->execute([$studentId]);

    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if (isset($counts[$c['status']])) {
            $counts[$c['status']] = (int) $c['total'];
        }
    }

    // Show all clearance offices and the student's status.
    $q = $pdo->prepare(
        'SELECT
            o.name AS office_name,
            o.description,
            cr.status
         FROM offices o
         LEFT JOIN clearance_requests cr
            ON cr.office_id = o.id
            AND cr.student_id = ?
         ORDER BY o.id'
    );
    $q->execute([$studentId]);
    $officeCards = $q->fetchAll(PDO::FETCH_ASSOC);

    // Student's print report.
    $q = $pdo->prepare(
        'SELECT
            o.name AS office_name,
            cr.status,
            cr.student_note,
            cr.office_note,
            cr.updated_at
         FROM offices o
         LEFT JOIN clearance_requests cr
            ON cr.office_id = o.id
            AND cr.student_id = ?
         ORDER BY o.id'
    );
    $q->execute([$studentId]);
    $printRows = $q->fetchAll(PDO::FETCH_ASSOC);

    $q = $pdo->prepare(
        'SELECT student_no FROM students WHERE id = ?'
    );
    $q->execute([$studentId]);
    $studentNoForPrint = (string) $q->fetchColumn();

} elseif ($u['role'] === 'office') {

    // Count requests belonging only to this office.
    $q = $pdo->prepare(
        'SELECT status, COUNT(*) AS total
         FROM clearance_requests
         WHERE office_id = ?
         GROUP BY status'
    );
    $q->execute([$u['office_id']]);

    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if (isset($counts[$c['status']])) {
            $counts[$c['status']] = (int) $c['total'];
        }
    }

    // Office report: only this office's requests.
    $q = $pdo->prepare(
        'SELECT
            s.full_name,
            s.student_no,
            o.name AS office_name,
            cr.status,
            cr.student_note,
            cr.office_note,
            cr.updated_at
         FROM clearance_requests cr
         JOIN students s ON s.id = cr.student_id
         JOIN offices o ON o.id = cr.office_id
         WHERE cr.office_id = ?
         ORDER BY cr.updated_at DESC'
    );
    $q->execute([$u['office_id']]);
    $printRows = $q->fetchAll(PDO::FETCH_ASSOC);

} elseif ($u['role'] === 'admin') {

    // Admin dashboard totals.
    $totalStudents = (int) $pdo
        ->query('SELECT COUNT(*) FROM students')
        ->fetchColumn();

    $totalOffices = (int) $pdo
        ->query('SELECT COUNT(*) FROM offices')
        ->fetchColumn();

    $totalRequests = (int) $pdo
        ->query('SELECT COUNT(*) FROM clearance_requests')
        ->fetchColumn();

    // Admin report: all clearance requests.
    $q = $pdo->query(
        'SELECT
            s.full_name,
            s.student_no,
            o.name AS office_name,
            cr.status,
            cr.student_note,
            cr.office_note,
            cr.updated_at
         FROM clearance_requests cr
         JOIN students s ON s.id = cr.student_id
         JOIN offices o ON o.id = cr.office_id
         ORDER BY cr.updated_at DESC'
    );
    $printRows = $q->fetchAll(PDO::FETCH_ASSOC);
}

// Load the common page header.
require __DIR__ . '/includes/header.php';
?>

<?php if ($u['role'] === 'student'): ?>

<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">UNIVERSITY STUDENT PORTAL</p>

        <h1>
            Welcome,<br>
            <span><?= h($u['name']) ?>!</span>
        </h1>

        <p>
            Manage your clearance process easily and track
            your progress from all offices.
        </p>

        <p class="tagline">Together for a Brighter Future</p>
    </div>
</section>

<section class="stats-grid">

    <article class="stat-card">
        <span class="stat-icon blue">♙</span>
        <div>
            <p>Total Offices</p>
            <strong><?= count($officeCards) ?></strong>
            <small>Clearance offices</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon green">▤</span>
        <div>
            <p>My Requests</p>
            <strong>
                <?= array_sum($counts) ?>
            </strong>
            <small>Total requests</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon orange">⌛</span>
        <div>
            <p>Pending</p>
            <strong><?= $counts['Pending'] ?></strong>
            <small>Waiting for review</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon teal">✓</span>
        <div>
            <p>Cleared</p>
            <strong><?= $counts['Cleared'] ?></strong>
            <small>Completed</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon red">×</span>
        <div>
            <p>Rejected</p>
            <strong><?= $counts['Rejected'] ?></strong>
            <small>Needs attention</small>
        </div>
    </article>

</section>

<section class="clearance-section">

    <div class="section-heading">
        <div>
            <h2>▤ &nbsp;Your Clearance Status</h2>
            <p>Track your clearance progress from all offices</p>
        </div>

        <a class="button" href="student.php">
            View All Requests →
        </a>
    </div>

    <div class="office-grid">

        <?php foreach ($officeCards as $i => $o): ?>
            <?php
                $status = $o['status'] ?: 'Not requested';
                $icons = ['▣', '▤', '◆', '⬟', '☕', '▤'];
                $icon = $icons[$i % count($icons)];
                $statusClass = strtolower(
                    str_replace(' ', '-', $status)
                );
            ?>

            <article class="office-card">

                <div class="office-icon icon-<?= $i % 6 ?>">
                    <?= $icon ?>
                </div>

                <h3><?= h($o['office_name']) ?></h3>

                <span class="status-badge <?= h($statusClass) ?>">
                    <?= h($status) ?>
                </span>

                <p>
                    <?= h($o['description'] ?? 'Clearance request') ?>
                </p>

                <a href="student.php">View Details →</a>

            </article>
        <?php endforeach; ?>

    </div>
</section>

<div class="help-bar">
    <span class="help-mark">i</span>

    <div>
        <strong>Need Help?</strong>
        <p>
            If you have questions or issues, please contact
            the respective office or check your requests.
        </p>
    </div>

    <a class="button button-outline" href="student.php">
        ✉ Contact / View Requests
    </a>
</div>


<?php elseif ($u['role'] === 'admin'): ?>

<section class="hero compact-hero">
    <div class="hero-copy">
        <p class="eyebrow">UNIVERSITY ADMINISTRATION</p>

        <h1>
            Welcome,<br>
            <span><?= h($u['name']) ?>!</span>
        </h1>

        <p>
            Manage users, offices, and student clearance
            activity in one place.
        </p>
    </div>
</section>

<section class="stats-grid">

    <article class="stat-card">
        <span class="stat-icon blue">♙</span>
        <div>
            <p>Students</p>
            <strong><?= $totalStudents ?></strong>
            <small>Registered students</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon green">▦</span>
        <div>
            <p>Offices</p>
            <strong><?= $totalOffices ?></strong>
            <small>Clearance offices</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon orange">▤</span>
        <div>
            <p>Requests</p>
            <strong><?= $totalRequests ?></strong>
            <small>Total requests</small>
        </div>
    </article>

</section>

<section class="panel">
    <h2>Administration</h2>
    <p>Manage users and review the clearance process.</p>

    <a class="button" href="admin.php">Manage System →</a>

    <a class="button" href="office.php">View Requests →</a>
</section>


<?php elseif ($u['role'] === 'office'): ?>

<section class="hero compact-hero">
    <div class="hero-copy">
        <p class="eyebrow">OFFICE CLEARANCE PORTAL</p>

        <h1>
            Welcome,<br>
            <span><?= h($officeName) ?>!</span>
        </h1>

        <p>
            Review student clearance requests assigned
            to your office.
        </p>
    </div>
</section>

<section class="stats-grid">

    <article class="stat-card">
        <span class="stat-icon orange">⌛</span>
        <div>
            <p>Pending</p>
            <strong><?= $counts['Pending'] ?></strong>
            <small>Waiting for review</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon teal">✓</span>
        <div>
            <p>Cleared</p>
            <strong><?= $counts['Cleared'] ?></strong>
            <small>Completed requests</small>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon red">×</span>
        <div>
            <p>Rejected</p>
            <strong><?= $counts['Rejected'] ?></strong>
            <small>Needs attention</small>
        </div>
    </article>

</section>

<section class="panel">
    <h2><?= h($officeName) ?> Dashboard</h2>

    <p>
        This dashboard only shows requests assigned to
        <?= h($officeName) ?>.
    </p>

    <a class="button" href="office.php">
        Manage <?= h($officeName) ?> Requests →
    </a>
</section>


<?php else: ?>

<section class="panel">
    <h2>Dashboard</h2>
    <p>Your account role is not recognized.</p>
</section>

<?php endif; ?>


<!-- PRINT REPORT TOOLBAR -->
<div class="print-toolbar no-print">

    <div>
        <strong>Print dashboard report</strong>
        <p>Print this report or save it as a PDF.</p>
    </div>

    <button
        type="button"
        class="button print-button"
        onclick="window.print()"
    >
        🖨 Print Report
    </button>

</div>


<!-- PRINT REPORT -->
<section class="print-report">

    <div class="print-report-heading">

        <h1>University Student Clearance System</h1>

        <h2>
            <?php
            if ($u['role'] === 'student') {
                echo 'Student Clearance Report';
            } elseif ($u['role'] === 'office') {
                echo h($officeName) . ' Clearance Report';
            } else {
                echo 'System Clearance Report';
            }
            ?>
        </h2>

        <p>
            <strong>Printed by:</strong>
            <?= h($u['name']) ?>
            &nbsp; | &nbsp;
            <strong>Date:</strong>
            <?= date('Y-m-d H:i') ?>
        </p>

        <?php if ($u['role'] === 'student'): ?>

            <p>
                <strong>Student ID:</strong>
                <?= h($studentNoForPrint) ?>
                &nbsp; | &nbsp;
                <strong>Role:</strong> Student
            </p>

        <?php else: ?>

            <p>
                <strong>Role:</strong>
                <?= h(ucfirst($u['role'])) ?>
            </p>

        <?php endif; ?>

    </div>

    <table class="print-table">

        <thead>
            <tr>

                <?php if ($u['role'] !== 'student'): ?>
                    <th>Student</th>
                    <th>Student No.</th>
                <?php endif; ?>

                <th>Office</th>
                <th>Status</th>
                <th>Student Note</th>
                <th>Office Note</th>
                <th>Last Updated</th>

            </tr>
        </thead>

        <tbody>

            <?php if (empty($printRows)): ?>

                <tr>
                    <td colspan="<?= $u['role'] === 'student' ? 5 : 7 ?>">
                        No clearance records found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($printRows as $r): ?>

                    <tr>

                        <?php if ($u['role'] !== 'student'): ?>
                            <td>
                                <?= h($r['full_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= h($r['student_no'] ?? '') ?>
                            </td>
                        <?php endif; ?>

                        <td>
                            <?= h($r['office_name'] ?? '') ?>
                        </td>

                        <td>
                            <?= h($r['status'] ?: 'Not requested') ?>
                        </td>

                        <td>
                            <?= h($r['student_note'] ?? '') ?>
                        </td>

                        <td>
                            <?= h($r['office_note'] ?? '') ?>
                        </td>

                        <td>
                            <?= h($r['updated_at'] ?? '—') ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

    <p class="print-signature">
        Authorized signature: ______________________________
    </p>

</section>

<?php
require __DIR__ . '/includes/footer.php';
?>
