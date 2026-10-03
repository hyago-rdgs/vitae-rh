<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidatos extends CI_Controller
{
    private $limite = 20;

    public function __construct()
    {
        parent::__construct();

        $this->controle_acesso->valida_permissao('candidatos.consultar');
        $this->load->database();
        $this->load->helper('download');
        $this->load->library('token_admin');
        $this->load->model('candidato_model');
        $this->load->model('candidato_filtro_model');
        $this->load->model('candidato_formulario_model');
        $this->load->model('candidato_resposta_model');
        $this->load->model('formulario_publicacao_model');
        $this->load->model('candidato_historico_model');
        $this->load->model('candidato_comunicacao_model');
    }

    public function index()
    {
        if ($this->input->method() !== 'get') {
            show_404();
        }

        $filtros = [
            'termo' => $this->obter_filtro_texto('termo', 100),
            'status' => $this->obter_status(),
            'situacao_seletiva' => $this->obter_situacao_seletiva(),
            'data_inicio' => $this->obter_data('data_inicio'),
            'data_fim' => $this->obter_data('data_fim')
        ];
        $erros_filtro = [];
        $campos_filtro = $this->candidato_filtro_model->campos();
        $filtros['campos'] = $this->candidato_filtro_model->validar(
            $this->input->get('campos'), $campos_filtro, $erros_filtro
        );

        if ($filtros['data_inicio'] === FALSE) {
            $filtros['data_inicio'] = '';
            $erros_filtro[] = 'A data inicial informada não é válida.';
        }

        if ($filtros['data_fim'] === FALSE) {
            $filtros['data_fim'] = '';
            $erros_filtro[] = 'A data final informada não é válida.';
        }

        if (
            $filtros['data_inicio'] !== '' &&
            $filtros['data_fim'] !== '' &&
            $filtros['data_inicio'] > $filtros['data_fim']
        ) {
            $erros_filtro[] = 'A data inicial deve ser anterior ou igual à data final.';
            $filtros['data_inicio'] = '';
            $filtros['data_fim'] = '';
        }

        $pagina = $this->obter_pagina();
        $total = $this->candidato_model->contar_para_administracao($filtros);
        $total_paginas = max(1, (int) ceil($total / $this->limite));

        if ($pagina > $total_paginas) {
            $pagina = $total_paginas;
        }

        $this->load->view('candidatos/lista', [
            'candidatos' => $this->candidato_model->listar_para_administracao(
                $filtros,
                $this->limite,
                ($pagina - 1) * $this->limite
            ),
            'filtros' => $filtros,
            'erros_filtro' => $erros_filtro,
            'total' => $total,
            'pagina' => $pagina,
            'total_paginas' => $total_paginas,
            'situacoes_seletivas' => $this->candidato_historico_model->situacoes(),
            'campos_filtro' => $campos_filtro,
            'mensagem_comunicacao' => $this->session->flashdata('candidato_comunicacao_mensagem'),
            'erro_comunicacao' => $this->session->flashdata('candidato_comunicacao_erro')
        ]);
    }

    public function detalhe($codigo = NULL)
    {
        if ($this->input->method() !== 'get' || !$this->codigo_valido($codigo)) {
            show_404();
        }

        $candidato = $this->candidato_model->buscar_por_codigo((int) $codigo);

        if (!$candidato) {
            show_404();
        }

        $formulario = $this->candidato_formulario_model
            ->buscar_por_candidato($candidato['codigo']);

        if (!$formulario) {
            show_error(
                'As informações do perfil não estão disponíveis.',
                500,
                'Perfil indisponível'
            );
        }

        $publicacao = $this->formulario_publicacao_model
            ->buscar_por_codigo($formulario['formulario_publicacao_codigo']);
        $estrutura = $publicacao
            ? json_decode($publicacao['estrutura'], TRUE)
            : FALSE;

        if (!is_array($estrutura) || empty($estrutura['secoes'])) {
            show_error(
                'A estrutura usada neste cadastro não está disponível.',
                500,
                'Perfil indisponível'
            );
        }

        $respostas = $this->candidato_resposta_model
            ->listar_por_candidato_formulario($formulario['codigo']);

        $this->load->view('candidatos/detalhe', [
            'candidato' => $candidato,
            'formulario' => $formulario,
            'estrutura' => $this->montar_perfil($estrutura, $respostas),
            'situacoes_seletivas' => $this->candidato_historico_model->situacoes(),
            'historico' => $this->candidato_historico_model
                ->listar_por_candidato($candidato['codigo']),
            'pode_gerenciar' => $this->controle_acesso
                ->tem_permissao('candidatos.gerenciar'),
            'mensagem_gestao' => $this->session->flashdata('candidato_gestao_mensagem'),
            'erro_gestao' => $this->session->flashdata('candidato_gestao_erro'),
            'modelos_mensagem' => $this->tem_permissao_comunicacao()
                ? $this->candidato_comunicacao_model->listar_modelos_ativos()
                : [],
            'pode_comunicar' => $this->tem_permissao_comunicacao(),
            'mensagem_comunicacao' => $this->session->flashdata('candidato_comunicacao_mensagem'),
            'erro_comunicacao' => $this->session->flashdata('candidato_comunicacao_erro')
        ]);
    }

    public function enviar_mensagem($codigo = NULL)
    {
        $this->exigir_comunicacao();

        if ($this->input->method() !== 'post' || !$this->codigo_valido($codigo)) {
            show_404();
        }
        $this->token_admin->exigir();

        $modelo_codigo = (int) $this->input->post('modelo_codigo', TRUE);
        if ($this->input->post('canal') === 'whatsapp') {
            $candidato = $this->candidato_model->buscar_por_codigo((int) $codigo);
            $modelo = $this->candidato_comunicacao_model->buscar_modelo($modelo_codigo, TRUE);
            if (!$candidato || !$modelo) show_404();
            $telefone = preg_replace('/[^0-9]/', '', $candidato['telefone']);
            if (strlen($telefone) === 10 || strlen($telefone) === 11) $telefone = '55' . $telefone;
            if (!preg_match('/^[1-9][0-9]{10,14}$/D', $telefone)) {
                $this->session->set_flashdata('candidato_comunicacao_erro', 'Revise o telefone com DDD e código do país.');
                redirect('candidatos/detalhe/' . (int) $codigo);
                return;
            }
            $texto = $this->candidato_comunicacao_model->renderizar(
                $modelo['conteudo'], $candidato, $this->candidato_historico_model->situacoes()
            );
            redirect('https://wa.me/' . $telefone . '?text=' . rawurlencode($texto), 'location', 303);
            return;
        }
        $resultado = $this->enviar_modelo((int) $codigo, $modelo_codigo);
        $chave = $resultado['sucesso'] && empty($resultado['aviso'])
            ? 'candidato_comunicacao_mensagem'
            : 'candidato_comunicacao_erro';
        $this->session->set_flashdata($chave, $resultado['mensagem']);
        redirect('candidatos/detalhe/' . (int) $codigo);
    }

    public function atualizar_situacao($codigo = NULL)
    {
        $this->exigir_gerenciamento();

        if ($this->input->method() !== 'post' || !$this->codigo_valido($codigo)) {
            show_404();
        }

        $situacao = $this->input->post('situacao_seletiva', TRUE);
        if (!$this->candidato_historico_model->situacao_valida($situacao)) {
            $this->session->set_flashdata('candidato_gestao_erro', 'A situação informada não é válida.');
            redirect('candidatos/detalhe/' . (int) $codigo);
            return;
        }

        $this->db->trans_begin();
        $candidato = $this->candidato_model->buscar_situacao_seletiva((int) $codigo, TRUE);

        if (!$candidato) {
            $this->db->trans_rollback();
            show_404();
        }

        $atual = $candidato['situacao_seletiva'];
        if ($atual === $situacao) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('candidato_gestao_mensagem', 'A situação não foi alterada.');
            redirect('candidatos/detalhe/' . (int) $codigo);
            return;
        }

        $atualizado = $this->candidato_model->atualizar_situacao_seletiva($codigo, $situacao);
        $registrado = $atualizado && $this->candidato_historico_model->registrar_situacao(
            $codigo,
            $this->controle_acesso->get('codigo'),
            $atual,
            $situacao
        );

        if (!$registrado || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('candidato_gestao_erro', 'Não foi possível atualizar a situação.');
        } else {
            $this->db->trans_commit();
            $this->session->set_flashdata('candidato_gestao_mensagem', 'Situação atualizada.');
        }

        redirect('candidatos/detalhe/' . (int) $codigo);
    }

    public function adicionar_anotacao($codigo = NULL)
    {
        $this->exigir_gerenciamento();

        if ($this->input->method() !== 'post' || !$this->codigo_valido($codigo)) {
            show_404();
        }

        $candidato = $this->candidato_model->buscar_por_codigo((int) $codigo);
        $anotacao = $this->input->post('anotacao', TRUE);
        $anotacao = is_string($anotacao) ? trim($anotacao) : '';

        $tamanho_anotacao = function_exists('mb_strlen')
            ? mb_strlen($anotacao, 'UTF-8')
            : strlen($anotacao);

        if (!$candidato || $anotacao === '' || $tamanho_anotacao > 5000) {
            $this->session->set_flashdata('candidato_gestao_erro', 'Informe uma anotação com até 5.000 caracteres.');
            redirect('candidatos/detalhe/' . (int) $codigo);
            return;
        }

        $salvo = $this->candidato_historico_model->registrar_anotacao(
            $codigo,
            $this->controle_acesso->get('codigo'),
            $anotacao
        );

        $this->session->set_flashdata(
            $salvo ? 'candidato_gestao_mensagem' : 'candidato_gestao_erro',
            $salvo ? 'Anotação registrada.' : 'Não foi possível registrar a anotação.'
        );
        redirect('candidatos/detalhe/' . (int) $codigo);
    }

    private function exigir_gerenciamento()
    {
        $this->controle_acesso->valida_permissao('candidatos.gerenciar');
    }

    private function exigir_comunicacao()
    {
        $this->controle_acesso->valida_permissao('candidatos.comunicar');
    }

    private function tem_permissao_comunicacao()
    {
        return $this->controle_acesso->tem_permissao('candidatos.comunicar');
    }

    private function enviar_modelo($codigo, $modelo_codigo)
    {
        $candidato = $this->candidato_model->buscar_por_codigo($codigo);
        $modelo = $this->candidato_comunicacao_model->buscar_modelo($modelo_codigo, TRUE);

        if (!$candidato || !$modelo) {
            return ['sucesso' => FALSE, 'mensagem' => 'Candidato ou modelo inválido.'];
        }

        $situacoes = $this->candidato_historico_model->situacoes();
        $assunto = $this->candidato_comunicacao_model->renderizar($modelo['assunto'], $candidato, $situacoes);
        $conteudo = $this->candidato_comunicacao_model->renderizar($modelo['conteudo'], $candidato, $situacoes);
        $erro = NULL;
        $enviado = FALSE;
        $this->load->library('correio');
        $enviado = $this->correio->enviar($candidato['email'], $assunto, $conteudo);
        if (!$enviado) $erro = 'Não foi possível enviar. Verifique PHPMailer e a configuração SMTP.';

        $registrado = $this->candidato_comunicacao_model->registrar_envio([
            'candidato_codigo' => $codigo,
            'usuario_codigo' => $this->controle_acesso->get('codigo'),
            'modelo_codigo' => $modelo['codigo'],
            'destinatario' => $candidato['email'],
            'assunto' => $assunto,
            'conteudo' => $conteudo,
            'status' => $enviado ? 'enviado' : 'erro',
            'erro' => $erro,
            'enviado_em' => $enviado ? date('Y-m-d H:i:s') : NULL
        ]);

        if (!$registrado) {
            log_message('error', 'Falha ao registrar envio de mensagem para candidato ' . $codigo . '.');
        }

        return [
            'sucesso' => $enviado,
            'aviso' => $enviado && !$registrado,
            'mensagem' => $enviado
                ? ($registrado ? 'Mensagem enviada com sucesso.' : 'Mensagem enviada, mas o histórico não pôde ser registrado.')
                : ($erro ?: 'Não foi possível enviar a mensagem.')
        ];
    }

    public function foto($codigo = NULL)
    {
        if ($this->input->method() !== 'get' || !$this->codigo_valido($codigo)) {
            show_404();
        }

        $candidato = $this->candidato_model->buscar_por_codigo((int) $codigo);

        if (!$candidato) {
            show_404();
        }

        $caminho = $this->caminho_arquivo($candidato['foto_arquivo']);

        if (!$caminho || !is_file($caminho) || !is_readable($caminho)) {
            show_404();
        }

        $informacao = @getimagesize($caminho);
        $tipos_permitidos = ['image/jpeg', 'image/png'];

        if (!$informacao || !in_array($informacao['mime'], $tipos_permitidos, TRUE)) {
            show_404();
        }

        $this->output
            ->set_header('Content-Type: ' . $informacao['mime'])
            ->set_header('Content-Disposition: inline; filename="foto-perfil"')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_output(file_get_contents($caminho));
    }

    public function curriculo($codigo = NULL)
    {
        if ($this->input->method() !== 'get' || !$this->codigo_valido($codigo)) {
            show_404();
        }

        $candidato = $this->candidato_model->buscar_por_codigo((int) $codigo);

        if (!$candidato) {
            show_404();
        }

        $caminho = $this->caminho_arquivo($candidato['curriculo_arquivo']);

        if (!$caminho || !is_file($caminho) || !is_readable($caminho)) {
            show_404();
        }

        $nome = preg_replace(
            '/[\x00-\x1F\x7F"]/',
            '',
            basename($candidato['curriculo_nome_original'])
        );

        if ($nome === '') {
            $nome = 'curriculo';
        }

        header('X-Content-Type-Options: nosniff');
        force_download($nome, file_get_contents($caminho));
    }

    private function montar_perfil($estrutura, $respostas)
    {
        $respostas_por_ocorrencia = [];

        foreach ($respostas as $resposta) {
            $grupo_codigo = (int) $resposta['grupo_codigo'];
            $indice = (int) $resposta['indice'];

            if (!isset($respostas_por_ocorrencia[$grupo_codigo][$indice])) {
                $respostas_por_ocorrencia[$grupo_codigo][$indice] = [];
            }

            if ($resposta['campo_chave'] !== NULL) {
                $respostas_por_ocorrencia[$grupo_codigo][$indice][
                    $resposta['campo_chave']
                ] = $resposta;
            }
        }

        foreach ($estrutura['secoes'] as &$secao) {
            foreach ($secao['grupos'] as &$grupo) {
                $grupo_codigo = (int) $grupo['codigo'];
                $ocorrencias = $respostas_por_ocorrencia[$grupo_codigo] ?? [];
                ksort($ocorrencias, SORT_NUMERIC);
                $grupo['ocorrencias'] = [];

                foreach ($ocorrencias as $indice => $respostas_grupo) {
                    $campos = [];

                    foreach ($grupo['campos'] as $campo) {
                        $resposta = $respostas_grupo[$campo['chave']] ?? NULL;

                        if (!$resposta) {
                            continue;
                        }

                        $valor = $this->formatar_resposta($campo, $resposta);

                        if ($valor !== '') {
                            $campos[] = [
                                'nome' => $campo['nome'],
                                'valor' => $valor
                            ];
                        }
                    }

                    $grupo['ocorrencias'][] = [
                        'indice' => $indice,
                        'campos' => $campos
                    ];
                }
            }
            unset($grupo);
        }
        unset($secao);

        return $estrutura;
    }

    private function formatar_resposta($campo, $resposta)
    {
        switch ($campo['tipo']) {
            case 'numero':
                $valor = $resposta['valor_numero'];
                break;
            case 'data':
                $valor = $resposta['valor_data'];

                if ($valor !== NULL) {
                    $data = DateTime::createFromFormat('!Y-m-d', $valor);
                    $valor = $data ? $data->format('d/m/Y') : $valor;
                }

                break;
            case 'sim_nao':
                $valor = $resposta['valor_booleano'] === NULL
                    ? ''
                    : ((int) $resposta['valor_booleano'] === 1 ? 'Sim' : 'Não');
                break;
            case 'multipla_selecao':
                $valores = json_decode($resposta['valor_json'] ?? '', TRUE);
                $valor = is_array($valores)
                    ? $this->rotulos_opcoes($campo, $valores)
                    : [];
                break;
            default:
                $valor = $resposta['valor_texto'];
                break;
        }

        if (in_array($campo['tipo'], ['selecao_unica'], TRUE)) {
            $valor = $this->rotulo_opcao($campo, $valor);
        }

        if (is_array($valor)) {
            return implode(', ', $valor);
        }

        return $valor === NULL ? '' : (string) $valor;
    }

    private function rotulos_opcoes($campo, $valores)
    {
        $rotulos = [];

        foreach ($valores as $valor) {
            $rotulos[] = $this->rotulo_opcao($campo, $valor);
        }

        return $rotulos;
    }

    private function rotulo_opcao($campo, $valor)
    {
        foreach (($campo['opcoes'] ?? []) as $opcao) {
            if ((string) $opcao['valor'] === (string) $valor) {
                return $opcao['nome'];
            }
        }

        return (string) $valor;
    }

    private function caminho_arquivo($nome)
    {
        if (!is_string($nome) || $nome === '' || basename($nome) !== $nome) {
            return FALSE;
        }

        return FCPATH . 'uploads/candidatos/' . $nome;
    }

    private function obter_filtro_texto($chave, $limite)
    {
        $valor = $this->input->get($chave, TRUE);

        if (!is_string($valor)) {
            return '';
        }

        return function_exists('mb_substr')
            ? mb_substr(trim($valor), 0, $limite, 'UTF-8')
            : substr(trim($valor), 0, $limite);
    }

    private function obter_status()
    {
        $status = $this->input->get('status', TRUE);

        return is_string($status) && in_array($status, ['ativo', 'inativo'], TRUE)
            ? $status
            : '';
    }

    private function obter_situacao_seletiva()
    {
        $situacao = $this->input->get('situacao_seletiva', TRUE);
        return $this->candidato_historico_model->situacao_valida($situacao)
            ? $situacao
            : '';
    }

    private function obter_data($chave)
    {
        $valor = $this->input->get($chave, TRUE);

        if ($valor === NULL || $valor === '') {
            return '';
        }

        if (!is_string($valor)) {
            return FALSE;
        }

        $data = DateTime::createFromFormat('!Y-m-d', $valor);
        $erros = DateTime::getLastErrors();

        if (
            !$data ||
            ($erros && ($erros['warning_count'] || $erros['error_count'])) ||
            $data->format('Y-m-d') !== $valor
        ) {
            return FALSE;
        }

        return $valor;
    }

    private function obter_pagina()
    {
        $pagina = $this->input->get('pagina', TRUE);

        return is_string($pagina) && ctype_digit($pagina) && (int) $pagina > 0
            ? (int) $pagina
            : 1;
    }

    private function codigo_valido($codigo)
    {
        return is_string($codigo) && ctype_digit($codigo) && (int) $codigo > 0;
    }
}
