<?php
// Espera $activePage definida antes do include
$items = [
    'study_cycles.php'  => ['label' => 'Ciclo de Estudos',  'icon' => '▥'],
    'flashcards.php'    => ['label' => 'Flashcards',        'icon' => '▤'],
];
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">⚡</div>
        <span>StudyFlow</span>
    </div>
    <nav>
        <?php foreach ($items as $page => $item): ?>
            <a href="<?= $page ?>" class="nav-item <?= $activePage === $page ? 'active' : '' ?>">
                <span><?= $item['icon'] ?></span> <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
        <a href="logout.php" class="nav-item" style="margin-top:16px; border-top:1px solid var(--border); padding-top:16px;">
            <span>⏻</span> Sair
        </a>
    </nav>
</aside>
