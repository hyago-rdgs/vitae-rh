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
                <?php if ($this->controle_acesso->tem_permissao('modulo.acao')): ?>
                    <!-- EXEMPLO DE CARD: -->

                    <!-- <div class="col-12 col-md-6 col-xl-4">
                        <a class="card h-100 border shadow-sm text-decoration-none text-body"
                            href="<?= base_url(''); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded p-3"
                                        aria-hidden="true">
                                        <i class="fa-solid fa-chart-column fa-lg"></i>
                                    </span>
                                    <i class="fa-solid fa-arrow-right text-body-tertiary" aria-hidden="true"></i>
                                </div>
                                <h3 class="h6 fw-semibold">Dashboard</h3>
                                <p class="small text-body-secondary mb-0">
                                    Acompanhe indicadores do acervo, digitalização e movimentações.
                                </p>
                            </div>
                        </a>
                    </div> -->
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php $this->load->view('js'); ?>
</body>

</html>