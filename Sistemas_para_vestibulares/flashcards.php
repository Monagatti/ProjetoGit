<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$activePage = 'flashcards.php';
$uid = $_SESSION['user_id'];

$perfil = getPerfil($pdo, $uid);
if (!$perfil) { header('Location: onboarding.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $materiaId = (int)$_POST['materia_id'] ?: null;
        $stmt = $pdo->prepare("INSERT INTO flashcards (usuario_id, materia_id, frente_pergunta, verso_resposta, classificacao) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $materiaId, trim($_POST['question']), trim($_POST['answer']), $_POST['difficulty']]);
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM flashcards WHERE id = ? AND usuario_id = ?");
        $stmt->execute([(int)$_POST['id'], $uid]);
    }
    header('Location: flashcards.php');
    exit;
}

// Matérias do curso do usuário (via pesos_materias) para o seletor
$stmt = $pdo->prepare("SELECT m.id, m.nome, m.cor FROM materias m
                        JOIN pesos_materias pm ON pm.materia_id = m.id
                        WHERE pm.curso_id = ? ORDER BY m.nome");
$stmt->execute([$perfil['curso_id']]);
$materiasCurso = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT f.*, m.nome as materia_nome, m.cor as materia_cor
                        FROM flashcards f LEFT JOIN materias m ON m.id = f.materia_id
                        WHERE f.usuario_id = ? ORDER BY f.criado_em DESC");
$stmt->execute([$uid]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT f.*, m.nome as materia_nome, m.cor as materia_cor
                        FROM flashcards f LEFT JOIN materias m ON m.id = f.materia_id
                        WHERE f.usuario_id = ? AND f.proxima_revisao <= DATE('now') ORDER BY f.proxima_revisao ASC");
$stmt->execute([$uid]);
$dueCards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($cards);
$dueToday = count($dueCards);
$avgMastery = 0;
if ($total > 0) {
    $sum = array_sum(array_map(fn($c) => $c['classificacao']==='Fácil'?100:($c['classificacao']==='Médio'?70:40), $cards));
    $avgMastery = round($sum / $total);
}

$stmt = $pdo->prepare("SELECT DISTINCT data_estudo FROM sessoes_estudo WHERE usuario_id = ? ORDER BY data_estudo DESC");
$stmt->execute([$uid]);
$diasEstudo = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'data_estudo');
$streak = 0; $cursor = new DateTime();
foreach ($diasEstudo as $d) { if ($d === $cursor->format('Y-m-d')) { $streak++; $cursor->modify('-1 day'); } else break; }

$colors = ['Fácil' => '#22c55e', 'Médio' => '#f97316', 'Difícil' => '#ef4444'];

// lista de matérias presentes nos cards, para os filtros
$materiasPresentes = [];
foreach ($cards as $c) {
    if ($c['materia_nome']) $materiasPresentes[$c['materia_nome']] = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>StudyFlow - Flashcards</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main">
        <div class="page-header">
            <div>
                <h1>Flashcards</h1>
                <p>Domine seus conteúdos com repetição espaçada — matérias de <?= htmlspecialchars($perfil['curso_nome']) ?></p>
            </div>
            <div class="header-actions">
                <button class="btn active-tab" id="tab-library">Biblioteca</button>
                <button class="btn" id="tab-review">Revisar</button>
                <button class="btn btn-accent" id="btn-new-card">+ Criar</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-value"><?= $total ?></div><div class="stat-sub">Total de Cards</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--green);"><?= $dueToday ?></div><div class="stat-sub">Para Hoje</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--orange);"><?= $avgMastery ?>%</div><div class="stat-sub">Taxa de Domínio</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--blue);"><?= $streak ?></div><div class="stat-sub">Dias Seguidos</div></div>
        </div>

        <!-- BIBLIOTECA -->
        <div id="view-library">
            <div class="filter-tabs" id="filter-tabs">
                <button class="filter-tab active" data-f="All">All</button>
                <?php foreach (array_keys($materiasPresentes) as $mn): ?>
                    <button class="filter-tab" data-f="<?= htmlspecialchars($mn) ?>"><?= htmlspecialchars($mn) ?></button>
                <?php endforeach; ?>
            </div>

            <?php if (empty($cards)): ?>
                <div class="empty-state">
                    <p>Você ainda não criou nenhum flashcard.</p>
                    <p style="margin-top:6px;">Clique em <b>+ Criar</b> para começar.</p>
                </div>
            <?php else: ?>
            <div class="cards-grid" id="cards-grid">
                <?php foreach ($cards as $c): ?>
                <div class="flash-card" data-subject="<?= htmlspecialchars($c['materia_nome'] ?? 'Geral') ?>">
                    <form method="POST" style="position:absolute; top:0; right:0;" onsubmit="return confirm('Excluir este flashcard?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="card-del">✕</button>
                    </form>
                    <span class="tag" style="background:<?= ($c['materia_cor'] ?? '#6366f1') ?>22; color:<?= $c['materia_cor'] ?? '#a78bfa' ?>;"><?= htmlspecialchars($c['materia_nome'] ?? 'Geral') ?></span>
                    <h3><?= htmlspecialchars($c['frente_pergunta']) ?></h3>
                    <div class="diff">Dificuldade: <b class="<?= $c['classificacao'] ?>"><?= $c['classificacao'] ?></b></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- REVISÃO -->
        <div id="view-review" style="display:none;">
            <?php if (empty($dueCards)): ?>
                <div class="empty-state">
                    <p>🎉 Nenhum card pendente para revisão agora.</p>
                </div>
            <?php else: ?>
            <div class="review-stage">
                <div class="review-progress" id="review-progress">Card 1 de <?= count($dueCards) ?></div>
                <div class="flip-card">
                    <div class="flip-inner" id="flip-inner">
                        <div class="flip-face front">
                            <div class="label">Pergunta</div>
                            <p id="q-text"></p>
                        </div>
                        <div class="flip-face back">
                            <div class="label">Resposta</div>
                            <p id="a-text"></p>
                        </div>
                    </div>
                </div>
                <div class="review-actions">
                    <button class="btn btn-danger" id="btn-hard">😖 Difícil</button>
                    <button class="btn" id="btn-medium">🙂 Médio</button>
                    <button class="btn btn-accent" id="btn-easy">😄 Fácil</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal criar card -->
<div class="modal-overlay" id="modal-new">
    <div class="modal-box">
        <h2>Novo Flashcard</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="field">
                <label>Matéria</label>
                <select name="materia_id" style="width:100%; padding:10px; background:var(--bg-card); border:1px solid var(--border-light); border-radius:8px; color:var(--text);">
                    <option value="">Geral</option>
                    <?php foreach ($materiasCurso as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Pergunta</label>
                <textarea name="question" required placeholder="Digite a pergunta..."></textarea>
            </div>
            <div class="field">
                <label>Resposta</label>
                <textarea name="answer" required placeholder="Digite a resposta..."></textarea>
            </div>
            <div class="field">
                <label>Dificuldade</label>
                <select name="difficulty" style="width:100%; padding:10px; background:var(--bg-card); border:1px solid var(--border-light); border-radius:8px; color:var(--text);">
                    <option>Fácil</option>
                    <option selected>Médio</option>
                    <option>Difícil</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn" id="btn-cancel">Cancelar</button>
                <button type="submit" class="btn btn-accent">Salvar</button>
            </div>
        </form>
    </div>
</div>

<div class="help-fab">?</div>

<script>
const dueCards = <?= json_encode($dueCards, JSON_UNESCAPED_UNICODE) ?>;

// tabs biblioteca / revisar
const tabLib = document.getElementById('tab-library');
const tabRev = document.getElementById('tab-review');
const viewLib = document.getElementById('view-library');
const viewRev = document.getElementById('view-review');
tabLib.addEventListener('click', () => { tabLib.classList.add('active-tab'); tabRev.classList.remove('active-tab'); viewLib.style.display='block'; viewRev.style.display='none'; });
tabRev.addEventListener('click', () => { tabRev.classList.add('active-tab'); tabLib.classList.remove('active-tab'); viewRev.style.display='block'; viewLib.style.display='none'; });

// filtro por matéria
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const f = tab.dataset.f;
        document.querySelectorAll('.flash-card').forEach(card => {
            card.style.display = (f === 'All' || card.dataset.subject === f) ? 'block' : 'none';
        });
    });
});

// modal
const modal = document.getElementById('modal-new');
document.getElementById('btn-new-card').addEventListener('click', () => modal.classList.add('open'));
document.getElementById('btn-cancel').addEventListener('click', () => modal.classList.remove('open'));
modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

// revisão com flip
let idx = 0;
const flip = document.getElementById('flip-inner');
const qText = document.getElementById('q-text');
const aText = document.getElementById('a-text');
const progress = document.getElementById('review-progress');

function loadCard() {
    if (!dueCards.length) return;
    if (idx >= dueCards.length) {
        document.querySelector('.review-stage').innerHTML = '<div class="empty-state"><p>🎉 Você revisou todos os cards pendentes!</p></div>';
        return;
    }
    flip.classList.remove('flipped');
    qText.textContent = dueCards[idx].frente_pergunta;
    aText.textContent = dueCards[idx].verso_resposta;
    progress.textContent = `Card ${idx+1} de ${dueCards.length}`;
}
if (flip) {
    flip.addEventListener('click', () => flip.classList.toggle('flipped'));
    loadCard();
}

function rate(level) {
    const card = dueCards[idx];
    fetch('api/flashcards.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({action:'review', id: card.id, level})
    }).then(() => { idx++; loadCard(); });
}
document.getElementById('btn-hard')?.addEventListener('click', () => rate('Difícil'));
document.getElementById('btn-medium')?.addEventListener('click', () => rate('Médio'));
document.getElementById('btn-easy')?.addEventListener('click', () => rate('Fácil'));
</script>
</body>
</html>
