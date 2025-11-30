<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\LicensePlate;
use DateTimeImmutable;

class Ticket
{
    private ?DateTimeImmutable $exitTime = null;
    private ?float $totalAmount = null;

    public function __construct(
        private ?int $id,
        private LicensePlate $plate,
        private VehicleType $type,
        private DateTimeImmutable $entryTime
    ) {}

    public function closeTicket(DateTimeImmutable $exitTime): void
    {
        if ($exitTime < $this->entryTime) {
            throw new \DomainException("Hora de saída anterior à entrada.");
        }
        $this->exitTime = $exitTime;
        $this->calculateTotal();
    }

    private function calculateTotal(): void
    {
        $interval = $this->entryTime->diff($this->exitTime);
        $hours = $interval->h + ($interval->i > 0 || $interval->s > 0 ? 1 : 0);
        if ($interval->days > 0) $hours += $interval->days * 24;
        if ($hours === 0) $hours = 1;

        $this->totalAmount = $hours * $this->type->getHourlyRate();
    }

    public function getId(): ?int { return $this->id; }
    public function getPlate(): string { return (string) $this->plate; }
    public function getType(): string { return $this->type->value; }
    public function getEntryTime(): string { return $this->entryTime->format('Y-m-d H:i:s'); }
    public function getExitTime(): ?string { return $this->exitTime?->format('Y-m-d H:i:s'); }
    public function getTotalAmount(): ?float { return $this->totalAmount; }
}