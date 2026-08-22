<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($name) < 2) {
        $error = 'Informe seu nome.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            header('Location: onboarding.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>StudyFlow - Cadastro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <a href="index.php" class="auth-logo">
        <div class="logo-icon">⚡</div>
        <span>StudyFlow</span>
    </a>

    <div class="auth-card">
        <h1>Criar conta</h1>
        <p class="auth-sub">Comece sua jornada de aprendizado agora</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Nome</label>
                <div class="input-wrap"><input type="text" name="name" placeholder="Seu nome" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"></div>
            </div>
            <div class="field">
                <label>E-mail</label>
                <div class="input-wrap"><input type="email" name="email" placeholder="seu@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
            </div>
            <div class="field">
                <label>Senha</label>
                <div class="input-wrap"><input type="password" name="password" placeholder="Mínimo 6 caracteres" required></div>
            </div>
            <button type="submit" class="btn-primary" style="margin-top:6px;">Criar conta</button>
        </form>

        <div class="auth-footer">Já tem uma conta? <a href="login.php" class="link-purple">Entrar</a></div>
    </div>
</div>
</body>
</html>
