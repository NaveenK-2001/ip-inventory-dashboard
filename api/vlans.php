<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/api.php';

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $location = (string) ($_GET['location'] ?? '');
        $zone = (string) ($_GET['zone'] ?? '');

        $sql = 'SELECT vlan_id AS id, name, location, zone FROM vlans';
        $params = [];
        $filters = [];
        if ($location !== '') {
            $filters[] = 'location = :location';
            $params['location'] = $location;
        }
        if ($zone !== '') {
            $filters[] = 'zone = :zone';
            $params['zone'] = $zone;
        }
        if ($filters) {
            $sql .= ' WHERE ' . implode(' AND ', $filters);
        }
        $sql .= ' ORDER BY location, zone, CAST(vlan_id AS UNSIGNED)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $input = getJsonInput();
        if (!isset($input['id'], $input['location'], $input['zone'])) {
            jsonResponse(['error' => 'id, location, zone required'], 422);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO vlans (vlan_id, name, location, zone)
             VALUES (:id, :name, :location, :zone)
             ON DUPLICATE KEY UPDATE name = VALUES(name)'
        );
        $stmt->execute([
            'id' => trim((string) $input['id']),
            'name' => trim((string) ($input['name'] ?? ('VLAN ' . $input['id']))),
            'location' => trim((string) $input['location']),
            'zone' => trim((string) $input['zone']),
        ]);
        jsonResponse(['message' => 'VLAN saved'], 201);
    }

    if ($method === 'DELETE') {
        $id = (string) ($_GET['id'] ?? '');
        $location = (string) ($_GET['location'] ?? '');
        $zone = (string) ($_GET['zone'] ?? '');
        if ($id === '' || $location === '' || $zone === '') {
            jsonResponse(['error' => 'id, location, zone required'], 422);
        }

        $check = $pdo->prepare('SELECT COUNT(*) FROM ip_addresses WHERE vlan = :id AND location = :location AND zone = :zone');
        $check->execute(['id' => $id, 'location' => $location, 'zone' => $zone]);
        if ((int) $check->fetchColumn() > 0) {
            jsonResponse(['error' => 'Cannot delete VLAN with IPs'], 409);
        }

        $stmt = $pdo->prepare('DELETE FROM vlans WHERE vlan_id = :id AND location = :location AND zone = :zone');
        $stmt->execute(['id' => $id, 'location' => $location, 'zone' => $zone]);
        jsonResponse(['message' => 'VLAN deleted']);
    }

    jsonResponse(['error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error', 'details' => $e->getMessage()], 500);
}
