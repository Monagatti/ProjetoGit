<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['senha_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $perfil = getPerfil($pdo, $user['id']);
        header('Location: ' . ($perfil ? 'study_cycles.php' : 'onboarding.php'));
        exit;
    } else {
        $error = 'E-mail ou senha inválidos.';
    }
}
$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>StudyFlow - Entrar</title>
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
        <h1>Bem-vindo de volta</h1>
        <p class="auth-sub">Entre para continuar sua jornada de aprendizado</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($registered): ?><div class="alert alert-success">Conta criada com sucesso! Faça login.</div><?php endif; ?>

        <button type="button" class="btn-oauth">🔍 Continuar com Google</button>
        <button type="button" class="btn-oauth">🐙 Continuar com GitHub</button>

        <div class="divider">Ou continue com e-mail</div>

        <form method="POST">
            <div class="field">
                <label>E-mail</label>
                <div class="input-wrap">
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>
            </div>
            <div class="field">
                <label>Senha</label>
                <div class="input-wrap">
                    <input type="password" name="password" placeholder="Digite sua senha" required>
                </div>
            </div>
            <div class="row-between">
                <label class="checkbox-row"><input type="checkbox" name="remember"> Lembrar de mim</label>
                <a href="#" class="link-purple">Esqueceu a senha?</a>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="auth-footer">Não tem uma conta? <a href="register.php" class="link-purple">Cadastre-se</a></div>
    </div>
</div>
</body>
</html>
