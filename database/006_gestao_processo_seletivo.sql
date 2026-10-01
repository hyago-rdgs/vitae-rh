-- Aplicar depois de 004_permissao_consulta_candidatos.sql e 005_portal_candidato.sql.
-- A situação seletiva é separada do status da conta do candidato.
ALTER TABLE `candidatos`
  ADD COLUMN `situacao_seletiva` varchar(30) NOT NULL DEFAULT 'recebido' AFTER `status`,
  ADD KEY `idx_candidatos_situacao_seletiva` (`situacao_seletiva`);

CREATE TABLE `candidato_historicos` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_codigo` bigint(20) UNSIGNED NOT NULL,
  `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `situacao_anterior` varchar(30) DEFAULT NULL,
  `situacao_nova` varchar(30) DEFAULT NULL,
  `anotacao` text,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`),
  KEY `idx_candidato_historicos_candidato` (`candidato_codigo`, `codigo`),
  KEY `idx_candidato_historicos_usuario` (`usuario_codigo`),
  CONSTRAINT `fk_candidato_historicos_candidato`
    FOREIGN KEY (`candidato_codigo`) REFERENCES `candidatos` (`codigo`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_candidato_historicos_usuario`
    FOREIGN KEY (`usuario_codigo`) REFERENCES `usuarios` (`codigo`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissoes` (
  `modulo_codigo`, `nome`, `chave`, `descricao`, `ordem`
)
SELECT
  `codigo`,
  'Gerenciar processo seletivo',
  'candidatos.gerenciar',
  'Atualizar situação seletiva e registrar anotações internas de candidatos.',
  20
FROM `modulos`
WHERE `chave` = 'candidatos'
  AND NOT EXISTS (
    SELECT 1 FROM `permissoes` WHERE `chave` = 'candidatos.gerenciar'
  );

INSERT INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT `perfis`.`codigo`, `permissoes`.`codigo`
FROM `perfis`
INNER JOIN `permissoes`
  ON `permissoes`.`chave` = 'candidatos.gerenciar'
WHERE `perfis`.`chave` = 'administrador'
  AND NOT EXISTS (
    SELECT 1
    FROM `perfil_permissoes`
    WHERE `perfil_permissoes`.`perfil_codigo` = `perfis`.`codigo`
      AND `perfil_permissoes`.`permissao_codigo` = `permissoes`.`codigo`
  );
