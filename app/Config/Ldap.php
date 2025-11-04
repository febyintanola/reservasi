<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Ldap extends BaseConfig
{
    public string $host        = '';
    public int    $port        = 389;
    public string $encryption  = 'none'; // none|ssl|starttls
    public string $baseDn      = '';
    public ?string $bindDn     = null;
    public ?string $bindPass   = null;
    public ?string $allowedDomain = null;
    public ?string $allowedGroupDn = null;

    public function __construct()
    {
        parent::__construct();
        $this->host          = env('LDAP_HOST', 'localhost');
        $this->port          = (int) env('LDAP_PORT', 389);
        $this->encryption    = env('LDAP_ENCRYPTION', 'none');
        $this->baseDn        = env('LDAP_BASE_DN', '');
        $this->bindDn        = env('LDAP_BIND_DN', null);
        $this->bindPass      = env('LDAP_BIND_PASSWORD', null);
        $this->allowedDomain = env('LDAP_ALLOWED_DOMAIN', null);
        $this->allowedGroupDn= env('LDAP_ALLOWED_GROUP_DN', null);
    }
}