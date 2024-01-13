<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Helpers;

use Dotenv\Dotenv;

trait LoadsEnvironmentVariables {

    public function loadEnvironmentVariables() {
        try {
            $dotenv = Dotenv::createMutable(__DIR__ . '/../../');
            $dotenv->load();
        } catch (\Exception $e) {
            echo "\nWARNING! Unable to load .env file due to Exception: \n";
            echo get_class($e);
            echo "\n" . $e->getMessage() . "\n";
            echo "Assuming that .env file is not used.\n";
        }
    }
}