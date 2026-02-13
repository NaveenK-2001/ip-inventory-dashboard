<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function jsonResponse(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function normalizeStatus(string $status): string
{
    $status = ucfirst(strtolower(trim($status)));
    return in_array($status, ['Used', 'Free', 'Reserved', 'Static'], true) ? $status : 'Used';
}
