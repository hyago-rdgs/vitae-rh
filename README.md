# Vitae RH

Sistema open source para apoiar a gestão de candidatos e as atividades de Recursos Humanos.

## Sobre o projeto

O Vitae RH está sendo desenvolvido para permitir que empresas configurem os dados que desejam coletar dos candidatos, publiquem uma estrutura única de perfil e, nas próximas etapas, ofereçam o cadastro e a manutenção desse perfil em um portal.

O escopo inicial prevê dados pessoais, escolaridade, experiências profissionais, idiomas, áreas de interesse, foto de perfil e currículo. A área administrativa deverá permitir consultar e filtrar candidatos e acessar os detalhes do perfil e seus arquivos. Integrações para contato por e-mail e WhatsApp estão previstas para etapas futuras.

## Estado atual

Foi concluída a fase de configuração administrativa do perfil do candidato. Já estão disponíveis:

- Autenticação e controle de acesso por perfis e permissões.
- Gerenciamento administrativo de usuários e perfis.
- Auditoria de ações administrativas.
- Configuração do formulário global do perfil, organizado em seções, grupos e campos.
- Grupos configuráveis como repetíveis, para dados que podem ter mais de uma ocorrência.
- Campos de texto curto, texto longo, número, data, sim/não, seleção única e múltipla seleção.
- Opções, obrigatoriedade, validações, filtros e ordenação de campos.
- Pré-visualização da estrutura configurada.
- Publicação versionada, com registro das versões e cópia da estrutura publicada.

O portal do candidato, o cadastro efetivo de candidatos e a consulta administrativa dos candidatos ainda fazem parte das etapas seguintes.

## Módulos

| Módulo | Responsabilidade |
| --- | --- |
| Autenticação | Entrada de usuários administrativos no sistema. |
| Usuários | Cadastro, edição e gerenciamento de acesso dos usuários. |
| Perfis e permissões | Definição de perfis e das operações autorizadas para cada perfil. |
| Configuração do perfil | Gerenciamento de seções, grupos, campos e opções do formulário. |
| Publicações | Validação e publicação de versões do formulário global. |
| Auditoria | Registro das ações administrativas relevantes. |

## Modelo de configuração do perfil

O formulário é global: há uma estrutura publicada por vez, composta por seções, grupos e campos. As seções organizam o perfil em blocos temáticos; os grupos organizam os campos e podem ser configurados como repetíveis, por exemplo, para registrar várias experiências profissionais ou formações.

As alterações são preparadas na configuração administrativa, podem ser conferidas na pré-visualização e são disponibilizadas por meio de uma publicação versionada. A publicação mantém uma cópia da estrutura daquela versão para servir de referência ao cadastro do candidato nas próximas etapas do projeto.

## Tecnologias

- PHP 7.4, conforme a faixa definida no `composer.json`.
- CodeIgniter 3.
- MySQL ou MariaDB.
- HTML, CSS e JavaScript.
- Bootstrap 5 e jQuery na interface administrativa.

## Estrutura do projeto

```text
application/
  config/        Configuração da aplicação
  controllers/   Controladores e fluxos administrativos
  libraries/     Controle de acesso e auditoria
  models/        Acesso e regras de dados
  views/         Interfaces do sistema
database/        Scripts de estrutura e evolução do banco
system/          Núcleo do CodeIgniter
```

## Padrões adotados

- Nomes de classes e arquivos seguem o padrão existente no CodeIgniter 3.
- Controladores recebem e validam as requisições e coordenam as operações dos modelos.
- Regras de persistência e consultas ficam nos modelos.
- Ações administrativas relevantes são registradas na auditoria.
- Alterações de estrutura e publicação do formulário devem preservar a integridade dos dados e das versões.
- O banco usa exclusão lógica nos cadastros configuráveis, mantendo o histórico necessário para auditoria e publicações.

## Próximas etapas

1. Implementar o cadastro e o portal de acesso do candidato, incluindo dados fixos, campos configurados, foto e currículo.
2. Implementar a área de consulta e gestão de candidatos para o RH, com filtros e visualização detalhada.
3. Desenvolver interfaces de integração para contato por e-mail e WhatsApp.
4. Consolidar validações, segurança, auditoria e documentação do projeto.

## Licença

O arquivo `license.txt` contém a licença MIT do CodeIgniter incluído no repositório. A licença própria do Vitae RH deverá ser definida pelo projeto.
