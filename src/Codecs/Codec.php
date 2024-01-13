<?php

namespace JohnPetersonG17\OAuthTokenManagement\Codecs;

use JohnPetersonG17\OAuthTokenManagement\Token\Token;

/**
 * A Codec is responsible for encoding and decoding a Token
 */
interface Codec {

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