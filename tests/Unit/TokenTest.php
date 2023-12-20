<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Unit;

use JohnPetersonG17\JwtAuthentication\TokenType;
use JohnPetersonG17\JwtAuthentication\Token;
use PHPUnit\Framework\TestCase;
use DateTime;

class TokenTest extends TestCase {

    /**
     * @test
     */
    public function it_can_create_a_token()
    {
        $token = new Token(
            new DateTime(),
            new DateTime(),
            'encoded_jwt',
            TokenType::ACCESS_TOKEN
        );
    }
}