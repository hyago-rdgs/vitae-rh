<?php
$modulo_atual = $this->uri->segment(1);
$formulario_ativo = in_array(
    $modulo_atual,
    ['formulario', 'formulario_secao', 'formulario_grupo'],
    TRUE
);
?>
<header class="bg-white border-bottom sticky-top">
    <nav class="navbar navbar-expand-lg" aria-label="Navegação principal">
        <section class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?= base_url(); ?>"
                aria-label="Vitae RH — página inicial">
                <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded p-2"
                    aria-hidden="true">
                    <i class="fa-solid fa-people-group"></i>
                </span>
                <span>Vitae RH</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navegacao-principal"
                aria-controls="navegacao-principal" aria-expanded="false" aria-label="Abrir navegação"><span
                    class="navbar-toggler-icon"></span></button>
            <section class="collapse navbar-collapse" id="navegacao-principal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
                    <?php if ($this->controle_acesso->tem_permissao('usuarios.gerenciar')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $modulo_atual == 'usuario' ? 'active fw-semibold' : ''; ?>"
                            <?= $modulo_atual == 'usuario' ? 'aria-current="page"' : ''; ?>
                            href="<?= base_url('usuario'); ?>">
                            Usuários
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->controle_acesso->tem_permissao('perfis.gerenciar')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $modulo_atual == 'perfil' ? 'active fw-semibold' : ''; ?>"
                            <?= $modulo_atual == 'perfil' ? 'aria-current="page"' : ''; ?>
                            href="<?= base_url('perfil'); ?>">
                            Perfis
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->controle_acesso->tem_permissao('formularios.gerenciar')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $formulario_ativo ? 'active fw-semibold' : ''; ?>"
                            <?= $formulario_ativo ? 'aria-current="page"' : ''; ?>
                            href="<?= base_url('formulario'); ?>">
                            Configuração do perfil
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                                class="fa-regular fa-circle-user" aria-hidden="true"></i>
                            <span class="d-flex flex-column text-start">
                                <span><?= html_escape($this->controle_acesso->get('nome')); ?></span>
                                <small class="text-body-secondary">
                                    <?= html_escape($this->controle_acesso->get('perfil_nome')); ?>
                                </small>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item log-out" href="<?= base_url('autenticacao/logout'); ?>">Sair</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </section>
        </section>
    </nav>
</header>
