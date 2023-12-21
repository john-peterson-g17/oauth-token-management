<?php

namespace JohnPetersonG17\JwtAuthentication;

use DateTimeInterface;

class Token
{
    private TokenType $type;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $expiresAt;
    private string $value; // The encoded payload of the token

    public function __construct(
        TokenType $type,
        DateTimeInterface $createdAt,
        DateTimeInterface $expiresAt,
        string $value,
    )
    {
        $this->type = $type;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->value = $value;
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

    public function value(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return  [
            'type' => $this->type->value,
            'expires_at' => $this->expiresAt->getTimestamp(),
            'created_at' => $this->createdAt->getTimestamp(),
            'value' => $this->value,
        ];
    }

    public function _toString(): string
    {
        return json_encode($this->toArray());
    }
}