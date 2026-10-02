-- Aplicar depois de 006_gestao_processo_seletivo.sql.

CREATE TABLE `candidato_mensagens` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `idx_candidato_mensagens_ativo` (`ativo`, `exclusao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidato_envios` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_codigo` bigint(20) UNSIGNED NOT NULL,
  `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `modelo_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `destinatario` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `erro` varchar(500) DEFAULT NULL,
  `enviado_em` datetime DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`),
  KEY `idx_candidato_envios_candidato` (`candidato_codigo`, `codigo`),
  KEY `idx_candidato_envios_status` (`status`),
  CONSTRAINT `fk_candidato_envios_candidato`
    FOREIGN KEY (`candidato_codigo`) REFERENCES `candidatos` (`codigo`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_candidato_envios_usuario`
    FOREIGN KEY (`usuario_codigo`) REFERENCES `usuarios` (`codigo`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_candidato_envios_modelo`
    FOREIGN KEY (`modelo_codigo`) REFERENCES `candidato_mensagens` (`codigo`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissoes` (
  `modulo_codigo`, `nome`, `chave`, `descricao`, `ordem`
)
SELECT
  `codigo`,
  'Comunicar',
  'candidatos.comunicar',
  'Gerenciar modelos e enviar mensagens aos candidatos.',
  30
FROM `modulos`
WHERE `chave` = 'candidatos'
  AND NOT EXISTS (
    SELECT 1 FROM `permissoes` WHERE `chave` = 'candidatos.comunicar'
  );

INSERT INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT `perfis`.`codigo`, `permissoes`.`codigo`
FROM `perfis`
INNER JOIN `permissoes`
  ON `permissoes`.`chave` = 'candidatos.comunicar'
WHERE `perfis`.`chave` = 'administrador'
  AND NOT EXISTS (
    SELECT 1 FROM `perfil_permissoes`
    WHERE `perfil_permissoes`.`perfil_codigo` = `perfis`.`codigo`
      AND `perfil_permissoes`.`permissao_codigo` = `permissoes`.`codigo`
  );
