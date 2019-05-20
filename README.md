# mamaclub OAuth2 Client 

This package makes it simple to integrate your application with [OAuth 2.0](http://oauth.net/2/) service providers.

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](http://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-client/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-client/)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

---

We are all used to seeing those "Connect with Facebook/Google/etc." buttons around the internet, and social network integration is an important feature of most web applications these days. Many of these sites use an authentication and authorization standard called OAuth 2.0 ([RFC 6749](http://tools.ietf.org/html/rfc6749)).

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights, please send a patch via pull request. If you're interesting in contributing to this library, please take a look at our [contributing guidelines](CONTRIBUTING.md).

## Requirements

The following versions of PHP are supported.

* PHP 5.6
* PHP 7.0
* PHP 7.1
* PHP 7.2
* PHP 7.3

## Installation

The Facebook PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require mamaclub/oauth2
```


## Usage

Simple GET example of a user's profile.

```php
$mamaclub = new MamaclubOAuth([
    'clientId' => 'your client id',
    'clientSecret' => 'your client secret',
    'redirectUri' => 'your redirect uri'
]);


// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $mamaclub->getAuthorizationUrl(['state' => 'mmc_login']);

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $mamaclub->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $mamaclub->getResourceOwner($accessToken);


    } catch (\Mamaclub\Exception\Exception $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}
```

### Refreshing a Token

```php
$newAccessToken = $mamaclub->getAccessToken('refresh_token', [
    'refresh_token' => $accessToken->getRefreshToken()
]);
```

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md