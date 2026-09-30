<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_campo extends CI_Controller
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
        $this->load->model('formulario_grupo_model');
        $this->load->model('formulario_campo_model');
        $this->load->model('campo_opcao_model');
    }

    public function cadastrar()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $grupo = $this->buscar_grupo(
            $this->input->post('grupo_codigo')
        );
        $resultado = $this->validar(
            $this->input->post(),
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

        $resultado['dados']['grupo_codigo'] = $grupo['codigo'];
        $resultado['dados']['chave'] = $this->gerar_chave_unica(
            $resultado['dados']['nome']
        );

        $this->db->trans_begin();

        $codigo = $this->formulario_campo_model->cadastrar(
            $resultado['dados']
        );

        $opcoes_sincronizadas = $codigo
            ? $this->campo_opcao_model->sincronizar(
                $codigo,
                $resultado['opcoes']
            )
            : FALSE;

        $dados_auditoria = (
            $codigo &&
            $opcoes_sincronizadas
        )
            ? $this->preparar_dados_auditoria($codigo)
            : FALSE;

        $auditoria_salva = $dados_auditoria
            ? $this->auditoria->registrar(
                'formularios',
                'CAMPO_CADASTRADO',
                'formulario_campos',
                $codigo,
                NULL,
                $dados_auditoria
            )
            : FALSE;

        if (
            !$codigo ||
            !$opcoes_sincronizadas ||
            !$dados_auditoria ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível cadastrar o campo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Campo cadastrado com sucesso.',
            ['codigo' => $codigo],
            201
        );
    }

    public function atualizar($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $campo = $this->buscar_campo($codigo);
        $resultado = $this->validar(
            $this->input->post(),
            $campo['grupo_codigo'],
            $campo['codigo']
        );

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $dados_anteriores = $this->preparar_dados_auditoria(
            $campo['codigo']
        );

        $this->db->trans_begin();

        $atualizado = $this->formulario_campo_model->atualizar(
            $campo['codigo'],
            $resultado['dados']
        );

        $opcoes_sincronizadas = $atualizado
            ? $this->campo_opcao_model->sincronizar(
                $campo['codigo'],
                $resultado['opcoes']
            )
            : FALSE;

        $dados_novos = (
            $atualizado &&
            $opcoes_sincronizadas
        )
            ? $this->preparar_dados_auditoria($campo['codigo'])
            : FALSE;

        $auditoria_salva = (
            $dados_anteriores &&
            $dados_novos
        )
            ? $this->auditoria->registrar(
                'formularios',
                'CAMPO_ATUALIZADO',
                'formulario_campos',
                $campo['codigo'],
                $dados_anteriores,
                $dados_novos
            )
            : FALSE;

        if (
            !$dados_anteriores ||
            !$atualizado ||
            !$opcoes_sincronizadas ||
            !$dados_novos ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível atualizar o campo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Campo atualizado com sucesso.',
            ['codigo' => $campo['codigo']]
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

        $campo = $this->buscar_campo($codigo);
        $campo_adjacente = $this->formulario_campo_model
            ->buscar_adjacente($campo, $direcao);

        if (!$campo_adjacente) {
            resposta_json(
                TRUE,
                'O campo já está no limite da ordenação.',
                ['codigo' => $campo['codigo']]
            );
        }

        $this->db->trans_begin();

        $ordem_original = $campo['ordem'];

        $campo_atualizado = $this->formulario_campo_model
            ->atualizar_ordem(
                $campo['codigo'],
                $campo_adjacente['ordem']
            );

        $adjacente_atualizado = $campo_atualizado
            ? $this->formulario_campo_model->atualizar_ordem(
                $campo_adjacente['codigo'],
                $ordem_original
            )
            : FALSE;

        $campo_novo = (
            $campo_atualizado &&
            $adjacente_atualizado
        )
            ? $this->formulario_campo_model->buscar_por_codigo(
                $campo['codigo']
            )
            : FALSE;

        $campo_adjacente_novo = $campo_novo
            ? $this->formulario_campo_model->buscar_por_codigo(
                $campo_adjacente['codigo']
            )
            : FALSE;

        $dados_anteriores = (
            $campo_novo &&
            $campo_adjacente_novo
        )
            ? [
                'campo' => $campo,
                'campo_adjacente' => $campo_adjacente
            ]
            : FALSE;

        $dados_novos = (
            $campo_novo &&
            $campo_adjacente_novo
        )
            ? [
                'campo' => $campo_novo,
                'campo_adjacente' => $campo_adjacente_novo
            ]
            : FALSE;

        $auditoria_salva = $dados_anteriores && $dados_novos
            ? $this->auditoria->registrar(
                'formularios',
                'CAMPO_REORDENADO',
                'formulario_campos',
                $campo['codigo'],
                $dados_anteriores,
                $dados_novos
            )
            : FALSE;

        if (
            !$campo_atualizado ||
            !$adjacente_atualizado ||
            !$campo_adjacente_novo ||
            !$dados_novos ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível reordenar o campo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Campo reordenado com sucesso.',
            ['codigo' => $campo['codigo']]
        );
    }

    public function excluir($codigo = NULL)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $campo = $this->buscar_campo($codigo);
        $dados_anteriores = $this->preparar_dados_auditoria(
            $campo['codigo']
        );

        $this->db->trans_begin();

        $opcoes_excluidas = $this->campo_opcao_model
            ->excluir_por_campo($campo['codigo']);
        $campo_excluido = $opcoes_excluidas
            ? $this->formulario_campo_model->excluir(
                $campo['codigo']
            )
            : FALSE;

        $auditoria_salva = (
            $dados_anteriores &&
            $campo_excluido
        )
            ? $this->auditoria->registrar(
                'formularios',
                'CAMPO_EXCLUIDO',
                'formulario_campos',
                $campo['codigo'],
                $dados_anteriores,
                NULL
            )
            : FALSE;

        if (
            !$dados_anteriores ||
            !$opcoes_excluidas ||
            !$campo_excluido ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível excluir o campo.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Campo excluído com sucesso.',
            ['codigo' => $campo['codigo']]
        );
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
        $formulario = $this->formulario_model->buscar();

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

    private function buscar_campo($codigo)
    {
        if (empty($codigo) || !ctype_digit((string) $codigo)) {
            resposta_json(
                FALSE,
                'Não foi possível identificar o campo.',
                ['erros' => ['O código do campo é inválido.']],
                422
            );
        }

        $campo = $this->formulario_campo_model->buscar_por_codigo(
            (int) $codigo
        );
        $formulario = $this->formulario_model->buscar();

        if (
            !$campo ||
            !$formulario ||
            (int) $campo['formulario_codigo'] !==
                (int) $formulario['codigo']
        ) {
            resposta_json(
                FALSE,
                'Campo não encontrado ou já excluído.',
                [],
                404
            );
        }

        return $campo;
    }

    private function validar(
        $reg,
        $grupo_codigo,
        $codigo = NULL
    ) {
        $tipos = $this->formulario_campo_model->listar_tipos();
        $tipo = trim($reg['tipo'] ?? '');
        $dados = [
            'nome' => trim($reg['nome'] ?? ''),
            'tipo' => $tipo,
            'placeholder' => trim($reg['placeholder'] ?? ''),
            'texto_ajuda' => trim($reg['texto_ajuda'] ?? ''),
            'obrigatorio' => isset($reg['obrigatorio']) &&
                (string) $reg['obrigatorio'] === '1'
                    ? 1
                    : 0,
            'filtravel' => isset($reg['filtravel']) &&
                (string) $reg['filtravel'] === '1'
                    ? 1
                    : 0,
            'tamanho_maximo' => trim($reg['tamanho_maximo'] ?? ''),
            'numero_minimo' => trim($reg['numero_minimo'] ?? ''),
            'numero_maximo' => trim($reg['numero_maximo'] ?? ''),
            'data_minima' => trim($reg['data_minima'] ?? ''),
            'data_maxima' => trim($reg['data_maxima'] ?? ''),
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
        } elseif ($this->formulario_campo_model->nome_em_uso(
            $grupo_codigo,
            $dados['nome'],
            $codigo
        )) {
            $erros[] = 'Já existe um campo com o nome informado neste grupo.';
        }

        if (!array_key_exists($tipo, $tipos)) {
            $erros[] = 'Selecione um tipo de campo válido.';
        }

        if (strlen($dados['placeholder']) > 150) {
            $erros[] = 'O campo Placeholder deve possuir no máximo 150 caracteres.';
        }

        if (strlen($dados['texto_ajuda']) > 255) {
            $erros[] = 'O texto de ajuda deve possuir no máximo 255 caracteres.';
        }

        $this->validar_regras_tipo($dados, $erros);
        $opcoes = $this->preparar_opcoes($reg, $tipo, $erros);

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => $erros
            ];
        }

        $dados = $this->normalizar_regras_tipo($dados);

        return [
            'sucesso' => TRUE,
            'dados' => $dados,
            'opcoes' => $opcoes
        ];
    }

    private function validar_regras_tipo($dados, &$erros)
    {
        if (in_array($dados['tipo'], ['texto_curto', 'texto_longo'], TRUE)) {
            if (
                $dados['tamanho_maximo'] !== '' &&
                (
                    !ctype_digit($dados['tamanho_maximo']) ||
                    (int) $dados['tamanho_maximo'] < 1 ||
                    (int) $dados['tamanho_maximo'] > 65535
                )
            ) {
                $erros[] = 'O tamanho máximo deve ser um número inteiro entre 1 e 65535.';
            }
        }

        if ($dados['tipo'] === 'numero') {
            if (
                $dados['numero_minimo'] !== '' &&
                !is_numeric($dados['numero_minimo'])
            ) {
                $erros[] = 'O valor mínimo deve ser numérico.';
            } elseif (
                $dados['numero_minimo'] !== '' &&
                !$this->numero_valido_banco($dados['numero_minimo'])
            ) {
                $erros[] = 'O valor mínimo deve possuir até 11 inteiros e 4 casas decimais.';
            }

            if (
                $dados['numero_maximo'] !== '' &&
                !is_numeric($dados['numero_maximo'])
            ) {
                $erros[] = 'O valor máximo deve ser numérico.';
            } elseif (
                $dados['numero_maximo'] !== '' &&
                !$this->numero_valido_banco($dados['numero_maximo'])
            ) {
                $erros[] = 'O valor máximo deve possuir até 11 inteiros e 4 casas decimais.';
            }

            if (
                is_numeric($dados['numero_minimo']) &&
                is_numeric($dados['numero_maximo']) &&
                (float) $dados['numero_minimo'] >
                    (float) $dados['numero_maximo']
            ) {
                $erros[] = 'O valor mínimo não pode ser maior que o máximo.';
            }
        }

        if ($dados['tipo'] === 'data') {
            if (
                $dados['data_minima'] !== '' &&
                !$this->data_valida($dados['data_minima'])
            ) {
                $erros[] = 'A data mínima é inválida.';
            }

            if (
                $dados['data_maxima'] !== '' &&
                !$this->data_valida($dados['data_maxima'])
            ) {
                $erros[] = 'A data máxima é inválida.';
            }

            if (
                $this->data_valida($dados['data_minima']) &&
                $this->data_valida($dados['data_maxima']) &&
                $dados['data_minima'] > $dados['data_maxima']
            ) {
                $erros[] = 'A data mínima não pode ser posterior à máxima.';
            }
        }
    }

    private function preparar_opcoes($reg, $tipo, &$erros)
    {
        if (!in_array(
            $tipo,
            ['selecao_unica', 'multipla_selecao'],
            TRUE
        )) {
            return [];
        }

        $nomes = isset($reg['opcao_nome']) &&
            is_array($reg['opcao_nome'])
                ? $reg['opcao_nome']
                : [];
        $codigos = isset($reg['opcao_codigo']) &&
            is_array($reg['opcao_codigo'])
                ? $reg['opcao_codigo']
                : [];
        $opcoes = [];
        $nomes_utilizados = [];

        foreach ($nomes as $indice => $nome) {
            $nome = trim($nome);
            $codigo = trim($codigos[$indice] ?? '');

            if ($nome === '') {
                $erros[] = 'Informe o nome de todas as opções.';
                continue;
            }

            if (strlen($nome) > 100) {
                $erros[] = 'Cada opção deve possuir no máximo 100 caracteres.';
            }

            $nome_normalizado = $this->normalizar($nome);

            if (in_array($nome_normalizado, $nomes_utilizados, TRUE)) {
                $erros[] = 'As opções do campo não podem ser duplicadas.';
            }

            $nomes_utilizados[] = $nome_normalizado;

            if ($codigo !== '' && !ctype_digit($codigo)) {
                $erros[] = 'Uma das opções informadas é inválida.';
            }

            $opcoes[] = [
                'codigo' => $codigo !== '' ? (int) $codigo : NULL,
                'nome' => $nome
            ];
        }

        if (count($opcoes) < 2) {
            $erros[] = 'Cadastre pelo menos duas opções para o campo.';
        }

        return $opcoes;
    }

    private function normalizar_regras_tipo($dados)
    {
        $dados['placeholder'] = in_array(
            $dados['tipo'],
            ['texto_curto', 'texto_longo', 'numero'],
            TRUE
        ) && $dados['placeholder'] !== ''
            ? $dados['placeholder']
            : NULL;
        $dados['texto_ajuda'] = $dados['texto_ajuda'] !== ''
            ? $dados['texto_ajuda']
            : NULL;
        $dados['tamanho_maximo'] = in_array(
            $dados['tipo'],
            ['texto_curto', 'texto_longo'],
            TRUE
        ) && $dados['tamanho_maximo'] !== ''
            ? (int) $dados['tamanho_maximo']
            : NULL;
        $dados['numero_minimo'] =
            $dados['tipo'] === 'numero' &&
            $dados['numero_minimo'] !== ''
                ? $dados['numero_minimo']
                : NULL;
        $dados['numero_maximo'] =
            $dados['tipo'] === 'numero' &&
            $dados['numero_maximo'] !== ''
                ? $dados['numero_maximo']
                : NULL;
        $dados['data_minima'] =
            $dados['tipo'] === 'data' &&
            $dados['data_minima'] !== ''
                ? $dados['data_minima']
                : NULL;
        $dados['data_maxima'] =
            $dados['tipo'] === 'data' &&
            $dados['data_maxima'] !== ''
                ? $dados['data_maxima']
                : NULL;

        return $dados;
    }

    private function gerar_chave_unica($nome)
    {
        $base = $this->normalizar($nome);
        $chave = $base;
        $sufixo = 2;

        while ($this->formulario_campo_model->chave_em_uso($chave)) {
            $chave = $base . '_' . $sufixo;
            $sufixo++;
        }

        return $chave;
    }

    private function normalizar($valor)
    {
        $normalizado = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $valor
        );
        $normalizado = $normalizado !== FALSE
            ? $normalizado
            : $valor;
        $normalizado = strtolower($normalizado);
        $normalizado = preg_replace(
            '/[^a-z0-9]+/',
            '_',
            $normalizado
        );
        $normalizado = trim($normalizado, '_');
        $normalizado = substr($normalizado, 0, 90);

        return $normalizado !== '' ? $normalizado : 'campo';
    }

    private function numero_valido_banco($numero)
    {
        return preg_match(
            '/^-?[0-9]{1,11}(\.[0-9]{1,4})?$/',
            $numero
        ) === 1;
    }

    private function data_valida($data)
    {
        if ($data === '') {
            return FALSE;
        }

        $objeto = DateTime::createFromFormat('Y-m-d', $data);

        return $objeto && $objeto->format('Y-m-d') === $data;
    }

    private function preparar_dados_auditoria($codigo)
    {
        $campo = $this->formulario_campo_model->buscar_por_codigo(
            $codigo
        );

        if (!$campo) {
            return FALSE;
        }

        $campo['opcoes'] = $this->campo_opcao_model
            ->listar_por_campo($codigo);

        return $campo;
    }
}
