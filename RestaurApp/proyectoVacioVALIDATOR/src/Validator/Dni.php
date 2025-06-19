<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class  Dni extends Constraint
{
    public $message = 'El valor "{{ string }}" no es valor valido';
    public string $mode = 'strict';

    // all configurable options must be passed to the constructor
    public function __construct(string $mode = null, string $message = null, array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
    
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}


