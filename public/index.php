<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCase\ParkingService;
use App\Infra\Repository\SqliteTicketRepository;

$repo = new SqliteTicketRepository();
$service = new ParkingService($repo);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'entry') {
            $service->enter($_POST['plate'], $_POST['type']);
            $message = "Entrada registrada com sucesso!";
            $messageType = 'success';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'exit') {
            $ticket = $service->exit($_POST['plate']);
            $message = "Saída registrada. Valor: R$ " . number_format($ticket->getTotalAmount(), 2, ',', '.');
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$history = $service->getHistory();
$report = $service->getReportData();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estacionamento Inteligente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Controle de Estacionamento</h1>

        <?php if ($message): ?>
            <script>
                Swal.fire({
                    title: '<?= $messageType === 'success' ? 'Sucesso!' : 'Atenção!' ?>',
                    text: '<?= $message ?>',
                    icon: '<?= $messageType ?>',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="border p-4 rounded bg-blue-50">
                <h2 class="text-xl font-bold mb-4">Registrar Entrada</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="entry">
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Placa</label>
                        <input type="text" name="plate" required class="w-full p-2 border rounded uppercase" placeholder="ABC-1234">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Tipo</label>
                        <select name="type" class="w-full p-2 border rounded">
                            <option value="carro">Carro (R$ 5/h)</option>
                            <option value="moto">Moto (R$ 3/h)</option>
                            <option value="caminhao">Caminhão (R$ 10/h)</option>
                        </select>
                    </div>
                    <button class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Registrar Entrada</button>
                </form>
            </div>

            <div class="border p-4 rounded bg-red-50">
                <h2 class="text-xl font-bold mb-4">Registrar Saída</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="exit">
                    <div class="mb-3">
                        <label class="block text-sm font-bold mb-1">Placa</label>
                        <input type="text" name="plate" required class="w-full p-2 border rounded uppercase" placeholder="ABC-1234">
                    </div>
                    <button class="w-full bg-red-500 text-white p-2 rounded hover:bg-red-600 mt-10">Calcular e Registrar Saída</button>
                </form>
            </div>
        </div>

        <h2 class="text-2xl font-bold mb-4">Relatório de Faturamento</h2>
        <div class="grid grid-cols-3 gap-4 mb-8">
            <?php foreach ($report as $row): ?>
            <div class="bg-gray-800 text-white p-4 rounded text-center">
                <h3 class="capitalize text-lg font-bold"><?= $row['type'] ?></h3>
                <p>Veículos: <?= $row['count'] ?></p>
                <p class="text-green-400 font-bold">R$ <?= number_format($row['total'], 2, ',', '.') ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="text-2xl font-bold mb-4">Histórico Recente</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Placa</th>
                    <th class="p-2 border">Tipo</th>
                    <th class="p-2 border">Entrada</th>
                    <th class="p-2 border">Saída</th>
                    <th class="p-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $t): ?>
                <tr class="border-b">
                    <td class="p-2"><?= $t->getId() ?></td>
                    <td class="p-2 font-mono font-bold"><?= $t->getPlate() ?></td>
                    <td class="p-2 capitalize"><?= $t->getType() ?></td>
                    <td class="p-2"><?= $t->getEntryTime() ?></td>
                    <td class="p-2"><?= $t->getExitTime() ?: '<span class="text-green-600">Estacionado</span>' ?></td>
                    <td class="p-2"><?= $t->getTotalAmount() ? 'R$ ' . number_format($t->getTotalAmount(), 2) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>