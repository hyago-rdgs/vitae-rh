<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Perfil de candidato no Vitae RH">
    <title><?= html_escape($candidato['nome_completo']); ?> | Candidatos | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= base_url('candidatos'); ?>">
            <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>
            Voltar à lista
        </a>

        <section class="card border shadow-sm mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <img class="rounded-circle border object-fit-cover" width="104" height="104"
                        src="<?= base_url('candidatos/foto/' . (int) $candidato['codigo']); ?>"
                        alt="Foto de <?= html_escape($candidato['nome_completo']); ?>">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h1 class="h3 mb-0"><?= html_escape($candidato['nome_completo']); ?></h1>
                            <?php if ($candidato['status'] === 'ativo'): ?>
                                <span class="badge text-bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-body-secondary mb-1">
                            <i class="fa-regular fa-envelope me-1" aria-hidden="true"></i>
                            <?= html_escape($candidato['email']); ?>
                        </p>
                        <p class="text-body-secondary mb-0">
                            <i class="fa-brands fa-whatsapp me-1" aria-hidden="true"></i>
                            <?= html_escape($candidato['telefone']); ?>
                        </p>
                    </div>
                    <a class="btn btn-success flex-shrink-0"
                        href="<?= base_url('candidatos/curriculo/' . (int) $candidato['codigo']); ?>">
                        <i class="fa-regular fa-file-lines me-2" aria-hidden="true"></i>
                        Baixar currículo
                    </a>
                </div>
            </div>
        </section>

        <section class="card border shadow-sm mb-4" aria-labelledby="cadastro-title">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0" id="cadastro-title">Dados do cadastro</h2>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 col-lg-3">Cadastrado em</dt>
                    <dd class="col-sm-8 col-lg-9">
                        <?= html_escape(date('d/m/Y H:i', strtotime($candidato['cadastro']))); ?>
                    </dd>
                    <dt class="col-sm-4 col-lg-3">Versão do formulário</dt>
                    <dd class="col-sm-8 col-lg-9">v<?= (int) $formulario['formulario_versao']; ?></dd>
                    <dt class="col-sm-4 col-lg-3">Consentimento LGPD</dt>
                    <dd class="col-sm-8 col-lg-9">
                        <?= $candidato['consentimento_lgpd'] ? 'Registrado' : 'Não registrado'; ?>
                        <?php if (!empty($candidato['consentimento_lgpd_em'])): ?>
                            · <?= html_escape(date('d/m/Y H:i', strtotime($candidato['consentimento_lgpd_em']))); ?>
                            (<?= html_escape($candidato['consentimento_lgpd_versao']); ?>)
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </section>

        <?php foreach ($estrutura['secoes'] as $secao): ?>
            <section class="card border shadow-sm mb-4" aria-labelledby="secao-<?= (int) $secao['codigo']; ?>">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-semibold mb-1" id="secao-<?= (int) $secao['codigo']; ?>">
                        <?= html_escape($secao['titulo']); ?>
                    </h2>
                    <?php if (!empty($secao['descricao'])): ?>
                        <p class="small text-body-secondary mb-0"><?= html_escape($secao['descricao']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-body d-grid gap-3">
                    <?php foreach ($secao['grupos'] as $grupo): ?>
                        <section class="border rounded p-3" aria-labelledby="grupo-<?= (int) $grupo['codigo']; ?>">
                            <h3 class="h6 fw-semibold mb-3" id="grupo-<?= (int) $grupo['codigo']; ?>">
                                <?= html_escape($grupo['nome']); ?>
                            </h3>
                            <?php if (empty($grupo['ocorrencias'])): ?>
                                <p class="small text-body-secondary mb-0">Sem informações preenchidas.</p>
                            <?php else: ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($grupo['ocorrencias'] as $ocorrencia): ?>
                                        <div class="<?= count($grupo['ocorrencias']) > 1 ? 'border rounded p-3' : ''; ?>">
                                            <?php if (count($grupo['ocorrencias']) > 1): ?>
                                                <p class="small fw-semibold text-body-secondary mb-2">
                                                    Registro <?= (int) $ocorrencia['indice']; ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (empty($ocorrencia['campos'])): ?>
                                                <p class="small text-body-secondary mb-0">Sem campos preenchidos.</p>
                                            <?php else: ?>
                                                <dl class="row mb-0">
                                                    <?php foreach ($ocorrencia['campos'] as $campo): ?>
                                                        <dt class="col-md-4 col-xl-3"><?= html_escape($campo['nome']); ?></dt>
                                                        <dd class="col-md-8 col-xl-9 text-break">
                                                            <?= nl2br(html_escape($campo['valor'])); ?>
                                                        </dd>
                                                    <?php endforeach; ?>
                                                </dl>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
