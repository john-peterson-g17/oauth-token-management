<?php

namespace JohnPetersonG17\JwtAuthentication;

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use DateTimeInterface;

class Token
{
    private mixed $userId;
    private TokenType $type;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $expiresAt;
    private string $encodedPayload;
    private string $key;
    private HashingAlgorithm $hashingAlgorithm;

    public function __construct(
        mixed $userId,
        TokenType $type,
        DateTimeInterface $createdAt,
        DateTimeInterface $expiresAt,
        string $encodedPayload,
        string $key,
        HashingAlgorithm $hashingAlgorithm = HashingAlgorithm::HS256
        // TODO: Support other hashing algorithms
    )
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->encodedPayload = $encodedPayload;
        $this->key = $key;
        $this->hashingAlgorithm = $hashingAlgorithm;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() < time();
    }

    public function type(): TokenType
    {
        return $this->type;
    }

    public function expiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function createdAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function userId(): mixed
    {
        return $this->userId;
    }

    public function decodePayload(): array
    {
        return (array) JWT::decode($this->encodedPayload, new Key($this->key, $this->hashingAlgorithm->value));
    }

    // public function toArray(): array
    // {
    //     return  [
    //         'expires' => $this->expires->timestamp,
    //         'created_at' => $this->createdAt->timestamp,
    //         'encoded_jwt' => $this->encodedJwt,
    //     ];
    // }

    // public function expires(): DateTimeInterface
    // {
    //     return $this->expires;
    // }

    // public function type(): TokenType;
}