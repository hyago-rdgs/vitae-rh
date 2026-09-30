CREATE TABLE `candidatos` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `foto_arquivo` varchar(255) NOT NULL,
  `curriculo_arquivo` varchar(255) NOT NULL,
  `curriculo_nome_original` varchar(255) NOT NULL,
  `curriculo_mime` varchar(100) DEFAULT NULL,
  `curriculo_tamanho` bigint(20) UNSIGNED DEFAULT NULL,
  `consentimento_lgpd` tinyint(1) NOT NULL,
  `consentimento_lgpd_versao` varchar(30) NOT NULL,
  `consentimento_lgpd_em` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'ativo',
  `ultimo_acesso` datetime DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  `exclusao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_candidatos_email` (`email`),
  KEY `idx_candidatos_status` (`status`),
  KEY `idx_candidatos_exclusao` (`exclusao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidato_formularios` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_codigo` bigint(20) UNSIGNED NOT NULL,
  `formulario_publicacao_codigo` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'rascunho',
  `iniciado_em` datetime DEFAULT NULL,
  `concluido_em` datetime DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_candidato_formularios_candidato` (`candidato_codigo`),
  KEY `idx_candidato_formularios_publicacao` (`formulario_publicacao_codigo`),
  CONSTRAINT `fk_candidato_formularios_candidato`
    FOREIGN KEY (`candidato_codigo`) REFERENCES `candidatos` (`codigo`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_candidato_formularios_publicacao`
    FOREIGN KEY (`formulario_publicacao_codigo`) REFERENCES `formulario_publicacoes` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidato_grupos` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_formulario_codigo` bigint(20) UNSIGNED NOT NULL,
  `grupo_codigo` bigint(20) UNSIGNED NOT NULL,
  `indice` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_candidato_grupos_ocorrencia` (`candidato_formulario_codigo`, `grupo_codigo`, `indice`),
  KEY `idx_candidato_grupos_formulario` (`candidato_formulario_codigo`),
  CONSTRAINT `fk_candidato_grupos_formulario`
    FOREIGN KEY (`candidato_formulario_codigo`) REFERENCES `candidato_formularios` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidato_respostas` (
  `codigo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidato_grupo_codigo` bigint(20) UNSIGNED NOT NULL,
  `campo_chave` varchar(100) NOT NULL,
  `valor_texto` text DEFAULT NULL,
  `valor_numero` decimal(15,4) DEFAULT NULL,
  `valor_data` date DEFAULT NULL,
  `valor_booleano` tinyint(1) DEFAULT NULL,
  `valor_json` longtext DEFAULT NULL,
  `cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `uk_candidato_respostas_campo` (`candidato_grupo_codigo`, `campo_chave`),
  KEY `idx_candidato_respostas_campo` (`campo_chave`),
  KEY `idx_candidato_respostas_texto` (`campo_chave`, `valor_texto`(100)),
  KEY `idx_candidato_respostas_numero` (`campo_chave`, `valor_numero`),
  KEY `idx_candidato_respostas_data` (`campo_chave`, `valor_data`),
  CONSTRAINT `fk_candidato_respostas_grupo`
    FOREIGN KEY (`candidato_grupo_codigo`) REFERENCES `candidato_grupos` (`codigo`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
