<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recuperacao_usuario extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('usuario_model');
        $this->load->model('usuario_recuperacao_model');
        $this->load->library('correio');
    }

    public function index()
    {
        $this->recuperar();
    }

    public function recuperar()
    {
        $erro = '';
        $sucesso = FALSE;
        if ($this->input->method() === 'post') {
            if (!$this->token_valido()) {
                $erro = 'Sessão expirada. Tente novamente.';
            } else {
                $email = $this->input->post('email');
                $usuario = is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL)
                    ? $this->db->where('email', trim($email))->where('ativo', 1)
                        ->where('exclusao IS NULL', NULL, FALSE)->get('usuarios')->row_array() : NULL;
                if ($usuario && $this->usuario_model->buscar_para_sessao($usuario['codigo'])) {
                    $token = bin2hex(random_bytes(32));
                    if ($this->usuario_recuperacao_model->solicitar($usuario['codigo'], hash('sha256', $token))) {
                        $this->correio->enviar($usuario['email'], 'Redefinição de senha administrativa - Vitae RH',
                            'Acesse em até 1 hora: ' . base_url('recuperacao_usuario/redefinir/' . $token));
                    }
                }
                $sucesso = TRUE;
            }
        } elseif ($this->input->method() !== 'get') show_404();
        $this->tela(['pagina' => 'recuperacao', 'erro' => $erro, 'sucesso' => $sucesso]);
    }

    public function redefinir($token = '')
    {
        $this->output->set_header('Referrer-Policy: no-referrer');
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) show_404();
        $hash = hash('sha256', $token);
        $reg = $this->usuario_recuperacao_model->buscar_valido($hash);
        if (!$reg || !$this->usuario_model->buscar_para_sessao($reg['usuario_codigo'])) {
            show_error('Link expirado ou inválido. Solicite outro link.', 410);
        }
        $erro = '';
        if ($this->input->method() === 'post') {
            $senha = $this->input->post('senha');
            if (!$this->token_valido()) {
                $erro = 'Sessão expirada. Tente novamente.';
            } elseif (!is_string($senha) || strlen($senha) < 8 || strlen($senha) > 72 ||
                $senha !== $this->input->post('confirmar_senha')) {
                $erro = 'Informe e confirme uma senha entre 8 e 72 caracteres.';
            } else {
                $this->db->trans_begin();
                $ok = $this->usuario_recuperacao_model->consumir($hash, $reg['usuario_codigo'])
                    && $this->usuario_model->atualizar($reg['usuario_codigo'], ['senha' => password_hash($senha, PASSWORD_DEFAULT)])
                    && $this->usuario_recuperacao_model->invalidar_outros($reg['usuario_codigo']);
                if ($ok && $this->db->trans_status() !== FALSE) {
                    $this->db->trans_commit();
                    $this->controle_acesso->destruir();
                    redirect('autenticacao/login', 'location', 303);
                    return;
                }
                $this->db->trans_rollback();
                $erro = 'Não foi possível redefinir a senha.';
            }
        } elseif ($this->input->method() !== 'get') show_404();
        $this->tela(['pagina' => 'redefinicao', 'erro' => $erro, 'link' => $token]);
    }

    private function token_valido()
    {
        $esperado = $this->session->userdata('usuario_recuperacao_csrf');
        $recebido = $this->input->post('token');
        $this->session->unset_userdata('usuario_recuperacao_csrf');
        return is_string($esperado) && is_string($recebido) && hash_equals($esperado, $recebido);
    }

    private function tela($dados)
    {
        $dados['token'] = bin2hex(random_bytes(32));
        $dados['rota_acesso'] = 'recuperacao_usuario';
        $this->session->set_userdata('usuario_recuperacao_csrf', $dados['token']);
        $this->load->view('candidato/acesso', $dados);
    }
}
