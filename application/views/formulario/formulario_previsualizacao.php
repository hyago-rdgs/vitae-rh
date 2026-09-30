<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pré-visualização do formulário de candidatos">
    <title>Pré-visualização do formulário | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <?php
    $secoes_ativas = array_filter(
        $secoes,
        static function ($secao) {
            return (int) $secao['ativo'] === 1;
        }
    );
    ?>

    <main class="container py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <section>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="h3 mb-0">Pré-visualização do formulário</h1>
                    <span class="badge text-bg-warning">Rascunho</span>
                </div>
                <p class="text-body-secondary mb-0">
                    Visualização aproximada do cadastro que será apresentado ao candidato.
                </p>
            </section>

            <a class="btn btn-light border" href="<?= base_url('formulario'); ?>">
                <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                Voltar à configuração
            </a>
        </header>

        <div class="alert alert-info" role="status">
            Esta tela é somente para conferência. Os campos estão desabilitados e nenhuma informação será salva.
        </div>

        <section class="card border shadow-sm mb-4" aria-labelledby="dados-acesso-title">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-semibold mb-1" id="dados-acesso-title">Dados pessoais e acesso</h2>
                <p class="small text-secondary mb-0">Informações obrigatórias presentes em todos os cadastros.</p>
            </div>
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_nome">Nome completo <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_nome" type="text" disabled>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_email">E-mail de acesso <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_email" type="email" disabled>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_telefone">Telefone/WhatsApp <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_telefone" type="tel" disabled>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_senha">Senha <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_senha" type="password" disabled>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_foto">Foto de perfil <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_foto" type="file" disabled>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prev_curriculo">Currículo <span class="text-danger">*</span></label>
                        <input class="form-control" id="prev_curriculo" type="file" disabled>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" id="prev_lgpd" type="checkbox" disabled>
                            <label class="form-check-label" for="prev_lgpd">
                                Concordo com o tratamento dos meus dados pessoais conforme a política de privacidade.
                                <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php foreach ($secoes_ativas as $secao): ?>
            <?php
            $grupos_ativos = array_filter(
                $grupos_por_secao[$secao['codigo']] ?? [],
                static function ($grupo) {
                    return (int) $grupo['ativo'] === 1;
                }
            );
            $primeiro_grupo = !empty($grupos_ativos)
                ? reset($grupos_ativos)
                : NULL;
            $grupo_principal = count($grupos_ativos) === 1 &&
                (int) $primeiro_grupo['repetivel'] === 0
                    ? $primeiro_grupo
                    : NULL;
            ?>

            <section class="card border shadow-sm mb-4" aria-labelledby="secao_<?= $secao['codigo']; ?>_title">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-semibold mb-1" id="secao_<?= $secao['codigo']; ?>_title">
                        <?= html_escape($secao['titulo']); ?>
                    </h2>
                    <?php if (!empty($secao['descricao'])): ?>
                        <p class="small text-secondary mb-0"><?= html_escape($secao['descricao']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="card-body p-3 p-lg-4">
                    <?php if ($grupo_principal): ?>
                        <?php
                        $this->load->view(
                            'formulario/campos_previsualizacao',
                            [
                                'campos' => $campos_por_grupo[$grupo_principal['codigo']] ?? [],
                                'opcoes_por_campo' => $opcoes_por_campo,
                                'id_prefix' => 'secao_' . $secao['codigo']
                            ]
                        );
                        ?>
                    <?php elseif (!empty($grupos_ativos)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($grupos_ativos as $grupo): ?>
                                <section class="border rounded p-3" aria-labelledby="grupo_<?= $grupo['codigo']; ?>_title">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                        <div>
                                            <h3 class="h6 fw-semibold mb-1" id="grupo_<?= $grupo['codigo']; ?>_title">
                                                <?= html_escape($grupo['nome']); ?>
                                            </h3>
                                            <?php if (!empty($grupo['descricao'])): ?>
                                                <p class="small text-secondary mb-0"><?= html_escape($grupo['descricao']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ((int) $grupo['repetivel'] === 1): ?>
                                            <span class="badge text-bg-light border align-self-start">
                                                <?= (int) $grupo['quantidade_minima']; ?> a <?= (int) $grupo['quantidade_maxima']; ?> registros
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php
                                    $this->load->view(
                                        'formulario/campos_previsualizacao',
                                        [
                                            'campos' => $campos_por_grupo[$grupo['codigo']] ?? [],
                                            'opcoes_por_campo' => $opcoes_por_campo,
                                            'id_prefix' => 'grupo_' . $grupo['codigo']
                                        ]
                                    );
                                    ?>

                                    <?php if ((int) $grupo['repetivel'] === 1): ?>
                                        <button class="btn btn-sm btn-light border mt-3" type="button" disabled>
                                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>
                                            Adicionar registro
                                        </button>
                                    <?php endif; ?>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="small text-secondary mb-0">Nenhum campo ativo nesta seção.</p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if (empty($secoes_ativas)): ?>
            <div class="alert alert-light border text-secondary">
                Ainda não há seções ativas configuradas para o candidato.
            </div>
        <?php endif; ?>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
