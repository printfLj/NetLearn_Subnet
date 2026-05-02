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
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Inputs: base_ip, prefix, hosts (array)
    $baseIP = $data['base_ip'];
    $prefix = (int)$data['prefix'];
    $hosts = $data['hosts']; // e.g. [50, 20, 10]

    $result = SubnetCalculator::calculateVLSM($baseIP, $prefix, $hosts);
    echo json_encode($result);
    exit;
}