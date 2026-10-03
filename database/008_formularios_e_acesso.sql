-- Aplicar após 007. Preserva publicações anteriores para interpretar respostas.
ALTER TABLE formulario_publicacoes ADD COLUMN exclusao datetime DEFAULT NULL;

CREATE TABLE formulario_vigencia (
  codigo tinyint UNSIGNED NOT NULL PRIMARY KEY,
  publicacao_codigo bigint(20) UNSIGNED DEFAULT NULL,
  CONSTRAINT fk_vigencia_publicacao FOREIGN KEY (publicacao_codigo)
    REFERENCES formulario_publicacoes (codigo)
) ENGINE=InnoDB;

INSERT INTO formulario_vigencia (codigo, publicacao_codigo)
SELECT 1, MAX(p.codigo) FROM formulario_publicacoes p
INNER JOIN formularios f ON f.codigo = p.formulario_codigo
WHERE f.chave = 'perfil_candidato';

CREATE TABLE usuario_recuperacoes (
  codigo bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_codigo bigint(20) UNSIGNED NOT NULL,
  token_hash char(64) NOT NULL,
  expira_em datetime NOT NULL,
  utilizado_em datetime DEFAULT NULL,
  cadastro datetime NOT NULL,
  UNIQUE KEY uk_usuario_recuperacao_token (token_hash),
  KEY idx_usuario_recuperacao (usuario_codigo, cadastro),
  CONSTRAINT fk_usuario_recuperacao FOREIGN KEY (usuario_codigo)
    REFERENCES usuarios (codigo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
