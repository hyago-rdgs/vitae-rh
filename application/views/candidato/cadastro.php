<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cadastro de perfil de candidato">
    <title>Cadastro de candidato | Vitae RH</title>

    <?php $this->load->view('css'); ?>
</head>

<body class="bg-body-tertiary">
    <header class="bg-white border-bottom">
        <nav class="navbar" aria-label="Navegação principal">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?= base_url('candidato/cadastro'); ?>">
                    <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded p-2"
                        aria-hidden="true">
                        <i class="fa-solid fa-people-group"></i>
                    </span>
                    <span>Vitae RH</span>
                </a>
            </div>
        </nav>
    </header>

    <main class="container py-4 py-lg-5">
        <header class="mb-4">
            <p class="small text-success fw-semibold text-uppercase mb-1">
                Perfil do candidato · Versão <?= (int) $publicacao['versao']; ?>
            </p>
            <h1 class="h2 mb-2">Crie seu perfil</h1>
            <p class="text-body-secondary mb-0">
                Preencha seus dados e as informações solicitadas para participar de oportunidades.
            </p>
            <?php if (!empty($estrutura['formulario']['descricao'])): ?>
                <p class="text-body-secondary mt-2 mb-0">
                    <?= html_escape($estrutura['formulario']['descricao']); ?>
                </p>
            <?php endif; ?>
        </header>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger" role="alert" aria-labelledby="erros-title">
                <h2 class="h6 fw-semibold" id="erros-title">Não foi possível concluir o cadastro</h2>
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= html_escape($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('candidato/cadastro'); ?>" method="post"
            enctype="multipart/form-data" id="form-cadastro-candidato">
            <input type="hidden" name="token_cadastro" value="<?= html_escape($token_cadastro); ?>">
            <input type="hidden" name="formulario_publicacao_codigo"
                value="<?= (int) $publicacao['codigo']; ?>">
            <section class="card border shadow-sm mb-4" aria-labelledby="dados-pessoais-title">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-semibold mb-1" id="dados-pessoais-title">Dados pessoais e acesso</h2>
                    <p class="small text-body-secondary mb-0">Os campos marcados com * são obrigatórios.</p>
                </div>
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="nome_completo">
                                Nome completo <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="nome_completo" name="nome_completo"
                                type="text" maxlength="150" autocomplete="name" required
                                value="<?= html_escape($valores['nome_completo']); ?>">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="email">
                                E-mail de acesso <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="email" name="email" type="email"
                                maxlength="150" autocomplete="email" required
                                value="<?= html_escape($valores['email']); ?>">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="telefone">
                                Telefone/WhatsApp <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="telefone" name="telefone" type="tel"
                                maxlength="30" autocomplete="tel" required
                                value="<?= html_escape($valores['telefone']); ?>">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="foto_perfil">
                                Foto de perfil <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="foto_perfil" name="foto_perfil"
                                type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                            <div class="form-text">JPG ou PNG; tamanho máximo de 5 MB.</div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="curriculo">
                                Currículo <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="curriculo" name="curriculo"
                                type="file" accept=".pdf,.doc,.docx,application/pdf" required>
                            <div class="form-text">PDF, DOC ou DOCX; tamanho máximo de 10 MB.</div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="senha">
                                Senha <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="senha" name="senha" type="password"
                                minlength="8" maxlength="72" autocomplete="new-password" required>
                            <div class="form-text">Use entre 8 e 72 caracteres.</div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="confirmar_senha">
                                Confirmar senha <span class="text-danger">*</span>
                            </label>
                            <input class="form-control" id="confirmar_senha" name="confirmar_senha"
                                type="password" minlength="8" maxlength="72"
                                autocomplete="new-password" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" id="consentimento_lgpd"
                                    name="consentimento_lgpd" type="checkbox" value="1" required>
                                <label class="form-check-label" for="consentimento_lgpd">
                                    Autorizo o tratamento dos dados informados para fins de recrutamento,
                                    seleção e contato sobre oportunidades. <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php foreach ($estrutura['secoes'] as $secao): ?>
                <section class="card border shadow-sm mb-4" aria-labelledby="secao-<?= (int) $secao['codigo']; ?>">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 fw-semibold mb-1" id="secao-<?= (int) $secao['codigo']; ?>">
                            <?= html_escape($secao['titulo']); ?>
                        </h2>
                        <?php if (!empty($secao['descricao'])): ?>
                            <p class="small text-body-secondary mb-0">
                                <?= html_escape($secao['descricao']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-3 p-lg-4 d-grid gap-4">
                        <?php foreach ($secao['grupos'] as $grupo): ?>
                            <?php
                            $grupo_codigo = (int) $grupo['codigo'];
                            $repetivel = !empty($grupo['repetivel']);
                            $quantidade_minima = $repetivel
                                ? (int) $grupo['quantidade_minima']
                                : 1;
                            $quantidade_maxima = $repetivel
                                ? (int) $grupo['quantidade_maxima']
                                : 1;
                            $respostas_grupo = $valores['respostas'][(string) $grupo_codigo] ?? [];
                            $respostas_grupo = is_array($respostas_grupo)
                                ? array_values($respostas_grupo)
                                : [];
                            $quantidade_inicial = count($respostas_grupo) > 0
                                ? min(count($respostas_grupo), $quantidade_maxima)
                                : $quantidade_minima;

                            if (!$repetivel) {
                                $quantidade_inicial = 1;
                            }

                            $quantidade_inicial = max(
                                $quantidade_inicial,
                                $quantidade_minima
                            );
                            $respostas_grupo = array_slice(
                                $respostas_grupo,
                                0,
                                $quantidade_inicial
                            );

                            while (count($respostas_grupo) < $quantidade_inicial) {
                                $respostas_grupo[] = [];
                            }
                            ?>
                            <section class="border rounded p-3 p-lg-4" data-grupo-candidato
                                data-grupo-codigo="<?= $grupo_codigo; ?>"
                                data-min="<?= $quantidade_minima; ?>"
                                data-max="<?= $quantidade_maxima; ?>"
                                data-proximo-indice="<?= $quantidade_inicial + 1; ?>">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                    <div>
                                        <h3 class="h6 fw-semibold mb-1">
                                            <?= html_escape($grupo['nome']); ?>
                                        </h3>
                                        <?php if (!empty($grupo['descricao'])): ?>
                                            <p class="small text-body-secondary mb-0">
                                                <?= html_escape($grupo['descricao']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($repetivel): ?>
                                        <span class="badge text-bg-light border align-self-start">
                                            <?= $quantidade_minima; ?> a <?= $quantidade_maxima; ?> registros
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-grid gap-3" data-instancias-grupo>
                                    <?php foreach ($respostas_grupo as $posicao => $respostas_ocorrencia): ?>
                                        <?php if (!is_array($respostas_ocorrencia)) $respostas_ocorrencia = []; ?>
                                        <article class="rounded border bg-body-tertiary p-3" data-instancia-grupo>
                                            <?php if ($repetivel): ?>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="small fw-semibold mb-0" data-titulo-instancia>
                                                        Registro <?= $posicao + 1; ?>
                                                    </h4>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        type="button" data-remover-instancia>
                                                        Remover
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row g-3">
                                                <?php foreach ($grupo['campos'] as $campo): ?>
                                                    <?php $this->load->view(
                                                        'candidato/componentes/campo',
                                                        [
                                                            'campo' => $campo,
                                                            'grupo_codigo' => $grupo_codigo,
                                                            'indice' => $posicao + 1,
                                                            'valor' => $respostas_ocorrencia[$campo['chave']] ?? NULL
                                                        ]
                                                    ); ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($repetivel): ?>
                                    <template data-modelo-instancia>
                                        <article class="rounded border bg-body-tertiary p-3" data-instancia-grupo>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="small fw-semibold mb-0" data-titulo-instancia>
                                                    Novo registro
                                                </h4>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    type="button" data-remover-instancia>
                                                    Remover
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <?php foreach ($grupo['campos'] as $campo): ?>
                                                    <?php $this->load->view(
                                                        'candidato/componentes/campo',
                                                        [
                                                            'campo' => $campo,
                                                            'grupo_codigo' => $grupo_codigo,
                                                            'indice' => '__INDICE__',
                                                            'valor' => NULL
                                                        ]
                                                    ); ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </article>
                                    </template>
                                    <button class="btn btn-sm btn-light border mt-3 align-self-start"
                                        type="button" data-adicionar-instancia>
                                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>
                                        Adicionar registro
                                    </button>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                <button class="btn btn-success btn-lg" type="submit" id="btn-cadastrar-candidato">
                    <i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>
                    Criar meu perfil
                </button>
            </div>
        </form>
    </main>

    <?php $this->load->view('js'); ?>
    <script>
        $(document).ready(function () {
            $('[data-grupo-candidato]').each(function () {
                atualizar_instancias($(this));
            });

            $(document).on('click', '[data-adicionar-instancia]', function () {
                const grupo = $(this).closest('[data-grupo-candidato]');
                const template = grupo.find('template[data-modelo-instancia]')[0];
                const indice = parseInt(grupo.attr('data-proximo-indice'), 10) || 1;
                const html = template.innerHTML.split('__INDICE__').join(indice);
                const elemento = document.createElement('div');
                elemento.innerHTML = html.trim();
                grupo.find('[data-instancias-grupo]').append(elemento.firstElementChild);
                grupo.attr('data-proximo-indice', indice + 1);
                atualizar_instancias(grupo);
            });

            $(document).on('click', '[data-remover-instancia]', function () {
                const grupo = $(this).closest('[data-grupo-candidato]');
                const quantidade = grupo.find('[data-instancia-grupo]').length;
                const minimo = parseInt(grupo.attr('data-min'), 10) || 0;

                if (quantidade > minimo) {
                    $(this).closest('[data-instancia-grupo]').remove();
                    atualizar_instancias(grupo);
                }
            });

            $('#form-cadastro-candidato').on('submit', function () {
                $('#btn-cadastrar-candidato')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Enviando...');
            });
        });

        function atualizar_instancias(grupo) {
            const instancias = grupo.find('[data-instancia-grupo]');
            const minimo = parseInt(grupo.attr('data-min'), 10) || 0;
            const maximo = parseInt(grupo.attr('data-max'), 10) || 1;

            instancias.each(function (indice) {
                $(this).find('[data-titulo-instancia]').text('Registro ' + (indice + 1));
                $(this).find('[data-remover-instancia]').prop(
                    'disabled',
                    instancias.length <= minimo
                );
            });

            grupo.find('[data-adicionar-instancia]').prop(
                'disabled',
                instancias.length >= maximo
            );
        }
    </script>
</body>

</html>
