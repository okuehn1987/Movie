<?php

namespace App\Exceptions;

use Exception;

class OpenAIServerException extends Exception
{
    private $error_code = '';

    public function __construct($message = 'OpenAI server error', $code = 500, $error_code = null)
    {
        parent::__construct($message, $code);

        $this->error_code = $error_code;
    }
    public function render()
    {
        return back()->withErrors(['openai' => $this->getMessage(), 'code' => $this->getCode(), 'error_code' => $this->error_code]);
    }
}
