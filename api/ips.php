<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/api.php';

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $filters = [];
        $params = [];

        if (!empty($_GET['location'])) {
            $filters[] = 'location = :location';
            $params['location'] = $_GET['location'];
        }
        if (!empty($_GET['zone'])) {
            $filters[] = 'zone = :zone';
            $params['zone'] = $_GET['zone'];
        }
        if (!empty($_GET['vlan'])) {
            $filters[] = 'vlan = :vlan';
            $params['vlan'] = $_GET['vlan'];
        }
        if (!empty($_GET['status'])) {
            $filters[] = 'status = :status';
            $params['status'] = normalizeStatus((string) $_GET['status']);
        }
        if (!empty($_GET['q'])) {
            $filters[] = 'ip LIKE :q';
            $params['q'] = '%' . $_GET['q'] . '%';
        }

        $sql = 'SELECT id, ip, hostname, location, zone, vlan, device, os, status, owner FROM ip_addresses';
        if ($filters) {
            $sql .= ' WHERE ' . implode(' AND ', $filters);
        }
        $sql .= ' ORDER BY INET_ATON(ip), location, zone, CAST(vlan AS UNSIGNED)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $input = getJsonInput();

        if (!isset($input['ip'], $input['location'], $input['vlan'])) {
            jsonResponse(['error' => 'ip, location and vlan are required'], 422);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO ip_addresses (ip, hostname, location, zone, vlan, device, os, status, owner)
             VALUES (:ip, :hostname, :location, :zone, :vlan, :device, :os, :status, :owner)'
        );
        $stmt->execute([
            'ip' => trim((string) $input['ip']),
            'hostname' => trim((string) ($input['hostname'] ?? '')),
            'location' => trim((string) $input['location']),
            'zone' => trim((string) ($input['zone'] ?? '')),
            'vlan' => trim((string) $input['vlan']),
            'device' => trim((string) ($input['device'] ?? '')),
            'os' => trim((string) ($input['os'] ?? '')),
            'status' => normalizeStatus((string) ($input['status'] ?? 'Used')),
            'owner' => trim((string) ($input['owner'] ?? '')),
        ]);

        if (!empty($input['vlan_name']) && !empty($input['zone'])) {
            $vstmt = $pdo->prepare(
                'INSERT INTO vlans (vlan_id, name, location, zone)
                 VALUES (:vlan_id, :name, :location, :zone)
                 ON DUPLICATE KEY UPDATE name = VALUES(name)'
            );
            $vstmt->execute([
                'vlan_id' => trim((string) $input['vlan']),
                'name' => trim((string) $input['vlan_name']),
                'location' => trim((string) $input['location']),
                'zone' => trim((string) $input['zone']),
            ]);
        }

        jsonResponse(['message' => 'IP created'], 201);
    }

    if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonResponse(['error' => 'id is required'], 422);
        }
        $input = getJsonInput();

        $stmt = $pdo->prepare(
            'UPDATE ip_addresses
             SET hostname = :hostname, device = :device, os = :os, status = :status, owner = :owner
             WHERE id = :id'
        );
        $stmt->execute([
            'hostname' => trim((string) ($input['hostname'] ?? '')),
            'device' => trim((string) ($input['device'] ?? '')),
            'os' => trim((string) ($input['os'] ?? '')),
            'status' => normalizeStatus((string) ($input['status'] ?? 'Used')),
            'owner' => trim((string) ($input['owner'] ?? '')),
            'id' => $id,
        ]);

        jsonResponse(['message' => 'IP updated']);
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonResponse(['error' => 'id is required'], 422);
        }

        $stmt = $pdo->prepare('DELETE FROM ip_addresses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        jsonResponse(['message' => 'IP deleted']);
    }

    jsonResponse(['error' => 'Method not allowed'], 405);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error', 'details' => $e->getMessage()], 500);
}
