<?php foreach ($campos_filtro as $chave => $campo): ?>
    <?php $valor = $filtros['campos'][$chave] ?? []; $id = 'filtro-' . (int) $campo['codigo']; ?>
    <div class="col-12 col-md-6 col-lg-4">
        <label class="form-label" for="<?= $id; ?>"><?= html_escape($campo['nome']); ?>
            <small class="text-body-secondary">(<?= html_escape($campo['formulario_nome']); ?>)</small>
        </label>
        <?php if (in_array($campo['tipo'], ['numero', 'data'], TRUE)): ?>
            <div class="input-group">
                <input class="form-control" id="<?= $id; ?>" type="<?= $campo['tipo'] === 'numero' ? 'number' : 'date'; ?>"
                    step="any" name="campos[<?= html_escape($chave); ?>][min]" aria-label="De"
                    placeholder="De" value="<?= html_escape($valor['min'] ?? ''); ?>">
                <input class="form-control" type="<?= $campo['tipo'] === 'numero' ? 'number' : 'date'; ?>"
                    step="any" name="campos[<?= html_escape($chave); ?>][max]" aria-label="Até"
                    placeholder="Até" value="<?= html_escape($valor['max'] ?? ''); ?>">
            </div>
        <?php elseif ($campo['tipo'] === 'sim_nao'): ?>
            <select class="form-select" id="<?= $id; ?>" name="campos[<?= html_escape($chave); ?>][valor]">
                <option value="">Todos</option>
                <option value="1" <?= ($valor['valor'] ?? '') === '1' ? 'selected' : ''; ?>>Sim</option>
                <option value="0" <?= ($valor['valor'] ?? '') === '0' ? 'selected' : ''; ?>>Não</option>
            </select>
        <?php elseif (in_array($campo['tipo'], ['selecao_unica', 'multipla_selecao'], TRUE)): ?>
            <select class="form-select" id="<?= $id; ?>" name="campos[<?= html_escape($chave); ?>][valor]">
                <option value="">Todas as opções</option>
                <?php foreach ($campo['opcoes'] as $opcao): ?>
                    <option value="<?= html_escape($opcao['valor']); ?>" <?= ($valor['valor'] ?? '') === (string) $opcao['valor'] ? 'selected' : ''; ?>><?= html_escape($opcao['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input class="form-control" id="<?= $id; ?>" maxlength="150"
                name="campos[<?= html_escape($chave); ?>][valor]" value="<?= html_escape($valor['valor'] ?? ''); ?>">
        <?php endif; ?>
    </div>
<?php endforeach; ?>
