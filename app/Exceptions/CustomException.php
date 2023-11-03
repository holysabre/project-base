<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public function __construct($msg = '', $code = null)
    {
        parent::__construct($msg, $code);
    }

    public function render()
    {
        return json_response(400, (string)$this->getMessage());
    }
}
