<?php

namespace JohnPetersonG17\JwtAuthentication\Codecs;

use JohnPetersonG17\JwtAuthentication\HashingAlgorithm;
use JohnPetersonG17\JwtAuthentication\Token\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FirebaseJWTCodec implements CodecInterface
{
    private string $key;
    private HashingAlgorithm $hashingAlgorithm;

    public function __construct(string $key, HashingAlgorithm $hashingAlgorithm)
    {
        $this->key = $key;
        $this->hashingAlgorithm = $hashingAlgorithm;
    }

    public function encode(Token $token): string
    {
        return JWT::encode($token->toArray(), $this->key, $this->hashingAlgorithm->value);
    }

    public function decode(string $value): array
    {
        return (array) JWT::decode($value, new Key($this->key, $this->hashingAlgorithm->value));
    }
}