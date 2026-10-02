<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_mensagens extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->controle_acesso->valida_permissao('candidatos.comunicar');
        $this->load->database();
        $this->load->model('candidato_comunicacao_model');
    }

    public function index()
    {
        if ($this->input->method() !== 'get') {
            show_404();
        }

        $this->load->view('candidatos/mensagens_lista', [
            'modelos' => $this->candidato_comunicacao_model->listar_modelos()
        ]);
    }

    public function cadastrar($codigo = NULL)
    {
        if ($this->input->method() !== 'get' ||
            ($codigo !== NULL && (!ctype_digit((string) $codigo) || (int) $codigo < 1))) {
            show_404();
        }

        $modelo = $codigo !== NULL
            ? $this->candidato_comunicacao_model->buscar_modelo((int) $codigo)
            : NULL;

        if ($codigo !== NULL && !$modelo) {
            show_404();
        }

        $this->load->view('candidatos/mensagem_form', [
            'modelo' => $modelo ?: [
                'codigo' => NULL, 'nome' => '', 'assunto' => '',
                'conteudo' => '', 'ativo' => 1
            ],
            'erro' => $this->session->flashdata('mensagem_modelo_erro')
        ]);
    }

    public function salvar($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $post = $this->input->post(NULL, TRUE);
        if ($codigo !== NULL && (!ctype_digit((string) $codigo) || (int) $codigo < 1 ||
            !$this->candidato_comunicacao_model->buscar_modelo((int) $codigo))) {
            show_404();
        }

        $nome = is_string($post['nome'] ?? NULL) ? trim($post['nome']) : '';
        $assunto = is_string($post['assunto'] ?? NULL) ? trim($post['assunto']) : '';
        $conteudo = is_string($post['conteudo'] ?? NULL) ? trim($post['conteudo']) : '';
        $ativo = isset($post['ativo']) ? 1 : 0;
        $erros = [];

        if ($nome === '' || strlen($nome) > 100) {
            $erros[] = 'Informe um nome com até 100 caracteres.';
        }
        if ($assunto === '' || strlen($assunto) > 255) {
            $erros[] = 'Informe um assunto com até 255 caracteres.';
        }
        if ($conteudo === '' || strlen($conteudo) > 10000) {
            $erros[] = 'Informe o conteúdo com até 10.000 caracteres.';
        }

        if ($erros) {
            $this->session->set_flashdata('mensagem_modelo_erro', implode(' ', $erros));
            redirect('candidato_mensagens/cadastrar' . ($codigo ? '/' . (int) $codigo : ''));
            return;
        }

        $salvo = $this->candidato_comunicacao_model->salvar_modelo([
            'nome' => $nome,
            'assunto' => $assunto,
            'conteudo' => $conteudo,
            'ativo' => $ativo
        ], $codigo !== NULL ? (int) $codigo : NULL);

        if (!$salvo) {
            $this->session->set_flashdata('mensagem_modelo_erro', 'Não foi possível salvar o modelo.');
            redirect('candidato_mensagens/cadastrar' . ($codigo ? '/' . (int) $codigo : ''));
            return;
        }

        redirect('candidato_mensagens');
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post' || !ctype_digit((string) $codigo) ||
            (int) $codigo < 1 ||
            !$this->candidato_comunicacao_model->buscar_modelo((int) $codigo)) {
            show_404();
        }

        $this->candidato_comunicacao_model->excluir_modelo((int) $codigo);
        redirect('candidato_mensagens');
    }
}
