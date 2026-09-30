<?php
$tipo = $campo['tipo'];
$nome = 'respostas[' . (int) $grupo_codigo . '][' . $indice . '][' . $campo['chave'] . ']';
$id_base = preg_replace(
    '/[^a-zA-Z0-9_-]/',
    '_',
    'grupo_' . $grupo_codigo . '_' . $indice . '_' . $campo['chave']
);
$obrigatorio = !empty($campo['obrigatorio']);
$valor_escalar = is_string($valor) || is_numeric($valor)
    ? (string) $valor
    : '';
$opcoes_selecionadas = [];

if (is_array($valor)) {
    foreach ($valor as $opcao_selecionada) {
        if (is_string($opcao_selecionada) || is_numeric($opcao_selecionada)) {
            $opcoes_selecionadas[] = (string) $opcao_selecionada;
        }
    }
}
?>
<div class="col-12 <?= $tipo === 'texto_longo' ? '' : 'col-lg-6'; ?>">
    <?php if (in_array($tipo, ['sim_nao', 'multipla_selecao'], TRUE)): ?>
        <fieldset>
            <legend class="form-label fw-semibold fs-6">
                <?= html_escape($campo['nome']); ?>
                <?php if ($obrigatorio): ?><span class="text-danger" aria-label="obrigatório">*</span><?php endif; ?>
            </legend>
    <?php else: ?>
        <label class="form-label fw-semibold" for="<?= html_escape($id_base); ?>">
            <?= html_escape($campo['nome']); ?>
            <?php if ($obrigatorio): ?><span class="text-danger" aria-label="obrigatório">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($tipo === 'texto_longo'): ?>
        <textarea class="form-control" id="<?= html_escape($id_base); ?>"
            name="<?= html_escape($nome); ?>" rows="3"
            placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
            <?= $obrigatorio ? 'required' : ''; ?>
            <?= !empty($campo['tamanho_maximo']) ? 'maxlength="' . (int) $campo['tamanho_maximo'] . '"' : ''; ?>><?= html_escape($valor_escalar); ?></textarea>
    <?php elseif ($tipo === 'numero'): ?>
        <input class="form-control" id="<?= html_escape($id_base); ?>"
            name="<?= html_escape($nome); ?>" type="number" step="any"
            value="<?= html_escape($valor_escalar); ?>"
            placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
            <?= $obrigatorio ? 'required' : ''; ?>
            <?= $campo['numero_minimo'] !== NULL ? 'min="' . html_escape($campo['numero_minimo']) . '"' : ''; ?>
            <?= $campo['numero_maximo'] !== NULL ? 'max="' . html_escape($campo['numero_maximo']) . '"' : ''; ?>>
    <?php elseif ($tipo === 'data'): ?>
        <input class="form-control" id="<?= html_escape($id_base); ?>"
            name="<?= html_escape($nome); ?>" type="date"
            value="<?= html_escape($valor_escalar); ?>"
            <?= $obrigatorio ? 'required' : ''; ?>
            <?= !empty($campo['data_minima']) ? 'min="' . html_escape($campo['data_minima']) . '"' : ''; ?>
            <?= !empty($campo['data_maxima']) ? 'max="' . html_escape($campo['data_maxima']) . '"' : ''; ?>>
    <?php elseif ($tipo === 'sim_nao'): ?>
        <div class="d-flex flex-wrap gap-4">
            <?php foreach (['sim' => 'Sim', 'nao' => 'Não'] as $opcao_valor => $opcao_nome): ?>
                <?php $opcao_id = $id_base . '_' . $opcao_valor; ?>
                <div class="form-check">
                    <input class="form-check-input" id="<?= html_escape($opcao_id); ?>"
                        name="<?= html_escape($nome); ?>" type="radio"
                        value="<?= $opcao_valor; ?>"
                        <?= $valor_escalar === $opcao_valor ? 'checked' : ''; ?>
                        <?= $obrigatorio ? 'required' : ''; ?>>
                    <label class="form-check-label" for="<?= html_escape($opcao_id); ?>">
                        <?= $opcao_nome; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($tipo === 'selecao_unica'): ?>
        <select class="form-select" id="<?= html_escape($id_base); ?>"
            name="<?= html_escape($nome); ?>" <?= $obrigatorio ? 'required' : ''; ?>>
            <option value="">Selecione uma opção</option>
            <?php foreach ($campo['opcoes'] as $opcao): ?>
                <option value="<?= html_escape($opcao['valor']); ?>"
                    <?= $valor_escalar === (string) $opcao['valor'] ? 'selected' : ''; ?>>
                    <?= html_escape($opcao['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($tipo === 'multipla_selecao'): ?>
        <div class="d-grid gap-2">
            <?php foreach ($campo['opcoes'] as $opcao): ?>
                <?php $opcao_id = $id_base . '_opcao_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $opcao['codigo']); ?>
                <div class="form-check">
                    <input class="form-check-input" id="<?= html_escape($opcao_id); ?>"
                        name="<?= html_escape($nome); ?>[]" type="checkbox"
                        value="<?= html_escape($opcao['valor']); ?>"
                        <?= in_array((string) $opcao['valor'], $opcoes_selecionadas, TRUE) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="<?= html_escape($opcao_id); ?>">
                        <?= html_escape($opcao['nome']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <input class="form-control" id="<?= html_escape($id_base); ?>"
            name="<?= html_escape($nome); ?>" type="text"
            value="<?= html_escape($valor_escalar); ?>"
            placeholder="<?= html_escape($campo['placeholder'] ?? ''); ?>"
            <?= $obrigatorio ? 'required' : ''; ?>
            <?= !empty($campo['tamanho_maximo']) ? 'maxlength="' . (int) $campo['tamanho_maximo'] . '"' : ''; ?>>
    <?php endif; ?>

    <?php if (in_array($tipo, ['sim_nao', 'multipla_selecao'], TRUE)): ?>
        </fieldset>
    <?php endif; ?>

    <?php if (!empty($campo['texto_ajuda'])): ?>
        <div class="form-text"><?= html_escape($campo['texto_ajuda']); ?></div>
    <?php endif; ?>
</div>
