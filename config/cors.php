<?php
/**
 * CORS and Security Headers Configuration
 * ASHIRU TRADITIONAL BONE SETTER API
 */

// Allow from any origin (or specify front-end domain)
if (isset($_SERVER['HTTP_ORIGIN']) && !headers_sent()) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
} elseif (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
}

// Access-Control headers are received during OPTIONS requests
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && !headers_sent()) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) && !headers_sent()) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}
