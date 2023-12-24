<?php

namespace JohnPetersonG17\JwtAuthentication;

// A Grant is a set of tokens that are issued to a client
// It is a statement of successful authentication and access granted to the system
// Grants are Immutable
class Grant {

    private mixed $userId;
    private string $accessToken;
    private string $refreshToken;
    private int $expiresInSeconds;

    public function __construct(mixed $userId, string $accessToken, string $refreshToken, int $expiresInSeconds)
    {
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresInSeconds = $expiresInSeconds;
    }

    /**
     * Get the encoded access token for the grant
     * @return string
     */
    public function accessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Get the encoded refresh token for the grant
     * @return string
     */
    public function refreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Get the id of the user who was granted access
     * @return mixed
     */
    public function userId(): mixed
    {
        return $this->userId;
    }

    /**
     * Get the grant in array form
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresInSeconds,
        ];
    }

    /**
     * Get the grant in JSON form
     * @return string
     */
    public function _toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Get the grant in a stable form that can be used in API Responses
     * @return array
     */
    public function toResponseArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresInSeconds,
            'token_type' => 'Bearer',
        ];
    }
}