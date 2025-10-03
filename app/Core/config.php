<?php
// app/Core/config.php — Carrega variáveis do .env

function env($key, $default = null) {
    static $env = null;
    if ($env === null) {
        $env = [];
        if (file_exists(__DIR__ . '/../../.env')) {
            foreach (file(__DIR__ . '/../../.env') as $line) {
                if (preg_match('/^([A-Z0-9_]+)=(.*)$/', trim($line), $m)) {
                    $env[$m[1]] = trim($m[2], '"');
                }
            }
        }
    }
    return $env[$key] ?? $default;
}
