-- Aplicar depois de 003_estrutura_candidatos.sql.
-- Cada edição cria uma revisão imutável das respostas do formulário.
ALTER TABLE `candidato_formularios`
  DROP INDEX `uk_candidato_formularios_candidato`,
  ADD KEY `idx_candidato_formularios_candidato_revisao` (`candidato_codigo`, `codigo`);

CREATE TABLE `candidato_recuperacoes` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_codigo` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_em` datetime NOT NULL,
  `utilizado_em` datetime DEFAULT NULL,
  `cadastro` datetime NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_candidato_recuperacoes_token` (`token_hash`),
  KEY `idx_candidato_recuperacoes_candidato` (`candidato_codigo`, `cadastro`),
  CONSTRAINT `fk_candidato_recuperacoes_candidato`
    FOREIGN KEY (`candidato_codigo`) REFERENCES `candidatos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
