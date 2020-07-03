<?php

class LDAPHelper
{
    /**
     * @var $ldap
     */
    private $ldap;

    /**
     * @var $ldap
     */
    private $adminLdap;

    /**
     * @var string $baseDN
     */
    private $baseDN;

    /**
     * @var string $host
     */
    private $host;

    /**
     * LDAPHelper constructor.
     *
     * @param string $host
     * @param string $baseDN
     */
    public function __construct(
        $host,
        $baseDN
    ) {
        $this->ldap = ldap_connect($host) or
        error_log("LDAP connect failed!\n", 3, "/var/tmp/my-errors.log");

        $this->baseDN = $baseDN;
        $this->host = $host;

        if ($this->ldap) {
            ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        }
    }

    /**
     * LDAPHelper destructor.
     */
    public function __destruct()
    {
        // unbind the ldap
        if ($this->ldap)
            ldap_unbind($this->ldap);
        if ($this->adminLdap)
            ldap_unbind($this->adminLdap);
    }

    /**
     * Auth the admin
     */
    public function authAdmin()
    {
        error_log("Try to auth the admin.\n", 3, "/var/tmp/my-errors.log");

        $this->adminLdap = ldap_connect($this->host) or
        error_log("LDAP connect failed!\n", 3, "/var/tmp/my-errors.log");

        if ($this->adminLdap) {
            ldap_set_option($this->adminLdap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->adminLdap, LDAP_OPT_REFERRALS, 0);
        }

        @ldap_bind($this->adminLdap, "cn=admin,{$this->baseDN}", "admin") or
        error_log("Failed to auth the admin.\n", 3, "/var/tmp/my-errors.log");
    }

    /**
     * Try to authenticate the user $username
     * with the password $clearPassword
     * over the linked LDAP of the project.
     *
     * Return a empty array on success
     * and a mapped array on error
     *
     * @param string      $dn
     * @param string      $username
     * @param string      $password
     * @param string|null $extraGroup
     *
     * @return array
     */
    public function authUserOverLDAP($dn, $username, $password, $extraGroup = null)
    {
        if (!$this->ldap)
            return ["logged" => false, "reason" => "Invalid LDAP connection."];

        error_log("Your ldap connection seem fine!\n", 3, "/var/tmp/my-errors.log");

        if (!($username and $password))
            return ["logged" => false, "reason" => "User or password not feed."];

        $bindRdn = $extraGroup ? "{$username},{$extraGroup},{$dn}" : "{$username},{$dn}";

        // try to bind the ldap with the user/pass
        if (!($bind = @ldap_bind($this->ldap, $bindRdn, $password)))
            return ["logged" => false, "reason" => "Invalid dn / password combination."];

        // user successfully auth over ldap
        return [ "logged" => true,
                 "reason" => "User successfully logged.",
                 "userGroups" => $this->getField($username, "objectclass")
        ];
    }

    /**
     * Recursively remove the count attribute from LDAP entries
     *
     * @param $entries
     *
     * @return mixed
     */
    function rCountRemover($entries)
    {
        foreach ($entries as $key => $val)
            if ($key === "count")
                unset($entries[$key]);
            elseif (is_array($val))
                $entries[$key] = $this->rCountRemover($entries[$key]);

        return $entries;
    }

    /**
     * This function search in the LDAP tree
     * entry specified by $user and returns its $content or
     * null
     *
     * @param string $user
     *
     * @return string|array|null
     */
    function get($user)
    {
        $this->authAdmin();

        $result = ldap_search($this->adminLdap, $this->baseDN, "({$user})", ["*"]);

        if ($result === false)
            return null;

        $entries = ldap_get_entries($this->adminLdap, $result);

        return ($entries['count'] > 0) ? $this->rCountRemover($entries) : null;
    }

    /**
     * @param string $user
     * @param string $field
     *
     * @return array|null
     */
    function getField($user, $field)
    {
        return (($userData = $this->get($user)) && (key_exists($field, $userData[0])))
            ? $userData[0][$field] : null;
    }
}
