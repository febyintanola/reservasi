<?php
// Quick AD test script. Usage:
// php tests/ad_test.php user@domain.local PasswordHere
// or set AD_TEST_USER and AD_TEST_PASS env vars and run php tests/ad_test.php

require __DIR__ . '/../vendor/autoload.php';

// Provide minimal env() helper if not loaded by framework
if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            return $default;
        }
        // Normalize boolean-like strings similar to CI helper behavior
        $lower = strtolower($val);
        return match ($lower) {
            'true', '(true)', 'yes', 'on', '1' => true,
            'false', '(false)', 'no', 'off', '0' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $val,
        };
    }
}

// Load env from file if exists (optional)
if (file_exists(__DIR__ . '/../.env')) {
    // simple loader for local testing (not a full dotenv)
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if ($k && getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

$user = $argv[1] ?? getenv('AD_TEST_USER');
$pass = $argv[2] ?? getenv('AD_TEST_PASS');

// If no user/pass provided -> run health check only
$modeHealthOnly = (!$user || !$pass);
if ($modeHealthOnly) {
    echo "Running AD health check (no credentials provided) ...\n";
}

if (!$modeHealthOnly) {
    echo "AD test: identifier={$user}\n";
}

// Ensure AdAuthService exists
if (!class_exists('\App\Libraries\AdAuthService')) {
    echo "AdAuthService not found. Make sure app/Libraries/AdAuthService.php exists and composer autoload is set.\n";
    exit(1);
}

try {
    $ad = new \App\Libraries\AdAuthService();
    if ($modeHealthOnly) {
        $hc = $ad->healthCheck();
        echo "connected: " . ($hc['connected'] ? 'yes' : 'no') . "\n";
        echo "hosts: " . implode(',', $hc['hosts_tried']) . "\n";
        echo "base_dn: " . ($hc['base_dn'] ?? '(none)') . "\n";
        echo "root_default_naming_context: " . ($hc['root_default_naming_context'] ?? '(n/a)') . "\n";
        echo "using_service_account: " . ($hc['using_service_account'] ? 'yes' : 'no') . "\n";
        if ($hc['error']) {
            echo "error: {$hc['error']}\n";
            exit(2);
        }
        exit($hc['connected'] ? 0 : 3);
    } else {
        $res = $ad->authenticate($user, $pass);
        if ($res['success']) {
            echo "AUTH SUCCESS\n";
            if ($res['user']) {
                echo "Found LDAP user: " . ($res['user']->getFirstAttribute('cn') ?? $res['user']->getFirstAttribute('name') ?? 'n/a') . "\n";
            }
            exit(0);
        }
        echo "AUTH FAILED: " . ($res['error'] ?? 'unknown') . "\n";
        exit(2);
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(3);
}
