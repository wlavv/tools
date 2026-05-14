<?php

namespace Modules\StreamDeckAccess\Exceptions;

use RuntimeException;

class TriggerRejectedException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 403,
    ) {
        parent::__construct($message);
    }
}
