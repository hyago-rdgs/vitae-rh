<?php $rota_acesso = $rota_acesso ?? 'candidato'; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title><?= $pagina === 'login' ? 'Acesso' : ($pagina === 'recuperacao' ? 'Recuperar acesso' : 'Redefinir senha'); ?>
        | Vitae RH</title>
    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <section class="card border shadow-sm w-100" style="max-width: 520px;">
            <div class="card-body p-4 p-lg-5">
                <a href="<?= base_url('candidato/cadastro'); ?>"
                    class="text-decoration-none text-success fw-semibold">Vitae RH</a>
                <h1 class="h3 mt-3 mb-3">
                    <?= $pagina === 'login' ? 'Portal do candidato' : ($pagina === 'recuperacao' ? 'Recuperar acesso' : 'Nova senha'); ?>
                </h1>

                <?php if ($erro !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= html_escape($erro); ?></div>
                <?php endif; ?>
                <?php if ($pagina === 'login' && $this->session->flashdata('candidato_senha_redefinida')): ?>
                    <div class="alert alert-success" role="status">Senha redefinida. Entre com sua nova senha.</div>
                <?php endif; ?>
                <?php if ($pagina === 'recuperacao' && !empty($sucesso)): ?>
                    <div class="alert alert-success" role="status">Se o e-mail estiver cadastrado, você receberá as
                        instruções para redefinir a senha.</div>
                <?php endif; ?>

                <form method="post"
                    action="<?= base_url($pagina === 'login' ? 'candidato/login' : ($pagina === 'recuperacao' ? $rota_acesso . '/recuperar' : $rota_acesso . '/redefinir/' . $link)); ?>">
                    <input type="hidden" name="token" value="<?= html_escape($token); ?>">
                    <?php if ($pagina !== 'redefinicao'): ?>
                        <div class="mb-3">
                            <label class="form-label" for="email">E-mail</label>
                            <input class="form-control" type="email" id="email" name="email" maxlength="150"
                                autocomplete="email" required value="<?= html_escape($email ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                    <?php if ($pagina === 'redefinicao' || !empty($mostrar_senha)): ?>
                        <div class="mb-3">
                            <label class="form-label"
                                for="senha"><?= $pagina === 'login' ? 'Senha' : 'Nova senha'; ?></label>
                            <input class="form-control" type="password" id="senha" name="senha"
                                autocomplete="<?= $pagina === 'login' ? 'current-password' : 'new-password'; ?>" required
                                <?= $pagina === 'redefinicao' ? 'minlength="8" maxlength="72"' : ''; ?>>
                            <?php if ($pagina === 'login'): ?>
                                <a href="<?= base_url('candidato/recuperar'); ?>">Esqueci minha senha</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($pagina === 'redefinicao'): ?>
                        <div class="mb-3">
                            <label class="form-label" for="confirmar_senha">Confirmar nova senha</label>
                            <input class="form-control" type="password" id="confirmar_senha" name="confirmar_senha"
                                autocomplete="new-password" minlength="8" maxlength="72" required>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-success w-100" type="submit">
                        <?= $pagina === 'login' ? (!empty($mostrar_senha) ? 'Entrar' : 'Continuar') : ($pagina === 'recuperacao' ? 'Solicitar link' : 'Salvar nova senha'); ?>
                    </button>
                </form>
                <div class="d-flex gap-2 mt-2">
                    <?php if ($pagina === 'login'): ?>

                        <a class="btn btn-outline-success w-50" href="<?= base_url('candidato/cadastro'); ?>">
                            Criar perfil
                        </a>
                        <a class="btn btn-outline-secondary w-50" href="<?= base_url('autenticacao/login'); ?>">
                            Acesso administrativo
                        </a>

                    <?php else: ?>
                        <a
                            href="<?= base_url($rota_acesso === 'recuperacao_usuario' ? 'autenticacao/login' : 'candidato/login'); ?>">
                            Voltar para o acesso
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>

</html>