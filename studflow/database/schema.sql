-- Criacao do Banco de Dados
CREATE DATABASE IF NOT EXISTS sistema_vestibulares;
DEFAULT CHARACTER SET utf8mb4;
DEFAULT COLLATE utf8mb4_unicode_ci;

USE sistema_vestibulares;

-- ==========================================
-- 1. MÓDULO DE USUÁRIOS E CONFIGURAÇÕES
-- ==========================================

-- Tabela de Usuários (Alunos e Admins)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_acesso ENUM('aluno', 'admin') NOT NULL DEFAULT 'aluno',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================================
-- 2. MÓDULO DE VESTIBULARES, CURSOS E PESOS
-- ==========================================

-- Tabela de Vestibulares (Ex: FATEC, FUVEST, ENEM)
CREATE TABLE IF NOT EXISTS vestibulares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(20) NOT NULL,
) ENGINE=InnoDB;

-- Tabela de Cursos por Vestibular
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vestibular_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    CONSTRAINT fk_cursos_vestibulares
        FOREIGN KEY (vestibular_id) REFERENCES vestibulares(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabela de Matérias do Catálogo
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Tabela de Pesos (Relação N:N entre Cursos e Matérias)
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

-- Tabela de Perfil/Metas do Aluno
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
-- 3. MÓDULO DE CICLOS E TAREFAS
-- ==========================================

-- Tabela de Ciclos de Estudo
CREATE TABLE IF NOT EXISTS ciclos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    status ENUM('ativo', 'concluido', 'pausado') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ciclos_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabela de Itens/Blocos do Ciclo (Matérias e Duração)
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

-- Tabela de Tarefas Diárias (Painel Principal)
CREATE TABLE IF NOT EXISTS tarefas_diarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT NOT NULL,
    duracao_planejada_min INT NOT NULL,
    duracao_executada_min INT DEFAULT 0,
    data_agendada DATE NOT NULL,
    status ENUM('pendente', 'concluida') NOT NULL DEFAULT 'pendente',
    CONSTRAINT fk_tarefas_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tarefas_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 4. MÓDULO DE FLASHCARDS E QUESTÕES DE ERRO
-- ==========================================

-- Tabela de Flashcards / Caderno de Erros
CREATE TABLE IF NOT EXISTS flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT NOT NULL,
    frente_pergunta TEXT NOT NULL,
    verso_resposta TEXT NOT NULL,
    proxima_revisao DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_flashcards_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_flashcards_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabela de Histórico de Revisões de Flashcards
CREATE TABLE IF NOT EXISTS historico_revisoes_flashcard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flashcard_id INT NOT NULL,
    classificacao ENUM('errei', 'dificil', 'bom', 'facil') NOT NULL,
    data_revisao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_flashcards
        FOREIGN KEY (flashcard_id) REFERENCES flashcards(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 5. MÓDULO DE DESEMPENHO E LOGS DE ESTUDO
-- ==========================================

-- Tabela de Sessões de Estudo Finalizadas
CREATE TABLE IF NOT EXISTS sessoes_estudo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    materia_id INT NOT NULL,
    tempo_estudado_min INT NOT NULL,
    data_estudo DATE NOT NULL,
    CONSTRAINT fk_sessoes_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sessoes_materias
        FOREIGN KEY (materia_id) REFERENCES materias(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
