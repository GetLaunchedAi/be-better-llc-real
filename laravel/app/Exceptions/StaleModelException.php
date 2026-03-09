<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an optimistic lock update fails because the model
 * was modified by another user (lock_version mismatch).
 */
class StaleModelException extends RuntimeException
{
    //
}

