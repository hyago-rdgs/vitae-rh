<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_secao extends CI_Controller
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
    }

    public function cadastrar()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $formulario = $this->formulario_model->buscar();

        if (!$formulario) {
            resposta_json(
                FALSE,
                'O formulário não foi encontrado.',
                [],
                404
            );
        }

        $resultado = $this->validar(
            $this->input->post(),
            $formulario['codigo']
        );

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $resultado['dados']['formulario_codigo'] =
            $formulario['codigo'];

        $this->db->trans_begin();

        $codigo = $this->formulario_secao_model->cadastrar(
            $resultado['dados']
        );

        $secao = $codigo
            ? $this->formulario_secao_model->buscar_por_codigo(
                $codigo
            )
            : FALSE;

        $grupo_codigo = $secao
            ? $this->formulario_grupo_model->cadastrar([
                'secao_codigo' => $secao['codigo'],
                'nome' => $secao['titulo'],
                'descricao' => NULL,
                'repetivel' => 0,
                'quantidade_minima' => 1,
                'quantidade_maxima' => 1,
                'ativo' => $secao['ativo']
            ])
            : FALSE;

        $grupo = $grupo_codigo
            ? $this->formulario_grupo_model->buscar_por_codigo(
                $grupo_codigo
            )
            : FALSE;

        $dados_auditoria = (
            $secao &&
            $grupo
        )
            ? [
                'secao' => $secao,
                'grupo_principal' => $grupo
            ]
            : FALSE;

        $auditoria_salva = $dados_auditoria
            ? $this->auditoria->registrar(
                'formularios',
                'SECAO_CADASTRADA',
                'formulario_secoes',
                $codigo,
                NULL,
                $dados_auditoria
            )
            : FALSE;

        if (
            !$codigo ||
            !$secao ||
            !$grupo_codigo ||
            !$grupo ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível cadastrar a seção.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Seção cadastrada com sucesso.',
            ['codigo' => $codigo],
            201
        );
    }

    public function atualizar($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $secao = $this->buscar_secao($codigo);

        $resultado = $this->validar(
            $this->input->post(),
            $secao['formulario_codigo'],
            $secao['codigo']
        );

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $this->db->trans_begin();

        $atualizado = $this->formulario_secao_model->atualizar(
            $secao['codigo'],
            $resultado['dados']
        );

        $secao_atualizada = $atualizado
            ? $this->formulario_secao_model->buscar_por_codigo(
                $secao['codigo']
            )
            : FALSE;

        $auditoria_salva = $secao_atualizada
            ? $this->auditoria->registrar(
                'formularios',
                'SECAO_ATUALIZADA',
                'formulario_secoes',
                $secao['codigo'],
                $secao,
                $secao_atualizada
            )
            : FALSE;

        if (
            !$atualizado ||
            !$secao_atualizada ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível atualizar a seção.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Seção atualizada com sucesso.',
            ['codigo' => $secao['codigo']]
        );
    }

    public function mover($codigo = NULL, $direcao = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (!in_array($direcao, ['subir', 'descer'], TRUE)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar a direção.',
                [],
                422
            );
        }

        $secao = $this->buscar_secao($codigo);
        $secao_adjacente = $this->formulario_secao_model
            ->buscar_adjacente($secao, $direcao);

        if (!$secao_adjacente) {
            resposta_json(
                TRUE,
                'A seção já está no limite da ordenação.',
                ['codigo' => $secao['codigo']]
            );
        }

        $this->db->trans_begin();

        $ordem_original = $secao['ordem'];

        $secao_atualizada = $this->formulario_secao_model
            ->atualizar_ordem(
                $secao['codigo'],
                $secao_adjacente['ordem']
            );

        $adjacente_atualizada = $secao_atualizada
            ? $this->formulario_secao_model->atualizar_ordem(
                $secao_adjacente['codigo'],
                $ordem_original
            )
            : FALSE;

        $dados_novos = (
            $secao_atualizada &&
            $adjacente_atualizada
        )
            ? $this->formulario_secao_model->buscar_por_codigo(
                $secao['codigo']
            )
            : FALSE;

        $auditoria_salva = $dados_novos
            ? $this->auditoria->registrar(
                'formularios',
                'SECAO_REORDENADA',
                'formulario_secoes',
                $secao['codigo'],
                $secao,
                $dados_novos
            )
            : FALSE;

        if (
            !$secao_atualizada ||
            !$adjacente_atualizada ||
            !$dados_novos ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível reordenar a seção.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Seção reordenada com sucesso.',
            ['codigo' => $secao['codigo']]
        );
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $secao = $this->buscar_secao($codigo);

        if ($this->formulario_grupo_model->secao_possui_campos(
            $secao['codigo']
        )) {
            resposta_json(
                FALSE,
                'Não é possível excluir uma seção que possui campos.',
                [
                    'erros' => [
                        'Exclua os campos vinculados antes de continuar.'
                    ]
                ],
                422
            );
        }

        $this->db->trans_begin();

        $grupos_excluidos = $this->formulario_grupo_model
            ->excluir_por_secao($secao['codigo']);

        $excluido = $grupos_excluidos
            ? $this->formulario_secao_model->excluir(
                $secao['codigo']
            )
            : FALSE;

        $auditoria_salva = $excluido
            ? $this->auditoria->registrar(
                'formularios',
                'SECAO_EXCLUIDA',
                'formulario_secoes',
                $secao['codigo'],
                $secao,
                NULL
            )
            : FALSE;

        if (
            !$grupos_excluidos ||
            !$excluido ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir a seção.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Seção excluída com sucesso.',
            ['codigo' => $secao['codigo']]
        );
    }

    private function buscar_secao($codigo)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar a seção.',
                ['erros' => ['O código da seção é inválido.']],
                422
            );
        }

        $secao = $this->formulario_secao_model->buscar_por_codigo(
            (int) $codigo
        );

        if (!$secao) {
            resposta_json(
                FALSE,
                'Seção não encontrada ou já excluída.',
                [],
                404
            );
        }

        return $secao;
    }

    private function validar(
        $reg,
        $formulario_codigo,
        $codigo = NULL
    ) {
        $dados = [
            'titulo' => trim($reg['titulo'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? ''),
            'ativo' => isset($reg['ativo']) &&
                (string) $reg['ativo'] === '1'
                    ? 1
                    : 0
        ];

        $erros = [];

        if ($dados['titulo'] === '') {
            $erros[] = 'O campo Título é obrigatório.';
        } elseif (strlen($dados['titulo']) > 100) {
            $erros[] = 'O campo Título deve possuir no máximo 100 caracteres.';
        } elseif ($this->formulario_secao_model->titulo_em_uso(
            $formulario_codigo,
            $dados['titulo'],
            $codigo
        )) {
            $erros[] = 'Já existe uma seção com o título informado.';
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
