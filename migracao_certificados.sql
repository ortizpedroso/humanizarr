-- Migração: colunas necessárias para datas de inscrição e certificados
-- Execute no phpMyAdmin da Hostinger (banco do HumanizaMais) ANTES de usar o painel.
ALTER TABLE cursos
  ADD COLUMN IF NOT EXISTS data_fim_evento    DATETIME     NULL,
  ADD COLUMN IF NOT EXISTS inscricao_inicio   DATETIME     NULL,
  ADD COLUMN IF NOT EXISTS inscricao_fim      DATETIME     NULL,
  ADD COLUMN IF NOT EXISTS vagas_limite       INT          NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS emitir_certificado TINYINT(1)   NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS certificado_arquivo VARCHAR(255) NULL;
