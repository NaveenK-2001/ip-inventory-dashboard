<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = db();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM ip_addresses')->fetchColumn();

    $statusStmt = $pdo->query(
        "SELECT status, COUNT(*) AS count_total
         FROM ip_addresses
         GROUP BY status"
    );

    $statusCounts = [
        'Used' => 0,
        'Free' => 0,
        'Reserved' => 0,
        'Static' => 0,
    ];

    foreach ($statusStmt as $row) {
        $status = ucfirst(strtolower((string) $row['status']));
        if (array_key_exists($status, $statusCounts)) {
            $statusCounts[$status] = (int) $row['count_total'];
        }
    }

    $locationStmt = $pdo->query(
        "SELECT
            location,
            SUM(CASE WHEN status = 'Used' THEN 1 ELSE 0 END) AS used_count,
            SUM(CASE WHEN status = 'Free' THEN 1 ELSE 0 END) AS free_count,
            SUM(CASE WHEN status = 'Reserved' THEN 1 ELSE 0 END) AS reserved_count,
            SUM(CASE WHEN status = 'Static' THEN 1 ELSE 0 END) AS static_count,
            COUNT(*) AS total_count
         FROM ip_addresses
         GROUP BY location
         ORDER BY location"
    );

    $locations = [];
    foreach ($locationStmt as $row) {
        $locations[] = [
            'location' => $row['location'],
            'used' => (int) $row['used_count'],
            'free' => (int) $row['free_count'],
            'reserved' => (int) $row['reserved_count'],
            'static' => (int) $row['static_count'],
            'total' => (int) $row['total_count'],
        ];
    }

    echo json_encode([
        'total' => $total,
        'used' => $statusCounts['Used'],
        'free' => $statusCounts['Free'],
        'reserved' => $statusCounts['Reserved'],
        'static' => $statusCounts['Static'],
        'locations' => $locations,
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load dashboard data.',
        'details' => $e->getMessage(),
    ]);
}
