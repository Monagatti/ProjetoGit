<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }

$uid = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'review') {
    $id = (int)($data['id'] ?? 0);
    $level = $data['level'] ?? 'Médio';

    $stmt = $pdo->prepare("SELECT intervalo_dias, materia_id FROM flashcards WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo json_encode(['error'=>'not found']); exit; }

    $interval = (int)$row['intervalo_dias'];
    if ($level === 'Difícil') $interval = 1;
    elseif ($level === 'Médio') $interval = max(1, $interval * 2);
    else $interval = max(1, $interval * 3); // Fácil

    $stmt = $pdo->prepare("UPDATE flashcards SET classificacao = ?, intervalo_dias = ?, proxima_revisao = DATE('now', ?), vezes_revisado = vezes_revisado + 1 WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$level, $interval, "+{$interval} days", $id, $uid]);

    $stmt = $pdo->prepare("INSERT INTO historico_revisoes_flashcard (flashcard_id, classificacao) VALUES (?, ?)");
    $stmt->execute([$id, $level]);

    $stmt = $pdo->prepare("INSERT INTO sessoes_estudo (usuario_id, materia_id, tempo_estudado_min) VALUES (?, ?, 2)");
    $stmt->execute([$uid, $row['materia_id']]);

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['error' => 'invalid action']);

