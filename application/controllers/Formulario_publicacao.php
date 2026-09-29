<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_publicacao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_permissao(
            'formularios.publicar'
        );

        $this->load->library('auditoria');
        $this->load->database();
        $this->load->model('formulario_model');
        $this->load->model('formulario_secao_model');
        $this->load->model('formulario_grupo_model');
        $this->load->model('formulario_campo_model');
        $this->load->model('campo_opcao_model');
        $this->load->model('formulario_publicacao_model');
    }

    public function publicar()
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

        $observacao = trim($this->input->post('observacao') ?? '');

        if (strlen($observacao) > 255) {
            resposta_json(
                FALSE,
                'Verifique os campos informados.',
                [
                    'erros' => [
                        'A observação deve possuir no máximo 255 caracteres.'
                    ]
                ],
                422
            );
        }

        $resultado = $this->preparar_estrutura($formulario);

        if (!$resultado['sucesso']) {
            resposta_json(
                FALSE,
                'O formulário possui pendências para publicação.',
                ['erros' => $resultado['erros']],
                422
            );
        }

        $this->db->trans_begin();

        $formulario_bloqueado = $this->formulario_model
            ->bloquear_para_publicacao($formulario['codigo']);
        $versao = $formulario_bloqueado
            ? $this->formulario_publicacao_model->proxima_versao(
                $formulario['codigo']
            )
            : FALSE;

        $estrutura = $versao
            ? json_encode(
                $resultado['estrutura'],
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES |
                JSON_PRESERVE_ZERO_FRACTION
            )
            : FALSE;

        $codigo = $estrutura !== FALSE
            ? $this->formulario_publicacao_model->cadastrar([
                'formulario_codigo' => $formulario['codigo'],
                'versao' => $versao,
                'estrutura' => $estrutura,
                'observacao' => $observacao !== ''
                    ? $observacao
                    : NULL,
                'usuario_codigo' => (int) $this->controle_acesso
                    ->get('codigo')
            ])
            : FALSE;

        $campos_marcados = $codigo
            ? $this->formulario_campo_model->marcar_publicados(
                $formulario['codigo']
            )
            : FALSE;

        $publicacao = $campos_marcados
            ? $this->formulario_publicacao_model->buscar_por_codigo(
                $codigo
            )
            : FALSE;

        $dados_auditoria = $publicacao
            ? [
                'codigo' => $publicacao['codigo'],
                'formulario_codigo' => $publicacao['formulario_codigo'],
                'versao' => $publicacao['versao'],
                'observacao' => $publicacao['observacao'],
                'usuario_codigo' => $publicacao['usuario_codigo'],
                'cadastro' => $publicacao['cadastro']
            ]
            : FALSE;

        $auditoria_salva = $dados_auditoria
            ? $this->auditoria->registrar(
                'formularios',
                'FORMULARIO_PUBLICADO',
                'formulario_publicacoes',
                $codigo,
                NULL,
                $dados_auditoria
            )
            : FALSE;

        if (
            !$formulario_bloqueado ||
            !$versao ||
            $estrutura === FALSE ||
            !$codigo ||
            !$campos_marcados ||
            !$publicacao ||
            !$auditoria_salva ||
            $this->db->trans_status() === FALSE
        ) {
            $this->db->trans_rollback();

            resposta_json(
                FALSE,
                'Não foi possível publicar o formulário.',
                [],
                500
            );
        }

        $this->db->trans_commit();

        resposta_json(
            TRUE,
            'Versão ' . $versao . ' publicada com sucesso.',
            [
                'codigo' => $codigo,
                'versao' => $versao
            ],
            201
        );
    }

    private function preparar_estrutura($formulario)
    {
        $secoes = $this->formulario_secao_model
            ->listar_por_formulario($formulario['codigo']);
        $grupos = $this->formulario_grupo_model
            ->listar_por_formulario($formulario['codigo']);
        $campos = $this->formulario_campo_model
            ->listar_por_formulario($formulario['codigo']);
        $opcoes = $this->campo_opcao_model
            ->listar_por_formulario($formulario['codigo']);

        $grupos_por_secao = [];
        $campos_por_grupo = [];
        $opcoes_por_campo = [];

        foreach ($grupos as $grupo) {
            if ((int) $grupo['ativo'] === 1) {
                $grupos_por_secao[$grupo['secao_codigo']][] = $grupo;
            }
        }

        foreach ($campos as $campo) {
            if ((int) $campo['ativo'] === 1) {
                $campos_por_grupo[$campo['grupo_codigo']][] = $campo;
            }
        }

        foreach ($opcoes as $opcao) {
            if ((int) $opcao['ativo'] === 1) {
                $opcoes_por_campo[$opcao['campo_codigo']][] = $opcao;
            }
        }

        $estrutura_secoes = [];
        $erros = [];

        foreach ($secoes as $secao) {
            if ((int) $secao['ativo'] !== 1) {
                continue;
            }

            $grupos_secao = $grupos_por_secao[$secao['codigo']] ?? [];

            if (empty($grupos_secao)) {
                $erros[] = 'A seção "' . $secao['titulo'] .
                    '" não possui grupos ativos.';
                continue;
            }

            $estrutura_grupos = [];

            foreach ($grupos_secao as $grupo) {
                $campos_grupo = $campos_por_grupo[$grupo['codigo']] ?? [];

                if (
                    (int) $grupo['repetivel'] === 1 &&
                    (
                        $grupo['quantidade_maxima'] === NULL ||
                        (int) $grupo['quantidade_maxima'] < 1 ||
                        (int) $grupo['quantidade_minima'] >
                            (int) $grupo['quantidade_maxima']
                    )
                ) {
                    $erros[] = 'Revise os limites do grupo "' .
                        $grupo['nome'] . '".';
                }

                if (empty($campos_grupo)) {
                    $erros[] = 'O grupo "' . $grupo['nome'] .
                        '" não possui campos ativos.';
                    continue;
                }

                $estrutura_campos = [];

                foreach ($campos_grupo as $campo) {
                    $opcoes_campo = $opcoes_por_campo[$campo['codigo']] ?? [];

                    if (
                        in_array(
                            $campo['tipo'],
                            ['selecao_unica', 'multipla_selecao'],
                            TRUE
                        ) &&
                        count($opcoes_campo) < 2
                    ) {
                        $erros[] = 'O campo "' . $campo['nome'] .
                            '" deve possuir pelo menos duas opções ativas.';
                        continue;
                    }

                    $estrutura_campos[] = $this->preparar_campo(
                        $campo,
                        $opcoes_campo
                    );
                }

                $estrutura_grupos[] = [
                    'codigo' => (int) $grupo['codigo'],
                    'nome' => $grupo['nome'],
                    'descricao' => $grupo['descricao'],
                    'repetivel' => (bool) $grupo['repetivel'],
                    'quantidade_minima' => (int) $grupo['quantidade_minima'],
                    'quantidade_maxima' => (int) $grupo['quantidade_maxima'],
                    'ordem' => (int) $grupo['ordem'],
                    'campos' => $estrutura_campos
                ];
            }

            $estrutura_secoes[] = [
                'codigo' => (int) $secao['codigo'],
                'titulo' => $secao['titulo'],
                'descricao' => $secao['descricao'],
                'ordem' => (int) $secao['ordem'],
                'grupos' => $estrutura_grupos
            ];
        }

        if (empty($estrutura_secoes)) {
            $erros[] = 'Cadastre e ative pelo menos uma seção.';
        }

        if (!empty($erros)) {
            return [
                'sucesso' => FALSE,
                'erros' => array_values(array_unique($erros))
            ];
        }

        return [
            'sucesso' => TRUE,
            'estrutura' => [
                'schema_versao' => 1,
                'formulario' => [
                    'codigo' => (int) $formulario['codigo'],
                    'chave' => $formulario['chave'],
                    'nome' => $formulario['nome'],
                    'descricao' => $formulario['descricao']
                ],
                'campos_fixos' => $this->campos_fixos(),
                'secoes' => $estrutura_secoes
            ]
        ];
    }

    private function preparar_campo($campo, $opcoes)
    {
        $estrutura_opcoes = [];

        foreach ($opcoes as $opcao) {
            $estrutura_opcoes[] = [
                'codigo' => (int) $opcao['codigo'],
                'nome' => $opcao['nome'],
                'valor' => $opcao['valor'],
                'ordem' => (int) $opcao['ordem']
            ];
        }

        return [
            'codigo' => (int) $campo['codigo'],
            'nome' => $campo['nome'],
            'chave' => $campo['chave'],
            'tipo' => $campo['tipo'],
            'placeholder' => $campo['placeholder'],
            'texto_ajuda' => $campo['texto_ajuda'],
            'obrigatorio' => (bool) $campo['obrigatorio'],
            'filtravel' => (bool) $campo['filtravel'],
            'tamanho_maximo' => $campo['tamanho_maximo'] !== NULL
                ? (int) $campo['tamanho_maximo']
                : NULL,
            'numero_minimo' => $campo['numero_minimo'],
            'numero_maximo' => $campo['numero_maximo'],
            'data_minima' => $campo['data_minima'],
            'data_maxima' => $campo['data_maxima'],
            'ordem' => (int) $campo['ordem'],
            'opcoes' => $estrutura_opcoes
        ];
    }

    private function campos_fixos()
    {
        return [
            [
                'chave' => 'nome_completo',
                'nome' => 'Nome completo',
                'tipo' => 'texto_curto',
                'obrigatorio' => TRUE
            ],
            [
                'chave' => 'email',
                'nome' => 'E-mail de acesso',
                'tipo' => 'email',
                'obrigatorio' => TRUE
            ],
            [
                'chave' => 'telefone',
                'nome' => 'Telefone/WhatsApp',
                'tipo' => 'telefone',
                'obrigatorio' => TRUE
            ],
            [
                'chave' => 'foto_perfil',
                'nome' => 'Foto de perfil',
                'tipo' => 'arquivo_imagem',
                'obrigatorio' => FALSE
            ],
            [
                'chave' => 'curriculo',
                'nome' => 'Currículo',
                'tipo' => 'arquivo',
                'obrigatorio' => TRUE
            ],
            [
                'chave' => 'consentimento_lgpd',
                'nome' => 'Consentimento LGPD',
                'tipo' => 'aceite',
                'obrigatorio' => TRUE
            ]
        ];
    }
}
