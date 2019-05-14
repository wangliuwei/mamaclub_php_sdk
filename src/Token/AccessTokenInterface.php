<?php

namespace Mamaclub\Token;

use JsonSerializable;
use RuntimeException;

interface AccessTokenInterface extends JsonSerializable
{
    /**
     * Returns the access token string of this instance.
     *
     * @return string
     */
    public function getToken();

    /**
     * Returns the refresh token, if defined.
     *
     * @return string|null
     */
    public function getRefreshToken();

    /**
     * Returns the expiration timestamp, if defined.
     *
     * @return integer|null
     */
    public function getExpires();

    /**
     * Checks if this token has expired.
     *
     * @return boolean true if the token has expired, false otherwise.
     * @throws RuntimeException if 'expires' is not set on the token.
     */
    public function hasExpired();

    /**
     * Returns additional vendor values stored in the token.
     *
     * @return array
     */
    public function getValues();

    /**
     * Returns a string representation of the access token
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns an array of parameters to serialize when this is serialized with
     * json_encode().
     *
     * @return array
     */
    public function jsonSerialize();
}
