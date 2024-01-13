# OAuth 2.0 Token Management

This project seeks to be an unbiased framework agnostic module for managing tokens in the OAuth 2.0 per [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749). More specifically if you were creating an Authorization Server this package would be the module within that Authorization Server responsible for:

1. Issueing Tokens
2. Revoking Tokens
3. Refreshing Access Tokens
4. etc..

Reference the definition of the Authorization Server in OAuth 2.0 as defined in [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749#section-1.1)

## Concepts

### Authorization Gate

The class through which most of your interaction with this package will ocurr. It is responsible for issuing tokens (via Grant), authorizing access, refreshing access tokens, etc.. Think of it as the core module within your OAuth2 Authorization Server.

### Grant

A Grant is set of tokens that are issued to a client along with some other meta data. It is a statement of successful authentication and access granted to the system. Grants are immutable.

You can retrieve the data for a grant via various methods.

```php
$grant->userId(); // <-- The ID of the user that the grant was issued for (access was granted for)
$grant->accessToken(); // <-- The access token associated with this grant
$grant->refreshToken(); // <-- The refresh token associated with this grant
$grant->expiresIn(); // <-- The number of seconds until the access token associated with this grant expires
$grant->tokenType(); // <-- The token type for the grant. "Bearer" as stated in RFC 6750
```

The idea is that you can use the data from the Grant object to construct your "success" API response to your client as shown in [RFC 6749 Section 5.1](https://datatracker.ietf.org/doc/html/rfc6749#section-5.1)

Reference: 
- [Grant](./src/Grant.php)
- [RFC 6750](https://datatracker.ietf.org/doc/html/rfc6750)
- [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749)

## Installation

To install simply require the package via the composer command:
```bash
composer require @john-peterson-g17/jwt-authentication
```

## Setup

The first step to using the package is to setup the Authorization Gate (which is the module responsible for issuing, revoking, authorizing, and refreshing tokens etc..). This can be done by defining the configuration and creating the AuthorizationGate object with that configuration

```php
use JohnPetersonG17\OAuthTokenManagement\HashingAlgorithm;
use JohnPetersonG17\OAuthTokenManagement\Persistance\Driver;
use JohnPetersonG17\OAuthTokenManagement\Config;
use JohnPetersonG17\OAuthTokenManagement\AuthorizationGate;

// Expects an array of key value objects for configuring the authorization gate. Default values are provided if none are passed in via the array.
$config = new Config(
    [
        'issuer' => 'https://myserver.com',
        'key' => 'someSuperSecretKey1234',
        'hashing_algorithm' => HashingAlgorithm::HS256
        'access_token_expiration' => 30,
        'refresh_token_expiration' => 60,
        'persistance_driver' => Driver::None
    ]
); 

$gate = new AuthorizationGate($config); // <-- Pass the configuration when creating the AuthorizationGate
```

In the case that invalid types are passed or invalid values for the configuration options then an `\InvalidArgumentException` will be thrown.

```php
$config = new Config(
    [
        'persistance_driver' => 'some_incorrect_driver_type' 
    ]
); // <-- Throws \InvalidArgumentException
```

### Configuration Options

There are many options available for configuring your authorization gate. The list of available configuration options is given in the table below.

| Configuration Option | Description | Type | Default |
|--|--|--|--|
| `issuer` | The URL of the service that issued the token. This will be placed in the `iss` claim of any tokens created by this Authorization Gate. | string | `http://localhost.com` |
| `key` | The key used when hashing the token during token creation | string | `secret` |
| `access_token_expiration` | The amount of time in seconds that access tokens should be set to expire | int | 3600 |
| `refresh_token_expiration` | The amount of time in seconds that refresh tokens should be set to expire | int | 86400 |
| `hashing_algorithm` | The hashing algorithm to use when creating a new token | \JohnPetersonG17\OAuthTokenManagement\HashingAlgorithm | HashingAlgorithm::HS256 |
| `persistance_driver` | The persistance driver to use when storing the tokens | \JohnPetersonG17\OAuthTokenManagement\Persistance\Driver | Driver::None |

> **Info:** Remember that configuration options are expected to be given as an array of key value pairs using the key listed in the table above

### Persistance

By default there is no persistance driver used. This is useful for cases where you want to handle how your tokens are persisted and want the package to take care of only generating the tokens.

> **Warning:** Many functions of the authorization gate will throw an `\JohnPetersonG17\OAuthTokenManagement\Exceptions\PersistanceDriverNotSetException` if no persistance driver is set. Example: You cannot retrieve a token if it is not persisted anywhere. 

If you want to let the package have the responsability of persisting tokens to a data store then you can set one of the available persistence drivers as shown below.

#### Redis Persistance Driver

You can set the package to use redis to persist your tokens by setting the redis persistance driver in the configuration. You may then pass an additional key `redis` with an array of options for configuring connection to a redis server.

Under the hood, the predis client is used for connection/communication with redis so any options passed inside the `redis` key will be passed directly to predis. Thus all predis configuration options are supported.

Predis Reference: https://github.com/predis/predis

```php
use JohnPetersonG17\OAuthTokenManagement\Persistance\Driver;
use JohnPetersonG17\OAuthTokenManagement\Config;

$config = new Config(
    [
        'persistance_driver' => Driver::Redis,
        'redis' => [ // <-- Any options supported by predis can be passed in this array to configure the underlying predis client
            'parameters' => [
                'host' => $this->host,
                'port' => $this->port,
            ]
        ]
    ]
);
```

## Usage

Once you have configured and created an Authentication Gate you can then call all the avaialble function on the gate to create and check tokens.

> **Warning:** Many functions of the authorization gate will throw an `\JohnPetersonG17\OAuthTokenManagement\Exceptions\PersistanceDriverNotSetException` if no persistance driver is set. Example: You cannot retrieve a token if it is not persisted anywhere.

### Granting Access to a User (Issuing a Grant)

Use this method to grant a user an access and refresh token.

```php
$userId = 1234;

// ... Your application code authenticating the user

$grant = $gate->grant($userId); // <-- Authentication successful so lets grant the user some tokens via this method

// ... Your application code sharing the tokens/grant with the client (API Response, etc...)
```

The tokens are returned in a `Grant` object which holds the tokens and other information about the grant to the user.

### Authorizing a User (Validating the Access Token For a User)

Use this method to verify a users access token is valid and they can access the system.

```php
$accessToken = $grant->accessToken(); // <-- The user must have been issued a grant with an access token previously.

$gate->authorize($accessToken); // <-- Authentication successful so lets grant the user some tokens via this method

// ... Your application code now that the user is authorized access
```

In the case that the access token is not valid or is expired then an exception will be thrown.

```php
$accessToken = $grant->accessToken(); // <-- The user must have been issued a grant with an access token previously.

try {
    $gate->authorize($accessToken);
} catch (\JohnPetersonG17\OAuthTokenManagement\Exceptions\TokenExpiredException) {
    // ... Your application code informing the client that the access token has expired
} catch (\JohnPetersonG17\OAuthTokenManagement\Exceptions\NotFoundException) {
    // ... Handle the case where the token does not exist or cannot be found
}
```

### Revoking a Users Tokens (User Logout)

Use this method to revoke a users tokens/grant. Usually this would be done when a user explicitly logs out of the system.

```php
$userId = 1234;

// ... Your application code logging out the user

$grant = $gate->revoke($userId);
```

### Refresh a Users Access Token

Use this method to refresh a users access token, this allows a user to "stay logged in" to your system until the refresh token expires.

```php
$refreshToken = $grant->refreshToken(); // <-- The user must have been issued a grant with an refresh token previously.

$grant = $gate->refresh($refreshToken); // <-- A new grant is issued with a new access token. The refresh token is the same
```

### Retrieve a Users Tokens

This method is more for quality if life in the case that you need to get the existing tokens/grant for a user.

```php
$userId = 1234;

$grant = $gate->retrieve($userId); // <-- Throws a \JohnPetersonG17\OAuthTokenManagement\Exceptions\NotFoundException if a grant does not exist for the user
```

# Contributing

Reference our [CONTRIBUTING](./CONTRIBUTING) document for instructions on how to contribute to this project.

Everyone is welcome to contribute!
