<?php

namespace JohnPetersonG17\OAuthTokenManagement\Tests\Unit;

use JohnPetersonG17\OAuthTokenManagement\Token\TokenPurpose;
use JohnPetersonG17\OAuthTokenManagement\Token\Token;
use PHPUnit\Framework\TestCase;
use DateTime;

class TokenTest extends TestCase {

    // TODO: Test more granular times for expiration logic

    /**
     * @test
     */
    public function it_is_expired_if_the_current_time_is_greater_than_the_expiration_time()
    {
        $token = new Token(
            'someTokenId',
            'someIssuer',
            1,
            TokenPurpose::ACCESS,
            new DateTime(),
            new DateTime('yesterday'), // Expires yesterday
        );

        $this->assertTrue($token->isExpired());
    }

    /**
     * @test
     */
    public function it_is_not_expired_if_the_current_time_is_less_than_the_expiration_time()
    {
        $token = new Token(
            'someTokenId',
            'someIssuer',
            1,
            TokenPurpose::ACCESS,
            new DateTime(),
            new DateTime('tomorrow'), // Expires tomorrow
        );

        $this->assertFalse($token->isExpired());
    }

    /**
     * @test
     */
    public function it_can_be_represented_as_an_array_correctly()
    {
        $expiresAt = new DateTime();
        $issuedAt = new DateTime();

        $token = new Token(
            'someTokenId',
            'someIssuer',
            1,
            TokenPurpose::ACCESS,
            new DateTime(),
            new DateTime(),
        );

        $this->assertEqualsCanonicalizing([
            'jti' => 'someTokenId',
            'iss' => 'someIssuer',
            'sub' => 1,
            'prp' => TokenPurpose::ACCESS->value,
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ], $token->toArray());
    }
}