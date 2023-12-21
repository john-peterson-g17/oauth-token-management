<?php

// Configure Auth Facade

use JohnPetersonG17\JwtAuthentication\Config;

$config = new Config();

$manager = new TokenManager($config);

// Flow 1 - User logs in successfully, grant refresh and access tokens
// https://www.oauth.com/oauth2-servers/access-tokens/access-token-response/

$userId = 1;

// TODO: Better name for this DTO
$tokens = $manager->grant($userId);

$tokens->access(); // Access Token
$tokens->refresh(); // Refresh Token

// Flow 2 - User refreshes access token
// https://www.oauth.com/oauth2-servers/access-tokens/refreshing-access-tokens/

$refreshToken = "soi3enfoinls";

// Throws TokenNotFoundException if a token is not found
// Throws TokenExpiredException if token is expired
$accessToken = $manager->refresh($refreshToken);

// Flow 3 - User logs out, revoke refresh and access tokens
// https://www.oauth.com/oauth2-servers/access-tokens/revoking-access-tokens/

$userId = 1;

$manager->revoke($userId);