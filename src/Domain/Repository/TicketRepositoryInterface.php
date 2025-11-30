<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Ticket;

interface TicketRepositoryInterface
{
    public function save(Ticket $ticket): void;
    public function update(Ticket $ticket): void;
    public function findOpenByPlate(string $plate): ?Ticket;
    public function getAll(): array;
    /** @return array<string, mixed> Relatório de faturamento */
    public function getReport(): array;
}