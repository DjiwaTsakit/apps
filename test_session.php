<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

echo '<pre>';
echo 'Session Status: ' . session_status() . "\n";
echo 'Session ID: ' . session_id() . "\n";
echo 'Session Path: ' . session_save_path() . "\n";
echo 'Cookie Params: ' . print_r(session_get_cookie_params(), true) . "\n";
print_r($_SESSION);
