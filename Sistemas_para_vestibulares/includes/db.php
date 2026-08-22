<?php
// Conexão com banco de dados (SQLite - zero configuração)
// Para usar MySQL, troque o DSN abaixo por:
// new PDO("mysql:host=localhost;dbname=sistema_vestibulares;charset=utf8mb4", "usuario", "senha")

$dbPath = __DIR__ . '/../database/studyflow.sqlite';
$isNew = !file_exists($dbPath);

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('Erro na conexão com banco de dados: ' . $e->getMessage());
}

if ($isNew) {
    $pdo->exec("
        CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            senha_hash TEXT NOT NULL,
            tipo_acesso TEXT NOT NULL DEFAULT 'aluno',
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE vestibulares (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            sigla TEXT NOT NULL
        );

        CREATE TABLE cursos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vestibular_id INTEGER NOT NULL,
            nome TEXT NOT NULL,
            FOREIGN KEY(vestibular_id) REFERENCES vestibulares(id) ON DELETE CASCADE
        );

        CREATE TABLE materias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL UNIQUE,
            cor TEXT NOT NULL DEFAULT '#6366f1'
        );

        CREATE TABLE pesos_materias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            curso_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            peso REAL NOT NULL,
            FOREIGN KEY(curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
            FOREIGN KEY(materia_id) REFERENCES materias(id) ON DELETE CASCADE,
            UNIQUE(curso_id, materia_id)
        );

        CREATE TABLE perfil_estudante (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL UNIQUE,
            curso_id INTEGER NOT NULL,
            horas_disponiveis_semana REAL NOT NULL,
            atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY(curso_id) REFERENCES cursos(id) ON DELETE CASCADE
        );

        CREATE TABLE ciclos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'ativo',
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        CREATE TABLE itens_ciclo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ciclo_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            minutos_alocados INTEGER NOT NULL,
            ordem_execucao INTEGER NOT NULL,
            FOREIGN KEY(ciclo_id) REFERENCES ciclos(id) ON DELETE CASCADE,
            FOREIGN KEY(materia_id) REFERENCES materias(id) ON DELETE CASCADE
        );

        CREATE TABLE agenda_ciclo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            item_ciclo_id INTEGER NOT NULL,
            dia_semana TEXT NOT NULL,
            horario TEXT NOT NULL,
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY(item_ciclo_id) REFERENCES itens_ciclo(id) ON DELETE CASCADE
        );

        CREATE TABLE tarefas_diarias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            materia_id INTEGER,
            titulo TEXT NOT NULL,
            duracao_planejada_min INTEGER DEFAULT 30,
            duracao_executada_min INTEGER DEFAULT 0,
            data_agendada TEXT DEFAULT CURRENT_DATE,
            status TEXT DEFAULT 'pendente',
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        CREATE TABLE flashcards (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            materia_id INTEGER,
            frente_pergunta TEXT NOT NULL,
            verso_resposta TEXT NOT NULL,
            classificacao TEXT DEFAULT 'Médio',
            intervalo_dias INTEGER DEFAULT 1,
            proxima_revisao TEXT DEFAULT CURRENT_DATE,
            vezes_revisado INTEGER DEFAULT 0,
            criado_em TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY(materia_id) REFERENCES materias(id) ON DELETE SET NULL
        );

        CREATE TABLE historico_revisoes_flashcard (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            flashcard_id INTEGER NOT NULL,
            classificacao TEXT NOT NULL,
            data_revisao TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE
        );

        CREATE TABLE sessoes_estudo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            materia_id INTEGER,
            tempo_estudado_min INTEGER NOT NULL,
            data_estudo TEXT DEFAULT CURRENT_DATE,
            FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );
    ");

    // ---------- Seed de dados (vestibulares, cursos, materias, pesos) ----------
    $pdo->exec("INSERT INTO vestibulares (nome, sigla) VALUES
        ('ENEM 2027', 'ENEM'),
        ('FUVEST 2027', 'FUVEST'),
        ('FATEC 2027.1', 'FATEC')
    ");

    $pdo->exec("INSERT INTO cursos (vestibular_id, nome) VALUES
        (1, 'Medicina'),
        (1, 'Direito'),
        (2, 'Engenharia de Computação'),
        (3, 'Análise e Desenvolvimento de Sistemas'),
        (3, 'Gestão Empresarial')
    ");

    $materias = [
        ['Matemática', '#3b82f6'], ['Português', '#a855f7'], ['Física', '#ec4899'],
        ['Química', '#22c55e'], ['Biologia', '#f97316'], ['História', '#eab308'],
        ['Geografia', '#06b6d4'], ['Redação', '#ef4444'], ['Lógica de Programação', '#8b5cf6'],
        ['Inglês', '#14b8a6'],
    ];
    $stmt = $pdo->prepare("INSERT INTO materias (nome, cor) VALUES (?, ?)");
    foreach ($materias as $m) $stmt->execute($m);

    // curso_id => [ [materia_id, peso], ... ]
    $pesos = [
        1 => [[1,2.0],[2,2.0],[3,3.0],[4,3.0],[5,2.5],[8,1.5]],           // Medicina
        2 => [[1,1.0],[2,3.0],[6,2.5],[7,1.5],[8,2.0]],                   // Direito
        3 => [[1,3.0],[3,3.0],[9,2.0],[2,1.5],[10,1.0]],                  // Eng. Computação
        4 => [[1,3.0],[9,3.0],[2,1.5],[10,1.5]],                         // ADS
        5 => [[1,2.0],[2,2.0],[7,1.5],[8,1.5]],                         // Gestão
    ];
    $stmt = $pdo->prepare("INSERT INTO pesos_materias (curso_id, materia_id, peso) VALUES (?, ?, ?)");
    foreach ($pesos as $cursoId => $lista) {
        foreach ($lista as [$materiaId, $peso]) {
            $stmt->execute([$cursoId, $materiaId, $peso]);
        }
    }
}
