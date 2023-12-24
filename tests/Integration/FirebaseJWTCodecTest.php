<?php

namespace JohnPetersonG17\JwtAuthentication\Tests\Unit;

use JohnPetersonG17\JwtAuthentication\Codecs\FirebaseJWTCodec;
use JohnPetersonG17\JwtAuthentication\Token\TokenPurpose;
use JohnPetersonG17\JwtAuthentication\Token\Token;
use JohnPetersonG17\JwtAuthentication\HashingAlgorithm;
use PHPUnit\Framework\TestCase;
use DateTime;

class FirebaseJWTCodecTest extends TestCase {

    private FirebaseJWTCodec $codec;
    private Token $token;
    private string $key = 'someKey';

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new FirebaseJWTCodec($this->key, HashingAlgorithm::HS256);
        $this->token = new Token(
            'someTokenId',
            'someIssuer',
            1,
            TokenPurpose::ACCESS,
            new DateTime(),
            new DateTime('tomorrow'),
        );
    }

    /**
     * @test
     */
    public function it_can_encode_a_token()
    {
        $encodedToken = $this->codec->encode($this->token);
        
        $this->assertIsString($encodedToken);
    }

    /**
     * @test
     */
    public function it_can_decode_a_token()
    {
        $encodedToken = $this->codec->encode($this->token);
        $decodedToken = $this->codec->decode($encodedToken);

        $this->assertIsArray($decodedToken);
        $this->assertEquals($this->token->id(), $decodedToken['jti']);
        $this->assertEquals($this->token->issuer(), $decodedToken['iss']);
        $this->assertEquals($this->token->subject(), $decodedToken['sub']);
        $this->assertEquals($this->token->purpose()->value, $decodedToken['prp']);
        $this->assertEquals($this->token->issuedAt()->getTimestamp(), $decodedToken['iat']);
        $this->assertEquals($this->token->expiresAt()->getTimestamp(), $decodedToken['exp']);
    }
}