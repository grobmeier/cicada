<?php

namespace Cicada\Auth;

interface UserProvider {

    public function getUser($name);

    public function getUsersByRole(Role $role);
}