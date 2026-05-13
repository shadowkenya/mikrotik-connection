<?php
session_start();

// FIX: Added ../ to look in the parent directory for the class file
require_once('../routeros_api.class.php'); 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip   = $_POST['ip'];
    $user = $_POST['user'];
    $pass = $_POST['pass'] ?? '';
    $port = intval($_POST['port']);

    $API = new RouterosAPI();
    
    if ($API->connect($ip, $user, $pass, $port)) {
        // Store in session so fetch_stats.php and add_user.php can use them
        $_SESSION['router_ip'] = $ip;
        $_SESSION['router_user'] = $user;
        $_SESSION['router_pass'] = $pass;
        $_SESSION['router_port'] = $port;
        
        $API->disconnect();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not connect to ' . $ip]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}