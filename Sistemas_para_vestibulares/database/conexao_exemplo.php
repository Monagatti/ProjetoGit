<?php
// ============================================================
// Exemplo de conexão MySQL/MariaDB via PDO.
//
// Como usar:
// 1. Importe database/banco.sql no phpMyAdmin (ou via linha de comando)
// 2. Renomeie este arquivo para conexao.php (ou copie o conteúdo para
//    includes/db.php, substituindo a versão SQLite)
// 3. Ajuste $host / $user / $pass conforme seu ambiente (XAMPP/Laragon
//    normalmente usam user=root e senha em branco)
// ============================================================

$host    = 'localhost';
$db      = 'sistema_vestibulares';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

// ------------------------------------------------------------
// ATENÇÃO: ao trocar para MySQL, ajuste também estas diferenças
// de sintaxe usadas no restante do sistema (SQLite -> MySQL):
//
//   SQLite                              MySQL
//   DATE('now')                     ->  CURDATE()
//   DATE('now', '+N days')          ->  DATE_ADD(CURDATE(), INTERVAL N DAY)
//   INSERT ... ON CONFLICT(x)           INSERT ... ON DUPLICATE KEY UPDATE ...
//     DO UPDATE SET ...
// ------------------------------------------------------------

