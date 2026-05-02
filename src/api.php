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

if ($action === 'calculate') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Inputs: base_ip, prefix, hosts (array)
    $baseIP = $data['base_ip'];
    $prefix = (int)$data['prefix'];
    $hosts = $data['hosts']; // e.g. [50, 20, 10]

    $method = null;
    if (is_callable([SubnetCalculator::class, 'calculateVLSM'])) {
        $method = 'calculateVLSM';
    } elseif (is_callable([SubnetCalculator::class, 'calculate'])) {
        $method = 'calculate';
    }

    if ($method === null) {
        http_response_code(500);
        echo json_encode(['error' => 'Subnet calculation method unavailable']);
        exit;
    }

    $result = call_user_func([SubnetCalculator::class, $method], $baseIP, $prefix, $hosts);
    echo json_encode($result);
    exit;
}