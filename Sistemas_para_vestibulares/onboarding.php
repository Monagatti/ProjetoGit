<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$uid = $_SESSION['user_id'];

// Se já tem perfil configurado, manda para o ciclo
$perfilExistente = getPerfil($pdo, $uid);
if ($perfilExistente && !isset($_GET['refazer'])) {
    header('Location: study_cycles.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM vestibulares ORDER BY nome");
$vestibulares = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cursoId = (int)$_POST['curso_id'];
    $horas = (float)str_replace(',', '.', $_POST['horas']);

    if ($cursoId <= 0 || $horas <= 0) {
        $error = 'Selecione um curso e informe as horas disponíveis.';
    } else {
        // Salva/atualiza o perfil do estudante
        $stmt = $pdo->prepare("INSERT INTO perfil_estudante (usuario_id, curso_id, horas_disponiveis_semana)
                                VALUES (?, ?, ?)
                                ON CONFLICT(usuario_id) DO UPDATE SET curso_id = excluded.curso_id, horas_disponiveis_semana = excluded.horas_disponiveis_semana, atualizado_em = CURRENT_TIMESTAMP");
        $stmt->execute([$uid, $cursoId, $horas]);

        // Busca os pesos das matérias do curso escolhido
        $stmt = $pdo->prepare("SELECT pm.materia_id, pm.peso FROM pesos_materias pm WHERE pm.curso_id = ? ORDER BY pm.peso DESC");
        $stmt->execute([$cursoId]);
        $pesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $somaPesos = array_sum(array_column($pesos, 'peso'));

        // Cria um novo ciclo ativo (encerra os anteriores)
        $pdo->prepare("UPDATE ciclos SET status = 'concluido' WHERE usuario_id = ? AND status = 'ativo'")->execute([$uid]);
        $pdo->prepare("INSERT INTO ciclos (usuario_id, status) VALUES (?, 'ativo')")->execute([$uid]);
        $cicloId = (int)$pdo->lastInsertId();

        // Calcula minutos alocados por matéria: (peso / soma_pesos) * horas_semana * 60
        $totalMinutosSemana = $horas * 60;
        $ordem = 1;
        $stmtItem = $pdo->prepare("INSERT INTO itens_ciclo (ciclo_id, materia_id, minutos_alocados, ordem_execucao) VALUES (?, ?, ?, ?)");
        foreach ($pesos as $p) {
            $minutos = $somaPesos > 0 ? round(($p['peso'] / $somaPesos) * $totalMinutosSemana) : 0;
            if ($minutos < 15) $minutos = 15; // piso mínimo de sessão
            $stmtItem->execute([$cicloId, $p['materia_id'], $minutos, $ordem]);
            $ordem++;
        }

        header('Location: study_cycles.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>StudyFlow - Configurar seus estudos</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <a href="index.php" class="auth-logo">
        <div class="logo-icon">⚡</div>
        <span>StudyFlow</span>
    </a>

    <div class="auth-card" style="max-width:500px;">
        <h1>Vamos montar seu plano</h1>
        <p class="auth-sub">Escolha seu vestibular, curso e quanto tempo você tem disponível por semana</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" id="onboard-form">
            <div class="field">
                <label>Vestibular</label>
                <select id="vestibular-select" style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-light); border-radius:10px; color:var(--text); font-size:14px;">
                    <option value="">Selecione o vestibular</option>
                    <?php foreach ($vestibulares as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome']) ?> (<?= $v['sigla'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Curso</label>
                <select name="curso_id" id="curso-select" required style="width:100%; padding:12px 14px; background:var(--bg-card); border:1px solid var(--border-light); border-radius:10px; color:var(--text); font-size:14px;">
                    <option value="">Selecione o vestibular primeiro</option>
                </select>
            </div>

            <div class="field">
                <label>Horas disponíveis por semana</label>
                <div class="input-wrap"><input type="number" step="0.5" min="1" name="horas" placeholder="Ex: 20" required></div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:6px;">Gerar meu ciclo de estudos</button>
        </form>
    </div>
</div>

<script>
const cursosPorVestibular = <?php
    $stmt = $pdo->query("SELECT id, vestibular_id, nome FROM cursos ORDER BY nome");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($cursos as $c) $map[$c['vestibular_id']][] = $c;
    echo json_encode($map, JSON_UNESCAPED_UNICODE);
?>;

const vestibularSelect = document.getElementById('vestibular-select');
const cursoSelect = document.getElementById('curso-select');

vestibularSelect.addEventListener('change', () => {
    const cursos = cursosPorVestibular[vestibularSelect.value] || [];
    cursoSelect.innerHTML = cursos.length
        ? cursos.map(c => `<option value="${c.id}">${c.nome}</option>`).join('')
        : '<option value="">Nenhum curso cadastrado</option>';
});
</script>
</body>
</html>
