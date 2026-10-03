<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato extends CI_Controller
{
    private $versao_consentimento = 'cadastro_v1';

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('formulario_model');
        $this->load->model('formulario_publicacao_model');
        $this->load->model('candidato_model');
        $this->load->model('candidato_formulario_model');
        $this->load->model('candidato_grupo_model');
        $this->load->model('candidato_resposta_model');
    }

    public function index()
    {
        redirect('candidato/cadastro');
    }

    public function cadastro()
    {
        $contexto = $this->buscar_publicacao_vigente();

        if (!$contexto) {
            show_error(
                'O cadastro de candidatos ainda não está disponível.',
                503,
                'Formulário indisponível'
            );
        }

        if ($this->input->method() !== 'post') {
            if ($this->input->method() !== 'get') {
                show_404();
            }

            $this->exibir_cadastro($contexto);
            return;
        }

        $post = $this->input->post(NULL, FALSE);

        if (!$this->validar_token_cadastro($post)) {
            $this->exibir_cadastro(
                $contexto,
                ['A sessão do formulário expirou. Atualize a página e tente novamente.'],
                $post
            );
            return;
        }

        if (
            !isset($post['formulario_publicacao_codigo']) ||
            (int) $post['formulario_publicacao_codigo'] !==
                (int) $contexto['publicacao']['codigo']
        ) {
            $this->exibir_cadastro(
                $contexto,
                ['O formulário foi atualizado. Confira os campos e envie novamente.'],
                $post
            );
            return;
        }

        $validacao = $this->validar_cadastro($post, $contexto['estrutura']);

        if (!$validacao['sucesso']) {
            $this->exibir_cadastro(
                $contexto,
                $validacao['erros'],
                $post
            );
            return;
        }

        $arquivos = $this->receber_arquivos();

        if (!$arquivos['sucesso']) {
            $this->exibir_cadastro(
                $contexto,
                $arquivos['erros'],
                $post
            );
            return;
        }

        $salvo = $this->salvar_cadastro(
            $validacao['dados'],
            $validacao['respostas'],
            $arquivos['dados'],
            $contexto['publicacao']
        );

        if (!$salvo) {
            $this->remover_arquivos($arquivos['dados']);
            $this->exibir_cadastro(
                $contexto,
                ['Não foi possível concluir o cadastro. Tente novamente.'],
                $post
            );
            return;
        }

        $this->session->set_flashdata('candidato_cadastro_concluido', TRUE);
        redirect('candidato/sucesso', 'location', 303);
    }

    public function sucesso()
    {
        if (!$this->session->flashdata('candidato_cadastro_concluido')) {
            redirect('candidato/cadastro');
            return;
        }

        $this->load->view('candidato/cadastro_sucesso');
    }

    protected function buscar_publicacao_vigente()
    {
        $publicacao = $this->formulario_publicacao_model->buscar_vigente();
        if (!$publicacao) return FALSE;
        $estrutura = json_decode($publicacao['estrutura'], TRUE);

        if (!$this->estrutura_valida($estrutura)) {
            return FALSE;
        }

        return [
            'publicacao' => $publicacao,
            'estrutura' => $estrutura
        ];
    }

    protected function estrutura_valida($estrutura)
    {
        if (
            !is_array($estrutura) ||
            empty($estrutura['campos_fixos']) ||
            !isset($estrutura['secoes']) ||
            !is_array($estrutura['secoes'])
        ) {
            return FALSE;
        }

        $tipos_permitidos = [
            'texto_curto',
            'texto_longo',
            'numero',
            'data',
            'sim_nao',
            'selecao_unica',
            'multipla_selecao'
        ];

        foreach ($estrutura['secoes'] as $secao) {
            if (
                !is_array($secao) ||
                !isset($secao['grupos']) ||
                !is_array($secao['grupos'])
            ) {
                return FALSE;
            }

            foreach ($secao['grupos'] as $grupo) {
                if (
                    !is_array($grupo) ||
                    empty($grupo['codigo']) ||
                    !isset($grupo['campos']) ||
                    !is_array($grupo['campos'])
                ) {
                    return FALSE;
                }

                if (
                    !empty($grupo['repetivel']) &&
                    (
                        !isset($grupo['quantidade_maxima']) ||
                        (int) $grupo['quantidade_maxima'] < 1 ||
                        (int) ($grupo['quantidade_minima'] ?? 0) >
                            (int) $grupo['quantidade_maxima']
                    )
                ) {
                    return FALSE;
                }

                foreach ($grupo['campos'] as $campo) {
                    if (
                        !is_array($campo) ||
                        empty($campo['chave']) ||
                        !in_array($campo['tipo'] ?? '', $tipos_permitidos, TRUE)
                    ) {
                        return FALSE;
                    }

                    if (
                        in_array(
                            $campo['tipo'],
                            ['selecao_unica', 'multipla_selecao'],
                            TRUE
                        ) &&
                        empty($campo['opcoes'])
                    ) {
                        return FALSE;
                    }
                }
            }
        }

        return TRUE;
    }

    private function exibir_cadastro($contexto, $erros = [], $post = [])
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set_userdata('candidato_cadastro_token', $token);

        $this->load->view(
            'candidato/cadastro',
            [
                'publicacao' => $contexto['publicacao'],
                'estrutura' => $contexto['estrutura'],
                'erros' => $erros,
                'token_cadastro' => $token,
                'valores' => $this->preparar_valores_view($post)
            ]
        );
    }

    private function validar_token_cadastro($post)
    {
        $token_sessao = $this->session->userdata('candidato_cadastro_token');
        $token_post = isset($post['token_cadastro']) &&
            is_string($post['token_cadastro'])
                ? $post['token_cadastro']
                : '';

        if (
            !is_string($token_sessao) ||
            $token_sessao === '' ||
            $token_post === '' ||
            !hash_equals($token_sessao, $token_post)
        ) {
            return FALSE;
        }

        $this->session->unset_userdata('candidato_cadastro_token');

        return TRUE;
    }

    private function preparar_valores_view($post)
    {
        $valores = [];

        foreach (['nome_completo', 'email', 'telefone'] as $chave) {
            $valores[$chave] = isset($post[$chave]) && is_string($post[$chave])
                ? $post[$chave]
                : '';
        }

        $valores['respostas'] = isset($post['respostas']) &&
            is_array($post['respostas'])
                ? $post['respostas']
                : [];

        return $valores;
    }

    private function validar_cadastro($post, $estrutura)
    {
        $erros = [];
        $nome = isset($post['nome_completo']) && is_string($post['nome_completo'])
            ? trim($post['nome_completo'])
            : '';
        $email = isset($post['email']) && is_string($post['email'])
            ? strtolower(trim($post['email']))
            : '';
        $telefone = isset($post['telefone']) && is_string($post['telefone'])
            ? trim($post['telefone'])
            : '';
        $senha = isset($post['senha']) && is_string($post['senha'])
            ? $post['senha']
            : '';
        $confirmar_senha = isset($post['confirmar_senha']) &&
            is_string($post['confirmar_senha'])
                ? $post['confirmar_senha']
                : '';

        if ($nome === '') {
            $erros[] = 'O campo Nome completo é obrigatório.';
        } elseif ($this->tamanho_texto($nome) > 150) {
            $erros[] = 'O Nome completo deve possuir no máximo 150 caracteres.';
        }

        if ($email === '') {
            $erros[] = 'O campo E-mail é obrigatório.';
        } elseif (
            $this->tamanho_texto($email) > 150 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $erros[] = 'Informe um endereço de e-mail válido.';
        } elseif ($this->candidato_model->email_em_uso($email)) {
            $erros[] = 'O e-mail informado já está cadastrado.';
        }

        if ($telefone === '') {
            $erros[] = 'O campo Telefone/WhatsApp é obrigatório.';
        } elseif (
            strlen($telefone) > 30 ||
            !preg_match('/^[0-9+().\s-]{8,30}$/', $telefone)
        ) {
            $erros[] = 'Informe um Telefone/WhatsApp válido.';
        }

        if (strlen($senha) < 8 || strlen($senha) > 72) {
            $erros[] = 'A senha deve possuir entre 8 e 72 caracteres.';
        } elseif ($senha !== $confirmar_senha) {
            $erros[] = 'A confirmação da senha não corresponde à senha informada.';
        }

        if (
            !isset($post['consentimento_lgpd']) ||
            $post['consentimento_lgpd'] !== '1'
        ) {
            $erros[] = 'É necessário aceitar o consentimento para concluir o cadastro.';
        }

        $respostas = $this->validar_respostas($estrutura, $post, $erros);

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        return [
            'sucesso' => TRUE,
            'dados' => [
                'nome_completo' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'telefone' => $telefone,
                'consentimento_lgpd' => 1,
                'consentimento_lgpd_versao' => $this->versao_consentimento,
                'consentimento_lgpd_em' => date('Y-m-d H:i:s'),
                'status' => 'ativo'
            ],
            'respostas' => $respostas
        ];
    }

    protected function validar_respostas($estrutura, $post, &$erros)
    {
        $recebidas = isset($post['respostas']) && is_array($post['respostas'])
            ? $post['respostas']
            : [];
        $codigos_grupos_validos = [];
        $respostas_validadas = [];

        foreach ($estrutura['secoes'] as $secao) {
            foreach (($secao['grupos'] ?? []) as $grupo) {
                $grupo_codigo = (int) $grupo['codigo'];
                $codigos_grupos_validos[] = (string) $grupo_codigo;
                $linhas = $recebidas[(string) $grupo_codigo] ?? [];

                if (!is_array($linhas)) {
                    $erros[] = 'As informações de um grupo do formulário são inválidas.';
                    continue;
                }

                $indices = array_keys($linhas);
                usort($indices, 'strnatcmp');
                $linhas_ordenadas = [];

                foreach ($indices as $indice) {
                    if (!ctype_digit((string) $indice) || !is_array($linhas[$indice])) {
                        $erros[] = 'As ocorrências de um grupo do formulário são inválidas.';
                        continue;
                    }

                    $linhas_ordenadas[] = $linhas[$indice];
                }

                $repetivel = !empty($grupo['repetivel']);
                $quantidade = count($linhas_ordenadas);
                $minima = $repetivel
                    ? (int) ($grupo['quantidade_minima'] ?? 0)
                    : 1;
                $maxima = $repetivel
                    ? (int) $grupo['quantidade_maxima']
                    : 1;

                if ($quantidade < $minima || $quantidade > $maxima) {
                    $erros[] = 'O grupo "' . $grupo['nome'] . '" deve possuir ' .
                        ($minima === $maxima
                            ? $minima . ' registro(s).'
                            : 'entre ' . $minima . ' e ' . $maxima . ' registros.');
                    continue;
                }

                $campos_validos = array_column($grupo['campos'], 'chave');
                $respostas_validadas[$grupo_codigo] = [];

                foreach ($linhas_ordenadas as $indice => $linha) {
                    $indice_registro = $indice + 1;
                    $campos_enviados = array_diff(
                        array_keys($linha),
                        $campos_validos
                    );

                    if (!empty($campos_enviados)) {
                        $erros[] = 'Foram enviados campos não pertencentes ao grupo "' .
                            $grupo['nome'] . '".';
                    }

                    $respostas_validadas[$grupo_codigo][$indice_registro] = [];

                    foreach ($grupo['campos'] as $campo) {
                        $valor = $linha[$campo['chave']] ?? NULL;
                        $validacao = $this->validar_campo($campo, $valor);

                        if (!$validacao['sucesso']) {
                            $erros[] = $grupo['nome'] . ' (registro ' .
                                $indice_registro . '): ' . $validacao['erro'];
                            continue;
                        }

                        if (!$validacao['vazio']) {
                            $respostas_validadas[$grupo_codigo][$indice_registro][] = [
                                'campo_chave' => $campo['chave'],
                                'valor' => $validacao['valor']
                            ];
                        }
                    }
                }
            }
        }

        $grupos_nao_publicados = array_diff(
            array_keys($recebidas),
            $codigos_grupos_validos
        );

        if (!empty($grupos_nao_publicados)) {
            $erros[] = 'O formulário recebido não corresponde à publicação vigente.';
        }

        return $respostas_validadas;
    }

    private function validar_campo($campo, $valor)
    {
        $tipo = $campo['tipo'];
        $obrigatorio = !empty($campo['obrigatorio']);
        $vazio = $valor === NULL || $valor === '' || $valor === [];

        if ($tipo === 'multipla_selecao') {
            if ($vazio) {
                return $this->resultado_campo_vazio($obrigatorio, $campo['nome']);
            }

            if (!is_array($valor)) {
                return $this->erro_campo('Selecione opções válidas para ' . $campo['nome'] . '.');
            }

            $valores_permitidos = array_column($campo['opcoes'], 'valor');

            foreach ($valor as $opcao) {
                if (!is_string($opcao) || !in_array($opcao, $valores_permitidos, TRUE)) {
                    return $this->erro_campo('Há uma opção inválida em ' . $campo['nome'] . '.');
                }
            }

            $valores = array_values(array_unique($valor));

            return [
                'sucesso' => TRUE,
                'vazio' => FALSE,
                'valor' => [
                    'valor_json' => json_encode(
                        $valores,
                        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                    )
                ]
            ];
        }

        if ($vazio) {
            return $this->resultado_campo_vazio($obrigatorio, $campo['nome']);
        }

        if (!is_string($valor)) {
            return $this->erro_campo('O valor informado para ' . $campo['nome'] . ' é inválido.');
        }

        switch ($tipo) {
            case 'texto_curto':
            case 'texto_longo':
                $valor = trim($valor);

                if ($valor === '') {
                    return $this->resultado_campo_vazio($obrigatorio, $campo['nome']);
                }

                $tamanho_maximo = $campo['tamanho_maximo'] !== NULL
                    ? (int) $campo['tamanho_maximo']
                    : NULL;

                if (
                    ($tamanho_maximo !== NULL &&
                        $this->tamanho_texto($valor) > $tamanho_maximo) ||
                    strlen($valor) > 60000
                ) {
                    return $this->erro_campo('O valor de ' . $campo['nome'] . ' excede o tamanho permitido.');
                }

                return [
                    'sucesso' => TRUE,
                    'vazio' => FALSE,
                    'valor' => ['valor_texto' => $valor]
                ];

            case 'numero':
                if (
                    !preg_match('/^-?[0-9]{1,11}(?:\.[0-9]{1,4})?$/', $valor) ||
                    !is_numeric($valor) ||
                    !is_finite((float) $valor)
                ) {
                    return $this->erro_campo('Informe um número válido para ' . $campo['nome'] . '.');
                }

                $numero = (float) $valor;

                if (
                    ($campo['numero_minimo'] !== NULL &&
                        $numero < (float) $campo['numero_minimo']) ||
                    ($campo['numero_maximo'] !== NULL &&
                        $numero > (float) $campo['numero_maximo'])
                ) {
                    return $this->erro_campo('O número informado para ' . $campo['nome'] . ' está fora dos limites.');
                }

                return [
                    'sucesso' => TRUE,
                    'vazio' => FALSE,
                    'valor' => ['valor_numero' => $valor]
                ];

            case 'data':
                $data = DateTime::createFromFormat('!Y-m-d', $valor);
                $erros_data = DateTime::getLastErrors();

                if (
                    !$data ||
                    ($erros_data && ($erros_data['warning_count'] || $erros_data['error_count'])) ||
                    $data->format('Y-m-d') !== $valor
                ) {
                    return $this->erro_campo('Informe uma data válida para ' . $campo['nome'] . '.');
                }

                if (
                    (!empty($campo['data_minima']) && $valor < $campo['data_minima']) ||
                    (!empty($campo['data_maxima']) && $valor > $campo['data_maxima'])
                ) {
                    return $this->erro_campo('A data informada para ' . $campo['nome'] . ' está fora dos limites.');
                }

                return [
                    'sucesso' => TRUE,
                    'vazio' => FALSE,
                    'valor' => ['valor_data' => $valor]
                ];

            case 'sim_nao':
                if (!in_array($valor, ['sim', 'nao'], TRUE)) {
                    return $this->erro_campo('Selecione Sim ou Não para ' . $campo['nome'] . '.');
                }

                return [
                    'sucesso' => TRUE,
                    'vazio' => FALSE,
                    'valor' => ['valor_booleano' => $valor === 'sim' ? 1 : 0]
                ];

            case 'selecao_unica':
                $valores_permitidos = array_column($campo['opcoes'], 'valor');

                if (!in_array($valor, $valores_permitidos, TRUE)) {
                    return $this->erro_campo('Selecione uma opção válida para ' . $campo['nome'] . '.');
                }

                return [
                    'sucesso' => TRUE,
                    'vazio' => FALSE,
                    'valor' => ['valor_texto' => $valor]
                ];
        }

        return $this->erro_campo('O tipo do campo ' . $campo['nome'] . ' não é suportado.');
    }

    private function resultado_campo_vazio($obrigatorio, $nome)
    {
        if ($obrigatorio) {
            return $this->erro_campo('O campo ' . $nome . ' é obrigatório.');
        }

        return [
            'sucesso' => TRUE,
            'vazio' => TRUE,
            'valor' => []
        ];
    }

    private function erro_campo($mensagem)
    {
        return [
            'sucesso' => FALSE,
            'vazio' => FALSE,
            'erro' => $mensagem
        ];
    }

    protected function receber_arquivos($obrigatorios = TRUE)
    {
        $diretorio = FCPATH . 'uploads/candidatos/';

        if (
            !is_dir($diretorio) &&
            !mkdir($diretorio, 0750, TRUE) &&
            !is_dir($diretorio)
        ) {
            return [
                'sucesso' => FALSE,
                'erros' => ['Não foi possível preparar o armazenamento dos arquivos.']
            ];
        }

        if (!is_writable($diretorio)) {
            return [
                'sucesso' => FALSE,
                'erros' => ['O armazenamento de arquivos não está disponível.']
            ];
        }

        $this->load->library('upload');
        $arquivos = [];
        $erros = [];
        $configuracoes = [
            'foto_perfil' => [
                'rotulo' => 'Foto de perfil',
                'tipos' => 'jpg|jpeg|png',
                'tamanho' => 5120
            ],
            'curriculo' => [
                'rotulo' => 'Currículo',
                'tipos' => 'pdf|doc|docx',
                'tamanho' => 10240
            ]
        ];

        foreach ($configuracoes as $campo => $configuracao) {
            if (
                !isset($_FILES[$campo]) ||
                !is_array($_FILES[$campo]) ||
                !isset($_FILES[$campo]['error']) ||
                is_array($_FILES[$campo]['error']) ||
                (int) $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE
            ) {
                if ($obrigatorios) {
                    $erros[] = 'O arquivo ' . $configuracao['rotulo'] . ' é obrigatório.';
                }
                continue;
            }

            if ((int) $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
                $erros[] = 'Não foi possível receber o arquivo ' .
                    $configuracao['rotulo'] . '.';
                continue;
            }

            $config = [
                'upload_path' => $diretorio,
                'allowed_types' => $configuracao['tipos'],
                'max_size' => $configuracao['tamanho'],
                'encrypt_name' => TRUE,
                'remove_spaces' => TRUE,
                'detect_mime' => TRUE,
                'mod_mime_fix' => TRUE
            ];

            $this->upload->initialize($config, TRUE);

            if (!$this->upload->do_upload($campo)) {
                $erros[] = strip_tags(
                    $this->upload->display_errors('', '')
                );
                continue;
            }

            $arquivo = $this->upload->data();

            if ($this->tamanho_texto($arquivo['client_name']) > 255) {
                unlink($arquivo['full_path']);
                $erros[] = 'O nome original do arquivo ' .
                    $configuracao['rotulo'] . ' excede 255 caracteres.';
                continue;
            }

            $arquivos[$campo] = [
                'nome_armazenado' => $arquivo['file_name'],
                'nome_original' => $arquivo['client_name'],
                'mime' => $arquivo['file_type'],
                'tamanho' => (int) filesize($arquivo['full_path'])
            ];
        }

        if (!empty($erros)) {
            $this->remover_arquivos($arquivos);

            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        return [
            'sucesso' => TRUE,
            'dados' => $arquivos
        ];
    }

    private function salvar_cadastro($candidato, $respostas, $arquivos, $publicacao)
    {
        if (!$this->db->trans_begin()) {
            return FALSE;
        }
        if (!$this->formulario_publicacao_model->conferir_vigente($publicacao['codigo'])) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $candidato['foto_arquivo'] = $arquivos['foto_perfil']['nome_armazenado'];
        $candidato['curriculo_arquivo'] = $arquivos['curriculo']['nome_armazenado'];
        $candidato['curriculo_nome_original'] = $arquivos['curriculo']['nome_original'];
        $candidato['curriculo_mime'] = $arquivos['curriculo']['mime'];
        $candidato['curriculo_tamanho'] = $arquivos['curriculo']['tamanho'];

        $candidato_codigo = $this->candidato_model->cadastrar($candidato);

        $candidato_formulario_codigo = $candidato_codigo
            ? $this->candidato_formulario_model->cadastrar([
                'candidato_codigo' => $candidato_codigo,
                'formulario_publicacao_codigo' => $publicacao['codigo'],
                'status' => 'rascunho'
            ])
            : FALSE;

        $salvamento_respostas = (bool) $candidato_formulario_codigo;

        if ($salvamento_respostas) {
            foreach ($respostas as $grupo_codigo => $ocorrencias) {
                foreach ($ocorrencias as $indice => $campos) {
                    $candidato_grupo_codigo = $this->candidato_grupo_model
                        ->cadastrar([
                            'candidato_formulario_codigo' => $candidato_formulario_codigo,
                            'grupo_codigo' => $grupo_codigo,
                            'indice' => $indice
                        ]);

                    if (!$candidato_grupo_codigo) {
                        $salvamento_respostas = FALSE;
                        break 2;
                    }

                    foreach ($campos as $resposta) {
                        if (!$this->candidato_resposta_model->salvar(
                            $candidato_grupo_codigo,
                            $resposta['campo_chave'],
                            $resposta['valor']
                        )) {
                            $salvamento_respostas = FALSE;
                            break 3;
                        }
                    }
                }
            }
        }

        $formulario_concluido = $salvamento_respostas
            ? $this->candidato_formulario_model->concluir(
                $candidato_formulario_codigo
            )
            : FALSE;

        if (
            !$candidato_codigo ||
            !$candidato_formulario_codigo ||
            !$salvamento_respostas ||
            !$formulario_concluido ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();
            return FALSE;
        }

        if (!$this->db->trans_commit()) {
            $this->db->trans_rollback();
            return FALSE;
        }

        return TRUE;
    }

    protected function remover_arquivos($arquivos)
    {
        $diretorio = FCPATH . 'uploads/candidatos/';

        foreach ($arquivos as $arquivo) {
            if (empty($arquivo['nome_armazenado'])) {
                continue;
            }

            $caminho = $diretorio . basename($arquivo['nome_armazenado']);

            if (is_file($caminho)) {
                unlink($caminho);
            }
        }
    }

    protected function tamanho_texto($valor)
    {
        return function_exists('mb_strlen')
            ? mb_strlen($valor, 'UTF-8')
            : strlen($valor);
    }
}
