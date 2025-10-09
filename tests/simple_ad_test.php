<?php
// Usage:
// 1. Set in .env (or environment):
//    DIRECTORY_PATH=LDAP://10.8.10.31
//    DIRECTORY_DOMAIN=indonesiapower (or full like indonesiapower.local if known)
//    (Optional) DIRECTORY_BIND_USER=service@indonesiapower.local
//    (Optional) DIRECTORY_BIND_PASS=Secret
// 2. php tests/simple_ad_test.php           -> health check
// 3. php tests/simple_ad_test.php user pass -> authenticate

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val === false) return $default;
        $lower = strtolower($val);
        return match ($lower) {
            'true','(true)','yes','on','1' => true,
            'false','(false)','no','off','0' => false,
            'empty','(empty)' => '',
            'null','(null)' => null,
            default => $val,
        };
    }
}

// Optional basic .env loader (lightweight)
if (file_exists(__DIR__ . '/../.env')) {
    foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k,$v] = array_map('trim', explode('=', $line, 2));
        if ($k && getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

use App\Libraries\SimpleAd;

$identifier = $argv[1] ?? null;
$password = $argv[2] ?? null;

$ad = new SimpleAd();

if (!$identifier || !$password) {
    echo "== SIMPLE AD HEALTH CHECK ==\n";
    $hc = $ad->healthCheck();
    foreach ($hc as $k => $v) {
        if (is_array($v)) $v = json_encode($v);
        echo $k . ': ' . ($v === null ? '(null)' : $v) . "\n";
    }
    exit($hc['connected'] ? 0 : 2);
}

echo "== SIMPLE AD AUTH ==\n";
$res = $ad->authenticate($identifier, $password);
if ($res['success']) {
    echo "AUTH SUCCESS\n";
    if ($res['user']) {
        echo 'CN: ' . ($res['user']->getFirstAttribute('cn') ?? 'n/a') . "\n";
    }
    exit(0);
}
echo 'AUTH FAILED: ' . ($res['error'] ?? 'unknown') . "\n";
exit(3);
