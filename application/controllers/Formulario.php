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
        $this->load->library('token_admin');
        $this->load->database();
        $this->load->model('formulario_model');
        $this->load->model('formulario_secao_model');
        $this->load->model('formulario_grupo_model');
        $this->load->model('formulario_campo_model');
        $this->load->model('campo_opcao_model');
        $this->load->model('formulario_publicacao_model');
    }

    public function index()
    {
        if ($this->input->method() !== 'get') show_404();
        $this->load->view('formulario/lista', [
            'formularios' => $this->formulario_model->listar(),
            'vigente' => $this->formulario_publicacao_model->buscar_vigente()
        ]);
    }

    public function configurar($codigo = NULL)
    {
        $formulario = $this->formulario_model->buscar($codigo);

        if (!$formulario) {
            show_error(
                'A estrutura do formulário não foi encontrada.',
                500,
                'Configuração indisponível'
            );
        }

        if ($this->input->method() === 'post') {
            $this->token_admin->exigir();
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
                ? $this->formulario_model->buscar($formulario['codigo'])
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

        $dados = $this->preparar_dados_estrutura($formulario);

        $this->load->view(
            'formulario/formulario_configuracao',
            $dados
        );
    }

    public function previsualizar($codigo = NULL)
    {
        if ($this->input->method() !== 'get') {
            show_404();
        }

        $formulario = $this->formulario_model->buscar($codigo);

        if (!$formulario) {
            show_error(
                'A estrutura do formulário não foi encontrada.',
                500,
                'Pré-visualização indisponível'
            );
        }

        $dados = $this->preparar_dados_estrutura($formulario);

        $this->load->view(
            'formulario/formulario_previsualizacao',
            $dados
        );
    }

    public function versao($codigo = NULL)
    {
        if ($this->input->method() !== 'get' || !ctype_digit((string) $codigo)) show_404();
        $publicacao = $this->formulario_publicacao_model->buscar_por_codigo((int) $codigo);
        if (!$publicacao || !empty($publicacao['exclusao'])) show_404();
        $estrutura = json_decode($publicacao['estrutura'], TRUE);
        if (!is_array($estrutura) || empty($estrutura['secoes'])) show_error('Estrutura indisponível.', 422);
        $dados = ['formulario' => $estrutura['formulario'], 'versao' => $publicacao['versao'],
            'secoes' => [], 'grupos_por_secao' => [], 'campos_por_grupo' => [], 'opcoes_por_campo' => []];
        foreach ($estrutura['secoes'] as $secao) {
            $dados['secoes'][] = $secao + ['ativo' => 1];
            foreach ($secao['grupos'] as $grupo) {
                $dados['grupos_por_secao'][$secao['codigo']][] = $grupo + ['ativo' => 1];
                foreach ($grupo['campos'] as $campo) {
                    $dados['campos_por_grupo'][$grupo['codigo']][] = $campo + ['ativo' => 1];
                    foreach ($campo['opcoes'] as $opcao) {
                        $dados['opcoes_por_campo'][$campo['codigo']][] = $opcao + ['ativo' => 1];
                    }
                }
            }
        }
        $this->load->view('formulario/formulario_previsualizacao', $dados);
    }

    public function cadastrar()
    {
        if ($this->input->method() !== 'post') show_404();
        $this->token_admin->exigir();
        $nome = $this->input->post('nome', TRUE);
        if (!is_string($nome) || trim($nome) === '' || strlen(trim($nome)) > 100) {
            show_error('Informe um nome com até 100 caracteres.', 422);
        }
        $codigo = $this->formulario_model->cadastrar(['nome' => trim($nome)]);
        if (!$codigo) show_error('Não foi possível criar o formulário.', 500);
        redirect('formulario/configurar/' . $codigo);
    }

    private function preparar_dados_estrutura($formulario)
    {
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

        return [
            'formulario' => $formulario,
            'proxima_versao' => $this->formulario_publicacao_model->proxima_versao($formulario['codigo']),
            'secoes' => $this->formulario_secao_model
                ->listar_por_formulario($formulario['codigo']),
            'grupos_por_secao' => $grupos_por_secao,
            'campos_por_grupo' => $campos_por_grupo,
            'opcoes_por_campo' => $opcoes_por_campo,
            'tipos_campos' => $this->formulario_campo_model
                ->listar_tipos(),
            'publicacao_atual' => $this->formulario_publicacao_model
                ->buscar_atual($formulario['codigo']),
            'publicacoes' => $this->formulario_publicacao_model
                ->listar_por_formulario($formulario['codigo']),
            'pode_publicar' => $this->controle_acesso
                ->tem_permissao('formularios.publicar')
        ];
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
