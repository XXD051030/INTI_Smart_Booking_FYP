<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

require_admin();

$search = trim((string) ($_GET['search'] ?? ''));
$users = app()->users()->all($search !== '' ? $search : null);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="v2_users_export.csv"');

$output = fopen('php://output', 'wb');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($output, ['ID', 'Display Name', 'Student ID', 'Email', 'Language', 'Created At']);

foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['display_name'],
        student_id_from_email($user['email']),
        $user['email'],
        strtoupper($user['preferred_language']),
        $user['created_at'],
    ]);
}

fclose($output);
exit;
