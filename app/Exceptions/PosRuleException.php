<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a POS business rule is violated (insufficient stock, invalid
 * quantity, invalid state transition). Controllers map this to HTTP 422 so
 * genuine server faults still surface as 500 instead of being flattened
 * into a blanket 400.
 */
class PosRuleException extends Exception {}
