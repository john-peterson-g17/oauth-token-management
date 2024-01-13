<?php

namespace JohnPetersonG17\OAuthTokenManagement\Codecs;

use Firebase\JWT\ExpiredException;
use JohnPetersonG17\OAuthTokenManagement\HashingAlgorithm;
use JohnPetersonG17\OAuthTokenManagement\Token\Token;
use JohnPetersonG17\OAuthTokenManagement\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FirebaseJWTCodec implements Codec
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
        // Headers are automatically added to encoded tokens by the Firebase JWT library
        return JWT::encode($token->toArray(), $this->key, $this->hashingAlgorithm->value);
    }

    public function decode(string $value): array
    {
        try {
            return (array) JWT::decode($value, new Key($this->key, $this->hashingAlgorithm->value));
        } catch (ExpiredException $e) {
            throw new TokenExpiredException('Token is expired'); // Translate to our exception
        
        }
    }
}