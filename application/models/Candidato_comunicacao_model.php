<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Candidato_comunicacao_model extends CI_Model
{
    public function listar_modelos()
    {
        $this->db->select([
            'codigo', 'nome', 'assunto', 'conteudo', 'ativo',
            'cadastro', 'atualizacao'
        ]);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('nome', 'ASC');

        return $this->db->get('candidato_mensagens')->result_array();
    }

    public function listar_modelos_ativos()
    {
        $this->db->where('ativo', 1);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('nome', 'ASC');

        return $this->db->get('candidato_mensagens')->result_array();
    }

    public function buscar_modelo($codigo, $somente_ativo = FALSE)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($somente_ativo) {
            $this->db->where('ativo', 1);
        }

        return $this->db->get('candidato_mensagens')->row_array();
    }

    public function salvar_modelo($dados, $codigo = NULL)
    {
        $dados['atualizacao'] = date('Y-m-d H:i:s');

        if ($codigo === NULL) {
            $dados['cadastro'] = date('Y-m-d H:i:s');
            return $this->db->insert('candidato_mensagens', $dados)
                ? $this->db->insert_id()
                : FALSE;
        }

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->update('candidato_mensagens', $dados);
    }

    public function excluir_modelo($codigo)
    {
        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);
        return $this->db->update('candidato_mensagens', [
            'ativo' => 0,
            'exclusao' => date('Y-m-d H:i:s'),
            'atualizacao' => date('Y-m-d H:i:s')
        ]);
    }

    public function registrar_envio($dados)
    {
        $dados['cadastro'] = date('Y-m-d H:i:s');
        return $this->db->insert('candidato_envios', $dados);
    }

    public function listar_envios_por_candidato($candidato_codigo, $limite = 50)
    {
        $this->db->select([
            'e.codigo', 'e.destinatario', 'e.assunto', 'e.status',
            'e.erro', 'e.enviado_em', 'e.cadastro', 'm.nome AS modelo_nome',
            'u.nome AS usuario_nome'
        ]);
        $this->db->from('candidato_envios e');
        $this->db->join('candidato_mensagens m', 'm.codigo = e.modelo_codigo', 'LEFT');
        $this->db->join('usuarios u', 'u.codigo = e.usuario_codigo', 'LEFT');
        $this->db->where('e.candidato_codigo', $candidato_codigo);
        $this->db->order_by('e.codigo', 'DESC');
        $this->db->limit($limite);

        return $this->db->get()->result_array();
    }

    public function campos_publicados()
    {
        $this->load->model('formulario_model');
        $this->load->model('formulario_publicacao_model');
        $formulario = $this->formulario_model->buscar();

        if (!$formulario) {
            return [];
        }

        $publicacao = $this->formulario_publicacao_model
            ->buscar_atual($formulario['codigo']);
        $estrutura = $publicacao
            ? json_decode($publicacao['estrutura'] ?? '', TRUE)
            : [];
        $campos = [];

        foreach (($estrutura['secoes'] ?? []) as $secao) {
            foreach (($secao['grupos'] ?? []) as $grupo) {
                foreach (($grupo['campos'] ?? []) as $campo) {
                    if (!empty($campo['chave'])) {
                        $campos[$campo['chave']] = $campo['nome'];
                    }
                }
            }
        }

        asort($campos, SORT_NATURAL | SORT_FLAG_CASE);
        return $campos;
    }

    public function renderizar($texto, $candidato, $situacoes)
    {
        $variaveis = [
            '{{nome_completo}}' => $candidato['nome_completo'],
            '{{email}}' => $candidato['email'],
            '{{telefone}}' => $candidato['telefone'],
            '{{situacao_seletiva}}' => $situacoes[$candidato['situacao_seletiva']] ?? $candidato['situacao_seletiva'],
            '{{cadastro}}' => date('d/m/Y', strtotime($candidato['cadastro']))
        ];

        return strtr($texto, $variaveis);
    }
}
