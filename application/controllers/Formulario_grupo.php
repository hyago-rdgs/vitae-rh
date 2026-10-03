<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_grupo extends CI_Controller
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

        $secao = $this->buscar_secao(
            $this->input->post('secao_codigo')
        );

        $resultado = $this->validar(
            $this->input->post(),
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

        $resultado['dados']['secao_codigo'] = $secao['codigo'];

        $this->db->trans_begin();

        $codigo = $this->formulario_grupo_model->cadastrar(
            $resultado['dados']
        );

        $grupo = $codigo
            ? $this->formulario_grupo_model->buscar_por_codigo(
                $codigo
            )
            : FALSE;

        $auditoria_salva = $grupo
            ? $this->auditoria->registrar(
                'formularios',
                'GRUPO_CADASTRADO',
                'formulario_grupos',
                $codigo,
                NULL,
                $grupo
            )
            : FALSE;

        if (
            !$codigo ||
            !$grupo ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível cadastrar o grupo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Grupo cadastrado com sucesso.',
            ['codigo' => $codigo],
            201
        );
    }

    public function atualizar($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $grupo = $this->buscar_grupo($codigo);

        $resultado = $this->validar(
            $this->input->post(),
            $grupo['secao_codigo'],
            $grupo['codigo']
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

        $atualizado = $this->formulario_grupo_model->atualizar(
            $grupo['codigo'],
            $resultado['dados']
        );

        $grupo_atualizado = $atualizado
            ? $this->formulario_grupo_model->buscar_por_codigo(
                $grupo['codigo']
            )
            : FALSE;

        $auditoria_salva = $grupo_atualizado
            ? $this->auditoria->registrar(
                'formularios',
                'GRUPO_ATUALIZADO',
                'formulario_grupos',
                $grupo['codigo'],
                $grupo,
                $grupo_atualizado
            )
            : FALSE;

        if (
            !$atualizado ||
            !$grupo_atualizado ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível atualizar o grupo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Grupo atualizado com sucesso.',
            ['codigo' => $grupo['codigo']]
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

        $grupo = $this->buscar_grupo($codigo);
        $grupo_adjacente = $this->formulario_grupo_model
            ->buscar_adjacente($grupo, $direcao);

        if (!$grupo_adjacente) {
            resposta_json(
                TRUE,
                'O grupo já está no limite da ordenação.',
                ['codigo' => $grupo['codigo']]
            );
        }

        $this->db->trans_begin();

        $ordem_original = $grupo['ordem'];

        $grupo_atualizado = $this->formulario_grupo_model
            ->atualizar_ordem(
                $grupo['codigo'],
                $grupo_adjacente['ordem']
            );

        $adjacente_atualizado = $grupo_atualizado
            ? $this->formulario_grupo_model->atualizar_ordem(
                $grupo_adjacente['codigo'],
                $ordem_original
            )
            : FALSE;

        $grupo_novo = (
            $grupo_atualizado &&
            $adjacente_atualizado
        )
            ? $this->formulario_grupo_model->buscar_por_codigo(
                $grupo['codigo']
            )
            : FALSE;

        $grupo_adjacente_novo = $grupo_novo
            ? $this->formulario_grupo_model->buscar_por_codigo(
                $grupo_adjacente['codigo']
            )
            : FALSE;

        $dados_anteriores = (
            $grupo_novo &&
            $grupo_adjacente_novo
        )
            ? [
                'grupo' => $grupo,
                'grupo_adjacente' => $grupo_adjacente
            ]
            : FALSE;

        $dados_novos = (
            $grupo_novo &&
            $grupo_adjacente_novo
        )
            ? [
                'grupo' => $grupo_novo,
                'grupo_adjacente' => $grupo_adjacente_novo
            ]
            : FALSE;

        $auditoria_salva = $dados_anteriores && $dados_novos
            ? $this->auditoria->registrar(
                'formularios',
                'GRUPO_REORDENADO',
                'formulario_grupos',
                $grupo['codigo'],
                $dados_anteriores,
                $dados_novos
            )
            : FALSE;

        if (
            !$grupo_atualizado ||
            !$adjacente_atualizado ||
            !$grupo_adjacente_novo ||
            !$dados_novos ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível reordenar o grupo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Grupo reordenado com sucesso.',
            ['codigo' => $grupo['codigo']]
        );
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $grupo = $this->buscar_grupo($codigo);

        if ($this->formulario_grupo_model->possui_campos(
            $grupo['codigo']
        )) {
            resposta_json(
                FALSE,
                'Não é possível excluir um grupo que possui campos.',
                [
                    'erros' => [
                        'Exclua os campos vinculados antes de continuar.'
                    ]
                ],
                422
            );
        }

        $this->db->trans_begin();

        $excluido = $this->formulario_grupo_model->excluir(
            $grupo['codigo']
        );

        $auditoria_salva = $excluido
            ? $this->auditoria->registrar(
                'formularios',
                'GRUPO_EXCLUIDO',
                'formulario_grupos',
                $grupo['codigo'],
                $grupo,
                NULL
            )
            : FALSE;

        if (
            !$excluido ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o grupo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Grupo excluído com sucesso.',
            ['codigo' => $grupo['codigo']]
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
        $formulario = $this->formulario_model->buscar($secao['formulario_codigo'] ?? 0);

        if (
            !$secao ||
            !$formulario ||
            (int) $secao['formulario_codigo'] !==
                (int) $formulario['codigo']
        ) {
            resposta_json(
                FALSE,
                'Seção não encontrada ou já excluída.',
                [],
                404
            );
        }

        return $secao;
    }

    private function buscar_grupo($codigo)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o grupo.',
                ['erros' => ['O código do grupo é inválido.']],
                422
            );
        }

        $grupo = $this->formulario_grupo_model->buscar_por_codigo(
            (int) $codigo
        );
        $formulario = $this->formulario_model->buscar($grupo['formulario_codigo'] ?? 0);

        if (
            !$grupo ||
            !$formulario ||
            (int) $grupo['formulario_codigo'] !==
                (int) $formulario['codigo']
        ) {
            resposta_json(
                FALSE,
                'Grupo não encontrado ou já excluído.',
                [],
                404
            );
        }

        return $grupo;
    }

    private function validar(
        $reg,
        $secao_codigo,
        $codigo = NULL
    ) {
        $repetivel = isset($reg['repetivel']) &&
            (string) $reg['repetivel'] === '1';

        $dados = [
            'nome' => trim($reg['nome'] ?? ''),
            'descricao' => trim($reg['descricao'] ?? ''),
            'repetivel' => $repetivel ? 1 : 0,
            'quantidade_minima' => $repetivel
                ? trim($reg['quantidade_minima'] ?? '')
                : 1,
            'quantidade_maxima' => $repetivel
                ? trim($reg['quantidade_maxima'] ?? '')
                : 1,
            'ativo' => isset($reg['ativo']) &&
                (string) $reg['ativo'] === '1'
                    ? 1
                    : 0
        ];

        $erros = [];

        if ($dados['nome'] === '') {
            $erros[] = 'O campo Nome é obrigatório.';
        } elseif (strlen($dados['nome']) > 100) {
            $erros[] = 'O campo Nome deve possuir no máximo 100 caracteres.';
        } elseif ($this->formulario_grupo_model->nome_em_uso(
            $secao_codigo,
            $dados['nome'],
            $codigo
        )) {
            $erros[] = 'Já existe um grupo com o nome informado nesta seção.';
        }

        if (strlen($dados['descricao']) > 2000) {
            $erros[] = 'O campo Descrição deve possuir no máximo 2000 caracteres.';
        }

        if ($repetivel) {
            if (
                $dados['quantidade_minima'] === '' ||
                !ctype_digit((string) $dados['quantidade_minima'])
            ) {
                $erros[] = 'A quantidade mínima deve ser um número inteiro maior ou igual a zero.';
            }

            if (
                $dados['quantidade_maxima'] === '' ||
                !ctype_digit((string) $dados['quantidade_maxima']) ||
                (int) $dados['quantidade_maxima'] < 1
            ) {
                $erros[] = 'A quantidade máxima deve ser um número inteiro maior que zero.';
            }

            if (
                ctype_digit((string) $dados['quantidade_minima']) &&
                ctype_digit((string) $dados['quantidade_maxima']) &&
                (int) $dados['quantidade_minima'] >
                    (int) $dados['quantidade_maxima']
            ) {
                $erros[] = 'A quantidade mínima não pode ser maior que a máxima.';
            }

            if (
                (
                    ctype_digit((string) $dados['quantidade_minima']) &&
                    (int) $dados['quantidade_minima'] > 65535
                ) ||
                (
                    ctype_digit((string) $dados['quantidade_maxima']) &&
                    (int) $dados['quantidade_maxima'] > 65535
                )
            ) {
                $erros[] = 'As quantidades devem ser menores ou iguais a 65535.';
            }
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
        $dados['quantidade_minima'] =
            (int) $dados['quantidade_minima'];
        $dados['quantidade_maxima'] =
            (int) $dados['quantidade_maxima'];

        return [
            'sucesso' => TRUE,
            'dados' => $dados
        ];
    }
}
