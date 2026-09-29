<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_campo_model extends CI_Model
{
    private $tabela = 'formulario_campos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_tipos()
    {
        return [
            'texto_curto' => 'Texto curto',
            'texto_longo' => 'Texto longo',
            'numero' => 'Número',
            'data' => 'Data',
            'sim_nao' => 'Sim ou não',
            'selecao_unica' => 'Seleção única',
            'multipla_selecao' => 'Múltipla seleção'
        ];
    }

    public function listar_por_formulario($formulario_codigo)
    {
        $this->db->select([
            'c.codigo',
            'c.grupo_codigo',
            'c.nome',
            'c.chave',
            'c.tipo',
            'c.placeholder',
            'c.texto_ajuda',
            'c.obrigatorio',
            'c.filtravel',
            'c.tamanho_maximo',
            'c.numero_minimo',
            'c.numero_maximo',
            'c.data_minima',
            'c.data_maxima',
            'c.ordem',
            'c.ativo',
            'c.publicado',
            'c.cadastro',
            'c.atualizacao'
        ]);
        $this->db->from($this->tabela . ' c');
        $this->db->join(
            'formulario_grupos g',
            'g.codigo = c.grupo_codigo',
            'INNER'
        );
        $this->db->join(
            'formulario_secoes s',
            's.codigo = g.secao_codigo',
            'INNER'
        );
        $this->db->where('s.formulario_codigo', $formulario_codigo);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->where('g.exclusao IS NULL', NULL, FALSE);
        $this->db->where('c.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('s.ordem', 'ASC');
        $this->db->order_by('g.ordem', 'ASC');
        $this->db->order_by('c.ordem', 'ASC');
        $this->db->order_by('c.codigo', 'ASC');

        return $this->db->get()->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'c.codigo',
            'c.grupo_codigo',
            'c.nome',
            'c.chave',
            'c.tipo',
            'c.placeholder',
            'c.texto_ajuda',
            'c.obrigatorio',
            'c.filtravel',
            'c.tamanho_maximo',
            'c.numero_minimo',
            'c.numero_maximo',
            'c.data_minima',
            'c.data_maxima',
            'c.ordem',
            'c.ativo',
            'c.publicado',
            'c.cadastro',
            'c.atualizacao',
            'g.secao_codigo',
            's.formulario_codigo'
        ]);
        $this->db->from($this->tabela . ' c');
        $this->db->join(
            'formulario_grupos g',
            'g.codigo = c.grupo_codigo',
            'INNER'
        );
        $this->db->join(
            'formulario_secoes s',
            's.codigo = g.secao_codigo',
            'INNER'
        );
        $this->db->where('c.codigo', $codigo);
        $this->db->where('c.exclusao IS NULL', NULL, FALSE);
        $this->db->where('g.exclusao IS NULL', NULL, FALSE);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function chave_em_uso($chave)
    {
        $this->db->where('chave', $chave);

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function nome_em_uso($grupo_codigo, $nome, $codigo = NULL)
    {
        $this->db->where('grupo_codigo', $grupo_codigo);
        $this->db->where('nome', $nome);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function cadastrar($reg)
    {
        $reg['ordem'] = $this->proxima_ordem($reg['grupo_codigo']);
        $reg['cadastro'] = date('Y-m-d H:i:s');

        if (!$this->db->insert($this->tabela, $reg)) {
            return FALSE;
        }

        return $this->db->insert_id();
    }

    public function atualizar($codigo, $reg)
    {
        $reg['atualizacao'] = date('Y-m-d H:i:s');

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->update($this->tabela, $reg);
    }

    public function atualizar_ordem($codigo, $ordem)
    {
        return $this->atualizar(
            $codigo,
            ['ordem' => (int) $ordem]
        );
    }

    public function buscar_adjacente($campo, $direcao)
    {
        $this->db->select([
            'codigo',
            'grupo_codigo',
            'nome',
            'chave',
            'tipo',
            'ordem',
            'ativo'
        ]);
        $this->db->where('grupo_codigo', $campo['grupo_codigo']);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($direcao === 'subir') {
            $this->db->where('ordem <', $campo['ordem']);
            $this->db->order_by('ordem', 'DESC');
        } else {
            $this->db->where('ordem >', $campo['ordem']);
            $this->db->order_by('ordem', 'ASC');
        }

        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function excluir($codigo)
    {
        $dados = [
            'ativo' => 0,
            'atualizacao' => date('Y-m-d H:i:s'),
            'exclusao' => date('Y-m-d H:i:s')
        ];

        $this->db->where('codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if (!$this->db->update($this->tabela, $dados)) {
            return FALSE;
        }

        return $this->db->affected_rows() > 0;
    }

    public function marcar_publicados($formulario_codigo)
    {
        $sql = 'UPDATE ' . $this->tabela . ' c
            INNER JOIN formulario_grupos g
                ON g.codigo = c.grupo_codigo
            INNER JOIN formulario_secoes s
                ON s.codigo = g.secao_codigo
            SET c.publicado = 1,
                c.atualizacao = ?
            WHERE s.formulario_codigo = ?
                AND s.ativo = 1
                AND g.ativo = 1
                AND c.ativo = 1
                AND c.publicado = 0
                AND s.exclusao IS NULL
                AND g.exclusao IS NULL
                AND c.exclusao IS NULL';

        return $this->db->query(
            $sql,
            [date('Y-m-d H:i:s'), (int) $formulario_codigo]
        );
    }

    private function proxima_ordem($grupo_codigo)
    {
        $this->db->select_max('ordem');
        $this->db->where('grupo_codigo', $grupo_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $resultado = $this->db->get($this->tabela)->row_array();

        return (int) ($resultado['ordem'] ?? 0) + 1;
    }
}
