-- ============================================================
-- StudyFlow — Banco de Dados (MySQL / MariaDB)
-- Sistema de estudos para vestibulares — TCC
-- ============================================================

CREATE DATABASE IF NOT EXISTS sistema_vestibulares
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE sistema_vestibulares;

-- ==========================================
-- 1. USUÁRIOS
-- ==========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_acesso ENUM('aluno', 'admin') NOT NULL DEFAULT 'aluno',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================================
-- 2. VESTIBULARES, CURSOS, MATÉRIAS E PESOS
-- ==========================================
CREATE TABLE IF NOT EXISTS vestibulares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vestibular_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    CONSTRAINT fk_cursos_vestibulares
        FOREIGN KEY (vestibular_id) REFERENCES vestibulares(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    cor VARCHAR(7) NOT NULL DEFAULT '#6366f1'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pesos_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    materia_id INT NOT NULL,
    peso DECIMAL(3,2) NOT NULL,
    CONSTRAINT fk_pesos_cursos
        FOREIGN KEY (curso_id) REFERENCES cursos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pesos_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uk_curso_materia UNIQUE (curso_id, materia_id)
) ENGINE=InnoDB;

-- ==========================================
-- 3. PERFIL DO ESTUDANTE (vestibular/curso/tempo escolhidos)
-- ==========================================
CREATE TABLE IF NOT EXISTS perfil_estudante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    curso_id INT NOT NULL,
    horas_disponiveis_semana DECIMAL(4,2) NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfil_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_perfil_cursos
        FOREIGN KEY (curso_id) REFERENCES cursos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 4. CICLOS DE ESTUDO E AGENDA SEMANAL
-- ==========================================
CREATE TABLE IF NOT EXISTS ciclos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    status ENUM('ativo', 'concluido', 'pausado') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ciclos_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Itens do ciclo: tempo calculado por matéria = (peso / soma_pesos) * horas_semana * 60
CREATE TABLE IF NOT EXISTS itens_ciclo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciclo_id INT NOT NULL,
    materia_id INT NOT NULL,
    minutos_alocados INT NOT NULL,
    ordem_execucao INT NOT NULL,
    CONSTRAINT fk_itens_ciclos
        FOREIGN KEY (ciclo_id) REFERENCES ciclos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Posicionamento dos itens do ciclo na agenda semanal (dia + horário, por arrastar-e-soltar)
CREATE TABLE IF NOT EXISTS agenda_ciclo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    item_ciclo_id INT NOT NULL,
    dia_semana VARCHAR(3) NOT NULL,   -- Seg, Ter, Qua, Qui, Sex, Sáb, Dom
    horario VARCHAR(5) NOT NULL,      -- Ex: '08:00'
    CONSTRAINT fk_agenda_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_agenda_itens
        FOREIGN KEY (item_ciclo_id) REFERENCES itens_ciclo(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uk_usuario_dia_horario UNIQUE (usuario_id, dia_semana, horario)
) ENGINE=InnoDB;

-- ==========================================
-- 5. TAREFAS DIÁRIAS (painel/dashboard futuro)
-- ==========================================
CREATE TABLE IF NOT EXISTS tarefas_diarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT,
    titulo VARCHAR(150) NOT NULL,
    duracao_planejada_min INT DEFAULT 30,
    duracao_executada_min INT DEFAULT 0,
    data_agendada DATE NOT NULL,
    status ENUM('pendente', 'concluida') NOT NULL DEFAULT 'pendente',
    CONSTRAINT fk_tarefas_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tarefas_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 6. FLASHCARDS E REVISÕES
-- ==========================================
CREATE TABLE IF NOT EXISTS flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT,
    frente_pergunta TEXT NOT NULL,
    verso_resposta TEXT NOT NULL,
    classificacao ENUM('Fácil', 'Médio', 'Difícil') NOT NULL DEFAULT 'Médio',
    intervalo_dias INT NOT NULL DEFAULT 1,
    proxima_revisao DATE NOT NULL,
    vezes_revisado INT NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_flashcards_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_flashcards_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historico_revisoes_flashcard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flashcard_id INT NOT NULL,
    classificacao ENUM('Fácil', 'Médio', 'Difícil') NOT NULL,
    data_revisao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_flashcards
        FOREIGN KEY (flashcard_id) REFERENCES flashcards(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 7. SESSÕES DE ESTUDO (log para métricas de desempenho)
-- ==========================================
CREATE TABLE IF NOT EXISTS sessoes_estudo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT,
    tempo_estudado_min INT NOT NULL,
    data_estudo DATE NOT NULL,
    CONSTRAINT fk_sessoes_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sessoes_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- DADOS DE EXEMPLO (para a demonstração/apresentação)
-- ============================================================

INSERT INTO vestibulares (nome, sigla) VALUES
    ('ENEM 2027', 'ENEM'),
    ('FUVEST 2027', 'FUVEST'),
    ('FATEC 2027.1', 'FATEC');

INSERT INTO cursos (vestibular_id, nome) VALUES
    (1, 'Medicina'),
    (1, 'Direito'),
    (2, 'Engenharia de Computação'),
    (3, 'Análise e Desenvolvimento de Sistemas'),
    (3, 'Gestão Empresarial');

INSERT INTO materias (nome, cor) VALUES
    ('Matemática', '#3b82f6'),
    ('Português', '#a855f7'),
    ('Física', '#ec4899'),
    ('Química', '#22c55e'),
    ('Biologia', '#f97316'),
    ('História', '#eab308'),
    ('Geografia', '#06b6d4'),
    ('Redação', '#ef4444'),
    ('Lógica de Programação', '#8b5cf6'),
    ('Inglês', '#14b8a6');

-- Pesos por curso (curso_id, materia_id, peso)
INSERT INTO pesos_materias (curso_id, materia_id, peso) VALUES
    -- Medicina (1)
    (1, 1, 2.0), (1, 2, 2.0), (1, 3, 3.0), (1, 4, 3.0), (1, 5, 2.5), (1, 8, 1.5),
    -- Direito (2)
    (2, 1, 1.0), (2, 2, 3.0), (2, 6, 2.5), (2, 7, 1.5), (2, 8, 2.0),
    -- Engenharia de Computação (3)
    (3, 1, 3.0), (3, 3, 3.0), (3, 9, 2.0), (3, 2, 1.5), (3, 10, 1.0),
    -- Análise e Desenvolvimento de Sistemas (4)
    (4, 1, 3.0), (4, 9, 3.0), (4, 2, 1.5), (4, 10, 1.5),
    -- Gestão Empresarial (5)
    (5, 1, 2.0), (5, 2, 2.0), (5, 7, 1.5), (5, 8, 1.5);

