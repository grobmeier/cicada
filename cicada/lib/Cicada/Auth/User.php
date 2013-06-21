<?php

namespace Cicada\Auth;

class User {
    private $username;
    private $password;
    private $roles;

    function __construct($password, $username, $roles = array()) {
        $this->password = $password;
        $this->username = $username;
        $this->roles = $roles;
    }

    public function addRole(Role $role) {
        array_push($this->roles, $role);
    }

    /**
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }

    public function hasRole(Role $role) {
        /** @var Role $userRole */
        foreach ($this->roles as $userRole) {
            if($role->getName() == $userRole->getName()) {
                return true;
            }
        };
        return false;
    }
}