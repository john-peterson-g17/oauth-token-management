<?php

namespace JohnPetersonG17\JwtAuthentication\Token;

use DateTimeInterface;

class Token
{
    private mixed $id;
    private string $issuer;
    private mixed $subject;
    private TokenPurpose $purpose;
    private DateTimeInterface $issuedAt;
    private DateTimeInterface $expiresAt;


    public function __construct(
        mixed $id,
        string $issuer,
        mixed $subject,
        TokenPurpose $purpose,
        DateTimeInterface $issuedAt,
        DateTimeInterface $expiresAt
    )
    {
        $this->id = $id;
        $this->issuer = $issuer;
        $this->subject = $subject;
        $this->purpose = $purpose;
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
    }

    public function id(): mixed
    {
        return $this->id;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): mixed
    {
        return $this->subject;
    }

    public function purpose(): TokenPurpose
    {
        return $this->purpose;
    }

    public function expiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function issuedAt(): DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function toArray(): array
    {
        return [
            'jti' => $this->id,
            'iss' => $this->issuer,
            'sub' => $this->subject,
            'prp' => $this->purpose->value,
            'iat' => $this->issuedAt->getTimestamp(),
            'exp' => $this->expiresAt->getTimestamp(),
        ];
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() < time();
    }
}