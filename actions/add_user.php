<?php
session_start();
require_once('../routeros_api.class.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['router_ip'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $profile = $_POST['profile'];

    $API = new RouterosAPI();

    if ($API->connect($_SESSION['router_ip'], $_SESSION['router_user'], $_SESSION['router_pass'], $_SESSION['router_port'])) {
        
        // Add the user to the Hotspot
        $API->comm("/ip/hotspot/user/add", array(
            "name"     => $user,
            "password" => $pass,
            "profile"  => $profile,
            "comment"  => "Added via Dashboard"
        ));

        $API->disconnect();
        // Redirect back with success
        header("Location: ../index.php?status=success");
        exit;
    } else {
        die("Connection Failed. Please reconnect your router on the dashboard.");
    }
} else {
    header("Location: ../index.php?status=error");
}