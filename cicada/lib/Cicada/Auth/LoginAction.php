<?php
namespace Cicada\Auth;

use Cicada\Action;

class LoginAction extends Action {

    private $username;
    private $password;
    /** @var $userProvider UserProvider */
    private $userProvider;

    function __construct($username, $password) {
        $this->password = $password;
        $this->username = $username;
    }

    public function execute() {
        $user = $this->userProvider->getUser($this->username);
        if($user == null) {
            return self::LOGIN;
        }

        if ($this->password == $user->getPassword()) {
            return self::SUCCESS;
        }
        return self::ERROR;
    }

    public function setUserProvider(UserProvider $userProvider) {
        $this->userProvider = $userProvider;
    }
}