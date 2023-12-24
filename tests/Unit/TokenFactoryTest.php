<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Unit;

use JohnPetersonG17\JwtAuthentication\TokenPurpose;
use JohnPetersonG17\JwtAuthentication\Token;
use PHPUnit\Framework\TestCase;
use DateTime;
use JohnPetersonG17\JwtAuthentication\TokenFactory;
use JohnPetersonG17\JwtAuthentication\Config;

class TokenFactoryTest extends TestCase {

    private TokenFactory $tokenFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Config(
            'http://localhost.com',
            'someKey',
        );

        $this->tokenFactory = new TokenFactory($config);
    }

    /**
     * @test
     */
    public function it_can_make_a_token()
    {
        $token = $this->tokenFactory->make(1, TokenPurpose::ACCESS);

        $this->assertInstanceOf(Token::class, $token);
    }

    // /**
    //  * @test
    //  * @dataProvider TokenPurposeProvider
    //  */
    // public function it_makes_a_token_of_the_correct_purpose(TokenPurpose $expected)
    // {
    //     $token = $this->tokenFactory->make(1, $expected);

    //     $this->assertEquals($expected, $token->purpose());
    // }

    // public static function TokenPurposeProvider(): array
    // {
    //     return [
    //         'access_token' => [
    //             'expected' => TokenPurpose::ACCESS
    //         ],
    //         'refresh_token' => [
    //             'expected' => TokenPurpose::REFRESH
    //         ],
    //     ];
    // }

    /**
     * @test
     */
    public function it_can_decode_a_token_after_creation()
    {
        $token = $this->tokenFactory->make(1, TokenPurpose::ACCESS);

        $decodedToken = $this->tokenFactory->decode($token->value());

        // $this->assertEquals(1, $decodedToken->sub);
        // $this->assertEquals(1, $decodedToken->sub);
    }


}