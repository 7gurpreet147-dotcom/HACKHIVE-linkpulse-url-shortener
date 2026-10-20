<?php

// Configure secure session cookies.
$isHttps = !empty($_SERVER["HTTPS"])
    && $_SERVER["HTTPS"] !== "off";

ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");
ini_set("session.cookie_httponly", "1");
ini_set("session.cookie_samesite", "Lax");

session_name("LINKPULSE_SESSION");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/LinkPulse/",
    "domain" => "",
    "secure" => $isHttps,
    "httponly" => true,
    "samesite" => "Lax"
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}