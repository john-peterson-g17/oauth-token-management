<?php

namespace JohnPetersonG17\JwtAuthentication\Codecs;

use JohnPetersonG17\JwtAuthentication\Token;

interface CodecInterface {

    /**
     * Encodes a Token into a JWT string
     * @param Token $token
     * @return string
     */
    public function encode(Token $token): string;

    /**
     * Decodes a JWT string into an raw array
     * @param string $value
     * @return array
     */
    public function decode(string $value): array;
}