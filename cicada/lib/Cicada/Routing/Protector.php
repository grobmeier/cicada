<?php
namespace Cicada\Routing;

use Cicada\Auth\UserProvider;

class Protector {
    private $route;
    private $users;
    private $roles;

    private $onFail;

    use Url;

    function __construct($route, UserProvider $userProvider) {
        $this->route = $route;
    }

    public function allowUsers($users) {
        $this->users = $users;
        return $this;
    }

    public function allowRoles($roles) {
        $this->roles = $roles;
        return $this;
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