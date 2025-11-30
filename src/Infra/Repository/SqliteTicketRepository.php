<?php

namespace App\Infra\Repository;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\VehicleType;
use App\Domain\Repository\TicketRepositoryInterface;
use App\Domain\ValueObject\LicensePlate;
use App\Infra\Database\SqliteConnection;
use DateTimeImmutable;
use PDO;

class SqliteTicketRepository implements TicketRepositoryInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = SqliteConnection::getConnection();
    }

    public function save(Ticket $ticket): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO tickets (plate, type, entry_time) VALUES (:plate, :type, :entry)");
        $stmt->execute([
            ':plate' => $ticket->getPlate(),
            ':type' => $ticket->getType(),
            ':entry' => $ticket->getEntryTime()
        ]);
    }

    public function update(Ticket $ticket): void
    {
        $stmt = $this->pdo->prepare("UPDATE tickets SET exit_time = :exit, total_amount = :amount WHERE id = :id");
        $stmt->execute([
            ':exit' => $ticket->getExitTime(),
            ':amount' => $ticket->getTotalAmount(),
            ':id' => $ticket->getId()
        ]);
    }

    public function findOpenByPlate(string $plate): ?Ticket
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE plate = :plate AND exit_time IS NULL LIMIT 1");
        $stmt->execute([':plate' => $plate]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new Ticket(
            $data['id'],
            new LicensePlate($data['plate']),
            VehicleType::from($data['type']),
            new DateTimeImmutable($data['entry_time'])
        );
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tickets ORDER BY id DESC");
        $tickets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ticket = new Ticket(
                $row['id'],
                new LicensePlate($row['plate']),
                VehicleType::from($row['type']),
                new DateTimeImmutable($row['entry_time'])
            );
            if ($row['exit_time']) {
                $ticket->closeTicket(new DateTimeImmutable($row['exit_time'])); 
            }
            $tickets[] = $ticket;
        }
        return $tickets;
    }

    public function getReport(): array
    {
        $sql = "SELECT type, COUNT(*) as count, SUM(total_amount) as total FROM tickets WHERE exit_time IS NOT NULL GROUP BY type";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}