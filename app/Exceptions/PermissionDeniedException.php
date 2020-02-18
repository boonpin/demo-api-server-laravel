<?php

namespace App\Exceptions;

class PermissionDeniedException extends \Exception
{
    public function __construct($message = "Permission Denied!", \Throwable $previous = null, $code = 403)
    {
        parent::__construct($message, $previous, $code);
    }
}
