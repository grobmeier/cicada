<?php
namespace Cicada\Routing;

use Cicada\Auth\User;
use Cicada\Auth\UserProvider;

class Protector {
    private $route;
    private $userProvider;
    private $users;
    private $roles;

    private $onFail;

    use Url;

    function __construct($route, UserProvider $userProvider) {
        $this->route = $route;
        $this->userProvider = $userProvider;
    }

    public function allowUsers($users) {
        $this->users = $users;
        return $this;
    }

    public function allowRoles($roles) {
        $this->roles = $roles;
        return $this;
    }

    public function isUserAllowed($user) {
        if (!($user instanceof User)) {
            $user = $this->userProvider->getUser($user);
        }

        /** @var User $allowedUser */
        foreach ($this->users as $allowedUser) {
            if ($allowedUser == $user->getUsername()) {
                return true;
            }
        }

        foreach ($this->roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function setOnFail($path) {
        $this->onFail = $path;
    }

    /**
     * @return mixed
     */
    public function getOnFail() {
        return $this->onFail;
    }

    public function getRoute() {
        return $this->route;
    }

    public function setMatches($matches) {
        // matches currently not used
    }
}