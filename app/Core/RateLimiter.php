<?php
namespace App\Core;

class RateLimiter
{
    public static function check($key, $maxAttempts = 5, $minutes = 5)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $file = sys_get_temp_dir() . '/crm_rate_' . md5($key . $ip);
        $now = time();
        $attempts = [];
        if (file_exists($file)) {
            $attempts = json_decode(file_get_contents($file), true) ?: [];
            $attempts = array_filter($attempts, fn($t) => $t > $now - $minutes * 60);
        }
        if (count($attempts) >= $maxAttempts) {
            http_response_code(429);
            die('Muitas tentativas. Tente novamente em alguns minutos.');
        }
        $attempts[] = $now;
        file_put_contents($file, json_encode($attempts));
    }
}
