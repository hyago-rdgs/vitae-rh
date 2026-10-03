<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Formulários | Vitae RH</title><?php $this->load->view('css'); ?></head>
<body class="bg-body-tertiary">
<?php $this->load->view('nav'); ?>
<main class="container py-4">
    <h1 class="h3">Formulários</h1>
    <p>Cadastre e edite formulários. Escolha uma publicação para receber novos cadastros.</p>
    <form method="post" action="<?= base_url('formulario/cadastrar'); ?>" class="card card-body mb-4">
        <input type="hidden" name="_token_admin" value="<?= html_escape($this->token_admin->obter()); ?>">
        <label class="form-label" for="nome">Nome do novo formulário</label>
        <div class="input-group"><input id="nome" name="nome" maxlength="100" required class="form-control">
        <button class="btn btn-success">Criar formulário</button></div>
    </form>
    <?php if (!$vigente): ?><div class="alert alert-warning">Nenhuma publicação vigente. Publique e selecione uma versão para liberar cadastros.</div><?php endif; ?>
    <div class="list-group">
        <?php foreach ($formularios as $formulario): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
            <a href="<?= base_url('formulario/previsualizar/' . (int) $formulario['codigo']); ?>"><?= html_escape($formulario['nome']); ?></a>
            <div>
            <?php if ($vigente && (int) $vigente['formulario_codigo'] === (int) $formulario['codigo']): ?>
                <span class="badge text-bg-success">Principal — v<?= (int) $vigente['versao']; ?></span>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('formulario/configurar/' . (int) $formulario['codigo']); ?>">Editar e gerenciar publicações</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main><?php $this->load->view('js'); ?></body></html>
