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

use Cicada\Action;
use Cicada\Session;

class LoginAction extends Action {
    const CICADA_USER = 'cicada.user';

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
            Session::getInstance()->add(self::CICADA_USER, $user);
            return self::SUCCESS;
        }
        return self::ERROR;
    }

    public function setUserProvider(UserProvider $userProvider) {
        $this->userProvider = $userProvider;
    }
}