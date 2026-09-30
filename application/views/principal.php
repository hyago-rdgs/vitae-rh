<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tela inicial do sistema Vitae RH">
    <title>Início | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">

    <?php $this->load->view('nav'); ?>

    <main class="container-fluid px-3 px-lg-4 py-4 py-lg-5">
        <section aria-labelledby="modulos-title">
            <header class="mb-3">
                <h2 class="h5 mb-1" id="modulos-title">Módulos</h2>
                <p class="small text-body-secondary mb-0">
                    Acesse as principais áreas de gerenciamento do Vitae RH.
                </p>
            </header>

            <div class="row g-3">
                <?php if ($this->controle_acesso->tem_permissao('formularios.gerenciar')): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card h-100 border shadow-sm text-decoration-none text-body"
                            href="<?= base_url('formulario'); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded p-3"
                                        aria-hidden="true">
                                        <i class="fa-solid fa-list-check fa-lg"></i>
                                    </span>
                                    <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                                </div>
                                <h3 class="h6 fw-semibold">Configuração do perfil</h3>
                                <p class="small text-body-secondary mb-0">
                                    Organize as seções, grupos e campos solicitados aos candidatos.
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($this->controle_acesso->tem_permissao('candidatos.consultar')): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card h-100 border shadow-sm text-decoration-none text-body"
                            href="<?= base_url('candidatos'); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded p-3"
                                        aria-hidden="true">
                                        <i class="fa-solid fa-users fa-lg"></i>
                                    </span>
                                    <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                                </div>
                                <h3 class="h6 fw-semibold">Candidatos</h3>
                                <p class="small text-body-secondary mb-0">
                                    Consulte os perfis recebidos e acesse os currículos.
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <a class="card h-100 border shadow-sm text-decoration-none text-body"
                        href="<?= base_url('candidato/cadastro'); ?>" target="_blank" rel="noopener">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                    aria-hidden="true">
                                    <i class="fa-solid fa-user-plus fa-lg"></i>
                                </span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-body-tertiary" aria-hidden="true"></i>
                            </div>
                            <h3 class="h6 fw-semibold">Cadastro do candidato</h3>
                            <p class="small text-body-secondary mb-0">
                                Acesse o formulário publicado para testar o fluxo de cadastro.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>
