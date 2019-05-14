<?php
namespace Mamaclub\Grant\Exception;

use InvalidArgumentException;

/**
 * Exception thrown if the grant does not extend from AbstractGrant.
 *
 * @see Mamaclub\Grant\AbstractGrant
 */
class InvalidGrantException extends InvalidArgumentException
{
}
