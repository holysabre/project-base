<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class CustomException extends Exception
{
    public function __construct($msg = '', $code = null)
    {
        parent::__construct($msg, $code);
    }

    public function render()
    {
        Log::error((string)$this->getMessage(), [$this->getFile(), $this->getLine()]);
        return json_response(400, (string)$this->getMessage());
    }
}
