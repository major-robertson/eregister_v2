<?php

namespace App\Domains\Lien\Exceptions;

use App\Domains\Lien\Enums\FilingStatus;
use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(
        public readonly FilingStatus $from,
        public readonly FilingStatus $to
    ) {
        parent::__construct(
            "Invalid status transition from '{$from->value}' to '{$to->value}'"
        );
    }
}
