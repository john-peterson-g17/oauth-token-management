<?php

namespace JohnPetersonG17\OAuthTokenManagement;

// A Grant is a set of tokens that are issued to a client
// It is a statement of successful authentication and access granted to the system
// Grants are Immutable
class Grant {

    private mixed $userId;
    private string $accessToken;
    private string $refreshToken;
    private int $expiresIn; // The number of seconds that the access token in this grant expires in

    public function __construct(mixed $userId, string $accessToken, string $refreshToken, int $expiresIn)
    {
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
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
     * Get the number of seconds seconds that the access token in this grant expires in
     * @return int
     */
    public function expiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * Get the token type of the Grant for Oauth2 Responses
     * @return string
     */
    public function tokenType(): string
    {
        return 'Bearer';
    }

    private function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
        ];
    }

    /**
     * Get the grant in JSON string form
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}