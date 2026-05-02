<?php
// api.php
header('Content-Type: application/json');
require_once 'SubnetCalculator.php';
require_once 'IpUtils.php';

$action = $_GET['action'] ?? '';

if ($action === 'generate') {
    echo json_encode(['ip' => IpUtils::generateRandomIP($_GET['class'] ?? 'C')]);
    exit;
}

if ($action === 'generate_for_prefix') {
    $prefix = (int)($_GET['prefix'] ?? 24);
    $prefix = max(0, min(30, $prefix)); // clamp to valid range
    echo json_encode(['ip' => IpUtils::generateForPrefix($prefix)]);
    exit;
}

if ($action === 'calculate') {
    $data = json_decode(file_get_contents('php://input'), true);

    $baseIP = $data['base_ip'] ?? '';
    $prefix = (int)($data['prefix'] ?? 0);
    $hosts  = $data['hosts'] ?? [];

    // Validate prefix: must be a positive integer between 1 and 30
    if ($prefix < 0 || $prefix > 30) {
        echo json_encode(['status' => 'error', 'message' => 'Prefix must be a positive integer between 1 and 30.']);
        exit;
    }

    // Validate hosts: each must be a positive integer
    $hosts = array_values(array_filter(array_map('intval', $hosts), fn($h) => $h > 0));
    if (empty($hosts)) {
        echo json_encode(['status' => 'error', 'message' => 'At least one valid positive host count is required.']);
        exit;
    }

    $result = SubnetCalculator::calculateVLSM($baseIP, $prefix, $hosts);
    echo json_encode($result);
    exit;
}