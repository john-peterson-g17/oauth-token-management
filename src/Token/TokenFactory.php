<?php

namespace JohnPetersonG17\JwtAuthentication\Token;

use JohnPetersonG17\JwtAuthentication\Config;
use Ramsey\Uuid\Uuid;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class TokenFactory
{
    private mixed $userId;
    private TokenPurpose $purpose;
    private DateTimeImmutable $expiresAt;
    private DateTimeImmutable $issuedAt;
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function make(mixed $userId, TokenPurpose $purpose): Token
    {
        $this->userId = $userId;
        $this->purpose = $purpose;

        $this->calculateissuedAt();
        $this->calculateExpiresAt();

        return $this->createToken();
    }

    private function calculateIssuedAt(): void
    {
        $this->issuedAt = new DateTimeImmutable('now', new DateTimeZone('utc'));
    }

    private function calculateExpiresAt(): void
    {
        $interval = null;
        if ($this->purpose == TokenPurpose::ACCESS) {
            $interval = DateInterval::createFromDateString($this->config->accessTokenExpiration . ' seconds');
        } else { // Refresh Token
            $interval = DateInterval::createFromDateString($this->config->refreshTokenExpiration . ' seconds');
        }

        $this->expiresAt = $this->issuedAt->add($interval);
    }

    private function createToken(): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            $this->config->issuer,
            $this->userId,
            $this->purpose,
            $this->issuedAt,
            $this->expiresAt
        );
    }

    public function makeFromArray(array $data): Token
    {
        return new Token(
            $data['jti'],
            $data['iss'],
            $data['sub'],
            TokenPurpose::from($data['prp']),
            new DateTimeImmutable($data['iat']),
            new DateTimeImmutable($data['exp'])
        );
    }
}