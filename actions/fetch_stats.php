<?php
session_start();
require_once('../routeros_api.class.php'); 
header('Content-Type: application/json');

if (!isset($_SESSION['router_ip'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$API = new RouterosAPI();
if ($API->connect($_SESSION['router_ip'], $_SESSION['router_user'], $_SESSION['router_pass'], $_SESSION['router_port'])) {
    
    $resource = $API->comm('/system/resource/print');
    $activeUsers = $API->comm('/ip/hotspot/active/print');

    $cpu = "0";
    $uptime = "--:--:--";

    if (is_array($resource)) {
        foreach ($resource as $item) {
            $cpu = $item['cpu-load'] ?? $item['load'] ?? $cpu;
            $uptime = $item['uptime'] ?? $uptime;
        }
    }

    echo json_encode([
        'status'   => 'connected',
        'cpu' => ($cpu == "" ? "0" : $cpu),
        'uptime'   => $uptime,
        'users'    => count($activeUsers),
        'userList' => $activeUsers // This sends the names to the table
    ]);
    
    $API->disconnect();
} else {
    echo json_encode(['status' => 'disconnected']);
}