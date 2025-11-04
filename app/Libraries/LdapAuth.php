<?php

namespace App\Libraries;

use App\Config\Ldap as LdapConfig;

class LdapAuth
{
    protected LdapConfig $cfg;

    public function __construct(?LdapConfig $cfg = null)
    {
        $this->cfg = $cfg ?? new LdapConfig();
    }

    public function authenticate(string $username, string $password): array
    {
        if (!extension_loaded('ldap')) {
            return ['ok' => false, 'error' => 'PHP LDAP extension not loaded'];
        }
        if ($password === '') {
            return ['ok' => false, 'error' => 'Empty password'];
        }

        $host = $this->cfg->host;
        if ($this->cfg->encryption === 'ssl') {
            if (stripos($host, 'ldaps://') !== 0) {
                $host = 'ldaps://' . $host;
            }
            // Port biasanya 636 untuk LDAPS
        }

        $conn = @ldap_connect($host, $this->cfg->port);
        if (!$conn) {
            return ['ok' => false, 'error' => 'Cannot connect to LDAP host'];
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if ($this->cfg->encryption === 'starttls') {
            if (!@ldap_start_tls($conn)) {
                return ['ok' => false, 'error' => 'STARTTLS failed'];
            }
        }

        // Gunakan UPN jika tidak ada '@' dan domain di-set
        $input = trim($username);
        $userUPN = $input;
        if (strpos($input, '@') === false && !empty($this->cfg->allowedDomain)) {
            $userUPN = $input . '@' . $this->cfg->allowedDomain;
        }

        // Bind langsung sebagai user
        if (!@ldap_bind($conn, $userUPN, $password)) {
            return ['ok' => false, 'error' => 'Invalid credentials: ' . ldap_error($conn)];
        }

        // Ambil atribut user setelah bind
        $filter = (strpos($input, '@') !== false)
            ? sprintf('(userPrincipalName=%s)', $this->escape($userUPN))
            : sprintf('(sAMAccountName=%s)', $this->escape($input));
        $attrs  = ['distinguishedName','displayName','mail','memberOf','sAMAccountName','userPrincipalName'];
        $sr     = @ldap_search($conn, $this->cfg->baseDn, $filter, $attrs);
        if ($sr) {
            $entries = ldap_get_entries($conn, $sr);
            if (($entries['count'] ?? 0) > 0) {
                $userDn    = $entries[0]['dn'];
                $userAttrs = $this->normalizeAttrs($entries[0]);
            }
        }

        // Cek membership group (opsional)
        if ($this->cfg->allowedGroupDn && isset($userAttrs['memberof'])) {
            if (!$this->inGroup((array) $userAttrs['memberof'], $this->cfg->allowedGroupDn)) {
                return ['ok' => false, 'error' => 'User not in allowed group'];
            }
        }

        return [
            'ok'   => true,
            'user' => [
                'dn'   => $userDn,
                'name' => $userAttrs['displayname'] ?? $userAttrs['samaccountname'] ?? $input,
                'mail' => $userAttrs['mail'] ?? null,
                'upn'  => $userAttrs['userprincipalname'] ?? $userUPN,
                'sam'  => $userAttrs['samaccountname'] ?? $input,
                'groups' => (array) ($userAttrs['memberof'] ?? []),
            ],
        ];
    }

    protected function inGroup(array $memberOf, string $groupDn): bool
    {
        foreach ($memberOf as $dn) {
            if (strcasecmp($dn, $groupDn) === 0) return true;
        }
        return false;
    }

    protected function normalizeAttrs(array $entry): array
    {
        $out = [];
        foreach ($entry as $k => $v) {
            if (is_int($k)) continue;
            if (is_array($v) && isset($v['count'])) {
                $vals = [];
                for ($i=0; $i<$v['count']; $i++) $vals[] = $v[$i];
                $out[strtolower($k)] = $v['count'] <= 1 ? ($vals[0] ?? null) : $vals;
            } else {
                $out[strtolower($k)] = $v;
            }
        }
        return $out;
    }

    protected function escape(string $value): string
    {
        $from = ['\\', '*', '(', ')', "\x00"];
        $to   = ['\\5c', '\\2a', '\\28', '\\29', '\\00'];
        return str_replace($from, $to, $value);
    }
}