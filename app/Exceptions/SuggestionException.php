<?php

namespace App\Exceptions;

use Exception;

class SuggestionException extends Exception
{
    public $suggestedName;
    public $suggestedNameId;
    public function __construct($message, $suggestedName, $suggestedNameId)
    {
        parent::__construct($message);
        $this->suggestedName = $suggestedName;
        $this->suggestedNameId = $suggestedNameId;
    }
}
