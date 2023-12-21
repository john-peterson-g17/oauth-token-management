<?php

namespace JohnPetersonG17\JwtAuthentication;

class Tokens {

    public function __construct(
        public readonly Token $accessToken,
        public readonly Token $refreshToken
    ) {}
}