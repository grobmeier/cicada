<?php

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