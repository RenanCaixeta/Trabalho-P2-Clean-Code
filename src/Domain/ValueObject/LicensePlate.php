<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

readonly class LicensePlate
{
    public function __construct(public string $value)
    {
        if (empty($value) || strlen($value) < 7) {
            throw new InvalidArgumentException("Placa inválida.");
        }
    }

    public function __toString(): string
    {
        return strtoupper($this->value);
    }
}