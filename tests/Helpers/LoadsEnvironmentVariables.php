<?php

namespace JohnPetersonG17\OAuthTokenManagement\Tests\Helpers;

use Dotenv\Dotenv;

trait LoadsEnvironmentVariables {

    public function loadEnvironmentVariables() {
        $dotenv = Dotenv::createMutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        // Populate environment variables into getenv() as well
        foreach ($_ENV as $key => $value) {
            putenv("$key=$value");
        }
    }
}