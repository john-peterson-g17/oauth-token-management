<?php

namespace JohnPetersonG17\JwtAuthentication;

use JohnPetersonG17\JwtAuthentication\Config;
use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class TokenFactory
{
    private mixed $userId;
    private TokenType $type;
    private DateTime $expiresAt;
    private DateTime $createdAt;
    private array $payload;
    private string $encodedPayload;
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function fromValue(string $value): Token
    {
        $decodedToken = JWT::decode($value, new Key($this->config->key, $this->config->hashingAlgorithm->value));

        
    }

    public function make(mixed $userId, TokenType $type): Token
    {
        $this->userId = $userId;
        $this->type = $type;

        $this->createPayload();
        $this->encodeJwtPayload();
        return $this->createToken();
    }

    private function createPayload(): void
    {
        $this->calculateCreatedAt();
        $this->calculateexpiresAt();

        // Create the payload
        $this->payload = [
            'iss' => $this->config->issuer,
            'exp' => $this->expiresAt->getTimestamp(),
            'sub' => $this->userId,
            'jti' => Uuid::uuid4()->toString(),
            'iat' => $this->createdAt->getTimestamp(),
            // TODO: Any other useful claims here...
        ];
    }

    private function calculateCreatedAt(): void
    {
        $this->createdAt = new DateTimeImmutable('now', new DateTimeZone(DateTimeZone::UTC));
    }

    private function calculateExpiresAt(): void
    {
        $interval = null;
        if ($this->type == TokenType::ACCESS) {
            $interval = DateInterval::createFromDateString($this->config->accessTokenExpiration . ' seconds');
        } else { // Refresh Token
            $interval = DateInterval::createFromDateString($this->config->refreshTokenExpiration . ' seconds');
        }

        $this->expiresAt = $this->createdAt->add($interval);
    }

    private function encodeJwtPayload(): void
    {
        $this->encodedPayload = JWT::encode($this->payload, $this->config->key, $this->config->hashingAlgorithm->value);
    }

    private function createToken(): Token
    {
        return new Token(
            $this->userId,
            $this->type,
            $this->createdAt,
            $this->expiresAt,
            $this->encodedPayload,
            $this->config->key,
            $this->config->hashingAlgorithm
        );
    }
}