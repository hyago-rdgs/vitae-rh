INSERT INTO `modulos` (`nome`, `chave`, `descricao`, `ordem`)
SELECT
  'Candidatos',
  'candidatos',
  'Consulta dos perfis cadastrados pelos candidatos.',
  80
WHERE NOT EXISTS (
  SELECT 1
  FROM `modulos`
  WHERE `chave` = 'candidatos'
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
  'Consultar',
  'candidatos.consultar',
  'Consultar perfis, fotos e currículos dos candidatos.',
  10
FROM `modulos`
WHERE `chave` = 'candidatos'
  AND NOT EXISTS (
    SELECT 1
    FROM `permissoes`
    WHERE `chave` = 'candidatos.consultar'
  );

INSERT INTO `perfil_permissoes` (`perfil_codigo`, `permissao_codigo`)
SELECT
  `perfis`.`codigo`,
  `permissoes`.`codigo`
FROM `perfis`
INNER JOIN `permissoes`
  ON `permissoes`.`chave` = 'candidatos.consultar'
WHERE `perfis`.`chave` = 'administrador'
  AND NOT EXISTS (
    SELECT 1
    FROM `perfil_permissoes`
    WHERE `perfil_permissoes`.`perfil_codigo` = `perfis`.`codigo`
      AND `perfil_permissoes`.`permissao_codigo` = `permissoes`.`codigo`
  );
