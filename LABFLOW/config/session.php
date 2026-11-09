<?php
/**
 * Mulai sesi dengan cookie yang lebih aman (HttpOnly + SameSite=Lax, Secure bila HTTPS).
 * Dipakai oleh auth.php, login.php, dan logout.php.
 */
function lf_session_start(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
