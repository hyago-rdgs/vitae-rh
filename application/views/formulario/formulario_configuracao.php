<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Configuração do perfil dos candidatos">
    <title>Configuração do perfil | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <h1 class="h3 mb-1">Configuração do perfil</h1>
                <p class="text-body-secondary mb-0">
                    Organize as informações solicitadas durante o cadastro dos candidatos.
                </p>
            </section>

            <button class="btn btn-success" type="button" id="nova_secao"
                data-bs-toggle="modal" data-bs-target="#modalSecao">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                Nova seção
            </button>
        </header>

        <section class="alert alert-light border shadow-sm mb-4" aria-labelledby="estrutura-title">
            <h2 class="h6 fw-semibold mb-2" id="estrutura-title">Como o perfil é organizado</h2>
            <p class="small text-secondary mb-0">
                <strong>Seções</strong> dividem o formulário em etapas,
                <strong>grupos</strong> reúnem informações relacionadas e
                <strong>campos</strong> são as perguntas respondidas pelo candidato.
            </p>
        </section>

        <form id="formulario_configuracao" method="post" novalidate>
            <div id="alerta-configuracao" class="alert alert-danger d-none" role="alert"></div>

            <section class="card border shadow-sm mb-4" aria-labelledby="configuracao-geral-title">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1" id="configuracao-geral-title">Informações gerais</h2>
                    <p class="small text-secondary mb-4">
                        Identifique a estrutura global utilizada no perfil dos candidatos.
                    </p>

                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <label class="form-label" for="nome">Nome</label>
                            <input class="form-control" id="nome" name="nome"
                                maxlength="100" required type="text"
                                value="<?= html_escape($formulario['nome']); ?>">
                        </div>

                        <div class="col-12 col-lg-7">
                            <label class="form-label" for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao"
                                maxlength="2000" rows="2"><?= html_escape($formulario['descricao'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-success" type="submit" id="salvar_configuracao">
                            Salvar configurações
                        </button>
                    </div>
                </div>
            </section>
        </form>

        <div id="alerta-secoes" class="alert alert-danger d-none" role="alert"></div>

        <section class="card border shadow-sm" aria-labelledby="secoes-title">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-semibold mb-1" id="secoes-title">Seções</h2>
                <p class="small text-secondary mb-0">
                    As seções organizam os grupos e campos exibidos ao candidato.
                </p>
            </div>

            <?php if (!empty($secoes)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($secoes as $indice => $secao): ?>
                        <article class="list-group-item p-3 p-lg-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="badge text-bg-light border mt-1">
                                        <?= (int) $secao['ordem']; ?>
                                    </span>

                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h3 class="h6 fw-semibold mb-0">
                                                <?= html_escape($secao['titulo']); ?>
                                            </h3>

                                            <?php if ((int) $secao['ativo'] === 1): ?>
                                                <span class="badge text-bg-success">Ativa</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Inativa</span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($secao['descricao'])): ?>
                                            <p class="text-secondary mb-2">
                                                <?= html_escape($secao['descricao']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <span class="small text-secondary">
                                            <?= (int) $secao['total_grupos']; ?> grupos cadastrados
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-lg-end align-items-start gap-2">
                                    <button class="btn btn-sm btn-light border mover-secao" type="button"
                                        data-codigo="<?= $secao['codigo']; ?>" data-direcao="subir"
                                        <?= $indice === 0 ? 'disabled' : ''; ?>
                                        aria-label="Mover <?= html_escape($secao['titulo']); ?> para cima">
                                        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light border mover-secao" type="button"
                                        data-codigo="<?= $secao['codigo']; ?>" data-direcao="descer"
                                        <?= $indice === count($secoes) - 1 ? 'disabled' : ''; ?>
                                        aria-label="Mover <?= html_escape($secao['titulo']); ?> para baixo">
                                        <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light border editar-secao" type="button"
                                        data-codigo="<?= $secao['codigo']; ?>"
                                        data-titulo="<?= html_escape($secao['titulo']); ?>"
                                        data-descricao="<?= html_escape($secao['descricao'] ?? ''); ?>"
                                        data-ativo="<?= (int) $secao['ativo']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalSecao">
                                        <i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>
                                        Editar
                                    </button>

                                    <button class="btn btn-sm btn-light border text-danger excluir-secao" type="button"
                                        data-codigo="<?= $secao['codigo']; ?>"
                                        data-titulo="<?= html_escape($secao['titulo']); ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalExcluirSecao"
                                        aria-label="Excluir <?= html_escape($secao['titulo']); ?>">
                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <?php $grupos_secao = $grupos_por_secao[$secao['codigo']] ?? []; ?>

                            <div class="border rounded mt-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 border-bottom">
                                    <div>
                                        <h4 class="h6 fw-semibold mb-1">Grupos da seção</h4>
                                        <p class="small text-secondary mb-0">
                                            Reúna campos relacionados, como dados pessoais ou escolaridade.
                                        </p>
                                    </div>

                                    <button class="btn btn-sm btn-success novo-grupo" type="button"
                                        data-secao-codigo="<?= $secao['codigo']; ?>"
                                        data-secao-titulo="<?= html_escape($secao['titulo']); ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalGrupo">
                                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>
                                        Novo grupo
                                    </button>
                                </div>

                                <?php if (!empty($grupos_secao)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($grupos_secao as $indice_grupo => $grupo): ?>
                                            <div class="list-group-item p-3">
                                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                                    <div class="d-flex align-items-start gap-3">
                                                        <span class="badge text-bg-light border mt-1">
                                                            <?= (int) $grupo['ordem']; ?>
                                                        </span>

                                                        <div>
                                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                                <h5 class="h6 mb-0">
                                                                    <?= html_escape($grupo['nome']); ?>
                                                                </h5>

                                                                <?php if ((int) $grupo['ativo'] === 1): ?>
                                                                    <span class="badge text-bg-success">Ativo</span>
                                                                <?php else: ?>
                                                                    <span class="badge text-bg-secondary">Inativo</span>
                                                                <?php endif; ?>

                                                                <?php if ((int) $grupo['repetivel'] === 1): ?>
                                                                    <span class="badge text-bg-info">
                                                                        Repetível:
                                                                        <?= (int) $grupo['quantidade_minima']; ?> a
                                                                        <?= (int) $grupo['quantidade_maxima']; ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <?php if (!empty($grupo['descricao'])): ?>
                                                                <p class="small text-secondary mb-1">
                                                                    <?= html_escape($grupo['descricao']); ?>
                                                                </p>
                                                            <?php endif; ?>

                                                            <span class="small text-secondary">
                                                                <?= (int) $grupo['total_campos']; ?> campos cadastrados
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-wrap justify-content-lg-end align-items-start gap-2">
                                                        <button class="btn btn-sm btn-light border mover-grupo" type="button"
                                                            data-codigo="<?= $grupo['codigo']; ?>" data-direcao="subir"
                                                            <?= $indice_grupo === 0 ? 'disabled' : ''; ?>
                                                            aria-label="Mover <?= html_escape($grupo['nome']); ?> para cima">
                                                            <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-light border mover-grupo" type="button"
                                                            data-codigo="<?= $grupo['codigo']; ?>" data-direcao="descer"
                                                            <?= $indice_grupo === count($grupos_secao) - 1 ? 'disabled' : ''; ?>
                                                            aria-label="Mover <?= html_escape($grupo['nome']); ?> para baixo">
                                                            <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-light border editar-grupo" type="button"
                                                            data-codigo="<?= $grupo['codigo']; ?>"
                                                            data-secao-titulo="<?= html_escape($secao['titulo']); ?>"
                                                            data-nome="<?= html_escape($grupo['nome']); ?>"
                                                            data-descricao="<?= html_escape($grupo['descricao'] ?? ''); ?>"
                                                            data-repetivel="<?= (int) $grupo['repetivel']; ?>"
                                                            data-quantidade-minima="<?= (int) $grupo['quantidade_minima']; ?>"
                                                            data-quantidade-maxima="<?= (int) $grupo['quantidade_maxima']; ?>"
                                                            data-ativo="<?= (int) $grupo['ativo']; ?>"
                                                            data-bs-toggle="modal" data-bs-target="#modalGrupo">
                                                            <i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>
                                                            Editar
                                                        </button>

                                                        <button class="btn btn-sm btn-light border text-danger excluir-grupo" type="button"
                                                            data-codigo="<?= $grupo['codigo']; ?>"
                                                            data-nome="<?= html_escape($grupo['nome']); ?>"
                                                            data-bs-toggle="modal" data-bs-target="#modalExcluirGrupo"
                                                            aria-label="Excluir <?= html_escape($grupo['nome']); ?>">
                                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 text-center text-secondary small">
                                        Nenhum grupo cadastrado nesta seção.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-table-list fa-2x text-secondary mb-3" aria-hidden="true"></i>
                    <h3 class="h5 fw-semibold">Nenhuma seção cadastrada</h3>
                    <p class="text-secondary mb-4">
                        Cadastre a primeira seção para começar a estruturar o perfil.
                    </p>
                    <button class="btn btn-success" type="button" data-bs-toggle="modal"
                        data-bs-target="#modalSecao" id="cadastrar_primeira_secao">
                        Cadastrar seção
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalSecao" tabindex="-1" aria-labelledby="modalSecaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modalSecaoLabel">Nova seção</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_secao" method="post" novalidate>
                    <div class="modal-body">
                        <div id="alerta-secao" class="alert alert-danger d-none" role="alert"></div>

                        <div class="mb-3">
                            <label class="form-label" for="titulo_secao">Título</label>
                            <input class="form-control" id="titulo_secao" name="titulo"
                                maxlength="100" required type="text">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="descricao_secao">Descrição</label>
                            <textarea class="form-control" id="descricao_secao" name="descricao"
                                maxlength="2000" rows="3"></textarea>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" id="ativo_secao" name="ativo"
                                type="checkbox" value="1" checked>
                            <label class="form-check-label" for="ativo_secao">Seção ativa</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-success" type="submit" id="salvar_secao">
                            Cadastrar seção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExcluirSecao" tabindex="-1"
        aria-labelledby="modalExcluirSecaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5" id="modalExcluirSecaoLabel">Excluir seção</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_exclusao_secao" method="post">
                    <div class="modal-body">
                        <div id="alerta-exclusao-secao" class="alert alert-danger d-none" role="alert"></div>
                        <p class="mb-2">Deseja realmente excluir esta seção?</p>
                        <p class="fw-semibold mb-0" id="titulo_secao_exclusao"></p>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-danger" type="submit" id="confirmar_exclusao_secao">
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-labelledby="modalGrupoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="modalGrupoLabel">Novo grupo</h2>
                        <p class="small text-secondary mb-0" id="grupo_secao_titulo"></p>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_grupo" method="post" novalidate>
                    <input id="grupo_secao_codigo" name="secao_codigo" type="hidden">

                    <div class="modal-body">
                        <div id="alerta-grupo" class="alert alert-danger d-none" role="alert"></div>

                        <div class="mb-3">
                            <label class="form-label" for="nome_grupo">Nome</label>
                            <input class="form-control" id="nome_grupo" name="nome"
                                maxlength="100" required type="text">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="descricao_grupo">Descrição</label>
                            <textarea class="form-control" id="descricao_grupo" name="descricao"
                                maxlength="2000" rows="3"></textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" id="repetivel_grupo" name="repetivel"
                                type="checkbox" value="1">
                            <label class="form-check-label" for="repetivel_grupo">Grupo repetível</label>
                            <div class="form-text">
                                Permite ao candidato cadastrar mais de um registro, como experiências profissionais.
                            </div>
                        </div>

                        <div class="row g-3 mb-3 d-none" id="limites_grupo">
                            <div class="col-6">
                                <label class="form-label" for="quantidade_minima_grupo">Quantidade mínima</label>
                                <input class="form-control" id="quantidade_minima_grupo"
                                    name="quantidade_minima" min="0" max="65535"
                                    step="1" type="number" value="0" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label" for="quantidade_maxima_grupo">Quantidade máxima</label>
                                <input class="form-control" id="quantidade_maxima_grupo"
                                    name="quantidade_maxima" min="1" max="65535"
                                    step="1" type="number" value="1" disabled>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" id="ativo_grupo" name="ativo"
                                type="checkbox" value="1" checked>
                            <label class="form-check-label" for="ativo_grupo">Grupo ativo</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-success" type="submit" id="salvar_grupo">
                            Cadastrar grupo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExcluirGrupo" tabindex="-1"
        aria-labelledby="modalExcluirGrupoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5" id="modalExcluirGrupoLabel">Excluir grupo</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formulario_exclusao_grupo" method="post">
                    <div class="modal-body">
                        <div id="alerta-exclusao-grupo" class="alert alert-danger d-none" role="alert"></div>
                        <p class="mb-2">Deseja realmente excluir este grupo?</p>
                        <p class="fw-semibold mb-0" id="nome_grupo_exclusao"></p>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-light border" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-danger" type="submit" id="confirmar_exclusao_grupo">
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
        <div id="toast-feedback" class="toast border-0 shadow" role="status">
            <div class="toast-body d-flex align-items-center gap-3">
                <i id="toast-icone" class="fa-solid fa-circle-check text-success fs-5" aria-hidden="true"></i>
                <span id="toast-mensagem" class="flex-grow-1"></span>
                <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <?php $this->load->view('js'); ?>

    <script>
        $(document).ready(function () {
            const base_url = '<?= base_url(); ?>';
            let secao_edicao = null;
            let secao_exclusao = null;
            let grupo_edicao = null;
            let grupo_exclusao = null;

            function preparar_nova_secao() {
                secao_edicao = null;

                $('#modalSecaoLabel').text('Nova seção');
                $('#formulario_secao')[0].reset();
                $('#ativo_secao').prop('checked', true);
                $('#salvar_secao').html('Cadastrar seção');
                $('#alerta-secao').empty().addClass('d-none');
            }

            function alternar_limites_grupo() {
                const repetivel = $('#repetivel_grupo').is(':checked');

                $('#limites_grupo').toggleClass('d-none', !repetivel);
                $('#quantidade_minima_grupo, #quantidade_maxima_grupo')
                    .prop('disabled', !repetivel);
            }

            function preparar_novo_grupo($botao) {
                grupo_edicao = null;

                $('#modalGrupoLabel').text('Novo grupo');
                $('#formulario_grupo')[0].reset();
                $('#grupo_secao_codigo').val(
                    $botao.data('secao-codigo')
                );
                $('#grupo_secao_titulo').text(
                    'Seção: ' + $botao.data('secao-titulo')
                );
                $('#quantidade_minima_grupo').val(0);
                $('#quantidade_maxima_grupo').val(1);
                $('#ativo_grupo').prop('checked', true);
                $('#salvar_grupo').html('Cadastrar grupo');
                $('#alerta-grupo').empty().addClass('d-none');

                alternar_limites_grupo();
            }

            $('#nova_secao, #cadastrar_primeira_secao').on('click', function () {
                preparar_nova_secao();
            });

            $('.editar-secao').on('click', function () {
                secao_edicao = $(this).data('codigo');

                $('#modalSecaoLabel').text('Editar seção');
                $('#titulo_secao').val($(this).data('titulo'));
                $('#descricao_secao').val($(this).data('descricao'));
                $('#ativo_secao').prop(
                    'checked',
                    Number($(this).data('ativo')) === 1
                );
                $('#salvar_secao').html('Salvar alterações');
                $('#alerta-secao').empty().addClass('d-none');
            });

            $('.novo-grupo').on('click', function () {
                preparar_novo_grupo($(this));
            });

            $('#repetivel_grupo').on('change', function () {
                alternar_limites_grupo();
            });

            $('.editar-grupo').on('click', function () {
                grupo_edicao = $(this).data('codigo');

                $('#modalGrupoLabel').text('Editar grupo');
                $('#grupo_secao_codigo').val('');
                $('#grupo_secao_titulo').text(
                    'Seção: ' + $(this).data('secao-titulo')
                );
                $('#nome_grupo').val($(this).data('nome'));
                $('#descricao_grupo').val($(this).data('descricao'));
                $('#repetivel_grupo').prop(
                    'checked',
                    Number($(this).data('repetivel')) === 1
                );
                $('#quantidade_minima_grupo').val(
                    $(this).data('quantidade-minima')
                );
                $('#quantidade_maxima_grupo').val(
                    $(this).data('quantidade-maxima')
                );
                $('#ativo_grupo').prop(
                    'checked',
                    Number($(this).data('ativo')) === 1
                );
                $('#salvar_grupo').html('Salvar alterações');
                $('#alerta-grupo').empty().addClass('d-none');

                alternar_limites_grupo();
            });

            $('#formulario_configuracao').on('submit', function (e) {
                e.preventDefault();

                const $botao = $('#salvar_configuracao');

                $('#alerta-configuracao').empty().addClass('d-none');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...'
                    );

                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function (response) {
                    mostrar_feedback(
                        response.mensagem?.conteudo,
                        'success'
                    );
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-configuracao');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html('Salvar configurações');
                });
            });

            $('#formulario_secao').on('submit', function (e) {
                e.preventDefault();

                const $botao = $('#salvar_secao');
                const url = secao_edicao
                    ? base_url + 'formulario_secao/atualizar/' + secao_edicao
                    : base_url + 'formulario_secao/cadastrar';

                $('#alerta-secao').empty().addClass('d-none');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...'
                    );

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function () {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('modalSecao')
                        )
                        .hide();

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-secao');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html(
                            secao_edicao
                                ? 'Salvar alterações'
                                : 'Cadastrar seção'
                        );
                });
            });

            $('.mover-secao').on('click', function () {
                const $botao = $(this);
                const codigo = $botao.data('codigo');
                const direcao = $botao.data('direcao');

                $botao.prop('disabled', true);

                $.ajax({
                    url: base_url + 'formulario_secao/mover/' + codigo + '/' + direcao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function () {
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-secoes');
                    $botao.prop('disabled', false);
                });
            });

            $('.excluir-secao').on('click', function () {
                secao_exclusao = $(this).data('codigo');

                $('#titulo_secao_exclusao').text(
                    $(this).data('titulo')
                );

                $('#alerta-exclusao-secao')
                    .empty()
                    .addClass('d-none');
            });

            $('#formulario_exclusao_secao').on('submit', function (e) {
                e.preventDefault();

                if (!secao_exclusao) {
                    return;
                }

                const $botao = $('#confirmar_exclusao_secao');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $.ajax({
                    url: base_url + 'formulario_secao/excluir/' + secao_exclusao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function () {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('modalExcluirSecao')
                        )
                        .hide();

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-exclusao-secao');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html('Excluir');
                });
            });

            $('#formulario_grupo').on('submit', function (e) {
                e.preventDefault();

                const $botao = $('#salvar_grupo');
                const url = grupo_edicao
                    ? base_url + 'formulario_grupo/atualizar/' + grupo_edicao
                    : base_url + 'formulario_grupo/cadastrar';

                $('#alerta-grupo').empty().addClass('d-none');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando...'
                    );

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                }).done(function () {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('modalGrupo')
                        )
                        .hide();

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-grupo');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html(
                            grupo_edicao
                                ? 'Salvar alterações'
                                : 'Cadastrar grupo'
                        );
                });
            });

            $('.mover-grupo').on('click', function () {
                const $botao = $(this);
                const codigo = $botao.data('codigo');
                const direcao = $botao.data('direcao');

                $botao.prop('disabled', true);

                $.ajax({
                    url: base_url + 'formulario_grupo/mover/' + codigo + '/' + direcao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function () {
                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-secoes');
                    $botao.prop('disabled', false);
                });
            });

            $('.excluir-grupo').on('click', function () {
                grupo_exclusao = $(this).data('codigo');

                $('#nome_grupo_exclusao').text(
                    $(this).data('nome')
                );

                $('#alerta-exclusao-grupo')
                    .empty()
                    .addClass('d-none');
            });

            $('#formulario_exclusao_grupo').on('submit', function (e) {
                e.preventDefault();

                if (!grupo_exclusao) {
                    return;
                }

                const $botao = $('#confirmar_exclusao_grupo');

                $botao
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
                    );

                $.ajax({
                    url: base_url + 'formulario_grupo/excluir/' + grupo_exclusao,
                    method: 'POST',
                    dataType: 'json'
                }).done(function () {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('modalExcluirGrupo')
                        )
                        .hide();

                    window.location.reload();
                }).fail(function (xhr) {
                    mostrar_erro_ajax(xhr, 'alerta-exclusao-grupo');
                }).always(function () {
                    $botao
                        .prop('disabled', false)
                        .html('Excluir');
                });
            });
        });
    </script>
</body>

</html>
