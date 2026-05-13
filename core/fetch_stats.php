<?php
session_start();
require_once('../core/routeros_api.class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['router_ip'])) {
    echo json_encode(['status' => 'disconnected']);
    exit;
}

$API = new RouterosAPI();

if ($API->connect($_SESSION['router_ip'], $_SESSION['router_user'], $_SESSION['router_pass'], $_SESSION['router_port'])) {
    
    // Get CPU Load
    $resource = $API->comm("/system/resource/print");
    $cpu = $resource[0]['cpu-load'] ?? 0;
    $uptime = $resource[0]['uptime'] ?? '--';

    // Get Active Hotspot Users
    $users = $API->comm("/ip/hotspot/active/print", array("count-only" => ""));
    
    echo json_encode([
        'status' => 'connected',
        'cpu' => $cpu,
        'users' => $users,
        'uptime' => $uptime
    ]);

    $API->disconnect();
} else {
    echo json_encode(['status' => 'error']);
}