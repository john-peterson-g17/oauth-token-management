<?php

namespace JohnPetersonG17\JwtAuthentication;

// A Grant is a set of tokens that are issued to a client
// It is a statement of successful authentication and access granted to the system
class Grant {

    private string $accessToken;
    private string $refreshToken;
    private int $expiresInSeconds;

    public function __construct(string $accessToken, string $refreshToken, int $expiresInSeconds) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresInSeconds = $expiresInSeconds;
    }

    /**
     * An array representation of the grant which can be used in an HTTP Response
     * @return array
     */
    public function toArray(): array {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresInSeconds,
            'token_type' => 'Bearer'
        ];
    }

    public function _toString(): string {
        return json_encode($this->toArray());
    }
}