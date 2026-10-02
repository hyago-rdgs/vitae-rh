<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $modelo['codigo'] ? 'Editar' : 'Novo'; ?> modelo | Vitae RH</title>
    <?php $this->load->view('css'); ?>
</head>
<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>
    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= base_url('candidato_mensagens'); ?>">Voltar</a>
        <section class="card border shadow-sm">
            <div class="card-body p-3 p-lg-4">
                <h1 class="h4 mb-1"><?= $modelo['codigo'] ? 'Editar modelo' : 'Novo modelo'; ?></h1>
                <p class="small text-body-secondary">Variáveis disponíveis: <code>{{nome_completo}}</code>, <code>{{email}}</code>, <code>{{telefone}}</code>, <code>{{situacao_seletiva}}</code> e <code>{{cadastro}}</code>.</p>
                <?php if ($erro): ?><div class="alert alert-danger"><?= html_escape($erro); ?></div><?php endif; ?>
                <form method="post" action="<?= base_url('candidato_mensagens/salvar' . ($modelo['codigo'] ? '/' . (int) $modelo['codigo'] : '')); ?>">
                    <div class="mb-3"><label class="form-label" for="nome">Nome</label><input class="form-control" id="nome" name="nome" maxlength="100" required value="<?= html_escape($modelo['nome']); ?>"></div>
                    <div class="mb-3"><label class="form-label" for="assunto">Assunto</label><input class="form-control" id="assunto" name="assunto" maxlength="255" required value="<?= html_escape($modelo['assunto']); ?>"></div>
                    <div class="mb-3"><label class="form-label" for="conteudo">Mensagem</label><textarea class="form-control" id="conteudo" name="conteudo" rows="10" maxlength="10000" required><?= html_escape($modelo['conteudo']); ?></textarea></div>
                    <div class="form-check mb-3"><input class="form-check-input" id="ativo" name="ativo" type="checkbox" value="1" <?= (int) $modelo['ativo'] === 1 ? 'checked' : ''; ?>><label class="form-check-label" for="ativo">Modelo ativo</label></div>
                    <button class="btn btn-success" type="submit">Salvar modelo</button>
                </form>
            </div>
        </section>
    </main>
    <?php $this->load->view('js'); ?>
</body>
</html>
