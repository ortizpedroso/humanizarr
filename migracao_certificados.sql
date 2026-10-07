-- =====================================================================
-- MIGRAÇÃO COMPLETA: Sistema de Cursos/Eventos + Certificados
-- HumanizaMais | Banco correto na Hostinger: u970180508_humanizarr
-- (NÃO é o banco u970180508_certificados — por isso deu erro #1146)
--
-- COMO EXECUTAR:
--   phpMyAdmin -> selecione o banco "u970180508_humanizarr" (coluna esquerda)
--   -> aba SQL -> cole TODO este arquivo -> Executar
-- O script é idempotente: pode rodar de novo sem quebrar nada.
-- =====================================================================

-- 1) Cria as tabelas se ainda não existirem ---------------------------

CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    duracao VARCHAR(50) NOT NULL,
    local VARCHAR(255) NOT NULL,
    palestrante VARCHAR(255) NOT NULL,
    presidente VARCHAR(255) NOT NULL,
    vice_presidente VARCHAR(255) NOT NULL,
    data_evento DATETIME NOT NULL,
    status ENUM('Aberto', 'Rascunho', 'Encerrado') DEFAULT 'Rascunho',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    tipo ENUM('Acadêmico', 'Profissional de Saúde') NOT NULL,
    instituicao VARCHAR(255),
    semestre_atuacao VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscricoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_inscrito INT NOT NULL,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmada', 'Cancelada') DEFAULT 'Confirmada',
    FOREIGN KEY (id_curso) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_inscrito) REFERENCES inscritos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inscricao (id_curso, id_inscrito),
    INDEX idx_curso (id_curso),
    INDEX idx_data (data_inscricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Adiciona as colunas novas apenas se ainda não existirem ----------
-- (usa INFORMATION_SCHEMA porque nem todo MySQL/MariaDB da Hostinger
--  aceita "ADD COLUMN IF NOT EXISTS")

SET @db := DATABASE();

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN data_fim_evento DATETIME NULL',
  'SELECT "coluna data_fim_evento ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='data_fim_evento');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN inscricao_inicio DATETIME NULL',
  'SELECT "coluna inscricao_inicio ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='inscricao_inicio');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN inscricao_fim DATETIME NULL',
  'SELECT "coluna inscricao_fim ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='inscricao_fim');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN vagas_limite INT NOT NULL DEFAULT 0',
  'SELECT "coluna vagas_limite ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='vagas_limite');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN emitir_certificado TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT "coluna emitir_certificado ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='emitir_certificado');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE cursos ADD COLUMN certificado_arquivo VARCHAR(255) NULL',
  'SELECT "coluna certificado_arquivo ja existe"')
  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='cursos' AND COLUMN_NAME='certificado_arquivo');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 3) Conferência rápida ------------------------------------------------
-- Rode depois da migração para conferir as colunas:
-- SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
--  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cursos';
