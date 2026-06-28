<?php

namespace App\Domains\Esign\Exceptions;

use RuntimeException;

/**
 * Thrown for e-sign guard failures (e.g. unsupported document type, missing
 * signer, already-active session). The UI layer catches these and flashes the
 * message to the admin.
 */
class EsignException extends RuntimeException {}
