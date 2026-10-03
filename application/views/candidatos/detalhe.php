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
                            <span class="badge text-bg-success">
                                <?= html_escape($situacoes_seletivas[$candidato['situacao_seletiva']] ?? 'Recebido'); ?>
                            </span>
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
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-secondary" href="mailto:<?= html_escape($candidato['email']); ?>">
                            <i class="fa-regular fa-envelope me-1" aria-hidden="true"></i>E-mail
                        </a>
                        <?php $telefone_whatsapp = preg_replace('/\D+/', '', $candidato['telefone']); ?>
                        <?php if (strlen($telefone_whatsapp) === 10 || strlen($telefone_whatsapp) === 11): ?>
                            <?php $telefone_whatsapp = '55' . $telefone_whatsapp; ?>
                        <?php endif; ?>
                        <a class="btn btn-outline-success" target="_blank" rel="noopener noreferrer"
                            href="https://wa.me/<?= html_escape($telefone_whatsapp); ?>">
                            <i class="fa-brands fa-whatsapp me-1" aria-hidden="true"></i>WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($mensagem_gestao)): ?>
            <div class="alert alert-success" role="status"><?= html_escape($mensagem_gestao); ?></div>
        <?php endif; ?>
        <?php if (!empty($erro_gestao)): ?>
            <div class="alert alert-danger" role="alert"><?= html_escape($erro_gestao); ?></div>
        <?php endif; ?>
        <?php if (!empty($mensagem_comunicacao)): ?><div class="alert alert-success" role="status"><?= html_escape($mensagem_comunicacao); ?></div><?php endif; ?>
        <?php if (!empty($erro_comunicacao)): ?><div class="alert alert-danger" role="alert"><?= html_escape($erro_comunicacao); ?></div><?php endif; ?>

        <?php if ($pode_gerenciar): ?>
            <section class="card border shadow-sm mb-4" aria-labelledby="gestao-title">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-0" id="gestao-title">Gestão do processo seletivo</h2>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('candidatos/situacao/' . (int) $candidato['codigo']); ?>"
                        method="post" class="row g-3 align-items-end">
                        <div class="col-12 col-md-8">
                            <label class="form-label" for="situacao_seletiva_atualizar">Situação</label>
                            <select class="form-select" id="situacao_seletiva_atualizar" name="situacao_seletiva" required>
                                <?php foreach ($situacoes_seletivas as $chave => $rotulo): ?>
                                    <option value="<?= html_escape($chave); ?>"
                                        <?= $candidato['situacao_seletiva'] === $chave ? 'selected' : ''; ?>>
                                        <?= html_escape($rotulo); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-grid">
                            <button class="btn btn-success" type="submit">Atualizar situação</button>
                        </div>
                    </form>
                    <hr>
                    <form action="<?= base_url('candidatos/anotacao/' . (int) $candidato['codigo']); ?>" method="post">
                        <label class="form-label" for="anotacao">Anotação interna</label>
                        <textarea class="form-control" id="anotacao" name="anotacao" rows="3"
                            maxlength="5000" required></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-body-secondary">Visível apenas à equipe autorizada do RH.</small>
                            <button class="btn btn-outline-success" type="submit">Registrar anotação</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($pode_comunicar && !empty($modelos_mensagem)): ?>
            <section class="card border shadow-sm mb-4" aria-labelledby="comunicacao-title">
                <div class="card-header bg-white py-3"><h2 class="h6 fw-semibold mb-0" id="comunicacao-title">Enviar mensagem</h2></div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('candidatos/enviar-mensagem/' . (int) $candidato['codigo']); ?>" class="row g-3 align-items-end">
                        <input type="hidden" name="_token_admin" value="<?= html_escape($this->token_admin->obter()); ?>">
                        <div class="col-12 col-md-8"><label class="form-label" for="modelo_codigo">Modelo</label>
                            <select class="form-select" id="modelo_codigo" name="modelo_codigo" required><option value="">Selecione um modelo</option>
                                <?php foreach ($modelos_mensagem as $modelo): ?><option value="<?= (int) $modelo['codigo']; ?>"><?= html_escape($modelo['nome']); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-grid gap-2">
                            <button class="btn btn-success" type="submit" name="canal" value="email">Enviar por e-mail</button>
                            <button class="btn btn-outline-success" type="submit" name="canal" value="whatsapp" formtarget="_blank">Abrir no WhatsApp</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>


        <section class="card border shadow-sm mb-4" aria-labelledby="historico-title">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0" id="historico-title">Histórico interno</h2>
            </div>
            <?php if (empty($historico)): ?>
                <div class="card-body"><p class="small text-body-secondary mb-0">Nenhum registro interno até o momento.</p></div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($historico as $evento): ?>
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                <strong><?= $evento['tipo'] === 'situacao' ? 'Mudança de situação' : 'Anotação'; ?></strong>
                                <span class="small text-body-secondary">
                                    <?= html_escape(date('d/m/Y H:i', strtotime($evento['cadastro']))); ?>
                                </span>
                            </div>
                            <div class="small text-body-secondary mb-2">
                                <?= html_escape($evento['usuario_nome'] ?? 'Usuário indisponível'); ?>
                            </div>
                            <?php if ($evento['tipo'] === 'situacao'): ?>
                                <p class="mb-0">
                                    <?= html_escape($situacoes_seletivas[$evento['situacao_anterior']] ?? $evento['situacao_anterior']); ?>
                                    <i class="fa-solid fa-arrow-right mx-1" aria-hidden="true"></i>
                                    <?= html_escape($situacoes_seletivas[$evento['situacao_nova']] ?? $evento['situacao_nova']); ?>
                                </p>
                            <?php else: ?>
                                <p class="mb-0 text-break"><?= nl2br(html_escape($evento['anotacao'])); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
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
