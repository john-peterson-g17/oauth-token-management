<?php

namespace JohnPetersonG17\JwtAuthentication;

use JohnPetersonG17\JwtAuthentication\HashingAlgorithm;
use JohnPetersonG17\JwtAuthentication\Persistance\Driver;

class Config {

    public const DEFAULT_ISSUER = 'http://localhost.com';
    public const DEFAULT_KEY = 'secret';
    public const DEFAULT_ACCESS_TOKEN_EXPIRATION = 3600; // 1 hour
    public const DEFAULT_REFRESH_TOKEN_EXPIRATION = 86400; // 1 day
    public const DEFAULT_HASHING_ALGORITHM = HashingAlgorithm::HS256;
    public const DEFAULT_PERSISTANCE_DRIVER = Driver::None;
    public const DEFAULT_REDIS_PARAMETERS = [];
    public const DEFAULT_REDIS_OPTIONS = [];

    private array $values;

    public function __construct(array $values = [])
    {
        $this->validateValues($values);
        $this->setValuesAndDefaults($values);
    }

    /**
     * Validates any values that have been set in the incoming array
     * @param array $values
     * @throws \InvalidArgumentException
     */
    private function validateValues(array $values): void
    {
        if (isset($values['issuer'])) {
            $this->validateIssuer($values['issuer']);
        }

        if (isset($values['key'])) {
            $this->validateKey($values['key']);
        }

        if (isset($values['access_token_expiration'])) {
            $this->validateAccessTokenExpiration($values['access_token_expiration']);
        }

        if (isset($values['refresh_token_expiration'])) {
            $this->validateRefreshTokenExpiration($values['refresh_token_expiration']);
        }

        if (isset($values['hashing_algorithm'])) {
            $this->validateHashingAlgorithm($values['hashing_algorithm']);
        }

        if (isset($values['persistance_driver'])) {
            $this->validatePersistanceDriver($values['persistance_driver']);
        }
    }

    private function validateIssuer(mixed $issuer): void
    {            
        if (empty($issuer)) {
            throw new \InvalidArgumentException('Issuer cannot be empty');
        }

        if (!is_string($issuer)) {
            throw new \InvalidArgumentException('Issuer must be a string');
        }
    }

    private function validateKey(mixed $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key cannot be empty');
        }

        if (!is_string($key)) {
            throw new \InvalidArgumentException('Key must be a string');
        }
    }

    private function validateAccessTokenExpiration(mixed $accessTokenExpiration): void
    {
        if (!is_int($accessTokenExpiration)) {
            throw new \InvalidArgumentException('Access token expiration must be an integer (Seconds)');
        }

        if ($accessTokenExpiration <= 0) {
            throw new \InvalidArgumentException('Access token expiration must be greater than 0');
        }
    }

    private function validateRefreshTokenExpiration(mixed $refreshTokenExpiration): void
    {
        if (!is_int($refreshTokenExpiration)) {
            throw new \InvalidArgumentException('Refresh token expiration must be an integer (Seconds)');
        }

        if ($refreshTokenExpiration <= 0) {
            throw new \InvalidArgumentException('Refresh token expiration must be greater than 0');
        }
    }

    private function validateHashingAlgorithm(mixed $hashingAlgorithm): void
    {
        if($hashingAlgorithm instanceof HashingAlgorithm) {
            return;
        }

        $message = "Hashing Algorithm is invalid or unsupported. The value must be a valid hashing algorithm from the " . HashingAlgorithm::class . " enum";

        if (!is_string($hashingAlgorithm)) {
            throw new \InvalidArgumentException($message);
        }

        if (empty($hashingAlgorithm)) {
            throw new \InvalidArgumentException($message);
        }

        if (HashingAlgorithm::tryFrom($hashingAlgorithm) == null) { // Returns null if not found in enum class
            throw new \InvalidArgumentException($message);
        }
    }

    private function validatePersistanceDriver(mixed $driver): void
    {
        if($driver instanceof Driver) {
            return;
        }

        $message = "Persistance Driver is invalid or unsupported. The value must be a valid driver from the " . Driver::class . " enum";

        if (!is_string($driver)) {
            throw new \InvalidArgumentException($message);
        }

        if (empty($driver)) {
            throw new \InvalidArgumentException($message);
        }

        if (Driver::tryFrom($driver) == null) { // Returns null if not found in enum class
            throw new \InvalidArgumentException($message);
        }
    }

    private function setValuesAndDefaults(array $values): void
    {
        $this->values = [
            'issuer' => $values['issuer'] ?? self::DEFAULT_ISSUER,
            'key' => $values['key'] ?? self::DEFAULT_KEY,
            'access_token_expiration' => $values['access_token_expiration'] ?? self::DEFAULT_ACCESS_TOKEN_EXPIRATION,
            'refresh_token_expiration' => $values['refresh_token_expiration'] ?? self::DEFAULT_REFRESH_TOKEN_EXPIRATION,
            'hashing_algorithm' => $this->getHashingAlgorithm($values['hashing_algorithm'] ?? null),
            'persistance_driver' => $this->getPersistanceDriver($values['persistance_driver'] ?? null)
        ];

        // Only set default redis parameters and options if the persistance driver is redis
        if ($this->values['persistance_driver'] == Driver::Redis) {
            $this->values['redis']['parameters'] = $values['redis']['parameters'] ?? self::DEFAULT_REDIS_PARAMETERS;
            $this->values['redis']['options'] = $values['redis']['options'] ?? self::DEFAULT_REDIS_OPTIONS;
        }
    }

    private function getHashingAlgorithm(mixed $hashingAlgorithm): HashingAlgorithm
    {
        if (!isset($hashingAlgorithm)) {
            return self::DEFAULT_HASHING_ALGORITHM;
        }

        if($hashingAlgorithm instanceof HashingAlgorithm) {
            return $hashingAlgorithm;
        }

        return HashingAlgorithm::from($hashingAlgorithm);
    }

    private function getPersistanceDriver(mixed $driver): Driver
    {
        if (!isset($driver)) {
            return self::DEFAULT_PERSISTANCE_DRIVER;
        }

        if($driver instanceof Driver) {
            return $driver;
        }

        return Driver::from($driver);
    }

    /**
     * Get a config value by name
     * @param string $name
     * @throws NotFoundException
     * @return mixed
     */
    public function get(string $name): mixed
    {
        // Parse dot notation
        if(str_contains($name, '.')) {
            return $this->parseValueFromDotNotation($name);
        }

        // String does not have dot notation
        if (!isset($this->values[$name])) {
            throw new NotFoundException("Config value $name not found");
        }

        return $this->values[$name];
    }

    private function parseValueFromDotNotation(string $name): mixed
    {
        $parts = explode('.', $name);

        foreach ($parts as $part) {
            if (!isset($this->values[$part])) {
                throw new NotFoundException("Config value $name not found");
            }

            $this->values[$part];
        }

        return $value;
    }

    /**
     * Check if a config value exists by name
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->values[$name]);
    }
}