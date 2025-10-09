<?php
// Raw low-level LDAP probe (no LdapRecord) to diagnose connectivity.
// Usage: php tests/raw_ldap_probe.php
// Optional env: LDAP_HOST=10.8.10.31 LDAP_PORT=389

$host = getenv('LDAP_HOST') ?: '10.8.10.31';
$ports = [ (int)(getenv('LDAP_PORT') ?: 389), 636 ];

echo "LDAP Raw Probe to host: $host\n";

foreach ($ports as $port) {
    echo "\n--- Testing port $port ---\n";
    $conn = @ldap_connect($host, $port);
    if (!$conn) {
        echo "ldap_connect failed\n";
        continue;
    }
    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

    if ($port == 389) {
        // Try plain anonymous bind first
        $ok = @ldap_bind($conn);
        echo 'Anonymous bind: ' . ($ok ? 'OK' : ('FAIL (' . ldap_error($conn) . ')')) . "\n";
        // Try STARTTLS
        if (function_exists('ldap_start_tls')) {
            echo "Attempt STARTTLS... ";
            if (@ldap_start_tls($conn)) {
                echo "OK\n";
            } else {
                echo 'FAIL (' . ldap_error($conn) . ")\n";
            }
        }
    } else { // 636
        // LDAPS typically still just ldap_bind (connection is already SSL if supported)
        $ok = @ldap_bind($conn);
        echo 'LDAPS anonymous bind: ' . ($ok ? 'OK' : ('FAIL (' . ldap_error($conn) . ')')) . "\n";
    }
    @ldap_unbind($conn);
}

echo "\nDone.\n";