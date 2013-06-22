<?php
namespace Cicada\Auth;

use Cicada\Action;
use Cicada\Session;

class LogoutAction extends Action {
    function __construct() {
    }

    public function execute() {
        Session::getInstance()->destroy();
        return self::SUCCESS;
    }
}