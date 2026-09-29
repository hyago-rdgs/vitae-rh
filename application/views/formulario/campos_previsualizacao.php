<?php
$campos_ativos = array_filter(
    $campos,
    static function ($campo) {
        return (int) $campo['ativo'] === 1;
    }
);
?>

<?php if (!empty($campos_ativos)): ?>
    <div class="row g-3">
        <?php foreach ($campos_ativos as $campo): ?>
            <?php
            $campo_id = $id_prefix . '_campo_' . $campo['codigo'];
            $opcoes = array_filter(
                $opcoes_por_campo[$campo['codigo']] ?? [],
                static function ($opcao) {
                    return (int) $opcao['ativo'] === 1;
                }
            );
            ?>

            <div class="col-12 <?= $campo['tipo'] === 'texto_longo' ? '' : 'col-lg-6'; ?>">
                <?php if (in_array($campo['tipo'], ['sim_nao', 'multipla_selecao'], TRUE)): ?>
                    <div class="form-label fw-semibold" id="<?= $campo_id; ?>_label">
                <?php else: ?>
                    <label class="form-label fw-semibold" for="<?= $campo_id; ?>">
                <?php endif; ?>
                    <?= html_escape($campo['nome']); ?>
                    <?php if ((int) $campo['obrigatorio'] === 1): ?>
                        <span class="text-danger" aria-label="obrigatório">*</span>
                    <?php endif; ?>
                <?php if (in_array($campo['tipo'], ['sim_nao', 'multipla_selecao'], TRUE)): ?>
                    </div>
                <?php else: ?>
                    </label>
                <?php endif; ?>

                <?php if ($campo['tipo'] === 'texto_longo'): ?>
                    <textarea class="form-control" id="<?= $campo_id; ?>" rows="3"
                        placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>" disabled></textarea>
                <?php elseif ($campo['tipo'] === 'numero'): ?>
                    <input class="form-control" id="<?= $campo_id; ?>" type="number"
                        placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
                        <?= $campo['numero_minimo'] !== NULL ? 'min="' . html_escape($campo['numero_minimo']) . '"' : ''; ?>
                        <?= $campo['numero_maximo'] !== NULL ? 'max="' . html_escape($campo['numero_maximo']) . '"' : ''; ?>
                        disabled>
                <?php elseif ($campo['tipo'] === 'data'): ?>
                    <input class="form-control" id="<?= $campo_id; ?>" type="date"
                        <?= !empty($campo['data_minima']) ? 'min="' . html_escape($campo['data_minima']) . '"' : ''; ?>
                        <?= !empty($campo['data_maxima']) ? 'max="' . html_escape($campo['data_maxima']) . '"' : ''; ?>
                        disabled>
                <?php elseif ($campo['tipo'] === 'sim_nao'): ?>
                    <div class="d-flex gap-4 pt-2" id="<?= $campo_id; ?>"
                        role="group" aria-labelledby="<?= $campo_id; ?>_label">
                        <?php foreach (['sim' => 'Sim', 'nao' => 'Não'] as $valor => $nome): ?>
                            <div class="form-check">
                                <input class="form-check-input" id="<?= $campo_id . '_' . $valor; ?>"
                                    type="radio" disabled>
                                <label class="form-check-label" for="<?= $campo_id . '_' . $valor; ?>">
                                    <?= $nome; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($campo['tipo'] === 'selecao_unica'): ?>
                    <select class="form-select" id="<?= $campo_id; ?>" disabled>
                        <option selected>Selecione uma opção</option>
                        <?php foreach ($opcoes as $opcao): ?>
                            <option><?= html_escape($opcao['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($campo['tipo'] === 'multipla_selecao'): ?>
                    <div id="<?= $campo_id; ?>" role="group"
                        aria-labelledby="<?= $campo_id; ?>_label">
                        <?php foreach ($opcoes as $opcao): ?>
                            <?php $opcao_id = $campo_id . '_opcao_' . $opcao['codigo']; ?>
                            <div class="form-check">
                                <input class="form-check-input" id="<?= $opcao_id; ?>"
                                    type="checkbox" disabled>
                                <label class="form-check-label" for="<?= $opcao_id; ?>">
                                    <?= html_escape($opcao['nome']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($opcoes)): ?>
                            <p class="small text-secondary mb-0">Nenhuma opção ativa cadastrada.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <input class="form-control" id="<?= $campo_id; ?>" type="text"
                        placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
                        <?= !empty($campo['tamanho_maximo']) ? 'maxlength="' . (int) $campo['tamanho_maximo'] . '"' : ''; ?>
                        disabled>
                <?php endif; ?>

                <?php if (!empty($campo['texto_ajuda'])): ?>
                    <div class="form-text"><?= html_escape($campo['texto_ajuda']); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="small text-secondary mb-0">Nenhum campo ativo nesta área.</p>
<?php endif; ?>
