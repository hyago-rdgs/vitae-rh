<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Candidato.php';

class Portal_candidato extends Candidato
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('controle_candidato');
        $this->load->model('candidato_recuperacao_model');
    }

    public function login()
    {
        if ($this->controle_candidato->buscar()) {
            redirect('candidato/portal');
            return;
        }

        $erro = '';
        $email = '';

        if ($this->input->method() === 'post') {
            $post = $this->input->post(NULL, FALSE);
            $email = is_string($post['email'] ?? NULL)
                ? strtolower(trim($post['email'])) : '';
            $senha = is_string($post['senha'] ?? NULL) ? $post['senha'] : '';

            if (!$this->validar_token($post, 'login')) {
                $erro = 'A sessão expirou. Atualize a página e tente novamente.';
            } else {
                $candidato = $this->candidato_model->buscar_para_autenticacao($email);

                if (!$candidato || !password_verify($senha, $candidato['senha'])) {
                    $erro = 'E-mail ou senha inválidos.';
                } else {
                    $this->controle_candidato->criar($candidato);
                    $this->candidato_model->atualizar_ultimo_acesso($candidato['codigo']);
                    redirect('candidato/portal', 'location', 303);
                    return;
                }
            }
        } elseif ($this->input->method() !== 'get') {
            show_404();
        }

        $this->load->view('candidato/acesso', [
            'pagina' => 'login', 'erro' => $erro, 'email' => $email,
            'token' => $this->novo_token('login')
        ]);
    }

    public function logout()
    {
        if ($this->input->method() !== 'post' ||
            !$this->validar_token($this->input->post(NULL, FALSE), 'logout')) {
            show_error('Solicitação inválida.', 403);
        }

        $this->controle_candidato->destruir();
        redirect('candidato/login', 'location', 303);
    }

    public function solicitar_recuperacao()
    {
        $erro = '';
        $sucesso = FALSE;

        if ($this->input->method() === 'post') {
            $post = $this->input->post(NULL, FALSE);

            if (!$this->validar_token($post, 'recuperacao')) {
                $erro = 'A sessão expirou. Atualize a página e tente novamente.';
            } else {
                $email = is_string($post['email'] ?? NULL)
                    ? strtolower(trim($post['email'])) : '';
                $candidato = filter_var($email, FILTER_VALIDATE_EMAIL)
                    ? $this->candidato_model->buscar_para_autenticacao($email) : FALSE;

                if ($candidato) {
                    $token = bin2hex(random_bytes(32));
                    $hash = hash('sha256', $token);

                    if ($this->candidato_recuperacao_model->solicitar($candidato['codigo'], $hash)) {
                        $this->enviar_recuperacao($candidato['email'], $token);
                    }
                }

                $sucesso = TRUE;
            }
        } elseif ($this->input->method() !== 'get') {
            show_404();
        }

        $this->load->view('candidato/acesso', [
            'pagina' => 'recuperacao', 'erro' => $erro, 'sucesso' => $sucesso,
            'token' => $this->novo_token('recuperacao')
        ]);
    }

    public function redefinir_senha($token = '')
    {
        $this->output->set_header('Referrer-Policy: no-referrer');

        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
            show_404();
        }

        $hash = hash('sha256', $token);
        $recuperacao = $this->candidato_recuperacao_model->buscar_valido($hash);

        if (!$recuperacao) {
            show_error('O link expirou ou já foi utilizado. Solicite um novo link.', 410);
        }

        $erro = '';

        if ($this->input->method() === 'post') {
            $post = $this->input->post(NULL, FALSE);
            $senha = is_string($post['senha'] ?? NULL) ? $post['senha'] : '';
            $confirmacao = is_string($post['confirmar_senha'] ?? NULL)
                ? $post['confirmar_senha'] : '';

            if (!$this->validar_token($post, 'redefinicao')) {
                $erro = 'A sessão expirou. Atualize a página e tente novamente.';
            } elseif (strlen($senha) < 8 || strlen($senha) > 72 || $senha !== $confirmacao) {
                $erro = 'Informe uma senha entre 8 e 72 caracteres e confirme-a corretamente.';
            } elseif (!$this->db->trans_begin()) {
                $erro = 'Não foi possível redefinir a senha. Tente novamente.';
            } else {
                $consumido = $this->candidato_recuperacao_model->consumir(
                    $hash, $recuperacao['candidato_codigo']
                );
                $atualizado = $consumido && $this->candidato_model->atualizar(
                    $recuperacao['candidato_codigo'],
                    ['senha' => password_hash($senha, PASSWORD_DEFAULT)]
                );
                $invalidado = $atualizado && $this->candidato_recuperacao_model
                    ->invalidar_outros($recuperacao['candidato_codigo']);

                if (!$invalidado || $this->db->trans_status() === FALSE ||
                    !$this->db->trans_commit()) {
                    $this->db->trans_rollback();
                    $erro = 'Não foi possível redefinir a senha. Solicite outro link.';
                } else {
                    $this->controle_candidato->destruir();
                    $this->session->set_flashdata('candidato_senha_redefinida', TRUE);
                    redirect('candidato/login', 'location', 303);
                    return;
                }
            }
        } elseif ($this->input->method() !== 'get') {
            show_404();
        }

        $this->load->view('candidato/acesso', [
            'pagina' => 'redefinicao', 'erro' => $erro, 'link' => $token,
            'token' => $this->novo_token('redefinicao')
        ]);
    }

    public function perfil()
    {
        $candidato = $this->controle_candidato->exigir();

        if ($this->input->method() !== 'get') {
            show_404();
        }

        $formulario = $this->candidato_formulario_model
            ->buscar_por_candidato($candidato['codigo']);
        $publicacao = $formulario ? $this->formulario_publicacao_model
            ->buscar_por_codigo($formulario['formulario_publicacao_codigo']) : FALSE;
        $estrutura = $publicacao ? json_decode($publicacao['estrutura'], TRUE) : FALSE;

        if (!$formulario || !$this->estrutura_valida($estrutura)) {
            show_error('As informações do perfil não estão disponíveis.', 500);
        }

        $this->load->view('candidato/perfil', [
            'candidato' => $candidato,
            'formulario' => $formulario,
            'estrutura' => $estrutura,
            'respostas' => $this->valores_salvos($formulario['codigo']),
            'token_logout' => $this->novo_token('logout'),
            'salvo' => $this->session->flashdata('candidato_perfil_salvo')
        ]);
    }

    public function editar()
    {
        $candidato = $this->controle_candidato->exigir();
        $contexto = $this->buscar_publicacao_vigente();

        if (!$contexto) {
            show_error('A edição está indisponível até que um formulário seja publicado.', 503);
        }

        $formulario = $this->candidato_formulario_model
            ->buscar_por_candidato($candidato['codigo']);

        if (!$formulario) {
            show_error('As informações do perfil não estão disponíveis.', 500);
        }

        $valores = [
            'nome_completo' => $candidato['nome_completo'],
            'email' => $candidato['email'],
            'telefone' => $candidato['telefone'],
            'respostas' => $this->valores_salvos($formulario['codigo'])
        ];
        $erros = [];

        if ($this->input->method() === 'post') {
            $post = $this->input->post(NULL, FALSE);
            $valores = array_merge($valores, [
                'nome_completo' => is_string($post['nome_completo'] ?? NULL) ? $post['nome_completo'] : '',
                'email' => is_string($post['email'] ?? NULL) ? $post['email'] : '',
                'telefone' => is_string($post['telefone'] ?? NULL) ? $post['telefone'] : '',
                'respostas' => is_array($post['respostas'] ?? NULL) ? $post['respostas'] : []
            ]);

            if (!$this->validar_token($post, 'edicao')) {
                $erros[] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
            } elseif ((int) ($post['formulario_publicacao_codigo'] ?? 0) !==
                (int) $contexto['publicacao']['codigo'] ||
                (int) ($post['candidato_formulario_codigo'] ?? 0) !== (int) $formulario['codigo']) {
                $erros[] = 'O formulário ou seu perfil mudou. Atualize a página antes de salvar.';
            } else {
                $dados = $this->validar_dados_edicao($post, $candidato, $erros);
                $respostas = $this->validar_respostas($contexto['estrutura'], $post, $erros);

                if (!$erros) {
                    $arquivos = $this->receber_arquivos(FALSE);
                    $erros = $arquivos['sucesso'] ? [] : $arquivos['erros'];

                    if (!$erros) {
                        if ($this->salvar_edicao(
                            $candidato, $formulario, $dados, $respostas,
                            $arquivos['dados'], $contexto['publicacao']
                        )) {
                            $this->session->set_flashdata('candidato_perfil_salvo', TRUE);
                            redirect('candidato/portal', 'location', 303);
                            return;
                        }

                        $this->remover_arquivos($arquivos['dados']);
                        $erros[] = 'Não foi possível salvar o perfil. Atualize a página e tente novamente.';
                    }
                }
            }
        } elseif ($this->input->method() !== 'get') {
            show_404();
        }

        $this->load->view('candidato/cadastro', [
            'publicacao' => $contexto['publicacao'],
            'estrutura' => $contexto['estrutura'],
            'erros' => $erros,
            'token_cadastro' => $this->novo_token('edicao'),
            'valores' => $valores,
            'edicao' => TRUE,
            'candidato' => $candidato,
            'formulario_atual_codigo' => $formulario['codigo']
        ]);
    }

    public function foto()
    {
        $candidato = $this->controle_candidato->exigir();

        if ($this->input->method() !== 'get') show_404();

        $caminho = $this->caminho_arquivo($candidato['foto_arquivo']);
        $informacao = $caminho && is_readable($caminho) ? @getimagesize($caminho) : FALSE;

        if (!$informacao || !in_array($informacao['mime'], ['image/jpeg', 'image/png'], TRUE)) {
            show_404();
        }

        $this->output->set_header('Content-Type: ' . $informacao['mime'])
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_output(file_get_contents($caminho));
    }

    public function curriculo()
    {
        $candidato = $this->controle_candidato->exigir();

        if ($this->input->method() !== 'get') show_404();

        $caminho = $this->caminho_arquivo($candidato['curriculo_arquivo']);

        if (!$caminho || !is_readable($caminho)) show_404();

        $this->load->helper('download');
        $nome = preg_replace('/[\x00-\x1F\x7F"\\\\]/', '',
            basename($candidato['curriculo_nome_original']));
        header('X-Content-Type-Options: nosniff');
        force_download($nome ?: 'curriculo', file_get_contents($caminho));
    }

    private function caminho_arquivo($nome)
    {
        if (!is_string($nome) || $nome === '' || basename($nome) !== $nome) {
            return FALSE;
        }

        $caminho = FCPATH . 'uploads/candidatos/' . $nome;
        return is_file($caminho) ? $caminho : FALSE;
    }

    private function validar_dados_edicao($post, $candidato, &$erros)
    {
        $nome = is_string($post['nome_completo'] ?? NULL) ? trim($post['nome_completo']) : '';
        $email = is_string($post['email'] ?? NULL) ? strtolower(trim($post['email'])) : '';
        $telefone = is_string($post['telefone'] ?? NULL) ? trim($post['telefone']) : '';

        if ($nome === '' || $this->tamanho_texto($nome) > 150) {
            $erros[] = 'Informe um nome completo de até 150 caracteres.';
        }

        if ($email === '' || $this->tamanho_texto($email) > 150 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $this->candidato_model->email_em_uso($email, $candidato['codigo'])) {
            $erros[] = 'Informe um e-mail válido que ainda não esteja cadastrado.';
        }

        if (!preg_match('/^[0-9+().\s-]{8,30}$/', $telefone)) {
            $erros[] = 'Informe um Telefone/WhatsApp válido.';
        }

        return ['nome_completo' => $nome, 'email' => $email, 'telefone' => $telefone];
    }

    private function valores_salvos($formulario_codigo)
    {
        $valores = [];
        $linhas = $this->candidato_resposta_model
            ->listar_por_candidato_formulario($formulario_codigo);

        foreach ($linhas as $linha) {
            $grupo = (string) $linha['grupo_codigo'];
            $indice = (int) $linha['indice'];

            if (!isset($valores[$grupo][$indice])) $valores[$grupo][$indice] = [];
            if ($linha['campo_chave'] === NULL) continue;

            if ($linha['valor_json'] !== NULL) {
                $valor = json_decode($linha['valor_json'], TRUE);
                $valor = is_array($valor) ? $valor : [];
            } elseif ($linha['valor_booleano'] !== NULL) {
                $valor = $linha['valor_booleano'] ? 'sim' : 'nao';
            } else {
                $valor = $linha['valor_texto'] ?? $linha['valor_numero'] ?? $linha['valor_data'];
            }

            $valores[$grupo][$indice][$linha['campo_chave']] = $valor;
        }

        return $valores;
    }

    private function salvar_edicao($candidato, $formulario, $dados, $respostas, $arquivos, $publicacao)
    {
        if (!$this->db->trans_begin()) return FALSE;

        // Bloqueia edições simultâneas do mesmo perfil até o fim da transação.
        $bloqueado = $this->db->query(
            'SELECT codigo FROM candidatos WHERE codigo = ? FOR UPDATE',
            [(int) $candidato['codigo']]
        )->row_array();
        $atual = $this->candidato_formulario_model->buscar_por_candidato($candidato['codigo']);

        if (!$bloqueado || !$atual || (int) $atual['codigo'] !== (int) $formulario['codigo']) {
            $this->db->trans_rollback();
            return FALSE;
        }

        if (isset($arquivos['foto_perfil'])) {
            $dados['foto_arquivo'] = $arquivos['foto_perfil']['nome_armazenado'];
        }

        if (isset($arquivos['curriculo'])) {
            $dados['curriculo_arquivo'] = $arquivos['curriculo']['nome_armazenado'];
            $dados['curriculo_nome_original'] = $arquivos['curriculo']['nome_original'];
            $dados['curriculo_mime'] = $arquivos['curriculo']['mime'];
            $dados['curriculo_tamanho'] = $arquivos['curriculo']['tamanho'];
        }

        $salvo = $this->candidato_model->atualizar($candidato['codigo'], $dados);
        $revisao = $salvo ? $this->candidato_formulario_model->cadastrar([
            'candidato_codigo' => $candidato['codigo'],
            'formulario_publicacao_codigo' => $publicacao['codigo']
        ]) : FALSE;

        if ($revisao) {
            foreach ($respostas as $grupo_codigo => $ocorrencias) {
                foreach ($ocorrencias as $indice => $campos) {
                    $grupo = $this->candidato_grupo_model->cadastrar([
                        'candidato_formulario_codigo' => $revisao,
                        'grupo_codigo' => $grupo_codigo,
                        'indice' => $indice
                    ]);

                    if (!$grupo) { $salvo = FALSE; break 2; }

                    foreach ($campos as $resposta) {
                        if (!$this->candidato_resposta_model->salvar(
                            $grupo, $resposta['campo_chave'], $resposta['valor']
                        )) { $salvo = FALSE; break 3; }
                    }
                }
            }
        }

        if (!$salvo || !$revisao || !$this->candidato_formulario_model->concluir($revisao) ||
            $this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $antigos = [];

        if (isset($arquivos['foto_perfil'])) {
            $antigos[] = ['nome_armazenado' => $candidato['foto_arquivo']];
        }

        if (isset($arquivos['curriculo'])) {
            $antigos[] = ['nome_armazenado' => $candidato['curriculo_arquivo']];
        }

        $this->remover_arquivos($antigos);
        return TRUE;
    }

    private function novo_token($finalidade)
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set_userdata('candidato_token_' . $finalidade, $token);
        return $token;
    }

    private function validar_token($post, $finalidade)
    {
        $esperado = $this->session->userdata('candidato_token_' . $finalidade);
        $recebido = is_array($post) && is_string($post['token'] ?? NULL)
            ? $post['token'] : '';
        $this->session->unset_userdata('candidato_token_' . $finalidade);

        return is_string($esperado) && $esperado !== '' &&
            $recebido !== '' && hash_equals($esperado, $recebido);
    }

    private function enviar_recuperacao($destinatario, $token)
    {
        $remetente = getenv('VITAE_EMAIL_REMETENTE');

        if (!$remetente || !filter_var($remetente, FILTER_VALIDATE_EMAIL) ||
            !is_file(APPPATH . 'config/email.php')) {
            log_message('error', 'Configure email.php e VITAE_EMAIL_REMETENTE para a recuperação.');
            return;
        }

        $this->load->library('email');
        $link = base_url('candidato/redefinir/' . $token);
        $this->email->from($remetente, 'Vitae RH');
        $this->email->to($destinatario);
        $this->email->subject('Redefinição de senha - Vitae RH');
        $this->email->message(
            "Recebemos um pedido de redefinição de senha.\n\n" .
            "Acesse o link em até 1 hora: " . $link . "\n\n" .
            "Se não foi você, ignore esta mensagem."
        );

        if (!$this->email->send()) {
            log_message('error', 'Falha ao enviar recuperação de senha do candidato.');
        }
    }
}
