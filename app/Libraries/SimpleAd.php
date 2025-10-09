<?php

namespace App\Libraries;

use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * Simple wrapper so you can configure ONLY two things like:
 *  DIRECTORY_PATH=LDAP://10.8.10.31
 *  DIRECTORY_DOMAIN=indonesiapower
 * Optionally DIRECTORY_BIND_USER / DIRECTORY_BIND_PASS (UPN format recommended)
 *
 * It will:
 *  - Strip LDAP:// prefix
 *  - Attempt base DN derivation if domain contains dots
 *  - Try RootDSE discovery if base DN not inferred
 *  - Provide healthCheck() and authenticate($identifier,$password)
 */
class SimpleAd
{
    protected string $rawPath;
    protected string $rawDomain;
    protected ?string $bindUser;
    protected ?string $bindPass;

    protected string $host;
    protected ?string $baseDn = null;

    public function __construct()
    {
        $this->rawPath = (string) (env('DIRECTORY_PATH', ''));
        $this->rawDomain = (string) (env('DIRECTORY_DOMAIN', ''));
        $this->bindUser = env('DIRECTORY_BIND_USER', null);
        $this->bindPass = env('DIRECTORY_BIND_PASS', null);

        $this->host = $this->normalizeHost($this->rawPath);
        $this->baseDn = $this->deriveBaseDn($this->rawDomain);
    }

    protected function normalizeHost(string $path): string
    {
        $path = trim($path);
        $path = preg_replace('#^ldap://#i', '', $path);
        $path = preg_replace('#^ldaps://#i', '', $path);
        return $path;
    }

    protected function deriveBaseDn(string $domain): ?string
    {
        $domain = trim($domain);
        if ($domain === '') return null;
        if (strpos($domain, '.') === false) return null; // single label, can't build full DN yet
        $parts = array_filter(explode('.', $domain));
        if (!$parts) return null;
        return implode(',', array_map(fn($p) => 'DC=' . $p, $parts));
    }

    protected function makeConnection(): Connection
    {
        $config = [
            'hosts' => [$this->host],
            'port' => 389,
            'use_ssl' => false,
            'use_tls' => false,
        ];
        if ($this->baseDn) {
            $config['base_dn'] = $this->baseDn;
        }
        if ($this->bindUser && $this->bindPass) {
            $config['username'] = $this->bindUser;
            $config['password'] = $this->bindPass;
        }
        $conn = new Connection($config);
        $conn->connect();

        // Attempt RootDSE to fill base DN if missing
        if (!$this->baseDn && method_exists($conn, 'getRootDse')) {
            try {
                $root = $conn->getRootDse();
                $nc = $root->get('defaultNamingContext');
                if ($nc) {
                    $this->baseDn = is_array($nc) ? $nc[0] : $nc;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return $conn;
    }

    public function healthCheck(): array
    {
        if (!$this->host) {
            return [
                'connected' => false,
                'host' => null,
                'base_dn' => $this->baseDn,
                'error' => 'DIRECTORY_PATH not set',
            ];
        }
        try {
            $conn = $this->makeConnection();
            return [
                'connected' => true,
                'host' => $this->host,
                'base_dn' => $this->baseDn,
                'using_bind' => (bool) ($this->bindUser && $this->bindPass),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'host' => $this->host,
                'base_dn' => $this->baseDn,
                'using_bind' => (bool) ($this->bindUser && $this->bindPass),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function authenticate(string $identifier, string $password): array
    {
        try {
            $conn = $this->makeConnection();
            // 1. Try treat identifier as UPN or DN directly
            if ($conn->auth()->attempt($identifier, $password)) {
                $user = LdapUser::where('userprincipalname', '=', $identifier)->first() ?: LdapUser::where('samaccountname', '=', $identifier)->first();
                return ['success' => true, 'user' => $user, 'error' => null];
            }

            // 2. If we have bind account, search user attributes then bind by DN
            if ($this->bindUser && $this->bindPass) {
                $user = LdapUser::where('userprincipalname', '=', $identifier)->first();
                if (!$user) {
                    $user = LdapUser::where('samaccountname', '=', $identifier)->first();
                }
                if ($user) {
                    if ($conn->auth()->attempt($user->getDn(), $password)) {
                        return ['success' => true, 'user' => $user, 'error' => null];
                    }
                }
            }
            return ['success' => false, 'user' => null, 'error' => 'Invalid credentials'];
        } catch (\Throwable $e) {
            return ['success' => false, 'user' => null, 'error' => $e->getMessage()];
        }
    }
}
