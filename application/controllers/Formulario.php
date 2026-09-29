<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_permissao(
            'formularios.gerenciar'
        );

        $this->load->library('auditoria');
        $this->load->database();
        $this->load->model('formulario_model');
        $this->load->model('formulario_secao_model');
        $this->load->model('formulario_grupo_model');
        $this->load->model('formulario_campo_model');
        $this->load->model('campo_opcao_model');
    }

    public function index()
    {
        $this->configurar();
        return;
    }

    public function configurar()
    {
        $formulario = $this->formulario_model->buscar();

        if (!$formulario) {
            show_error(
                'A estrutura do formulário não foi encontrada.',
                500,
                'Configuração indisponível'
            );
        }

        if ($this->input->method() === 'post') {
            $resultado = $this->validar($this->input->post());

            if (!$resultado['sucesso']) {
                resposta_json(
                    FALSE,
                    'Verifique os campos informados.',
                    ['erros' => $resultado['erros']],
                    422
                );
            }

            $this->db->trans_begin();

            $atualizado = $this->formulario_model->atualizar(
                $formulario['codigo'],
                $resultado['dados']
            );

            $formulario_atualizado = $atualizado
                ? $this->formulario_model->buscar()
                : FALSE;

            $auditoria_salva = $formulario_atualizado
                ? $this->auditoria->registrar(
                    'formularios',
                    'FORMULARIO_ATUALIZADO',
                    'formularios',
                    $formulario['codigo'],
                    $formulario,
                    $formulario_atualizado
                )
                : FALSE;

            if (
                !$atualizado ||
                !$formulario_atualizado ||
                !$auditoria_salva ||
                $this->db->trans_status() === FALSE
            ) {
                $this->db->trans_rollback();

                resposta_json(
                    FALSE,
                    'Não foi possível atualizar o formulário.',
                    [],
                    500
                );
            }

            $this->db->trans_commit();

            resposta_json(
                TRUE,
                'Configurações atualizadas com sucesso.',
                ['codigo' => $formulario['codigo']]
            );
        }

        $grupos = $this->formulario_grupo_model
            ->listar_por_formulario($formulario['codigo']);
        $grupos_por_secao = [];

        foreach ($grupos as $grupo) {
            $grupos_por_secao[$grupo['secao_codigo']][] = $grupo;
        }

        $campos = $this->formulario_campo_model
            ->listar_por_formulario($formulario['codigo']);
        $campos_por_grupo = [];

        foreach ($campos as $campo) {
            $campos_por_grupo[$campo['grupo_codigo']][] = $campo;
        }

        $opcoes = $this->campo_opcao_model
            ->listar_por_formulario($formulario['codigo']);
        $opcoes_por_campo = [];

        foreach ($opcoes as $opcao) {
            $opcoes_por_campo[$opcao['campo_codigo']][] = $opcao;
        }

        $dados = [
            'formulario' => $formulario,
            'secoes' => $this->formulario_secao_model
                ->listar_por_formulario($formulario['codigo']),
            'grupos_por_secao' => $grupos_por_secao,
            'campos_por_grupo' => $campos_por_grupo,
            'opcoes_por_campo' => $opcoes_por_campo,
            'tipos_campos' => $this->formulario_campo_model
                ->listar_tipos()
        ];

        $this->load->view(
            'formulario/formulario_configuracao',
            $dados
        );
    }

    private function validar($reg)
    {
        $dados = [
            'nome' => trim($reg['nome'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? '')
        ];

        $erros = [];

        if ($dados['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        } elseif (strlen($dados['nome']) > 100) {
            $erros[] = 'O campo Nome deve possuir no máximo 100 caracteres.';
        }

        if (strlen($dados['descricao']) > 2000) {
            $erros[] = 'O campo Descrição deve possuir no máximo 2000 caracteres.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $dados['descricao'] = $dados['descricao'] !== ''
            ? $dados['descricao']
            : NULL;

        return [
            'sucesso' => TRUE,
            'dados' => $dados
        ];
    }
}
