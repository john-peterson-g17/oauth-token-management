<?php

namespace JohnPetersonG17\JwtAuthentication;

use JohnPetersonG17\JwtAuthentication\HashingAlgorithm;

class Config {

    public const DEFAULT_ISSUER = 'http://localhost.com';
    public const DEFAULT_KEY = 'secret';
    public const DEFAULT_ACCESS_TOKEN_EXPIRATION = 3600; // 1 hour
    public const DEFAULT_REFRESH_TOKEN_EXPIRATION = 86400; // 1 day
    public const DEFAULT_HASHING_ALGORITHM = HashingAlgorithm::HS256;

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

        if (HashingAlgorithm::tryFrom($hashingAlgorithm) == null) { // Returns null if not found in enum class
            throw new \InvalidArgumentException("Hashing Algorithm is invalid or unsupported. The value must be a valid hashing algorithm from the " . HashingAlgorithm::class . " enum");
        }
    }

    private function setValuesAndDefaults(array $values): void
    {
        $this->values = [
            'issuer' => $values['issuer'] ?? self::DEFAULT_ISSUER,
            'key' => $values['key'] ?? self::DEFAULT_KEY,
            'access_token_expiration' => $values['access_token_expiration'] ?? self::DEFAULT_ACCESS_TOKEN_EXPIRATION,
            'refresh_token_expiration' => $values['refresh_token_expiration'] ?? self::DEFAULT_REFRESH_TOKEN_EXPIRATION,
            'hashing_algorithm' => $this->getHashingAlgorithm($values['hashing_algorithm'] ?? null)
        ];
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

    public function get(string $name): mixed
    {
        if (!isset($this->values[$name])) {
            throw new NotFoundException("Config value $name not found");
        }

        return $this->values[$name];
    }
}