<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modelos de mensagens | Vitae RH</title>
    <?php $this->load->view('css'); ?>
</head>
<body class="bg-body-tertiary">
    <?php $this->load->view('nav'); ?>
    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Modelos de mensagens</h1>
                <p class="text-body-secondary mb-0">Crie textos reutilizáveis para contato com candidatos.</p>
            </div>
            <a class="btn btn-success" href="<?= base_url('candidato_mensagens/cadastrar'); ?>">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>Novo modelo
            </a>
        </header>
        <section class="card border shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th class="px-3">Nome</th><th>Assunto</th><th>Status</th><th class="text-end px-3">Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($modelos as $modelo): ?>
                            <tr>
                                <td class="px-3 fw-semibold"><?= html_escape($modelo['nome']); ?></td>
                                <td><?= html_escape($modelo['assunto']); ?></td>
                                <td><?= (int) $modelo['ativo'] === 1 ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>'; ?></td>
                                <td class="text-end px-3">
                                    <a class="btn btn-sm btn-light border" href="<?= base_url('candidato_mensagens/cadastrar/' . (int) $modelo['codigo']); ?>"><i class="fa-solid fa-pen"></i></a>
                                    <form class="d-inline" method="post" action="<?= base_url('candidato_mensagens/excluir/' . (int) $modelo['codigo']); ?>">
                                        <button class="btn btn-sm btn-light border text-danger" type="submit"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($modelos)): ?><div class="card-body text-center text-body-secondary">Nenhum modelo cadastrado.</div><?php endif; ?>
        </section>
    </main>
    <?php $this->load->view('js'); ?>
</body>
</html>
