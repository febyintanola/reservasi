<?php

namespace App\Libraries;

use LdapRecord\Container;
use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Auth\BindException;

class AdAuthService
{
    protected $hosts;
    protected $baseDn;
    protected $domain; 
    protected $port;
    protected $useSsl;
    protected $useTls;
    protected $version;
    protected $bindUser;
    protected $bindPassword;

    public function __construct()
    {
        $this->hosts = array_filter(array_map('trim', explode(',', env('AD_HOSTS', ''))));
        $this->baseDn = env('AD_BASE_DN', '');
        $this->domain = env('AD_DOMAIN', '');
        $this->port = (int) env('AD_PORT', 389);
        $this->useSsl = filter_var(env('AD_USE_SSL', false), FILTER_VALIDATE_BOOLEAN);
        $this->useTls = filter_var(env('AD_USE_TLS', false), FILTER_VALIDATE_BOOLEAN);
        $this->version = (int) env('AD_VERSION', 3);
        $this->bindUser = env('AD_BIND_USER', '');
        $this->bindPassword = env('AD_BIND_PASSWORD', '');

        // Normalize / derive base DN if user provided only a bare domain label or left it empty.
        $this->normalizeBaseDn();
    }

    /**
     * Attempt to build a proper base DN from AD_BASE_DN or AD_DOMAIN.
     * Rules:
     *  - If baseDn already contains '=' we assume it's a full DN and keep it.
     *  - Else if a domain (AD_DOMAIN or baseDn value) contains dots: build DC=part list.
     *  - Else (single label or empty) we leave for later rootDSE discovery; as a last resort use DC=<label>.
     */
    protected function normalizeBaseDn(): void
    {
        $candidate = $this->baseDn;
        if (empty($candidate) && !empty($this->domain)) {
            $candidate = $this->domain;
        }

        if (!empty($candidate) && strpos($candidate, '=') === false) {
            // Looks like a raw domain string
            if (strpos($candidate, '.') !== false) {
                $parts = array_filter(explode('.', $candidate));
                if ($parts) {
                    $this->baseDn = implode(',', array_map(fn($p) => 'DC=' . $p, $parts));
                }
            } else {
                // Single label, keep empty for rootDSE discovery later, but store fallback
                $this->baseDn = ''; // will attempt runtime discovery
                $this->domain = $candidate; // ensure retained
            }
        }
    }

    /**
     * Authenticate against AD. Returns ['success'=>bool, 'user'=>LdapUser|null, 'error'=>string|null]
     */
    public function authenticate(string $identifier, string $password): array
    {
        if (empty($this->hosts)) {
            return ['success' => false, 'user' => null, 'error' => 'AD hosts not configured'];
        }

        try {
            $connConfig = [
                'hosts' => $this->hosts,
                // base_dn may be empty here; we will attempt discovery if so
                'base_dn' => $this->baseDn,
                'port' => $this->port,
                'use_ssl' => $this->useSsl,
                'use_tls' => $this->useTls,
                'version' => $this->version,
            ];

            if (!empty($this->bindUser) && !empty($this->bindPassword)) {
                $connConfig['username'] = $this->bindUser;
                $connConfig['password'] = $this->bindPassword;
            }

            $connection = new Connection($connConfig);
            Container::addConnection($connection);
            $connection->connect();

            // If base DN was not set (empty) attempt RootDSE discovery
            if (empty($this->baseDn)) {
                try {
                    if (method_exists($connection, 'getRootDse')) {
                        $root = $connection->getRootDse();
                        $defaultNc = $root->get('defaultNamingContext');
                        if ($defaultNc) {
                            $this->baseDn = is_array($defaultNc) ? $defaultNc[0] : $defaultNc;
                        }
                    }
                } catch (\Throwable $e) {
                    // Swallow - fallback will be single-label domain if provided
                    if (empty($this->baseDn) && !empty($this->domain)) {
                        $this->baseDn = 'DC=' . $this->domain;
                    }
                }
            }

            // If service account present -> search then bind as user
            if (!empty($this->bindUser) && !empty($this->bindPassword)) {
                // Try common attributes
                $ldapUser = LdapUser::where('mail', '=', $identifier)->first();
                if (!$ldapUser) {
                    $ldapUser = LdapUser::where('userprincipalname', '=', $identifier)->first();
                }
                if (!$ldapUser) {
                    $ldapUser = LdapUser::where('samaccountname', '=', $identifier)->first();
                }

                if (!$ldapUser) {
                    return ['success' => false, 'user' => null, 'error' => 'User not found in AD'];
                }

                $dn = $ldapUser->getDn();
                if ($connection->auth()->attempt($dn, $password)) {
                    return ['success' => true, 'user' => $ldapUser, 'error' => null];
                }

                return ['success' => false, 'user' => null, 'error' => 'Invalid credentials'];
            }

            // If no service account -> direct attempt (identifier must be bindable)
            if ($connection->auth()->attempt($identifier, $password)) {
                $ldapUser = LdapUser::where('mail', '=', $identifier)->first() ?: LdapUser::where('userprincipalname', '=', $identifier)->first();
                return ['success' => true, 'user' => $ldapUser, 'error' => null];
            }

            return ['success' => false, 'user' => null, 'error' => 'Invalid credentials'];
        } catch (BindException $e) {
            return ['success' => false, 'user' => null, 'error' => 'BindException: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'user' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Basic connectivity / health check without needing a user password.
     * Returns array:
     *  [ 'connected' => bool, 'base_dn' => string|null, 'root_default_naming_context' => string|null,
     *    'hosts_tried' => array, 'error' => string|null, 'using_service_account' => bool ]
     */
    public function healthCheck(): array
    {
        if (empty($this->hosts)) {
            return [
                'connected' => false,
                'base_dn' => $this->baseDn ?: null,
                'root_default_naming_context' => null,
                'hosts_tried' => [],
                'error' => 'AD hosts not configured',
                'using_service_account' => false,
            ];
        }

        $connConfig = [
            'hosts' => $this->hosts,
            'base_dn' => $this->baseDn,
            'port' => $this->port,
            'use_ssl' => $this->useSsl,
            'use_tls' => $this->useTls,
            'version' => $this->version,
        ];

        $usingService = false;
        if (!empty($this->bindUser) && !empty($this->bindPassword)) {
            $connConfig['username'] = $this->bindUser;
            $connConfig['password'] = $this->bindPassword;
            $usingService = true;
        }

        try {
            $connection = new Connection($connConfig);
            $connection->connect();
            $rootNc = null;
            if (method_exists($connection, 'getRootDse')) {
                try {
                    $root = $connection->getRootDse();
                    $defaultNc = $root->get('defaultNamingContext');
                    if ($defaultNc) {
                        $rootNc = is_array($defaultNc) ? $defaultNc[0] : $defaultNc;
                        if (empty($this->baseDn)) {
                            $this->baseDn = $rootNc;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore rootDSE fetch errors for health
                }
            }
            return [
                'connected' => true,
                'base_dn' => $this->baseDn ?: null,
                'root_default_naming_context' => $rootNc,
                'hosts_tried' => $this->hosts,
                'error' => null,
                'using_service_account' => $usingService,
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'base_dn' => $this->baseDn ?: null,
                'root_default_naming_context' => null,
                'hosts_tried' => $this->hosts,
                'error' => $e->getMessage(),
                'using_service_account' => $usingService,
            ];
        }
    }
}
