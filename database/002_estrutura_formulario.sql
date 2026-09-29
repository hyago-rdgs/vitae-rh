CREATE TABLE `formularios` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `chave` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_formularios_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `formulario_publicacoes` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `formulario_codigo` bigint(20) UNSIGNED NOT NULL,
  `versao` int(10) UNSIGNED NOT NULL,
  `estrutura` longtext NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `usuario_codigo` bigint(20) UNSIGNED DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_formulario_publicacoes_versao` (`formulario_codigo`, `versao`),
  KEY `idx_formulario_publicacoes_usuario` (`usuario_codigo`),
  CONSTRAINT `fk_formulario_publicacoes_formulario`
    FOREIGN KEY (`formulario_codigo`) REFERENCES `formularios` (`codigo`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_formulario_publicacoes_usuario`
    FOREIGN KEY (`usuario_codigo`) REFERENCES `usuarios` (`codigo`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `formulario_secoes` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `formulario_codigo` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `idx_formulario_secoes_formulario` (`formulario_codigo`),
  KEY `idx_formulario_secoes_ordem` (`formulario_codigo`, `ordem`),
  CONSTRAINT `fk_formulario_secoes_formulario`
    FOREIGN KEY (`formulario_codigo`) REFERENCES `formularios` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `formulario_grupos` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `secao_codigo` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `repetivel` tinyint(1) NOT NULL DEFAULT 0,
  `quantidade_minima` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `quantidade_maxima` smallint(5) UNSIGNED DEFAULT 1,
  `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `idx_formulario_grupos_secao` (`secao_codigo`),
  KEY `idx_formulario_grupos_ordem` (`secao_codigo`, `ordem`),
  CONSTRAINT `fk_formulario_grupos_secao`
    FOREIGN KEY (`secao_codigo`) REFERENCES `formulario_secoes` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `formulario_campos` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `grupo_codigo` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `placeholder` varchar(150) DEFAULT NULL,
  `texto_ajuda` varchar(255) DEFAULT NULL,
  `obrigatorio` tinyint(1) NOT NULL DEFAULT 0,
  `filtravel` tinyint(1) NOT NULL DEFAULT 0,
  `tamanho_maximo` int(10) UNSIGNED DEFAULT NULL,
  `numero_minimo` decimal(15,4) DEFAULT NULL,
  `numero_maximo` decimal(15,4) DEFAULT NULL,
  `data_minima` date DEFAULT NULL,
  `data_maxima` date DEFAULT NULL,
  `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `publicado` tinyint(1) NOT NULL DEFAULT 0,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_formulario_campos_chave` (`chave`),
  KEY `idx_formulario_campos_grupo` (`grupo_codigo`),
  KEY `idx_formulario_campos_ordem` (`grupo_codigo`, `ordem`),
  KEY `idx_formulario_campos_filtravel` (`filtravel`),
  CONSTRAINT `fk_formulario_campos_grupo`
    FOREIGN KEY (`grupo_codigo`) REFERENCES `formulario_grupos` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `campo_opcoes` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campo_codigo` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `valor` varchar(100) NOT NULL,
  `ordem` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_campo_opcoes_valor` (`campo_codigo`, `valor`),
  KEY `idx_campo_opcoes_ordem` (`campo_codigo`, `ordem`),
  CONSTRAINT `fk_campo_opcoes_campo`
    FOREIGN KEY (`campo_codigo`) REFERENCES `formulario_campos` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `formularios` (`chave`, `nome`, `descricao`)
VALUES (
  'perfil_candidato',
  'Perfil do candidato',
  'Estrutura global utilizada no cadastro e na atualização do perfil do candidato.'
);

INSERT INTO `modulos` (`nome`, `chave`, `descricao`, `ordem`)
SELECT
  'Configuração do perfil',
  'formularios',
  'Configuração da estrutura do perfil dos candidatos.',
  70
WHERE NOT EXISTS (
  SELECT 1
  FROM `modulos`
  WHERE `chave` = 'formularios'
);

INSERT INTO `permissoes` (
  `modulo_codigo`,
  `nome`,
  `chave`,
  `descricao`,
  `ordem`
)
SELECT
  `codigo`,
  'Gerenciar',
  'formularios.gerenciar',
  'Configurar seções, grupos, campos e opções do perfil.',
  10
FROM `modulos`
WHERE `chave` = 'formularios'
  AND NOT EXISTS (
    SELECT 1
    FROM `permissoes`
    WHERE `chave` = 'formularios.gerenciar'
  );

INSERT INTO `permissoes` (
  `modulo_codigo`,
  `nome`,
  `chave`,
  `descricao`,
  `ordem`
)
SELECT
  `codigo`,
  'Publicar',
  'formularios.publicar',
  'Publicar uma nova estrutura do perfil dos candidatos.',
  20
FROM `modulos`
WHERE `chave` = 'formularios'
  AND NOT EXISTS (
    SELECT 1
    FROM `permissoes`
    WHERE `chave` = 'formularios.publicar'
  );

INSERT INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT
  `perfis`.`codigo`,
  `permissoes`.`codigo`
FROM `perfis`
INNER JOIN `permissoes`
  ON `permissoes`.`chave` IN (
    'formularios.gerenciar',
    'formularios.publicar'
  )
WHERE `perfis`.`chave` = 'administrador'
  AND NOT EXISTS (
    SELECT 1
    FROM `perfil_permissoes`
    WHERE `perfil_permissoes`.`perfil_codigo` = `perfis`.`codigo`
      AND `perfil_permissoes`.`permissao_codigo` = `permissoes`.`codigo`
  );
