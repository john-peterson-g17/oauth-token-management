<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Helpers;

use Dotenv\Dotenv;

trait LoadsEnvironmentVariables {

    public function loadEnvironmentVariables() {
        $dotenv = Dotenv::createMutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    }
}