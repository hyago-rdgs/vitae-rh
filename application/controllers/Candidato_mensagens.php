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
        $this->load->view('candidatos/mensagens_lista', [
            'modelos' => $this->candidato_comunicacao_model->listar_modelos()
        ]);
    }

    public function cadastrar($codigo = NULL)
    {
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
        $nome = trim((string) ($post['nome'] ?? ''));
        $assunto = trim((string) ($post['assunto'] ?? ''));
        $conteudo = trim((string) ($post['conteudo'] ?? ''));
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
        if ($this->input->method() !== 'post' || !$codigo) {
            show_404();
        }

        $this->candidato_comunicacao_model->excluir_modelo((int) $codigo);
        redirect('candidato_mensagens');
    }
}
