<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Consulta de candidatos cadastrados no Vitae RH">
    <title>Candidatos | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="mb-4">
            <h1 class="h3 mb-1">Candidatos</h1>
            <p class="text-body-secondary mb-0">
                Consulte os perfis enviados e acesse os currículos cadastrados.
            </p>
        </header>

        <?php if (!empty($erros_filtro)): ?>
            <div class="alert alert-warning" role="alert">
                <ul class="mb-0">
                    <?php foreach ($erros_filtro as $erro): ?>
                        <li><?= html_escape($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_comunicacao)): ?>
            <div class="alert alert-success" role="status"><?= html_escape($mensagem_comunicacao); ?></div>
        <?php endif; ?>
        <?php if (!empty($erro_comunicacao)): ?>
            <div class="alert alert-danger" role="alert"><?= html_escape($erro_comunicacao); ?></div>
        <?php endif; ?>

        <section aria-labelledby="filtros-title" class="card border shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3" id="filtros-title">Filtros</h2>
                <form action="<?= base_url('candidatos'); ?>" method="get">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-3">
                            <label class="form-label" for="termo">Buscar candidato</label>
                            <input class="form-control" id="termo" name="termo"
                                placeholder="Contato ou informação do perfil" type="search"
                                value="<?= html_escape($filtros['termo']); ?>">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="status">Conta</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todas</option>
                                <option value="ativo" <?= $filtros['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?= $filtros['status'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="situacao_seletiva">Processo seletivo</label>
                            <select class="form-select" id="situacao_seletiva" name="situacao_seletiva">
                                <option value="">Todas</option>
                                <?php foreach ($situacoes_seletivas as $chave => $rotulo): ?>
                                    <option value="<?= html_escape($chave); ?>"
                                        <?= $filtros['situacao_seletiva'] === $chave ? 'selected' : ''; ?>>
                                        <?= html_escape($rotulo); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_inicio">Cadastro a partir de</label>
                            <input class="form-control" id="data_inicio" name="data_inicio"
                                type="date" value="<?= html_escape($filtros['data_inicio']); ?>">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label" for="data_fim">Cadastro até</label>
                            <input class="form-control" id="data_fim" name="data_fim"
                                type="date" value="<?= html_escape($filtros['data_fim']); ?>">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="campo_chave">Campo configurável</label>
                            <select class="form-select" id="campo_chave" name="campo_chave">
                                <option value="">Todos os campos</option>
                                <?php foreach ($campos_filtro as $chave => $nome): ?>
                                    <option value="<?= html_escape($chave); ?>" <?= $filtros['campo_chave'] === $chave ? 'selected' : ''; ?>>
                                        <?= html_escape($nome); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="valor_campo">Valor do campo</label>
                            <input class="form-control" id="valor_campo" name="valor_campo" maxlength="150"
                                value="<?= html_escape($filtros['valor_campo']); ?>">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-1 d-grid">
                            <button class="btn btn-success" type="submit">
                                <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                <span class="visually-hidden">Filtrar</span>
                            </button>
                        </div>
                    </div>
                    <?php if ($filtros['termo'] !== '' || $filtros['status'] !== '' || $filtros['situacao_seletiva'] !== '' || $filtros['campo_chave'] !== '' || $filtros['valor_campo'] !== '' || $filtros['data_inicio'] !== '' || $filtros['data_fim'] !== ''): ?>
                        <div class="mt-3">
                            <a class="small" href="<?= base_url('candidatos'); ?>">Limpar filtros</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <?php if ($pode_comunicar && !empty($modelos_mensagem)): ?>
            <form id="envio-lote" method="post" action="<?= base_url('candidatos/enviar-mensagem-lote'); ?>" class="card border shadow-sm mb-4">
                <div class="card-body d-flex flex-column flex-md-row gap-3 align-items-md-end">
                    <div class="flex-grow-1"><label class="form-label" for="modelo_lote">Enviar modelo aos selecionados</label>
                        <select class="form-select" id="modelo_lote" name="modelo_codigo" required>
                            <option value="">Selecione um modelo</option>
                            <?php foreach ($modelos_mensagem as $modelo): ?><option value="<?= (int) $modelo['codigo']; ?>"><?= html_escape($modelo['nome']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-success" type="submit">Enviar aos selecionados</button>
                </div>
            </form>
        <?php endif; ?>

        <section aria-labelledby="lista-candidatos-title" class="card border shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-1" id="lista-candidatos-title">Perfis cadastrados</h2>
                <p class="small text-body-secondary mb-0">
                    <?= (int) $total; ?> candidato(s) encontrado(s)
                </p>
            </div>

            <?php if (!empty($candidatos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-secondary text-uppercase">
                                <?php if ($pode_comunicar && !empty($modelos_mensagem)): ?><th class="px-3 py-3"><span class="visually-hidden">Selecionar</span></th><?php endif; ?>
                                <th class="px-3 py-3" scope="col">Candidato</th>
                                <th class="py-3" scope="col">Telefone</th>
                                <th class="py-3" scope="col">Cadastro</th>
                                <th class="py-3 text-center" scope="col">Processo seletivo</th>
                                <th class="px-3 py-3 text-end" scope="col">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatos as $candidato): ?>
                                <tr>
                                    <?php if ($pode_comunicar && !empty($modelos_mensagem)): ?><td class="px-3"><input class="form-check-input" form="envio-lote" type="checkbox" name="candidatos[]" value="<?= (int) $candidato['codigo']; ?>"></td><?php endif; ?>
                                    <td class="px-3">
                                        <a class="text-decoration-none text-body fw-semibold"
                                            href="<?= base_url('candidatos/detalhe/' . (int) $candidato['codigo']); ?>">
                                            <?= html_escape($candidato['nome_completo']); ?>
                                        </a>
                                        <div class="small text-body-secondary">
                                            <?= html_escape($candidato['email']); ?>
                                        </div>
                                    </td>
                                    <td><?= html_escape($candidato['telefone']); ?></td>
                                    <td><?= html_escape(date('d/m/Y H:i', strtotime($candidato['cadastro']))); ?></td>
                                    <td class="text-center">
                                        <?= html_escape($situacoes_seletivas[$candidato['situacao_seletiva']] ?? 'Recebido'); ?>
                                    </td>
                                    <td class="px-3 text-end">
                                        <a class="btn btn-sm btn-outline-success"
                                            href="<?= base_url('candidatos/detalhe/' . (int) $candidato['codigo']); ?>">
                                            <i class="fa-regular fa-eye me-1" aria-hidden="true"></i>
                                            Ver perfil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_paginas > 1): ?>
                    <?php
                    $parametros = array_filter($filtros, function ($valor) {
                        return $valor !== '';
                    });
                    $gerar_url = function ($numero) use ($parametros) {
                        $parametros['pagina'] = $numero;
                        return base_url('candidatos') . '?' . http_build_query($parametros);
                    };
                    ?>
                    <nav class="p-3 border-top" aria-label="Paginação de candidatos">
                        <ul class="pagination pagination-sm justify-content-end mb-0">
                            <li class="page-item <?= $pagina <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= $pagina > 1 ? $gerar_url($pagina - 1) : '#'; ?>"
                                    aria-label="Página anterior">Anterior</a>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">Página <?= (int) $pagina; ?> de <?= (int) $total_paginas; ?></span>
                            </li>
                            <li class="page-item <?= $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= $pagina < $total_paginas ? $gerar_url($pagina + 1) : '#'; ?>"
                                    aria-label="Próxima página">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="card-body py-5 text-center">
                    <i class="fa-regular fa-folder-open fa-2x text-body-tertiary mb-3" aria-hidden="true"></i>
                    <p class="fw-semibold mb-1">Nenhum candidato encontrado</p>
                    <p class="small text-body-secondary mb-0">Tente alterar ou limpar os filtros.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
