<?php
// api.php
header('Content-Type: application/json');
require_once 'SubnetCalculator.php';
require_once 'IpUtils.php';

$action = $_GET['action'] ?? '';

if ($action === 'generate') {
    $prefix = isset($_GET['prefix']) ? (int)$_GET['prefix'] : null;
    if ($prefix !== null) {
        $prefix = max(0, min(30, $prefix));
        echo json_encode(['ip' => IpUtils::generateForPrefix($prefix)]);
    } else {
        echo json_encode(['ip' => IpUtils::generateRandomIP($_GET['class'] ?? 'C')]);
    }
    exit;
}

if ($action === 'calculate') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $baseIP = $data['base_ip'] ?? '';
    $prefix = (int)($data['prefix'] ?? 0);
    $hosts  = $data['hosts'] ?? [];

    // Validate prefix
    if ($prefix < 1 || $prefix > 30) {
        echo json_encode(['status' => 'error', 'message' => 'Prefix must be between 1 and 30.']);
        exit;
    }

    // Validate and sanitize hosts array: [{name, count}, ...]
    $hosts = array_values(array_filter(
        array_map(fn($h) => [
            'name'  => trim($h['name'] ?? 'Unnamed'),
            'count' => (int)($h['count'] ?? 0)
        ], $hosts),
        fn($h) => $h['count'] > 0
    ));

    if (empty($hosts)) {
        echo json_encode(['status' => 'error', 'message' => 'At least one valid positive host count is required.']);
        exit;
    }

    echo json_encode(SubnetCalculator::calculate($baseIP, $prefix, $hosts));
    exit;
}