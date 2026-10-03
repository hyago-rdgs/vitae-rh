<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meu perfil | Vitae RH</title>
    <?php $this->load->view('css'); ?>
</head>
<body class="bg-body-tertiary">
    <header class="bg-white border-bottom">
        <nav class="navbar container d-flex justify-content-between" aria-label="Navegação principal">
            <a class="navbar-brand fw-semibold" href="<?= base_url('candidato/portal'); ?>">Vitae RH</a>
            <form method="post" action="<?= base_url('candidato/logout'); ?>">
                <input type="hidden" name="token" value="<?= html_escape($token_logout); ?>">
                <button class="btn btn-outline-secondary btn-sm" type="submit">Sair</button>
            </form>
        </nav>
    </header>
    <main class="container py-4 py-lg-5">
        <?php if ($nova_publicacao && (int) $nova_publicacao['codigo'] !== (int) $formulario['formulario_publicacao_codigo']): ?>
            <div class="alert alert-info" role="status">
                O formulário principal mudou. Há informações que você pode preencher ou revisar.
                <a href="<?= base_url('candidato/portal/editar'); ?>" class="alert-link">Atualizar meu perfil</a>
            </div>
        <?php endif; ?>
        <?php if ($salvo): ?>
            <div class="alert alert-success" role="status">Seu perfil foi atualizado.</div>
        <?php endif; ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <p class="small text-success fw-semibold text-uppercase mb-1">Portal do candidato</p>
                <h1 class="h2 mb-0">Meu perfil</h1>
            </div>
            <a class="btn btn-success" href="<?= base_url('candidato/portal/editar'); ?>">Editar informações</a>
        </div>
        <section class="card border shadow-sm mb-4" aria-labelledby="dados-pessoais">
            <div class="card-body p-4">
                <h2 class="h5 mb-4" id="dados-pessoais">Dados pessoais</h2>
                <div class="row g-4">
                    <div class="col-12 col-md-auto">
                        <img src="<?= base_url('candidato/portal/foto'); ?>" alt="Foto de perfil"
                            class="rounded object-fit-cover" width="128" height="128">
                    </div>
                    <div class="col">
                        <p class="mb-2"><strong>Nome:</strong> <?= html_escape($candidato['nome_completo']); ?></p>
                        <p class="mb-2"><strong>E-mail:</strong> <?= html_escape($candidato['email']); ?></p>
                        <p class="mb-2"><strong>Telefone/WhatsApp:</strong> <?= html_escape($candidato['telefone']); ?></p>
                        <p class="mb-0"><strong>Currículo:</strong>
                            <a href="<?= base_url('candidato/portal/curriculo'); ?>"><?= html_escape($candidato['curriculo_nome_original']); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <p class="small text-body-secondary">Informações respondidas na versão <?= (int) $formulario['formulario_versao']; ?> do formulário.</p>
        <?php foreach ($estrutura['secoes'] as $secao): ?>
            <section class="card border shadow-sm mb-4" aria-labelledby="secao-<?= (int) $secao['codigo']; ?>">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0" id="secao-<?= (int) $secao['codigo']; ?>"><?= html_escape($secao['titulo']); ?></h2>
                </div>
                <div class="card-body p-4 d-grid gap-4">
                    <?php foreach ($secao['grupos'] as $grupo): ?>
                        <div>
                            <h3 class="h6 mb-3"><?= html_escape($grupo['nome']); ?></h3>
                            <?php $ocorrencias = $respostas[(string) $grupo['codigo']] ?? []; ?>
                            <?php foreach ($ocorrencias as $indice => $campos): ?>
                                <div class="border rounded p-3 mb-2">
                                    <?php if (!empty($grupo['repetivel'])): ?>
                                        <p class="small fw-semibold">Registro <?= (int) $indice; ?></p>
                                    <?php endif; ?>
                                    <?php foreach ($grupo['campos'] as $campo): ?>
                                        <?php
                                        $valor = $campos[$campo['chave']] ?? NULL;
                                        if (is_array($valor)) {
                                            $opcoes = array_column($campo['opcoes'] ?? [], 'nome', 'valor');
                                            $texto = implode(', ', array_map(function ($item) use ($opcoes) {
                                                return $opcoes[$item] ?? $item;
                                            }, $valor));
                                        } elseif ($campo['tipo'] === 'selecao_unica') {
                                            $opcoes = array_column($campo['opcoes'] ?? [], 'nome', 'valor');
                                            $texto = $opcoes[$valor ?? ''] ?? $valor;
                                        } elseif ($campo['tipo'] === 'sim_nao') {
                                            $texto = $valor === 'sim' ? 'Sim' : ($valor === 'nao' ? 'Não' : '');
                                        } else {
                                            $texto = $valor;
                                        }
                                        ?>
                                        <p class="mb-2"><strong><?= html_escape($campo['nome']); ?>:</strong>
                                            <?= $texto === NULL || $texto === '' ? '—' : nl2br(html_escape((string) $texto)); ?>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (!$ocorrencias): ?><p class="text-body-secondary mb-0">Nenhuma informação registrada.</p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>
</body>
</html>
