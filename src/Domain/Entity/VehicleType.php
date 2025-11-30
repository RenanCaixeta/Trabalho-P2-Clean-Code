<?php

namespace App\Domain\Entity;

enum VehicleType: string
{
    case CAR = 'carro';
    case MOTORCYCLE = 'moto';
    case TRUCK = 'caminhao';

    public function getHourlyRate(): int
    {
        return match($this) {
            self::CAR => 5,
            self::MOTORCYCLE => 3,
            self::TRUCK => 10,
        };
    }
}