<?php
session_start();
require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPerfil($pdo, $uid) {
    $stmt = $pdo->prepare('SELECT p.*, c.nome as curso_nome, v.nome as vestibular_nome, v.sigla as vestibular_sigla
                            FROM perfil_estudante p
                            JOIN cursos c ON c.id = p.curso_id
                            JOIN vestibulares v ON v.id = c.vestibular_id
                            WHERE p.usuario_id = ?');
    $stmt->execute([$uid]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
