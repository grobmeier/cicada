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

    public function getPassword() {
        return $this->password;
    }

    /**
     * checks if the user has a role, which is either
     * passed by Role object or string.
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role) {
        if ($role instanceof Role) {
            $roleName = $role->getName();
        } else {
            $roleName = $role;
        }

        /** @var Role $userRole */
        foreach ($this->roles as $userRole) {
            if($roleName == $userRole) {
                return true;
            }
        };
        return false;
    }
}