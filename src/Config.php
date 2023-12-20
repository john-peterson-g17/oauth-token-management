<?php

namespace JohnPetersonG17\JwtAuthentication;

class Config {
    public function __construct(
        public readonly string $issuer,
        public readonly string $key, 
        public readonly int $accessTokenExpiration = 3600, // Default 1 hour
        public readonly int $refreshTokenExpiration = 86400, // Default 1 day
        public readonly HashingAlgorithm $hashingAlgorithm = HashingAlgorithm::HS256,
        // TODO: Support other hashing algorithms
    ) {}
}