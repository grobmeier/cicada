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

class FileUserProvider implements UserProvider {
    private $users;
    private $roles;

    function __construct($rolesFile, $userFile) {
        $this->roles = include $rolesFile;
        $this->users = include $userFile;
    }

    public function getUser($name) {
        $users = $this->users;

        /** @var $user User */
        foreach ($users as $user) {
            if ($name == $user->getUsername()) {
                return $user;
            }
        }
        return null;
    }

    public function getUsersByRole(Role $role) {
        $result = array();
        $users = $this->users;
        /** @var $user User */
        foreach ($users as $user) {
            if ($user->hasRole($role)) {
               array_push($result, $user);
            }
        }
        return $result;
    }
}