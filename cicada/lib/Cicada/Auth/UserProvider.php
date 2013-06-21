<?php

namespace Cicada\Auth;

interface UserProvider {

    /**
     * @param $name the name to search for
     * @return User the user
     */
    public function getUser($name);

    public function getUsersByRole(Role $role);
}