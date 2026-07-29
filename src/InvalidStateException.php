<?php

declare(strict_types=1);

namespace Irwinlopez1023\MexCore;

class InvalidStateException extends \InvalidArgumentException
{
    public function __construct(string $input)
    {
        parent::__construct("No se encontró un estado de México para: {$input}");
    }
}
