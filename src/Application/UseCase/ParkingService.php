<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\VehicleType;
use App\Domain\Repository\TicketRepositoryInterface;
use App\Domain\ValueObject\LicensePlate;
use DateTimeImmutable;

class ParkingService
{
    public function __construct(private TicketRepositoryInterface $repository) {}

    public function enter(string $plateRaw, string $typeRaw): void
    {
        $plate = new LicensePlate($plateRaw);
        
        if ($this->repository->findOpenByPlate($plateRaw)) {
            throw new \Exception("Veículo já está no estacionamento.");
        }

        $ticket = new Ticket(
            null,
            $plate,
            VehicleType::from($typeRaw),
            new DateTimeImmutable()
        );

        $this->repository->save($ticket);
    }

    public function exit(string $plateRaw): Ticket
    {
        $plate = new LicensePlate($plateRaw);
        $ticket = $this->repository->findOpenByPlate((string) $plate);
        
        if (!$ticket) {
            throw new \Exception("Veículo não encontrado ou já saiu.");
        }

        $ticket->closeTicket(new DateTimeImmutable());
        $this->repository->update($ticket);
        
        return $ticket;
    }
    
    public function getHistory(): array
    {
        return $this->repository->getAll();
    }

    public function getReportData(): array
    {
        return $this->repository->getReport();
    }
}