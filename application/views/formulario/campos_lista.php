<?php if (!empty($campos)): ?>
    <div class="list-group list-group-flush">
        <?php foreach ($campos as $indice_campo => $campo): ?>
            <div class="list-group-item px-3 py-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
                    <div class="d-flex align-items-start gap-2">
                        <span class="badge text-bg-light border mt-1">
                            <?= (int) $campo['ordem']; ?>
                        </span>

                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="small fw-semibold">
                                    <?= html_escape($campo['nome']); ?>
                                </span>
                                <span class="badge text-bg-light border">
                                    <?= html_escape($tipos_campos[$campo['tipo']] ?? $campo['tipo']); ?>
                                </span>

                                <?php if ((int) $campo['obrigatorio'] === 1): ?>
                                    <span class="badge text-bg-warning">Obrigatório</span>
                                <?php endif; ?>

                                <?php if ((int) $campo['filtravel'] === 1): ?>
                                    <span class="badge text-bg-info">Filtrável</span>
                                <?php endif; ?>

                                <?php if ((int) $campo['ativo'] !== 1): ?>
                                    <span class="badge text-bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($campo['texto_ajuda'])): ?>
                                <span class="small text-secondary">
                                    <?= html_escape($campo['texto_ajuda']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-lg-end align-items-start gap-2">
                        <button class="btn btn-sm btn-light border mover-campo" type="button"
                            data-codigo="<?= $campo['codigo']; ?>" data-direcao="subir"
                            <?= $indice_campo === 0 ? 'disabled' : ''; ?>
                            aria-label="Mover <?= html_escape($campo['nome']); ?> para cima">
                            <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                        </button>

                        <button class="btn btn-sm btn-light border mover-campo" type="button"
                            data-codigo="<?= $campo['codigo']; ?>" data-direcao="descer"
                            <?= $indice_campo === count($campos) - 1 ? 'disabled' : ''; ?>
                            aria-label="Mover <?= html_escape($campo['nome']); ?> para baixo">
                            <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                        </button>

                        <button class="btn btn-sm btn-light border editar-campo" type="button"
                            data-codigo="<?= $campo['codigo']; ?>"
                            data-grupo-nome="<?= html_escape($grupo['nome']); ?>"
                            data-nome="<?= html_escape($campo['nome']); ?>"
                            data-tipo="<?= html_escape($campo['tipo']); ?>"
                            data-placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
                            data-texto-ajuda="<?= html_escape($campo['texto_ajuda'] ?? ''); ?>"
                            data-obrigatorio="<?= (int) $campo['obrigatorio']; ?>"
                            data-filtravel="<?= (int) $campo['filtravel']; ?>"
                            data-tamanho-maximo="<?= html_escape($campo['tamanho_maximo'] ?? ''); ?>"
                            data-numero-minimo="<?= html_escape($campo['numero_minimo'] ?? ''); ?>"
                            data-numero-maximo="<?= html_escape($campo['numero_maximo'] ?? ''); ?>"
                            data-data-minima="<?= html_escape($campo['data_minima'] ?? ''); ?>"
                            data-data-maxima="<?= html_escape($campo['data_maxima'] ?? ''); ?>"
                            data-ativo="<?= (int) $campo['ativo']; ?>"
                            data-bs-toggle="modal" data-bs-target="#modalCampo">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                        </button>

                        <button class="btn btn-sm btn-light border text-danger excluir-campo" type="button"
                            data-codigo="<?= $campo['codigo']; ?>"
                            data-nome="<?= html_escape($campo['nome']); ?>"
                            data-bs-toggle="modal" data-bs-target="#modalExcluirCampo"
                            aria-label="Excluir <?= html_escape($campo['nome']); ?>">
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="px-3 py-2 text-center text-secondary small">
        Nenhum campo cadastrado.
    </div>
<?php endif; ?>
