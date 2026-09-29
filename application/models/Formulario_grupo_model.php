<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Formulario_grupo_model extends CI_Model
{
    private $tabela = 'formulario_grupos';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function listar_por_formulario($formulario_codigo)
    {
        $this->db->select([
            'g.codigo',
            'g.secao_codigo',
            'g.nome',
            'g.descricao',
            'g.repetivel',
            'g.quantidade_minima',
            'g.quantidade_maxima',
            'g.ordem',
            'g.ativo',
            'g.cadastro',
            'g.atualizacao',
            '(SELECT COUNT(*) FROM formulario_campos c
                WHERE c.grupo_codigo = g.codigo
                AND c.exclusao IS NULL) AS total_campos'
        ], FALSE);
        $this->db->from($this->tabela . ' g');
        $this->db->join(
            'formulario_secoes s',
            's.codigo = g.secao_codigo',
            'INNER'
        );
        $this->db->where('s.formulario_codigo', $formulario_codigo);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->where('g.exclusao IS NULL', NULL, FALSE);
        $this->db->order_by('s.ordem', 'ASC');
        $this->db->order_by('g.ordem', 'ASC');
        $this->db->order_by('g.codigo', 'ASC');

        return $this->db->get()->result_array();
    }

    public function buscar_por_codigo($codigo)
    {
        $this->db->select([
            'g.codigo',
            'g.secao_codigo',
            'g.nome',
            'g.descricao',
            'g.repetivel',
            'g.quantidade_minima',
            'g.quantidade_maxima',
            'g.ordem',
            'g.ativo',
            'g.cadastro',
            'g.atualizacao',
            's.formulario_codigo'
        ]);
        $this->db->from($this->tabela . ' g');
        $this->db->join(
            'formulario_secoes s',
            's.codigo = g.secao_codigo',
            'INNER'
        );
        $this->db->where('g.codigo', $codigo);
        $this->db->where('g.exclusao IS NULL', NULL, FALSE);
        $this->db->where('s.exclusao IS NULL', NULL, FALSE);
        $this->db->limit(1);

        return $this->db->get()->row_array();
    }

    public function nome_em_uso($secao_codigo, $nome, $codigo = NULL)
    {
        $this->db->where('secao_codigo', $secao_codigo);
        $this->db->where('nome', $nome);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($codigo !== NULL) {
            $this->db->where('codigo !=', $codigo);
        }

        return $this->db->count_all_results($this->tabela) > 0;
    }

    public function cadastrar($reg)
    {
        $reg['ordem'] = $this->proxima_ordem($reg['secao_codigo']);
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

    public function buscar_adjacente($grupo, $direcao)
    {
        $this->db->select([
            'codigo',
            'secao_codigo',
            'nome',
            'descricao',
            'repetivel',
            'quantidade_minima',
            'quantidade_maxima',
            'ordem',
            'ativo'
        ]);
        $this->db->where('secao_codigo', $grupo['secao_codigo']);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        if ($direcao === 'subir') {
            $this->db->where('ordem <', $grupo['ordem']);
            $this->db->order_by('ordem', 'DESC');
        } else {
            $this->db->where('ordem >', $grupo['ordem']);
            $this->db->order_by('ordem', 'ASC');
        }

        $this->db->order_by('codigo', 'ASC');

        return $this->db->get($this->tabela, 1)->row_array();
    }

    public function possui_campos($codigo)
    {
        $this->db->where('grupo_codigo', $codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        return $this->db->count_all_results('formulario_campos') > 0;
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

    private function proxima_ordem($secao_codigo)
    {
        $this->db->select_max('ordem');
        $this->db->where('secao_codigo', $secao_codigo);
        $this->db->where('exclusao IS NULL', NULL, FALSE);

        $resultado = $this->db->get($this->tabela)->row_array();

        return (int) ($resultado['ordem'] ?? 0) + 1;
    }
}
