<?php
/*
 *  Copyright 2013 Christian Grobmeier
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
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