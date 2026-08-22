<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: study_cycles.php'); exit; }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>StudyFlow - Sua aprovação no vestibular começa aqui</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="StudyFlow: monte seu ciclo de estudos calculado pelo peso das matérias do seu curso e memorize com flashcards de repetição espaçada.">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="landing-header">
    <div class="logo">
        <div class="logo-icon">⚡</div>
        <span>StudyFlow</span>
    </div>
    <nav class="landing-nav">
        <a href="#recursos" class="link">Recursos</a>
        <a href="#como-funciona" class="link">Como funciona</a>
        <div class="landing-actions">
            <a href="login.php" class="btn">Entrar</a>
            <a href="register.php" class="btn btn-accent">Começar grátis</a>
        </div>
    </nav>
</header>

<section class="hero">
    <span class="eyebrow">🎯 Feito para quem estuda para vestibular</span>
    <h1>Organize seus estudos com <span class="grad">inteligência</span>, não no achismo</h1>
    <p class="sub">Escolha seu vestibular e curso, informe quanto tempo você tem disponível e o StudyFlow monta seu ciclo de estudos automaticamente, com base no peso real de cada matéria. Depois é só memorizar o que errou com flashcards de repetição espaçada.</p>
    <div class="hero-cta">
        <a href="register.php" class="btn-primary" style="text-decoration:none; display:inline-block;">Criar minha conta grátis</a>
        <a href="login.php" class="btn-oauth" style="text-decoration:none; display:inline-block;">Já tenho conta</a>
    </div>
    <div class="hero-note">Sem cartão de crédito · Leva menos de 2 minutos para começar</div>
</section>

<section class="section" id="recursos">
    <div class="section-title">
        <h2>Tudo que você precisa para estudar melhor</h2>
        <p>Um plano de estudos justo com o que realmente cai na sua prova, e uma memória que não te deixa na mão.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(59,130,246,.15); color:#3b82f6;">🎓</div>
            <h3>Vestibular e curso certos</h3>
            <p>Escolha entre ENEM, FUVEST, FATEC e outros, e o curso que você quer cursar. Cada um tem seu próprio peso de matérias.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(124,108,246,.15); color:#a78bfa;">⚙️</div>
            <h3>Ciclo de estudos automático</h3>
            <p>Informe suas horas disponíveis por semana e o sistema calcula quanto tempo dedicar a cada matéria, proporcional ao peso dela no seu curso.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(34,197,94,.15); color:#22c55e;">🧠</div>
            <h3>Flashcards de repetição espaçada</h3>
            <p>Guarde as questões que você errou ou teve dificuldade. O sistema traz elas de volta no momento certo para fixar de vez.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(249,115,22,.15); color:#f97316;">🗓️</div>
            <h3>Agenda semanal visual</h3>
            <p>Arraste cada matéria do seu ciclo para os dias e horários que fazem sentido na sua rotina.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(236,72,153,.15); color:#ec4899;">📊</div>
            <h3>Acompanhamento de desempenho</h3>
            <p>Veja sua sequência de dias estudando, taxa de domínio dos flashcards e cobertura da sua agenda.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:rgba(6,182,212,.15); color:#06b6d4;">🔒</div>
            <h3>Seus dados, sua conta</h3>
            <p>Login seguro com senha criptografada. Seu plano de estudos fica salvo e disponível sempre que você entrar.</p>
        </div>
    </div>
</section>

<section class="section" id="como-funciona">
    <div class="section-title">
        <h2>Como funciona</h2>
        <p>Três passos entre criar a conta e ter seu plano de estudos pronto.</p>
    </div>
    <div class="steps-row">
        <div class="step-card">
            <div class="step-num">1</div>
            <h4>Escolha vestibular e curso</h4>
            <p>Selecione a prova e o curso que você quer prestar.</p>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <h4>Informe seu tempo disponível</h4>
            <p>Diga quantas horas por semana você consegue estudar.</p>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <h4>Estude com o ciclo gerado</h4>
            <p>Organize as sessões na agenda e revise flashcards todo dia.</p>
        </div>
    </div>
</section>

<div class="cta-band">
    <h2>Pronto para estudar com um plano de verdade?</h2>
    <p>Crie sua conta gratuitamente e monte seu primeiro ciclo de estudos agora mesmo.</p>
    <a href="register.php" class="btn-primary" style="max-width:280px; margin:0 auto; text-decoration:none; display:inline-block;">Começar agora</a>
</div>

<footer class="landing-footer">
    © <?= date('Y') ?> StudyFlow — Projeto acadêmico (TCC) de preparação para vestibulares.
</footer>

</body>
</html>
