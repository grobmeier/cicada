<?php

namespace Cicada;

abstract class Action {
    const SUCCESS = "success";
    const ERROR = "error";
    const LOGIN = "login";

    public abstract function execute();
}