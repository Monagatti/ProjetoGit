<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }

$uid = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'place') {
    $itemCicloId = (int)($data['item_ciclo_id'] ?? 0);
    $day = $data['day'] ?? '';
    $time = $data['time'] ?? '';

    // confere se o item pertence a um ciclo do usuário
    $stmt = $pdo->prepare("SELECT ic.id FROM itens_ciclo ic JOIN ciclos c ON c.id = ic.ciclo_id WHERE ic.id = ? AND c.usuario_id = ?");
    $stmt->execute([$itemCicloId, $uid]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'item inválido']); exit; }

    // remove o que já estiver nesse slot
    $stmt = $pdo->prepare("DELETE FROM agenda_ciclo WHERE usuario_id = ? AND dia_semana = ? AND horario = ?");
    $stmt->execute([$uid, $day, $time]);

    $stmt = $pdo->prepare("INSERT INTO agenda_ciclo (usuario_id, item_ciclo_id, dia_semana, horario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$uid, $itemCicloId, $day, $time]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'remove') {
    $id = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM agenda_ciclo WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $uid]);
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['error' => 'invalid action']);

