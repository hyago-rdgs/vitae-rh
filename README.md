# Vitae RH

Sistema open source para apoiar a gestão de candidatos e as atividades de Recursos Humanos.

## Sobre o projeto

O Vitae RH permite configurar os dados solicitados aos candidatos, publicar uma estrutura versionada de perfil e disponibilizar o cadastro e a manutenção desse perfil em um portal.

O escopo inicial inclui dados pessoais, escolaridade, experiências profissionais, idiomas, áreas de interesse, foto e currículo, conforme o formulário configurado. A área administrativa consulta e filtra candidatos, acompanha o processo seletivo e envia mensagens por e-mail. A integração com WhatsApp permanece para uma etapa futura.

## Estado atual

O código contempla:

- Autenticação e controle de acesso por perfis e permissões.
- Gerenciamento administrativo de usuários e perfis.
- Auditoria de ações administrativas.
- Configuração do formulário global do perfil, organizado em seções, grupos e campos.
- Grupos configuráveis como repetíveis, para dados que podem ter mais de uma ocorrência.
- Campos de texto curto, texto longo, número, data, sim/não, seleção única e múltipla seleção.
- Opções, obrigatoriedade, validações, filtros e ordenação de campos.
- Pré-visualização da estrutura configurada.
- Publicação versionada, com registro das versões e cópia da estrutura publicada.
- Cadastro do candidato, autenticação, recuperação de senha e atualização do perfil no portal.
- Foto e currículo com acesso controlado.
- Consulta administrativa com filtros, paginação e visualização do perfil publicado.
- Situação seletiva e anotações internas com histórico.
- Modelos de mensagem, envio individual ou em lote por e-mail e histórico de envios.

Estas funcionalidades dependem da aplicação das evoluções do banco e, para e-mails reais, da configuração SMTP no ambiente. A validação integrada em execução ainda está pendente.

## Módulos

| Módulo | Responsabilidade |
| --- | --- |
| Autenticação | Entrada de usuários administrativos no sistema. |
| Usuários | Cadastro, edição e gerenciamento de acesso dos usuários. |
| Perfis e permissões | Definição de perfis e das operações autorizadas para cada perfil. |
| Configuração do perfil | Gerenciamento de seções, grupos, campos e opções do formulário. |
| Publicações | Validação e publicação de versões do formulário global. |
| Portal do candidato | Cadastro, acesso, recuperação de senha, edição de perfil e arquivos. |
| Candidatos | Busca, filtros, detalhes e acesso protegido a foto e currículo. |
| Processo seletivo | Situação e anotações internas com histórico. |
| Comunicação | Modelos de e-mail, envios e histórico por candidato. |
| Auditoria | Registro das ações administrativas relevantes. |

## Modelo de configuração do perfil

O formulário é global: há uma estrutura publicada por vez, composta por seções, grupos e campos. As seções organizam o perfil em blocos temáticos; os grupos organizam os campos e podem ser configurados como repetíveis, por exemplo, para registrar várias experiências profissionais ou formações.

As alterações são preparadas na configuração administrativa, conferidas na pré-visualização e disponibilizadas por uma publicação versionada. Cada cadastro conserva a referência à estrutura publicada utilizada.

## Pendências para ativação e homologação

- Em uma cópia de segurança do banco, conferir as evoluções já aplicadas e executar as pendentes em ordem: `database/003_estrutura_candidatos.sql`, `004_permissao_consulta_candidatos.sql`, `005_portal_candidato.sql`, `006_gestao_processo_seletivo.sql` e `007_comunicacao_candidatos.sql`. Os scripts 006 e 007 não foram executados neste ambiente; não reaplique migrações já existentes sem verificar o esquema.
- Configurar `application/config/email.php` a partir de `application/config/email.php.example` com os dados SMTP do ambiente e definir `VITAE_EMAIL_REMETENTE` com um endereço autorizado pelo provedor. O arquivo local com credenciais é ignorado pelo Git. Validar o remetente e o transporte antes de enviar mensagens reais.
- Homologar com uma publicação ativa: cadastro, login e recuperação de senha; edição do perfil e arquivos; filtros e detalhes na área administrativa; mudança de situação, anotações, envio individual e envio em lote com poucos destinatários de teste. Verificar permissões e registros de auditoria/histórico, inclusive falhas de SMTP.
- Confirmar a política de tratamento de dados pessoais e o conteúdo do consentimento LGPD antes de disponibilizar o portal publicamente.

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

## Depois da homologação

Integração com WhatsApp, automação de comunicação e melhorias de escala para envios em lote permanecem fora da entrega inicial de e-mail manual. Antes do uso em produção, revisar também proteção contra CSRF, limites operacionais de envio e observabilidade do transporte.

## Licença

O arquivo `license.txt` contém a licença MIT do CodeIgniter incluído no repositório. A licença própria do Vitae RH deverá ser definida pelo projeto.
