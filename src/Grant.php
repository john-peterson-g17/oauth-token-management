<?php

namespace JohnPetersonG17\JwtAuthentication;

// A Grant is a set of tokens that are issued to a client
// It is a statement of successful authentication and access granted to the system
class Grant {

    private mixed $userId;
    private Token $accessToken;
    private Token $refreshToken;

    public function __construct(mixed $userId, Token $accessToken, Token $refreshToken) {
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function userId(): mixed
    {
        return $this->userId;
    }

    // TODO: Not sure if these methods are needed
    // public function accessToken(): Token {
    //     return $this->accessToken;
    // }

    // public function refreshToken(): Token {
    //     return $this->refreshToken;
    // }

    /**
     * An array representation of the grant which can be used in an HTTP Response
     * @return array
     */
    public function toArray(): array {
        return [
            'access_token' => $this->accessToken->value(),
            'refresh_token' => $this->refreshToken->value(),
            'expires_in' => $this->accessToken->expiresAt()->getTimestamp() - time(),
            'token_type' => 'Bearer'
        ];
    }

    // TODO: Another array representation for persistance that keeps all the token meta data
    // $token->toArray()

    public function _toString(): string {
        return json_encode($this->toArray());
    }
}